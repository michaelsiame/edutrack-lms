<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Services\EmailQueueService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCertificateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Certificate $certificate
    ) {}

    public function handle(EmailQueueService $emailService): void
    {
        $user = $this->certificate->user;
        $course = $this->certificate->course;

        if (!$user || !$course) {
            return;
        }

        $subject = "Your Certificate for {$course->title} is Ready!";
        $body = view('emails.certificate-issued', [
            'user' => $user,
            'course' => $course,
            'certificate' => $this->certificate,
        ])->render();

        $emailService->queue($user->email, $subject, $body, [], 5);
    }
}
