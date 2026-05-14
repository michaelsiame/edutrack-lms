<?php
/**
 * Activity Logs / Audit Trail Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 20;
$offset = ($page_num - 1) * $per_page;

$actionFilter = $_GET['action'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($actionFilter) {
    $where .= " AND activity_type = ?";
    $params[] = $actionFilter;
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM activity_logs $where", $params);
$totalPages = ceil($total / $per_page);

$logs = $db->fetchAll("SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
    FROM activity_logs a
    LEFT JOIN users u ON a.user_id = u.id
    $where
    ORDER BY a.created_at DESC LIMIT $per_page OFFSET $offset", $params);
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Activity Logs</h1>
            <p class="text-gray-500 mt-1">Audit trail of system activities</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="activity-logs">
            <select name="action" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Activities</option>
                <option value="login" <?= $actionFilter === 'login' ? 'selected' : '' ?>>Login</option>
                <option value="logout" <?= $actionFilter === 'logout' ? 'selected' : '' ?>>Logout</option>
                <option value="create" <?= $actionFilter === 'create' ? 'selected' : '' ?>>Create</option>
                <option value="update" <?= $actionFilter === 'update' ? 'selected' : '' ?>>Update</option>
                <option value="delete" <?= $actionFilter === 'delete' ? 'selected' : '' ?>>Delete</option>
                <option value="payment" <?= $actionFilter === 'payment' ? 'selected' : '' ?>>Payment</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($logs as $log): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap"><?= date('M j, Y H:i', strtotime($log['created_at'])) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= 
                            $log['activity_type'] === 'delete' ? 'bg-red-100 text-red-800' : 
                            ($log['activity_type'] === 'create' ? 'bg-green-100 text-green-800' : 
                            ($log['activity_type'] === 'update' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) ?>">
                            <?= ucfirst($log['activity_type']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($log['entity_type'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500 font-mono"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate"><?= htmlspecialchars($log['description'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-history text-4xl mb-3 text-gray-300"></i><p>No activity logs</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=activity-logs&p=<?= $i ?><?= $actionFilter ? '&action=' . urlencode($actionFilter) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
