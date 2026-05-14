<?php
/**
 * Email Templates Management Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$typeFilter = $_GET['type'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($typeFilter) {
    $where .= " AND template_type = ?";
    $params[] = $typeFilter;
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM email_templates $where", $params);
$totalPages = ceil($total / $per_page);

$templates = $db->fetchAll("SELECT * FROM email_templates $where ORDER BY template_name ASC LIMIT $per_page OFFSET $offset", $params);
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Email Templates</h1>
            <p class="text-gray-500 mt-1">Manage system email templates</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="email-templates">
            <select name="type" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Types</option>
                <option value="welcome" <?= $typeFilter === 'welcome' ? 'selected' : '' ?>>Welcome</option>
                <option value="enrollment" <?= $typeFilter === 'enrollment' ? 'selected' : '' ?>>Enrollment</option>
                <option value="payment" <?= $typeFilter === 'payment' ? 'selected' : '' ?>>Payment</option>
                <option value="certificate" <?= $typeFilter === 'certificate' ? 'selected' : '' ?>>Certificate</option>
                <option value="notification" <?= $typeFilter === 'notification' ? 'selected' : '' ?>>Notification</option>
                <option value="password_reset" <?= $typeFilter === 'password_reset' ? 'selected' : '' ?>>Password Reset</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Updated</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($templates as $t): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($t['template_name']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600 capitalize"><?= htmlspecialchars($t['template_type'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($t['subject'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= $t['updated_at'] ? date('M j, Y', strtotime($t['updated_at'])) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($templates)): ?>
                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-envelope-open text-4xl mb-3 text-gray-300"></i><p>No email templates found</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=email-templates&p=<?= $i ?><?= $typeFilter ? '&type=' . urlencode($typeFilter) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
