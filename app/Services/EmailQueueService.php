<?php

namespace App\Services;

use App\Models\EmailQueue;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailQueueService
{
    protected PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }

    protected function configureMailer(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = config('mail.mailers.smtp.host', 'smtp.gmail.com');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = config('mail.mailers.smtp.username');
        $this->mailer->Password = config('mail.mailers.smtp.password');
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = config('mail.mailers.smtp.port', 587);
        $this->mailer->setFrom(
            config('mail.from.address', 'edutrackzambia@gmail.com'),
            config('mail.from.name', 'Edutrack LMS')
        );
    }

    /**
     * Queue an email for sending.
     * Always sends immediately for shared hosting (no queue workers).
     */
    public function queue(string $recipient, string $subject, string $body, array $attachments = [], int $priority = 0): EmailQueue
    {
        $email = EmailQueue::create([
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'attachments' => !empty($attachments) ? json_encode($attachments) : null,
            'status' => 'pending',
            'priority' => $priority,
            'scheduled_at' => now(),
        ]);

        // Always send immediately — shared hosting has no queue workers
        try {
            $this->send($email);
        } catch (\Exception $e) {
            \Log::error('Sync email failed', ['error' => $e->getMessage(), 'recipient' => $recipient]);
        }

        return $email;
    }

    /**
     * Send a templated email using an active EmailTemplate.
     */
    public function sendTemplated(string $recipient, string $templateType, array $data = [], array $attachments = []): ?EmailQueue
    {
        $template = \App\Models\EmailTemplate::where('template_type', $templateType)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            \Log::warning("Email template not found: {$templateType}");
            return null;
        }

        $subject = $this->replacePlaceholders($template->subject, $data);
        $body = $this->replacePlaceholders($template->body, $data);

        return $this->queue($recipient, $subject, $body, $attachments);
    }

    /**
     * Replace {{placeholder}} values in a string.
     */
    protected function replacePlaceholders(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace('{{' . $key . '}}', (string) $value, $text);
        }
        return $text;
    }

    /**
     * Create an in-app notification for a user.
     */
    public function sendNotification(int $userId, string $title, string $message, string $type = 'info', ?string $actionUrl = null): void
    {
        \App\Models\Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'notification_type' => $type,
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);
    }

    /**
     * Process pending emails in the queue.
     */
    public function processQueue(int $limit = 50): int
    {
        $emails = EmailQueue::pending()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        $sent = 0;

        foreach ($emails as $email) {
            try {
                $this->send($email);
                $sent++;
            } catch (\Exception $e) {
                Log::error('Email queue send failed', [
                    'email_id' => $email->id,
                    'error' => $e->getMessage(),
                ]);

                $email->update([
                    'status' => 'failed',
                    'attempts' => $email->attempts + 1,
                    'last_attempt' => now(),
                ]);

                // Mark as failed permanently after 3 attempts (use fresh value)
                if ($email->fresh()->attempts >= 3) {
                    $email->update(['status' => 'failed']);
                }
            }
        }

        return $sent;
    }

    /**
     * Send a queued email.
     * Falls back to PHP mail() on shared hosting if SMTP fails.
     */
    public function send(EmailQueue $email): void
    {
        $email->update(['status' => 'processing']);

        $smtpFailed = false;

        // Try SMTP first if credentials are configured
        if (!empty(config('mail.mailers.smtp.password'))) {
            try {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($email->recipient);
                $this->mailer->Subject = $email->subject;
                $this->mailer->isHTML(true);
                $this->mailer->Body = $email->body;
                $this->mailer->AltBody = strip_tags($email->body);

                if ($email->attachments) {
                    $attachments = json_decode($email->attachments, true);
                    foreach ($attachments as $attachment) {
                        if (file_exists($attachment)) {
                            $this->mailer->addAttachment($attachment);
                        }
                    }
                }

                $this->mailer->send();
            } catch (\Exception $e) {
                Log::warning('SMTP send failed, will try mail() fallback', [
                    'recipient' => $email->recipient,
                    'error' => $e->getMessage(),
                ]);
                $smtpFailed = true;
            }
        } else {
            $smtpFailed = true;
        }

        // Fallback to PHP mail() for shared hosting
        if ($smtpFailed) {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . config('mail.from.address', 'edutrackzambia@gmail.com') . "\r\n";
            $headers .= "Reply-To: " . config('mail.from.address', 'edutrackzambia@gmail.com') . "\r\n";

            $sent = mail($email->recipient, $email->subject, $email->body, $headers);

            if (!$sent) {
                throw new \RuntimeException('Both SMTP and mail() failed to send email to ' . $email->recipient);
            }
        }

        $email->update([
            'status' => 'sent',
            'sent_at' => now(),
            'attempts' => $email->attempts + 1,
        ]);
    }

    /**
     * Send an email immediately (not queued).
     */
    public function sendImmediate(string $recipient, string $subject, string $body): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($recipient);
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            $this->mailer->send();

            return true;
        } catch (PHPMailerException $e) {
            Log::error('Immediate email failed', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send a time-sensitive email NOW (verification links, password resets,
     * etc.) so the recipient isn't waiting on the 5-minute queue. If immediate
     * SMTP delivery fails, fall back to the queue (which the cron retries and
     * which has its own mail() fallback) so the message is never lost.
     */
    public function sendUrgent(string $recipient, string $subject, string $body): void
    {
        if (!$this->sendImmediate($recipient, $subject, $body)) {
            $this->queue($recipient, $subject, $body, [], 10);
        }
    }
}
