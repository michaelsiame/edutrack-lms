<?php
/**
 * Financials Management Page
 */

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'verify' && isset($_POST['transaction_id'])) {
        $transactionId = (int)$_POST['transaction_id'];
        $db->update('transactions', ['status' => 'Verified', 'verified_at' => date('Y-m-d H:i:s')], 'id = ?', [$transactionId]);
        header('Location: ?page=financials&msg=verified');
        exit;
    }

    if ($action === 'reject' && isset($_POST['transaction_id'])) {
        $transactionId = (int)$_POST['transaction_id'];
        $db->update('transactions', ['status' => 'Rejected'], 'id = ?', [$transactionId]);
        header('Location: ?page=financials&msg=rejected');
        exit;
    }
}

// Fetch statistics
$totalRevenue = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE status = 'Verified'");
$pendingPayments = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE status = 'Pending'");
$thisMonthRevenue = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE status = 'Verified' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");

// Fetch transactions
$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT t.*, u.full_name, u.email, c.title as course_title FROM transactions t LEFT JOIN users u ON t.user_id = u.id LEFT JOIN courses c ON t.course_id = c.id";
if ($statusFilter) {
    $sql .= " WHERE t.status = ?";
    $transactions = $db->fetchAll($sql . " ORDER BY t.created_at DESC", [$statusFilter]);
} else {
    $transactions = $db->fetchAll($sql . " ORDER BY t.created_at DESC");
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
                    <h3 class="text-2xl font-bold text-green-600 mt-1"><?= $currency ?> <?= number_format($totalRevenue['total'], 2) ?></h3>
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
                    <h3 class="text-2xl font-bold text-yellow-600 mt-1"><?= $currency ?> <?= number_format($pendingPayments['total'], 2) ?></h3>
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
                    <h3 class="text-2xl font-bold text-blue-600 mt-1"><?= $currency ?> <?= number_format($thisMonthRevenue['total'], 2) ?></h3>
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
                <option value="">All Transactions</option>
                <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Verified" <?= $statusFilter === 'Verified' ? 'selected' : '' ?>>Verified</option>
                <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">Filter</button>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($transactions as $tx): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono text-sm"><?= htmlspecialchars($tx['reference'] ?? $tx['id']) ?></td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($tx['full_name'] ?? 'Unknown') ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($tx['email'] ?? '') ?></p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($tx['course_title'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 font-semibold"><?= $currency ?> <?= number_format($tx['amount'], 2) ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($tx['payment_method'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full <?= $tx['status'] === 'Verified' ? 'bg-green-100 text-green-700' : ($tx['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
                                <?= $tx['status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-sm"><?= date('M j, Y H:i', strtotime($tx['created_at'])) ?></td>
                        <td class="px-6 py-4">
                            <?php if ($tx['status'] === 'Pending'): ?>
                                <div class="flex gap-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="verify">
                                        <input type="hidden" name="transaction_id" value="<?= $tx['id'] ?>">
                                        <button type="submit" class="text-xs px-2 py-1 rounded bg-green-100 text-green-700 hover:bg-green-200">Verify</button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="transaction_id" value="<?= $tx['id'] ?>">
                                        <button type="submit" class="text-xs px-2 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200">Reject</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">No transactions found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
