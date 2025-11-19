<?php
/**
 * Module Class
 * Handles course modules/sections
 */

class Module {
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
     * Load module data
     */
    private function load() {
        $sql = "SELECT m.*, c.title as course_title, c.slug as course_slug,
                (SELECT COUNT(*) FROM lessons WHERE module_id = m.id) as lesson_count
                FROM modules m
                JOIN courses c ON m.course_id = c.id
                WHERE m.id = :id";
        
        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
    }
    
    /**
     * Check if module exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Find module by ID
     */
    public static function find($id) {
        $module = new self($id);
        return $module->exists() ? $module : null;
    }
    
    /**
     * Get all modules for a course
     */
    public static function getByCourse($courseId) {
        $db = Database::getInstance();
        $sql = "SELECT m.*,
                (SELECT COUNT(*) FROM lessons WHERE module_id = m.id) as lesson_count
                FROM modules m
                WHERE m.course_id = :course_id
                ORDER BY m.display_order ASC";
        
        return $db->query($sql, ['course_id' => $courseId])->fetchAll();
    }
    
    /**
     * Create new module
     */
    public static function create($data) {
        $db = Database::getInstance();

        $sql = "INSERT INTO modules (
            course_id, title, description, order_index
        ) VALUES (
            :course_id, :title, :description, :order_index
        )";

        $params = [
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'order_index' => $data['order_index'] ?? 0
        ];

        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update module
     */
    public function update($data) {
        $allowed = ['title', 'description', 'order_index'];
        
        $updates = [];
        $params = ['id' => $this->id];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE modules SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :id";

        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }
    
    /**
     * Delete module
     */
    public function delete() {
        // Delete all lessons first
        $sql = "DELETE FROM lessons WHERE module_id = :id";
        $this->db->query($sql, ['id' => $this->id]);
        
        // Delete module
        $sql = "DELETE FROM modules WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }
    
    /**
     * Get module lessons
     */
    public function getLessons() {
        require_once __DIR__ . '/Lesson.php';
        return Lesson::getByModule($this->id);
    }
    
    /**
     * Get completed lessons count for user
     */
    public function getCompletedCount($userId) {
        $sql = "SELECT COUNT(*) as count
                FROM lesson_progress lp
                JOIN lessons l ON lp.lesson_id = l.id
                WHERE l.module_id = :module_id 
                AND lp.user_id = :user_id 
                AND lp.completed = 1";
        
        $result = $this->db->query($sql, [
            'module_id' => $this->id,
            'user_id' => $userId
        ])->fetch();
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Check if module is completed by user
     */
    public function isCompletedByUser($userId) {
        $totalLessons = $this->getLessonCount();
        if ($totalLessons == 0) {
            return false;
        }
        
        $completedLessons = $this->getCompletedCount($userId);
        return $completedLessons >= $totalLessons;
    }
    
    /**
     * Get module progress percentage for user
     */
    public function getProgressPercentage($userId) {
        $totalLessons = $this->getLessonCount();
        if ($totalLessons == 0) {
            return 0;
        }
        
        $completedLessons = $this->getCompletedCount($userId);
        return round(($completedLessons / $totalLessons) * 100);
    }
    
    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getCourseSlug() { return $this->data['course_slug'] ?? ''; }
    public function getTitle() { return $this->data['title'] ?? ''; }
    public function getDescription() { return $this->data['description'] ?? ''; }
    public function getOrderIndex() { return $this->data['order_index'] ?? 0; }
    public function getLessonCount() { return $this->data['lesson_count'] ?? 0; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getUpdatedAt() { return $this->data['updated_at'] ?? null; }
}