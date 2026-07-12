<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Promotion;

class PaymentVerificationService
{
    /**
     * Mark a payment as completed and trigger all downstream business processes.
     * This is the shared path used by manual/admin verification, Lipila webhooks,
     * and Lenco webhooks so that every completion behaves identically.
     */
    public function verifyPayment(Payment $payment): void
    {
        $payment->update([
            'payment_status' => 'Completed',
            'payment_date' => now(),
            'recorded_by' => auth()->id(),
        ]);

        if ($payment->enrollment) {
            $this->updateEnrollmentPaymentStatus($payment->enrollment);
        }

        $this->runCompletionSideEffects($payment);
    }

    /**
     * Run the side effects that should only happen the first time a payment
     * transitions to Completed: promotion usage, invoice generation, receipt
     * email, and in-app notification.
     *
     * Callers are responsible for ensuring this is only invoked on the first
     * transition to Completed. This method guards against incomplete records
     * by refreshing the payment before acting.
     */
    public function runCompletionSideEffects(Payment $payment): void
    {
        $freshPayment = $payment->fresh();
        if (!$freshPayment || !$freshPayment->isCompleted()) {
            return;
        }

        // Increment promotion usage only once.
        if ($freshPayment->promotion_id) {
            Promotion::where('id', $freshPayment->promotion_id)
                ->whereRaw('COALESCE(used_count, 0) < COALESCE(max_uses, 999999)')
                ->increment('used_count');
        }

        // Generate invoice if none exists.
        if (!$freshPayment->invoice) {
            try {
                $invoiceService = app(InvoiceService::class);
                $invoiceService->generateInvoice($freshPayment);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to generate invoice for completed payment', [
                    'payment_id' => $freshPayment->payment_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send receipt email and in-app notification.
        try {
            $emailService = app(\App\Services\EmailQueueService::class);
            $user = $freshPayment->enrollment?->user ?? $freshPayment->student?->user;
            $userId = $user?->id ?? $freshPayment->student?->user_id;

            if ($user && $user->email) {
                $emailService->sendTemplated($user->email, 'Payment', [
                    'name' => $user->full_name,
                    'course' => $freshPayment->course?->title,
                    'amount' => number_format($freshPayment->amount, 2),
                    'date' => $freshPayment->payment_date?->format('F d, Y'),
                ]);
            }

            if ($userId) {
                $emailService->sendNotification(
                    $userId,
                    'Payment Received',
                    "Your payment of ZMW {$freshPayment->amount} for {$freshPayment->course?->title} has been received.",
                    'payment'
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send payment receipt email', [
                'payment_id' => $freshPayment->payment_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update enrollment payment status based on total payments.
     * Single source of truth for the 30% deposit rule and 100% certificate rule.
     */
    public function updateEnrollmentPaymentStatus(Enrollment $enrollment): void
    {
        $this->recalculateEnrollmentState($enrollment);
    }

    /**
     * Recalculate enrollment state from all remaining completed payments.
     * Syncs amount_paid, payment_status, enrollment_status, certificate_blocked,
     * and the related payment plan totals/balance.
     *
     * Discounts (Payment.discount_amount) are counted toward the paid total so
     * that a promotion covering part or all of the fee unlocks content/certificates
     * consistently across all payment gateways and manual verification.
     */
    public function recalculateEnrollmentState(Enrollment $enrollment): void
    {
        $coursePrice = $enrollment->effectivePrice();

        $totalPaid = Payment::where('enrollment_id', $enrollment->id)
            ->where('payment_status', 'Completed')
            ->sum('amount');

        $totalDiscount = Payment::where('enrollment_id', $enrollment->id)
            ->where('payment_status', 'Completed')
            ->sum('discount_amount');

        $effectivePaid = (float) $totalPaid + (float) $totalDiscount;
        $percentagePaid = $coursePrice > 0 ? ($effectivePaid / $coursePrice) * 100 : 100;

        $enrollmentStatus = $enrollment->enrollment_status;
        if ($percentagePaid >= 30 && in_array($enrollmentStatus, ['Enrolled'])) {
            $enrollmentStatus = 'In Progress';
        }

        $certificateBlocked = $effectivePaid < $coursePrice;
        $paymentStatus = $effectivePaid >= $coursePrice ? 'completed' : ($effectivePaid > 0 ? 'partial' : 'pending');

        $enrollment->update([
            'amount_paid' => $totalPaid,
            'payment_status' => $paymentStatus,
            'enrollment_status' => $enrollmentStatus,
            'certificate_blocked' => $certificateBlocked,
        ]);

        // Update payment plan
        $paymentPlan = $enrollment->paymentPlan;
        if ($paymentPlan) {
            $totalFee = (float) ($paymentPlan->total_fee ?: $coursePrice);
            $paymentPlan->update([
                'total_paid' => $totalPaid,
                'balance' => max(0, $totalFee - $effectivePaid),
                'payment_status' => $paymentStatus,
            ]);
        }
    }
}
