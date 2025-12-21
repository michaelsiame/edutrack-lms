<?php
/**
 * Email Notification Hooks
 * Automatically triggers emails when certain actions occur
 *
 * Include this file in bootstrap.php to enable automatic emails
 */

require_once __DIR__ . '/../classes/EmailNotificationService.php';

/**
 * Send enrollment confirmation email
 * Call this after creating an enrollment
 */
function sendEnrollmentConfirmation($enrollmentId) {
    $service = new EmailNotificationService();
    return $service->sendEnrollmentEmail($enrollmentId);
}

/**
 * Send payment receipt email
 * Call this after processing a payment
 */
function sendPaymentReceipt($transactionId) {
    $service = new EmailNotificationService();
    return $service->sendPaymentReceipt($transactionId);
}

/**
 * Send certificate issued email
 * Call this after issuing a certificate
 */
function sendCertificateNotification($certificateId) {
    $service = new EmailNotificationService();
    return $service->sendCertificateEmail($certificateId);
}

/**
 * Send welcome email
 * Call this after user registration
 */
function sendWelcomeEmail($userId) {
    $service = new EmailNotificationService();
    return $service->sendWelcomeEmail($userId);
}
