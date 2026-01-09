<?php
/**
 * Live Session Reminders - Cron Job Script
 *
 * This script sends notifications for upcoming live sessions:
 * - 30 minutes before: Reminder notification
 * - 5 minutes before: "Starting soon" notification
 * - At start time: "Starting now" notification
 *
**/

// Prevent web access without secret key
if (php_sapi_name() !== 'cli') {
    $secretKey = getenv('CRON_SECRET_KEY') ?: 'change_this_secret';
    if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
        http_response_code(403);
        die('Access denied.');
    }
}

$startTime = microtime(true);

// Bootstrap the application
require_once dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/src/classes/Notification.php';

// Log setup
$logFile = dirname(__DIR__) . '/storage/logs/cron-session-reminders.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
}

logMessage("=== Session Reminders Cron Started ===");

try {
    $db = Database::getInstance();
    $now = date('Y-m-d H:i:s');
    $in30Minutes = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    $in5Minutes = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $in1Minute = date('Y-m-d H:i:s', strtotime('+1 minute'));

    $stats = [
        '30min_reminders' => 0,
        '5min_reminders' => 0,
        'starting_now' => 0,
        'status_updates' => 0
    ];

    // ==========================================
    // 1. Send 30-minute reminders
    // ==========================================
    $sessions30Min = $db->fetchAll("
        SELECT
            ls.id,
            ls.scheduled_start_time,
            ls.reminder_30_sent,
            l.title as lesson_title,
            m.course_id
        FROM live_sessions ls
        JOIN lessons l ON ls.lesson_id = l.id
        JOIN modules m ON l.module_id = m.id
        WHERE ls.status = 'scheduled'
          AND ls.scheduled_start_time BETWEEN ? AND ?
          AND (ls.reminder_30_sent IS NULL OR ls.reminder_30_sent = 0)
    ", [$now, $in30Minutes]);

    foreach ($sessions30Min as $session) {
        // Get enrolled students
        $students = $db->fetchAll("
            SELECT user_id FROM enrollments
            WHERE course_id = ? AND status = 'enrolled'
        ", [$session['course_id']]);

        foreach ($students as $student) {
            Notification::notifyLiveSessionReminder(
                $student['user_id'],
                $session['lesson_title'],
                $session['scheduled_start_time'],
                $session['id']
            );
            $stats['30min_reminders']++;
        }

        // Mark reminder as sent
        $db->query("UPDATE live_sessions SET reminder_30_sent = 1 WHERE id = ?", [$session['id']]);
    }

    // ==========================================
    // 2. Send 5-minute reminders
    // ==========================================
    $sessions5Min = $db->fetchAll("
        SELECT
            ls.id,
            ls.scheduled_start_time,
            ls.reminder_5_sent,
            l.title as lesson_title,
            m.course_id
        FROM live_sessions ls
        JOIN lessons l ON ls.lesson_id = l.id
        JOIN modules m ON l.module_id = m.id
        WHERE ls.status = 'scheduled'
          AND ls.scheduled_start_time BETWEEN ? AND ?
          AND (ls.reminder_5_sent IS NULL OR ls.reminder_5_sent = 0)
    ", [$now, $in5Minutes]);

    foreach ($sessions5Min as $session) {
        $students = $db->fetchAll("
            SELECT user_id FROM enrollments
            WHERE course_id = ? AND status = 'enrolled'
        ", [$session['course_id']]);

        foreach ($students as $student) {
            Notification::create([
                'user_id' => $student['user_id'],
                'type' => 'live_session_reminder',
                'title' => 'Live Session in 5 Minutes!',
                'message' => "'{$session['lesson_title']}' is starting in 5 minutes. Get ready!",
                'link' => 'live-session.php?session_id=' . $session['id'],
                'icon' => 'fa-clock',
                'color' => 'orange'
            ]);
            $stats['5min_reminders']++;
        }

        $db->query("UPDATE live_sessions SET reminder_5_sent = 1 WHERE id = ?", [$session['id']]);
    }

    // ==========================================
    // 3. Send "Starting Now" notifications
    // ==========================================
    $sessionsStartingNow = $db->fetchAll("
        SELECT
            ls.id,
            ls.scheduled_start_time,
            ls.start_notification_sent,
            l.title as lesson_title,
            m.course_id
        FROM live_sessions ls
        JOIN lessons l ON ls.lesson_id = l.id
        JOIN modules m ON l.module_id = m.id
        WHERE ls.status = 'scheduled'
          AND ls.scheduled_start_time <= ?
          AND (ls.start_notification_sent IS NULL OR ls.start_notification_sent = 0)
    ", [$in1Minute]);

    foreach ($sessionsStartingNow as $session) {
        $students = $db->fetchAll("
            SELECT user_id FROM enrollments
            WHERE course_id = ? AND status = 'enrolled'
        ", [$session['course_id']]);

        foreach ($students as $student) {
            Notification::notifyLiveSessionStarting(
                $student['user_id'],
                $session['lesson_title'],
                $session['id']
            );
            $stats['starting_now']++;
        }

        $db->query("UPDATE live_sessions SET start_notification_sent = 1 WHERE id = ?", [$session['id']]);
    }

    // ==========================================
    // 4. Update session statuses
    // ==========================================
    // Mark sessions as 'in_progress' if start time has passed
    $updated = $db->query("
        UPDATE live_sessions
        SET status = 'in_progress', actual_start_time = NOW()
        WHERE status = 'scheduled'
          AND scheduled_start_time <= ?
    ", [$now]);
    $stats['status_updates'] += $db->rowCount();

    // Mark sessions as 'completed' if they've been running longer than their duration + buffer
    $db->query("
        UPDATE live_sessions
        SET status = 'completed', actual_end_time = NOW()
        WHERE status = 'in_progress'
          AND scheduled_start_time <= DATE_SUB(NOW(), INTERVAL (duration_minutes + COALESCE(buffer_minutes_after, 30)) MINUTE)
    ");
    $stats['status_updates'] += $db->rowCount();

    // ==========================================
    // Clean up old notifications (optional)
    // ==========================================
    Notification::deleteOld(90);

    // Log results
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    $summary = sprintf(
        "30min: %d, 5min: %d, Starting: %d, Status updates: %d, Duration: %ss",
        $stats['30min_reminders'],
        $stats['5min_reminders'],
        $stats['starting_now'],
        $stats['status_updates'],
        $duration
    );

    logMessage($summary);
    echo $summary . "\n";

    logMessage("=== Session Reminders Cron Completed ===");
    exit(0);

} catch (Exception $e) {
    $error = "ERROR: " . $e->getMessage();
    logMessage($error);
    echo $error . "\n";
    exit(1);
}
