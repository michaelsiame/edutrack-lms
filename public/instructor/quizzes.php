<?php
/**
 * Instructor - Quiz Management & Results
 * Enhanced quiz analytics and management
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Quiz.php';

$db = Database::getInstance();
$userId = currentUserId();

// Get instructor ID
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : $userId;

// Get filter parameters
$courseFilter = $_GET['course'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

// Get instructor's courses for filter
$courses = $db->fetchAll("
    SELECT id, title FROM courses
    WHERE instructor_id = ? AND status != 'archived'
    ORDER BY title
", [$instructorId]);

// Build query for quizzes
$where = ["c.instructor_id = ?"];
$params = [$instructorId];

if ($courseFilter) {
    $where[] = "m.course_id = ?";
    $params[] = $courseFilter;
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Get quizzes with statistics
$quizzes = $db->fetchAll("
    SELECT q.*,
           l.title as lesson_title,
           m.title as module_title,
           c.title as course_title, c.id as course_id,
           (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id) as total_attempts,
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id AND status = 'completed') as completed_attempts,
           (SELECT AVG(score) FROM quiz_attempts WHERE quiz_id = q.id AND status = 'completed') as avg_score,
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id AND passed = 1) as passed_count
    FROM quizzes q
    JOIN lessons l ON q.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    $whereClause
    ORDER BY q.created_at DESC
    LIMIT ? OFFSET ?
", array_merge($params, [$perPage, ($page - 1) * $perPage]));

// Get total count
$totalQuizzes = (int) $db->fetchColumn("
    SELECT COUNT(*)
    FROM quizzes q
    JOIN lessons l ON q.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    $whereClause
", $params);

$totalPages = ceil($totalQuizzes / $perPage);

// Get recent quiz attempts
$recentAttempts = $db->fetchAll("
    SELECT qa.*, q.title as quiz_title, c.title as course_title, q.passing_score,
           u.first_name, u.last_name, u.email, u.avatar_url
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN lessons l ON q.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    JOIN students st ON qa.student_id = st.id
    JOIN users u ON st.user_id = u.id
    WHERE c.instructor_id = ?
    ORDER BY qa.started_at DESC
    LIMIT 10
", [$instructorId]);

// Statistics
$stats = $db->fetchOne("
    SELECT 
        COUNT(DISTINCT q.id) as total_quizzes,
        COUNT(DISTINCT qa.id) as total_attempts,
        AVG(CASE WHEN qa.status = 'completed' THEN qa.score END) as avg_score
    FROM quizzes q
    JOIN lessons l ON q.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id
    WHERE c.instructor_id = ?
", [$instructorId]);

$page_title = 'Quiz Management';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Quiz Management</h1>
                <p class="text-gray-500 mt-1">Monitor quiz performance and student results</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center gap-3">
                <a href="<?= url('instructor/courses.php') ?>" 
                   class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition">
                    <i class="fas fa-plus mr-2"></i>Create Quiz
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Quizzes</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total_quizzes'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-question-circle text-blue-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Attempts</p>
                        <p class="text-2xl font-bold text-purple-600"><?= $stats['total_attempts'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-purple-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Avg Score</p>
                        <p class="text-2xl font-bold text-green-600"><?= $stats['avg_score'] ? round($stats['avg_score'], 1) : '0' ?>%</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-500"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Main Content - Quizzes List -->
            <div class="xl:col-span-2">
                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-card border border-gray-100 p-5 mb-6">
                    <form method="GET" class="flex gap-4">
                        <div class="flex-1">
                            <select name="course" onchange="this.form.submit()"
                                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500">
                                <option value="">All Courses</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>" <?= $courseFilter == $course['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($course['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($courseFilter): ?>
                        <a href="quizzes.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                            <i class="fas fa-times mr-1"></i>Clear
                        </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Quizzes List -->
                <?php if (empty($quizzes)): ?>
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-question-circle text-gray-400 text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Quizzes Found</h3>
                    <p class="text-gray-500 mb-6">Create quizzes within your course lessons to assess student learning.</p>
                    <a href="<?= url('instructor/courses.php') ?>" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition">
                        <i class="fas fa-book mr-2"></i>Go to Courses
                    </a>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($quizzes as $quiz):
                        $passRate = $quiz['completed_attempts'] > 0
                            ? round(($quiz['passed_count'] / $quiz['completed_attempts']) * 100, 1)
                            : 0;
                    ?>
                    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 hover:shadow-card-hover transition">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-1"><?= htmlspecialchars($quiz['title']) ?></h3>
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-book text-primary-500 mr-1"></i><?= htmlspecialchars($quiz['course_title']) ?>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-bookmark text-secondary-500 mr-1"></i><?= htmlspecialchars($quiz['lesson_title']) ?>
                                </p>
                            </div>
                            <a href="<?= url('instructor/courses/modules.php?id=' . $quiz['course_id']) ?>"
                               class="px-4 py-2 bg-primary-50 text-primary-600 rounded-lg hover:bg-primary-100 transition text-sm font-medium">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <i class="fas fa-question-circle text-primary-500 mb-1"></i>
                                <p class="text-xs text-gray-500">Questions</p>
                                <p class="text-xl font-bold text-gray-900"><?= $quiz['question_count'] ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <i class="fas fa-users text-purple-500 mb-1"></i>
                                <p class="text-xs text-gray-500">Attempts</p>
                                <p class="text-xl font-bold text-purple-600"><?= $quiz['total_attempts'] ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <i class="fas fa-chart-line text-blue-500 mb-1"></i>
                                <p class="text-xs text-gray-500">Avg Score</p>
                                <p class="text-xl font-bold text-blue-600"><?= $quiz['avg_score'] ? round($quiz['avg_score'], 1) . '%' : 'N/A' ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <i class="fas fa-trophy text-green-500 mb-1"></i>
                                <p class="text-xs text-gray-500">Pass Rate</p>
                                <p class="text-xl font-bold <?= $passRate >= 70 ? 'text-green-600' : 'text-orange-600' ?>"><?= $passRate ?>%</p>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 pt-4 border-t border-gray-100">
                            <?php if ($quiz['time_limit']): ?>
                            <span><i class="fas fa-clock mr-1"></i><?= $quiz['time_limit'] ?> min</span>
                            <?php endif; ?>
                            <span><i class="fas fa-check-circle mr-1"></i>Pass: <?= $quiz['passing_score'] ?>%</span>
                            <?php if ($quiz['max_attempts']): ?>
                            <span><i class="fas fa-redo mr-1"></i>Max <?= $quiz['max_attempts'] ?> attempts</span>
                            <?php endif; ?>
                            <span class="ml-auto">Created <?= date('M j, Y', strtotime($quiz['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Page <?= $page ?> of <?= $totalPages ?>
                    </p>
                    <div class="flex items-center gap-2">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&course=<?= $courseFilter ?>" 
                           class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Previous</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&course=<?= $courseFilter ?>" 
                           class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Recent Attempts -->
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Recent Attempts</h3>
                    </div>

                    <?php if (empty($recentAttempts)): ?>
                    <div class="p-8 text-center">
                        <p class="text-gray-500 text-sm">No quiz attempts yet</p>
                    </div>
                    <?php else: ?>
                    <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        <?php foreach ($recentAttempts as $attempt): ?>
                        <div class="p-4 hover:bg-gray-50/50 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="<?= getGravatar($attempt['email']) ?>" class="w-8 h-8 rounded-full">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 text-sm truncate">
                                            <?= htmlspecialchars($attempt['first_name'] . ' ' . $attempt['last_name']) ?>
                                        </p>
                                        <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($attempt['quiz_title']) ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <?php if ($attempt['status'] == 'completed'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium <?= $attempt['passed'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                        <?= round($attempt['score'], 1) ?>%
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-yellow-100 text-yellow-700">
                                        In Progress
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">
                                <i class="fas fa-clock mr-1"></i><?= timeAgo($attempt['started_at']) ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tips Card -->
                <div class="bg-gradient-to-br from-primary-600 to-purple-600 rounded-2xl p-6 text-white">
                    <h3 class="text-lg font-bold mb-4">
                        <i class="fas fa-lightbulb text-yellow-300 mr-2"></i>Quiz Tips
                    </h3>
                    <ul class="space-y-3 text-sm opacity-90">
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-2 text-green-300"></i>
                            <span>Set appropriate time limits based on question count</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-2 text-green-300"></i>
                            <span>Use multiple question types to assess different skills</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-2 text-green-300"></i>
                            <span>Review low pass rates to improve quiz questions</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-2 text-green-300"></i>
                            <span>Enable question randomization to reduce cheating</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
