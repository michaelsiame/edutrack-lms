<?php
/**
 * Instructor Class
 * Handles instructor-related operations
 * Created to eliminate code duplication across instructor pages
 */

class Instructor {
    private $db;
    private $id;
    private $userId;
    private $data = [];

    /**
     * Constructor
     */
    public function __construct($id = null) {
        $this->db = Database::getInstance();

        if ($id) {
            $this->id = $id;
            $this->load();
        }
    }

    /**
     * Load instructor data
     */
    private function load() {
        $sql = "SELECT i.*, u.first_name, u.last_name, u.email, u.phone, u.avatar_url, u.status
                FROM instructors i
                JOIN users u ON i.user_id = u.id
                WHERE i.id = ?";

        $result = $this->db->fetchOne($sql, [$this->id]);
        $this->data = $result ?: [];

        if ($this->data) {
            $this->userId = $this->data['user_id'];
        }
    }

    /**
     * Check if instructor exists
     */
    public function exists() {
        return !empty($this->data);
    }

    /**
     * Find instructor by ID
     */
    public static function find($id) {
        $instructor = new self($id);
        return $instructor->exists() ? $instructor : null;
    }

    /**
     * Find instructor by user ID
     * This is the most commonly needed lookup pattern
     */
    public static function findByUserId($userId) {
        $db = Database::getInstance();
        $result = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);

        if ($result) {
            return new self($result['id']);
        }

