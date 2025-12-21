<?php
// /**
//  * Email Queue Processor - Cron Job Script
//  *
//  * This script processes pending emails in the email_queue table
//  *
//  * Setup on Hostinger:
//  * 1. Go to hPanel → Advanced → Cron Jobs
//  * 2. Add new cron job:
//  *    - Common Settings: Every 5 minutes (or custom)
//  *    - Command: /usr/bin/php /home/yourusername/public_html/cron/process-emails.php
//  *    - Or: cd /home/yourusername/public_html && php cron/process-emails.php
//  *
//  * Schedule examples:
//  * - Every 5 minutes:  *//* * * * *
//  * - Every 10 minutes: */10 * * * *
//  * - Every hour:       0 * * * *
//  **/

// Prevent web access
if (php_sapi_name() !== 'cli') {
    // Allow web access only with secret key
    $secretKey = getenv('CRON_SECRET_KEY') ?: 'change_this_secret';
    if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
        http_response_code(403);
        die('Access denied. This script can only be run via cron job.');
    }
}

// Start timing
$startTime = microtime(true);

// Bootstrap the application
require_once dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/src/classes/EmailNotificationService.php';

// Log start
$logFile = dirname(__DIR__) . '/storage/logs/cron-email.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
}

logMessage("=== Email Queue Processor Started ===");

try {
    // Process up to 50 emails per run
    $result = EmailNotificationService::processQueue(50);

    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    $message = sprintf(
        "Processed: %d, Sent: %d, Failed: %d, Duration: %s seconds",
        $result['processed'],
        $result['sent'],
        $result['failed'],
        $duration
    );

    logMessage($message);

    // Output for cron job logs
    echo $message . "\n";

    // Clean up old sent emails (older than 30 days)
    $db = Database::getInstance();
    $deleted = $db->delete('email_queue',
        "status = 'sent' AND sent_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );

    if ($deleted > 0) {
        logMessage("Cleaned up {$deleted} old sent emails");
        echo "Cleaned up {$deleted} old sent emails\n";
    }

    logMessage("=== Email Queue Processor Completed ===");
    exit(0);

} catch (Exception $e) {
    $error = "ERROR: " . $e->getMessage();
    logMessage($error);
    echo $error . "\n";
    exit(1);
}
