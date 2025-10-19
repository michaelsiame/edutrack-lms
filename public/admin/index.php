<?php
/**
 * Admin Dashboard
 * Main admin panel overview
 */

require_once '../../src/middleware/admin-only.php';
require_once '../../src/classes/User.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Payment.php';
require_once '../../src/classes/Certificate.php';

// Get dashboard statistics
$stats = [
    'total_students' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'student'"),
    'total_instructors' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'instructor'"),
    'total_courses' => $db->fetchColumn("SELECT COUNT(*) FROM courses"),
    'published_courses' => $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE status = 'published'"),
    'total_enrollments' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments"),
    'active_enrollments' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'active'"),
    'completed_enrollments' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'completed'"),
    'total_certificates' => $db->fetchColumn("SELECT COUNT(*) FROM certificates"),
    'pending_payments' => $db->fetchColumn("SELECT COUNT(*) FROM payments WHERE status = 'pending'"),
    'total_revenue' => $db->fetchColumn("SELECT SUM(amount) FROM payments WHERE status = 'completed'") ?? 0,
];

// Recent activity
$recentUsers = $db->fetchAll("
    SELECT id, first_name, last_name, email, role, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");

$recentPayments = Payment::all(['limit' => 5, 'order' => 'created_at DESC']);
$recentEnrollments = $db->fetchAll("
    SELECT e.*, u.first_name, u.last_name, c.title as course_title
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
    LIMIT 5
");

// Monthly revenue chart data (last 6 months)
$revenueData = $db->fetchAll("
    SELECT 
        DATE_FORMAT(payment_date, '%Y-%m') as month,
        SUM(amount) as revenue
    FROM payments
    WHERE status = 'completed'
    AND payment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY month ASC
");

$page_title = 'Admin Dashboard';
require_once '../../src/templates/admin-header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-tachometer-alt text-primary-600 mr-2"></i>
                    Admin Dashboard
                </h1>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-600">
                        <i class="far fa-clock mr-1"></i>
                        <?= date('l, F j, Y') ?>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Total Students -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= number_format($stats['total_students']) ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-user-graduate text-blue-600 text-2xl"></i>
                    </div>
                </div>
                <a href="<?= url('admin/users/index.php?role=student') ?>" class="text-sm text-blue-600 hover:text-blue-700 mt-4 inline-block">
                    View all students →
                </a>
            </div>

            <!-- Total Courses -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Courses</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= number_format($stats['total_courses']) ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?= $stats['published_courses'] ?> published</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-book text-green-600 text-2xl"></i>
                    </div>
                </div>
                <a href="<?= url('admin/courses/index.php') ?>" class="text-sm text-green-600 hover:text-green-700 mt-4 inline-block">
                    Manage courses →
                </a>
            </div>

            <!-- Revenue -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= formatCurrency($stats['total_revenue']) ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?= $stats['pending_payments'] ?> pending</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-money-bill-wave text-yellow-600 text-2xl"></i>
                    </div>
                </div>
                <a href="<?= url('admin/payments/index.php') ?>" class="text-sm text-yellow-600 hover:text-yellow-700 mt-4 inline-block">
                    View payments →
                </a>
            </div>

            <!-- Enrollments -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Enrollments</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= number_format($stats['active_enrollments']) ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?= $stats['completed_enrollments'] ?> completed</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-clipboard-check text-purple-600 text-2xl"></i>
                    </div>
                </div>
                <a href="<?= url('admin/enrollments/index.php') ?>" class="text-sm text-purple-600 hover:text-purple-700 mt-4 inline-block">
                    View enrollments →
                </a>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <!-- Revenue Chart -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Revenue Overview (6 Months)</h2>
                </div>
                <div class="p-6">
                    <canvas id="revenueChart" height="200"></canvas>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Quick Actions</h2>
                </div>
                <div class="p-6 grid grid-cols-2 gap-4">
                    <a href="<?= url('admin/courses/create.php') ?>" 
                       class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition">
                        <i class="fas fa-plus-circle text-3xl text-primary-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700">Create Course</span>
                    </a>
                    <a href="<?= url('admin/users/create.php') ?>" 
                       class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition">
                        <i class="fas fa-user-plus text-3xl text-primary-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700">Add User</span>
                    </a>
                    <a href="<?= url('admin/payments/verify.php') ?>" 
                       class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition">
                        <i class="fas fa-check-circle text-3xl text-primary-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700">Verify Payments</span>
                    </a>
                    <a href="<?= url('admin/certificates/issue.php') ?>" 
                       class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition">
                        <i class="fas fa-certificate text-3xl text-primary-600 mb-2"></i>
                        <span class="text-sm font-medium text-gray-700">Issue Certificate</span>
                    </a>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Recent Users -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">Recent Users</h2>
                    <a href="<?= url('admin/users/index.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">View all</a>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($recentUsers as $user): ?>
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900"><?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?></p>
                                <p class="text-sm text-gray-600"><?= sanitize($user['email']) ?></p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $user['role'] == 'instructor' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1"><?= timeAgo($user['created_at']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">Recent Payments</h2>
                    <a href="<?= url('admin/payments/index.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">View all</a>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($recentPayments as $payment): ?>
                    <?php $p = new Payment($payment['id']); ?>
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <p class="font-medium text-gray-900"><?= formatCurrency($p->getAmount()) ?></p>
                            <?= $p->getStatusBadge() ?>
                        </div>
                        <p class="text-sm text-gray-600"><?= sanitize($p->getUserName()) ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?= timeAgo($p->getCreatedAt()) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Enrollments -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">Recent Enrollments</h2>
                    <a href="<?= url('admin/enrollments/index.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">View all</a>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($recentEnrollments as $enrollment): ?>
                    <div class="p-4 hover:bg-gray-50">
                        <p class="font-medium text-gray-900"><?= sanitize($enrollment['course_title']) ?></p>
                        <p class="text-sm text-gray-600"><?= sanitize($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?= timeAgo($enrollment['enrolled_at']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($revenueData, 'month')) ?>,
        datasets: [{
            label: 'Revenue (ZMW)',
            data: <?= json_encode(array_column($revenueData, 'revenue')) ?>,
            borderColor: '#2E70DA',
            backgroundColor: 'rgba(46, 112, 218, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'K' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

<?php require_once '../../src/templates/admin-footer.php'; ?>