<?php
/**
 * Submission Class
 * Handles assignment submissions
 */

class Submission {
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
     * Load submission data
     */
    private function load() {
        $sql = "SELECT s.*, a.title as assignment_title, a.max_points,
                u.first_name, u.last_name, u.email
                FROM assignment_submissions s
                JOIN assignments a ON s.assignment_id = a.id
                JOIN users u ON s.user_id = u.id
                WHERE s.id = :id";
        
        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
    }
    
    /**
     * Check if submission exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Find submission by ID
     */
    public static function find($id) {
        $submission = new self($id);
        return $submission->exists() ? $submission : null;
    }
    
    /**
     * Find by user and assignment
     */
    public static function findByUserAndAssignment($userId, $assignmentId) {
        $db = Database::getInstance();
        $sql = "SELECT id FROM assignment_submissions 
                WHERE user_id = :user_id AND assignment_id = :assignment_id";
        
        $result = $db->query($sql, [
            'user_id' => $userId,
            'assignment_id' => $assignmentId
        ])->fetch();
        
        return $result ? new self($result['id']) : null;
    }
    
    /**
     * Create new submission
     */
    public static function create($data) {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO assignment_submissions (
            assignment_id, user_id, course_id, submission_text,
            file_path, file_name, file_size, status, submitted_at
        ) VALUES (
            :assignment_id, :user_id, :course_id, :submission_text,
            :file_path, :file_name, :file_size, 'submitted', NOW()
        )";
        
        $params = [
            'assignment_id' => $data['assignment_id'],
            'user_id' => $data['user_id'],
            'course_id' => $data['course_id'],
            'submission_text' => $data['submission_text'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'file_name' => $data['file_name'] ?? null,
            'file_size' => $data['file_size'] ?? null
        ];
        
        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update submission
     */
    public function update($data) {
        $allowed = ['submission_text', 'file_path', 'file_name', 'file_size', 'status'];
        
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
        
        $sql = "UPDATE assignment_submissions SET " . implode(', ', $updates) . " WHERE id = :id";
        
        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }
    
    /**
     * Grade submission
     */
    public function grade($points, $feedback = null) {
        $sql = "UPDATE assignment_submissions SET 
                points_earned = :points,
                feedback = :feedback,
                status = 'graded',
                graded_at = NOW()
                WHERE id = :id";
        
        if ($this->db->query($sql, [
            'points' => $points,
            'feedback' => $feedback,
            'id' => $this->id
        ])) {
            $this->load();
            return true;
        }
        return false;
    }
    
    /**
     * Delete submission
     */
    public function delete() {
        // Delete file if exists
        if ($this->getFilePath()) {
            $filePath = PUBLIC_PATH . '/uploads/assignments/submissions/' . $this->getFilePath();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        $sql = "DELETE FROM assignment_submissions WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }
    
    /**
     * Check if submission is late
     */
    public function isLate() {
        require_once __DIR__ . '/Assignment.php';
        $assignment = Assignment::find($this->getAssignmentId());
        
        if (!$assignment || !$assignment->getDueDate()) {
            return false;
        }
        
        return strtotime($this->getSubmittedAt()) > strtotime($assignment->getDueDate());
    }
    
    /**
     * Get grade percentage
     */
    public function getGradePercentage() {
        if (!$this->isGraded()) {
            return null;
        }
        
        $maxPoints = $this->getMaxPoints();
        $earned = $this->getPointsEarned();
        
        if ($maxPoints == 0) {
            return 0;
        }
        
        return ($earned / $maxPoints) * 100;
    }
    
    /**
     * Get letter grade
     */
    public function getLetterGrade() {
        $percentage = $this->getGradePercentage();
        
        if ($percentage === null) {
            return 'Not Graded';
        }
        
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }
    
    /**
     * Get download URL
     */
    public function getDownloadUrl() {
        if (!$this->getFilePath()) {
            return null;
        }
        
        return url('api/download.php?type=submission&id=' . $this->getId());
    }
    
    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getAssignmentId() { return $this->data['assignment_id'] ?? null; }
    public function getAssignmentTitle() { return $this->data['assignment_title'] ?? ''; }
    public function getUserId() { return $this->data['user_id'] ?? null; }
    public function getUserName() { 
        return trim(($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''));
    }
    public function getUserEmail() { return $this->data['email'] ?? ''; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getSubmissionText() { return $this->data['submission_text'] ?? ''; }
    public function getFilePath() { return $this->data['file_path'] ?? null; }
    public function getFileName() { return $this->data['file_name'] ?? null; }
    public function getFileSize() { return $this->data['file_size'] ?? 0; }
    public function getStatus() { return $this->data['status'] ?? 'submitted'; }
    public function getPointsEarned() { return $this->data['points_earned'] ?? null; }
    public function getMaxPoints() { return $this->data['max_points'] ?? 100; }
    public function getFeedback() { return $this->data['feedback'] ?? ''; }
    public function getSubmittedAt() { return $this->data['submitted_at'] ?? null; }
    public function getGradedAt() { return $this->data['graded_at'] ?? null; }
    
    /**
     * Check if graded
     */
    public function isGraded() {
        return $this->getStatus() == 'graded';
    }
    
    /**
     * Get formatted submission date
     */
    public function getFormattedSubmittedAt() {
        if (!$this->getSubmittedAt()) {
            return 'Not submitted';
        }
        
        return date('F j, Y g:i A', strtotime($this->getSubmittedAt()));
    }
    
    /**
     * Get formatted file size
     */
    public function getFormattedFileSize() {
        return formatFileSize($this->getFileSize());
    }
}