        return null;
    }

    /**
     * Get instructor ID from user ID
     * Returns instructor.id if found, or null
     * This eliminates the repeated pattern across instructor pages
     */
    public static function getInstructorIdFromUserId($userId) {
        $db = Database::getInstance();
        $result = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);

        return $result ? $result['id'] : null;
    }

    /**
     * Get or create instructor record for a user
     * Ensures a user always has an instructor record if they have instructor role
     */
    public static function getOrCreate($userId) {
        $instructor = self::findByUserId($userId);

        if ($instructor) {
            return $instructor;
        }

        // Create new instructor record
        $db = Database::getInstance();
        $db->insert('instructors', [
            'user_id' => $userId,
            'is_verified' => 0
        ]);

        $instructorId = $db->lastInsertId();
        return new self($instructorId);
    }

    /**
     * Create new instructor
     */
    public static function create($data) {
        $db = Database::getInstance();

        $insertData = [
            'user_id' => $data['user_id'],
            'bio' => $data['bio'] ?? null,
            'specialization' => $data['specialization'] ?? null,
            'years_experience' => $data['years_experience'] ?? null,
            'education' => $data['education'] ?? null,
            'certifications' => $data['certifications'] ?? null,
            'is_verified' => $data['is_verified'] ?? 0
        ];

        if ($db->insert('instructors', $insertData)) {
            return new self($db->lastInsertId());
        }

        return null;
    }

    /**
     * Update instructor
     */
    public function update($data) {
        $allowed = ['bio', 'specialization', 'years_experience', 'education',
                    'certifications', 'is_verified'];

        $updates = [];
        $params = [];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $this->id;
        $sql = "UPDATE instructors SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";

        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }

        return false;
    }

    /**
     * Get instructor's courses
     */
    public function getCourses($status = null) {
        $sql = "SELECT c.*, cat.name as category_name,
                (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count,
                (SELECT COUNT(*) FROM modules WHERE course_id = c.id) as module_count,
                (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id) as lesson_count
                FROM courses c
                LEFT JOIN course_categories cat ON c.category_id = cat.id
                WHERE c.instructor_id = ?";

        $params = [$this->id];

        if ($status) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY c.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get instructor's students (unique enrollments)
     */
    public function getStudents($limit = null) {
        $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.email,
                e.enrolled_at, e.progress as progress_percentage, e.enrollment_status,
                c.title as course_title
                FROM users u
                JOIN enrollments e ON u.id = e.user_id
                JOIN courses c ON e.course_id = c.id
                WHERE c.instructor_id = ?
                ORDER BY e.enrolled_at DESC";

        $params = [$this->id];

        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get pending assignment submissions
     */
    public function getPendingSubmissions($limit = null) {
        $sql = "SELECT asub.*, a.title as assignment_title, a.max_points,
                c.title as course_title, c.slug as course_slug,
                u.first_name, u.last_name
                FROM assignment_submissions asub
                JOIN assignments a ON asub.assignment_id = a.id
                JOIN courses c ON a.course_id = c.id
                JOIN students st ON asub.student_id = st.id
                JOIN users u ON st.user_id = u.id
                WHERE c.instructor_id = ? AND asub.status = 'Submitted'
                ORDER BY asub.submitted_at DESC";

        $params = [$this->id];

        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get recent enrollments in instructor's courses
     */
    public function getRecentEnrollments($limit = 10) {
        $sql = "SELECT e.*, c.title as course_title, c.slug as course_slug,
                u.first_name, u.last_name, u.email
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                JOIN users u ON e.user_id = u.id
                WHERE c.instructor_id = ?
                ORDER BY e.enrolled_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$this->id, $limit]);
    }

    /**
     * Get recent reviews
     */
    public function getRecentReviews($limit = 5) {
        $sql = "SELECT cr.*, c.title as course_title, c.slug as course_slug,
                u.first_name, u.last_name
                FROM course_reviews cr
                JOIN courses c ON cr.course_id = c.id
                JOIN users u ON cr.user_id = u.id
                WHERE c.instructor_id = ?
                ORDER BY cr.created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$this->id, $limit]);
    }

    /**
     * Get revenue statistics
     */
    public function getRevenue() {
        $sql = "SELECT
                COALESCE(SUM(CASE WHEN e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN c.price ELSE 0 END), 0) as monthly_revenue,
                COALESCE(SUM(c.price), 0) as total_revenue
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE c.instructor_id = ? AND e.payment_status = 'completed'";

        return $this->db->fetchOne($sql, [$this->id]);
    }

    /**
     * Get all instructors
     */
    public static function all($verified = null, $limit = 100, $offset = 0) {
        $db = Database::getInstance();

        $sql = "SELECT i.*, u.first_name, u.last_name, u.email, u.phone, u.status,
                (SELECT COUNT(*) FROM courses WHERE instructor_id = i.id) as course_count,
                (SELECT COUNT(DISTINCT e.user_id) FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.instructor_id = i.id) as student_count
                FROM instructors i
                JOIN users u ON i.user_id = u.id";

        $params = [];

        if ($verified !== null) {
            $sql .= " WHERE i.is_verified = ?";
            $params[] = $verified ? 1 : 0;
        }

        $sql .= " ORDER BY i.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $db->fetchAll($sql, $params);
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->userId ?? ($this->data['user_id'] ?? null); }
    public function getBio() { return $this->data['bio'] ?? ''; }
    public function getSpecialization() { return $this->data['specialization'] ?? ''; }
    public function getYearsExperience() { return $this->data['years_experience'] ?? 0; }
    public function getEducation() { return $this->data['education'] ?? ''; }
    public function getCertifications() { return $this->data['certifications'] ?? ''; }
    public function getRating() { return $this->data['rating'] ?? 0; }
    public function getTotalStudents() { return $this->data['total_students'] ?? 0; }
    public function getTotalCourses() { return $this->data['total_courses'] ?? 0; }
    public function isVerified() { return (bool)($this->data['is_verified'] ?? false); }

    // User data getters
    public function getFirstName() { return $this->data['first_name'] ?? ''; }
    public function getLastName() { return $this->data['last_name'] ?? ''; }
    public function getFullName() { return trim($this->getFirstName() . ' ' . $this->getLastName()); }
    public function getEmail() { return $this->data['email'] ?? ''; }
    public function getPhone() { return $this->data['phone'] ?? ''; }
    public function getAvatarUrl() { return $this->data['avatar_url'] ?? ''; }
}
