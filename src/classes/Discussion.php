<?php
/**
 * Discussion Forum Class
 * Handles course discussion threads and replies
 */

class Discussion {
    private $db;
    private $data = [];

    public function __construct($id = null) {
        $this->db = Database::getInstance();
        if ($id !== null) {
            $this->load($id);
        }
    }

    private function load($id) {
        $sql = "SELECT d.*,
                CONCAT(u.first_name, ' ', u.last_name) as author_name,
                u.avatar_url as author_avatar
                FROM discussions d
                JOIN users u ON d.created_by = u.id
                WHERE d.discussion_id = ?";
        $result = $this->db->fetchOne($sql, [$id]);
        $this->data = $result ?: [];
    }

    public static function create($data) {
        $db = Database::getInstance();

        $required = ['course_id', 'created_by', 'title', 'content'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }

        $sql = "INSERT INTO discussions (course_id, created_by, title, content, is_pinned, is_locked, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $db->query($sql, [
            $data['course_id'],
            $data['created_by'],
            $data['title'],
            $data['content'],
            $data['is_pinned'] ?? 0,
            $data['is_locked'] ?? 0,
        ]);

        return $db->getConnection()->lastInsertId();
    }

    public static function getByCourse($courseId, $page = 1, $perPage = 20) {
        $db = Database::getInstance();
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT d.*,
                CONCAT(u.first_name, ' ', u.last_name) as author_name,
                u.avatar_url as author_avatar
                FROM discussions d
                JOIN users u ON d.created_by = u.id
                WHERE d.course_id = ?
                ORDER BY d.is_pinned DESC, d.updated_at DESC
                LIMIT ? OFFSET ?";

        return $db->fetchAll($sql, [$courseId, $perPage, $offset]);
    }

    public static function getCount($courseId) {
        $db = Database::getInstance();
        return (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM discussions WHERE course_id = ?",
            [$courseId]
        );
    }

    public function addReply($data) {
        $sql = "INSERT INTO discussion_replies (discussion_id, user_id, content, parent_reply_id, is_instructor_reply, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";

        $this->db->query($sql, [
            $this->data['discussion_id'],
            $data['user_id'],
            $data['content'],
            $data['parent_reply_id'] ?? null,
            $data['is_instructor_reply'] ?? 0,
        ]);

        // Update reply count and updated_at
        $this->db->query(
            "UPDATE discussions SET reply_count = reply_count + 1, updated_at = NOW() WHERE discussion_id = ?",
            [$this->data['discussion_id']]
        );

        return $this->db->getConnection()->lastInsertId();
    }

    public function getReplies($page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT dr.*,
                CONCAT(u.first_name, ' ', u.last_name) as author_name,
                u.avatar_url as author_avatar
                FROM discussion_replies dr
                JOIN users u ON dr.user_id = u.id
                WHERE dr.discussion_id = ?
                ORDER BY dr.is_best_answer DESC, dr.created_at ASC
                LIMIT ? OFFSET ?";

        return $this->db->fetchAll($sql, [$this->data['discussion_id'], $perPage, $offset]);
    }

    public function incrementViewCount() {
        $this->db->query(
            "UPDATE discussions SET view_count = view_count + 1 WHERE discussion_id = ?",
            [$this->data['discussion_id']]
        );
    }

    public function togglePin() {
        $newValue = ($this->data['is_pinned'] ?? 0) ? 0 : 1;
        $this->db->query(
            "UPDATE discussions SET is_pinned = ? WHERE discussion_id = ?",
            [$newValue, $this->data['discussion_id']]
        );
        $this->data['is_pinned'] = $newValue;
    }

    public function toggleLock() {
        $newValue = ($this->data['is_locked'] ?? 0) ? 0 : 1;
        $this->db->query(
            "UPDATE discussions SET is_locked = ? WHERE discussion_id = ?",
            [$newValue, $this->data['discussion_id']]
        );
        $this->data['is_locked'] = $newValue;
    }

    public function markBestAnswer($replyId) {
        // Unmark any existing best answer
        $this->db->query(
            "UPDATE discussion_replies SET is_best_answer = 0 WHERE discussion_id = ?",
            [$this->data['discussion_id']]
        );
        // Mark the new best answer
        $this->db->query(
            "UPDATE discussion_replies SET is_best_answer = 1 WHERE reply_id = ? AND discussion_id = ?",
            [$replyId, $this->data['discussion_id']]
        );
    }

    public function delete() {
        $this->db->query("DELETE FROM discussion_replies WHERE discussion_id = ?", [$this->data['discussion_id']]);
        $this->db->query("DELETE FROM discussions WHERE discussion_id = ?", [$this->data['discussion_id']]);
    }

    // Getters
    public function getId() { return $this->data['discussion_id'] ?? null; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getTitle() { return $this->data['title'] ?? ''; }
    public function getContent() { return $this->data['content'] ?? ''; }
    public function getAuthorName() { return $this->data['author_name'] ?? ''; }
    public function getAuthorAvatar() { return $this->data['author_avatar'] ?? null; }
    public function getViewCount() { return (int) ($this->data['view_count'] ?? 0); }
    public function getReplyCount() { return (int) ($this->data['reply_count'] ?? 0); }
    public function isPinned() { return (bool) ($this->data['is_pinned'] ?? false); }
    public function isLocked() { return (bool) ($this->data['is_locked'] ?? false); }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getUpdatedAt() { return $this->data['updated_at'] ?? null; }
    public function getData() { return $this->data; }
}
