<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\EmailQueueService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Payment $payment
    ) {}

    public function handle(EmailQueueService $emailService): void
    {
        $student = $this->payment->student;
        $course = $this->payment->course;

        if (!$student || !$course) {
            return;
        }

        $subject = "Payment Receipt - {$course->title}";
        $body = view('emails.payment-receipt', [
            'name' => $student->user?->full_name ?? $student->user?->first_name ?? 'there',
            'course' => $course->title,
            'amount' => number_format((float) $this->payment->amount, 2),
            'date' => ($this->payment->payment_date ?? $this->payment->created_at)?->format('F d, Y'),
            'status' => $this->payment->payment_status ?? 'Completed',
        ])->render();

        $emailService->queue($student->email, $subject, $body, [], 5);
    }
}
