<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Payment;

class PaymentVerificationService
{
    /**
     * Mark a payment as completed and update enrollment status.
     * Replicates the logic from LencoPaymentService::updateEnrollmentPaymentStatus.
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

        // Generate invoice for this payment (only if not already generated)
        if (!$payment->invoice) {
            $invoiceService = app(InvoiceService::class);
            $invoiceService->generateInvoice($payment);
        }

        // Send payment receipt email
        try {
            $emailService = app(\App\Services\EmailQueueService::class);
            $user = $payment->enrollment?->user ?? $payment->student?->user;
            $userId = $user?->id ?? $payment->student?->user_id;

            if ($user && $user->email) {
                $emailService->sendTemplated($user->email, 'Payment', [
                    'name' => $user->full_name,
                    'course' => $payment->course?->title,
                    'amount' => number_format($payment->amount, 2),
                    'date' => $payment->payment_date?->format('F d, Y'),
                ]);
            }

            if ($userId) {
                $emailService->sendNotification($userId, 'Payment Received', "Your payment of ZMW {$payment->amount} for {$payment->course?->title} has been received.", 'payment');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send payment receipt email: ' . $e->getMessage());
        }
    }

    /**
     * Update enrollment payment status based on total payments.
     * Implements the 30% deposit rule and 100% certificate rule.
     */
    protected function updateEnrollmentPaymentStatus(Enrollment $enrollment): void
    {
        $coursePrice = $enrollment->course->discount_price ?? $enrollment->course->price;

        $totalPaid = Payment::where('enrollment_id', $enrollment->id)
            ->where('payment_status', 'Completed')
            ->sum('amount');

        $percentagePaid = $coursePrice > 0 ? ($totalPaid / $coursePrice) * 100 : 100;

        // Determine enrollment status based on payment
        $enrollmentStatus = $enrollment->enrollment_status;
        if ($percentagePaid >= 30 && $enrollmentStatus === 'Enrolled') {
            $enrollmentStatus = 'In Progress';
        }

        // Certificate blocked until fully paid
        $certificateBlocked = $totalPaid < $coursePrice;

        $paymentStatus = $totalPaid >= $coursePrice ? 'completed' : 'pending';

        $enrollment->update([
            'amount_paid' => $totalPaid,
            'payment_status' => $paymentStatus,
            'enrollment_status' => $enrollmentStatus,
            'certificate_blocked' => $certificateBlocked,
        ]);

        // Update payment plan
        $paymentPlan = $enrollment->paymentPlan;
        if ($paymentPlan) {
            $paymentPlan->update([
                'total_paid' => $totalPaid,
                'payment_status' => $paymentStatus,
            ]);
        }
    }
}
