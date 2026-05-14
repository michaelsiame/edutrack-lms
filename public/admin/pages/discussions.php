<?php
/**
 * Discussions / Forum Moderation Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$search = $_GET['search'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($search) {
    $where .= " AND (d.title LIKE ? OR d.content LIKE ?)";
    $params = ["%$search%", "%$search%"];
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM discussions d $where", $params);
$totalPages = ceil($total / $per_page);

$discussions = $db->fetchAll("SELECT d.*, c.title as course_title, CONCAT(u.first_name, ' ', u.last_name) as author_name,
    (SELECT COUNT(*) FROM discussion_replies WHERE discussion_id = d.discussion_id) as reply_count
    FROM discussions d
    JOIN courses c ON d.course_id = c.id
    JOIN users u ON d.created_by = u.id
    $where
    ORDER BY d.created_at DESC LIMIT $per_page OFFSET $offset", $params);
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Discussions</h1>
            <p class="text-gray-500 mt-1">Moderate forum discussions across courses</p>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="discussions">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search discussions..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Search</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Topic</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Replies</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posted</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($discussions as $d): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($d['title']) ?></p>
                        <p class="text-xs text-gray-500 line-clamp-1"><?= htmlspecialchars(substr($d['content'], 0, 80)) ?>...</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($d['course_title']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($d['author_name']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $d['reply_count'] ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= date('M j, Y', strtotime($d['created_at'])) ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= ($d['is_locked'] ?? 0) ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                            <?= ($d['is_locked'] ?? 0) ? 'Locked' : 'Open' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($discussions)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-comments text-4xl mb-3 text-gray-300"></i><p>No discussions found</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=discussions&p=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
