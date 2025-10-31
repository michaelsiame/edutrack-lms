<?php
/**
 * Assignment Class
 * Handles course assignments
 */

class Assignment {
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
     * Load assignment data
     */
    private function load() {
        $sql = "SELECT a.*, l.title as lesson_title, m.course_id,
                c.title as course_title
                FROM assignments a
                LEFT JOIN lessons l ON a.lesson_id = l.id
                LEFT JOIN course_modules m ON l.module_id = m.id
                LEFT JOIN courses c ON m.course_id = c.id
                WHERE a.id = :id";
        
        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
    }
    
    /**
     * Check if assignment exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Find assignment by ID
     */
    public static function find($id) {
        $assignment = new self($id);
        return $assignment->exists() ? $assignment : null;
    }
    
    /**
     * Get assignments by course
     */
    public static function getByCourse($courseId) {
        $db = Database::getInstance();
        $sql = "SELECT a.*, l.title as lesson_title
                FROM assignments a
                JOIN lessons l ON a.lesson_id = l.id
                JOIN course_modules m ON l.module_id = m.id
                WHERE m.course_id = :course_id
                ORDER BY a.due_date ASC";
        
        return $db->query($sql, ['course_id' => $courseId])->fetchAll();
    }
    
    /**
     * Get assignment by lesson
     */
    public static function getByLesson($lessonId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM assignments WHERE lesson_id = :lesson_id";
        $result = $db->query($sql, ['lesson_id' => $lessonId])->fetch();
        
        return $result ? new self($result['id']) : null;
    }
    
    /**
     * Create new assignment
     */
    public static function create($data) {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO assignments (
            lesson_id, title, description, instructions,
            max_points, due_date, allow_late_submission,
            max_file_size, allowed_file_types
        ) VALUES (
            :lesson_id, :title, :description, :instructions,
            :max_points, :due_date, :allow_late_submission,
            :max_file_size, :allowed_file_types
        )";
        
        $params = [
            'lesson_id' => $data['lesson_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'instructions' => $data['instructions'] ?? '',
            'max_points' => $data['max_points'] ?? 100,
            'due_date' => $data['due_date'] ?? null,
            'allow_late_submission' => $data['allow_late_submission'] ?? 1,
            'max_file_size' => $data['max_file_size'] ?? 10485760, // 10MB
            'allowed_file_types' => $data['allowed_file_types'] ?? 'pdf,doc,docx,txt,zip'
        ];
        
        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update assignment
     */
    public function update($data) {
        $allowed = ['title', 'description', 'instructions', 'max_points', 
                   'due_date', 'allow_late_submission', 'max_file_size', 
                   'allowed_file_types'];
        
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
        
        $sql = "UPDATE assignments SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :id";
        
        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }
    
    /**
     * Delete assignment
     */
    public function delete() {
        // Delete all submissions
        $sql = "DELETE FROM assignment_submissions WHERE assignment_id = :id";
        $this->db->query($sql, ['id' => $this->id]);
        
        // Delete assignment
        $sql = "DELETE FROM assignments WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }
    
    /**
     * Get user submission
     */
    public function getUserSubmission($userId) {
        require_once __DIR__ . '/Submission.php';
        return Submission::findByUserAndAssignment($userId, $this->id);
    }
    
    /**
     * Get all submissions
     */
    public function getSubmissions() {
        $sql = "SELECT s.*, u.first_name, u.last_name, u.email
                FROM assignment_submissions s
                JOIN users u ON s.user_id = u.id
                WHERE s.assignment_id = :assignment_id
                ORDER BY s.submitted_at DESC";
        
        return $this->db->query($sql, ['assignment_id' => $this->id])->fetchAll();
    }
    
    /**
     * Get pending submissions count
     */
    public function getPendingCount() {
        $sql = "SELECT COUNT(*) as count 
                FROM assignment_submissions 
                WHERE assignment_id = :assignment_id AND status = 'submitted'";
        
        $result = $this->db->query($sql, ['assignment_id' => $this->id])->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get graded submissions count
     */
    public function getGradedCount() {
        $sql = "SELECT COUNT(*) as count 
                FROM assignment_submissions 
                WHERE assignment_id = :assignment_id AND status = 'graded'";
        
        $result = $this->db->query($sql, ['assignment_id' => $this->id])->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Check if assignment is overdue
     */
    public function isOverdue() {
        if (!$this->getDueDate()) {
            return false;
        }
        
        return strtotime($this->getDueDate()) < time();
    }
    
    /**
     * Check if user can submit
     */
    public function canUserSubmit($userId) {
        // Check if already submitted
        $submission = $this->getUserSubmission($userId);
        if ($submission && $submission->exists()) {
            return false; // Already submitted
        }
        
        // Check if overdue and late submissions not allowed
        if ($this->isOverdue() && !$this->allowsLateSubmission()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get time remaining
     */
    public function getTimeRemaining() {
        if (!$this->getDueDate()) {
            return null;
        }
        
        $dueTime = strtotime($this->getDueDate());
        $remaining = $dueTime - time();
        
        if ($remaining < 0) {
            return 'Overdue';
        }
        
        $days = floor($remaining / 86400);
        $hours = floor(($remaining % 86400) / 3600);
        $minutes = floor(($remaining % 3600) / 60);
        
        if ($days > 0) {
            return $days . ' day' . ($days > 1 ? 's' : '') . ' remaining';
        } elseif ($hours > 0) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' remaining';
        } else {
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' remaining';
        }
    }
    
    /**
     * Get allowed file types array
     */
    public function getAllowedFileTypesArray() {
        $types = $this->getAllowedFileTypes();
        return $types ? explode(',', $types) : [];
    }
    
    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getLessonId() { return $this->data['lesson_id'] ?? null; }
    public function getLessonTitle() { return $this->data['lesson_title'] ?? ''; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getTitle() { return $this->data['title'] ?? ''; }
    public function getDescription() { return $this->data['description'] ?? ''; }
    public function getInstructions() { return $this->data['instructions'] ?? ''; }
    public function getMaxPoints() { return $this->data['max_points'] ?? 100; }
    public function getDueDate() { return $this->data['due_date'] ?? null; }
    public function allowsLateSubmission() { return $this->data['allow_late_submission'] == 1; }
    public function getMaxFileSize() { return $this->data['max_file_size'] ?? 10485760; }
    public function getAllowedFileTypes() { return $this->data['allowed_file_types'] ?? 'pdf,doc,docx,txt,zip'; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getUpdatedAt() { return $this->data['updated_at'] ?? null; }
    
    /**
     * Get formatted due date
     */
    public function getFormattedDueDate() {
        if (!$this->getDueDate()) {
            return 'No due date';
        }
        
        return date('F j, Y g:i A', strtotime($this->getDueDate()));
    }
    
    /**
     * Get formatted file size
     */
    public function getFormattedFileSize() {
        return formatFileSize($this->getMaxFileSize());
    }
}