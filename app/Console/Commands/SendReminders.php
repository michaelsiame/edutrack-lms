<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Services\EmailQueueService;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send session and course reminders to students';

    public function handle(EmailQueueService $emailService): int
    {
        $this->info('Sending session reminders...');

        // Live sessions starting in next 24 hours — notify all enrolled students
        $upcomingSessions = LiveSession::with(['lesson.course'])
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_start_time', [now(), now()->addHours(24)])
            ->get();

        $sent = 0;

        foreach ($upcomingSessions as $session) {
            $course = $session->lesson?->course;
            if (!$course) {
                continue;
            }

            $enrollments = Enrollment::with('user')
                ->where('course_id', $course->id)
                ->where('enrollment_status', 'In Progress')
                ->get();

            foreach ($enrollments as $enrollment) {
                $student = $enrollment->user;
                if (!$student || !$student->email) {
                    continue;
                }

                $subject = "Reminder: Live Session - {$course->title}";
                $body = view('emails.session-reminder', [
                    'student' => $student,
                    'session' => $session,
                    'course' => $course,
                ])->render();

                $emailService->queue($student->email, $subject, $body, [], 3);
                $sent++;
            }
        }

        $this->info("Sent {$sent} session reminders.");

        // Students with low progress
        $this->info('Checking for students with low progress...');
        $lowProgressEnrollments = Enrollment::with(['user', 'course'])
            ->where('enrollment_status', 'In Progress')
            ->where('progress', '<', 25)
            ->where('last_accessed', '<', now()->subDays(7))
            ->get();

        foreach ($lowProgressEnrollments as $enrollment) {
            if (!$enrollment->user || !$enrollment->user->email) {
                continue;
            }

            $subject = "Don't Give Up! Continue {$enrollment->course->title}";
            $body = view('emails.progress-reminder', [
                'student' => $enrollment->user,
                'course' => $enrollment->course,
                'progress' => $enrollment->progress,
            ])->render();

            $emailService->queue($enrollment->user->email, $subject, $body, [], 2);
            $sent++;
        }

        $this->info("Sent {$sent} total reminders.");
        return self::SUCCESS;
    }
}
