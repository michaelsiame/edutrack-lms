<?php
/**
 * Hero Slides / Homepage Carousel Management Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$total = $db->fetchColumn("SELECT COUNT(*) FROM hero_slides");
$totalPages = ceil($total / $per_page);

$slides = $db->fetchAll("SELECT * FROM hero_slides ORDER BY display_order ASC, created_at DESC LIMIT $per_page OFFSET $offset");
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Hero Slides</h1>
            <p class="text-gray-500 mt-1">Manage homepage carousel images</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preview</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($slides as $slide): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <?php if (!empty($slide['image_path'])): ?>
                            <img src="<?= htmlspecialchars($slide['image_path']) ?>" alt="" class="w-20 h-12 object-cover rounded">
                        <?php else: ?>
                            <div class="w-20 h-12 bg-gray-200 rounded flex items-center justify-center text-gray-400"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($slide['title'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $slide['display_order'] ?? 0 ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= ($slide['is_active'] ?? 1) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= ($slide['is_active'] ?? 1) ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($slides)): ?>
                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-images text-4xl mb-3 text-gray-300"></i><p>No hero slides configured</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=hero-slides&p=<?= $i ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
