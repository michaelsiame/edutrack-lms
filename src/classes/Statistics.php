<?php
/**
 * Statistics Class
 * Centralizes all dashboard and reporting queries
 * Reduces code duplication across admin and instructor dashboards
 */

class Statistics {
    private static $db;

    /**
     * Get database instance
     */
    private static function getDb() {
        if (!self::$db) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    /**
     * Get total number of students
     */
    public static function getTotalStudents() {
        $db = self::getDb();
        return (int) $db->fetchColumn("
            SELECT COUNT(DISTINCT u.id)
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.id
            WHERE r.role_name = 'Student'
        ");
    }

    /**
     * Get total number of instructors
     */
    public static function getTotalInstructors() {
        $db = self::getDb();
        return (int) $db->fetchColumn("
            SELECT COUNT(DISTINCT u.id)
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.id
            WHERE r.role_name = 'Instructor'
        ");
    }

    /**
     * Get total number of admins
     */
    public static function getTotalAdmins() {
        $db = self::getDb();
        return (int) $db->fetchColumn("
            SELECT COUNT(DISTINCT u.id)
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.id
            WHERE r.role_name IN ('Admin', 'Super Admin')
        ");
    }

    /**
     * Get total active users
     */
    public static function getActiveUsers() {
        $db = self::getDb();
        return (int) $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'active'");
    }

    /**
     * Get total number of courses
     */
    public static function getTotalCourses() {
        $db = self::getDb();
        return (int) $db->fetchColumn("SELECT COUNT(*) FROM courses");
    }

    /**
     * Get published courses count
     */
    public static function getPublishedCourses() {
        $db = self::getDb();
        return (int) $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE status = 'published'");
    }

    /**
     * Get draft courses count
     */
    public static function getDraftCourses() {
        $db = self::getDb();
        return (int) $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE status = 'draft'");
    }

    /**
     * Get total enrollments
     */
    public static function getTotalEnrollments() {
        $db = self::getDb();
        return (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments");
    }

    /**
     * Get active enrollments
     * Uses correct schema enum values: 'Enrolled', 'In Progress'
     */
    public static function getActiveEnrollments() {
        $db = self::getDb();
        return (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status IN ('Enrolled', 'In Progress')");
    }

    /**
     * Get completed enrollments
     * Uses correct schema enum value: 'Completed'
     */
    public static function getCompletedEnrollments() {
        $db = self::getDb();
        return (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'Completed'");
    }

    /**
     * Get total revenue
     */
    public static function getTotalRevenue() {
        $db = self::getDb();
        return (float) $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'Completed'");
    }

    /**
     * Get revenue for specific period
     */
    public static function getRevenueByPeriod($months = 6) {
        $db = self::getDb();
        return $db->fetchAll("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(amount) as revenue,
                COUNT(*) as transactions
            FROM payments
            WHERE payment_status = 'Completed'
              AND created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
        ", [$months]);
    }

    /**
     * Get pending payments count
     */
    public static function getPendingPayments() {
        $db = self::getDb();
        return (int) $db->fetchColumn("SELECT COUNT(*) FROM payments WHERE payment_status = 'Pending'");
    }

    /**
     * Get total certificates issued
     */
    public static function getTotalCertificates() {
        $db = self::getDb();
        return (int) $db->fetchColumn("SELECT COUNT(*) FROM certificates");
    }

    /**
     * Get recent enrollments
     */
    public static function getRecentEnrollments($limit = 10) {
        $db = self::getDb();
        return $db->fetchAll("
            SELECT e.*,
                   u.first_name, u.last_name, u.email,
                   c.title as course_title, c.slug as course_slug
            FROM enrollments e
            JOIN users u ON e.user_id = u.id
            JOIN courses c ON e.course_id = c.id
            ORDER BY e.enrolled_at DESC
            LIMIT ?
        ", [$limit]);
    }

    /**
     * Get recent payments
     */
    public static function getRecentPayments($limit = 10) {
        $db = self::getDb();
        return $db->fetchAll("
            SELECT p.*,
                   u.first_name, u.last_name, u.email,
                   c.title as course_title
            FROM payments p
            JOIN students s ON p.student_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN courses c ON p.course_id = c.id
            ORDER BY p.created_at DESC
            LIMIT ?
        ", [$limit]);
    }

    /**
     * Get course statistics
     */
    public static function getCourseStats($courseId) {
        $db = self::getDb();

        $stats = [];

        // Total enrollments
        $stats['enrollments'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM enrollments WHERE course_id = ?",
            [$courseId]
        );

        // Active students (uses correct schema enum values: 'Enrolled', 'In Progress')
        $stats['active_students'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND enrollment_status IN ('Enrolled', 'In Progress')",
            [$courseId]
        );

        // Completed students (uses correct schema enum value: 'Completed')
        $stats['completed_students'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND enrollment_status = 'Completed'",
            [$courseId]
        );

        // Average progress (uses correct field name: 'progress')
        $stats['avg_progress'] = (float) $db->fetchColumn(
            "SELECT AVG(progress) FROM enrollments WHERE course_id = ?",
            [$courseId]
        );

        // Total revenue from this course
        $stats['revenue'] = (float) $db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE course_id = ? AND payment_status = 'Completed'",
            [$courseId]
        );

        // Average rating
        $stats['avg_rating'] = (float) $db->fetchColumn(
            "SELECT AVG(rating) FROM course_reviews WHERE course_id = ?",
            [$courseId]
        );

        // Total reviews
        $stats['total_reviews'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM course_reviews WHERE course_id = ?",
            [$courseId]
        );

        return $stats;
    }

    /**
     * Get instructor statistics
     */
    public static function getInstructorStats($instructorId) {
        $db = self::getDb();

        $stats = [];

        // Total courses
        $stats['total_courses'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM courses WHERE instructor_id = ?",
            [$instructorId]
        );

        // Published courses
        $stats['published_courses'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM courses WHERE instructor_id = ? AND status = 'published'",
            [$instructorId]
        );

        // Total students (unique enrollments across all courses)
        $stats['total_students'] = (int) $db->fetchColumn(
            "SELECT COUNT(DISTINCT e.user_id)
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             WHERE c.instructor_id = ?",
            [$instructorId]
        );

        // Total enrollments
        $stats['total_enrollments'] = (int) $db->fetchColumn(
            "SELECT COUNT(*)
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             WHERE c.instructor_id = ?",
            [$instructorId]
        );

        // Total revenue
        $stats['total_revenue'] = (float) $db->fetchColumn(
            "SELECT COALESCE(SUM(p.amount), 0)
             FROM payments p
             JOIN courses c ON p.course_id = c.id
             WHERE c.instructor_id = ? AND p.payment_status = 'Completed'",
            [$instructorId]
        );

        // Average course rating
        $stats['avg_rating'] = (float) $db->fetchColumn(
            "SELECT AVG(r.rating)
             FROM course_reviews r
             JOIN courses c ON r.course_id = c.id
             WHERE c.instructor_id = ?",
            [$instructorId]
        );

        // Pending submissions to grade
        $stats['pending_submissions'] = (int) $db->fetchColumn(
            "SELECT COUNT(*)
             FROM assignment_submissions s
             JOIN assignments a ON s.assignment_id = a.id
             JOIN courses c ON a.course_id = c.id
             WHERE c.instructor_id = ? AND s.status = 'submitted'",
            [$instructorId]
        );

        return $stats;
    }

    /**
     * Get student statistics
     */
    public static function getStudentStats($studentId) {
        $db = self::getDb();

        $stats = [];

        // Enrolled courses
        $stats['enrolled_courses'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM enrollments WHERE user_id = ?",
            [$studentId]
        );

        // Completed courses (uses correct schema enum value: 'Completed')
        $stats['completed_courses'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND enrollment_status = 'Completed'",
            [$studentId]
        );

        // In progress courses (uses correct schema enum values: 'Enrolled', 'In Progress')
        $stats['in_progress_courses'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND enrollment_status IN ('Enrolled', 'In Progress')",
            [$studentId]
        );

        // Total certificates (certificates are linked via enrollments)
        $stats['total_certificates'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM certificates c
             JOIN enrollments e ON c.enrollment_id = e.id
             WHERE e.user_id = ?",
            [$studentId]
        );

        // Average progress across all courses (uses correct field name: 'progress')
        $stats['avg_progress'] = (float) $db->fetchColumn(
            "SELECT AVG(progress) FROM enrollments WHERE user_id = ?",
            [$studentId]
        );

        // Total quiz attempts (using student_id from students table)
        $stats['total_quiz_attempts'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM quiz_attempts qa
             JOIN students s ON qa.student_id = s.id
             WHERE s.user_id = ?",
            [$studentId]
        );

        // Average quiz score (using student_id from students table)
        $stats['avg_quiz_score'] = (float) $db->fetchColumn(
            "SELECT AVG(score) FROM quiz_attempts qa
             JOIN students s ON qa.student_id = s.id
             WHERE s.user_id = ?",
            [$studentId]
        );

        // Total assignments submitted (using student_id from students table)
        $stats['assignments_submitted'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM assignment_submissions asub
             JOIN students s ON asub.student_id = s.id
             WHERE s.user_id = ?",
            [$studentId]
        );

        return $stats;
    }

    /**
     * Get dashboard summary (admin)
     */
    public static function getAdminDashboard() {
        return [
            'users' => [
                'total' => self::getTotalStudents() + self::getTotalInstructors() + self::getTotalAdmins(),
                'students' => self::getTotalStudents(),
                'instructors' => self::getTotalInstructors(),
                'admins' => self::getTotalAdmins(),
                'active' => self::getActiveUsers()
            ],
            'courses' => [
                'total' => self::getTotalCourses(),
                'published' => self::getPublishedCourses(),
                'draft' => self::getDraftCourses()
            ],
            'enrollments' => [
                'total' => self::getTotalEnrollments(),
                'active' => self::getActiveEnrollments(),
                'completed' => self::getCompletedEnrollments()
            ],
            'revenue' => [
                'total' => self::getTotalRevenue(),
                'pending_payments' => self::getPendingPayments()
            ],
            'certificates' => [
                'total' => self::getTotalCertificates()
            ]
        ];
    }

    /**
     * Get enrollment trends
     */
    public static function getEnrollmentTrends($months = 6) {
        $db = self::getDb();
        return $db->fetchAll("
            SELECT
                DATE_FORMAT(enrolled_at, '%Y-%m') as month,
                COUNT(*) as enrollments
            FROM enrollments
            WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(enrolled_at, '%Y-%m')
            ORDER BY month ASC
        ", [$months]);
    }

    /**
     * Get popular courses
     */
    public static function getPopularCourses($limit = 10) {
        $db = self::getDb();
        return $db->fetchAll("
            SELECT c.*,
                   COUNT(e.id) as enrollment_count,
                   AVG(r.rating) as avg_rating,
                   u.first_name as instructor_first_name,
                   u.last_name as instructor_last_name
            FROM courses c
            LEFT JOIN enrollments e ON c.id = e.course_id
            LEFT JOIN course_reviews r ON c.id = r.course_id
            LEFT JOIN users u ON c.instructor_id = u.id
            WHERE c.status = 'published'
            GROUP BY c.id
            ORDER BY enrollment_count DESC
            LIMIT ?
        ", [$limit]);
    }

    /**
     * Get top performing students
     * Uses correct schema enum value: 'Completed' and field name: 'progress'
     */
    public static function getTopStudents($limit = 10) {
        $db = self::getDb();
        return $db->fetchAll("
            SELECT u.id, u.first_name, u.last_name, u.email,
                   COUNT(DISTINCT e.course_id) as courses_enrolled,
                   COUNT(DISTINCT c.id) as courses_completed,
                   AVG(e.progress) as avg_progress,
                   COUNT(DISTINCT cert.certificate_id) as certificates_earned
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.id
            LEFT JOIN enrollments e ON u.id = e.user_id
            LEFT JOIN enrollments c ON u.id = c.user_id AND c.enrollment_status = 'Completed'
            LEFT JOIN certificates cert ON c.id = cert.enrollment_id
            WHERE r.role_name = 'Student'
            GROUP BY u.id
            ORDER BY certificates_earned DESC, avg_progress DESC
            LIMIT ?
        ", [$limit]);
    }
}
