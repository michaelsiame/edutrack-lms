<?php
/**
 * Edutrack Computer Training College
 * Enrollment Class
 * Handles course access, progress tracking, and financial logic.
 */

class Enrollment {
    private $db;
    private $id;
    private $data = [];
    private $paymentPlan = [];
    
    public function __construct($id = null) {
        $this->db = Database::getInstance();
        if ($id) {
            $this->id = $id;
            $this->load();
        }
    }
    
    /**
     * Load enrollment data with financial info
     */
    private function load() {
        // We LEFT JOIN the payment plans so we know the balance/paid status immediately
        $sql = "SELECT e.*, 
                       c.title as course_title, c.slug as course_slug, c.price as course_price, c.duration_weeks,
                       u.first_name, u.last_name, u.email,
                       p.total_fee, p.total_paid, p.balance, p.payment_status as plan_status
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                JOIN users u ON e.user_id = u.id
                LEFT JOIN enrollment_payment_plans p ON e.id = p.enrollment_id
                WHERE e.id = :id";
        
        $result = $this->db->fetchOne($sql, ['id' => $this->id]);
        
        if ($result) {
            $this->data = $result;
            // Separate payment plan data for clarity, though it's in $this->data now too
            $this->paymentPlan = [
                'total_fee' => $result['total_fee'] ?? $result['course_price'],
                'total_paid' => $result['total_paid'] ?? 0,
                'balance' => $result['balance'] ?? 0,
                'status' => $result['plan_status'] ?? 'pending'
            ];
        }
    }
    
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
     * Find enrollment by User and Course
     */
    public static function findByUserAndCourse($userId, $courseId) {
        $db = Database::getInstance();
        $result = $db->fetchOne("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
        
        return $result ? new self($result['id']) : null;
    }
    
    /**
     * Check if user is already enrolled
     */
    public static function isEnrolled($userId, $courseId) {
        return self::findByUserAndCourse($userId, $courseId) !== null;
    }
    
    /**
     * CORE LOGIC: Create New Enrollment + Payment Plan
     * This runs inside a transaction to ensure financial data is created.
     */
    public static function create($data) {
        $db = Database::getInstance();

        // 1. Check Duplicates
        if (self::isEnrolled($data['user_id'], $data['course_id'])) {
            return false;
        }

        // 2. Ensure Student Record Exists (Foreign Key Requirement)
        $student = $db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$data['user_id']]);
        if (!$student) {
            $db->insert('students', [
                'user_id' => $data['user_id'],
                'enrollment_date' => date('Y-m-d')
            ]);
            $studentId = $db->lastInsertId();
        } else {
            $studentId = $student['id'];
        }

        // 3. Get Course Price for Payment Plan
        $course = $db->fetchOne("SELECT price FROM courses WHERE id = ?", [$data['course_id']]);
        $coursePrice = $course['price'] ?? 0;

