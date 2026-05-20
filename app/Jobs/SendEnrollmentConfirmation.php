<?php

namespace App\Jobs;

use App\Models\Enrollment;
use App\Services\EmailQueueService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEnrollmentConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Enrollment $enrollment
    ) {}

    public function handle(EmailQueueService $emailService): void
    {
        $user = $this->enrollment->user;
        $course = $this->enrollment->course;

        if (!$user || !$course) {
            return;
        }

        $subject = "Enrollment Confirmed: {$course->title}";
        $body = view('emails.enrollment-confirmed', [
            'user' => $user,
            'course' => $course,
            'enrollment' => $this->enrollment,
        ])->render();

        $emailService->queue($user->email, $subject, $body, [], 5);
    }
}
