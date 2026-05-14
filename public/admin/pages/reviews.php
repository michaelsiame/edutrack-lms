<?php
/**
 * Course Reviews Moderation Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$search = $_GET['search'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($search) {
    $where .= " AND (r.review LIKE ? OR c.title LIKE ?)";
    $params = ["%$search%", "%$search%"];
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM course_reviews r JOIN courses c ON r.course_id = c.id $where", $params);
$totalPages = ceil($total / $per_page);

$reviews = $db->fetchAll("SELECT r.*, c.title as course_title, CONCAT(u.first_name, ' ', u.last_name) as reviewer_name
    FROM course_reviews r
    JOIN courses c ON r.course_id = c.id
    JOIN users u ON r.user_id = u.id
    $where
    ORDER BY r.created_at DESC LIMIT $per_page OFFSET $offset", $params);

$avgRating = $db->fetchColumn("SELECT COALESCE(AVG(rating), 0) FROM course_reviews");
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Course Reviews</h1>
            <p class="text-gray-500 mt-1">Moderate student reviews and ratings</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Reviews</p>
            <p class="text-2xl font-bold text-gray-900"><?= $total ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Average Rating</p>
            <p class="text-2xl font-bold text-yellow-600"><?= number_format($avgRating, 1) ?> <i class="fas fa-star text-sm"></i></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Courses Reviewed</p>
            <p class="text-2xl font-bold text-gray-900"><?= $db->fetchColumn("SELECT COUNT(DISTINCT course_id) FROM course_reviews") ?></p>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="reviews">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search reviews..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Search</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Review</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reviewer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($reviews as $rev): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 line-clamp-2"><?= htmlspecialchars($rev['review'] ?? '-') ?></p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($rev['course_title']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($rev['reviewer_name']) ?></td>
                    <td class="px-6 py-4">
                        <div class="flex text-yellow-400 text-sm">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?= $i > ($rev['rating'] ?? 0) ? '-o' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= date('M j, Y', strtotime($rev['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($reviews)): ?>
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-star text-4xl mb-3 text-gray-300"></i><p>No reviews yet</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=reviews&p=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
