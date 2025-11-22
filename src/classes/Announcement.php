<?php
/**
 * Announcement Class
 * Manages system-wide and course-specific announcements
 *
 * Database schema (announcements table):
 * - announcement_id (PK)
 * - course_id (FK to courses)
 * - title
 * - content
 * - posted_by (FK to users)
 * - created_at
 */

class Announcement {
    private $db;
    private $id;
    private $courseId;
    private $postedBy;
    private $title;
    private $content;
    private $createdAt;
    private $creatorName;
    private $courseTitle;

    public function __construct($db = null) {
        if ($db === null) {
            $this->db = Database::getInstance();
        } else {
            $this->db = $db;
        }
    }

    /**
     * Create a new announcement
     */
    public static function create($data) {
        $db = Database::getInstance();

        // Validate required fields
        $postedBy = $data['posted_by'] ?? ($data['created_by'] ?? null);
        if (empty($data['title']) || empty($data['content']) || empty($postedBy)) {
            throw new Exception('Title, content, and creator are required');
        }

        $sql = "INSERT INTO announcements (
            course_id, posted_by, title, content
        ) VALUES (?, ?, ?, ?)";

        $params = [
            $data['course_id'] ?? null,
            $postedBy,
            $data['title'],
            $data['content']
        ];

        $result = $db->query($sql, $params);

        if ($result) {
            $announcementId = $db->lastInsertId();
            return self::find($announcementId);
        }

