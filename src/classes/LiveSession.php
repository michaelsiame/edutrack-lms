<?php
/**
 * LiveSession Class
 * Handles live session management with Jitsi Meet integration
 */

class LiveSession {
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
     * Load live session data
     */
    private function load() {
        $sql = "SELECT ls.*,
                l.title as lesson_title, l.module_id,
                m.course_id,
                c.title as course_title,
                u.name as instructor_name
                FROM live_sessions ls
                JOIN lessons l ON ls.lesson_id = l.id
                JOIN modules m ON l.module_id = m.id
                JOIN courses c ON m.course_id = c.id
                JOIN instructors i ON ls.instructor_id = i.id
                JOIN users u ON i.user_id = u.id
                WHERE ls.id = :id";

        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
    }

    /**
     * Check if session exists
     */
    public function exists() {
        return !empty($this->data);
    }

    /**
     * Find session by ID
     */
    public static function find($id) {
        $session = new self($id);
        return $session->exists() ? $session : null;
    }

    /**
     * Get session by lesson ID
     */
    public static function getByLesson($lessonId) {
        $db = Database::getInstance();
        $sql = "SELECT ls.*, u.name as instructor_name
                FROM live_sessions ls
                JOIN instructors i ON ls.instructor_id = i.id
                JOIN users u ON i.user_id = u.id
                WHERE ls.lesson_id = :lesson_id
                ORDER BY ls.scheduled_start_time DESC";
        return $db->query($sql, ['lesson_id' => $lessonId])->fetchAll();
    }

    /**
     * Get upcoming sessions for a course
     */
    public static function getUpcomingByCourse($courseId, $limit = null) {
        $db = Database::getInstance();
        $sql = "SELECT ls.*, l.title as lesson_title, u.name as instructor_name
                FROM live_sessions ls
                JOIN lessons l ON ls.lesson_id = l.id
                JOIN modules m ON l.module_id = m.id
                JOIN instructors i ON ls.instructor_id = i.id
                JOIN users u ON i.user_id = u.id
                WHERE m.course_id = :course_id
                AND ls.status IN ('scheduled', 'live')
                AND ls.scheduled_start_time >= NOW()
                ORDER BY ls.scheduled_start_time ASC";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        return $db->query($sql, ['course_id' => $courseId])->fetchAll();
    }

    /**
     * Get sessions by instructor
     */
    public static function getByInstructor($instructorId, $status = null) {
        $db = Database::getInstance();
        $sql = "SELECT ls.*, l.title as lesson_title, c.title as course_title
                FROM live_sessions ls
                JOIN lessons l ON ls.lesson_id = l.id
                JOIN modules m ON l.module_id = m.id
                JOIN courses c ON m.course_id = c.id
                WHERE ls.instructor_id = :instructor_id";

        $params = ['instructor_id' => $instructorId];

        if ($status) {
            $sql .= " AND ls.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY ls.scheduled_start_time DESC";

        return $db->query($sql, $params)->fetchAll();
    }

    /**
     * Create new live session
     */
    public static function create($data) {
        $db = Database::getInstance();

        // Generate unique meeting room ID
        $roomId = self::generateRoomId($data['lesson_id']);

        // Calculate end time from duration
        $startTime = new DateTime($data['scheduled_start_time']);
        $duration = (int)($data['duration_minutes'] ?? 60);
        $endTime = clone $startTime;
        $endTime->modify("+{$duration} minutes");

        $sql = "INSERT INTO live_sessions (
            lesson_id, instructor_id, meeting_room_id,
            scheduled_start_time, scheduled_end_time, duration_minutes,
            description, max_participants, allow_recording,
            auto_start_recording, enable_chat, enable_screen_share,
            buffer_minutes_before, buffer_minutes_after, status
        ) VALUES (
            :lesson_id, :instructor_id, :meeting_room_id,
            :scheduled_start_time, :scheduled_end_time, :duration_minutes,
            :description, :max_participants, :allow_recording,
            :auto_start_recording, :enable_chat, :enable_screen_share,
            :buffer_minutes_before, :buffer_minutes_after, :status
        )";

        $params = [
            'lesson_id' => $data['lesson_id'],
            'instructor_id' => $data['instructor_id'],
            'meeting_room_id' => $roomId,
            'scheduled_start_time' => $startTime->format('Y-m-d H:i:s'),
            'scheduled_end_time' => $endTime->format('Y-m-d H:i:s'),
            'duration_minutes' => $duration,
            'description' => $data['description'] ?? '',
            'max_participants' => $data['max_participants'] ?? null,
            'allow_recording' => $data['allow_recording'] ?? 1,
            'auto_start_recording' => $data['auto_start_recording'] ?? 0,
            'enable_chat' => $data['enable_chat'] ?? 1,
            'enable_screen_share' => $data['enable_screen_share'] ?? 1,
            'buffer_minutes_before' => $data['buffer_minutes_before'] ?? 15,
            'buffer_minutes_after' => $data['buffer_minutes_after'] ?? 30,
            'status' => 'scheduled'
        ];

        $db->query($sql, $params);
        return $db->lastInsertId();
    }

