<?php
/**
 * Admin Dashboard Comprehensive Test Suite
 * Tests all admin pages, CRUD operations, and features
 *
 * Usage: php tests/test-admin-dashboard.php
 * Or via browser: http://localhost/edutrack-lms/tests/test-admin-dashboard.php
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
define('TEST_PREFIX', 'TEST_' . time() . '_');
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

    // Test each critical table exists
    $tables = ['users', 'roles', 'user_roles', 'courses', 'enrollments', 'payments',
               'certificates', 'categories', 'students', 'instructors'];

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
// SECTION 2: PAGE LOAD TESTS
// ============================================================================

function testPageLoads() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 2: PAGE LOAD TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $pages = [
        // Main pages
        'Dashboard' => '/admin/index.php',

        // Users
        'Users List' => '/admin/users/index.php',
        'Create User Form' => '/admin/users/create.php',

        // Students
        'Students List' => '/admin/students/index.php',
        'Student Enrollments' => '/admin/students/enrollments.php',

        // Courses
        'Courses List' => '/admin/courses/index.php',
        'Create Course Form' => '/admin/courses/create.php',

        // Enrollments
        'Enrollments List' => '/admin/enrollments/index.php',

        // Payments
        'Payments List' => '/admin/payments/index.php',
        'Payment Verification' => '/admin/payments/verify.php',
        'Payment Reports' => '/admin/payments/reports.php',

        // Certificates
        'Certificates List' => '/admin/certificates/index.php',
        'Issue Certificate Form' => '/admin/certificates/issue.php',
        'Certificate Templates' => '/admin/certificates/templates.php',

        // Categories
        'Categories List' => '/admin/categories/index.php',
        'Create Category Form' => '/admin/categories/create.php',

        // Instructors
        'Instructors List' => '/admin/instructors/index.php',
        'Create Instructor Form' => '/admin/instructors/create.php',

        // Announcements
        'Announcements List' => '/admin/announcements/index.php',
        'Create Announcement Form' => '/admin/announcements/create.php',

        // Reviews
        'Reviews List' => '/admin/reviews/index.php',

        // Reports
        'Reports Dashboard' => '/admin/reports/index.php',
        'Enrollment Reports' => '/admin/reports/enrollments.php',
        'Revenue Reports' => '/admin/reports/revenue.php',

        // Settings
        'General Settings' => '/admin/settings/index.php',
        'Email Settings' => '/admin/settings/email.php',
        'Payment Methods' => '/admin/settings/payment-methods.php',
    ];

    foreach ($pages as $name => $url) {
        testSinglePage($name, $url);
    }
}

function testSinglePage($name, $url) {
    $fullUrl = BASE_URL . $url;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $startTime = microtime(true);
    $response = curl_exec($ch);
    $responseTime = round((microtime(true) - $startTime) * 1000, 2);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        recordResult('Page Load', $name, 'FAILED', "cURL Error: $curlError");
        return;
    }

    // Check for errors in response
    $errors = [];

    if ($httpCode !== 200 && $httpCode !== 302) {
        $errors[] = "HTTP $httpCode";
    }

    if (preg_match('/Fatal error:/i', $response)) {
        $errors[] = "PHP Fatal Error";
    }

    if (preg_match('/SQLSTATE\[(\w+)\]/i', $response, $matches)) {
        $errors[] = "Database Error: {$matches[1]}";
    }

    if (preg_match('/Column not found/i', $response)) {
        $errors[] = "Column not found error";
    }

    if (preg_match('/<!-- DEBUG: FATAL ERROR: (.+?) -->/i', $response, $matches)) {
        $errors[] = "Debug Error: {$matches[1]}";
    }

    if (empty($errors)) {
        recordResult('Page Load', $name, 'PASSED', "{$responseTime}ms");
    } else {
        recordResult('Page Load', $name, 'FAILED', implode(', ', $errors));
    }
}

// ============================================================================
// SECTION 3: USERS CRUD TESTS
// ============================================================================

function testUsersCRUD() {
    global $testDataIds;

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 3: USERS CRUD TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();
    $testEmail = TEST_PREFIX . 'user@test.com';

    // CREATE - Insert a new user
    try {
        $userData = [
            'email' => $testEmail,
            'password_hash' => password_hash('TestPassword123', PASSWORD_DEFAULT),
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '1234567890',
            'status' => 'active',
            'email_verified' => 1
        ];

        $userId = $db->insert('users', $userData);
        $testDataIds['users'][] = $userId;

        recordResult('Users CRUD', 'CREATE - Insert new user', $userId > 0 ? 'PASSED' : 'FAILED', "ID: $userId");
    } catch (Exception $e) {
        recordResult('Users CRUD', 'CREATE - Insert new user', 'FAILED', $e->getMessage());
        return;
    }

    // READ - Fetch the user
    try {
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        $success = $user && $user['email'] === $testEmail;
        recordResult('Users CRUD', 'READ - Fetch user by ID', $success ? 'PASSED' : 'FAILED');
    } catch (Exception $e) {
        recordResult('Users CRUD', 'READ - Fetch user by ID', 'FAILED', $e->getMessage());
    }

    // READ - List users with role join
    try {
        $users = $db->fetchAll("
            SELECT u.*, r.role_name
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            LIMIT 5
        ");
        recordResult('Users CRUD', 'READ - List users with roles', count($users) > 0 ? 'PASSED' : 'FAILED', count($users) . " users");
    } catch (Exception $e) {
        recordResult('Users CRUD', 'READ - List users with roles', 'FAILED', $e->getMessage());
    }

    // UPDATE - Update user details
    try {
        $updated = $db->update('users', ['first_name' => 'Updated', 'last_name' => 'Name'], 'id = ?', [$userId]);

        $user = $db->fetchOne("SELECT first_name FROM users WHERE id = ?", [$userId]);
        $success = $user['first_name'] === 'Updated';
        recordResult('Users CRUD', 'UPDATE - Update user details', $success ? 'PASSED' : 'FAILED');
    } catch (Exception $e) {
        recordResult('Users CRUD', 'UPDATE - Update user details', 'FAILED', $e->getMessage());
    }

    // Assign role to user
    try {
        $role = $db->fetchOne("SELECT id FROM roles WHERE role_name = 'Student'");
        if ($role) {
            $db->insert('user_roles', ['user_id' => $userId, 'role_id' => $role['id']]);
            recordResult('Users CRUD', 'CREATE - Assign role to user', 'PASSED');
        } else {
            recordResult('Users CRUD', 'CREATE - Assign role to user', 'SKIPPED', 'No Student role found');
        }
    } catch (Exception $e) {
        recordResult('Users CRUD', 'CREATE - Assign role to user', 'FAILED', $e->getMessage());
    }

    // DELETE - Remove user role
    try {
        $deleted = $db->delete('user_roles', 'user_id = ?', [$userId]);
        recordResult('Users CRUD', 'DELETE - Remove user role', 'PASSED');
    } catch (Exception $e) {
        recordResult('Users CRUD', 'DELETE - Remove user role', 'FAILED', $e->getMessage());
    }

    // DELETE - Remove user
    try {
        $deleted = $db->delete('users', 'id = ?', [$userId]);
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        $success = $user === false || $user === null;
        recordResult('Users CRUD', 'DELETE - Remove user', $success ? 'PASSED' : 'FAILED');

        // Remove from tracking since we deleted it
        $testDataIds['users'] = array_diff($testDataIds['users'] ?? [], [$userId]);
    } catch (Exception $e) {
        recordResult('Users CRUD', 'DELETE - Remove user', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 4: COURSES CRUD TESTS
// ============================================================================

function testCoursesCRUD() {
    global $testDataIds;

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 4: COURSES CRUD TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // Get an instructor for course creation
    $instructor = $db->fetchOne("
        SELECT u.id FROM users u
        JOIN user_roles ur ON u.id = ur.user_id
        JOIN roles r ON ur.role_id = r.id
        WHERE r.role_name = 'Instructor'
        LIMIT 1
    ");

    $instructorId = $instructor ? $instructor['id'] : 1;

    // Get a category
    $category = $db->fetchOne("SELECT id FROM categories LIMIT 1");
    $categoryId = $category ? $category['id'] : null;

    // CREATE - Insert a new course
    try {
        $courseData = [
            'title' => TEST_PREFIX . 'Test Course',
            'slug' => TEST_PREFIX . 'test-course',
            'description' => 'This is a test course for automated testing',
            'short_description' => 'Test course',
            'instructor_id' => $instructorId,
            'category_id' => $categoryId,
            'price' => 99.99,
            'duration_hours' => 10,
            'level' => 'beginner',
            'status' => 'draft'
        ];

        $courseId = $db->insert('courses', $courseData);
        $testDataIds['courses'][] = $courseId;

        recordResult('Courses CRUD', 'CREATE - Insert new course', $courseId > 0 ? 'PASSED' : 'FAILED', "ID: $courseId");
    } catch (Exception $e) {
        recordResult('Courses CRUD', 'CREATE - Insert new course', 'FAILED', $e->getMessage());
        return;
    }

    // READ - Fetch the course
    try {
        $course = $db->fetchOne("SELECT * FROM courses WHERE id = ?", [$courseId]);
        $success = $course && strpos($course['title'], TEST_PREFIX) !== false;
        recordResult('Courses CRUD', 'READ - Fetch course by ID', $success ? 'PASSED' : 'FAILED');
    } catch (Exception $e) {
        recordResult('Courses CRUD', 'READ - Fetch course by ID', 'FAILED', $e->getMessage());
    }

    // READ - List courses with instructor
    try {
        $courses = $db->fetchAll("
            SELECT c.*, u.first_name, u.last_name, cat.name as category_name
            FROM courses c
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            LIMIT 5
        ");
        recordResult('Courses CRUD', 'READ - List courses with joins', count($courses) > 0 ? 'PASSED' : 'FAILED', count($courses) . " courses");
    } catch (Exception $e) {
        recordResult('Courses CRUD', 'READ - List courses with joins', 'FAILED', $e->getMessage());
    }

    // UPDATE - Update course
    try {
        $db->update('courses', ['title' => TEST_PREFIX . 'Updated Course', 'status' => 'published'], 'id = ?', [$courseId]);

        $course = $db->fetchOne("SELECT * FROM courses WHERE id = ?", [$courseId]);
        $success = strpos($course['title'], 'Updated') !== false && $course['status'] === 'published';
        recordResult('Courses CRUD', 'UPDATE - Update course details', $success ? 'PASSED' : 'FAILED');
    } catch (Exception $e) {
        recordResult('Courses CRUD', 'UPDATE - Update course details', 'FAILED', $e->getMessage());
    }

    // DELETE - Remove course
    try {
        $db->delete('courses', 'id = ?', [$courseId]);
        $course = $db->fetchOne("SELECT * FROM courses WHERE id = ?", [$courseId]);
        $success = $course === false || $course === null;
        recordResult('Courses CRUD', 'DELETE - Remove course', $success ? 'PASSED' : 'FAILED');

        $testDataIds['courses'] = array_diff($testDataIds['courses'] ?? [], [$courseId]);
    } catch (Exception $e) {
        recordResult('Courses CRUD', 'DELETE - Remove course', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 5: CATEGORIES CRUD TESTS
// ============================================================================

function testCategoriesCRUD() {
    global $testDataIds;

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 5: CATEGORIES CRUD TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // CREATE
    try {
        $categoryData = [
            'name' => TEST_PREFIX . 'Test Category',
            'slug' => TEST_PREFIX . 'test-category',
            'description' => 'Test category for automated testing',
            'status' => 'active'
        ];

        $categoryId = $db->insert('categories', $categoryData);
        $testDataIds['categories'][] = $categoryId;

        recordResult('Categories CRUD', 'CREATE - Insert new category', $categoryId > 0 ? 'PASSED' : 'FAILED', "ID: $categoryId");
    } catch (Exception $e) {
        recordResult('Categories CRUD', 'CREATE - Insert new category', 'FAILED', $e->getMessage());
        return;
    }

    // READ
    try {
        $category = $db->fetchOne("SELECT * FROM categories WHERE id = ?", [$categoryId]);
        $success = $category && strpos($category['name'], TEST_PREFIX) !== false;
        recordResult('Categories CRUD', 'READ - Fetch category by ID', $success ? 'PASSED' : 'FAILED');
    } catch (Exception $e) {
        recordResult('Categories CRUD', 'READ - Fetch category by ID', 'FAILED', $e->getMessage());
    }

    // READ - List with course count
    try {
        $categories = $db->fetchAll("
            SELECT c.*, COUNT(co.id) as course_count
            FROM categories c
            LEFT JOIN courses co ON c.id = co.category_id
            GROUP BY c.id
            LIMIT 5
        ");
        recordResult('Categories CRUD', 'READ - List with course count', count($categories) > 0 ? 'PASSED' : 'FAILED');
    } catch (Exception $e) {
        recordResult('Categories CRUD', 'READ - List with course count', 'FAILED', $e->getMessage());
    }

    // UPDATE
    try {
        $db->update('categories', ['name' => TEST_PREFIX . 'Updated Category'], 'id = ?', [$categoryId]);
        $category = $db->fetchOne("SELECT * FROM categories WHERE id = ?", [$categoryId]);
        $success = strpos($category['name'], 'Updated') !== false;
        recordResult('Categories CRUD', 'UPDATE - Update category', $success ? 'PASSED' : 'FAILED');
    } catch (Exception $e) {
        recordResult('Categories CRUD', 'UPDATE - Update category', 'FAILED', $e->getMessage());
    }

    // DELETE
    try {
        $db->delete('categories', 'id = ?', [$categoryId]);
        $category = $db->fetchOne("SELECT * FROM categories WHERE id = ?", [$categoryId]);
        $success = $category === false || $category === null;
        recordResult('Categories CRUD', 'DELETE - Remove category', $success ? 'PASSED' : 'FAILED');

        $testDataIds['categories'] = array_diff($testDataIds['categories'] ?? [], [$categoryId]);
    } catch (Exception $e) {
        recordResult('Categories CRUD', 'DELETE - Remove category', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 6: ENROLLMENTS TESTS
// ============================================================================

function testEnrollments() {
    global $testDataIds;

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 6: ENROLLMENTS TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // Get existing user and course for enrollment
    $user = $db->fetchOne("SELECT id FROM users LIMIT 1");
    $course = $db->fetchOne("SELECT id FROM courses LIMIT 1");

    if (!$user || !$course) {
        recordResult('Enrollments', 'Prerequisites check', 'SKIPPED', 'No users or courses found');
        return;
    }

    // READ - List enrollments
    try {
        $enrollments = $db->fetchAll("
            SELECT e.*, u.first_name, u.last_name, c.title as course_title
            FROM enrollments e
            JOIN users u ON e.user_id = u.id
            JOIN courses c ON e.course_id = c.id
            LIMIT 5
        ");
        recordResult('Enrollments', 'READ - List enrollments with joins', 'PASSED', count($enrollments) . " enrollments");
    } catch (Exception $e) {
        recordResult('Enrollments', 'READ - List enrollments with joins', 'FAILED', $e->getMessage());
    }

    // READ - Count by status
    try {
        $stats = [
            'active' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'active'"),
            'completed' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'completed'"),
            'total' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments")
        ];
        recordResult('Enrollments', 'READ - Count by status', 'PASSED', "Total: {$stats['total']}, Active: {$stats['active']}, Completed: {$stats['completed']}");
    } catch (Exception $e) {
        recordResult('Enrollments', 'READ - Count by status', 'FAILED', $e->getMessage());
    }

    // Test enrollment statistics query (used in dashboard)
    try {
        $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'active'");
        $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'completed'");
        recordResult('Enrollments', 'Statistics queries', 'PASSED');
    } catch (Exception $e) {
        recordResult('Enrollments', 'Statistics queries', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 7: PAYMENTS TESTS
// ============================================================================

function testPayments() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 7: PAYMENTS TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // READ - List payments with student and user info
    try {
        $payments = $db->fetchAll("
            SELECT p.*, u.first_name, u.last_name, u.email, c.title as course_title
            FROM payments p
            JOIN students s ON p.student_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN courses c ON p.course_id = c.id
            ORDER BY p.created_at DESC
            LIMIT 5
        ");
        recordResult('Payments', 'READ - List payments with joins', 'PASSED', count($payments) . " payments");
    } catch (Exception $e) {
        recordResult('Payments', 'READ - List payments with joins', 'FAILED', $e->getMessage());
    }

    // READ - Count by status
    try {
        $stats = [
            'pending' => $db->fetchColumn("SELECT COUNT(*) FROM payments WHERE payment_status = 'Pending'"),
            'completed' => $db->fetchColumn("SELECT COUNT(*) FROM payments WHERE payment_status = 'Completed'"),
            'total' => $db->fetchColumn("SELECT COUNT(*) FROM payments")
        ];
        recordResult('Payments', 'READ - Count by status', 'PASSED', "Total: {$stats['total']}, Pending: {$stats['pending']}, Completed: {$stats['completed']}");
    } catch (Exception $e) {
        recordResult('Payments', 'READ - Count by status', 'FAILED', $e->getMessage());
    }

    // READ - Revenue calculation
    try {
        $revenue = $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'Completed'");
        recordResult('Payments', 'READ - Total revenue', 'PASSED', "Revenue: $" . number_format($revenue, 2));
    } catch (Exception $e) {
        recordResult('Payments', 'READ - Total revenue', 'FAILED', $e->getMessage());
    }

    // READ - Revenue by period
    try {
        $revenueByMonth = $db->fetchAll("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as revenue
            FROM payments
            WHERE payment_status = 'Completed'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
        ");
        recordResult('Payments', 'READ - Revenue by month', 'PASSED', count($revenueByMonth) . " months");
    } catch (Exception $e) {
        recordResult('Payments', 'READ - Revenue by month', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 8: CERTIFICATES TESTS
// ============================================================================

function testCertificates() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 8: CERTIFICATES TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // READ - List certificates with enrollment data
    try {
        $certificates = $db->fetchAll("
            SELECT c.*, u.first_name, u.last_name, co.title as course_title
            FROM certificates c
            JOIN enrollments e ON c.enrollment_id = e.id
            JOIN users u ON e.user_id = u.id
            JOIN courses co ON e.course_id = co.id
            ORDER BY c.issued_at DESC
            LIMIT 5
        ");
        recordResult('Certificates', 'READ - List certificates with joins', 'PASSED', count($certificates) . " certificates");
    } catch (Exception $e) {
        recordResult('Certificates', 'READ - List certificates with joins', 'FAILED', $e->getMessage());
    }

    // READ - Count statistics
    try {
        $stats = [
            'total' => $db->fetchColumn("SELECT COUNT(*) FROM certificates"),
            'this_month' => $db->fetchColumn("SELECT COUNT(*) FROM certificates WHERE issued_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),
            'revoked' => $db->fetchColumn("SELECT COUNT(*) FROM certificates WHERE revoked = 1")
        ];
        recordResult('Certificates', 'READ - Certificate statistics', 'PASSED', "Total: {$stats['total']}, This month: {$stats['this_month']}");
    } catch (Exception $e) {
        recordResult('Certificates', 'READ - Certificate statistics', 'FAILED', $e->getMessage());
    }

    // READ - Eligible for certificate (completed enrollments without certificate)
    try {
        $eligible = $db->fetchAll("
            SELECT e.id as enrollment_id, u.first_name, u.last_name, c.title as course_title
            FROM enrollments e
            JOIN users u ON e.user_id = u.id
            JOIN courses c ON e.course_id = c.id
            LEFT JOIN certificates cert ON cert.enrollment_id = e.id
            WHERE e.enrollment_status = 'completed'
            AND cert.certificate_id IS NULL
            LIMIT 5
        ");
        recordResult('Certificates', 'READ - Eligible for certificate', 'PASSED', count($eligible) . " eligible");
    } catch (Exception $e) {
        recordResult('Certificates', 'READ - Eligible for certificate', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 9: STATISTICS & REPORTS TESTS
// ============================================================================

function testStatisticsAndReports() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 9: STATISTICS & REPORTS TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // Dashboard stats
    try {
        $stats = [
            'users' => $db->fetchColumn("SELECT COUNT(*) FROM users"),
            'courses' => $db->fetchColumn("SELECT COUNT(*) FROM courses"),
            'enrollments' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments"),
            'revenue' => $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'Completed'")
        ];
        recordResult('Statistics', 'Dashboard overview stats', 'PASSED',
            "Users: {$stats['users']}, Courses: {$stats['courses']}, Enrollments: {$stats['enrollments']}");
    } catch (Exception $e) {
        recordResult('Statistics', 'Dashboard overview stats', 'FAILED', $e->getMessage());
    }

    // User counts by role
    try {
        $students = $db->fetchColumn("
            SELECT COUNT(DISTINCT u.id) FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE r.role_name = 'Student'
        ");
        $instructors = $db->fetchColumn("
            SELECT COUNT(DISTINCT u.id) FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE r.role_name = 'Instructor'
        ");
        recordResult('Statistics', 'User counts by role', 'PASSED', "Students: $students, Instructors: $instructors");
    } catch (Exception $e) {
        recordResult('Statistics', 'User counts by role', 'FAILED', $e->getMessage());
    }

    // Course stats
    try {
        $published = $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE status = 'published'");
        $draft = $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE status = 'draft'");
        recordResult('Statistics', 'Course counts by status', 'PASSED', "Published: $published, Draft: $draft");
    } catch (Exception $e) {
        recordResult('Statistics', 'Course counts by status', 'FAILED', $e->getMessage());
    }

    // Enrollment trends
    try {
        $trends = $db->fetchAll("
            SELECT DATE_FORMAT(enrolled_at, '%Y-%m') as month, COUNT(*) as count
            FROM enrollments
            WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(enrolled_at, '%Y-%m')
            ORDER BY month DESC
        ");
        recordResult('Statistics', 'Enrollment trends', 'PASSED', count($trends) . " months of data");
    } catch (Exception $e) {
        recordResult('Statistics', 'Enrollment trends', 'FAILED', $e->getMessage());
    }

    // Top students query
    try {
        $topStudents = $db->fetchAll("
            SELECT u.id, u.first_name, u.last_name,
                   COUNT(DISTINCT e.course_id) as courses_enrolled,
                   COUNT(DISTINCT c.id) as courses_completed,
                   AVG(e.progress_percentage) as avg_progress
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.id
            LEFT JOIN enrollments e ON u.id = e.user_id
            LEFT JOIN enrollments c ON u.id = c.user_id AND c.enrollment_status = 'completed'
            WHERE r.role_name = 'Student'
            GROUP BY u.id
            ORDER BY courses_completed DESC
            LIMIT 5
        ");
        recordResult('Statistics', 'Top students query', 'PASSED', count($topStudents) . " students");
    } catch (Exception $e) {
        recordResult('Statistics', 'Top students query', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 10: INSTRUCTORS TESTS
// ============================================================================

function testInstructors() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 10: INSTRUCTORS TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // READ - List instructors
    try {
        $instructors = $db->fetchAll("
            SELECT u.*, COUNT(c.id) as course_count
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            LEFT JOIN courses c ON u.id = c.instructor_id
            WHERE r.role_name = 'Instructor'
            GROUP BY u.id
            LIMIT 5
        ");
        recordResult('Instructors', 'READ - List instructors with course count', 'PASSED', count($instructors) . " instructors");
    } catch (Exception $e) {
        recordResult('Instructors', 'READ - List instructors with course count', 'FAILED', $e->getMessage());
    }

    // READ - Instructor stats
    try {
        $instructorCount = $db->fetchColumn("
            SELECT COUNT(DISTINCT u.id) FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE r.role_name = 'Instructor'
        ");
        recordResult('Instructors', 'READ - Total instructor count', 'PASSED', "$instructorCount instructors");
    } catch (Exception $e) {
        recordResult('Instructors', 'READ - Total instructor count', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 11: ANNOUNCEMENTS TESTS
// ============================================================================

function testAnnouncements() {
    global $testDataIds;

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 11: ANNOUNCEMENTS TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // Check if announcements table exists
    try {
        $count = $db->fetchColumn("SELECT COUNT(*) FROM announcements");
        recordResult('Announcements', 'Table exists', 'PASSED', "$count announcements");
    } catch (Exception $e) {
        recordResult('Announcements', 'Table exists', 'SKIPPED', 'Table may not exist');
        return;
    }

    // CREATE
    try {
        $announcementData = [
            'title' => TEST_PREFIX . 'Test Announcement',
            'content' => 'This is a test announcement for automated testing',
            'status' => 'published',
            'created_by' => 1
        ];

        $announcementId = $db->insert('announcements', $announcementData);
        $testDataIds['announcements'][] = $announcementId;

        recordResult('Announcements', 'CREATE - Insert announcement', $announcementId > 0 ? 'PASSED' : 'FAILED');
    } catch (Exception $e) {
        recordResult('Announcements', 'CREATE - Insert announcement', 'FAILED', $e->getMessage());
        return;
    }

    // READ
    try {
        $announcement = $db->fetchOne("SELECT * FROM announcements WHERE id = ?", [$announcementId]);
        recordResult('Announcements', 'READ - Fetch announcement', $announcement ? 'PASSED' : 'FAILED');
    } catch (Exception $e) {
        recordResult('Announcements', 'READ - Fetch announcement', 'FAILED', $e->getMessage());
    }

    // UPDATE
    try {
        $db->update('announcements', ['title' => TEST_PREFIX . 'Updated Announcement'], 'id = ?', [$announcementId]);
        recordResult('Announcements', 'UPDATE - Update announcement', 'PASSED');
    } catch (Exception $e) {
        recordResult('Announcements', 'UPDATE - Update announcement', 'FAILED', $e->getMessage());
    }

    // DELETE
    try {
        $db->delete('announcements', 'id = ?', [$announcementId]);
        recordResult('Announcements', 'DELETE - Remove announcement', 'PASSED');
        $testDataIds['announcements'] = array_diff($testDataIds['announcements'] ?? [], [$announcementId]);
    } catch (Exception $e) {
        recordResult('Announcements', 'DELETE - Remove announcement', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// SECTION 12: REVIEWS TESTS
// ============================================================================

function testReviews() {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "SECTION 12: REVIEWS TESTS\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();

    // Check if reviews table exists
    try {
        $count = $db->fetchColumn("SELECT COUNT(*) FROM reviews");
        recordResult('Reviews', 'Table exists', 'PASSED', "$count reviews");
    } catch (Exception $e) {
        recordResult('Reviews', 'Table exists', 'SKIPPED', 'Table may not exist');
        return;
    }

    // READ - List reviews with user and course info
    try {
        $reviews = $db->fetchAll("
            SELECT r.*, u.first_name, u.last_name, c.title as course_title
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN courses c ON r.course_id = c.id
            ORDER BY r.created_at DESC
            LIMIT 5
        ");
        recordResult('Reviews', 'READ - List reviews with joins', 'PASSED', count($reviews) . " reviews");
    } catch (Exception $e) {
        recordResult('Reviews', 'READ - List reviews with joins', 'FAILED', $e->getMessage());
    }

    // READ - Average ratings by course
    try {
        $avgRatings = $db->fetchAll("
            SELECT c.title, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
            FROM courses c
            LEFT JOIN reviews r ON c.id = r.course_id
            GROUP BY c.id
            HAVING review_count > 0
            LIMIT 5
        ");
        recordResult('Reviews', 'READ - Average ratings by course', 'PASSED', count($avgRatings) . " courses with reviews");
    } catch (Exception $e) {
        recordResult('Reviews', 'READ - Average ratings by course', 'FAILED', $e->getMessage());
    }
}

// ============================================================================
// CLEANUP
// ============================================================================

function cleanup() {
    global $testDataIds;

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "CLEANUP\n";
    echo str_repeat('=', 80) . "\n\n";

    $db = getDb();
    $cleanedUp = 0;

    // Clean up any remaining test data
    foreach ($testDataIds as $table => $ids) {
        foreach ($ids as $id) {
            try {
                $db->delete($table, 'id = ?', [$id]);
                $cleanedUp++;
            } catch (Exception $e) {
                // Ignore cleanup errors
            }
        }
    }

    // Clean up any orphaned test data by prefix
    $tables = ['users', 'courses', 'categories', 'announcements'];
    foreach ($tables as $table) {
        try {
            $column = $table === 'categories' ? 'name' : ($table === 'users' ? 'email' : 'title');
            $deleted = $db->query("DELETE FROM $table WHERE $column LIKE ?", [TEST_PREFIX . '%']);
            if ($deleted) {
                $cleanedUp++;
            }
        } catch (Exception $e) {
            // Ignore
        }
    }

    echo "Cleaned up $cleanedUp test records\n";
}

// ============================================================================
// PRINT SUMMARY
// ============================================================================

function printSummary() {
    global $totalTests, $passedTests, $failedTests, $skippedTests, $results;

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "TEST SUMMARY\n";
    echo str_repeat('=', 80) . "\n\n";

    echo "Total Tests:   $totalTests\n";
    echo "✅ Passed:     $passedTests\n";
    echo "❌ Failed:     $failedTests\n";
    echo "⏭️  Skipped:    $skippedTests\n\n";

    $successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;
    echo "Success Rate: {$successRate}%\n";

    // Group failed tests by category
    if ($failedTests > 0) {
        echo "\n" . str_repeat('-', 80) . "\n";
        echo "FAILED TESTS:\n";
        echo str_repeat('-', 80) . "\n";

        $failedByCategory = [];
        foreach ($results as $result) {
            if ($result['status'] === 'FAILED') {
                $failedByCategory[$result['category']][] = $result;
            }
        }

        foreach ($failedByCategory as $category => $tests) {
            echo "\n[$category]\n";
            foreach ($tests as $test) {
                echo "  ❌ {$test['name']}";
                if ($test['message']) {
                    echo " - {$test['message']}";
                }
                echo "\n";
            }
        }
    }

    echo "\n" . str_repeat('=', 80) . "\n";

    if ($failedTests === 0) {
        echo "🎉 ALL TESTS PASSED! The admin dashboard is fully functional.\n";
    } else {
        echo "⚠️  SOME TESTS FAILED. Please review the errors above.\n";
    }

    echo str_repeat('=', 80) . "\n";
}

// ============================================================================
// MAIN EXECUTION
// ============================================================================

// Set output format
if (php_sapi_name() === 'cli') {
    echo "\n";
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Admin Dashboard Test Suite</title>';
    echo '<style>body{font-family:monospace;background:#1a1a2e;color:#eee;padding:20px;} pre{background:#16213e;padding:20px;border-radius:5px;overflow-x:auto;}</style>';
    echo '</head><body><pre>';
}

echo str_repeat('=', 80) . "\n";
echo "EDUTRACK LMS - ADMIN DASHBOARD COMPREHENSIVE TEST SUITE\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Test Prefix: " . TEST_PREFIX . "\n";
echo str_repeat('=', 80) . "\n";

// Run all test sections
testDatabaseConnection();
testPageLoads();
testUsersCRUD();
testCoursesCRUD();
testCategoriesCRUD();
testEnrollments();
testPayments();
testCertificates();
testStatisticsAndReports();
testInstructors();
testAnnouncements();
testReviews();

// Cleanup test data
cleanup();

// Print summary
printSummary();

// Close HTML if in web mode
if (php_sapi_name() !== 'cli') {
    echo '</pre></body></html>';
}

// Exit with appropriate code
exit($failedTests > 0 ? 1 : 0);
