<?php
/**
 * Email Queue Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 20;
$offset = ($page_num - 1) * $per_page;

$statusFilter = $_GET['status'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($statusFilter) {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM email_queue $where", $params);
$totalPages = ceil($total / $per_page);

$emails = $db->fetchAll("SELECT * FROM email_queue $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset", $params);
$pendingCount = $db->fetchColumn("SELECT COUNT(*) FROM email_queue WHERE status = 'pending'");
$sentCount = $db->fetchColumn("SELECT COUNT(*) FROM email_queue WHERE status = 'sent'");
$failedCount = $db->fetchColumn("SELECT COUNT(*) FROM email_queue WHERE status = 'failed'");
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Email Queue</h1>
            <p class="text-gray-500 mt-1">Monitor outgoing email status</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600"><?= $pendingCount ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Sent</p>
            <p class="text-2xl font-bold text-green-600"><?= $sentCount ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Failed</p>
            <p class="text-2xl font-bold text-red-600"><?= $failedCount ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="email-queue">
            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Status</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="sent" <?= $statusFilter === 'sent' ? 'selected' : '' ?>>Sent</option>
                <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>Failed</option>
                <option value="processing" <?= $statusFilter === 'processing' ? 'selected' : '' ?>>Processing</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attempts</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Queued</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($emails as $e): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($e['recipient']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate"><?= htmlspecialchars($e['subject'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= 
                            $e['status'] === 'sent' ? 'bg-green-100 text-green-800' : 
                            ($e['status'] === 'failed' ? 'bg-red-100 text-red-800' : 
                            ($e['status'] === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')) ?>">
                            <?= ucfirst($e['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $e['attempts'] ?? 0 ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $e['priority'] ?? 0 ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= date('M j, Y H:i', strtotime($e['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($emails)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-envelope text-4xl mb-3 text-gray-300"></i><p>No emails in queue</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=email-queue&p=<?= $i ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
