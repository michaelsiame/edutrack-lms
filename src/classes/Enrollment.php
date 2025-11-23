<?php
/**
 * Enrollment Class
 * Handles course enrollments
 */

class Enrollment {
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
     * Load enrollment data
     */
    private function load() {
        $sql = "SELECT e.*, c.title as course_title, c.slug as course_slug,
                u.first_name, u.last_name, u.email
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                JOIN users u ON e.user_id = u.id
                WHERE e.id = :id";
        
        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
    }
    
    /**
     * Check if enrollment exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Find enrollment by ID
     */
    public static function find($id) {
        $enrollment = new self($id);
        return $enrollment->exists() ? $enrollment : null;
    }
    
    /**
     * Find enrollment by user and course
     */
    public static function findByUserAndCourse($userId, $courseId) {
        $db = Database::getInstance();
        $sql = "SELECT id FROM enrollments 
                WHERE user_id = :user_id AND course_id = :course_id";
        
        $result = $db->query($sql, [
            'user_id' => $userId,
            'course_id' => $courseId
        ])->fetch();
        
        if ($result) {
            return new self($result['id']);
        }
        return null;
    }
    
    /**
     * Check if user is enrolled in course
     */
    public static function isEnrolled($userId, $courseId) {
        $enrollment = self::findByUserAndCourse($userId, $courseId);
        return $enrollment !== null;
    }
    
