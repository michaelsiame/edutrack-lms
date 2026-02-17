<?php
/**
 * Admin Dashboard - Simple PHP Version
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
$validPages = ['dashboard', 'users', 'courses', 'modules', 'enrollments', 'financials', 'settings', 'announcements', 'departments', 'reports'];
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
    // Handle AJAX requests
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

    // Handle form submissions
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

// Process Departments page handlers
if ($page === 'departments') {
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        if ($_GET['ajax'] === 'get_department' && isset($_GET['id'])) {
            $dept = $db->fetchOne("SELECT * FROM departments WHERE id = ?", [(int)$_GET['id']]);
            echo json_encode($dept ?: ['error' => 'Department not found']);
            exit;
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include 'handlers/departments_handler.php';
    }
}

// Process Announcements page handlers
if ($page === 'announcements') {
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        if ($_GET['ajax'] === 'get_announcement' && isset($_GET['id'])) {
            $announcement = $db->fetchOne("SELECT * FROM announcements WHERE announcement_id = ?", [(int)$_GET['id']]);
            echo json_encode($announcement ?: ['error' => 'Announcement not found']);
            exit;
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include 'handlers/announcements_handler.php';
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-800 text-white flex-shrink-0 min-h-screen">
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-xl font-bold flex items-center gap-2">
                    <i class="fas fa-graduation-cap text-blue-400"></i>
                    EduTrack Admin
                </h1>
            </div>
            <nav class="p-4 flex flex-col h-[calc(100vh-80px)]">
                <!-- Main Navigation -->
                <div class="flex-1">
                    <p class="text-xs uppercase text-slate-500 font-semibold mb-2 px-4">Main</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="?page=dashboard" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?= $page === 'dashboard' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
                                <i class="fas fa-home w-5 mr-3"></i> Dashboard
                            </a>
                        </li>
                    </ul>

                    <p class="text-xs uppercase text-slate-500 font-semibold mb-2 px-4 mt-6">Management</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="?page=users" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?= $page === 'users' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
                                <i class="fas fa-users w-5 mr-3"></i> Users
                            </a>
                        </li>
                        <li>
                            <a href="?page=courses" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?= $page === 'courses' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
                                <i class="fas fa-book w-5 mr-3"></i> Courses
                            </a>
                        </li>
                        <li>
                            <a href="?page=modules" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?= $page === 'modules' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
                                <i class="fas fa-layer-group w-5 mr-3"></i> Modules
                            </a>
                        </li>
                        <li>
                            <a href="?page=enrollments" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?= $page === 'enrollments' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
                                <i class="fas fa-user-graduate w-5 mr-3"></i> Enrollments
                            </a>
                        </li>
                    </ul>

                    <p class="text-xs uppercase text-slate-500 font-semibold mb-2 px-4 mt-6">Organization</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="?page=departments" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?= $page === 'departments' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
                                <i class="fas fa-building w-5 mr-3"></i> Departments
                            </a>
                        </li>
                        <li>
                            <a href="?page=reports" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?= $page === 'reports' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
                                <i class="fas fa-chart-bar w-5 mr-3"></i> Reports
                            </a>
                        </li>
                    </ul>

                    <p class="text-xs uppercase text-slate-500 font-semibold mb-2 px-4 mt-6">Finance & Communication</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="?page=financials" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?= $page === 'financials' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
                                <i class="fas fa-money-bill-wave w-5 mr-3"></i> Financials
                            </a>
                        </li>
                        <li>
                            <a href="?page=announcements" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?= $page === 'announcements' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
                                <i class="fas fa-bullhorn w-5 mr-3"></i> Announcements
                            </a>
                        </li>
                    </ul>

                    <p class="text-xs uppercase text-slate-500 font-semibold mb-2 px-4 mt-6">System</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="?page=settings" class="flex items-center px-4 py-2.5 rounded-lg transition-colors <?= $page === 'settings' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
                                <i class="fas fa-cog w-5 mr-3"></i> Settings
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Bottom Navigation -->
                <div class="pt-4 border-t border-slate-700 mt-4">
                    <ul class="space-y-1">
                        <li>
                            <a href="<?= url('index.php') ?>" class="flex items-center px-4 py-2.5 rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                                <i class="fas fa-external-link-alt w-5 mr-3"></i> View Site
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('logout.php') ?>" class="flex items-center px-4 py-2.5 rounded-lg text-slate-300 hover:bg-red-600 hover:text-white transition-colors">
                                <i class="fas fa-sign-out-alt w-5 mr-3"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <?php if ($page === 'dashboard'): ?>
                <!-- Dashboard Page -->
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h2>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Students</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $totalStudents ?></h3>
                            </div>
                            <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Active Courses</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $activeCourses ?></h3>
                            </div>
                            <div class="p-3 bg-purple-100 text-purple-600 rounded-lg">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Enrollments</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $totalEnrollments ?></h3>
                            </div>
                            <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Pending Payments</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $currency ?> <?= number_format($pendingAmount, 2) ?></h3>
                            </div>
                            <div class="p-3 bg-yellow-100 text-yellow-600 rounded-lg">
                                <i class="fas fa-money-bill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border">
                        <div class="p-6 border-b">
                            <h3 class="font-semibold text-gray-800">Recent Enrollments</h3>
                        </div>
                        <div class="divide-y">
                            <?php
                            $recentEnrollments = $db->fetchAll("
                                SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as full_name, c.title
                                FROM enrollments e
                                JOIN users u ON e.user_id = u.id
                                JOIN courses c ON e.course_id = c.id
                                ORDER BY e.enrolled_at DESC
                                LIMIT 5
                            ");
                            foreach ($recentEnrollments as $enrollment): ?>
                                <div class="p-4 flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                        <?= strtoupper(substr($enrollment['full_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($enrollment['full_name'] ?? 'Unknown') ?></p>
                                        <p class="text-xs text-gray-500">Enrolled in <?= htmlspecialchars($enrollment['title']) ?></p>
                                    </div>
                                    <span class="ml-auto text-xs text-gray-400"><?= date('M j, Y', strtotime($enrollment['enrolled_at'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($recentEnrollments)): ?>
                                <div class="p-4 text-gray-500 text-center">No recent enrollments</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Quick Stats</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Instructors</span>
                                <span class="font-medium"><?= $totalInstructors ?></span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Total Courses</span>
                                <span class="font-medium"><?= $db->count('courses') ?></span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Certificates Issued</span>
                                <span class="font-medium"><?= $db->count('certificates') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

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
            <?php elseif ($page === 'departments'): ?>
                <?php include 'pages/departments.php'; ?>
            <?php elseif ($page === 'reports'): ?>
                <?php include 'pages/reports.php'; ?>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Simple toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} shadow-lg z-50`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    </script>
</body>
</html>
