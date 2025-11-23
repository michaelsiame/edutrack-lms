<?php
/**
 * Instructor - Quiz Management & Results
 */

// Debug initialization
$DEBUG_MODE = defined('DEBUG_MODE') ? $DEBUG_MODE : ($_ENV['DEBUG_MODE'] ?? false);
$page_start_time = microtime(true);
$page_start_memory = memory_get_usage();
$debug_data = [
    'page' => 'instructor/quizzes.php',
    'timestamp' => date('Y-m-d H:i:s'),
    'queries' => [],
    'errors' => []
];

// Error handler for debugging
if ($DEBUG_MODE) {
    set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$debug_data) {
        $debug_data['errors'][] = [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ];
        return false;
    });
}

require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Quiz.php';

// Debug: Log user info
if ($DEBUG_MODE) {
    $debug_data['user'] = [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role' => $_SESSION['user_role'] ?? null
    ];
}

$db = Database::getInstance();
$userId = currentUserId();

// Get instructor ID from instructors table (instructor_id in courses references instructors.id, not users.id)
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : $userId;

// Debug: Log instructor ID
if ($DEBUG_MODE) {
    $debug_data['user_id'] = $userId;
    $debug_data['instructor_id'] = $instructorId;
}

// Get filter parameters
$courseFilter = $_GET['course'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Get instructor's courses
$courses = $db->fetchAll("
    SELECT id, title FROM courses
    WHERE instructor_id = ?
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
           c.title as course_title,
           c.id as course_id,
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
", $params);

// Get recent quiz attempts for instructor's courses
$recentAttempts = $db->fetchAll("
    SELECT qa.*, q.title as quiz_title, c.title as course_title,
           u.first_name, u.last_name, u.email
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN lessons l ON q.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    JOIN students st ON qa.student_id = st.id
    JOIN users u ON st.user_id = u.id
    WHERE c.instructor_id = ?
    ORDER BY qa.started_at DESC
    LIMIT 20
", [$instructorId]);

// Statistics
$stats = [
    'total_quizzes' => count($quizzes),
    'total_attempts' => array_sum(array_column($quizzes, 'total_attempts')),
    'avg_pass_rate' => 0
];

if ($stats['total_attempts'] > 0) {
    $totalPassed = array_sum(array_column($quizzes, 'passed_count'));
    $totalCompleted = array_sum(array_column($quizzes, 'completed_attempts'));
    $stats['avg_pass_rate'] = $totalCompleted > 0 ? round(($totalPassed / $totalCompleted) * 100, 1) : 0;
}

// Debug: Log data
if ($DEBUG_MODE) {
    $debug_data['data'] = [
        'quizzes_count' => count($quizzes),
        'courses_count' => count($courses),
        'recent_attempts_count' => count($recentAttempts),
        'stats' => $stats
    ];
    $debug_data['filters'] = [
        'course' => $courseFilter,
        'status' => $statusFilter
    ];
}

$page_title = 'Quiz Management - Instructor';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-question-circle text-primary-600 mr-3"></i>
                Quiz Management
            </h1>
            <p class="text-gray-600 mt-2">View and manage quizzes across your courses</p>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Quizzes</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?= $stats['total_quizzes'] ?></p>
                    </div>
                    <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-question-circle text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Attempts</p>
                        <p class="text-3xl font-bold text-purple-600 mt-1"><?= $stats['total_attempts'] ?></p>
                    </div>
                    <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Average Pass Rate</p>
                        <p class="text-3xl font-bold text-green-600 mt-1"><?= $stats['avg_pass_rate'] ?>%</p>
                    </div>
                    <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select name="course" onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= $courseFilter == $course['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($course['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($courseFilter): ?>
                <div class="flex items-end">
                    <a href="quizzes.php" class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-center">
                        <i class="fas fa-times mr-2"></i>Clear Filters
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Quizzes List -->
            <div class="lg:col-span-2">
                <?php if (empty($quizzes)): ?>
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <i class="fas fa-question-circle text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Quizzes Found</h3>
                    <p class="text-gray-600 mb-6">Create quizzes within your course lessons to assess student learning.</p>
                    <a href="<?= url('instructor/courses.php') ?>" class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
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
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?= htmlspecialchars($quiz['title']) ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="fas fa-book mr-1"></i><?= htmlspecialchars($quiz['course_title']) ?>
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-bookmark mr-1"></i><?= htmlspecialchars($quiz['lesson_title']) ?>
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="<?= url('instructor/courses/modules.php?id=' . $quiz['course_id']) ?>"
                                       class="px-3 py-1 text-sm bg-blue-50 text-blue-600 rounded hover:bg-blue-100">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </a>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div class="bg-gray-50 p-3 rounded">
                                    <p class="text-gray-500">Questions</p>
                                    <p class="text-lg font-bold text-gray-900"><?= $quiz['question_count'] ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded">
                                    <p class="text-gray-500">Attempts</p>
                                    <p class="text-lg font-bold text-purple-600"><?= $quiz['total_attempts'] ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded">
                                    <p class="text-gray-500">Avg Score</p>
                                    <p class="text-lg font-bold text-blue-600">
                                        <?= $quiz['avg_score'] ? round($quiz['avg_score'], 1) . '%' : 'N/A' ?>
                                    </p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded">
                                    <p class="text-gray-500">Pass Rate</p>
                                    <p class="text-lg font-bold <?= $passRate >= 70 ? 'text-green-600' : 'text-orange-600' ?>">
                                        <?= $passRate ?>%
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center space-x-4">
                                    <?php if ($quiz['time_limit']): ?>
                                    <span><i class="fas fa-clock mr-1"></i><?= $quiz['time_limit'] ?> min</span>
                                    <?php endif; ?>
                                    <span><i class="fas fa-check-circle mr-1"></i>Pass: <?= $quiz['passing_score'] ?>%</span>
                                    <?php if ($quiz['max_attempts']): ?>
                                    <span><i class="fas fa-redo mr-1"></i>Max <?= $quiz['max_attempts'] ?> attempts</span>
                                    <?php endif; ?>
                                </div>
                                <span>Created <?= date('M j, Y', strtotime($quiz['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Attempts Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Recent Attempts</h3>
                    </div>

                    <?php if (empty($recentAttempts)): ?>
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-clipboard-list text-3xl mb-2"></i>
                        <p class="text-sm">No quiz attempts yet</p>
                    </div>
                    <?php else: ?>
                    <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                        <?php foreach ($recentAttempts as $attempt): ?>
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        <?= htmlspecialchars($attempt['first_name'] . ' ' . $attempt['last_name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">
                                        <?= htmlspecialchars($attempt['quiz_title']) ?>
                                    </p>
                                </div>
                                <div class="ml-4 text-right">
                                    <?php if ($attempt['status'] == 'completed'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $attempt['passed'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= round($attempt['score'], 1) ?>%
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        In Progress
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                <i class="fas fa-clock mr-1"></i>
                                <?= timeAgo($attempt['started_at']) ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Tips -->
                <div class="bg-gradient-to-br from-primary-500 to-purple-600 rounded-lg shadow-md p-6 text-white mt-6">
                    <h3 class="text-lg font-bold mb-4">
                        <i class="fas fa-lightbulb mr-2"></i>Quiz Tips
                    </h3>
                    <ul class="space-y-2 text-sm opacity-90">
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-2"></i>
                            <span>Set appropriate time limits based on question count</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-2"></i>
                            <span>Use multiple question types to assess different skills</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-2"></i>
                            <span>Review low pass rates to improve quiz questions</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-1 mr-2"></i>
                            <span>Enable question randomization to reduce cheating</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
// Debug panel output
if ($DEBUG_MODE) {
    $debug_data['performance'] = [
        'execution_time' => round((microtime(true) - $page_start_time) * 1000, 2) . 'ms',
        'memory_used' => round((memory_get_usage() - $page_start_memory) / 1024, 2) . 'KB',
        'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB'
    ];
?>
<!-- Debug Panel -->
<div id="debug-panel" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white text-xs z-50 max-h-96 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-between p-2 bg-gray-800 sticky top-0">
        <span class="font-bold"><i class="fas fa-bug mr-2"></i>Debug Panel - <?= $debug_data['page'] ?></span>
        <div class="flex items-center space-x-4">
            <span class="text-green-400">Time: <?= $debug_data['performance']['execution_time'] ?></span>
            <span class="text-blue-400">Memory: <?= $debug_data['performance']['memory_used'] ?></span>
            <button onclick="document.getElementById('debug-panel').style.display='none'" class="text-red-400 hover:text-red-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <h4 class="font-bold text-yellow-400 mb-2">User Info</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto"><?= json_encode($debug_data['user'] ?? [], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <div>
            <h4 class="font-bold text-yellow-400 mb-2">Data</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto"><?= json_encode($debug_data['data'] ?? [], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <div>
            <h4 class="font-bold text-yellow-400 mb-2">Filters</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto"><?= json_encode($debug_data['filters'] ?? [], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <?php if (!empty($debug_data['errors'])): ?>
        <div>
            <h4 class="font-bold text-red-400 mb-2">Errors (<?= count($debug_data['errors']) ?>)</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto text-red-300"><?= json_encode($debug_data['errors'], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <?php endif; ?>
    </div>
</div>
<button onclick="document.getElementById('debug-panel').style.display = document.getElementById('debug-panel').style.display === 'none' ? 'block' : 'none'"
        class="fixed bottom-4 right-4 bg-gray-900 text-white p-3 rounded-full shadow-lg hover:bg-gray-700 z-50" title="Toggle Debug Panel">
    <i class="fas fa-bug"></i>
</button>
<?php } ?>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
