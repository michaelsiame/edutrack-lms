<?php
/**
 * Quizzes Management Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$search = $_GET['search'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($search) {
    $where .= " AND (q.title LIKE ? OR c.title LIKE ?)";
    $params = ["%$search%", "%$search%"];
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM quizzes q JOIN courses c ON q.course_id = c.id $where", $params);
$totalPages = ceil($total / $per_page);

$quizzes = $db->fetchAll("SELECT q.*, c.title as course_title, l.title as lesson_title,
    (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    LEFT JOIN lessons l ON q.lesson_id = l.id
    $where
    ORDER BY q.created_at DESC LIMIT $per_page OFFSET $offset", $params);
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Quizzes</h1>
            <p class="text-gray-500 mt-1">Manage course quizzes and assessments</p>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="quizzes">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search quizzes..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Search</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quiz</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lesson</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Questions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pass %</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($quizzes as $q): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($q['title']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($q['course_title']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($q['lesson_title'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $q['question_count'] ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $q['passing_score'] ?? 60 ?>%</td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $q['time_limit_minutes'] ?? '-' ?> min</td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($quizzes)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-question-circle text-4xl mb-3 text-gray-300"></i><p>No quizzes found</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=quizzes&p=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
