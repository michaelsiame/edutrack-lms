<?php
/**
 * Instructor Courses Management
 */

// Debug initialization
$DEBUG_MODE = defined('DEBUG_MODE') ? DEBUG_MODE : ($_ENV['DEBUG_MODE'] ?? false);
$page_start_time = microtime(true);
$page_start_memory = memory_get_usage();
$debug_data = [
    'page' => 'instructor/courses.php',
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
require_once '../../src/classes/Statistics.php';

// Debug: Log user info
if ($DEBUG_MODE) {
    $debug_data['user'] = [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role' => $_SESSION['user_role'] ?? null
    ];
}

$user = User::current();
$instructorId = $user->getId();

// Debug: Log instructor ID
if ($DEBUG_MODE) {
    $debug_data['instructor_id'] = $instructorId;
}

// Get instructor's courses
$courses = Course::all(['instructor_id' => $instructorId]);

// Get statistics using Statistics class
$instructorStats = Statistics::getInstructorStats($instructorId);

$stats = [
    'total' => $instructorStats['total_courses'],
    'published' => $instructorStats['published_courses'],
    'draft' => $instructorStats['total_courses'] - $instructorStats['published_courses'],
    'total_students' => $instructorStats['total_students']
];

// Debug: Log courses and stats data
if ($DEBUG_MODE) {
    $debug_data['data'] = [
        'courses_count' => count($courses),
        'stats' => $stats,
        'instructor_stats' => $instructorStats
    ];
}

$page_title = 'My Courses - Instructor';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Courses</h1>
                <p class="text-gray-600 mt-1">Manage your courses and content</p>
            </div>
            <a href="<?= url('instructor/courses/create.php') ?>" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Create Course
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Courses</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total'] ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-book text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Published</p>
                        <p class="text-3xl font-bold text-green-600 mt-2"><?= $stats['published'] ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Drafts</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-2"><?= $stats['draft'] ?></p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-edit text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2"><?= $stats['total_students'] ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-users text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Grid -->
        <?php if (empty($courses)): ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Courses Yet</h3>
                <p class="text-gray-500 mb-6">Create your first course to start teaching</p>
                <a href="<?= url('instructor/courses/create.php') ?>" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i> Create Your First Course
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($courses as $course): ?>
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
                        <!-- Thumbnail -->
                        <div class="relative h-48">
                            <?php if (!empty($course['thumbnail_url'])): ?>
                                <img src="<?= htmlspecialchars($course['thumbnail_url']) ?>"
                                     alt="<?= htmlspecialchars($course['title']) ?>"
                                     class="w-full h-full object-cover rounded-t-lg">
                            <?php else: ?>
                                <div class="w-full h-full bg-gradient-to-br from-primary-400 to-primary-600 rounded-t-lg flex items-center justify-center">
                                    <i class="fas fa-book text-white text-5xl"></i>
                                </div>
                            <?php endif; ?>

                            <!-- Status Badge -->
                            <div class="absolute top-2 right-2">
                                <?php if ($course['status'] == 'published'): ?>
                                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        Published
                                    </span>
                                <?php elseif ($course['status'] == 'draft'): ?>
                                    <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        Draft
                                    </span>
                                <?php else: ?>
                                    <span class="bg-gray-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        Archived
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2">
                                <?= htmlspecialchars($course['title']) ?>
                            </h3>

                            <div class="flex items-center text-sm text-gray-600 space-x-4 mb-4">
                                <span><i class="fas fa-users mr-1"></i> <?= $course['total_students'] ?? 0 ?></span>
                                <span><i class="fas fa-book-open mr-1"></i> <?= $course['total_lessons'] ?? 0 ?> lessons</span>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between space-x-2">
                                <a href="<?= url('instructor/course-edit.php?id=' . $course['id']) ?>"
                                   class="flex-1 btn btn-primary btn-sm">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <a href="<?= url('instructor/courses/modules.php?id=' . $course['id']) ?>"
                                   class="flex-1 btn btn-secondary btn-sm">
                                    <i class="fas fa-list mr-1"></i> Content
                                </a>
                                <a href="<?= url('course.php?slug=' . $course['slug']) ?>"
                                   target="_blank"
                                   class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

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
            <h4 class="font-bold text-yellow-400 mb-2">Data</h4>
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
