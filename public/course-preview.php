<?php
/**
 * Course Preview Page
 * Preview free lessons before enrollment
 */

require_once '../src/bootstrap.php';

// Get course slug and lesson ID
$courseSlug = $_GET['course'] ?? '';
$lessonId = filter_input(INPUT_GET, 'lesson', FILTER_VALIDATE_INT);

if (empty($courseSlug)) {
    flash('error', 'Course not specified', 'error');
    redirect('courses.php');
}

try {
    // Get course details
    $course = $db->fetchOne("
        SELECT c.*,
               cat.name as category_name,
               u.first_name as instructor_fname, u.last_name as instructor_lname
        FROM courses c
        LEFT JOIN course_categories cat ON c.category_id = cat.id
        LEFT JOIN users u ON c.instructor_id = u.id
        WHERE c.slug = ? AND c.status = 'Published'
    ", [$courseSlug]);

    if (!$course) {
        flash('error', 'Course not found', 'error');
        redirect('courses.php');
    }

    // Get modules with lessons
    $modules = $db->fetchAll("
        SELECT m.*,
               COUNT(l.id) as lesson_count
        FROM modules m
        LEFT JOIN lessons l ON m.id = l.module_id
        WHERE m.course_id = ?
        GROUP BY m.id
        ORDER BY m.display_order ASC
    ", [$course['id']]);

    // Get lessons for each module
    foreach ($modules as &$module) {
        $module['lessons'] = $db->fetchAll("
            SELECT id, title, content_type, duration_minutes, is_free
            FROM lessons
            WHERE module_id = ?
            ORDER BY display_order ASC
        ", [$module['id']]);
    }

    // Get the preview lesson if specified
    $previewLesson = null;
    if ($lessonId) {
        $previewLesson = $db->fetchOne("
            SELECT l.*, m.title as module_title
            FROM lessons l
            JOIN modules m ON l.module_id = m.id
            WHERE l.id = ? AND m.course_id = ? AND l.is_free = 1
        ", [$lessonId, $course['id']]);

        if (!$previewLesson) {
            flash('warning', 'This lesson is not available for preview. Enroll in the course to access all content.', 'warning');
            $previewLesson = null;
        }
    }

    // If no lesson selected, find the first free lesson
    if (!$previewLesson) {
        $previewLesson = $db->fetchOne("
            SELECT l.*, m.title as module_title
            FROM lessons l
            JOIN modules m ON l.module_id = m.id
            WHERE m.course_id = ? AND l.is_free = 1
            ORDER BY m.display_order ASC, l.display_order ASC
            LIMIT 1
        ", [$course['id']]);
    }

    // Check if user is already enrolled
    $isEnrolled = false;
    if (isLoggedIn()) {
        $enrollment = $db->fetchOne("
            SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?
        ", [currentUserId(), $course['id']]);
        $isEnrolled = !empty($enrollment);
    }

    $page_title = 'Preview: ' . $course['title'];
    require_once '../src/templates/header.php';

} catch (Exception $e) {
    error_log("Course Preview Error: " . $e->getMessage());
    flash('error', 'An error occurred loading the preview', 'error');
    redirect('courses.php');
}
?>

<div class="min-h-screen bg-gray-100">

    <!-- Course Header -->
    <div class="bg-gradient-to-r from-primary-700 to-primary-900 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center text-sm mb-4">
                <a href="<?= url('courses.php') ?>" class="text-primary-200 hover:text-white">Courses</a>
                <i class="fas fa-chevron-right mx-2 text-xs text-primary-300"></i>
                <a href="<?= url('course.php?slug=' . urlencode($courseSlug)) ?>" class="text-primary-200 hover:text-white"><?= sanitize($course['title']) ?></a>
                <i class="fas fa-chevron-right mx-2 text-xs text-primary-300"></i>
                <span>Preview</span>
            </div>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <span class="inline-block px-3 py-1 bg-yellow-500 text-yellow-900 text-xs font-semibold rounded-full mb-2">
                        <i class="fas fa-eye mr-1"></i> FREE PREVIEW
                    </span>
                    <h1 class="text-2xl md:text-3xl font-bold"><?= sanitize($course['title']) ?></h1>
                    <p class="text-primary-200 mt-2"><?= sanitize($course['category_name'] ?? 'General') ?></p>
                </div>
                <div class="mt-4 md:mt-0">
                    <?php if ($isEnrolled): ?>
                    <a href="<?= url('learn.php?course=' . urlencode($courseSlug)) ?>"
                       class="inline-flex items-center px-6 py-3 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition">
                        <i class="fas fa-play-circle mr-2"></i>Continue Learning
                    </a>
                    <?php else: ?>
                    <a href="<?= url('course.php?slug=' . urlencode($courseSlug)) ?>"
                       class="inline-flex items-center px-6 py-3 bg-yellow-500 text-yellow-900 font-semibold rounded-lg hover:bg-yellow-400 transition">
                        <i class="fas fa-graduation-cap mr-2"></i>Enroll Now
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <?php if ($previewLesson): ?>
                <!-- Preview Player -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                    <div class="bg-gray-900 aspect-video flex items-center justify-center">
                        <?php if ($previewLesson['content_type'] === 'video' && !empty($previewLesson['video_url'])): ?>
                            <?php
                            // Check if YouTube
                            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $previewLesson['video_url'], $matches)) {
                                $videoId = $matches[1];
                            ?>
                            <iframe
                                src="https://www.youtube.com/embed/<?= $videoId ?>?rel=0"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                            <?php } elseif (preg_match('/vimeo\.com\/(\d+)/', $previewLesson['video_url'], $matches)) {
                                $videoId = $matches[1];
                            ?>
                            <iframe
                                src="https://player.vimeo.com/video/<?= $videoId ?>"
                                class="w-full h-full"
                                frameborder="0"
                                allow="autoplay; fullscreen; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                            <?php } else { ?>
                            <video controls class="w-full h-full">
                                <source src="<?= sanitize($previewLesson['video_url']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <?php } ?>
                        <?php else: ?>
                        <div class="text-center text-gray-400">
                            <i class="fas fa-file-alt text-6xl mb-4"></i>
                            <p>Text Lesson</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-6">
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium mr-2">
                                <i class="fas fa-unlock mr-1"></i>Free Preview
                            </span>
                            <span><?= sanitize($previewLesson['module_title']) ?></span>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4"><?= sanitize($previewLesson['title']) ?></h2>

                        <?php if ($previewLesson['content_type'] === 'text' || !empty($previewLesson['content'])): ?>
                        <div class="prose max-w-none text-gray-600">
                            <?= $previewLesson['content'] ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <!-- No Free Preview Available -->
                <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full mx-auto flex items-center justify-center mb-4">
                        <i class="fas fa-lock text-gray-400 text-3xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">No Free Preview Available</h2>
                    <p class="text-gray-600 mb-6">This course doesn't have any free preview lessons. Enroll to access all content.</p>
                    <a href="<?= url('course.php?slug=' . urlencode($courseSlug)) ?>"
                       class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition">
                        <i class="fas fa-graduation-cap mr-2"></i>View Course Details
                    </a>
                </div>
                <?php endif; ?>

                <!-- Enroll CTA -->
                <?php if (!$isEnrolled): ?>
                <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-lg p-6 text-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-xl font-bold">Ready to Learn More?</h3>
                            <p class="text-primary-100">Enroll now to access all lessons, quizzes, and get certified.</p>
                        </div>
                        <a href="<?= url('course.php?slug=' . urlencode($courseSlug)) ?>"
                           class="inline-flex items-center px-6 py-3 bg-white text-primary-600 font-semibold rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-graduation-cap mr-2"></i>Enroll Now
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar - Course Content -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden sticky top-4">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h3 class="font-bold text-gray-900">Course Content</h3>
                        <p class="text-sm text-gray-600">
                            <?= count($modules) ?> modules
                        </p>
                    </div>

                    <div class="divide-y divide-gray-200 max-h-[60vh] overflow-y-auto">
                        <?php foreach ($modules as $module): ?>
                        <div class="p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">
                                <i class="fas fa-folder text-primary-500 mr-2"></i>
                                <?= sanitize($module['title']) ?>
                            </h4>
                            <ul class="space-y-2 ml-6">
                                <?php foreach ($module['lessons'] as $lesson): ?>
                                <li>
                                    <?php if ($lesson['is_free']): ?>
                                    <a href="<?= url('course-preview.php?course=' . urlencode($courseSlug) . '&lesson=' . $lesson['id']) ?>"
                                       class="flex items-center text-sm <?= ($previewLesson && $previewLesson['id'] == $lesson['id']) ? 'text-primary-600 font-medium' : 'text-gray-600 hover:text-primary-600' ?>">
                                        <i class="fas fa-play-circle mr-2 text-green-500"></i>
                                        <?= sanitize($lesson['title']) ?>
                                        <span class="ml-auto text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded">Free</span>
                                    </a>
                                    <?php else: ?>
                                    <span class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-lock mr-2"></i>
                                        <?= sanitize($lesson['title']) ?>
                                    </span>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!$isEnrolled): ?>
                    <div class="p-4 bg-gray-50 border-t">
                        <a href="<?= url('course.php?slug=' . urlencode($courseSlug)) ?>"
                           class="block w-full py-3 px-4 bg-primary-600 text-white text-center font-semibold rounded-lg hover:bg-primary-700 transition">
                            <i class="fas fa-graduation-cap mr-2"></i>Enroll to Unlock All
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</div>

<?php require_once '../src/templates/footer.php'; ?>
