<?php
/**
 * Student Interface Comprehensive Test Suite
 * Tests all student-facing pages, queries, and features
 *
 * Usage: php tests/test-student-interface.php
 * Or via browser: http://localhost/edutrack-lms/tests/test-student-interface.php
 */

// Define paths
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

// Skip security headers for CLI/test mode
if (!defined('SKIP_SECURITY_HEADERS')) {
    define('SKIP_SECURITY_HEADERS', true);
}

// Load only the essential includes for testing
require_once SRC_PATH . '/includes/config.php';
require_once SRC_PATH . '/includes/database.php';
require_once SRC_PATH . '/includes/functions.php';

// Test configuration
define('TEST_PREFIX', 'TEST_STU_' . time() . '_');
define('BASE_URL', 'http://localhost/edutrack-lms/public');

// Global counters
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$skippedTests = 0;
$results = [];
$testDataIds = []; // Track created test data for cleanup

/**
 * Test result helper
 */
function recordResult($category, $name, $status, $message = '', $details = []) {
    global $results, $totalTests, $passedTests, $failedTests, $skippedTests;

    $totalTests++;

    if ($status === 'PASSED') {
        $passedTests++;
        $icon = '✅';
    } elseif ($status === 'FAILED') {
        $failedTests++;
        $icon = '❌';
    } else {
        $skippedTests++;
        $icon = '⏭️';
    }

    $results[] = [
        'category' => $category,
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'details' => $details
    ];

    echo "$icon [$category] $name";
    if ($message) {
        echo " - $message";
    }
    echo "\n";

    if (!empty($details) && $status === 'FAILED') {
        foreach ($details as $key => $value) {
            echo "   $key: $value\n";
        }
    }

    return $status === 'PASSED';
}

/**
 * Database helper
 */
function getDb() {
    return Database::getInstance();
}

// ============================================================================
// SECTION 1: DATABASE CONNECTION TESTS
// ============================================================================

function testDatabaseConnection() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 1: DATABASE CONNECTION TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    try {
        $db = getDb();
        $result = $db->fetchColumn("SELECT 1");
        recordResult('Database', 'Connection Test', $result == 1 ? 'PASSED' : 'FAILED');
    } catch (Exception $e) {
        recordResult('Database', 'Connection Test', 'FAILED', $e->getMessage());
    }

    // Test each student-related table exists
    $tables = [
        'users', 'roles', 'user_roles', 'students',
        'courses', 'course_categories', 'course_modules', 'lessons',
        'enrollments', 'lesson_progress',
        'assignments', 'assignment_submissions',
        'quizzes', 'quiz_attempts', 'quiz_answers',
        'certificates', 'notifications', 'payments'
    ];

    foreach ($tables as $table) {
        try {
            $db = getDb();
            $count = $db->fetchColumn("SELECT COUNT(*) FROM $table");
            recordResult('Database', "Table '$table' exists", 'PASSED', "$count records");
        } catch (Exception $e) {
            recordResult('Database', "Table '$table' exists", 'FAILED', $e->getMessage());
        }
    }
}

// ============================================================================
// SECTION 2: STUDENT DASHBOARD TESTS
// ============================================================================

