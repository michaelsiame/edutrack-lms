<?php
/**
 * Email Helper Functions
 * Convenience functions for sending emails
 */

require_once __DIR__ . '/../classes/Email.php';

/**
 * Send email using default mailer
 */
function sendEmail($to, $subject, $body, $attachments = []) {
    try {
        $email = new Email();
        return $email->send($to, $subject, $body, $attachments);
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send welcome email
 */
function sendWelcomeEmail($user) {
    try {
        $email = new Email();
        return $email->sendWelcome($user);
    } catch (Exception $e) {
        error_log("Welcome email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send enrollment confirmation email
 */
function sendEnrollmentEmail($user, $course) {
    try {
        $email = new Email();
        return $email->sendEnrollmentConfirmation($user, $course);
    } catch (Exception $e) {
        error_log("Enrollment email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send payment confirmation email
 */
function sendPaymentEmail($user, $payment) {
    try {
        $email = new Email();
        return $email->sendPaymentConfirmation($user, $payment);
    } catch (Exception $e) {
        error_log("Payment email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send certificate issued email
 */
function sendCertificateEmail($user, $certificate) {
    try {
        $email = new Email();
        return $email->sendCertificateIssued($user, $certificate);
    } catch (Exception $e) {
        error_log("Certificate email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($user, $resetToken) {
    try {
        $email = new Email();
        return $email->sendPasswordReset($user, $resetToken);
    } catch (Exception $e) {
        error_log("Password reset email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send assignment graded notification email
 */
function sendAssignmentGradedEmail($user, $submission) {
    try {
        $email = new Email();
        return $email->sendAssignmentGraded($user, $submission);
    } catch (Exception $e) {
        error_log("Assignment graded email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send course completion email
 */
function sendCourseCompletedEmail($user, $course) {
    try {
        $email = new Email();
        return $email->sendCourseCompleted($user, $course);
    } catch (Exception $e) {
        error_log("Course completion email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if email sending is enabled
 */
function isEmailEnabled() {
    $mailConfig = require __DIR__ . '/../../config/mail.php';
    return !($mailConfig['disable_delivery'] ?? false);
}

/**
 * Queue email for later sending (basic implementation)
 */
function queueEmail($to, $subject, $body, $attachments = []) {
    $db = Database::getInstance();

    $sql = "INSERT INTO email_queue (recipient, subject, body, attachments, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())";

    return $db->query($sql, [
        $to,
        $subject,
        $body,
        json_encode($attachments)
    ]);
}

/**
 * Process email queue (should be called via cron)
 */
function processEmailQueue($limit = 50) {
    $db = Database::getInstance();

    $emails = $db->fetchAll(
        "SELECT * FROM email_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT ?",
        [$limit]
    );

    $processed = 0;
    $failed = 0;

    foreach ($emails as $emailData) {
        $attachments = json_decode($emailData['attachments'], true) ?: [];

        if (sendEmail($emailData['recipient'], $emailData['subject'], $emailData['body'], $attachments)) {
            $db->query(
                "UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?",
                [$emailData['id']]
            );
            $processed++;
        } else {
            $db->query(
                "UPDATE email_queue SET status = 'failed', attempts = attempts + 1 WHERE id = ?",
                [$emailData['id']]
            );
            $failed++;
        }
    }

    return [
        'processed' => $processed,
        'failed' => $failed
    ];
}
