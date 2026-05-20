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
            'student' => $student,
            'course' => $course,
            'payment' => $this->payment,
        ])->render();

        $emailService->queue($student->email, $subject, $body, [], 5);
    }
}