function testStudentDashboard() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 2: STUDENT DASHBOARD TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // Get a test user ID (first user with student role)
    try {
        $testUserId = $db->fetchColumn("
            SELECT u.id FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.id
            WHERE r.role_name = 'Student'
            LIMIT 1
        ");

        if (!$testUserId) {
            // Fall back to any user
            $testUserId = $db->fetchColumn("SELECT id FROM users LIMIT 1");
        }

        recordResult('Dashboard', 'Get test user', $testUserId ? 'PASSED' : 'FAILED', "User ID: $testUserId");
    } catch (Exception $e) {
        recordResult('Dashboard', 'Get test user', 'FAILED', $e->getMessage());
        return; // Can't continue without a test user
    }

    // Test: Get recent enrollments query (from dashboard.php)
    try {
        $enrollments = $db->fetchAll("
            SELECT e.*, c.title, c.slug, c.thumbnail, c.description,
                   e.last_accessed, e.progress_percentage
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE e.user_id = ? AND e.enrollment_status IN ('Enrolled', 'In Progress')
            ORDER BY e.last_accessed DESC, e.enrolled_at DESC
            LIMIT 4
        ", [$testUserId]);
        recordResult('Dashboard', 'Recent enrollments query', 'PASSED', count($enrollments) . ' enrollments found');
    } catch (Exception $e) {
        recordResult('Dashboard', 'Recent enrollments query', 'FAILED', $e->getMessage());
    }

    // Test: Get upcoming deadlines query (from dashboard.php)
    try {
        $deadlines = $db->fetchAll("
            SELECT a.*, c.title as course_title, c.slug as course_slug
            FROM assignments a
            JOIN courses c ON a.course_id = c.id
            JOIN enrollments e ON e.course_id = c.id
            WHERE e.user_id = ?
            AND a.status = 'published'
            AND a.due_date > NOW()
            AND a.due_date < DATE_ADD(NOW(), INTERVAL 7 DAY)
            AND a.id NOT IN (
                SELECT assignment_id FROM assignment_submissions WHERE user_id = ?
            )
            ORDER BY a.due_date ASC
            LIMIT 5
        ", [$testUserId, $testUserId]);
        recordResult('Dashboard', 'Upcoming deadlines query', 'PASSED', count($deadlines) . ' deadlines found');
    } catch (Exception $e) {
        recordResult('Dashboard', 'Upcoming deadlines query', 'FAILED', $e->getMessage());
    }

    // Test: Get unread notifications query (from dashboard.php)
    try {
        $notifications = $db->fetchAll("
            SELECT * FROM notifications
            WHERE user_id = ? AND is_read = 0
            ORDER BY created_at DESC
            LIMIT 5
        ", [$testUserId]);
        recordResult('Dashboard', 'Unread notifications query', 'PASSED', count($notifications) . ' notifications found');
    } catch (Exception $e) {
        recordResult('Dashboard', 'Unread notifications query', 'FAILED', $e->getMessage());
    }

    // Test: Get recent quiz attempts query (from dashboard.php)
    try {
        $quizAttempts = $db->fetchAll("
            SELECT qa.*, q.title as quiz_title, c.title as course_title, c.slug as course_slug
            FROM quiz_attempts qa
            JOIN quizzes q ON qa.quiz_id = q.id
            JOIN courses c ON q.course_id = c.id
            WHERE qa.user_id = ?
            ORDER BY qa.completed_at DESC
            LIMIT 3
        ", [$testUserId]);
        recordResult('Dashboard', 'Recent quiz attempts query', 'PASSED', count($quizAttempts) . ' attempts found');
    } catch (Exception $e) {
        recordResult('Dashboard', 'Recent quiz attempts query', 'FAILED', $e->getMessage());
    }

    // Test: Get recent graded assignments query (from dashboard.php)
    try {
        $gradedAssignments = $db->fetchAll("
            SELECT asub.*, a.title as assignment_title, c.title as course_title, c.slug as course_slug
            FROM assignment_submissions asub
            JOIN assignments a ON asub.assignment_id = a.id
            JOIN courses c ON a.course_id = c.id
            WHERE asub.user_id = ? AND asub.status = 'graded'
            ORDER BY asub.graded_at DESC
            LIMIT 3
        ", [$testUserId]);
        recordResult('Dashboard', 'Recent graded assignments query', 'PASSED', count($gradedAssignments) . ' graded assignments found');
    } catch (Exception $e) {
        recordResult('Dashboard', 'Recent graded assignments query', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 3: MY COURSES TESTS
// ============================================================================

function testMyCourses() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 3: MY COURSES TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // Get a test user ID
    $testUserId = $db->fetchColumn("SELECT id FROM users LIMIT 1");

    // Test: Get enrollments with detailed statistics (from my-courses.php)
    try {
        $enrollments = $db->fetchAll("
            SELECT e.*, c.title, c.slug, c.thumbnail, c.description, c.price,
                   c.instructor_id,
                   u.first_name as instructor_first_name, u.last_name as instructor_last_name,
                   e.enrolled_at, e.last_accessed, e.progress_percentage, e.enrollment_status,
                   COUNT(DISTINCT l.id) as total_lessons,
                   COUNT(DISTINCT lp.lesson_id) as completed_lessons,
                   COUNT(DISTINCT a.id) as total_assignments,
                   COUNT(DISTINCT asub.id) as submitted_assignments,
                   COUNT(DISTINCT q.id) as total_quizzes,
                   COUNT(DISTINCT qa.id) as attempted_quizzes
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            LEFT JOIN instructors i ON c.instructor_id = i.id
            LEFT JOIN users u ON i.user_id = u.id
            LEFT JOIN course_modules m ON c.id = m.course_id
            LEFT JOIN lessons l ON m.id = l.module_id
            LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = e.user_id AND lp.status = 'completed'
            LEFT JOIN assignments a ON c.id = a.course_id AND a.status = 'published'
            LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.user_id = e.user_id
            LEFT JOIN quizzes q ON c.id = q.course_id AND q.status = 'published'
            LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.user_id = e.user_id
            WHERE e.user_id = ?
            GROUP BY e.id, c.id
            ORDER BY e.last_accessed DESC, e.enrolled_at DESC
        ", [$testUserId]);
        recordResult('My Courses', 'Enrollments with statistics query', 'PASSED', count($enrollments) . ' enrollments found');
    } catch (Exception $e) {
        recordResult('My Courses', 'Enrollments with statistics query', 'FAILED', $e->getMessage());
    }

    // Test: Get module completion (from my-courses.php)
    try {
        $courseId = $db->fetchColumn("SELECT id FROM courses LIMIT 1");
        if ($courseId) {
            $modules = $db->fetchAll("
                SELECT m.id, m.title,
                       COUNT(DISTINCT l.id) as total_lessons,
                       COUNT(DISTINCT lp.lesson_id) as completed_lessons
                FROM course_modules m
                LEFT JOIN lessons l ON m.id = l.module_id
                LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ? AND lp.status = 'completed'
                WHERE m.course_id = ?
                GROUP BY m.id
                ORDER BY m.display_order ASC
            ", [$testUserId, $courseId]);
            recordResult('My Courses', 'Module completion query', 'PASSED', count($modules) . ' modules found');
        } else {
            recordResult('My Courses', 'Module completion query', 'SKIPPED', 'No courses found');
        }
    } catch (Exception $e) {
        recordResult('My Courses', 'Module completion query', 'FAILED', $e->getMessage());
    }

    // Test: Count by status queries (from my-courses.php)
    try {
        $allCount = (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ?", [$testUserId]);
        recordResult('My Courses', 'Count all enrollments', 'PASSED', "$allCount total");
    } catch (Exception $e) {
        recordResult('My Courses', 'Count all enrollments', 'FAILED', $e->getMessage());
    }

    try {
        $activeCount = (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND enrollment_status IN ('Enrolled', 'In Progress')", [$testUserId]);
        recordResult('My Courses', 'Count active enrollments', 'PASSED', "$activeCount active");
    } catch (Exception $e) {
        recordResult('My Courses', 'Count active enrollments', 'FAILED', $e->getMessage());
    }

    try {
        $completedCount = (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND enrollment_status = 'Completed'", [$testUserId]);
        recordResult('My Courses', 'Count completed enrollments', 'PASSED', "$completedCount completed");
    } catch (Exception $e) {
        recordResult('My Courses', 'Count completed enrollments', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 4: ASSIGNMENTS TESTS
// ============================================================================

function testAssignments() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 4: ASSIGNMENTS TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();
    $testUserId = $db->fetchColumn("SELECT id FROM users LIMIT 1");

    // Test: Get all assignments from enrolled courses (from student/assignments.php)
    try {
        $assignments = $db->fetchAll("
            SELECT a.*,
                   c.title as course_title, c.slug as course_slug,
                   asub.id as submission_id,
                   asub.status as submission_status,
                   asub.submitted_at,
                   asub.points_earned,
                   asub.feedback,
                   asub.graded_at,
                   asub.file_path as submission_file,
                   e.id as enrollment_id
            FROM assignments a
            JOIN courses c ON a.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.user_id = ?
            WHERE a.status = 'published'
            ORDER BY a.due_date ASC, a.created_at DESC
        ", [$testUserId, $testUserId]);
        recordResult('Assignments', 'Get all assignments query', 'PASSED', count($assignments) . ' assignments found');
    } catch (Exception $e) {
        recordResult('Assignments', 'Get all assignments query', 'FAILED', $e->getMessage());
    }

    // Test: Count all assignments
    try {
        $allCount = count($db->fetchAll("
            SELECT a.id FROM assignments a
            JOIN courses c ON a.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            WHERE a.status = 'published'
        ", [$testUserId]));
        recordResult('Assignments', 'Count all assignments', 'PASSED', "$allCount total");
    } catch (Exception $e) {
        recordResult('Assignments', 'Count all assignments', 'FAILED', $e->getMessage());
    }

    // Test: Count pending assignments
    try {
        $pendingCount = count($db->fetchAll("
            SELECT a.id FROM assignments a
            JOIN courses c ON a.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            WHERE a.status = 'published'
            AND a.id NOT IN (SELECT assignment_id FROM assignment_submissions WHERE user_id = ?)
        ", [$testUserId, $testUserId]));
        recordResult('Assignments', 'Count pending assignments', 'PASSED', "$pendingCount pending");
    } catch (Exception $e) {
        recordResult('Assignments', 'Count pending assignments', 'FAILED', $e->getMessage());
    }

    // Test: Count submitted assignments
    try {
        $submittedCount = count($db->fetchAll("
            SELECT a.id FROM assignments a
            JOIN courses c ON a.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.user_id = ?
            WHERE a.status = 'published' AND asub.status = 'submitted'
        ", [$testUserId, $testUserId]));
        recordResult('Assignments', 'Count submitted assignments', 'PASSED', "$submittedCount submitted");
    } catch (Exception $e) {
        recordResult('Assignments', 'Count submitted assignments', 'FAILED', $e->getMessage());
    }

    // Test: Count graded assignments
    try {
        $gradedCount = count($db->fetchAll("
            SELECT a.id FROM assignments a
            JOIN courses c ON a.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.user_id = ?
            WHERE a.status = 'published' AND asub.status = 'graded'
        ", [$testUserId, $testUserId]));
        recordResult('Assignments', 'Count graded assignments', 'PASSED', "$gradedCount graded");
    } catch (Exception $e) {
        recordResult('Assignments', 'Count graded assignments', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 5: QUIZZES TESTS
// ============================================================================

function testQuizzes() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 5: QUIZZES TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();
    $testUserId = $db->fetchColumn("SELECT id FROM users LIMIT 1");

    // Test: Get all quizzes from enrolled courses (from student/quizzes.php)
    try {
        $quizzes = $db->fetchAll("
            SELECT q.*,
                   c.title as course_title, c.slug as course_slug,
                   COUNT(DISTINCT qa.id) as attempt_count,
                   MAX(qa.score) as best_score,
                   MAX(qa.completed_at) as last_attempt,
                   AVG(qa.score) as avg_score,
                   e.id as enrollment_id
            FROM quizzes q
            JOIN courses c ON q.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.user_id = ?
            WHERE q.status = 'published'
            GROUP BY q.id
            ORDER BY q.created_at DESC
        ", [$testUserId, $testUserId]);
        recordResult('Quizzes', 'Get all quizzes query', 'PASSED', count($quizzes) . ' quizzes found');
    } catch (Exception $e) {
        recordResult('Quizzes', 'Get all quizzes query', 'FAILED', $e->getMessage());
    }

    // Test: Count all quizzes
    try {
        $allCount = count($db->fetchAll("
            SELECT q.id FROM quizzes q
            JOIN courses c ON q.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            WHERE q.status = 'published'
        ", [$testUserId]));
        recordResult('Quizzes', 'Count all quizzes', 'PASSED', "$allCount total");
    } catch (Exception $e) {
        recordResult('Quizzes', 'Count all quizzes', 'FAILED', $e->getMessage());
    }

    // Test: Count pending (not attempted) quizzes
    try {
        $pendingCount = count($db->fetchAll("
            SELECT q.id FROM quizzes q
            JOIN courses c ON q.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            WHERE q.status = 'published'
            AND q.id NOT IN (SELECT DISTINCT quiz_id FROM quiz_attempts WHERE user_id = ?)
        ", [$testUserId, $testUserId]));
        recordResult('Quizzes', 'Count pending quizzes', 'PASSED', "$pendingCount pending");
    } catch (Exception $e) {
        recordResult('Quizzes', 'Count pending quizzes', 'FAILED', $e->getMessage());
    }

    // Test: Count completed quizzes
    try {
        $completedCount = count($db->fetchAll("
            SELECT DISTINCT q.id FROM quizzes q
            JOIN courses c ON q.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.user_id = ?
            WHERE q.status = 'published'
        ", [$testUserId, $testUserId]));
        recordResult('Quizzes', 'Count completed quizzes', 'PASSED', "$completedCount completed");
    } catch (Exception $e) {
        recordResult('Quizzes', 'Count completed quizzes', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 6: LESSON PROGRESS TESTS
// ============================================================================

function testLessonProgress() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 6: LESSON PROGRESS TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();
    $testUserId = $db->fetchColumn("SELECT id FROM users LIMIT 1");

    // Test: Get lesson progress for a course
    try {
        $courseId = $db->fetchColumn("SELECT id FROM courses LIMIT 1");
        if ($courseId) {
            $progress = $db->fetchAll("
                SELECT lp.*, l.title as lesson_title, m.title as module_title
                FROM lesson_progress lp
                JOIN lessons l ON lp.lesson_id = l.id
                JOIN course_modules m ON l.module_id = m.id
                WHERE lp.user_id = ? AND m.course_id = ?
                ORDER BY m.display_order, l.display_order
            ", [$testUserId, $courseId]);
            recordResult('Lesson Progress', 'Get lesson progress', 'PASSED', count($progress) . ' progress records found');
        } else {
            recordResult('Lesson Progress', 'Get lesson progress', 'SKIPPED', 'No courses found');
        }
    } catch (Exception $e) {
        recordResult('Lesson Progress', 'Get lesson progress', 'FAILED', $e->getMessage());
    }

    // Test: Count completed lessons
    try {
        $completedCount = (int) $db->fetchColumn("
            SELECT COUNT(*) FROM lesson_progress
            WHERE user_id = ? AND status = 'completed'
        ", [$testUserId]);
        recordResult('Lesson Progress', 'Count completed lessons', 'PASSED', "$completedCount completed");
    } catch (Exception $e) {
        recordResult('Lesson Progress', 'Count completed lessons', 'FAILED', $e->getMessage());
    }

    // Test: Calculate overall progress percentage
    try {
        $courseId = $db->fetchColumn("SELECT id FROM courses LIMIT 1");
        if ($courseId) {
            $totalLessons = (int) $db->fetchColumn("
                SELECT COUNT(*) FROM lessons l
                JOIN course_modules m ON l.module_id = m.id
                WHERE m.course_id = ?
            ", [$courseId]);

            $completedLessons = (int) $db->fetchColumn("
                SELECT COUNT(*) FROM lesson_progress lp
                JOIN lessons l ON lp.lesson_id = l.id
                JOIN course_modules m ON l.module_id = m.id
                WHERE lp.user_id = ? AND m.course_id = ? AND lp.status = 'completed'
            ", [$testUserId, $courseId]);

            $percentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;
            recordResult('Lesson Progress', 'Calculate progress percentage', 'PASSED', "$percentage% ($completedLessons/$totalLessons)");
        } else {
            recordResult('Lesson Progress', 'Calculate progress percentage', 'SKIPPED', 'No courses found');
        }
    } catch (Exception $e) {
        recordResult('Lesson Progress', 'Calculate progress percentage', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 7: CERTIFICATES TESTS
// ============================================================================

function testCertificates() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 7: CERTIFICATES TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();
    $testUserId = $db->fetchColumn("SELECT id FROM users LIMIT 1");

    // Test: Get user certificates
    try {
        $certificates = $db->fetchAll("
            SELECT cert.*, c.title as course_title, c.slug as course_slug
            FROM certificates cert
            JOIN enrollments e ON cert.enrollment_id = e.id
            JOIN courses c ON e.course_id = c.id
            WHERE e.user_id = ?
            ORDER BY cert.issued_date DESC
        ", [$testUserId]);
        recordResult('Certificates', 'Get user certificates', 'PASSED', count($certificates) . ' certificates found');
    } catch (Exception $e) {
        recordResult('Certificates', 'Get user certificates', 'FAILED', $e->getMessage());
    }

    // Test: Count total certificates
    try {
        $count = (int) $db->fetchColumn("
            SELECT COUNT(*) FROM certificates cert
            JOIN enrollments e ON cert.enrollment_id = e.id
            WHERE e.user_id = ?
        ", [$testUserId]);
        recordResult('Certificates', 'Count certificates', 'PASSED', "$count total");
    } catch (Exception $e) {
        recordResult('Certificates', 'Count certificates', 'FAILED', $e->getMessage());
    }

    // Test: Get eligible for certificate (completed courses without certificate)
    try {
        $eligible = $db->fetchAll("
            SELECT e.*, c.title as course_title
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            LEFT JOIN certificates cert ON e.id = cert.enrollment_id
            WHERE e.user_id = ? AND e.enrollment_status = 'Completed' AND cert.certificate_id IS NULL
        ", [$testUserId]);
        recordResult('Certificates', 'Get eligible for certificate', 'PASSED', count($eligible) . ' eligible');
    } catch (Exception $e) {
        recordResult('Certificates', 'Get eligible for certificate', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 8: PAYMENTS TESTS
// ============================================================================

function testPayments() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 8: PAYMENTS TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();
    $testUserId = $db->fetchColumn("SELECT id FROM users LIMIT 1");

    // Test: Get user payment history
    try {
        $payments = $db->fetchAll("
            SELECT p.*, c.title as course_title
            FROM payments p
            JOIN courses c ON p.course_id = c.id
            JOIN students st ON p.student_id = st.id
            WHERE st.user_id = ?
            ORDER BY p.created_at DESC
        ", [$testUserId]);
        recordResult('Payments', 'Get payment history', 'PASSED', count($payments) . ' payments found');
    } catch (Exception $e) {
        recordResult('Payments', 'Get payment history', 'FAILED', $e->getMessage());
    }

    // Test: Get total spent
    try {
        $totalSpent = (float) $db->fetchColumn("
            SELECT COALESCE(SUM(p.amount), 0)
            FROM payments p
            JOIN students st ON p.student_id = st.id
            WHERE st.user_id = ? AND p.payment_status = 'Completed'
        ", [$testUserId]);
        recordResult('Payments', 'Get total spent', 'PASSED', "Total: $totalSpent");
    } catch (Exception $e) {
        recordResult('Payments', 'Get total spent', 'FAILED', $e->getMessage());
    }

    // Test: Count pending payments
    try {
        $pendingCount = (int) $db->fetchColumn("
            SELECT COUNT(*)
            FROM payments p
            JOIN students st ON p.student_id = st.id
            WHERE st.user_id = ? AND p.payment_status = 'Pending'
        ", [$testUserId]);
        recordResult('Payments', 'Count pending payments', 'PASSED', "$pendingCount pending");
    } catch (Exception $e) {
        recordResult('Payments', 'Count pending payments', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 9: NOTIFICATIONS TESTS
// ============================================================================

function testNotifications() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 9: NOTIFICATIONS TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();
    $testUserId = $db->fetchColumn("SELECT id FROM users LIMIT 1");

    // Test: Get all notifications
    try {
        $notifications = $db->fetchAll("
            SELECT * FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 20
        ", [$testUserId]);
        recordResult('Notifications', 'Get all notifications', 'PASSED', count($notifications) . ' notifications found');
    } catch (Exception $e) {
        recordResult('Notifications', 'Get all notifications', 'FAILED', $e->getMessage());
    }

    // Test: Count unread notifications
    try {
        $unreadCount = (int) $db->fetchColumn("
            SELECT COUNT(*) FROM notifications
            WHERE user_id = ? AND is_read = 0
        ", [$testUserId]);
        recordResult('Notifications', 'Count unread notifications', 'PASSED', "$unreadCount unread");
    } catch (Exception $e) {
        recordResult('Notifications', 'Count unread notifications', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 10: COURSE BROWSING TESTS
// ============================================================================

function testCourseBrowsing() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 10: COURSE BROWSING TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // Test: Get all published courses
    try {
        $courses = $db->fetchAll("
            SELECT c.*, cat.name as category_name,
                   u.first_name as instructor_first_name, u.last_name as instructor_last_name,
                   COUNT(DISTINCT e.id) as enrollment_count
            FROM courses c
            LEFT JOIN course_categories cat ON c.category_id = cat.id
            LEFT JOIN instructors i ON c.instructor_id = i.id
            LEFT JOIN users u ON i.user_id = u.id
            LEFT JOIN enrollments e ON c.id = e.course_id
            WHERE c.status = 'published'
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        recordResult('Course Browsing', 'Get published courses', 'PASSED', count($courses) . ' courses found');
    } catch (Exception $e) {
        recordResult('Course Browsing', 'Get published courses', 'FAILED', $e->getMessage());
    }

    // Test: Get courses by category
    try {
        $categoryId = $db->fetchColumn("SELECT id FROM course_categories LIMIT 1");
        if ($categoryId) {
            $courses = $db->fetchAll("
                SELECT c.*, cat.name as category_name
                FROM courses c
                JOIN course_categories cat ON c.category_id = cat.id
                WHERE c.status = 'published' AND c.category_id = ?
            ", [$categoryId]);
            recordResult('Course Browsing', 'Get courses by category', 'PASSED', count($courses) . ' courses found');
        } else {
            recordResult('Course Browsing', 'Get courses by category', 'SKIPPED', 'No categories found');
        }
    } catch (Exception $e) {
        recordResult('Course Browsing', 'Get courses by category', 'FAILED', $e->getMessage());
    }

    // Test: Search courses
    try {
        $courses = $db->fetchAll("
            SELECT c.* FROM courses c
            WHERE c.status = 'published'
            AND (c.title LIKE ? OR c.description LIKE ?)
        ", ['%test%', '%test%']);
        recordResult('Course Browsing', 'Search courses', 'PASSED', count($courses) . ' results');
    } catch (Exception $e) {
        recordResult('Course Browsing', 'Search courses', 'FAILED', $e->getMessage());
    }

    // Test: Get featured courses
    try {
        $featured = $db->fetchAll("
            SELECT c.* FROM courses c
            WHERE c.status = 'published' AND c.is_featured = 1
            ORDER BY c.created_at DESC
            LIMIT 6
        ");
        recordResult('Course Browsing', 'Get featured courses', 'PASSED', count($featured) . ' featured courses');
    } catch (Exception $e) {
        recordResult('Course Browsing', 'Get featured courses', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// RUN ALL TESTS
// ============================================================================

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║           STUDENT INTERFACE COMPREHENSIVE TEST SUITE                          ║\n";
echo "║                      EduTrack LMS - Test Runner                               ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════════╝\n";
echo "\nStarted at: " . date('Y-m-d H:i:s') . "\n";

// Run all test sections
testDatabaseConnection();
testStudentDashboard();
testMyCourses();
testAssignments();
testQuizzes();
testLessonProgress();
testCertificates();
testPayments();
testNotifications();
testCourseBrowsing();

// ============================================================================
// SUMMARY
// ============================================================================

echo "\n" . str_repeat('=', 80) . "\n";
echo "TEST SUMMARY\n";
echo str_repeat('=', 80) . "\n\n";

echo "Total Tests: $totalTests\n";
echo "✅ Passed: $passedTests\n";
echo "❌ Failed: $failedTests\n";
echo "⏭️ Skipped: $skippedTests\n";
echo "\n";

$successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;
echo "Success Rate: $successRate%\n";

if ($failedTests > 0) {
    echo "\n" . str_repeat('-', 80) . "\n";
    echo "FAILED TESTS:\n";
    echo str_repeat('-', 80) . "\n\n";

    foreach ($results as $result) {
        if ($result['status'] === 'FAILED') {
            echo "❌ [{$result['category']}] {$result['name']}\n";
            if ($result['message']) {
                echo "   Error: {$result['message']}\n";
            }
        }
    }
}

echo "\nCompleted at: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('=', 80) . "\n";

// Exit with appropriate code
exit($failedTests > 0 ? 1 : 0);
