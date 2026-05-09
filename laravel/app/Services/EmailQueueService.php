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
     */
    public function queue(string $recipient, string $subject, string $body, array $attachments = [], int $priority = 0): EmailQueue
    {
        return EmailQueue::create([
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'attachments' => !empty($attachments) ? json_encode($attachments) : null,
            'status' => 'pending',
            'priority' => $priority,
            'scheduled_at' => now(),
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

                // Mark as failed permanently after 3 attempts
                if ($email->attempts >= 3) {
                    $email->update(['status' => 'failed']);
                }
            }
        }

        return $sent;
    }

    /**
     * Send a queued email.
     */
    public function send(EmailQueue $email): void
    {
        $email->update(['status' => 'processing']);

        $this->mailer->clearAddresses();
        $this->mailer->addAddress($email->recipient);
        $this->mailer->Subject = $email->subject;
        $this->mailer->isHTML(true);
        $this->mailer->Body = $email->body;
        $this->mailer->AltBody = strip_tags($email->body);

        // Handle attachments
        if ($email->attachments) {
            $attachments = json_decode($email->attachments, true);
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $this->mailer->addAttachment($attachment);
                }
            }
        }

        $this->mailer->send();

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
}
