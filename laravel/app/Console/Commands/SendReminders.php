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

        // Live sessions starting in next 24 hours
        $upcomingSessions = LiveSession::with(['lesson.course', 'attendance.student'])
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_start_time', [now(), now()->addHours(24)])
            ->get();

        $sent = 0;

        foreach ($upcomingSessions as $session) {
            $attendees = $session->attendance()->with('student')->get();

            foreach ($attendees as $attendance) {
                $student = $attendance->student;
                if (!$student) continue;

                $subject = "Reminder: Live Session - {$session->lesson->course->title}";
                $body = view('emails.session-reminder', [
                    'student' => $student,
                    'session' => $session,
                    'course' => $session->lesson->course,
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
