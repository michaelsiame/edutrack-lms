<?php
/**
 * Admin Payment Reports
 */

require_once '../../../src/includes/admin-debug.php';
require_once '../../../src/middleware/admin-only.php';

// Get date range
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

// Get revenue stats
$totalRevenue = $db->fetchColumn(
    "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'completed' AND created_at BETWEEN ? AND ?",
    [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
);

$totalTransactions = $db->fetchColumn(
    "SELECT COUNT(*) FROM payments WHERE payment_status = 'completed' AND created_at BETWEEN ? AND ?",
    [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
);

$avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

// Get monthly revenue for chart
$monthlyRevenue = $db->fetchAll("
    SELECT
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COALESCE(SUM(amount), 0) as revenue,
        COUNT(*) as transactions
    FROM payments
    WHERE payment_status = 'completed'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");

// Get top courses by revenue
$topCourses = $db->fetchAll("
    SELECT c.title, COUNT(p.id) as sales, COALESCE(SUM(p.amount), 0) as revenue
    FROM payments p
    JOIN courses c ON p.course_id = c.id
    WHERE p.payment_status = 'completed'
    AND p.created_at BETWEEN ? AND ?
    GROUP BY c.id
    ORDER BY revenue DESC
    LIMIT 10
", [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

// Get payment methods breakdown
$paymentMethods = $db->fetchAll("
    SELECT payment_method, COUNT(*) as count, COALESCE(SUM(amount), 0) as total
    FROM payments
    WHERE payment_status = 'completed'
    AND created_at BETWEEN ? AND ?
    GROUP BY payment_method
    ORDER BY total DESC
", [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

$page_title = 'Payment Reports';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-chart-bar text-primary-600 mr-2"></i>Payment Reports
            </h1>
            <p class="text-gray-600 mt-1">Revenue analytics and insights</p>
        </div>
        <a href="<?= url('admin/payments/index.php') ?>" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i>Back to Payments
        </a>
    </div>

    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="<?= $startDate ?>"
                       class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="<?= $endDate ?>"
                       class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>
            <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700">
                <i class="fas fa-search mr-2"></i>Apply Filter
            </button>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900">ZMW <?= number_format($totalRevenue, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Transactions</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($totalTransactions) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Average Transaction</p>
                    <p class="text-2xl font-bold text-gray-900">ZMW <?= number_format($avgTransaction, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Courses by Revenue -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Top Courses by Revenue</h2>
            </div>
            <div class="p-6">
                <?php if (empty($topCourses)): ?>
                    <p class="text-gray-500 text-center py-8">No data available for this period</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($topCourses as $course): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        <?= sanitize($course['title']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500"><?= $course['sales'] ?> sales</p>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">
                                    ZMW <?= number_format($course['revenue'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Payment Methods</h2>
            </div>
            <div class="p-6">
                <?php if (empty($paymentMethods)): ?>
                    <p class="text-gray-500 text-center py-8">No data available for this period</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($paymentMethods as $method): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-credit-card text-gray-400 mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?= sanitize(ucfirst($method['payment_method'] ?? 'Unknown')) ?>
                                        </p>
                                        <p class="text-xs text-gray-500"><?= $method['count'] ?> transactions</p>
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">
                                    ZMW <?= number_format($method['total'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue Table -->
    <div class="bg-white rounded-lg shadow mt-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Monthly Revenue (Last 12 Months)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transactions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($monthlyRevenue)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                No data available
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach (array_reverse($monthlyRevenue) as $row): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?= date('F Y', strtotime($row['month'] . '-01')) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= number_format($row['transactions']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    ZMW <?= number_format($row['revenue'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
