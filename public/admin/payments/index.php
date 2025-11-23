<?php
/**
 * Admin Payments Management
 */

require_once '../../../src/includes/admin-debug.php';
require_once '../../../src/middleware/admin-only.php';

// Filters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$perPage = 20;

// Build query - payments table uses student_id and payment_id
$baseQuery = "FROM payments p
    LEFT JOIN students s ON p.student_id = s.id
    LEFT JOIN users u ON s.user_id = u.id
    LEFT JOIN courses c ON p.course_id = c.id";

$where = [];
$params = [];

if ($status) {
    $where[] = "p.payment_status = ?";
    $params[] = ucfirst($status); // Schema uses capitalized enum values
}
if ($search) {
    $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR p.transaction_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$totalPayments = $db->fetchColumn("SELECT COUNT(*) $baseQuery $whereClause", $params);
$totalPages = ceil($totalPayments / $perPage);
$offset = ($page - 1) * $perPage;

// Get payments
$sql = "SELECT p.payment_id as id, p.student_id, p.course_id, p.amount, p.currency,
        p.payment_status, p.transaction_id, p.payment_date, p.created_at,
        u.first_name, u.last_name, u.email,
        c.title as course_title
        $baseQuery $whereClause
        ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$payments = $db->fetchAll($sql, $params);

// Get stats - use capitalized enum values as in schema
$stats = [
    'total' => $db->fetchColumn("SELECT COUNT(*) FROM payments"),
    'completed' => $db->fetchColumn("SELECT COUNT(*) FROM payments WHERE payment_status = 'Completed'"),
    'pending' => $db->fetchColumn("SELECT COUNT(*) FROM payments WHERE payment_status = 'Pending'"),
    'total_revenue' => $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'Completed'"),
];

$page_title = 'Payments Management';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-credit-card text-primary-600 mr-2"></i>Payments
            </h1>
            <p class="text-gray-600 mt-1">Total: <?= number_format($totalPayments) ?> payments</p>
        </div>
        <a href="<?= url('admin/payments/reports.php') ?>" class="btn btn-primary">
            <i class="fas fa-chart-bar mr-2"></i>View Reports
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Total Payments</p>
            <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Completed</p>
            <p class="text-2xl font-bold text-green-600"><?= number_format($stats['completed']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Pending</p>
            <p class="text-2xl font-bold text-yellow-600"><?= number_format($stats['pending']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Total Revenue</p>
            <p class="text-2xl font-bold text-primary-600">ZMW <?= number_format($stats['total_revenue'], 2) ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Search by name, email, transaction ID..."
                   class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">

            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Status</option>
                <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="failed" <?= $status == 'failed' ? 'selected' : '' ?>>Failed</option>
                <option value="refunded" <?= $status == 'refunded' ? 'selected' : '' ?>>Refunded</option>
            </select>

            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="<?= url('admin/payments/index.php') ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4 block"></i>
                            No payments found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $payment): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= sanitize($payment['transaction_id'] ?? 'N/A') ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= sanitize($payment['payment_method'] ?? 'Unknown') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= sanitize($payment['first_name'] . ' ' . $payment['last_name']) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= sanitize($payment['email']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <?= sanitize($payment['course_title'] ?? 'N/A') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900">
                                    ZMW <?= number_format($payment['amount'], 2) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $statusColors = [
                                    'Completed' => 'bg-green-100 text-green-800',
                                    'Pending' => 'bg-yellow-100 text-yellow-800',
                                    'Failed' => 'bg-red-100 text-red-800',
                                    'Refunded' => 'bg-gray-100 text-gray-800',
                                    'Cancelled' => 'bg-red-100 text-red-800',
                                ];
                                $paymentStatus = $payment['payment_status'] ?? 'Pending';
                                $statusClass = $statusColors[$paymentStatus] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $paymentStatus ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= date('M d, Y H:i', strtotime($payment['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4">
                                <a href="<?= url('admin/payments/verify.php?id=' . $payment['id']) ?>"
                                   class="text-blue-600 hover:text-blue-800" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Showing <?= number_format($offset + 1) ?> to <?= number_format(min($offset + $perPage, $totalPayments)) ?> of <?= number_format($totalPayments) ?> payments
                </p>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"
                           class="px-3 py-2 border rounded hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"
                           class="px-3 py-2 border rounded hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
