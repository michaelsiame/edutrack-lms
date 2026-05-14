<?php
/**
 * Newsletter Subscribers Page
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

$total = $db->fetchColumn("SELECT COUNT(*) FROM newsletter_subscribers $where", $params);
$totalPages = ceil($total / $per_page);

$subscribers = $db->fetchAll("SELECT * FROM newsletter_subscribers $where ORDER BY subscribed_at DESC LIMIT $per_page OFFSET $offset", $params);
$activeCount = $db->fetchColumn("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'active'");
$unsubscribedCount = $db->fetchColumn("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'unsubscribed'");
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Newsletter Subscribers</h1>
            <p class="text-gray-500 mt-1">Manage email subscribers and campaigns</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Subscribers</p>
            <p class="text-2xl font-bold text-gray-900"><?= $total ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Active</p>
            <p class="text-2xl font-bold text-green-600"><?= $activeCount ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Unsubscribed</p>
            <p class="text-2xl font-bold text-red-600"><?= $unsubscribedCount ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="newsletter-subscribers">
            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Status</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="unsubscribed" <?= $statusFilter === 'unsubscribed' ? 'selected' : '' ?>>Unsubscribed</option>
                <option value="bounced" <?= $statusFilter === 'bounced' ? 'selected' : '' ?>>Bounced</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">First Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subscribed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($subscribers as $sub): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($sub['email']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($sub['first_name'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $sub['status'] === 'active' ? 'bg-green-100 text-green-800' : ($sub['status'] === 'bounced' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') ?>">
                            <?= ucfirst($sub['status'] ?? 'Active') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= $sub['subscribed_at'] ? date('M j, Y', strtotime($sub['subscribed_at'])) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($subscribers)): ?>
                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-envelope text-4xl mb-3 text-gray-300"></i><p>No subscribers yet</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=newsletter-subscribers&p=<?= $i ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