        return false;
    }

    /**
     * Find announcement by ID
     */
    public static function find($id) {
        $db = Database::getInstance();

        $sql = "SELECT a.announcement_id, a.course_id, a.title, a.content,
                       a.posted_by, a.created_at,
                       CONCAT(u.first_name, ' ', u.last_name) as creator_name,
                       c.title as course_title
                FROM announcements a
                JOIN users u ON a.posted_by = u.id
                LEFT JOIN courses c ON a.course_id = c.id
                WHERE a.announcement_id = ?";

        $data = $db->fetchOne($sql, [$id]);

        if ($data) {
            $announcement = new self();
            $announcement->hydrate($data);
            return $announcement;
        }

        return null;
    }

    /**
     * Get all announcements with filters
     */
    public static function getAll($filters = []) {
        $db = Database::getInstance();

        $sql = "SELECT a.announcement_id, a.course_id, a.title, a.content,
                       a.posted_by, a.created_at,
                       CONCAT(u.first_name, ' ', u.last_name) as creator_name,
                       c.title as course_title
                FROM announcements a
                JOIN users u ON a.posted_by = u.id
                LEFT JOIN courses c ON a.course_id = c.id
                WHERE 1=1";

        $params = [];

        // Filter by course
        if (isset($filters['course_id'])) {
            if ($filters['course_id'] === 'global') {
                $sql .= " AND a.course_id IS NULL";
            } else {
                $sql .= " AND (a.course_id = ? OR a.course_id IS NULL)";
                $params[] = $filters['course_id'];
            }
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';

        // Map order_by to actual column names
        if ($orderBy === 'id') {
            $orderBy = 'announcement_id';
        }
        $sql .= " ORDER BY a.{$orderBy} {$orderDir}";

        // Limit
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (isset($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }

        $results = $db->fetchAll($sql, $params);

        $announcements = [];
        foreach ($results as $data) {
            $announcement = new self();
            $announcement->hydrate($data);
            $announcements[] = $announcement;
        }

        return $announcements;
    }

    /**
     * Get active announcements for a user
     */
    public static function getActiveForUser($userId, $role = null, $courseId = null) {
        $db = Database::getInstance();

        $sql = "SELECT a.announcement_id, a.course_id, a.title, a.content,
                       a.posted_by, a.created_at,
                       CONCAT(u.first_name, ' ', u.last_name) as creator_name,
                       c.title as course_title
                FROM announcements a
                JOIN users u ON a.posted_by = u.id
                LEFT JOIN courses c ON a.course_id = c.id
                WHERE 1=1";

        $params = [];

        // Filter by course or global
        if ($courseId) {
            $sql .= " AND (a.course_id = ? OR a.course_id IS NULL)";
            $params[] = $courseId;
        } else {
            $sql .= " AND a.course_id IS NULL";
        }

        $sql .= " ORDER BY a.created_at DESC";
        $sql .= " LIMIT 10";

        $results = $db->fetchAll($sql, $params);

        $announcements = [];
        foreach ($results as $data) {
            $announcement = new self();
            $announcement->hydrate($data);
            $announcements[] = $announcement;
        }

        return $announcements;
    }

    /**
     * Get announcements by instructor (for instructor's courses)
     */
    public static function getByInstructor($instructorId, $filters = []) {
        $db = Database::getInstance();

        $sql = "SELECT a.announcement_id, a.course_id, a.title, a.content,
                       a.posted_by, a.created_at,
                       CONCAT(u.first_name, ' ', u.last_name) as creator_name,
                       c.title as course_title
                FROM announcements a
                JOIN users u ON a.posted_by = u.id
                LEFT JOIN courses c ON a.course_id = c.id
                WHERE (a.posted_by = ? OR c.instructor_id = ?)";

        $params = [$instructorId, $instructorId];

        // Filter by course
        if (isset($filters['course_id']) && $filters['course_id']) {
            $sql .= " AND a.course_id = ?";
            $params[] = $filters['course_id'];
        }

        $sql .= " ORDER BY a.created_at DESC";

        // Limit
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $results = $db->fetchAll($sql, $params);

        $announcements = [];
        foreach ($results as $data) {
            $announcement = new self();
            $announcement->hydrate($data);
            $announcements[] = $announcement;
        }

        return $announcements;
    }

    /**
     * Update an announcement
     */
    public function update($data) {
        $fields = [];
        $params = [];

        if (isset($data['title'])) {
            $fields[] = "title = ?";
            $params[] = $data['title'];
        }

        if (isset($data['content'])) {
            $fields[] = "content = ?";
            $params[] = $data['content'];
        }

        if (isset($data['course_id'])) {
            $fields[] = "course_id = ?";
            $params[] = $data['course_id'];
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $this->id;

        $sql = "UPDATE announcements SET " . implode(', ', $fields) . " WHERE announcement_id = ?";

        return $this->db->query($sql, $params);
    }

    /**
     * Delete an announcement
     */
    public function delete() {
        return $this->db->query("DELETE FROM announcements WHERE announcement_id = ?", [$this->id]);
    }

    /**
     * Check if announcement is active (always true since we don't have expiry in this schema)
     */
    public function isActive() {
        return true;
    }

    /**
     * Hydrate object from database row
     */
    private function hydrate($data) {
        $this->id = $data['announcement_id'] ?? ($data['id'] ?? null);
        $this->courseId = $data['course_id'] ?? null;
        $this->postedBy = $data['posted_by'] ?? ($data['created_by'] ?? null);
        $this->title = $data['title'] ?? null;
        $this->content = $data['content'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;

        // Additional data from JOINs
        if (isset($data['creator_name'])) {
            $this->creatorName = $data['creator_name'];
        }
        if (isset($data['course_title'])) {
            $this->courseTitle = $data['course_title'];
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getCourseId() { return $this->courseId; }
    public function getPostedBy() { return $this->postedBy; }
    public function getCreatedBy() { return $this->postedBy; } // Alias for compatibility
    public function getTitle() { return $this->title; }
    public function getContent() { return $this->content; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getCreatorName() { return $this->creatorName ?? 'Unknown'; }
    public function getCourseTitle() { return $this->courseTitle ?? 'Global'; }
    public function isGlobal() { return $this->courseId === null; }

    // Compatibility methods for code expecting old schema
    public function getAnnouncementType() { return 'info'; }
    public function getTargetAudience() { return 'all'; }
    public function isPublished() { return true; }
    public function getPublishedAt() { return $this->createdAt; }
    public function getExpiresAt() { return null; }
    public function getUpdatedAt() { return $this->createdAt; }
    public function getTypeBadgeColor() { return 'blue'; }
    public function getTypeIcon() { return 'bell'; }
}
