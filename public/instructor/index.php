<?php
/**
 * Instructor Dashboard
 * Main dashboard showing overview, stats, and recent activities
 */

// Enable full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug initialization
$DEBUG_MODE = true; // Force debug mode for troubleshooting
$page_start_time = microtime(true);
$page_start_memory = memory_get_usage();
$debug_data = [
    'page' => 'instructor/index.php',
    'timestamp' => date('Y-m-d H:i:s'),
    'queries' => [],
    'errors' => [],
    'debug_trace' => []
];

// Custom error handler that captures all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$debug_data) {
    $debug_data['errors'][] = [
        'type' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ];
    // Also log to error log
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return false; // Continue with normal error handling
});

// Set exception handler
set_exception_handler(function($e) use (&$debug_data) {
    $debug_data['errors'][] = [
        'type' => 'Exception',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo "<pre>Exception: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "File: " . htmlspecialchars($e->getFile()) . " Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
});

$debug_data['debug_trace'][] = 'Starting script';

try {
    $debug_data['debug_trace'][] = 'Loading middleware';
    require_once '../../src/middleware/instructor-only.php';
    $debug_data['debug_trace'][] = 'Middleware loaded successfully';

    $debug_data['debug_trace'][] = 'Loading Statistics class';
    require_once '../../src/classes/Statistics.php';
    $debug_data['debug_trace'][] = 'Statistics class loaded';

} catch (Exception $e) {
    die("<pre>Error loading dependencies: " . htmlspecialchars($e->getMessage()) . "\nTrace: " . htmlspecialchars($e->getTraceAsString()) . "</pre>");
}

$debug_data['debug_trace'][] = 'Initializing database';

// Initialize database connection
$db = Database::getInstance();

$debug_data['debug_trace'][] = 'Database initialized';

// Debug: Log user info
$debug_data['user'] = [
    'id' => $_SESSION['user_id'] ?? null,
    'email' => $_SESSION['user_email'] ?? null,
    'role' => $_SESSION['user_role'] ?? null
];

$debug_data['debug_trace'][] = 'Getting current user';

$user = User::current();
if (!$user) {
    redirect(url('login.php'));
    exit;
}

$debug_data['debug_trace'][] = 'User found, getting instructor ID';

$userId = $user->getId();
$debug_data['debug_trace'][] = 'User ID: ' . $userId;

// Get instructor ID from instructors table (instructor_id in courses references instructors.id, not users.id)
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : $userId;

$debug_data['debug_trace'][] = 'Instructor ID (from instructors table): ' . $instructorId;

// Debug: Log IDs
if ($DEBUG_MODE) {
    $debug_data['user_id'] = $userId;
    $debug_data['instructor_id'] = $instructorId;
}

// Get comprehensive instructor statistics
$stats = Statistics::getInstructorStats($instructorId);

// Debug: Log stats data
if ($DEBUG_MODE) {
    $debug_data['data']['stats'] = $stats;
}

// Get recent enrollments in instructor's courses
$recentEnrollments = $db->fetchAll("
    SELECT e.*, c.title as course_title, c.slug as course_slug,
           u.first_name, u.last_name, u.email
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON e.user_id = u.id
    WHERE c.instructor_id = ?
    ORDER BY e.enrolled_at DESC
    LIMIT 10
", [$instructorId]);

// Get pending assignment submissions
$pendingAssignments = $db->fetchAll("
    SELECT asub.*, a.title as assignment_title, a.max_points,
           c.title as course_title, c.slug as course_slug,
           u.first_name, u.last_name
    FROM assignment_submissions asub
    JOIN assignments a ON asub.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN students st ON asub.student_id = st.id
    JOIN users u ON st.user_id = u.id
    WHERE c.instructor_id = ? AND asub.status = 'Submitted'
    ORDER BY asub.submitted_at DESC
    LIMIT 10
", [$instructorId]);

// Get instructor's courses with enrollment count
$courses = $db->fetchAll("
    SELECT c.*, COUNT(DISTINCT e.id) as student_count,
           COUNT(DISTINCT m.id) as module_count,
           COUNT(DISTINCT l.id) as lesson_count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN modules m ON c.id = m.course_id
    LEFT JOIN lessons l ON m.id = l.module_id
    WHERE c.instructor_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 6
", [$instructorId]);

// Get recent reviews
$recentReviews = $db->fetchAll("
    SELECT cr.*, c.title as course_title, c.slug as course_slug,
           u.first_name, u.last_name
    FROM course_reviews cr
    JOIN courses c ON cr.course_id = c.id
    JOIN users u ON cr.user_id = u.id
    WHERE c.instructor_id = ?
    ORDER BY cr.created_at DESC
    LIMIT 5
", [$instructorId]);

// Calculate revenue (if applicable)
$revenue = $db->fetchOne("
    SELECT
        COALESCE(SUM(CASE WHEN e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN c.price ELSE 0 END), 0) as monthly_revenue,
        COALESCE(SUM(c.price), 0) as total_revenue
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ? AND e.payment_status = 'completed'
", [$instructorId]);

// Debug: Log all fetched data counts
if ($DEBUG_MODE) {
    $debug_data['data']['recent_enrollments_count'] = count($recentEnrollments);
    $debug_data['data']['pending_assignments_count'] = count($pendingAssignments);
    $debug_data['data']['courses_count'] = count($courses);
    $debug_data['data']['recent_reviews_count'] = count($recentReviews);
    $debug_data['data']['revenue'] = $revenue;
}

$page_title = 'Instructor Dashboard - Edutrack';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Welcome back, <?= htmlspecialchars($user->first_name) ?>!
            </h1>
            <p class="text-gray-600 mt-2">Here's what's happening with your courses today.</p>
        </div>

        <!-- Announcements -->
        <?php include '../../src/templates/announcements.php'; ?>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Courses -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Courses</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_courses'] ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-book text-blue-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-sm">
                    <span class="text-green-600 font-medium"><?= $stats['published_courses'] ?> Published</span>
                </div>
            </div>

            <!-- Total Students -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_students'] ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-users text-purple-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= url('instructor/students.php') ?>" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                        View all students →
                    </a>
                </div>
            </div>

            <!-- Pending Reviews -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending Grading</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= count($pendingAssignments) ?></p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-tasks text-orange-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= url('instructor/assignments.php') ?>" class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                        Review assignments →
                    </a>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Monthly Revenue</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            K<?= number_format($revenue['monthly_revenue'] ?? 0, 2) ?>
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    Total: K<?= number_format($revenue['total_revenue'] ?? 0, 2) ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">

                <!-- My Courses -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">My Courses</h2>
                        <a href="<?= url('instructor/courses.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">
                            View All
                        </a>
                    </div>

                    <?php if (empty($courses)): ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Courses Yet</h3>
                        <p class="text-gray-500 mb-6">Create your first course to start teaching</p>
                        <a href="<?= url('instructor/courses/create.php') ?>" class="btn-primary px-6 py-3 rounded-lg inline-block">
                            <i class="fas fa-plus mr-2"></i> Create Your First Course
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="p-6 space-y-4">
                        <?php foreach ($courses as $course): ?>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 border border-gray-200 rounded-lg hover:border-primary-300 transition">
                            <div class="w-full sm:w-20 h-32 sm:h-20 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center">
                                <?php if (!empty($course['thumbnail_url'])): ?>
                                <img src="<?= htmlspecialchars($course['thumbnail_url']) ?>"
                                     alt="<?= htmlspecialchars($course['title']) ?>"
                                     class="w-full h-full object-cover rounded-lg">
                                <?php else: ?>
                                <i class="fas fa-book text-white text-3xl"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">
                                    <?= htmlspecialchars($course['title']) ?>
                                </h3>
                                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                                    <span>
                                        <i class="fas fa-users text-purple-500 mr-1"></i>
                                        <?= $course['student_count'] ?> students
                                    </span>
                                    <span>
                                        <i class="fas fa-book-open text-blue-500 mr-1"></i>
                                        <?= $course['lesson_count'] ?> lessons
                                    </span>
                                    <span class="px-2 py-1 rounded-full text-xs <?= $course['status'] == 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= ucfirst($course['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2 w-full sm:w-auto">
                                <a href="<?= url('instructor/course-edit.php?id=' . $course['id']) ?>"
                                   class="flex-1 sm:flex-initial text-center px-4 py-2 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition text-sm font-medium">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <a href="<?= url('course.php?slug=' . $course['slug']) ?>"
                                   class="flex-1 sm:flex-initial text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Pending Assignment Submissions -->
                <?php if (!empty($pendingAssignments)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-clipboard-list text-orange-500 mr-2"></i>
                            Pending Reviews
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php foreach (array_slice($pendingAssignments, 0, 5) as $submission): ?>
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">
                                        <?= htmlspecialchars($submission['assignment_title']) ?>
                                    </h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <?= htmlspecialchars($submission['course_title']) ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="fas fa-user mr-1"></i>
                                        <?= htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <i class="fas fa-clock mr-1"></i>
                                        Submitted <?= timeAgo($submission['submitted_at']) ?>
                                    </p>
                                </div>
                                <a href="<?= url('instructor/assignments.php?submission=' . $submission['id']) ?>"
                                   class="px-4 py-2 bg-orange-50 text-orange-600 rounded-md hover:bg-orange-100 transition text-sm font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>Review
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Recent Enrollments -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Recent Enrollments</h3>
                    </div>
                    <?php if (empty($recentEnrollments)): ?>
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-user-plus text-3xl mb-2"></i>
                        <p class="text-sm">No enrollments yet</p>
                    </div>
                    <?php else: ?>
                    <div class="divide-y divide-gray-200">
                        <?php foreach (array_slice($recentEnrollments, 0, 5) as $enrollment): ?>
                        <div class="p-4">
                            <p class="font-medium text-gray-900 text-sm">
                                <?= htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?>
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                                <?= htmlspecialchars($enrollment['course_title']) ?>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                <i class="fas fa-clock mr-1"></i>
                                <?= timeAgo($enrollment['enrolled_at']) ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Reviews -->
                <?php if (!empty($recentReviews)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Recent Reviews</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($recentReviews as $review): ?>
                        <div class="p-4">
                            <div class="flex items-center mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' ?> text-sm"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-sm text-gray-700 mb-2 line-clamp-2">
                                "<?= htmlspecialchars($review['review_text']) ?>"
                            </p>
                            <p class="text-xs text-gray-500">
                                - <?= htmlspecialchars($review['first_name']) ?> on <?= htmlspecialchars($review['course_title']) ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="bg-gradient-to-br from-primary-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
                    <h3 class="text-lg font-bold mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="<?= url('instructor/courses/create.php') ?>"
                           class="block w-full px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg transition text-center backdrop-blur-sm">
                            <i class="fas fa-plus mr-2"></i>Create New Course
                        </a>
                        <a href="<?= url('instructor/assignments.php') ?>"
                           class="block w-full px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg transition text-center backdrop-blur-sm">
                            <i class="fas fa-tasks mr-2"></i>Review Assignments
                        </a>
                        <a href="<?= url('instructor/students.php') ?>"
                           class="block w-full px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg transition text-center backdrop-blur-sm">
                            <i class="fas fa-users mr-2"></i>View Students
                        </a>
                        <a href="<?= url('instructor/analytics.php') ?>"
                           class="block w-full px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg transition text-center backdrop-blur-sm">
                            <i class="fas fa-chart-line mr-2"></i>View Analytics
                        </a>
                    </div>
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
    <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <h4 class="font-bold text-yellow-400 mb-2">User Info</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto"><?= json_encode($debug_data['user'] ?? [], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <div>
            <h4 class="font-bold text-yellow-400 mb-2">Data Counts</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto"><?= json_encode($debug_data['data'] ?? [], JSON_PRETTY_PRINT) ?></pre>
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
