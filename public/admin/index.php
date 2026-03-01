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
$validPages = ['dashboard', 'users', 'courses', 'modules', 'enrollments', 'financials', 'settings', 'announcements', 'help'];
if (!in_array($page, $validPages)) {
    $page = 'dashboard';
}

// Fetch database and settings early
$db = Database::getInstance();
$settings = $db->fetchOne("SELECT * FROM system_settings WHERE setting_id = 1");
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

// Count students (users with role_id=4 which is 'Student')
$totalStudents = $db->fetchColumn("
    SELECT COUNT(DISTINCT u.id)
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    JOIN roles r ON ur.role_id = r.id
    WHERE r.role_name = 'Student'
") ?: 0;

// Count instructors (role_id=3 which is 'Instructor')
$totalInstructors = $db->fetchColumn("
    SELECT COUNT(DISTINCT u.id)
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    JOIN roles r ON ur.role_id = r.id
    WHERE r.role_name = 'Instructor'
") ?: 0;

$activeCourses = $db->count('courses', "status = 'published'");
$totalEnrollments = $db->count('enrollments');

// Pending payments from payments table
$pendingPayments = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'Pending'");
$pendingAmount = $pendingPayments['total'] ?? 0;

// Get monthly enrollment data for chart
$monthlyEnrollments = $db->fetchAll("
    SELECT DATE_FORMAT(enrolled_at, '%Y-%m') as month, COUNT(*) as count
    FROM enrollments
    WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(enrolled_at, '%Y-%m')
    ORDER BY month ASC
");

// Get recent activity
$recentActivity = $db->fetchAll("
    SELECT 'enrollment' as type, e.enrolled_at as date, CONCAT(u.first_name, ' ', u.last_name) as user_name, c.title as item_name
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
    LIMIT 5
");

// Get course completion stats
$completionStats = $db->fetchOne("
    SELECT 
        COUNT(CASE WHEN progress = 100 THEN 1 END) as completed,
        COUNT(CASE WHEN progress > 0 AND progress < 100 THEN 1 END) as in_progress,
        COUNT(CASE WHEN progress = 0 THEN 1 END) as not_started
    FROM enrollments
");

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    </style>
</head>
<body class="bg-gray-50">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
        
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"></div>
        
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static flex flex-col">
            
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

                <!-- Management -->
                <div class="px-4 py-2 mt-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Management</span>
                </div>
                <a href="?page=users" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'users' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'users' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <span>Users</span>
                    <span class="ml-auto bg-primary-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $totalStudents ?></span>
                </a>
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
                    <span>Modules</span>
                </a>
                <a href="?page=enrollments" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'enrollments' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'enrollments' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <span>Enrollments</span>
                </a>

                <!-- Finance & Communication -->
                <div class="px-4 py-2 mt-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Finance & Communication</span>
                </div>
                <a href="?page=financials" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'financials' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'financials' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <span>Financials</span>
                </a>
                <a href="?page=announcements" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $page === 'announcements' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                    <div class="w-8 h-8 rounded-lg <?= $page === 'announcements' ? 'bg-white/20' : 'bg-slate-700/50' ?> flex items-center justify-center mr-3">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <span>Announcements</span>
                </a>

                <!-- System -->
                <div class="px-4 py-2 mt-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">System</span>
                </div>
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
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white font-bold">
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
                            <!-- Recent Enrollments -->
                            <div class="lg:col-span-2 bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-gray-900">Recent Activity</h3>
                                    <a href="?page=enrollments" class="text-sm text-primary-600 hover:text-primary-700">View All</a>
                                </div>
                                <div class="divide-y divide-gray-100">
                                    <?php
                                    $recentEnrollments = $db->fetchAll("
                                        SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as full_name, c.title
                                        FROM enrollments e
                                        JOIN users u ON e.user_id = u.id
                                        JOIN courses c ON e.course_id = c.id
                                        ORDER BY e.enrolled_at DESC
                                        LIMIT 5
                                    ");
                                    foreach ($recentEnrollments as $enrollment): 
                                        $initials = strtoupper(substr($enrollment['full_name'] ?? 'U', 0, 1));
                                    ?>
                                        <div class="p-4 flex items-center hover:bg-gray-50/50 transition">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold">
                                                <?= $initials ?>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($enrollment['full_name'] ?? 'Unknown') ?></p>
                                                <p class="text-xs text-gray-500">Enrolled in <?= htmlspecialchars($enrollment['title']) ?></p>
                                            </div>
                                            <span class="text-xs text-gray-400"><?= date('M j, Y', strtotime($enrollment['enrolled_at'])) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($recentEnrollments)): ?>
                                        <div class="p-8 text-center text-gray-500">
                                            <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                            <p>No recent activity</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-4">System Overview</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mr-3">
                                                <i class="fas fa-chalkboard-teacher text-purple-600"></i>
                                            </div>
                                            <span class="text-gray-700">Instructors</span>
                                        </div>
                                        <span class="text-xl font-bold text-gray-900"><?= $totalInstructors ?></span>
                                    </div>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-3">
                                                <i class="fas fa-book text-blue-600"></i>
                                            </div>
                                            <span class="text-gray-700">Total Courses</span>
                                        </div>
                                        <span class="text-xl font-bold text-gray-900"><?= $db->count('courses') ?></span>
                                    </div>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center mr-3">
                                                <i class="fas fa-certificate text-yellow-600"></i>
                                            </div>
                                            <span class="text-gray-700">Certificates</span>
                                        </div>
                                        <span class="text-xl font-bold text-gray-900"><?= $db->count('certificates') ?></span>
                                    </div>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mr-3">
                                                <i class="fas fa-money-bill text-green-600"></i>
                                            </div>
                                            <span class="text-gray-700">Total Revenue</span>
                                        </div>
                                        <span class="text-xl font-bold text-gray-900"><?= $currency ?> <?= number_format($db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'Completed'") ?? 0, 0) ?></span>
                                    </div>
                                </div>
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
                <?php elseif ($page === 'courses'): ?>
                    <?php include 'pages/courses.php'; ?>
                <?php elseif ($page === 'modules'): ?>
                    <?php include 'pages/modules.php'; ?>
                <?php elseif ($page === 'enrollments'): ?>
                    <?php include 'pages/enrollments.php'; ?>
                <?php elseif ($page === 'financials'): ?>
                    <?php include 'pages/financials.php'; ?>
                <?php elseif ($page === 'announcements'): ?>
                    <?php include 'pages/announcements.php'; ?>
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
