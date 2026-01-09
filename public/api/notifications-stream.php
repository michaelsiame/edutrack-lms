<?php
/**
 * Server-Sent Events (SSE) Endpoint for Real-time Notifications
 *
 * This endpoint provides a persistent connection for real-time notifications
 * using Server-Sent Events (SSE).
 *
 * Usage:
 * const eventSource = new EventSource('/api/notifications-stream.php');
 * eventSource.onmessage = (event) => {
 *     const data = JSON.parse(event.data);
 *     // Handle notification
 * };
 */

// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable nginx buffering

// Prevent output buffering
while (ob_get_level()) {
    ob_end_flush();
}
ob_implicit_flush(true);

// Bootstrap the application
require_once '../../src/bootstrap.php';
require_once '../../src/classes/Notification.php';

// Require authentication
if (!isLoggedIn()) {
    echo "event: error\n";
    echo "data: " . json_encode(['error' => 'Unauthorized']) . "\n\n";
    flush();
    exit;
}

$userId = currentUserId();
$lastEventId = isset($_SERVER['HTTP_LAST_EVENT_ID']) ? (int)$_SERVER['HTTP_LAST_EVENT_ID'] : 0;

// Store the last notification ID we've seen
$lastNotificationId = $lastEventId;

// Send initial connection success message
echo "event: connected\n";
echo "data: " . json_encode([
    'message' => 'Connected to notification stream',
    'user_id' => $userId
]) . "\n\n";
flush();

// Main SSE loop
$maxRuntime = 300; // 5 minutes max runtime
$startTime = time();
$pollInterval = 3; // Check for new notifications every 3 seconds

while (true) {
    // Check if we've exceeded max runtime
    if ((time() - $startTime) > $maxRuntime) {
        echo "event: timeout\n";
        echo "data: " . json_encode(['message' => 'Connection timeout, please reconnect']) . "\n\n";
        flush();
        break;
    }

    // Check if client disconnected
    if (connection_aborted()) {
        break;
    }

    try {
        $db = Database::getInstance();

        // Get new notifications since last check
        $notifications = $db->fetchAll("
            SELECT 
                notification_id AS id, 
                notification_type AS type, 
                title, 
                message, 
                action_url AS link, 
                created_at
            FROM notifications
            WHERE user_id = ? AND notification_id > ?
            ORDER BY notification_id ASC
            LIMIT 10
        ", [$userId, $lastNotificationId]);

        if (!empty($notifications)) {
            foreach ($notifications as $notification) {
                // Send each notification as an event
                echo "id: {$notification['id']}\n";
                echo "event: notification\n";
                echo "data: " . json_encode([
                    'id' => $notification['id'],
                    'type' => $notification['type'],
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                    'link' => $notification['link'],
                    'icon' => $notification['icon'],
                    'color' => $notification['color'],
                    'time' => timeAgo($notification['created_at'])
                ]) . "\n\n";
                flush();

                $lastNotificationId = $notification['id'];
            }
        }

        // Get unread count
        $unreadCount = Notification::getUnreadCount($userId);
        echo "event: unread_count\n";
        echo "data: " . json_encode(['count' => $unreadCount]) . "\n\n";
        flush();

        // Check for upcoming live sessions (within next 5 minutes)
        $upcomingSessions = $db->fetchAll("
            SELECT
                ls.id,
                ls.scheduled_start_time,
                l.title as lesson_title,
                c.title as course_title
            FROM live_sessions ls
            JOIN lessons l ON ls.lesson_id = l.id
            JOIN modules m ON l.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            JOIN enrollments e ON e.course_id = c.id
            WHERE e.user_id = ?
              AND e.status = 'enrolled'
              AND ls.status IN ('scheduled', 'in_progress', 'live')
              AND ls.scheduled_start_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 5 MINUTE)
            LIMIT 5
        ", [$userId]);

        if (!empty($upcomingSessions)) {
            echo "event: upcoming_sessions\n";
            echo "data: " . json_encode([
                'sessions' => $upcomingSessions,
                'count' => count($upcomingSessions)
            ]) . "\n\n";
            flush();
        }

    } catch (Exception $e) {
        echo "event: error\n";
        echo "data: " . json_encode(['error' => 'Database error']) . "\n\n";
        flush();
        error_log("SSE Error: " . $e->getMessage());
    }

    // Send heartbeat/keep-alive
    echo ": heartbeat " . time() . "\n\n";
    flush();

    // Sleep before next check
    sleep($pollInterval);
}
