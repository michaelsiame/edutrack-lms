<?php
/**
 * Badges & Achievements Management Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$total = $db->fetchColumn("SELECT COUNT(*) FROM badges");
$totalPages = ceil($total / $per_page);

$badges = $db->fetchAll("SELECT * FROM badges ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
$achievementCount = $db->fetchColumn("SELECT COUNT(*) FROM student_achievements");
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Badges & Achievements</h1>
            <p class="text-gray-500 mt-1"><?= $achievementCount ?> achievements earned across <?= $total ?> badges</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Badges</p>
            <p class="text-2xl font-bold text-gray-900"><?= $total ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Achievements Earned</p>
            <p class="text-2xl font-bold text-gray-900"><?= $achievementCount ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Active Badges</p>
            <p class="text-2xl font-bold text-gray-900"><?= $db->fetchColumn("SELECT COUNT(*) FROM badges WHERE is_active = 1") ?></p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Badge</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Criteria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($badges as $badge): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <?php if (!empty($badge['badge_icon_url'])): ?>
                            <img src="<?= htmlspecialchars($badge['badge_icon_url']) ?>" alt="" class="w-10 h-10 rounded-full object-cover">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                                <i class="fas fa-award"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($badge['badge_name']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($badge['badge_type'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate"><?= htmlspecialchars($badge['criteria'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $badge['points'] ?? 0 ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= ($badge['is_active'] ?? 1) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= ($badge['is_active'] ?? 1) ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($badges)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-award text-4xl mb-3 text-gray-300"></i><p>No badges configured</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=badges&p=<?= $i ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
