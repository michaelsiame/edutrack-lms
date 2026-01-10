<?php
/**
 * Email Notification Service
 * Handles all automated email notifications in the system
 */

require_once __DIR__ . '/Email.php';

class EmailNotificationService {

    private $db;
    private $emailClass;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->emailClass = new Email();
    }

    /**
     * Send Welcome Email
     */
    public function sendWelcomeEmail($userId) {
        try {
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            if (!$user) return false;

            $subject = "Welcome to " . (getenv('APP_NAME') ?: 'Edutrack LMS');
            $body = $this->loadTemplate('welcome', [
                'first_name' => $user['first_name'],
                'email' => $user['email'],
                'login_url' => (getenv('APP_URL') ?: 'https://edutrackzambia.com') . '/login.php'
            ]);

            $this->queueEmail($user['email'], $subject, $body, 'welcome');
            return true;
        } catch (Exception $e) {
            error_log("Failed to send welcome email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Enrollment Confirmation Email
     */
    public function sendEnrollmentEmail($enrollmentId) {
        try {
            $enrollment = $this->db->fetchOne("
                SELECT e.*, u.first_name, u.last_name, u.email,
                       c.title as course_title, c.price, c.start_date
                FROM enrollments e
                JOIN users u ON e.user_id = u.id
                JOIN courses c ON e.course_id = c.id
                WHERE e.id = ?
            ", [$enrollmentId]);

            if (!$enrollment) return false;

            $subject = "Enrollment Confirmation - " . $enrollment['course_title'];
            $body = $this->loadTemplate('enrollment-confirm', [
                'first_name' => $enrollment['first_name'],
                'course_title' => $enrollment['course_title'],
                'start_date' => date('F j, Y', strtotime($enrollment['start_date'])),
                'course_price' => number_format($enrollment['price'], 2),
                'currency' => getenv('CURRENCY') ?: 'ZMW',
                'course_url' => (getenv('APP_URL') ?: 'https://edutrackzambia.com') . '/learn.php?course=' . $enrollment['course_id']
            ]);

            $this->queueEmail($enrollment['email'], $subject, $body, 'enrollment');
            return true;
        } catch (Exception $e) {
            error_log("Failed to send enrollment email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Payment Receipt Email
     */
    public function sendPaymentReceipt($transactionId) {
        try {
            $transaction = $this->db->fetchOne("
                SELECT t.*, u.first_name, u.last_name, u.email,
                       pm.method_name
                FROM transactions t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
                WHERE t.transaction_id = ?
            ", [$transactionId]);

            if (!$transaction) return false;

            $subject = "Payment Receipt - " . $transaction['reference_number'];
            $body = $this->loadTemplate('payment-received', [
                'first_name' => $transaction['first_name'],
                'amount' => number_format($transaction['amount'], 2),
                'currency' => getenv('CURRENCY') ?: 'ZMW',
                'transaction_id' => $transaction['reference_number'],
                'payment_method' => $transaction['method_name'] ?: 'N/A',
                'transaction_date' => date('F j, Y g:i A', strtotime($transaction['processed_at'])),
                'description' => $transaction['description'] ?: 'Course Payment',
                'receipt_url' => (getenv('APP_URL') ?: 'https://edutrackzambia.com') . '/receipt.php?id=' . $transaction['transaction_id']
            ]);

            $this->queueEmail($transaction['email'], $subject, $body, 'payment');
            return true;
        } catch (Exception $e) {
            error_log("Failed to send payment receipt: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Certificate Issued Email
     */
    public function sendCertificateEmail($certificateId) {
        try {
            $certificate = $this->db->fetchOne("
                SELECT cert.*, u.first_name, u.last_name, u.email,
                       c.title as course_title
                FROM certificates cert
                JOIN enrollments e ON cert.enrollment_id = e.id
                JOIN users u ON e.user_id = u.id
                JOIN courses c ON e.course_id = c.id
                WHERE cert.certificate_id = ?
            ", [$certificateId]);

            if (!$certificate) return false;

            $subject = "Certificate Issued - " . $certificate['course_title'];
            $body = $this->loadTemplate('certificate-issued', [
                'first_name' => $certificate['first_name'],
                'course_title' => $certificate['course_title'],
                'certificate_number' => $certificate['certificate_number'],
                'issued_date' => date('F j, Y', strtotime($certificate['issued_date'])),
                'verification_code' => $certificate['verification_code'],
                'download_url' => (getenv('APP_URL') ?: 'https://edutrackzambia.com') . '/download-certificate.php?id=' . $certificate['certificate_id'],
                'verify_url' => (getenv('APP_URL') ?: 'https://edutrackzambia.com') . '/verify-certificate.php'
            ]);

            $this->queueEmail($certificate['email'], $subject, $body, 'certificate');
            return true;
        } catch (Exception $e) {
            error_log("Failed to send certificate email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Load email template
     */
    private function loadTemplate($templateName, $variables = []) {
        $templatePath = dirname(__DIR__) . '/mail/' . $templateName . '.php';

        if (!file_exists($templatePath)) {
            // Fallback to simple template
            return $this->generateSimpleTemplate($variables);
        }

        // Extract variables for template
        extract($variables);

        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Generate simple fallback template
     */
    private function generateSimpleTemplate($variables) {
        $html = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
        $html .= '<h2 style="color: #2E70DA;">Edutrack Computer Training College</h2>';

        foreach ($variables as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));
            $html .= '<p><strong>' . htmlspecialchars($label) . ':</strong> ' . htmlspecialchars($value) . '</p>';
        }

        $html .= '<hr><p style="color: #666; font-size: 12px;">This is an automated email from Edutrack LMS. Please do not reply.</p>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Queue email for sending (uses email_queue table)
     */
    private function queueEmail($recipient, $subject, $body, $type = 'notification') {
        try {
            $this->db->insert('email_queue', [
                'recipient' => $recipient,
                'subject' => $subject,
                'body' => $body,
                'status' => 'pending',
                'attempts' => 0,
                'priority' => $this->getPriority($type),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Failed to queue email: " . $e->getMessage());

            // Fallback: send immediately if queue fails
            return $this->emailClass->send($recipient, $subject, $body);
        }
    }

    /**
     * Get priority based on email type
     */
    private function getPriority($type) {
        $priorities = [
            'welcome' => 5,
            'enrollment' => 5,
            'payment' => 10,
            'certificate' => 8,
            'password_reset' => 10,
            'notification' => 1
        ];

        return $priorities[$type] ?? 1;
    }

    /**
     * Process email queue (called by cron job)
     */
    public static function processQueue($limit = 50) {
        $db = Database::getInstance();
        $emailClass = new Email();

        // Get pending emails, ordered by priority and creation time
        $emails = $db->fetchAll("
            SELECT * FROM email_queue
            WHERE status = 'pending'
            AND (scheduled_at IS NULL OR scheduled_at <= NOW())
            AND attempts < 3
            ORDER BY priority DESC, created_at ASC
            LIMIT ?
        ", [$limit]);

        $sent = 0;
        $failed = 0;

        foreach ($emails as $email) {
            // Update status to processing
            $db->update('email_queue',
                ['status' => 'processing', 'last_attempt' => date('Y-m-d H:i:s')],
                'id = ?',
                [$email['id']]
            );

            // Attempt to send
            if ($emailClass->send($email['recipient'], $email['subject'], $email['body'])) {
                // Mark as sent
                $db->update('email_queue',
                    ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')],
                    'id = ?',
                    [$email['id']]
                );
                $sent++;
            } else {
                // Increment attempts
                $attempts = $email['attempts'] + 1;
                $status = $attempts >= 3 ? 'failed' : 'pending';

                $db->update('email_queue',
                    ['status' => $status, 'attempts' => $attempts],
                    'id = ?',
                    [$email['id']]
                );
                $failed++;
            }

            // Small delay to avoid overwhelming mail server
            usleep(100000); // 0.1 second
        }

        return [
            'processed' => count($emails),
            'sent' => $sent,
            'failed' => $failed
        ];
    }
}
