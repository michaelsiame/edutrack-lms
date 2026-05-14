<?php
/**
 * Admin Dashboard - Modern UI Version
 */
require_once '../../src/bootstrap.php';

// Check authentication
if (!isLoggedIn()) {
    flash('error', 'Please login to access the admin dashboard.', 'warning');
    redirect(url('login.php'));
    exit;
}

// Check admin role
require_once '../../src/includes/access-control.php';
if (!hasRole('admin')) {
    accessDenied('admin', 'You must be an administrator to access the admin dashboard.');
}

// Get current page
$page = $_GET['page'] ?? 'dashboard';
$validPages = [
    'dashboard',
    'users', 'instructors', 'students',
    'courses', 'modules', 'quizzes', 'assignments',
    'enrollments', 'certificates', 'badges',
    'announcements', 'events', 'hero-slides', 'institution-photos', 'email-templates',
    'discussions', 'reviews', 'contacts',
    'financials', 'payment-methods', 'registration-fees',
    'communication', 'email-queue', 'notifications', 'newsletter-subscribers',
    'reports', 'activity-logs',
    'settings', 'help', 'company-profile'
];
if (!in_array($page, $validPages)) {
    $page = 'dashboard';
}

// Fetch database and settings early
$db = Database::getInstance();
$settingsRows = $db->fetchAll("SELECT setting_key, setting_value FROM system_settings");
$settings = array_column($settingsRows, 'setting_value', 'setting_key');
$currency = $settings['currency'] ?? 'ZMW';

// ============================================
// PROCESS AJAX/POST REQUESTS BEFORE HTML OUTPUT
// ============================================

