<?php
/**
 * Notification Class
 * Handles in-app notifications for users
 */

class Notification {
    private $db;
    private $id;
    private $data = [];

    public function __construct($id = null) {
        $this->db = Database::getInstance();
        if ($id) {
            $this->id = $id;
            $this->load();
        }
    }

    /**
     * Load notification data
     */
    private function load() {
        $sql = "SELECT * FROM notifications WHERE id = ?";
        $this->data = $this->db->fetchOne($sql, [$this->id]);
    }

    /**
     * Check if notification exists
     */
    public function exists() {
        return !empty($this->data);
    }

    /**
     * Find notification by ID
     */
    public static function find($id) {
        $notification = new self($id);
        return $notification->exists() ? $notification : null;
    }

    /**
     * Get user notifications
     */
    public static function getByUser($userId, $options = []) {
        $db = Database::getInstance();

        $limit = $options['limit'] ?? 20;
        $offset = $options['offset'] ?? 0;
        $unreadOnly = $options['unread_only'] ?? false;

        $where = ['user_id = ?'];
        $params = [$userId];

        if ($unreadOnly) {
            $where[] = 'is_read = 0';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT * FROM notifications
                WHERE $whereClause
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        return $db->fetchAll($sql, $params);
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount($userId) {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0";
        return $db->fetchColumn($sql, [$userId]) ?: 0;
    }

    /**
     * Create notification
     */
    public static function create($data) {
        $db = Database::getInstance();

        $sql = "INSERT INTO notifications (
            user_id, type, title, message, link,
            icon, color, created_at
        ) VALUES (
            :user_id, :type, :title, :message, :link,
            :icon, :color, NOW()
        )";

        $params = [
            'user_id' => $data['user_id'],
            'type' => $data['type'] ?? 'info',
            'title' => $data['title'],
            'message' => $data['message'],
            'link' => $data['link'] ?? null,
            'icon' => $data['icon'] ?? self::getDefaultIcon($data['type'] ?? 'info'),
            'color' => $data['color'] ?? self::getDefaultColor($data['type'] ?? 'info')
        ];

        if ($db->query($sql, $params)) {
            $notificationId = $db->lastInsertId();

            // Log activity
            // FIXED: Adapted to match function logActivity($message, $level = 'info')
            if (function_exists('logActivity')) {
                $logMessage = "Notification created for User ID {$data['user_id']}: {$data['title']}";
                logActivity($logMessage, 'info');
            }

            return $notificationId;
        }

        return false;
    }

    /**
     * Create notification for multiple users
     */
    public static function createBulk($userIds, $data) {
        $ids = [];
        foreach ($userIds as $userId) {
            $data['user_id'] = $userId;
            $id = self::create($data);
            if ($id) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    /**
     * Mark as read
     */
    public function markAsRead() {
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?";

        if ($this->db->query($sql, [$this->id])) {
            $this->load();
            return true;
        }
        return false;
    }

    /**
     * Mark all as read for user
     */
    public static function markAllAsRead($userId) {
        $db = Database::getInstance();
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW()
                WHERE user_id = ? AND is_read = 0";

        return $db->query($sql, [$userId]);
    }

    /**
     * Delete notification
     */
    public function delete() {
        $sql = "DELETE FROM notifications WHERE id = ?";
        return $this->db->query($sql, [$this->id]);
    }

    /**
     * Delete old notifications
     */
    public static function deleteOld($days = 90) {
        $db = Database::getInstance();
        $sql = "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $db->query($sql, [$days]);
    }

    /**
     * Get default icon for notification type
     */
    private static function getDefaultIcon($type) {
        $icons = [
            'success' => 'fa-check-circle',
            'error' => 'fa-exclamation-circle',
            'warning' => 'fa-exclamation-triangle',
            'info' => 'fa-info-circle',
            'enrollment' => 'fa-book',
            'payment' => 'fa-credit-card',
            'certificate' => 'fa-certificate',
            'assignment' => 'fa-file-alt',
            'quiz' => 'fa-question-circle',
            'message' => 'fa-envelope',
            'announcement' => 'fa-bullhorn'
        ];

        return $icons[$type] ?? 'fa-bell';
    }

    /**
     * Get default color for notification type
     */
    private static function getDefaultColor($type) {
        $colors = [
            'success' => 'green',
            'error' => 'red',
            'warning' => 'orange',
            'info' => 'blue',
            'enrollment' => 'blue',
            'payment' => 'green',
            'certificate' => 'yellow',
            'assignment' => 'purple',
            'quiz' => 'indigo',
            'message' => 'gray',
            'announcement' => 'primary'
        ];

        return $colors[$type] ?? 'gray';
    }

    /**
     * Notification helper - Enrollment
     */
    public static function notifyEnrollment($userId, $courseTitle) {
        return self::create([
            'user_id' => $userId,
            'type' => 'enrollment',
            'title' => 'Course Enrollment',
            'message' => "You've successfully enrolled in: $courseTitle",
            'link' => 'my-courses.php',
            'icon' => 'fa-book',
            'color' => 'blue'
        ]);
    }

    /**
     * Notification helper - Payment Confirmed
     */
    public static function notifyPaymentConfirmed($userId, $courseTitle, $amount) {
        return self::create([
            'user_id' => $userId,
            'type' => 'payment',
            'title' => 'Payment Confirmed',
            'message' => "Your payment of " . formatCurrency($amount) . " for $courseTitle has been confirmed",
            'link' => 'my-courses.php',
            'icon' => 'fa-check-circle',
            'color' => 'green'
        ]);
    }

    /**
     * Notification helper - Certificate Issued
     */
    public static function notifyCertificateIssued($userId, $courseTitle, $certificateId) {
        return self::create([
            'user_id' => $userId,
            'type' => 'certificate',
            'title' => 'Certificate Issued',
            'message' => "Your TEVETA certificate for $courseTitle is ready for download!",
            'link' => 'download-certificate.php?id=' . $certificateId,
            'icon' => 'fa-certificate',
            'color' => 'yellow'
        ]);
    }

    /**
     * Notification helper - Assignment Graded
     */
    public static function notifyAssignmentGraded($userId, $assignmentTitle, $score, $submissionId) {
        return self::create([
            'user_id' => $userId,
            'type' => 'assignment',
            'title' => 'Assignment Graded',
            'message' => "Your assignment '$assignmentTitle' has been graded. Score: $score",
            'link' => 'assignment-result.php?id=' . $submissionId,
            'icon' => 'fa-file-alt',
            'color' => 'purple'
        ]);
    }

    /**
     * Notification helper - New Assignment
     */
    public static function notifyNewAssignment($userId, $courseTitle, $assignmentTitle) {
        return self::create([
            'user_id' => $userId,
            'type' => 'assignment',
            'title' => 'New Assignment',
            'message' => "New assignment posted in $courseTitle: $assignmentTitle",
            'link' => 'my-courses.php',
            'icon' => 'fa-plus-circle',
            'color' => 'blue'
        ]);
    }

    /**
     * Notification helper - Course Completed
     */
    public static function notifyCourseCompleted($userId, $courseTitle) {
        return self::create([
            'user_id' => $userId,
            'type' => 'success',
            'title' => 'Course Completed',
            'message' => "Congratulations! You've completed: $courseTitle",
            'link' => 'my-certificates.php',
            'icon' => 'fa-trophy',
            'color' => 'green'
        ]);
    }

    /**
     * Notification helper - Live Session Scheduled
     */
    public static function notifyLiveSessionScheduled($userId, $lessonTitle, $scheduledTime, $sessionId) {
        $formattedTime = date('M d, Y \a\t g:i A', strtotime($scheduledTime));
        return self::create([
            'user_id' => $userId,
            'type' => 'live_session',
            'title' => 'Live Session Scheduled',
            'message' => "A live session for '$lessonTitle' has been scheduled for $formattedTime",
            'link' => 'live-session.php?session_id=' . $sessionId,
            'icon' => 'fa-video',
            'color' => 'blue'
        ]);
    }

    /**
     * Notification helper - Live Session Reminder (30 min before)
     */
    public static function notifyLiveSessionReminder($userId, $lessonTitle, $scheduledTime, $sessionId) {
        $formattedTime = date('g:i A', strtotime($scheduledTime));
        return self::create([
            'user_id' => $userId,
            'type' => 'live_session_reminder',
            'title' => 'Live Session Starting Soon',
            'message' => "Reminder: '$lessonTitle' live session starts at $formattedTime (30 minutes from now)",
            'link' => 'live-session.php?session_id=' . $sessionId,
            'icon' => 'fa-clock',
            'color' => 'orange'
        ]);
    }

    /**
     * Notification helper - Live Session Starting Now
     */
    public static function notifyLiveSessionStarting($userId, $lessonTitle, $sessionId) {
        return self::create([
            'user_id' => $userId,
            'type' => 'live_session_starting',
            'title' => 'Live Session Starting Now!',
            'message' => "The live session for '$lessonTitle' is starting now. Join now!",
            'link' => 'live-session.php?session_id=' . $sessionId,
            'icon' => 'fa-play-circle',
            'color' => 'green'
        ]);
    }

    /**
     * Notification helper - Live Session Cancelled
     */
    public static function notifyLiveSessionCancelled($userId, $lessonTitle, $scheduledTime) {
        $formattedTime = date('M d, Y \a\t g:i A', strtotime($scheduledTime));
        return self::create([
            'user_id' => $userId,
            'type' => 'live_session_cancelled',
            'title' => 'Live Session Cancelled',
            'message' => "The live session for '$lessonTitle' scheduled for $formattedTime has been cancelled",
            'link' => 'my-courses.php',
            'icon' => 'fa-times-circle',
            'color' => 'red'
        ]);
    }

    /**
     * Notification helper - Live Session Rescheduled
     */
    public static function notifyLiveSessionRescheduled($userId, $lessonTitle, $oldTime, $newTime, $sessionId) {
        $formattedNewTime = date('M d, Y \a\t g:i A', strtotime($newTime));
        return self::create([
            'user_id' => $userId,
            'type' => 'live_session_rescheduled',
            'title' => 'Live Session Rescheduled',
            'message' => "The live session for '$lessonTitle' has been rescheduled to $formattedNewTime",
            'link' => 'live-session.php?session_id=' . $sessionId,
            'icon' => 'fa-calendar-alt',
            'color' => 'blue'
        ]);
    }

    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getUserId() { return $this->data['user_id'] ?? null; }
    public function getType() { return $this->data['type'] ?? 'info'; }
    public function getTitle() { return $this->data['title'] ?? ''; }
    public function getMessage() { return $this->data['message'] ?? ''; }
    public function getLink() { return $this->data['link'] ?? null; }
    public function getIcon() { return $this->data['icon'] ?? 'fa-bell'; }
    public function getColor() { return $this->data['color'] ?? 'gray'; }
    public function isRead() { return ($this->data['is_read'] ?? 0) == 1; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getReadAt() { return $this->data['read_at'] ?? null; }

    /**
     * Get time ago
     */
    public function getTimeAgo() {
        return timeAgo($this->getCreatedAt());
    }
}