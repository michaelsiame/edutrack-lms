<?php

namespace App\Console\Commands;

use App\Services\EmailQueueService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;

class PreviewEmails extends Command
{
    protected $signature = 'emails:preview {email : Recipient address}';

    protected $description = 'Render every email template with sample data and send them to one address for visual review.';

    public function handle(EmailQueueService $mail): int
    {
        $to = $this->argument('email');

        $user = (object) ['first_name' => 'Michael', 'last_name' => 'Siame', 'email' => $to];
        $course = (object) ['title' => 'Certificate in Digital Literacy'];
        $session = (object) [
            'title' => 'Live Q&A: Getting Online Safely',
            'scheduled_start_time' => now()->addDay()->setTime(15, 0),
            'duration_minutes' => 60,
            'description' => 'Bring your questions about email, mobile money safety, and avoiding scams.',
            'join_url' => 'https://edutrackzambia.com/live',
        ];

        // template view => [subject, data]
        $templates = [
            'welcome' => ['Welcome to Edutrack LMS', ['name' => 'Michael', 'login_url' => url('/login')]],
            'verify-email' => ['Verify your email address', ['user' => $user, 'verificationUrl' => url('/verify-email/sample-token-123')]],
            'password-reset' => ['Reset your password', ['user' => $user, 'resetUrl' => url('/reset-password/sample-token-123')]],
            'enrollment-confirmed' => ['Enrollment confirmed', ['name' => 'Michael', 'course' => $course->title, 'course_url' => url('/student/dashboard')]],
            'payment-receipt' => ['Your payment receipt', ['name' => 'Michael', 'course' => $course->title, 'amount' => '950.00', 'date' => now()->format('F d, Y')]],
            'certificate-issued' => ['Your certificate is ready', ['name' => 'Michael', 'course' => $course->title, 'certificate_number' => 'ECTC26001', 'download_url' => url('/student/certificates')]],
            'assignment-graded' => ['Your assignment has been graded', ['studentName' => 'Michael', 'assignmentTitle' => 'Build Your Digital Toolkit', 'courseTitle' => $course->title, 'pointsEarned' => 84, 'maxPoints' => 100, 'feedback' => 'Strong work organising your files. Add clearer folder names next time.']],
            'progress-reminder' => ['We miss you at Edutrack', ['student' => $user, 'course' => $course, 'progress' => 35]],
            'session-reminder' => ['Reminder: your live session is coming up', ['student' => $user, 'course' => $course, 'session' => $session]],
        ];

        $sent = 0;
        foreach ($templates as $view => [$subject, $data]) {
            try {
                $body = View::make("emails.{$view}", $data)->render();
                $mail->queue($to, "[Preview] {$subject}", $body, [], 10);
                $this->line("  queued: {$view}");
                $sent++;
            } catch (\Throwable $e) {
                $this->error("  FAILED {$view}: " . $e->getMessage());
            }
        }

        $this->info("Queued {$sent} preview emails to {$to}. Processing the queue now...");
        $this->call('email:process', ['--limit' => 50]);

        return self::SUCCESS;
    }
}
