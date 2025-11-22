<?php
/**
 * Instructor Panel Test Suite
 * Tests instructor dashboard, courses, students, and analytics pages
 *
 * Usage: php tests/test-instructor-panel.php
 * Or via browser: http://localhost/edutrack-lms/tests/test-instructor-panel.php
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
define('TEST_PREFIX', 'INST_TEST_' . time() . '_');

// Global counters
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$skippedTests = 0;
$results = [];

/**
 * Test result helper
 */
function recordResult($category, $name, $status, $message = '') {
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
    ];

    echo "$icon [$category] $name";
    if ($message) {
        echo " - $message";
    }
    echo "\n";

    return $status === 'PASSED';
}

/**
 * Database helper
 */
function getDb() {
    return Database::getInstance();
}

// ============================================================================
// SECTION 1: DATABASE CONNECTION
// ============================================================================

function testDatabaseConnection() {
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "SECTION 1: DATABASE CONNECTION\n";
    echo str_repeat('=', 70) . "\n\n";

    try {
        $db = getDb();
        $result = $db->fetchColumn("SELECT 1");
        recordResult('Database', 'Connection Test', $result == 1 ? 'PASSED' : 'FAILED');
    } catch (Exception $e) {
        recordResult('Database', 'Connection Test', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 2: INSTRUCTOR STATISTICS QUERIES
// ============================================================================

function testInstructorStatistics() {
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "SECTION 2: INSTRUCTOR STATISTICS QUERIES\n";
    echo str_repeat('=', 70) . "\n\n";

    $db = getDb();

    // Find an instructor to test with
    try {
        $instructor = $db->fetchOne("
            SELECT u.id FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.id
            WHERE r.role_name = 'Instructor'
            LIMIT 1
        ");

        if (!$instructor) {
            recordResult('Statistics', 'Find instructor', 'SKIPPED', 'No instructor found in database');
            return;
        }

        $instructorId = $instructor['id'];
        recordResult('Statistics', 'Find instructor', 'PASSED', "Instructor ID: $instructorId");
    } catch (Exception $e) {
        recordResult('Statistics', 'Find instructor', 'FAILED', $e->getMessage());
        return;
    }

    // Test total courses query
    try {
        $count = $db->fetchColumn(
            "SELECT COUNT(*) FROM courses WHERE instructor_id = ?",
            [$instructorId]
        );
        recordResult('Statistics', 'Total courses query', 'PASSED', "$count courses");
    } catch (Exception $e) {
        recordResult('Statistics', 'Total courses query', 'FAILED', $e->getMessage());
    }

    // Test published courses query
    try {
        $count = $db->fetchColumn(
            "SELECT COUNT(*) FROM courses WHERE instructor_id = ? AND status = 'published'",
            [$instructorId]
        );
        recordResult('Statistics', 'Published courses query', 'PASSED', "$count published");
    } catch (Exception $e) {
        recordResult('Statistics', 'Published courses query', 'FAILED', $e->getMessage());
    }

    // Test total students query
    try {
        $count = $db->fetchColumn("
            SELECT COUNT(DISTINCT e.user_id)
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE c.instructor_id = ?
        ", [$instructorId]);
        recordResult('Statistics', 'Total students query', 'PASSED', "$count students");
    } catch (Exception $e) {
        recordResult('Statistics', 'Total students query', 'FAILED', $e->getMessage());
    }

    // Test revenue query
    try {
        $revenue = $db->fetchOne("
            SELECT
                COALESCE(SUM(CASE WHEN e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN c.price ELSE 0 END), 0) as monthly_revenue,
                COALESCE(SUM(c.price), 0) as total_revenue
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE c.instructor_id = ? AND e.payment_status = 'completed'
        ", [$instructorId]);
        recordResult('Statistics', 'Revenue query', 'PASSED', "Monthly: {$revenue['monthly_revenue']}, Total: {$revenue['total_revenue']}");
    } catch (Exception $e) {
        recordResult('Statistics', 'Revenue query', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 3: INSTRUCTOR COURSES PAGE QUERIES
// ============================================================================

function testInstructorCourses() {
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "SECTION 3: INSTRUCTOR COURSES PAGE QUERIES\n";
    echo str_repeat('=', 70) . "\n\n";

    $db = getDb();

    // Get any instructor
    $instructor = $db->fetchOne("
        SELECT u.id FROM users u
        INNER JOIN user_roles ur ON u.id = ur.user_id
        INNER JOIN roles r ON ur.role_id = r.id
        WHERE r.role_name = 'Instructor'
        LIMIT 1
    ");

    if (!$instructor) {
        recordResult('Courses', 'Test courses list', 'SKIPPED', 'No instructor found');
        return;
    }

    $instructorId = $instructor['id'];

    // Test courses list query
    try {
        $courses = $db->fetchAll("
            SELECT c.*, COUNT(DISTINCT e.id) as student_count,
                   COUNT(DISTINCT m.id) as module_count,
                   COUNT(DISTINCT l.id) as lesson_count
            FROM courses c
            LEFT JOIN enrollments e ON c.id = e.course_id
            LEFT JOIN course_modules m ON c.id = m.course_id
            LEFT JOIN lessons l ON m.id = l.module_id
            WHERE c.instructor_id = ?
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ", [$instructorId]);
        recordResult('Courses', 'Courses with stats query', 'PASSED', count($courses) . " courses");
    } catch (Exception $e) {
        recordResult('Courses', 'Courses with stats query', 'FAILED', $e->getMessage());
    }

    // Test thumbnail_url column exists
    try {
        $course = $db->fetchOne("SELECT thumbnail_url FROM courses LIMIT 1");
        recordResult('Courses', 'thumbnail_url column exists', 'PASSED');
    } catch (Exception $e) {
        recordResult('Courses', 'thumbnail_url column exists', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 4: INSTRUCTOR STUDENTS PAGE QUERIES
// ============================================================================

function testInstructorStudents() {
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "SECTION 4: INSTRUCTOR STUDENTS PAGE QUERIES\n";
    echo str_repeat('=', 70) . "\n\n";

    $db = getDb();

    // Get any instructor
    $instructor = $db->fetchOne("
        SELECT u.id FROM users u
        INNER JOIN user_roles ur ON u.id = ur.user_id
        INNER JOIN roles r ON ur.role_id = r.id
        WHERE r.role_name = 'Instructor'
        LIMIT 1
    ");

    if (!$instructor) {
        recordResult('Students', 'Test students list', 'SKIPPED', 'No instructor found');
        return;
    }

    $instructorId = $instructor['id'];

    // Test students query
    try {
        $students = $db->fetchAll("
            SELECT DISTINCT u.id, u.first_name, u.last_name, u.email, u.created_at,
                   COUNT(DISTINCT e.id) as enrolled_courses,
                   AVG(e.progress) as avg_progress,
                   SUM(CASE WHEN e.enrollment_status = 'Completed' THEN 1 ELSE 0 END) as completed_courses
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            JOIN users u ON e.user_id = u.id
            WHERE c.instructor_id = ?
            GROUP BY u.id, u.first_name, u.last_name, u.email, u.created_at
            ORDER BY u.first_name, u.last_name
            LIMIT 10
        ", [$instructorId]);
        recordResult('Students', 'Students list query', 'PASSED', count($students) . " students");
    } catch (Exception $e) {
        recordResult('Students', 'Students list query', 'FAILED', $e->getMessage());
    }

    // Test active enrollments count
    try {
        $count = $db->fetchColumn("
            SELECT COUNT(*) FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE c.instructor_id = ? AND e.enrollment_status IN ('Enrolled', 'In Progress')
        ", [$instructorId]);
        recordResult('Students', 'Active enrollments count', 'PASSED', "$count active");
    } catch (Exception $e) {
        recordResult('Students', 'Active enrollments count', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 5: INSTRUCTOR ANALYTICS PAGE QUERIES
// ============================================================================

function testInstructorAnalytics() {
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "SECTION 5: INSTRUCTOR ANALYTICS PAGE QUERIES\n";
    echo str_repeat('=', 70) . "\n\n";

    $db = getDb();

    // Get any instructor
    $instructor = $db->fetchOne("
        SELECT u.id FROM users u
        INNER JOIN user_roles ur ON u.id = ur.user_id
        INNER JOIN roles r ON ur.role_id = r.id
        WHERE r.role_name = 'Instructor'
        LIMIT 1
    ");

    if (!$instructor) {
        recordResult('Analytics', 'Test analytics', 'SKIPPED', 'No instructor found');
        return;
    }

    $instructorId = $instructor['id'];

    // Test course metrics query
    try {
        $metrics = $db->fetchAll("
            SELECT c.id, c.title, c.slug, c.status, c.price, c.created_at,
                   COUNT(DISTINCT e.id) as total_enrollments,
                   COUNT(DISTINCT CASE WHEN e.enrollment_status = 'Completed' THEN e.id END) as completions,
                   AVG(e.progress) as avg_progress,
                   AVG(cr.rating) as avg_rating,
                   COUNT(DISTINCT cr.id) as review_count
            FROM courses c
            LEFT JOIN enrollments e ON c.id = e.course_id
            LEFT JOIN course_reviews cr ON c.id = cr.course_id
            WHERE c.instructor_id = ?
            GROUP BY c.id, c.title, c.slug, c.status, c.price, c.created_at
            ORDER BY total_enrollments DESC
        ", [$instructorId]);
        recordResult('Analytics', 'Course metrics query', 'PASSED', count($metrics) . " courses");
    } catch (Exception $e) {
        recordResult('Analytics', 'Course metrics query', 'FAILED', $e->getMessage());
    }

    // Test enrollment trend query
    try {
        $trend = $db->fetchAll("
            SELECT DATE_FORMAT(e.enrolled_at, '%Y-%m') as month,
                   COUNT(*) as enrollments
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE c.instructor_id = ?
            AND e.enrolled_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(e.enrolled_at, '%Y-%m')
            ORDER BY month ASC
        ", [$instructorId]);
        recordResult('Analytics', 'Enrollment trend query', 'PASSED', count($trend) . " months of data");
    } catch (Exception $e) {
        recordResult('Analytics', 'Enrollment trend query', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 6: STATISTICS CLASS
// ============================================================================

function testStatisticsClass() {
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "SECTION 6: STATISTICS CLASS\n";
    echo str_repeat('=', 70) . "\n\n";

    // Load Statistics class
    try {
        require_once SRC_PATH . '/classes/Statistics.php';
        recordResult('Statistics Class', 'Load class', 'PASSED');
    } catch (Exception $e) {
        recordResult('Statistics Class', 'Load class', 'FAILED', $e->getMessage());
        return;
    }

    $db = getDb();

    // Get any instructor
    $instructor = $db->fetchOne("
        SELECT u.id FROM users u
        INNER JOIN user_roles ur ON u.id = ur.user_id
        INNER JOIN roles r ON ur.role_id = r.id
        WHERE r.role_name = 'Instructor'
        LIMIT 1
    ");

    if (!$instructor) {
        recordResult('Statistics Class', 'getInstructorStats', 'SKIPPED', 'No instructor found');
        return;
    }

    // Test getInstructorStats method
    try {
        $stats = Statistics::getInstructorStats($instructor['id']);
        $hasKeys = isset($stats['total_courses']) && isset($stats['total_students']);
        recordResult('Statistics Class', 'getInstructorStats', $hasKeys ? 'PASSED' : 'FAILED',
            $hasKeys ? "Courses: {$stats['total_courses']}, Students: {$stats['total_students']}" : 'Missing expected keys');
    } catch (Exception $e) {
        recordResult('Statistics Class', 'getInstructorStats', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// RUN ALL TESTS
// ============================================================================

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║           INSTRUCTOR PANEL TEST SUITE                                ║\n";
echo "║           Testing Instructor Dashboard Components                    ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n";

// Run test sections
testDatabaseConnection();
testInstructorStatistics();
testInstructorCourses();
testInstructorStudents();
testInstructorAnalytics();
testStatisticsClass();

// Final Summary
echo "\n" . str_repeat('=', 70) . "\n";
echo "TEST SUMMARY\n";
echo str_repeat('=', 70) . "\n\n";

echo "Total Tests:   $totalTests\n";
echo "✅ Passed:     $passedTests\n";
echo "❌ Failed:     $failedTests\n";
echo "⏭️  Skipped:   $skippedTests\n";
echo "\n";

if ($failedTests === 0) {
    echo "🎉 All tests passed!\n";
} else {
    echo "⚠️  Some tests failed. Please review the output above.\n";
}

echo "\n";
