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

        // Generate invoice for this payment
        $invoiceService = new InvoiceService();
        $invoiceService->generateInvoice($payment);
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
