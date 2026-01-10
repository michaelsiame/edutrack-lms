<?php
/**
 * Financials Management Page
 * Uses the payments table for payment management
 */

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'verify' && isset($_POST['payment_id'])) {
        $paymentId = (int)$_POST['payment_id'];
        $db->update('payments', ['payment_status' => 'Completed', 'payment_date' => date('Y-m-d H:i:s')], 'payment_id = ?', [$paymentId]);

        // Also update enrollment payment_status if linked
        $payment = $db->fetchOne("SELECT enrollment_id FROM payments WHERE payment_id = ?", [$paymentId]);
        if ($payment && $payment['enrollment_id']) {
            $db->update('enrollments', ['payment_status' => 'completed'], 'id = ?', [$payment['enrollment_id']]);
        }

        header('Location: ?page=financials&msg=verified');
        exit;
    }

    if ($action === 'reject' && isset($_POST['payment_id'])) {
        $paymentId = (int)$_POST['payment_id'];
        $db->update('payments', ['payment_status' => 'Failed'], 'payment_id = ?', [$paymentId]);
        header('Location: ?page=financials&msg=rejected');
        exit;
    }
}

// Fetch statistics from payments table
$totalRevenue = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'Completed'");
$pendingPayments = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'Pending'");
$thisMonthRevenue = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'Completed' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");

// Fetch payments with user and course info
$statusFilter = $_GET['status'] ?? '';
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
    LEFT JOIN payment_methods pm ON p.payment_method_id = pm.id
";

if ($statusFilter) {
    $sql .= " WHERE p.payment_status = ?";
    $payments = $db->fetchAll($sql . " ORDER BY p.created_at DESC", [$statusFilter]);
} else {
    $payments = $db->fetchAll($sql . " ORDER BY p.created_at DESC");
}

$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Financials</h2>

    <?php if ($msg): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?= $msg === 'verified' ? 'Payment verified successfully!' : 'Payment rejected.' ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                    <h3 class="text-2xl font-bold text-green-600 mt-1"><?= $currency ?> <?= number_format($totalRevenue['total'] ?? 0, 2) ?></h3>
                </div>
                <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending Payments</p>
                    <h3 class="text-2xl font-bold text-yellow-600 mt-1"><?= $currency ?> <?= number_format($pendingPayments['total'] ?? 0, 2) ?></h3>
                </div>
                <div class="p-3 bg-yellow-100 text-yellow-600 rounded-lg">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">This Month</p>
                    <h3 class="text-2xl font-bold text-blue-600 mt-1"><?= $currency ?> <?= number_format($thisMonthRevenue['total'] ?? 0, 2) ?></h3>
                </div>
                <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                    <i class="fas fa-calendar"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" class="flex gap-4 items-center">
            <input type="hidden" name="page" value="financials">
            <select name="status" class="px-4 py-2 border rounded-lg">
                <option value="">All Payments</option>
                <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Completed" <?= $statusFilter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option value="Failed" <?= $statusFilter === 'Failed' ? 'selected' : '' ?>>Failed</option>
                <option value="Refunded" <?= $statusFilter === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
            </select>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">Filter</button>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($payments as $p): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono text-sm">#<?= $p['payment_id'] ?></td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($p['full_name'] ?? 'Unknown') ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($p['email'] ?? '') ?></p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($p['course_title'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 font-semibold"><?= $p['currency'] ?? $currency ?> <?= number_format($p['amount'], 2) ?></td>
                        <td class="px-6 py-4 text-gray-600 capitalize"><?= str_replace('_', ' ', $p['payment_type'] ?? 'N/A') ?></td>
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
                            <span class="px-2 py-1 text-xs rounded-full <?= $statusClass ?>">
                                <?= $p['payment_status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-sm"><?= date('M j, Y H:i', strtotime($p['created_at'])) ?></td>
                        <td class="px-6 py-4">
                            <?php if ($p['payment_status'] === 'Pending'): ?>
                                <div class="flex gap-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="verify">
                                        <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                                        <button type="submit" class="text-xs px-2 py-1 rounded bg-green-100 text-green-700 hover:bg-green-200">Verify</button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                                        <button type="submit" class="text-xs px-2 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200">Reject</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">No payments found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
