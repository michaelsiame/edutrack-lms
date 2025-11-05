<?php
/**
 * Course Learning Interface
 * Main page for taking courses (Simplified with error handling)
 */

require_once '../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

try {
    $user = User::current();
    $userId = $user->getId();
} catch (Exception $e) {
    error_log("Learn.php Error - User::current(): " . $e->getMessage());
    flash('error', 'Unable to load user information', 'error');
    redirect('index.php');
}

// Get course slug/ID from URL
$courseSlug = $_GET['course'] ?? null;
$lessonId = $_GET['lesson'] ?? null;

if (!$courseSlug) {
    redirect('my-courses.php');
}

try {
    // Get course information
    $course = $db->fetchOne("
        SELECT c.*,
               u.first_name as instructor_first_name,
               u.last_name as instructor_last_name,
               e.id as enrollment_id,
               e.progress_percentage,
               e.status as enrollment_status
        FROM courses c
        JOIN users u ON c.instructor_id = u.id
        LEFT JOIN enrollments e ON e.course_id = c.id AND e.user_id = ?
        WHERE c.slug = ?
    ", [$userId, $courseSlug]);

    if (!$course) {
        flash('error', 'Course not found', 'error');
        redirect('courses.php');
    }

    if (!$course['enrollment_id']) {
        flash('error', 'You are not enrolled in this course.', 'error');
        redirect('course.php?slug=' . urlencode($courseSlug));
    }

    $courseId = $course['id'];

    // Try to get modules and lessons (with error handling)
    $modules = [];
    $lessonsGrouped = [];

    try {
        // Get all modules with their lessons
        $modules = $db->fetchAll("
            SELECT m.*,
                   COUNT(DISTINCT l.id) as lesson_count
            FROM course_modules m
            LEFT JOIN lessons l ON m.id = l.module_id
            WHERE m.course_id = ?
            GROUP BY m.id
            ORDER BY m.order_index ASC, m.id ASC
        ", [$courseId]);

        // Get lessons for each module
        foreach ($modules as $module) {
            $lessonsGrouped[$module['id']] = $db->fetchAll("
                SELECT l.*
                FROM lessons l
                WHERE l.module_id = ?
                ORDER BY l.order_index ASC, l.id ASC
            ", [$module['id']]);
        }
    } catch (Exception $e) {
        error_log("Learn.php Error - Modules/Lessons query: " . $e->getMessage());
        // Continue with empty arrays
    }

    // If no lesson specified, get the first lesson
    if (!$lessonId && !empty($modules) && !empty($lessonsGrouped[$modules[0]['id']])) {
        $lessonId = $lessonsGrouped[$modules[0]['id']][0]['id'];
    }

    // Get current lesson details
    $currentLesson = null;
    $currentModule = null;
    if ($lessonId) {
        try {
            $currentLesson = $db->fetchOne("
                SELECT l.*,
                       m.title as module_title,
                       m.id as module_id
                FROM lessons l
                JOIN course_modules m ON l.module_id = m.id
                WHERE l.id = ? AND m.course_id = ?
            ", [$lessonId, $courseId]);

            if ($currentLesson) {
                $currentModule = $db->fetchOne("SELECT * FROM course_modules WHERE id = ?", [$currentLesson['module_id']]);
            }
        } catch (Exception $e) {
            error_log("Learn.php Error - Current lesson query: " . $e->getMessage());
        }
    }

    // Set page title
    $page_title = $course['title'] . ' - Learn';

} catch (Exception $e) {
    error_log("Learn.php Fatal Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    flash('error', 'An error occurred loading the course. Please contact support if this persists.', 'error');
    redirect('my-courses.php');
}

// Include header
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-100">

    <!-- Course Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="<?= url('my-courses.php') ?>" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($course['title']) ?></h1>
                        <p class="text-sm text-gray-600">
                            Instructor: <?= htmlspecialchars($course['instructor_first_name'] . ' ' . $course['instructor_last_name']) ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Progress</p>
                        <p class="text-lg font-bold text-blue-600"><?= round($course['progress_percentage'] ?? 0) ?>%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            <!-- Sidebar - Course Content -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h2 class="font-bold text-gray-900">Course Content</h2>
                    </div>

                    <?php if (empty($modules)): ?>
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-folder-open text-4xl mb-2"></i>
                        <p>No modules available yet</p>
                        <p class="text-sm mt-2">The instructor is preparing course content.</p>
                    </div>
                    <?php else: ?>
                    <div class="divide-y">
                        <?php foreach ($modules as $module): ?>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">
                                <?= htmlspecialchars($module['title']) ?>
                            </h3>

                            <?php if (!empty($lessonsGrouped[$module['id']])): ?>
                            <ul class="space-y-2 ml-4">
                                <?php foreach ($lessonsGrouped[$module['id']] as $lesson): ?>
                                <li>
                                    <a href="<?= url('learn.php?course=' . urlencode($courseSlug) . '&lesson=' . $lesson['id']) ?>"
                                       class="flex items-center text-sm <?= $lessonId == $lesson['id'] ? 'text-blue-600 font-semibold' : 'text-gray-700 hover:text-blue-600' ?>">
                                        <i class="fas fa-play-circle mr-2"></i>
                                        <?= htmlspecialchars($lesson['title']) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else: ?>
                            <p class="text-sm text-gray-500 ml-4">No lessons yet</p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Content - Lesson -->
            <div class="lg:col-span-3">
                <?php if ($currentLesson): ?>
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600"><?= htmlspecialchars($currentModule['title'] ?? '') ?></span>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($currentLesson['title']) ?></h2>
                    </div>

                    <div class="p-6">
                        <?php if ($currentLesson['type'] == 'video' && $currentLesson['content_url']): ?>
                        <!-- Video Player -->
                        <div class="aspect-video bg-black rounded-lg mb-6">
                            <iframe width="100%" height="100%"
                                    src="<?= htmlspecialchars($currentLesson['content_url']) ?>"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                    class="rounded-lg"></iframe>
                        </div>
                        <?php endif; ?>

                        <!-- Lesson Description -->
                        <?php if ($currentLesson['description']): ?>
                        <div class="prose max-w-none">
                            <?= $currentLesson['description'] ?>
                        </div>
                        <?php endif; ?>

                        <!-- Lesson Content -->
                        <?php if ($currentLesson['content']): ?>
                        <div class="mt-6 prose max-w-none">
                            <?= $currentLesson['content'] ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Lesson Navigation -->
                    <div class="p-6 border-t bg-gray-50">
                        <div class="flex justify-between">
                            <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                <i class="fas fa-arrow-left mr-2"></i>Previous Lesson
                            </button>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Mark as Complete
                            </button>
                            <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                Next Lesson<i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <i class="fas fa-graduation-cap text-6xl text-gray-300 mb-4"></i>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome to Your Course!</h2>
                    <p class="text-gray-600 mb-6">Select a lesson from the sidebar to begin learning.</p>
                    <?php if (!empty($modules) && !empty($lessonsGrouped[$modules[0]['id']])): ?>
                    <a href="<?= url('learn.php?course=' . urlencode($courseSlug) . '&lesson=' . $lessonsGrouped[$modules[0]['id']][0]['id']) ?>"
                       class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-play mr-2"></i>Start Learning
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>

<?php require_once '../src/templates/footer.php'; ?>
