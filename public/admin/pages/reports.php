<?php
/**
 * Reports & Analytics Page
 */
$period = $_GET['period'] ?? 'month';
$now = date('Y-m-d');

switch ($period) {
    case 'week':
        $start = date('Y-m-d', strtotime('-7 days'));
        break;
    case 'year':
        $start = date('Y-m-d', strtotime('-1 year'));
        break;
    default:
        $start = date('Y-m-01');
}

$revenue = $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'Completed' AND created_at >= ?", [$start]);
$newStudents = $db->fetchColumn("SELECT COUNT(*) FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE r.role_name = 'Student' AND u.created_at >= ?", [$start]);
$newEnrollments = $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrolled_at >= ?", [$start]);
$completedPayments = $db->fetchColumn("SELECT COUNT(*) FROM payments WHERE payment_status = 'Completed' AND created_at >= ?", [$start]);

$revenueByMethod = $db->fetchAll("SELECT pm.method_name as payment_method, COALESCE(SUM(p.amount), 0) as total, COUNT(*) as count 
    FROM payments p 
    LEFT JOIN payment_methods pm ON p.payment_method_id = pm.payment_method_id
    WHERE p.payment_status = 'Completed' AND p.created_at >= ? 
    GROUP BY pm.method_name", [$start]);
$topCourses = $db->fetchAll("SELECT c.title, COUNT(e.id) as enrollments FROM courses c LEFT JOIN enrollments e ON c.id = e.course_id WHERE e.enrolled_at >= ? GROUP BY c.id ORDER BY enrollments DESC LIMIT 5", [$start]);
$enrollmentTrend = $db->fetchAll("SELECT enrolled_at as date, COUNT(*) as count FROM enrollments WHERE enrolled_at >= ? GROUP BY enrolled_at ORDER BY date", [$start]);
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
            <p class="text-gray-500 mt-1">Financial and enrollment insights</p>
        </div>
        <div class="flex gap-2">
            <a href="?page=reports&period=week" class="px-3 py-2 rounded-lg text-sm font-medium <?= $period === 'week' ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>">7 Days</a>
            <a href="?page=reports&period=month" class="px-3 py-2 rounded-lg text-sm font-medium <?= $period === 'month' ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>">This Month</a>
            <a href="?page=reports&period=year" class="px-3 py-2 rounded-lg text-sm font-medium <?= $period === 'year' ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>">This Year</a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Revenue</p>
                    <p class="text-2xl font-bold text-gray-900">K<?= number_format($revenue, 2) ?></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center"><i class="fas fa-money-bill-wave text-green-600"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">New Students</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $newStudents ?></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center"><i class="fas fa-user-plus text-blue-600"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">New Enrollments</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $newEnrollments ?></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center"><i class="fas fa-graduation-cap text-purple-600"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Payments</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $completedPayments ?></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center"><i class="fas fa-check-circle text-yellow-600"></i></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue by Method -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue by Payment Method</h3>
            <?php if ($revenueByMethod): ?>
            <div class="space-y-3">
                <?php foreach ($revenueByMethod as $m): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700 capitalize"><?= htmlspecialchars($m['payment_method'] ?? 'Unknown') ?></span>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-900">K<?= number_format($m['total'], 2) ?></p>
                        <p class="text-xs text-gray-500"><?= $m['count'] ?> transactions</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-gray-500 text-center py-8">No payment data for this period</p>
            <?php endif; ?>
        </div>

        <!-- Top Courses -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Enrolled Courses</h3>
            <?php if ($topCourses): ?>
            <div class="space-y-3">
                <?php foreach ($topCourses as $c): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700 line-clamp-1"><?= htmlspecialchars($c['title']) ?></span>
                    <span class="px-2 py-1 bg-primary-100 text-primary-700 rounded text-xs font-semibold"><?= $c['enrollments'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-gray-500 text-center py-8">No enrollment data for this period</p>
            <?php endif; ?>
        </div>
    </div>
</div>
