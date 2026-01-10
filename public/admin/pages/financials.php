<?php
/**
 * Financials Management Page - Full CRUD
 * Features: Add Payment, Edit, Verify, Refund, Reports, Export
 * Note: AJAX and POST handlers are processed in index.php and handlers/financials_handler.php
 */

// Pagination
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

// Filters
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "
    SELECT
        p.payment_id,
        p.amount,
        p.currency,
        p.payment_status,
        p.payment_type,
        p.transaction_id as reference,
        p.notes,
        p.payment_date,
        p.created_at,
        CONCAT(u.first_name, ' ', u.last_name) as full_name,
        u.email,
        c.title as course_title,
        pm.method_name as payment_method
    FROM payments p
    LEFT JOIN users u ON p.student_id = u.id
    LEFT JOIN courses c ON p.course_id = c.id
    LEFT JOIN payment_methods pm ON p.payment_method_id = pm.payment_method_id
    WHERE 1=1
";
$countSql = "SELECT COUNT(*) FROM payments p LEFT JOIN users u ON p.student_id = u.id WHERE 1=1";
$params = [];

if ($statusFilter) {
    $sql .= " AND p.payment_status = ?";
    $countSql .= " AND p.payment_status = ?";
    $params[] = $statusFilter;
}

if ($typeFilter) {
    $sql .= " AND p.payment_type = ?";
    $countSql .= " AND p.payment_type = ?";
    $params[] = $typeFilter;
}

if ($dateFrom) {
    $sql .= " AND DATE(p.created_at) >= ?";
    $countSql .= " AND DATE(p.created_at) >= ?";
    $params[] = $dateFrom;
}

if ($dateTo) {
    $sql .= " AND DATE(p.created_at) <= ?";
    $countSql .= " AND DATE(p.created_at) <= ?";
    $params[] = $dateTo;
}

if ($search) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR p.transaction_id LIKE ?)";
    $countSql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR p.transaction_id LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}

$totalPayments = $db->fetchColumn($countSql, $params);
$totalPages = ceil($totalPayments / $per_page);

$sql .= " ORDER BY p.created_at DESC LIMIT $per_page OFFSET $offset";
$payments = $db->fetchAll($sql, $params);

// Statistics
$totalRevenue = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'Completed'");
$pendingPayments = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count FROM payments WHERE payment_status = 'Pending'");
$thisMonthRevenue = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'Completed' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
$lastMonthRevenue = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'Completed' AND MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))");
$refundedTotal = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'Refunded'");