// Process Users page handlers
if ($page === 'users') {
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');

        if ($_GET['ajax'] === 'get_user' && isset($_GET['id'])) {
            $user = $db->fetchOne("
                SELECT u.*, ur.role_id
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                WHERE u.id = ?
            ", [(int)$_GET['id']]);
            echo json_encode($user ?: ['error' => 'User not found']);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include 'handlers/users_handler.php';
    }
}

// Process Courses page handlers
if ($page === 'courses') {
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');

        if ($_GET['ajax'] === 'get_course' && isset($_GET['id'])) {
            $course = $db->fetchOne("SELECT * FROM courses WHERE id = ?", [(int)$_GET['id']]);
            echo json_encode($course ?: ['error' => 'Course not found']);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include 'handlers/courses_handler.php';
    }
}

// Process Modules page handlers
if ($page === 'modules') {
    $courseId = (int)($_GET['course_id'] ?? 0);

    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');

        if ($_GET['ajax'] === 'get_module' && isset($_GET['id'])) {
            $module = $db->fetchOne("SELECT * FROM modules WHERE id = ?", [(int)$_GET['id']]);
            echo json_encode($module ?: ['error' => 'Module not found']);
            exit;
        }

        if ($_GET['ajax'] === 'get_lesson' && isset($_GET['id'])) {
            $lesson = $db->fetchOne("SELECT * FROM lessons WHERE id = ?", [(int)$_GET['id']]);
            echo json_encode($lesson ?: ['error' => 'Lesson not found']);
            exit;
        }

        if ($_GET['ajax'] === 'reorder_modules' && isset($_POST['order'])) {
            $order = json_decode($_POST['order'], true);
            foreach ($order as $index => $moduleId) {
                $db->update('modules', ['display_order' => $index + 1], 'id = ?', [(int)$moduleId]);
            }
            echo json_encode(['success' => true]);
            exit;
        }

        if ($_GET['ajax'] === 'reorder_lessons' && isset($_POST['order'], $_POST['module_id'])) {
            $order = json_decode($_POST['order'], true);
            $moduleId = (int)$_POST['module_id'];
            foreach ($order as $index => $lessonId) {
                $db->update('lessons', ['display_order' => $index + 1, 'module_id' => $moduleId], 'id = ?', [(int)$lessonId]);
            }
            echo json_encode(['success' => true]);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include 'handlers/modules_handler.php';
    }
}

// Process Financials page handlers
if ($page === 'financials') {
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');

        if ($_GET['ajax'] === 'get_payment' && isset($_GET['id'])) {
            $payment = $db->fetchOne("SELECT * FROM payments WHERE payment_id = ?", [(int)$_GET['id']]);
            echo json_encode($payment ?: ['error' => 'Payment not found']);
            exit;
        }

        if ($_GET['ajax'] === 'get_students') {
            $students = $db->fetchAll("
                SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as full_name, u.email
                FROM users u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN roles r ON ur.role_id = r.id
                WHERE r.role_name = 'Student'
                ORDER BY u.first_name, u.last_name
            ");
            echo json_encode($students);
            exit;
        }

        if ($_GET['ajax'] === 'export') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="payments_export_' . date('Y-m-d') . '.csv"');

            $sql = "SELECT p.payment_id, CONCAT(u.first_name, ' ', u.last_name) as student, u.email, c.title as course, p.amount, p.currency, p.payment_status, p.payment_type, p.transaction_id, p.created_at FROM payments p LEFT JOIN users u ON p.student_id = u.id LEFT JOIN courses c ON p.course_id = c.id ORDER BY p.created_at DESC";
            $payments = $db->fetchAll($sql);

            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Student', 'Email', 'Course', 'Amount', 'Currency', 'Status', 'Type', 'Reference', 'Date']);
            foreach ($payments as $p) {
                fputcsv($output, $p);
            }
            fclose($output);
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include 'handlers/financials_handler.php';
    }
}

// ============================================
// FETCH DASHBOARD STATS
// ============================================

// Initialize defaults
$totalStudents = 0;
$totalInstructors = 0;
$activeCourses = 0;
$totalEnrollments = 0;
$pendingAmount = 0;
$monthlyEnrollments = [];
$recentActivity = [];
$completionStats = ['completed' => 0, 'in_progress' => 0, 'not_started' => 0];

try {
    // Count students (users with role_id=4 which is 'Student')
    $totalStudents = $db->fetchColumn("
        SELECT COUNT(DISTINCT u.id)
        FROM users u
        JOIN user_roles ur ON u.id = ur.user_id
        JOIN roles r ON ur.role_id = r.id
        WHERE r.role_name = 'Student'
    ") ?: 0;
} catch (Throwable $e) {
    error_log("Admin dashboard - student count error: " . $e->getMessage());
}

try {
    // Count instructors (role_id=3 which is 'Instructor')
    $totalInstructors = $db->fetchColumn("
        SELECT COUNT(DISTINCT u.id)
        FROM users u
        JOIN user_roles ur ON u.id = ur.user_id
        JOIN roles r ON ur.role_id = r.id
        WHERE r.role_name = 'Instructor'
    ") ?: 0;
} catch (Throwable $e) {
    error_log("Admin dashboard - instructor count error: " . $e->getMessage());
}

try {
    $activeCourses = $db->count('courses', "status = 'published'") ?: 0;
} catch (Throwable $e) {
    error_log("Admin dashboard - courses error: " . $e->getMessage());
}

try {
    $totalEnrollments = $db->count('enrollments') ?: 0;
} catch (Throwable $e) {
    error_log("Admin dashboard - enrollments error: " . $e->getMessage());
}

try {
    // Pending payments from payments table
    $pendingPayments = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'Pending'");
    $pendingAmount = $pendingPayments['total'] ?? 0;
} catch (Throwable $e) {
    error_log("Admin dashboard - payments error: " . $e->getMessage());
}

try {
    // Get monthly enrollment data for chart
    $monthlyEnrollments = $db->fetchAll("
        SELECT DATE_FORMAT(enrolled_at, '%Y-%m') as month, COUNT(*) as count
        FROM enrollments
        WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(enrolled_at, '%Y-%m')
        ORDER BY month ASC
    ") ?: [];
} catch (Throwable $e) {
    error_log("Admin dashboard - monthly enrollments error: " . $e->getMessage());
}

try {
    // Get recent activity
    $recentActivity = $db->fetchAll("
        SELECT 'enrollment' as type, e.enrolled_at as date, CONCAT(u.first_name, ' ', u.last_name) as user_name, c.title as item_name
        FROM enrollments e
        JOIN users u ON e.user_id = u.id
        JOIN courses c ON e.course_id = c.id
        ORDER BY e.enrolled_at DESC
        LIMIT 5
    ") ?: [];
} catch (Throwable $e) {
    error_log("Admin dashboard - recent activity error: " . $e->getMessage());
}

try {
    // Get course completion stats
    $completionStats = $db->fetchOne("
        SELECT 
            COUNT(CASE WHEN progress = 100 THEN 1 END) as completed,
            COUNT(CASE WHEN progress > 0 AND progress < 100 THEN 1 END) as in_progress,
            COUNT(CASE WHEN progress = 0 THEN 1 END) as not_started
        FROM enrollments
    ") ?: ['completed' => 0, 'in_progress' => 0, 'not_started' => 0];
} catch (Throwable $e) {
    error_log("Admin dashboard - completion stats error: " . $e->getMessage());
}

$page_title = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 50: '#EFF6FF', 100: '#DBEAFE', 500: '#3B82F6', 600: '#2563EB', 700: '#1D4ED8' },
                        admin: { 800: '#1E293B', 900: '#0F172A' }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover { background: rgba(255,255,255,0.1); }
        .sidebar-link.active { background: rgba(59, 130, 246, 0.9); box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3); }
        .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); }
        .shadow-card { box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04); }
    </style>
</head>
<body class="bg-gray-50">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
        
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"></div>
        
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static flex flex-col">
            
            <!-- Logo Section -->
            <div class="flex items-center h-16 px-6 bg-slate-900 border-b border-slate-700">
                <a href="?page=dashboard" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-shield-alt text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight">EduTrack</span>
                        <span class="block text-xs text-gray-400 -mt-1">Admin Panel</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden ml-auto text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                
                <!-- Main -->
                <div class="px-4 py-2">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Main</span>
                </div>
                <a href="?page=dashboard" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'dashboard' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'dashboard' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <span>Dashboard</span>
                </a>
                <a href="?page=reports" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'reports' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'reports' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <span>Reports</span>
                </a>

                <!-- People -->
                <div class="px-4 py-2 mt-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">People</span>
                </div>
                <a href="?page=users" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'users' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'users' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <span>All Users</span>
                    <span class="ml-auto bg-primary-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $totalStudents ?></span>
                </a>
                <a href="?page=instructors" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'instructors' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'instructors' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <span>Instructors</span>
                </a>
                <a href="?page=students" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'students' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'students' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <span>Students</span>
                </a>

                <!-- Academics -->
                <div class="px-4 py-2 mt-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Academics</span>
                </div>
                <a href="?page=courses" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'courses' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'courses' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-book"></i>
                    </div>
                    <span>Courses</span>
                </a>
                <a href="?page=modules" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'modules' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'modules' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <span>Modules & Lessons</span>
                </a>
                <a href="?page=quizzes" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'quizzes' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'quizzes' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <span>Quizzes</span>
                </a>
                <a href="?page=assignments" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'assignments' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'assignments' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <span>Assignments</span>
                </a>
                <a href="?page=enrollments" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'enrollments' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'enrollments' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <span>Enrollments</span>
                </a>
                <a href="?page=certificates" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'certificates' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'certificates' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <span>Certificates</span>
                </a>
                <a href="?page=badges" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'badges' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'badges' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-award"></i>
                    </div>
                    <span>Badges</span>
                </a>
                <a href="?page=live-sessions" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'live-sessions' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'live-sessions' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-video"></i>
                    </div>
                    <span>Live Sessions</span>
                </a>

                <!-- Content -->
                <div class="px-4 py-2 mt-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Content</span>
                </div>
                <a href="?page=announcements" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'announcements' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'announcements' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <span>Announcements</span>
                </a>
                <a href="?page=events" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'events' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'events' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <span>Events</span>
                </a>
                <a href="?page=hero-slides" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'hero-slides' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'hero-slides' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-image"></i>
                    </div>
                    <span>Hero Slides</span>
                </a>
                <a href="?page=institution-photos" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'institution-photos' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'institution-photos' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-images"></i>
                    </div>
                    <span>Photos</span>
                </a>
                <a href="?page=email-templates" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'email-templates' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'email-templates' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <span>Email Templates</span>
                </a>

                <!-- Community -->
                <div class="px-4 py-2 mt-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Community</span>
                </div>
                <a href="?page=discussions" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'discussions' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'discussions' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-comments"></i>
                    </div>
                    <span>Discussions</span>
                </a>
                <a href="?page=reviews" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'reviews' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'reviews' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-star"></i>
                    </div>
                    <span>Reviews</span>
                </a>
                <a href="?page=contacts" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'contacts' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'contacts' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <span>Contacts</span>
                </a>

                <!-- Finance -->
                <div class="px-4 py-2 mt-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Finance</span>
                </div>
                <a href="?page=financials" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'financials' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'financials' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <span>Payments</span>
                </a>
                <a href="?page=payment-methods" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'payment-methods' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'payment-methods' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <span>Payment Methods</span>
                </a>
                <a href="?page=registration-fees" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'registration-fees' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'registration-fees' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <span>Registration Fees</span>
                </a>

                <!-- Communication -->
                <div class="px-4 py-2 mt-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Communication</span>
                </div>
                <a href="?page=email-queue" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'email-queue' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'email-queue' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <span>Email Queue</span>
                </a>
                <a href="?page=notifications" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'notifications' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'notifications' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-bell"></i>
                    </div>
                    <span>Notifications</span>
                </a>
                <a href="?page=newsletter-subscribers" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'newsletter-subscribers' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'newsletter-subscribers' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <span>Newsletter</span>
                </a>

                <!-- System -->
                <div class="px-4 py-2 mt-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">System</span>
                </div>
                <a href="?page=activity-logs" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'activity-logs' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'activity-logs' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <span>Activity Logs</span>
                </a>
                <a href="?page=company-profile" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'company-profile' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'company-profile' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-building"></i>
                    </div>
                    <span>Company Profile</span>
                </a>
                <a href="?page=settings" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'settings' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'settings' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span>Settings</span>
                </a>
                <a href="<?= url('admin/help.php') ?>" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium text-gray-300 hover:text-white rounded-xl">
                    <div class="w-8 h-8 rounded-lg bg-slate-700/50 flex items-center justify-center mr-3">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <span>Help</span>
                </a>

                <hr class="my-4 border-slate-700/50">
                
                <a href="<?= url('index.php') ?>" target="_blank" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium text-gray-300 hover:text-white rounded-xl">
                    <div class="w-8 h-8 rounded-lg bg-slate-700/50 flex items-center justify-center mr-3">
                        <i class="fas fa-external-link-alt"></i>
                    </div>
                    <span>View Site</span>
                </a>
            </nav>
            
            <!-- User Profile Summary -->
            <div class="p-4 border-t border-slate-700/50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center text-white font-bold">
                        <?= strtoupper(substr($_SESSION['user_first_name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">
                            <?= htmlspecialchars($_SESSION['user_first_name'] ?? 'Admin') ?> <?= htmlspecialchars($_SESSION['user_last_name'] ?? '') ?>
                        </p>
                        <p class="text-xs text-gray-400 truncate">Administrator</p>
                    </div>
                    <a href="<?= url('logout.php') ?>" class="text-gray-400 hover:text-red-400 transition" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Help Link -->
                        <a href="<?= url('admin/help.php') ?>" 
                           class="inline-flex items-center px-3 py-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition text-sm font-medium">
                            <i class="fas fa-question-circle mr-2"></i>Help
                        </a>
                        
                        <!-- Quick Actions Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition shadow-sm">
                                <i class="fas fa-plus mr-2"></i>
                                <span>Quick Add</span>
                                <i class="fas fa-chevron-down ml-2 text-xs"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                                <a href="?page=users" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-user-plus w-5 mr-3 text-blue-500"></i>Add User
                                </a>
                                <a href="?page=courses" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-book w-5 mr-3 text-purple-500"></i>Add Course
                                </a>
                                <a href="?page=announcements" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-bullhorn w-5 mr-3 text-orange-500"></i>Send Announcement
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Flash Messages -->
            <?php if ($flash = getFlash()): ?>
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                <?= $flash ?>
            </div>
            <?php endif; ?>
            
            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto">
                <?php if ($page === 'dashboard'): ?>
                    <!-- Enhanced Dashboard -->
                    <div class="p-4 sm:p-6 lg:p-8">
                        <!-- Welcome Section -->
                        <div class="mb-8">
                            <h1 class="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
                            <p class="text-gray-500 mt-1">Welcome back! Here's what's happening with your LMS.</p>
                        </div>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                            <div class="stat-card bg-white rounded-2xl p-6 shadow-card border border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Total Students</p>
                                        <p class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($totalStudents) ?></p>
                                        <p class="text-xs text-green-600 mt-1"><i class="fas fa-arrow-up mr-1"></i>Active learners</p>
                                    </div>
                                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center">
                                        <i class="fas fa-users text-blue-500 text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="stat-card bg-white rounded-2xl p-6 shadow-card border border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Active Courses</p>
                                        <p class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($activeCourses) ?></p>
                                        <p class="text-xs text-purple-600 mt-1">Published & Live</p>
                                    </div>
                                    <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center">
                                        <i class="fas fa-book text-purple-500 text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="stat-card bg-white rounded-2xl p-6 shadow-card border border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Total Enrollments</p>
                                        <p class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($totalEnrollments) ?></p>
                                        <p class="text-xs text-green-600 mt-1">All time</p>
                                    </div>
                                    <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center">
                                        <i class="fas fa-user-graduate text-green-500 text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="stat-card bg-white rounded-2xl p-6 shadow-card border border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Pending Payments</p>
                                        <p class="text-3xl font-bold text-gray-900 mt-1"><?= $currency ?> <?= number_format($pendingAmount, 0) ?></p>
                                        <p class="text-xs text-orange-600 mt-1">Awaiting approval</p>
                                    </div>
                                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center">
                                        <i class="fas fa-money-bill-wave text-orange-500 text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Needs Attention -->
                        <?php
                        $pendingContacts = $db->fetchColumn("SELECT COUNT(*) FROM contacts WHERE is_read = 0") ?: 0;
                        $upcomingEvents = $db->fetchColumn("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()") ?: 0;
                        $totalReviews = $db->fetchColumn("SELECT COUNT(*) FROM course_reviews") ?: 0;
                        $scheduledSessions = $db->fetchColumn("SELECT COUNT(*) FROM live_sessions WHERE status = 'scheduled'") ?: 0;
                        $pendingEmails = $db->fetchColumn("SELECT COUNT(*) FROM email_queue WHERE status = 'pending'") ?: 0;
                        ?>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                            <a href="?page=contacts" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition <?= $pendingContacts > 0 ? 'ring-2 ring-blue-100' : '' ?>">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">New Contacts</p>
                                        <p class="text-2xl font-bold text-gray-900"><?= $pendingContacts ?></p>
                                    </div>
                                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-envelope text-blue-600"></i>
                                    </div>
                                </div>
                            </a>
                            <a href="?page=events" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition <?= $upcomingEvents > 0 ? 'ring-2 ring-green-100' : '' ?>">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Upcoming Events</p>
                                        <p class="text-2xl font-bold text-gray-900"><?= $upcomingEvents ?></p>
                                    </div>
                                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-calendar-alt text-green-600"></i>
                                    </div>
                                </div>
                            </a>
                            <a href="?page=reviews" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Total Reviews</p>
                                        <p class="text-2xl font-bold text-gray-900"><?= $totalReviews ?></p>
                                    </div>
                                    <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-star text-yellow-600"></i>
                                    </div>
                                </div>
                            </a>
                            <a href="?page=live-sessions" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition <?= $scheduledSessions > 0 ? 'ring-2 ring-purple-100' : '' ?>">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Live Sessions</p>
                                        <p class="text-2xl font-bold text-gray-900"><?= $scheduledSessions ?></p>
                                    </div>
                                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-video text-purple-600"></i>
                                    </div>
                                </div>
                            </a>
                            <a href="?page=email-queue" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition <?= $pendingEmails > 0 ? 'ring-2 ring-orange-100' : '' ?>">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Email Queue</p>
                                        <p class="text-2xl font-bold text-gray-900"><?= $pendingEmails ?></p>
                                    </div>
                                    <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-paper-plane text-orange-600"></i>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Charts and Activity -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                            <!-- Enrollment Chart -->
                            <div class="lg:col-span-2 bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-4">Enrollment Trends (6 Months)</h3>
                                <div class="h-64">
                                    <canvas id="enrollmentChart"></canvas>
                                </div>
                            </div>
                            
                            <!-- Completion Stats -->
                            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-4">Course Completion</h3>
                                <div class="space-y-4">
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">Completed</span>
                                            <span class="font-semibold text-green-600"><?= $completionStats['completed'] ?? 0 ?></span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: <?= $totalEnrollments ? round((($completionStats['completed'] ?? 0) / $totalEnrollments) * 100) : 0 ?>"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">In Progress</span>
                                            <span class="font-semibold text-blue-600"><?= $completionStats['in_progress'] ?? 0 ?></span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $totalEnrollments ? round((($completionStats['in_progress'] ?? 0) / $totalEnrollments) * 100) : 0 ?>"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">Not Started</span>
                                            <span class="font-semibold text-gray-500"><?= $completionStats['not_started'] ?? 0 ?></span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-gray-400 h-2 rounded-full" style="width: <?= $totalEnrollments ? round((($completionStats['not_started'] ?? 0) / $totalEnrollments) * 100) : 0 ?>"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity & Quick Stats -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Recent Activity Feed -->
                            <div class="lg:col-span-2 bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-gray-900">Recent Activity</h3>
                                    <a href="?page=activity-logs" class="text-sm text-primary-600 hover:text-primary-700">View Logs</a>
                                </div>
                                <div class="divide-y divide-gray-100">
                                    <?php
                                    $recentActivity = $db->fetchAll("
                                        SELECT 'enrollment' as type, e.enrolled_at as date, CONCAT(u.first_name, ' ', u.last_name) as user_name, c.title as item_name, null as action_type
                                        FROM enrollments e
                                        JOIN users u ON e.user_id = u.id
                                        JOIN courses c ON e.course_id = c.id
                                        ORDER BY e.enrolled_at DESC
                                        LIMIT 3
                                    ");
                                    $recentLogs = $db->fetchAll("
                                        SELECT a.activity_type as type, a.created_at as date, CONCAT(u.first_name, ' ', u.last_name) as user_name, a.entity_type as item_name, a.activity_type as action_type
                                        FROM activity_logs a
                                        LEFT JOIN users u ON a.user_id = u.id
                                        ORDER BY a.created_at DESC
                                        LIMIT 4
                                    ");
                                    $combined = array_merge($recentActivity, $recentLogs);
                                    usort($combined, function($a, $b) {
                                        return strtotime($b['date']) <=> strtotime($a['date']);
                                    });
                                    $combined = array_slice($combined, 0, 6);
                                    foreach ($combined as $activity):
                                        $isEnrollment = $activity['type'] === 'enrollment';
                                        $iconClass = $isEnrollment ? 'bg-green-100 text-green-600' : match($activity['action_type'] ?? '') {
                                            'login' => 'bg-blue-100 text-blue-600',
                                            'logout' => 'bg-gray-100 text-gray-600',
                                            'create' => 'bg-green-100 text-green-600',
                                            'update' => 'bg-yellow-100 text-yellow-600',
                                            'delete' => 'bg-red-100 text-red-600',
                                            'payment' => 'bg-emerald-100 text-emerald-600',
                                            default => 'bg-gray-100 text-gray-600'
                                        };
                                        $icon = $isEnrollment ? 'fa-user-plus' : match($activity['action_type'] ?? '') {
                                            'login' => 'fa-sign-in-alt',
                                            'logout' => 'fa-sign-out-alt',
                                            'create' => 'fa-plus',
                                            'update' => 'fa-edit',
                                            'delete' => 'fa-trash',
                                            'payment' => 'fa-money-bill',
                                            default => 'fa-cog'
                                        };
                                        $text = $isEnrollment 
                                            ? 'Enrolled in ' . htmlspecialchars($activity['item_name'])
                                            : ucfirst($activity['action_type'] ?? 'performed action') . ' ' . htmlspecialchars($activity['item_name'] ?? '');
                                    ?>
                                        <div class="p-4 flex items-center hover:bg-gray-50/50 transition">
                                            <div class="w-10 h-10 rounded-full <?= $iconClass ?> flex items-center justify-center">
                                                <i class="fas <?= $icon ?>"></i>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($activity['user_name'] ?? 'System') ?></p>
                                                <p class="text-xs text-gray-500"><?= $text ?></p>
                                            </div>
                                            <span class="text-xs text-gray-400"><?= date('M j, H:i', strtotime($activity['date'])) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($combined)): ?>
                                        <div class="p-8 text-center text-gray-500">
                                            <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                            <p>No recent activity</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- System Overview & Upcoming -->
                            <div class="space-y-6">
                                <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">System Overview</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-chalkboard-teacher text-purple-600 text-sm"></i>
                                                </div>
                                                <span class="text-sm text-gray-700">Instructors</span>
                                            </div>
                                            <span class="text-lg font-bold text-gray-900"><?= $totalInstructors ?></span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-book text-blue-600 text-sm"></i>
                                                </div>
                                                <span class="text-sm text-gray-700">Total Courses</span>
                                            </div>
                                            <span class="text-lg font-bold text-gray-900"><?= $db->count('courses') ?></span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-certificate text-yellow-600 text-sm"></i>
                                                </div>
                                                <span class="text-sm text-gray-700">Certificates</span>
                                            </div>
                                            <span class="text-lg font-bold text-gray-900"><?= $db->count('certificates') ?></span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-money-bill text-green-600 text-sm"></i>
                                                </div>
                                                <span class="text-sm text-gray-700">Total Revenue</span>
                                            </div>
                                            <span class="text-lg font-bold text-gray-900"><?= $currency ?> <?= number_format($db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'Completed'") ?? 0, 0) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upcoming Events -->
                                <?php $upcomingEventsList = $db->fetchAll("SELECT title, event_date, location FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3"); ?>
                                <?php if ($upcomingEventsList): ?>
                                <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-bold text-gray-900">Upcoming Events</h3>
                                        <a href="?page=events" class="text-xs text-primary-600 hover:text-primary-700">View All</a>
                                    </div>
                                    <div class="space-y-3">
                                        <?php foreach ($upcomingEventsList as $evt): ?>
                                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-calendar-alt text-green-600 text-sm"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($evt['title']) ?></p>
                                                <p class="text-xs text-gray-500"><?= date('M j, Y', strtotime($evt['event_date'])) ?></p>
                                                <?php if ($evt['location']): ?>
                                                    <p class="text-xs text-gray-400"><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($evt['location']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Upcoming Live Sessions -->
                                <?php $upcomingSessions = $db->fetchAll("SELECT l.title as lesson_title, ls.scheduled_start_time, c.title as course_title FROM live_sessions ls JOIN lessons l ON ls.lesson_id = l.id JOIN modules m ON l.module_id = m.id JOIN courses c ON m.course_id = c.id WHERE ls.status = 'scheduled' AND ls.scheduled_start_time >= NOW() ORDER BY ls.scheduled_start_time ASC LIMIT 3"); ?>
                                <?php if ($upcomingSessions): ?>
                                <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-bold text-gray-900">Live Sessions</h3>
                                        <a href="?page=live-sessions" class="text-xs text-primary-600 hover:text-primary-700">View All</a>
                                    </div>
                                    <div class="space-y-3">
                                        <?php foreach ($upcomingSessions as $s): ?>
                                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-video text-purple-600 text-sm"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($s['lesson_title']) ?></p>
                                                <p class="text-xs text-gray-500"><?= date('M j, H:i', strtotime($s['scheduled_start_time'])) ?></p>
                                                <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($s['course_title']) ?></p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <script>
                        // Enrollment Chart
                        const ctx = document.getElementById('enrollmentChart');
                        if (ctx) {
                            const enrollmentData = <?= json_encode($monthlyEnrollments) ?>;
                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: enrollmentData.map(d => {
                                        const [year, month] = d.month.split('-');
                                        return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short' });
                                    }),
                                    datasets: [{
                                        label: 'Enrollments',
                                        data: enrollmentData.map(d => d.count),
                                        borderColor: '#3B82F6',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        fill: true,
                                        tension: 0.4,
                                        borderWidth: 2
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                                        x: { grid: { display: false } }
                                    }
                                }
                            });
                        }
                    </script>

                <?php elseif ($page === 'users'): ?>
                    <?php include 'pages/users.php'; ?>
                <?php elseif ($page === 'instructors'): ?>
                    <?php include 'pages/instructors.php'; ?>
                <?php elseif ($page === 'students'): ?>
                    <?php include 'pages/students.php'; ?>
                <?php elseif ($page === 'courses'): ?>
                    <?php include 'pages/courses.php'; ?>
                <?php elseif ($page === 'modules'): ?>
                    <?php include 'pages/modules.php'; ?>
                <?php elseif ($page === 'quizzes'): ?>
                    <?php include 'pages/quizzes.php'; ?>
                <?php elseif ($page === 'assignments'): ?>
                    <?php include 'pages/assignments.php'; ?>
                <?php elseif ($page === 'enrollments'): ?>
                    <?php include 'pages/enrollments.php'; ?>
                <?php elseif ($page === 'certificates'): ?>
                    <?php include 'pages/certificates.php'; ?>
                <?php elseif ($page === 'badges'): ?>
                    <?php include 'pages/badges.php'; ?>
                <?php elseif ($page === 'live-sessions'): ?>
                    <?php include 'pages/live-sessions.php'; ?>
                <?php elseif ($page === 'announcements'): ?>
                    <?php include 'pages/announcements.php'; ?>
                <?php elseif ($page === 'events'): ?>
                    <?php include 'pages/events.php'; ?>
                <?php elseif ($page === 'hero-slides'): ?>
                    <?php include 'pages/hero-slides.php'; ?>
                <?php elseif ($page === 'institution-photos'): ?>
                    <?php include 'pages/institution-photos.php'; ?>
                <?php elseif ($page === 'email-templates'): ?>
                    <?php include 'pages/email-templates.php'; ?>
                <?php elseif ($page === 'discussions'): ?>
                    <?php include 'pages/discussions.php'; ?>
                <?php elseif ($page === 'reviews'): ?>
                    <?php include 'pages/reviews.php'; ?>
                <?php elseif ($page === 'contacts'): ?>
                    <?php include 'pages/contacts.php'; ?>
                <?php elseif ($page === 'financials'): ?>
                    <?php include 'pages/financials.php'; ?>
                <?php elseif ($page === 'payment-methods'): ?>
                    <?php include 'pages/payment-methods.php'; ?>
                <?php elseif ($page === 'registration-fees'): ?>
                    <?php include 'pages/registration-fees.php'; ?>
                <?php elseif ($page === 'email-queue'): ?>
                    <?php include 'pages/email-queue.php'; ?>
                <?php elseif ($page === 'notifications'): ?>
                    <?php include 'pages/notifications.php'; ?>
                <?php elseif ($page === 'newsletter-subscribers'): ?>
                    <?php include 'pages/newsletter-subscribers.php'; ?>
                <?php elseif ($page === 'reports'): ?>
                    <?php include 'pages/reports.php'; ?>
                <?php elseif ($page === 'activity-logs'): ?>
                    <?php include 'pages/activity-logs.php'; ?>
                <?php elseif ($page === 'company-profile'): ?>
                    <?php include 'pages/company-profile.php'; ?>
                <?php elseif ($page === 'settings'): ?>
                    <?php include 'pages/settings.php'; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-xl text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} shadow-lg z-50 animate-fade-in`;
            toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