    /**
     * Create new enrollment
     */
    public static function create($data) {
        $db = Database::getInstance();

        // Check if already enrolled
        if (self::isEnrolled($data['user_id'], $data['course_id'])) {
            return false; // Already enrolled
        }

        // Get student_id from students table (required FK)
        $student = $db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$data['user_id']]);

        // If user is not in students table, create student record
        if (!$student) {
            $db->insert('students', [
                'user_id' => $data['user_id'],
                'enrollment_date' => date('Y-m-d')
            ]);
            $studentId = $db->lastInsertId();
        } else {
            $studentId = $student['id'];
        }

        $sql = "INSERT INTO enrollments (
            user_id, student_id, course_id, enrollment_status, payment_status,
            amount_paid, enrolled_at
        ) VALUES (
            :user_id, :student_id, :course_id, :enrollment_status, :payment_status,
            :amount_paid, NOW()
        )";

        $params = [
            'user_id' => $data['user_id'],
            'student_id' => $studentId,
            'course_id' => $data['course_id'],
            'enrollment_status' => $data['enrollment_status'] ?? 'Enrolled',
            'payment_status' => $data['payment_status'] ?? 'pending',
            'amount_paid' => $data['amount_paid'] ?? 0
        ];

        if ($db->query($sql, $params)) {
            $enrollmentId = $db->lastInsertId();

            // Log activity
            self::logActivity($data['user_id'], 'enrollment', $data['course_id'], 'Enrolled in course');

            return $enrollmentId;
        }
        return false;
    }
    
    /**
     * Update enrollment
     */
    public function update($data) {
        $allowed = ['enrollment_status', 'payment_status', 'amount_paid', 'progress_percentage', 'total_time_spent'];
        
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
        
        $sql = "UPDATE enrollments SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :id";
        
        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }
    
    /**
     * Complete enrollment
     */
    public function complete() {
        return $this->update([
            'enrollment_status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'progress_percentage' => 100
        ]);
    }
    
    /**
     * Update progress
     */
    public function updateProgress($percentage) {
        return $this->update(['progress_percentage' => min(100, max(0, $percentage))]);
    }
    
    /**
     * Add time spent
     */
    public function addTimeSpent($minutes) {
        $currentTime = $this->getTotalTimeSpent();
        return $this->update(['total_time_spent' => $currentTime + $minutes]);
    }
    
    /**
     * Update last accessed
     */
    public function updateLastAccessed() {
        $sql = "UPDATE enrollments SET last_accessed_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }
    
    /**
     * Cancel enrollment
     */
    public function cancel() {
        return $this->update(['enrollment_status' => 'cancelled']);
    }
    
    /**
     * Get completed lessons
     */
    public function getCompletedLessons() {
        $sql = "SELECT lesson_id FROM lesson_progress
                WHERE enrollment_id = :enrollment_id AND status = 'Completed'";

        return $this->db->query($sql, [
            'enrollment_id' => $this->getId()
        ])->fetchAll();
    }

    /**
     * Get lesson progress
     */
    public function getLessonProgress($lessonId) {
        $sql = "SELECT * FROM lesson_progress
                WHERE enrollment_id = :enrollment_id AND lesson_id = :lesson_id";

        return $this->db->query($sql, [
            'enrollment_id' => $this->getId(),
            'lesson_id' => $lessonId
        ])->fetch();
    }

    /**
     * Mark lesson as completed
     */
    public function markLessonComplete($lessonId) {
        // Check if progress record exists
        $existing = $this->getLessonProgress($lessonId);

        if ($existing) {
            // Update existing record
            $sql = "UPDATE lesson_progress SET
                    status = 'Completed',
                    progress_percentage = 100.00,
                    completed_at = NOW(),
                    last_accessed = NOW()
                    WHERE enrollment_id = :enrollment_id AND lesson_id = :lesson_id";
        } else {
            // Insert new record
            $sql = "INSERT INTO lesson_progress (
                enrollment_id, lesson_id, status, progress_percentage,
                started_at, completed_at, last_accessed
            ) VALUES (
                :enrollment_id, :lesson_id, 'Completed', 100.00,
                NOW(), NOW(), NOW()
            )";
        }

        $result = $this->db->query($sql, [
            'enrollment_id' => $this->getId(),
            'lesson_id' => $lessonId
        ]);

        if ($result) {
            // Recalculate progress
            $this->recalculateProgress();

            // Log activity
            self::logActivity($this->getUserId(), 'lesson', $lessonId, 'Completed lesson');
        }

        return $result;
    }

    /**
     * Recalculate progress percentage
     */
    public function recalculateProgress() {
        // Get total lessons in course
        require_once __DIR__ . '/Course.php';
        $course = Course::find($this->getCourseId());
        if (!$course) {
            return;
        }
        $totalLessons = $course->getTotalLessons();

        if ($totalLessons == 0) {
            return;
        }

        // Get completed lessons count using enrollment_id
        $sql = "SELECT COUNT(*) as completed FROM lesson_progress
                WHERE enrollment_id = :enrollment_id AND status = 'Completed'";

        $result = $this->db->query($sql, [
            'enrollment_id' => $this->getId()
        ])->fetch();

        $completedLessons = $result['completed'];
        $percentage = ($completedLessons / $totalLessons) * 100;

        $this->updateProgress($percentage);

        // Check if course is completed
        if ($percentage >= 100) {
            $this->complete();
        }
    }
    
    /**
     * Get user's quiz attempts
     * Note: quiz_attempts uses student_id (FK to students table), not user_id
     */
    public function getQuizAttempts($quizId = null) {
        // Get student_id from students table
        $student = $this->db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$this->getUserId()]);
        if (!$student) {
            return [];
        }

        $sql = "SELECT qa.*, q.title as quiz_title
                FROM quiz_attempts qa
                JOIN quizzes q ON qa.quiz_id = q.id
                WHERE qa.student_id = :student_id AND q.course_id = :course_id";

        $params = [
            'student_id' => $student['id'],
            'course_id' => $this->getCourseId()
        ];

        if ($quizId) {
            $sql .= " AND qa.quiz_id = :quiz_id";
            $params['quiz_id'] = $quizId;
        }

        $sql .= " ORDER BY qa.started_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get user's assignment submissions
     * Note: assignment_submissions uses student_id (FK to students table), not user_id
     */
    public function getAssignmentSubmissions($assignmentId = null) {
        // Get student_id from students table
        $student = $this->db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$this->getUserId()]);
        if (!$student) {
            return [];
        }

        $sql = "SELECT asub.*, a.title as assignment_title
                FROM assignment_submissions asub
                JOIN assignments a ON asub.assignment_id = a.id
                WHERE asub.student_id = :student_id AND a.course_id = :course_id";

        $params = [
            'student_id' => $student['id'],
            'course_id' => $this->getCourseId()
        ];

        if ($assignmentId) {
            $sql .= " AND asub.assignment_id = :assignment_id";
            $params['assignment_id'] = $assignmentId;
        }

        $sql .= " ORDER BY asub.submitted_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Log activity
     * Uses correct column names for activity_logs table
     */
    private static function logActivity($userId, $entityType, $entityId = null, $description = null) {
        $db = Database::getInstance();

        $sql = "INSERT INTO activity_logs (
            user_id, activity_type, entity_type, entity_id, description,
            ip_address, user_agent, created_at
        ) VALUES (
            :user_id, :activity_type, :entity_type, :entity_id, :description,
            :ip_address, :user_agent, NOW()
        )";

        return $db->query($sql, [
            'user_id' => $userId,
            'activity_type' => $entityType . '_action',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    /**
     * Get all enrollments with filters
     */
    public static function all($filters = []) {
        $db = Database::getInstance();
        
        $sql = "SELECT e.*, c.title as course_title, c.slug as course_slug,
                u.first_name, u.last_name, u.email
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                JOIN users u ON e.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND e.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['course_id'])) {
            $sql .= " AND e.course_id = :course_id";
            $params['course_id'] = $filters['course_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND e.enrollment_status = :status";
            $params['status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY e.enrolled_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT :limit";
            $params['limit'] = (int)$filters['limit'];
        }
        
        return $db->query($sql, $params)->fetchAll();
    }
    
    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getUserId() { return $this->data['user_id'] ?? null; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getCourseSlug() { return $this->data['course_slug'] ?? ''; }
    public function getEnrollmentStatus() { return $this->data['enrollment_status'] ?? 'active'; }
    public function getPaymentStatus() { return $this->data['payment_status'] ?? 'pending'; }
    public function getAmountPaid() { return $this->data['amount_paid'] ?? 0; }
    public function getProgressPercentage() { return $this->data['progress_percentage'] ?? 0; }
    public function getTotalTimeSpent() { return $this->data['total_time_spent'] ?? 0; }
    public function getEnrolledAt() { return $this->data['enrolled_at'] ?? null; }
    public function getLastAccessedAt() { return $this->data['last_accessed_at'] ?? null; }
    public function getCompletedAt() { return $this->data['completed_at'] ?? null; }
    
    public function isActive() { return $this->getEnrollmentStatus() == 'active'; }
    public function isCompleted() { return $this->getEnrollmentStatus() == 'completed'; }
    public function isCancelled() { return $this->getEnrollmentStatus() == 'cancelled'; }
    public function isPaid() { return $this->getPaymentStatus() == 'paid'; }
}