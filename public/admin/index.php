<?php
/**
 * Admin Dashboard (FIXED)
 * Uses created_at instead of payment_date until database is updated
 */

require_once '../../src/middleware/admin-only.php';
require_once '../../src/classes/Statistics.php';
require_once '../../src/classes/Payment.php';

// Get dashboard statistics using Statistics class
$dashboardStats = Statistics::getAdminDashboard();

$stats = [
    'total_students' => $dashboardStats['users']['students'],
    'total_instructors' => $dashboardStats['users']['instructors'],
    'total_courses' => $dashboardStats['courses']['total'],
    'published_courses' => $dashboardStats['courses']['published'],
    'total_enrollments' => $dashboardStats['enrollments']['total'],
    'active_enrollments' => $dashboardStats['enrollments']['active'],
    'completed_enrollments' => $dashboardStats['enrollments']['completed'],
    'total_certificates' => $dashboardStats['certificates']['total'],
    'pending_payments' => $dashboardStats['revenue']['pending_payments'],
    'total_revenue' => $dashboardStats['revenue']['total'],
];

// Recent activity
$recentUsers = $db->fetchAll("
    SELECT id, first_name, last_name, email, role, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
");

$recentPayments = Statistics::getRecentPayments(5);
$recentEnrollments = Statistics::getRecentEnrollments(5);

// Monthly revenue chart data (last 6 months)
$revenueData = Statistics::getRevenueByPeriod(6);

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
            
            <!-- Students -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= number_format($stats['total_students']) ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                </div>
                <a href="<?= url('admin/users/index.php?role=student') ?>" class="text-sm text-blue-600 hover:text-blue-700 mt-4 inline-block">
                    View students →
                </a>
            </div>

            <!-- Courses -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Published Courses</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= number_format($stats['published_courses']) ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?= $stats['total_courses'] ?> total</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-book text-green-600 text-2xl"></i>
                    </div>
                </div>
                <a href="<?= url('admin/courses/index.php') ?>" class="text-sm text-green-600 hover:text-green-700 mt-4 inline-block">
                    View courses →
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

        <!-- Charts & Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            
            <!-- Revenue Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Revenue Trend (6 Months)</h2>
                <canvas id="revenueChart" height="200"></canvas>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="<?= url('admin/users/create.php') ?>" 
                       class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <i class="fas fa-user-plus text-blue-600 mr-3"></i>
                            <span class="font-medium text-gray-900">Add New User</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="<?= url('admin/courses/create.php') ?>" 
                       class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <i class="fas fa-book-plus text-green-600 mr-3"></i>
                            <span class="font-medium text-gray-900">Create New Course</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="<?= url('admin/payments/verify.php') ?>" 
                       class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-yellow-600 mr-3"></i>
                            <span class="font-medium text-gray-900">Verify Payments</span>
                            <?php if ($stats['pending_payments'] > 0): ?>
                            <span class="ml-2 px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                                <?= $stats['pending_payments'] ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                    <a href="<?= url('admin/certificates/index.php') ?>" 
                       class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <i class="fas fa-certificate text-purple-600 mr-3"></i>
                            <span class="font-medium text-gray-900">Manage Certificates</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Recent Users -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">Recent Users</h2>
                    <a href="<?= url('admin/users/index.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">View all</a>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($recentUsers as $user): ?>
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
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

<!-- Chart.js Script -->
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
            fill: true,
            tension: 0.4
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
                        return 'ZMW ' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

<?php require_once '../../src/templates/admin-footer.php'; ?>