    /**
     * Update session
     */
    public function update($data) {
        $updates = [];
        $params = ['id' => $this->id];

        $allowedFields = [
            'scheduled_start_time', 'duration_minutes', 'description',
            'max_participants', 'allow_recording', 'auto_start_recording',
            'enable_chat', 'enable_screen_share', 'buffer_minutes_before',
            'buffer_minutes_after', 'status', 'recording_url'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        // Recalculate end time if start time or duration changed
        if (isset($data['scheduled_start_time']) || isset($data['duration_minutes'])) {
            $startTime = isset($data['scheduled_start_time'])
                ? new DateTime($data['scheduled_start_time'])
                : new DateTime($this->data['scheduled_start_time']);

            $duration = $data['duration_minutes'] ?? $this->data['duration_minutes'];
            $endTime = clone $startTime;
            $endTime->modify("+{$duration} minutes");

            $updates[] = "scheduled_end_time = :scheduled_end_time";
            $params['scheduled_end_time'] = $endTime->format('Y-m-d H:i:s');
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE live_sessions SET " . implode(', ', $updates) . " WHERE id = :id";
        $this->db->query($sql, $params);
        $this->load(); // Reload data

        return true;
    }

    /**
     * Delete session
     */
    public function delete() {
        $sql = "DELETE FROM live_sessions WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }

    /**
     * Generate unique room ID for Jitsi
     */
    private static function generateRoomId($lessonId) {
        $prefix = 'edutrack';
        $timestamp = time();
        $random = substr(md5(uniqid(rand(), true)), 0, 8);
        return "{$prefix}_lesson{$lessonId}_{$timestamp}_{$random}";
    }

    /**
     * Get Jitsi meeting URL
     */
    public function getMeetingUrl() {
        $config = require __DIR__ . '/../../config/app.php';
        $domain = $config['jitsi']['domain'] ?? 'meet.jit.si';
        return "https://{$domain}/{$this->data['meeting_room_id']}";
    }

    /**
     * Check if session is currently live (within time window)
     */
    public function isLive() {
        $now = new DateTime();
        $start = new DateTime($this->data['scheduled_start_time']);
        $end = new DateTime($this->data['scheduled_end_time']);

        // Apply buffer times
        $bufferBefore = (int)($this->data['buffer_minutes_before'] ?? 15);
        $bufferAfter = (int)($this->data['buffer_minutes_after'] ?? 30);

        $start->modify("-{$bufferBefore} minutes");
        $end->modify("+{$bufferAfter} minutes");

        return $now >= $start && $now <= $end;
    }

    /**
     * Check if user can join session
     */
    public function canJoin($userId) {
        // Instructors can always join
        if ($this->data['instructor_id'] == $userId) {
            return true;
        }

        // Check if student is enrolled in the course
        $sql = "SELECT COUNT(*) as count
                FROM enrollments e
                JOIN modules m ON m.course_id = e.course_id
                JOIN lessons l ON l.module_id = m.id
                WHERE l.id = :lesson_id
                AND e.user_id = :user_id
                AND e.status = 'enrolled'";

        $result = $this->db->query($sql, [
            'lesson_id' => $this->data['lesson_id'],
            'user_id' => $userId
        ])->fetch();

        return $result['count'] > 0;
    }

    /**
     * Record user attendance
     */
    public function recordAttendance($userId, $isModerator = false) {
        $sql = "INSERT INTO live_session_attendance (
            live_session_id, user_id, joined_at, is_moderator
        ) VALUES (
            :session_id, :user_id, NOW(), :is_moderator
        )";

        $this->db->query($sql, [
            'session_id' => $this->id,
            'user_id' => $userId,
            'is_moderator' => $isModerator ? 1 : 0
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Update attendance when user leaves
     */
    public function updateAttendanceExit($attendanceId) {
        $sql = "UPDATE live_session_attendance
                SET left_at = NOW(),
                    duration_seconds = TIMESTAMPDIFF(SECOND, joined_at, NOW())
                WHERE id = :id";

        return $this->db->query($sql, ['id' => $attendanceId]);
    }

    /**
     * Get attendance records for session
     */
    public function getAttendance() {
        $sql = "SELECT a.*, u.name, u.email
                FROM live_session_attendance a
                JOIN users u ON a.user_id = u.id
                WHERE a.live_session_id = :session_id
                ORDER BY a.joined_at ASC";

        return $this->db->query($sql, ['session_id' => $this->id])->fetchAll();
    }

    /**
     * Get attendance count
     */
    public function getAttendanceCount() {
        $sql = "SELECT COUNT(DISTINCT user_id) as count
                FROM live_session_attendance
                WHERE live_session_id = :session_id";

        $result = $this->db->query($sql, ['session_id' => $this->id])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Update session status based on current time
     */
    public function updateStatus() {
        $now = new DateTime();
        $start = new DateTime($this->data['scheduled_start_time']);
        $end = new DateTime($this->data['scheduled_end_time']);

        $bufferAfter = (int)($this->data['buffer_minutes_after'] ?? 30);
        $end->modify("+{$bufferAfter} minutes");

        $newStatus = $this->data['status'];

        if ($now < $start && $this->data['status'] !== 'cancelled') {
            $newStatus = 'scheduled';
        } elseif ($now >= $start && $now <= $end && $this->data['status'] !== 'cancelled') {
            $newStatus = 'live';
        } elseif ($now > $end && $this->data['status'] !== 'cancelled') {
            $newStatus = 'ended';
        }

        if ($newStatus !== $this->data['status']) {
            $this->update(['status' => $newStatus]);
        }

        return $newStatus;
    }

    /**
     * Get upcoming sessions that need notifications
     */
    public static function getSessionsNeedingNotification($minutesBefore = 30) {
        $db = Database::getInstance();
        $sql = "SELECT ls.*, l.title as lesson_title, c.id as course_id, c.title as course_title
                FROM live_sessions ls
                JOIN lessons l ON ls.lesson_id = l.id
                JOIN modules m ON l.module_id = m.id
                JOIN courses c ON m.course_id = c.id
                WHERE ls.status = 'scheduled'
                AND ls.scheduled_start_time > NOW()
                AND ls.scheduled_start_time <= DATE_ADD(NOW(), INTERVAL :minutes MINUTE)";

        return $db->query($sql, ['minutes' => $minutesBefore])->fetchAll();
    }

    /**
     * Magic getter for data properties
     */
    public function __get($name) {
        return $this->data[$name] ?? null;
    }

    /**
     * Get all data as array
     */
    public function toArray() {
        return $this->data;
    }
}