        try {
            $db->beginTransaction();

            // 4. Insert Enrollment
            // Default status 'Enrolled' implies "Waiting for Payment/Deposit"
            $enrollParams = [
                'user_id' => $data['user_id'],
                'student_id' => $studentId,
                'course_id' => $data['course_id'],
                'enrollment_status' => 'Enrolled', 
                'payment_status' => 'pending',
                'amount_paid' => 0,
                'enrolled_at' => date('Y-m-d'),
                'start_date' => date('Y-m-d'),
                'progress' => 0,
                'certificate_blocked' => 1 // Blocked by default until fully paid
            ];
            
            if (!$db->insert('enrollments', $enrollParams)) {
                throw new Exception("Failed to insert enrollment record.");
            }
            $enrollmentId = $db->lastInsertId();

            // 5. Create Payment Plan Record (Crucial for Finance Reports)
            $planParams = [
                'enrollment_id' => $enrollmentId,
                'user_id' => $data['user_id'],
                'course_id' => $data['course_id'],
                'total_fee' => $coursePrice,
                'total_paid' => 0,
                'currency' => 'ZMW', // Defaulting to Zambia currency
                'payment_status' => 'pending'
            ];
            
            if (!$db->insert('enrollment_payment_plans', $planParams)) {
                throw new Exception("Failed to create payment plan.");
            }

            // 6. Log Activity
            self::logActivity($data['user_id'], 'enrollment', $data['course_id'], 'Enrolled in course (Pending Deposit)');

            $db->commit();
            return $enrollmentId;

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Enrollment::create Error: " . $e->getMessage());
            return false;
        }
    }
    
    // =====================================================================
    // FINANCIAL ACCESS LOGIC (The 30% Rule)
    // =====================================================================

    /**
     * Check if student can access course content
     * Logic: Must be Admin OR Instructor OR (Paid >= 30%)
     */
    public function canAccessContent() {
        // 1. Admins & Instructors bypass checks
        $currentUser = User::current();
        if ($currentUser && ($currentUser->hasRole('admin') || $currentUser->hasRole('instructor'))) {
            return true;
        }

        // 2. Completed courses are always accessible
        if ($this->data['enrollment_status'] === 'Completed') {
            return true;
        }

        // 3. Check Financials
        $totalFee = (float) $this->paymentPlan['total_fee'];
        $totalPaid = (float) $this->paymentPlan['total_paid'];

        // If free course, allow access
        if ($totalFee <= 0) {
            return true;
        }

        // Calculate percentage
        $percentagePaid = ($totalPaid / $totalFee) * 100;

        // THE RULE: 30% Threshold
        return $percentagePaid >= 30;
    }

    /**
     * Check if certificate download is allowed
     * Logic: Must be 100% Paid
     */
    public function canDownloadCertificate() {
        // If specific block flag is on
        if (!empty($this->data['certificate_blocked'])) {
            return false;
        }

        // Double check balance
        $balance = (float) ($this->paymentPlan['balance'] ?? 0);
        return $balance <= 0;
    }

    // =====================================================================
    // STANDARD METHODS
    // =====================================================================

    public function update($data) {
        // Alias mapping for backward compatibility
        if (isset($data['progress_percentage'])) {
            $data['progress'] = $data['progress_percentage'];
            unset($data['progress_percentage']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update('enrollments', $data, 'id = ?', [$this->id]);
    }
    
    public function updateProgress($percentage) {
        $percentage = min(100, max(0, floatval($percentage)));
        return $this->update(['progress' => $percentage]);
    }
    
    public function complete() {
        return $this->update([
            'enrollment_status' => 'Completed',
            'completion_date' => date('Y-m-d'),
            'progress' => 100
        ]);
    }
    
    public function addTimeSpent($minutes) {
        $currentTime = $this->getTotalTimeSpent();
        return $this->update(['total_time_spent' => $currentTime + $minutes]);
    }
    
    public function updateLastAccessed() {
        return $this->update(['last_accessed' => date('Y-m-d H:i:s')]);
    }
    
    public function cancel() {
        return $this->update(['enrollment_status' => 'Dropped']);
    }

    /**
     * Delete the enrollment and related records
     */
    public function delete() {
        try {
            $this->db->beginTransaction();

            // Delete payment plan first (foreign key)
            $this->db->query("DELETE FROM enrollment_payment_plans WHERE enrollment_id = ?", [$this->id]);

            // Delete lesson progress
            $this->db->query("DELETE FROM lesson_progress WHERE enrollment_id = ?", [$this->id]);

            // Delete the enrollment
            $this->db->query("DELETE FROM enrollments WHERE id = ?", [$this->id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Enrollment::delete Error: " . $e->getMessage());
            return false;
        }
    }

    // =====================================================================
    // PROGRESS TRACKING
    // =====================================================================

    public function getLessonProgress($lessonId) {
        return $this->db->fetchOne(
            "SELECT * FROM lesson_progress WHERE enrollment_id = ? AND lesson_id = ?", 
            [$this->id, $lessonId]
        );
    }

    public function markLessonComplete($lessonId) {
        try {
            $this->db->beginTransaction();

            // Use ON DUPLICATE KEY UPDATE to handle re-visits
            $sql = "INSERT INTO lesson_progress
                    (enrollment_id, lesson_id, status, progress_percentage, started_at, completed_at, last_accessed)
                    VALUES (?, ?, 'Completed', 100, NOW(), NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                    status = 'Completed', progress_percentage = 100, completed_at = NOW(), last_accessed = NOW()";

            $stmt = $this->db->query($sql, [$this->id, $lessonId]);
            $result = $stmt && ($stmt->rowCount() > 0);

            // Update main progress
            $this->recalculateProgress();

            // Log it
            self::logActivity($this->getUserId(), 'lesson', $lessonId, 'Completed lesson');

            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Enrollment::markLessonComplete Error: " . $e->getMessage());
            return false;
        }
    }

    public function recalculateProgress() {
        // Need Course class to count lessons
        if (!class_exists('Course')) {
            require_once __DIR__ . '/Course.php';
        }
        
        $course = Course::find($this->getCourseId());
        if (!$course) return;
        
        $totalLessons = $course->getTotalLessons();
        if ($totalLessons == 0) return; // Prevent division by zero

        // Count completed lessons
        $completed = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM lesson_progress WHERE enrollment_id = ? AND status = 'Completed'",
            [$this->id]
        );

        $percentage = ($completed / $totalLessons) * 100;
        $this->updateProgress($percentage);

        // Auto-complete course if 100%
        if ($percentage >= 100 && !$this->isCompleted()) {
            $this->complete();
        }
    }

    // =====================================================================
    // RELATED DATA GETTERS
    // =====================================================================

    public function getCompletedLessons() {
        $rows = $this->db->fetchAll(
            "SELECT lesson_id FROM lesson_progress WHERE enrollment_id = ? AND status = 'Completed'",
            [$this->id]
        );
        return array_column($rows, 'lesson_id');
    }

    public function getQuizAttempts($quizId = null) {
        $sql = "SELECT qa.*, q.title as quiz_title 
                FROM quiz_attempts qa 
                JOIN quizzes q ON qa.quiz_id = q.id 
                WHERE qa.student_id = ? AND q.course_id = ?";
        $params = [$this->getStudentId(), $this->getCourseId()];
        
        if ($quizId) {
            $sql .= " AND qa.quiz_id = ?";
            $params[] = $quizId;
        }
        $sql .= " ORDER BY qa.started_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getAssignmentSubmissions($assignmentId = null) {
        $sql = "SELECT s.*, a.title as assignment_title 
                FROM assignment_submissions s 
                JOIN assignments a ON s.assignment_id = a.id 
                WHERE s.student_id = ? AND a.course_id = ?";
        $params = [$this->getStudentId(), $this->getCourseId()];
        
        if ($assignmentId) {
            $sql .= " AND s.assignment_id = ?";
            $params[] = $assignmentId;
        }
        $sql .= " ORDER BY s.submitted_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    private static function logActivity($userId, $type, $entityId, $desc) {
        $db = Database::getInstance();
        $db->insert('activity_logs', [
            'user_id' => $userId,
            'activity_type' => $type,
            'entity_type' => 'course',
            'entity_id' => $entityId,
            'description' => $desc,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    // =====================================================================
    // HELPER GETTERS
    // =====================================================================

    public function getId() { return $this->id; }
    public function getUserId() { return $this->data['user_id'] ?? null; }
    public function getStudentId() { return $this->data['student_id'] ?? null; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getCourseSlug() { return $this->data['course_slug'] ?? ''; }
    public function getProgress() { return (float)($this->data['progress'] ?? 0); }
    public function getTotalTimeSpent() { return (int)($this->data['total_time_spent'] ?? 0); }
    public function getEnrolledAt() { return $this->data['enrolled_at'] ?? null; }
    
    // Status Helpers
    public function getStatus() { return $this->data['enrollment_status'] ?? 'Enrolled'; }
    public function isActive() { return in_array($this->getStatus(), ['Enrolled', 'In Progress']); }
    public function isCompleted() { return $this->getStatus() === 'Completed'; }
    public function isDropped() { return $this->getStatus() === 'Dropped'; }
    
    // Financial Helpers
    public function getTotalFee() { return (float)($this->paymentPlan['total_fee'] ?? 0); }
    public function getTotalPaid() { return (float)($this->paymentPlan['total_paid'] ?? 0); }
    public function getBalance() { return (float)($this->paymentPlan['balance'] ?? 0); }
    
    /**
     * Get list of all enrollments (for Admin/Reports)
     */
    public static function all($filters = []) {
        $db = Database::getInstance();
        $sql = "SELECT e.*, c.title as course_title, 
                       u.first_name, u.last_name, u.email
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                JOIN users u ON e.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        if (!empty($filters['user_id'])) {
            $sql .= " AND e.user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['course_id'])) {
            $sql .= " AND e.course_id = ?";
            $params[] = $filters['course_id'];
        }
        
        $sql .= " ORDER BY e.enrolled_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        return $db->fetchAll($sql, $params);
    }
}