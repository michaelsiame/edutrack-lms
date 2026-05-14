<?php
/**
 * Institution Photos / Gallery Management Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$categoryFilter = $_GET['category'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($categoryFilter) {
    $where .= " AND category = ?";
    $params[] = $categoryFilter;
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM institution_photos $where", $params);
$totalPages = ceil($total / $per_page);

$photos = $db->fetchAll("SELECT ip.*, CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
    FROM institution_photos ip
    LEFT JOIN users u ON ip.uploaded_by = u.id
    $where
    ORDER BY ip.display_order ASC, ip.created_at DESC
    LIMIT $per_page OFFSET $offset", $params);
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Institution Photos</h1>
            <p class="text-gray-500 mt-1">Manage campus gallery images</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="institution-photos">
            <select name="category" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Categories</option>
                <option value="campus" <?= $categoryFilter === 'campus' ? 'selected' : '' ?>>Campus</option>
                <option value="classroom" <?= $categoryFilter === 'classroom' ? 'selected' : '' ?>>Classroom</option>
                <option value="lab" <?= $categoryFilter === 'lab' ? 'selected' : '' ?>>Lab</option>
                <option value="event" <?= $categoryFilter === 'event' ? 'selected' : '' ?>>Event</option>
                <option value="faculty" <?= $categoryFilter === 'faculty' ? 'selected' : '' ?>>Faculty</option>
                <option value="student_life" <?= $categoryFilter === 'student_life' ? 'selected' : '' ?>>Student Life</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preview</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Featured</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($photos as $photo): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <?php if (!empty($photo['image_path'])): ?>
                            <img src="<?= htmlspecialchars($photo['image_path']) ?>" alt="" class="w-20 h-12 object-cover rounded">
                        <?php else: ?>
                            <div class="w-20 h-12 bg-gray-200 rounded flex items-center justify-center text-gray-400"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($photo['title']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600 capitalize"><?= str_replace('_', ' ', htmlspecialchars($photo['category'] ?? '-')) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $photo['display_order'] ?? 0 ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= ($photo['is_featured'] ?? 0) ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= ($photo['is_featured'] ?? 0) ? 'Yes' : 'No' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($photos)): ?>
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-images text-4xl mb-3 text-gray-300"></i><p>No photos uploaded</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=institution-photos&p=<?= $i ?><?= $categoryFilter ? '&category=' . urlencode($categoryFilter) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
