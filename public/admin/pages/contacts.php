<?php
/**
 * Contact Form Submissions Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$readFilter = $_GET['read'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($readFilter !== '') {
    $where .= " AND is_read = ?";
    $params[] = (int)$readFilter;
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM contacts $where", $params);
$totalPages = ceil($total / $per_page);

$contacts = $db->fetchAll("SELECT * FROM contacts $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset", $params);
$unreadCount = $db->fetchColumn("SELECT COUNT(*) FROM contacts WHERE is_read = 0");
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Contact Submissions</h1>
            <p class="text-gray-500 mt-1"><?= $unreadCount ?> unread messages</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="contacts">
            <select name="read" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All</option>
                <option value="0" <?= $readFilter === '0' ? 'selected' : '' ?>>Unread</option>
                <option value="1" <?= $readFilter === '1' ? 'selected' : '' ?>>Read</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($contacts as $c): ?>
                <tr class="hover:bg-gray-50 <?= empty($c['is_read']) ? 'bg-blue-50/30' : '' ?>">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($c['name']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($c['email']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($c['subject'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= empty($c['is_read']) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= empty($c['is_read']) ? 'Unread' : 'Read' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= date('M j, Y H:i', strtotime($c['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($contacts)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-envelope text-4xl mb-3 text-gray-300"></i><p>No contact submissions</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=contacts&p=<?= $i ?><?= $readFilter !== '' ? '&read=' . urlencode($readFilter) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
