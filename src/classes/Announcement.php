<?php
/**
 * Announcement Class
 * Manages system-wide and course-specific announcements
 * Fixed to match actual database schema
 */

class Announcement {
    private $db;
    private $id;
    private $courseId;
    private $postedBy;
    private $title;
    private $content;
    private $announcementType;
    private $priority;
    private $isPublished;
    private $publishedAt;
    private $expiresAt;
    private $createdAt;
    private $updatedAt;
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
     * Uses correct column names: announcement_id, posted_by, priority
     */
    public static function create($data) {
        $db = Database::getInstance();

        // Validate required fields
        if (empty($data['title']) || empty($data['content']) || empty($data['posted_by'])) {
            throw new Exception('Title, content, and creator are required');
        }

        $sql = "INSERT INTO announcements (
            course_id, posted_by, title, content, announcement_type,
            priority, is_published, published_at, expires_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $isPublished = $data['is_published'] ?? true;
        $publishedAt = $isPublished ? ($data['published_at'] ?? date('Y-m-d H:i:s')) : null;

        $params = [
            $data['course_id'] ?? null,
            $data['posted_by'],
            $data['title'],
            $data['content'],
            $data['announcement_type'] ?? 'General',
            $data['priority'] ?? 'Normal',
            $isPublished ? 1 : 0,
            $publishedAt,
            $data['expires_at'] ?? null
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
     * Uses announcement_id and posted_by columns
     */
    public static function find($id) {
        $db = Database::getInstance();

        $sql = "SELECT a.*,
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

        $sql = "SELECT a.*,
                       CONCAT(u.first_name, ' ', u.last_name) as creator_name,
                       c.title as course_title
                FROM announcements a
                JOIN users u ON a.posted_by = u.id
                LEFT JOIN courses c ON a.course_id = c.id
                WHERE 1=1";

        $params = [];

        // Filter by published status
        if (isset($filters['published']) && $filters['published']) {
            $sql .= " AND a.is_published = 1";
            $sql .= " AND (a.published_at IS NULL OR a.published_at <= NOW())";
            $sql .= " AND (a.expires_at IS NULL OR a.expires_at > NOW())";
        }

        // Filter by course
        if (isset($filters['course_id'])) {
            if ($filters['course_id'] === 'global') {
                $sql .= " AND a.course_id IS NULL";
            } else {
                $sql .= " AND (a.course_id = ? OR a.course_id IS NULL)";
                $params[] = $filters['course_id'];
            }
        }

        // Filter by type
        if (isset($filters['type'])) {
            $sql .= " AND a.announcement_type = ?";
            $params[] = $filters['type'];
        }

        // Filter by priority
        if (isset($filters['priority'])) {
            $sql .= " AND a.priority = ?";
            $params[] = $filters['priority'];
        }

        // Order by (whitelist to prevent SQL injection)
        $allowedOrderColumns = ['created_at', 'updated_at', 'title', 'priority', 'published_at', 'announcement_type'];
        $orderBy = (isset($filters['order_by']) && in_array($filters['order_by'], $allowedOrderColumns)) ? $filters['order_by'] : 'created_at';
        $orderDir = (isset($filters['order_dir']) && strtoupper($filters['order_dir']) === 'ASC') ? 'ASC' : 'DESC';
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
     * Fixed: Removed target_audience filter (column doesn't exist)
     * Uses announcement_type for filtering instead
     */
    public static function getActiveForUser($userId, $role = null, $courseId = null) {
        $db = Database::getInstance();

        $sql = "SELECT a.*,
                       CONCAT(u.first_name, ' ', u.last_name) as creator_name,
                       c.title as course_title
                FROM announcements a
                JOIN users u ON a.posted_by = u.id
                LEFT JOIN courses c ON a.course_id = c.id
                WHERE a.is_published = 1
                  AND (a.published_at IS NULL OR a.published_at <= NOW())
                  AND (a.expires_at IS NULL OR a.expires_at > NOW())";

        $params = [];

        // Filter by course or global (System/General types are global)
        if ($courseId) {
            $sql .= " AND (a.course_id = ? OR a.course_id IS NULL OR a.announcement_type IN ('System', 'General'))";
            $params[] = $courseId;
        } else {
            // Show system and general announcements for dashboard
            $sql .= " AND (a.course_id IS NULL OR a.announcement_type IN ('System', 'General'))";
        }

        $sql .= " ORDER BY a.priority = 'Urgent' DESC, a.announcement_type = 'Urgent' DESC, a.created_at DESC";
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

        if (isset($data['announcement_type'])) {
            $fields[] = "announcement_type = ?";
            $params[] = $data['announcement_type'];
        }

        if (isset($data['priority'])) {
            $fields[] = "priority = ?";
            $params[] = $data['priority'];
        }

        if (isset($data['is_published'])) {
            $fields[] = "is_published = ?";
            $params[] = $data['is_published'] ? 1 : 0;

            // Set published_at if publishing for the first time
            if ($data['is_published'] && !$this->isPublished) {
                $fields[] = "published_at = ?";
                $params[] = date('Y-m-d H:i:s');
            }
        }

        if (isset($data['expires_at'])) {
            $fields[] = "expires_at = ?";
            $params[] = $data['expires_at'];
        }

        if (isset($data['course_id'])) {
            $fields[] = "course_id = ?";
            $params[] = $data['course_id'];
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
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
     * Publish an announcement
     */
    public function publish() {
        return $this->update([
            'is_published' => true,
            'published_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Unpublish an announcement
     */
    public function unpublish() {
        return $this->update(['is_published' => false]);
    }

    /**
     * Check if announcement is expired
     */
    public function isExpired() {
        if (!$this->expiresAt) {
            return false;
        }
        return strtotime($this->expiresAt) < time();
    }

    /**
     * Check if announcement is active
     */
    public function isActive() {
        if (!$this->isPublished) {
            return false;
        }

        if ($this->publishedAt && strtotime($this->publishedAt) > time()) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        return true;
    }

    /**
     * Get announcement type badge color
     */
    public function getTypeBadgeColor() {
        $colors = [
            'General' => 'blue',
            'Course' => 'green',
            'System' => 'yellow',
            'Urgent' => 'red'
        ];

        return $colors[$this->announcementType] ?? 'gray';
    }

    /**
     * Get announcement type icon
     */
    public function getTypeIcon() {
        $icons = [
            'General' => 'info-circle',
            'Course' => 'book',
            'System' => 'cog',
            'Urgent' => 'exclamation-circle'
        ];

        return $icons[$this->announcementType] ?? 'bell';
    }

    /**
     * Hydrate object from database row
     * Fixed: Uses announcement_id and posted_by columns
     */
    private function hydrate($data) {
        $this->id = $data['announcement_id'] ?? null;
        $this->courseId = $data['course_id'] ?? null;
        $this->postedBy = $data['posted_by'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->content = $data['content'] ?? null;
        $this->announcementType = $data['announcement_type'] ?? 'General';
        $this->priority = $data['priority'] ?? 'Normal';
        $this->isPublished = (bool)($data['is_published'] ?? false);
        $this->publishedAt = $data['published_at'] ?? null;
        $this->expiresAt = $data['expires_at'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;

        // Additional data
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
    public function getCreatedBy() { return $this->postedBy; } // Alias for backward compatibility
    public function getTitle() { return $this->title; }
    public function getContent() { return $this->content; }
    public function getAnnouncementType() { return $this->announcementType; }
    public function getPriority() { return $this->priority; }
    public function isPublished() { return $this->isPublished; }
    public function getPublishedAt() { return $this->publishedAt; }
    public function getExpiresAt() { return $this->expiresAt; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
    public function getCreatorName() { return $this->creatorName ?? 'Unknown'; }
    public function getCourseTitle() { return $this->courseTitle ?? 'Global'; }
    public function isGlobal() { return $this->courseId === null; }
}
