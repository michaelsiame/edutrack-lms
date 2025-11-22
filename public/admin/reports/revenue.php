<?php
/**
 * Admin Revenue Report
 * Detailed revenue analytics and financial insights
 */

require_once '../../../src/middleware/admin-only.php';

// Date range filters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$courseId = $_GET['course_id'] ?? '';
$paymentStatus = $_GET['payment_status'] ?? '';

// Build query
$sql = "SELECT p.*,
        c.title as course_title,
        u.first_name, u.last_name, u.email
        FROM payments p
        JOIN courses c ON p.course_id = c.id
        JOIN students st ON p.student_id = st.id
        JOIN users u ON st.user_id = u.id
        WHERE p.created_at BETWEEN ? AND ?";

$params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];

if ($courseId) {
    $sql .= " AND p.course_id = ?";
    $params[] = $courseId;
}

if ($paymentStatus) {
    $sql .= " AND p.payment_status = ?";
    $params[] = $paymentStatus;
}

$sql .= " ORDER BY p.created_at DESC";
$payments = $db->fetchAll($sql, $params);

// Get summary statistics
$summaryParams = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
$summary = [
    'total_revenue' => (float) $db->fetchColumn("SELECT SUM(amount) FROM payments WHERE payment_status = 'Completed' AND created_at BETWEEN ? AND ?", $summaryParams) ?: 0,
    'pending_revenue' => (float) $db->fetchColumn("SELECT SUM(amount) FROM payments WHERE payment_status = 'Pending' AND created_at BETWEEN ? AND ?", $summaryParams) ?: 0,
    'completed_payments' => (int) $db->fetchColumn("SELECT COUNT(*) FROM payments WHERE payment_status = 'Completed' AND created_at BETWEEN ? AND ?", $summaryParams),
    'pending_payments' => (int) $db->fetchColumn("SELECT COUNT(*) FROM payments WHERE payment_status = 'Pending' AND created_at BETWEEN ? AND ?", $summaryParams),
    'failed_payments' => (int) $db->fetchColumn("SELECT COUNT(*) FROM payments WHERE payment_status = 'Failed' AND created_at BETWEEN ? AND ?", $summaryParams),
];

// Revenue by course
$revenueByCourse = $db->fetchAll("
    SELECT c.title, SUM(p.amount) as revenue, COUNT(*) as payment_count
    FROM payments p
    JOIN courses c ON p.course_id = c.id
    WHERE p.payment_status = 'Completed' AND p.created_at BETWEEN ? AND ?
    GROUP BY p.course_id
    ORDER BY revenue DESC
    LIMIT 10
", $summaryParams);

// Get courses for filter
$courses = $db->fetchAll("SELECT id, title FROM courses WHERE status = 'published' ORDER BY title");

$page_title = 'Revenue Report';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container-fluid px-4 py-6">

    <!-- Back Button -->
    <div class="mb-6">
        <a href="<?= url('admin/reports/index.php') ?>" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Reports
        </a>
    </div>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Revenue Report</h1>
        <p class="text-gray-600 mt-1">Financial performance and payment analytics</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <p class="text-green-100 text-sm">Total Revenue</p>
            <p class="text-3xl font-bold mt-2">ZMW <?= number_format($summary['total_revenue'], 2) ?></p>
            <p class="text-green-100 text-xs mt-2">Completed payments</p>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
            <p class="text-yellow-100 text-sm">Pending Revenue</p>
            <p class="text-3xl font-bold mt-2">ZMW <?= number_format($summary['pending_revenue'], 2) ?></p>
            <p class="text-yellow-100 text-xs mt-2">Awaiting verification</p>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <p class="text-blue-100 text-sm">Successful Payments</p>
            <p class="text-3xl font-bold mt-2"><?= number_format($summary['completed_payments']) ?></p>
            <p class="text-blue-100 text-xs mt-2">Completed transactions</p>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <p class="text-orange-100 text-sm">Pending Payments</p>
            <p class="text-3xl font-bold mt-2"><?= number_format($summary['pending_payments']) ?></p>
            <p class="text-orange-100 text-xs mt-2">Need attention</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>"
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>"
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                    <select name="course_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>" <?= $courseId == $course['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                    <select name="payment_status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="completed" <?= $paymentStatus == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="pending" <?= $paymentStatus == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="failed" <?= $paymentStatus == 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search mr-2"></i>Apply Filters
                    </button>
                    <a href="<?= url('admin/reports/revenue.php') ?>" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                    <button type="button" onclick="exportToCSV()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Revenue by Course -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Revenue by Course</h2>
            </div>
            <div class="p-6">
                <?php if (empty($revenueByCourse)): ?>
                    <p class="text-center text-gray-500 py-8">No revenue data available</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($revenueByCourse as $item): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['title']) ?></p>
                                <p class="text-xs text-gray-500"><?= $item['payment_count'] ?> payments</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600">ZMW <?= number_format($item['revenue'], 2) ?></p>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <?php 
                            $maxRevenue = $revenueByCourse[0]['revenue'];
                            $percentage = ($item['revenue'] / $maxRevenue) * 100;
                            ?>
                            <div class="bg-green-600 h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Quick Stats</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="border-l-4 border-green-500 pl-4">
                    <p class="text-sm text-gray-600">Average Transaction</p>
                    <p class="text-2xl font-bold text-gray-900">
                        ZMW <?= $summary['completed_payments'] > 0 ? number_format($summary['total_revenue'] / $summary['completed_payments'], 2) : '0.00' ?>
                    </p>
                </div>

                <div class="border-l-4 border-blue-500 pl-4">
                    <p class="text-sm text-gray-600">Success Rate</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php 
                        $totalTransactions = $summary['completed_payments'] + $summary['pending_payments'] + $summary['failed_payments'];
                        $successRate = $totalTransactions > 0 ? ($summary['completed_payments'] / $totalTransactions) * 100 : 0;
                        echo number_format($successRate, 1);
                        ?>%
                    </p>
                </div>

                <div class="border-l-4 border-yellow-500 pl-4">
                    <p class="text-sm text-gray-600">Failed Payments</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($summary['failed_payments']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Payment Transactions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="paymentsTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            No payments found for the selected criteria
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-mono text-gray-900"><?= htmlspecialchars($payment['transaction_reference']) ?></div>
                                <div class="text-xs text-gray-500">ID: <?= $payment['id'] ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?>
                                </div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($payment['email']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($payment['course_title']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">ZMW <?= number_format($payment['amount'], 2) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($payment['payment_status'] == 'Completed'): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                <?php elseif ($payment['payment_status'] == 'Pending'): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M d, Y', strtotime($payment['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($payment['payment_status'] == 'Pending'): ?>
                                <a href="<?= url('admin/payments/verify.php?id=' . $payment['id']) ?>" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i> Verify
                                </a>
                                <?php else: ?>
                                <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function exportToCSV() {
    const table = document.getElementById('paymentsTable');
    let csv = [];
    
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
    csv.push(headers.join(','));
    
    table.querySelectorAll('tbody tr').forEach(row => {
        if (row.cells.length > 1) {
            const rowData = Array.from(row.cells).map(cell => {
                let text = cell.textContent.trim().replace(/\n/g, ' ').replace(/,/g, ';');
                return `"${text}"`;
            });
            csv.push(rowData.join(','));
        }
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'revenue_report_<?= date('Y-m-d') ?>.csv';
    a.click();
}
</script>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
