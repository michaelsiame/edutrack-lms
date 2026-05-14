<?php
/**
 * Notifications / System Alerts Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 20;
$offset = ($page_num - 1) * $per_page;

$typeFilter = $_GET['type'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($typeFilter) {
    $where .= " AND notification_type = ?";
    $params[] = $typeFilter;
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM notifications $where", $params);
$totalPages = ceil($total / $per_page);

$notifications = $db->fetchAll("SELECT n.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
    FROM notifications n
    LEFT JOIN users u ON n.user_id = u.id
    $where
    ORDER BY n.created_at DESC LIMIT $per_page OFFSET $offset", $params);
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-gray-500 mt-1">System notifications and alerts</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="notifications">
            <select name="type" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Types</option>
                <option value="Info" <?= $typeFilter === 'Info' ? 'selected' : '' ?>>Info</option>
                <option value="Success" <?= $typeFilter === 'Success' ? 'selected' : '' ?>>Success</option>
                <option value="Warning" <?= $typeFilter === 'Warning' ? 'selected' : '' ?>>Warning</option>
                <option value="Error" <?= $typeFilter === 'Error' ? 'selected' : '' ?>>Error</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Read</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($notifications as $n): ?>
                <tr class="hover:bg-gray-50 <?= empty($n['is_read']) ? 'bg-blue-50/30' : '' ?>">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($n['title'] ?? '-') ?></p>
                        <p class="text-xs text-gray-500 max-w-md truncate"><?= htmlspecialchars($n['message']) ?></p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($n['user_name'] ?? 'All Users') ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= 
                            $n['notification_type'] === 'Error' ? 'bg-red-100 text-red-800' : 
                            ($n['notification_type'] === 'Success' ? 'bg-green-100 text-green-800' : 
                            ($n['notification_type'] === 'Warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) ?>">
                            <?= $n['notification_type'] ?? 'Info' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= !empty($n['is_read']) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= !empty($n['is_read']) ? 'Read' : 'Unread' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= date('M j, Y H:i', strtotime($n['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($notifications)): ?>
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-bell text-4xl mb-3 text-gray-300"></i><p>No notifications</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=notifications&p=<?= $i ?><?= $typeFilter ? '&type=' . urlencode($typeFilter) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
