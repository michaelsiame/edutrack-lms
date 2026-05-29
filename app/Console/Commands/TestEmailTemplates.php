<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EmailQueueService;
use Illuminate\Console\Command;

class TestEmailTemplates extends Command
{
    protected $signature = 'email:test-templates {recipient=siamem570@gmail.com}';
    protected $description = 'Send test emails for all templates to verify design and delivery';

    public function handle(): int
    {
        $recipient = $this->argument('recipient');
        $service = app(EmailQueueService::class);

        // Create a mock user for template variables
        $user = User::first() ?? new User([
            'first_name' => 'Michael',
            'last_name' => 'Siame',
            'email' => $recipient,
        ]);

        $this->info("Sending test emails to: {$recipient}");
        $this->newLine();

        $templates = [
            [
                'name' => 'Welcome Email',
                'view' => 'emails.welcome',
                'subject' => 'Welcome to Edutrack LMS!',
                'data' => [
                    'name' => 'Michael Siame',
                    'login_url' => url('/login'),
                ],
            ],
            [
                'name' => 'Verify Email',
                'view' => 'emails.verify-email',
                'subject' => 'Verify Your Email Address',
                'data' => [
                    'user' => $user,
                    'token' => 'test-verification-token-12345',
                    'verificationUrl' => url('/verify-email/test-token'),
                ],
            ],
            [
                'name' => 'Enrollment Confirmed',
                'view' => 'emails.enrollment-confirmed',
                'subject' => 'Enrollment Confirmed: Cybersecurity Fundamentals',
                'data' => [
                    'name' => 'Michael Siame',
                    'course' => 'Cybersecurity Fundamentals',
                    'course_url' => url('/courses/cybersecurity-fundamentals'),
                ],
            ],
            [
                'name' => 'Payment Receipt',
                'view' => 'emails.payment-receipt',
                'subject' => 'Payment Receipt - Cybersecurity Fundamentals',
                'data' => [
                    'name' => 'Michael Siame',
                    'course' => 'Cybersecurity Fundamentals',
                    'amount' => '2,500.00',
                    'date' => now()->format('F d, Y'),
                ],
            ],
            [
                'name' => 'Certificate Issued',
                'view' => 'emails.certificate-issued',
                'subject' => 'Your Certificate is Ready!',
                'data' => [
                    'name' => 'Michael Siame',
                    'course' => 'Cybersecurity Fundamentals',
                    'certificate_number' => 'NRC 2495807/1/1-ABC123',
                    'download_url' => url('/certificates/download/1'),
                ],
            ],
            [
                'name' => 'Password Reset',
                'view' => 'emails.password-reset',
                'subject' => 'Password Reset Request',
                'data' => [
                    'user' => $user,
                    'resetUrl' => url('/reset-password/test-token'),
                ],
            ],
            [
                'name' => 'Assignment Graded',
                'view' => 'emails.assignment-graded',
                'subject' => 'Your Assignment Has Been Graded',
                'data' => [
                    'studentName' => 'Michael Siame',
                    'assignmentTitle' => 'Network Security Lab',
                    'courseTitle' => 'Cybersecurity Fundamentals',
                    'pointsEarned' => '85',
                    'maxPoints' => '100',
                    'feedback' => 'Excellent work on the firewall configuration section. Consider adding more detail on intrusion detection systems.',
                ],
            ],
            [
                'name' => 'Session Reminder',
                'view' => 'emails.session-reminder',
                'subject' => 'Live Session Reminder: Network Security Q&A',
                'data' => [
                    'student' => $user,
                    'course' => (object)['title' => 'Cybersecurity Fundamentals'],
                    'session' => (object)[
                        'title' => 'Network Security Q&A',
                        'scheduled_start_time' => now()->addHours(2),
                        'duration_minutes' => 60,
                        'description' => 'Join us for an interactive Q&A session on network security best practices.',
                        'join_url' => 'https://meet.jit.si/edutrack-test-session',
                    ],
                ],
            ],
            [
                'name' => 'Progress Reminder',
                'view' => 'emails.progress-reminder',
                'subject' => 'We Miss You! Keep Learning',
                'data' => [
                    'student' => $user,
                    'course' => (object)['title' => 'Cybersecurity Fundamentals'],
                    'progress' => 45,
                ],
            ],
        ];

        $sent = 0;
        $failed = 0;

        foreach ($templates as $template) {
            $this->info("Sending: {$template['name']}...");

            try {
                $body = view($template['view'], $template['data'])->render();
                $service->queue($recipient, $template['subject'], $body, [], 0);
                $this->line("  <fg=green>✓ Sent</>");
                $sent++;
            } catch (\Exception $e) {
                $this->line("  <fg=red>✗ Failed: {$e->getMessage()}</>");
                $failed++;
            }

            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }

        $this->newLine();
        $this->info("Results: {$sent} sent, {$failed} failed");
        $this->info("Check {$recipient} inbox (and spam folder) for the test emails.");

        return $failed > 0 ? 1 : 0;
    }
}