// Chart data - last 6 months
$chartData = $db->fetchAll("
    SELECT
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COALESCE(SUM(CASE WHEN payment_status = 'Completed' THEN amount ELSE 0 END), 0) as revenue,
        COUNT(CASE WHEN payment_status = 'Completed' THEN 1 END) as count
    FROM payments
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");

// Get supporting data
$courses = $db->fetchAll("SELECT id, title FROM courses ORDER BY title");
$paymentMethods = $db->fetchAll("SELECT payment_method_id, method_name FROM payment_methods WHERE is_active = 1 ORDER BY method_name");

$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Financial Management</h2>
            <p class="text-gray-500 text-sm mt-1">Track payments, revenue, and financial reports</p>
        </div>
        <div class="flex gap-2">
            <a href="?page=financials&ajax=export" class="px-4 py-2 border rounded-lg hover:bg-gray-50 flex items-center gap-2">
                <i class="fas fa-download"></i> Export CSV
            </a>
            <button onclick="openPaymentModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 shadow-sm">
                <i class="fas fa-plus"></i> Record Payment
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($msg): ?>
        <div class="<?= $msg === 'rejected' ? 'bg-yellow-100 border-yellow-400 text-yellow-700' : 'bg-green-100 border-green-400 text-green-700' ?> border px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <?php
            echo match($msg) {
                'added' => 'Payment recorded successfully!',
                'updated' => 'Payment updated successfully!',
                'verified' => 'Payment verified successfully!',
                'rejected' => 'Payment rejected.',
                'refunded' => 'Payment refunded successfully!',
                'deleted' => 'Payment deleted successfully!',
                default => 'Action completed!'
            };
            ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-green-600"><?= $currency ?> <?= number_format($totalRevenue['total'] ?? 0, 2) ?></p>
                    <p class="text-xs text-gray-500">Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 text-yellow-600 rounded-lg">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-yellow-600"><?= $currency ?> <?= number_format($pendingPayments['total'] ?? 0, 2) ?></p>
                    <p class="text-xs text-gray-500"><?= $pendingPayments['count'] ?? 0 ?> Pending</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                    <i class="fas fa-calendar"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-blue-600"><?= $currency ?> <?= number_format($thisMonthRevenue['total'] ?? 0, 2) ?></p>
                    <p class="text-xs text-gray-500">This Month</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <?php
                    $growth = $lastMonthRevenue['total'] > 0 ? (($thisMonthRevenue['total'] - $lastMonthRevenue['total']) / $lastMonthRevenue['total']) * 100 : 0;
                    ?>
                    <p class="text-xl font-bold <?= $growth >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                        <?= $growth >= 0 ? '+' : '' ?><?= number_format($growth, 1) ?>%
                    </p>
                    <p class="text-xs text-gray-500">vs Last Month</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 text-red-600 rounded-lg">
                    <i class="fas fa-undo"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-red-600"><?= $currency ?> <?= number_format($refundedTotal['total'] ?? 0, 2) ?></p>
                    <p class="text-xs text-gray-500">Refunded</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue Trend (Last 6 Months)</h3>
        <div class="h-48 flex items-end justify-around gap-2">
            <?php
            $maxRevenue = max(array_column($chartData, 'revenue')) ?: 1;
            foreach ($chartData as $data):
                $height = ($data['revenue'] / $maxRevenue) * 100;
            ?>
                <div class="flex flex-col items-center flex-1">
                    <div class="w-full max-w-[40px] bg-blue-500 rounded-t" style="height: <?= max(5, $height) ?>%"></div>
                    <p class="text-xs text-gray-500 mt-2"><?= date('M', strtotime($data['month'] . '-01')) ?></p>
                    <p class="text-xs font-medium text-gray-700"><?= number_format($data['revenue'] / 1000, 1) ?>k</p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($chartData)): ?>
                <p class="text-gray-400 text-center w-full">No data available</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <input type="hidden" name="page" value="financials">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search student or reference..." class="w-full pl-10 pr-4 py-2 border rounded-lg">
                </div>
            </div>
            <select name="status" class="px-4 py-2 border rounded-lg">
                <option value="">All Status</option>
                <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Completed" <?= $statusFilter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option value="Failed" <?= $statusFilter === 'Failed' ? 'selected' : '' ?>>Failed</option>
                <option value="Refunded" <?= $statusFilter === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
            </select>
            <select name="type" class="px-4 py-2 border rounded-lg">
                <option value="">All Types</option>
                <option value="tuition" <?= $typeFilter === 'tuition' ? 'selected' : '' ?>>Tuition</option>
                <option value="installment" <?= $typeFilter === 'installment' ? 'selected' : '' ?>>Installment</option>
                <option value="registration" <?= $typeFilter === 'registration' ? 'selected' : '' ?>>Registration</option>
                <option value="material" <?= $typeFilter === 'material' ? 'selected' : '' ?>>Material</option>
            </select>
            <input type="date" name="date_from" value="<?= $dateFrom ?>" class="px-4 py-2 border rounded-lg" placeholder="From">
            <input type="date" name="date_to" value="<?= $dateTo ?>" class="px-4 py-2 border rounded-lg" placeholder="To">
            <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-800">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <?php if ($search || $statusFilter || $typeFilter || $dateFrom || $dateTo): ?>
                <a href="?page=financials" class="text-gray-600 hover:text-gray-800 px-3 py-2">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($payments as $p): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm">#<?= $p['payment_id'] ?></span>
                                <?php if ($p['reference']): ?>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars(substr($p['reference'], 0, 15)) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($p['full_name'] ?? 'Unknown') ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($p['email'] ?? '') ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($p['course_title'] ?? 'N/A') ?></td>
                            <td class="px-6 py-4 font-semibold"><?= $p['currency'] ?? $currency ?> <?= number_format($p['amount'], 2) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700 capitalize">
                                    <?= str_replace('_', ' ', $p['payment_type'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $statusClass = match($p['payment_status']) {
                                    'Completed' => 'bg-green-100 text-green-700',
                                    'Pending' => 'bg-yellow-100 text-yellow-700',
                                    'Failed' => 'bg-red-100 text-red-700',
                                    'Refunded' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                                ?>
                                <span class="px-2 py-1 text-xs rounded-full <?= $statusClass ?>"><?= $p['payment_status'] ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= date('M j, Y H:i', strtotime($p['created_at'])) ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <?php if ($p['payment_status'] === 'Pending'): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="verify">
                                            <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                                            <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Verify">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($p['payment_status'] === 'Completed'): ?>
                                        <button onclick="openRefundModal(<?= $p['payment_id'] ?>, <?= $p['amount'] ?>)" class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg" title="Refund">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="openEditModal(<?= $p['payment_id'] ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this payment record?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500">No payments found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 border-t bg-gray-50 flex items-center justify-between">
                <p class="text-sm text-gray-600">Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $totalPayments) ?> of <?= $totalPayments ?> payments</p>
                <div class="flex gap-1">
                    <?php if ($page_num > 1): ?>
                        <a href="?page=financials&p=<?= $page_num - 1 ?>&status=<?= $statusFilter ?>&type=<?= $typeFilter ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-100">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page_num - 2); $i <= min($totalPages, $page_num + 2); $i++): ?>
                        <a href="?page=financials&p=<?= $i ?>&status=<?= $statusFilter ?>&type=<?= $typeFilter ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border rounded-lg <?= $i === $page_num ? 'bg-blue-600 text-white border-blue-600' : 'hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page_num < $totalPages): ?>
                        <a href="?page=financials&p=<?= $page_num + 1 ?>&status=<?= $statusFilter ?>&type=<?= $typeFilter ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-100">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-lg shadow-2xl">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <h3 id="paymentModalTitle" class="text-xl font-semibold text-gray-800">Record Payment</h3>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
        </div>
        <form id="paymentForm" method="POST" class="p-6">
            <input type="hidden" name="action" id="paymentFormAction" value="add">
            <input type="hidden" name="payment_id" id="paymentId" value="">

            <div class="space-y-4">
                <div id="studentSelectDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student <span class="text-red-500">*</span></label>
                    <select name="student_id" id="studentId" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select Student</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                    <select name="course_id" id="courseId" class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select Course (optional)</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (<?= $currency ?>) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type</label>
                        <select name="payment_type" id="paymentType" class="w-full px-3 py-2 border rounded-lg">
                            <option value="tuition">Tuition</option>
                            <option value="installment">Installment</option>
                            <option value="registration">Registration</option>
                            <option value="material">Material Fee</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select name="payment_method_id" id="paymentMethodId" class="w-full px-3 py-2 border rounded-lg">
                            <option value="">Select Method</option>
                            <?php foreach ($paymentMethods as $method): ?>
                                <option value="<?= $method['payment_method_id'] ?>"><?= htmlspecialchars($method['method_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="statusSelectDiv">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="payment_status" id="paymentStatus" class="w-full px-3 py-2 border rounded-lg">
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Transaction ID</label>
                    <input type="text" name="transaction_id" id="transactionId" class="w-full px-3 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" id="paymentSubmitBtn">Record Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- Refund Modal -->
<div id="refundModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-md shadow-2xl">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Process Refund</h3>
                <button onclick="closeRefundModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="action" value="refund">
            <input type="hidden" name="payment_id" id="refundPaymentId" value="">

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-yellow-800 text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    You are about to refund <strong id="refundAmount"></strong>. This action will update the enrollment balance.
                </p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Refund Reason <span class="text-red-500">*</span></label>
                <textarea name="refund_reason" required rows="3" class="w-full px-3 py-2 border rounded-lg" placeholder="Provide a reason for the refund..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeRefundModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Process Refund</button>
            </div>
        </form>
    </div>
</div>

<script>
let studentsLoaded = false;

function loadStudents() {
    if (studentsLoaded) return;
    fetch('?page=financials&ajax=get_students')
        .then(r => r.json())
        .then(students => {
            const select = document.getElementById('studentId');
            students.forEach(s => {
                const option = document.createElement('option');
                option.value = s.id;
                option.textContent = s.full_name + ' (' + s.email + ')';
                select.appendChild(option);
            });
            studentsLoaded = true;
        });
}

function openPaymentModal() {
    loadStudents();
    document.getElementById('paymentModalTitle').textContent = 'Record Payment';
    document.getElementById('paymentFormAction').value = 'add';
    document.getElementById('paymentId').value = '';
    document.getElementById('paymentSubmitBtn').textContent = 'Record Payment';
    document.getElementById('studentSelectDiv').style.display = 'block';
    document.getElementById('statusSelectDiv').style.display = 'block';
    document.getElementById('studentId').required = true;
    document.getElementById('paymentForm').reset();
    document.getElementById('paymentModal').classList.remove('hidden');
}

function openEditModal(paymentId) {
    document.getElementById('paymentModalTitle').textContent = 'Edit Payment';
    document.getElementById('paymentFormAction').value = 'edit';
    document.getElementById('paymentId').value = paymentId;
    document.getElementById('paymentSubmitBtn').textContent = 'Save Changes';
    document.getElementById('studentSelectDiv').style.display = 'none';
    document.getElementById('statusSelectDiv').style.display = 'none';
    document.getElementById('studentId').required = false;

    fetch('?page=financials&ajax=get_payment&id=' + paymentId)
        .then(r => r.json())
        .then(data => {
            document.getElementById('courseId').value = data.course_id || '';
            document.getElementById('amount').value = data.amount || '';
            document.getElementById('paymentType').value = data.payment_type || 'tuition';
            document.getElementById('paymentMethodId').value = data.payment_method_id || '';
            document.getElementById('transactionId').value = data.transaction_id || '';
            document.getElementById('notes').value = data.notes || '';
            document.getElementById('paymentModal').classList.remove('hidden');
        });
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

function openRefundModal(paymentId, amount) {
    document.getElementById('refundPaymentId').value = paymentId;
    document.getElementById('refundAmount').textContent = '<?= $currency ?> ' + parseFloat(amount).toFixed(2);
    document.getElementById('refundModal').classList.remove('hidden');
}

function closeRefundModal() {
    document.getElementById('refundModal').classList.add('hidden');
}

document.getElementById('paymentModal').addEventListener('click', e => { if (e.target === document.getElementById('paymentModal')) closePaymentModal(); });
document.getElementById('refundModal').addEventListener('click', e => { if (e.target === document.getElementById('refundModal')) closeRefundModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closePaymentModal(); closeRefundModal(); }});
</script>
