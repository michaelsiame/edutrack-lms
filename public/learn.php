<?php
/**
 * Course Learning Interface
 * Main page for taking courses
 */

require_once '../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

// Get course slug/ID from URL
$courseSlug = $_GET['course'] ?? null;
$lessonId = $_GET['lesson'] ?? null;

if (!$courseSlug) {
    redirect('my-courses.php');
}

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

if (!$course || !$course['enrollment_id']) {
    flash('error', 'You are not enrolled in this course.', 'error');
    redirect('course.php?slug=' . urlencode($courseSlug));
}

$courseId = $course['id'];

// Get all modules with their lessons
$modules = $db->fetchAll("
    SELECT m.*,
           COUNT(DISTINCT l.id) as lesson_count,
           COUNT(DISTINCT lp.lesson_id) as completed_lessons
    FROM modules m
    LEFT JOIN lessons l ON m.id = l.module_id
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id
        AND lp.user_id = ? AND lp.status = 'completed'
    WHERE m.course_id = ?
    GROUP BY m.id
    ORDER BY m.order_index ASC, m.id ASC
", [$userId, $courseId]);

// Get lessons for each module with progress
$lessonsGrouped = [];
foreach ($modules as $module) {
    $lessonsGrouped[$module['id']] = $db->fetchAll("
        SELECT l.*,
               lp.status as progress_status,
               lp.completed_at,
               lp.time_spent
        FROM lessons l
        LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ?
        WHERE l.module_id = ?
        ORDER BY l.order_index ASC, l.id ASC
    ", [$userId, $module['id']]);
}

// If no lesson specified, get the first incomplete lesson or first lesson
if (!$lessonId) {
    foreach ($modules as $module) {
        foreach ($lessonsGrouped[$module['id']] as $lesson) {
            if ($lesson['progress_status'] !== 'completed') {
                $lessonId = $lesson['id'];
                break 2;
            }
        }
    }

    // If all completed, show first lesson
    if (!$lessonId && !empty($modules) && !empty($lessonsGrouped[$modules[0]['id']])) {
        $lessonId = $lessonsGrouped[$modules[0]['id']][0]['id'];
    }
}

// Get current lesson details
$currentLesson = null;
$currentModule = null;
if ($lessonId) {
    $currentLesson = $db->fetchOne("
        SELECT l.*,
               lp.status as progress_status,
               lp.completed_at,
               lp.time_spent,
               lp.last_position,
               m.title as module_title,
               m.id as module_id
        FROM lessons l
        JOIN modules m ON l.module_id = m.id
        LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ?
        WHERE l.id = ? AND m.course_id = ?
    ", [$userId, $lessonId, $courseId]);

    if ($currentLesson) {
        $currentModule = $db->fetchOne("SELECT * FROM modules WHERE id = ?", [$currentLesson['module_id']]);
    }
}

// Get assignments for this course
$assignments = $db->fetchAll("
    SELECT a.*,
           asub.id as submission_id,
           asub.status as submission_status,
           asub.points_earned,
           asub.submitted_at,
           asub.graded_at
    FROM assignments a
    LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.user_id = ?
    WHERE a.course_id = ? AND a.status = 'published'
    ORDER BY a.due_date ASC
", [$userId, $courseId]);

// Get quizzes for this course
$quizzes = $db->fetchAll("
    SELECT q.*,
           COUNT(DISTINCT qa.id) as attempt_count,
           MAX(qa.score) as best_score,
           qa2.score as latest_score,
           qa2.completed_at as latest_attempt
    FROM quizzes q
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.user_id = ?
    LEFT JOIN quiz_attempts qa2 ON q.id = qa2.quiz_id AND qa2.user_id = ?
        AND qa2.completed_at = (
            SELECT MAX(completed_at) FROM quiz_attempts WHERE quiz_id = q.id AND user_id = ?
        )
    WHERE q.course_id = ? AND q.status = 'published'
    GROUP BY q.id
    ORDER BY q.id ASC
", [$userId, $userId, $userId, $courseId]);

// Get previous and next lessons
$prevLesson = null;
$nextLesson = null;
if ($currentLesson) {
    // Flatten all lessons in order
    $allLessons = [];
    foreach ($modules as $module) {
        foreach ($lessonsGrouped[$module['id']] as $lesson) {
            $allLessons[] = $lesson;
        }
    }

    // Find current lesson index
    $currentIndex = -1;
    foreach ($allLessons as $index => $lesson) {
        if ($lesson['id'] == $currentLesson['id']) {
            $currentIndex = $index;
            break;
        }
    }

    if ($currentIndex > 0) {
        $prevLesson = $allLessons[$currentIndex - 1];
    }
    if ($currentIndex < count($allLessons) - 1) {
        $nextLesson = $allLessons[$currentIndex + 1];
    }
}

// Update last accessed
$db->execute("
    UPDATE enrollments
    SET last_accessed = NOW()
    WHERE id = ?
", [$course['enrollment_id']]);

$page_title = $course['title'] . " - Learn";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($page_title) ?> - Edutrack</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .lesson-content { max-height: calc(100vh - 200px); overflow-y: auto; }
        .sidebar { max-height: calc(100vh - 80px); overflow-y: auto; }
        .video-container { position: relative; padding-bottom: 56.25%; height: 0; }
        .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
    </style>
</head>
<body class="bg-gray-100">

<!-- Top Navigation Bar -->
<div class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-full mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-4">
                <a href="<?= url('my-courses.php') ?>" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-2"></i>My Courses
                </a>
                <div class="border-l border-gray-300 h-6"></div>
                <h1 class="text-lg font-bold text-gray-900 truncate max-w-md">
                    <?= sanitize($course['title']) ?>
                </h1>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    Progress: <span class="font-bold text-primary-600"><?= round($course['progress_percentage']) ?>%</span>
                </div>
                <div class="w-32 bg-gray-200 rounded-full h-2">
                    <div class="bg-primary-600 h-2 rounded-full" style="width: <?= round($course['progress_percentage']) ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex">
    <!-- Left Sidebar - Course Content Navigation -->
    <div class="w-80 bg-white shadow-lg sidebar">
        <div class="p-4 border-b border-gray-200">
            <h2 class="font-bold text-gray-900">Course Content</h2>
        </div>

        <!-- Modules and Lessons -->
        <div class="divide-y divide-gray-200">
            <?php foreach ($modules as $module): ?>
                <div class="module-section" x-data="{ open: <?= $currentLesson && $currentLesson['module_id'] == $module['id'] ? 'true' : 'false' ?> }">
                    <button @click="open = !open" class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                        <div class="flex-1 text-left">
                            <h3 class="font-semibold text-gray-900 text-sm"><?= sanitize($module['title']) ?></h3>
                            <p class="text-xs text-gray-500 mt-1">
                                <?= $module['completed_lessons'] ?> / <?= $module['lesson_count'] ?> lessons
                            </p>
                        </div>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'transform rotate-180': open }"></i>
                    </button>

                    <div x-show="open" x-collapse class="bg-gray-50">
                        <?php foreach ($lessonsGrouped[$module['id']] as $lesson): ?>
                            <a href="?course=<?= urlencode($courseSlug) ?>&lesson=<?= $lesson['id'] ?>"
                               class="block px-4 py-3 pl-8 hover:bg-gray-100 transition border-l-4 <?= $currentLesson && $currentLesson['id'] == $lesson['id'] ? 'border-primary-600 bg-primary-50' : 'border-transparent' ?>">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2 flex-1">
                                        <?php if ($lesson['progress_status'] === 'completed'): ?>
                                            <i class="fas fa-check-circle text-green-600"></i>
                                        <?php elseif ($lesson['progress_status'] === 'in_progress'): ?>
                                            <i class="fas fa-play-circle text-primary-600"></i>
                                        <?php else: ?>
                                            <i class="fas fa-circle text-gray-300"></i>
                                        <?php endif; ?>
                                        <span class="text-sm text-gray-700 <?= $currentLesson && $currentLesson['id'] == $lesson['id'] ? 'font-semibold' : '' ?>">
                                            <?= sanitize($lesson['title']) ?>
                                        </span>
                                    </div>
                                    <?php if ($lesson['type'] === 'video'): ?>
                                        <i class="fas fa-video text-gray-400 text-xs"></i>
                                    <?php elseif ($lesson['type'] === 'text'): ?>
                                        <i class="fas fa-file-alt text-gray-400 text-xs"></i>
                                    <?php endif; ?>
                                </div>
                                <?php if ($lesson['duration']): ?>
                                    <p class="text-xs text-gray-500 mt-1 ml-6"><?= $lesson['duration'] ?> min</p>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Assignments Section -->
        <?php if (!empty($assignments)): ?>
        <div class="border-t border-gray-200 p-4">
            <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-file-alt text-green-600 mr-2"></i>
                Assignments (<?= count($assignments) ?>)
            </h3>
            <div class="space-y-2">
                <?php foreach (array_slice($assignments, 0, 3) as $assignment): ?>
                    <div class="text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700"><?= sanitize($assignment['title']) ?></span>
                            <?php if ($assignment['submission_status'] === 'graded'): ?>
                                <span class="text-green-600 text-xs"><i class="fas fa-check"></i></span>
                            <?php elseif ($assignment['submission_status'] === 'submitted'): ?>
                                <span class="text-yellow-600 text-xs"><i class="fas fa-clock"></i></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quizzes Section -->
        <?php if (!empty($quizzes)): ?>
        <div class="border-t border-gray-200 p-4">
            <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                Quizzes (<?= count($quizzes) ?>)
            </h3>
            <div class="space-y-2">
                <?php foreach (array_slice($quizzes, 0, 3) as $quiz): ?>
                    <div class="text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700"><?= sanitize($quiz['title']) ?></span>
                            <?php if ($quiz['best_score']): ?>
                                <span class="text-primary-600 text-xs font-semibold"><?= round($quiz['best_score']) ?>%</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1">
        <?php if ($currentLesson): ?>
            <div class="bg-white">
                <!-- Video/Content Player -->
                <?php if ($currentLesson['type'] === 'video' && $currentLesson['video_url']): ?>
                    <div class="video-container bg-black">
                        <?php
                        // Extract video ID from YouTube URL
                        $videoUrl = $currentLesson['video_url'];
                        $videoId = null;
                        if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $videoUrl, $matches)) {
                            $videoId = $matches[1];
                        } elseif (preg_match('/youtu\.be\/([^?]+)/', $videoUrl, $matches)) {
                            $videoId = $matches[1];
                        }
                        ?>
                        <?php if ($videoId): ?>
                            <iframe
                                src="https://www.youtube.com/embed/<?= sanitize($videoId) ?>?rel=0"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        <?php else: ?>
                            <video controls class="w-full h-full">
                                <source src="<?= sanitize($currentLesson['video_url']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Lesson Info and Content -->
                <div class="p-6 lesson-content">
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1"><?= sanitize($currentModule['title']) ?></p>
                                <h1 class="text-2xl font-bold text-gray-900"><?= sanitize($currentLesson['title']) ?></h1>
                            </div>
                            <button id="mark-complete-btn"
                                    onclick="toggleLessonComplete(<?= $currentLesson['id'] ?>, '<?= $currentLesson['progress_status'] === 'completed' ? 'uncomplete' : 'complete' ?>')"
                                    class="px-6 py-2 rounded-md font-medium transition <?= $currentLesson['progress_status'] === 'completed' ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-primary-600 text-white hover:bg-primary-700' ?>">
                                <i class="fas <?= $currentLesson['progress_status'] === 'completed' ? 'fa-check-circle' : 'fa-circle' ?> mr-2"></i>
                                <span id="complete-btn-text"><?= $currentLesson['progress_status'] === 'completed' ? 'Completed' : 'Mark as Complete' ?></span>
                            </button>
                        </div>

                        <?php if ($currentLesson['description']): ?>
                            <div class="prose max-w-none">
                                <?= nl2br(sanitize($currentLesson['description'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Lesson Content -->
                    <?php if ($currentLesson['content']): ?>
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4">Lesson Content</h2>
                            <div class="prose max-w-none">
                                <?= $currentLesson['content'] ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Resources/Attachments -->
                    <?php if ($currentLesson['attachments']): ?>
                        <div class="bg-blue-50 rounded-lg p-6 mb-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-paperclip text-blue-600 mr-2"></i>
                                Resources
                            </h2>
                            <div class="space-y-2">
                                <?php
                                $attachments = json_decode($currentLesson['attachments'], true);
                                if ($attachments && is_array($attachments)):
                                    foreach ($attachments as $attachment):
                                ?>
                                    <a href="<?= sanitize($attachment['url']) ?>"
                                       target="_blank"
                                       class="flex items-center text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-download mr-2"></i>
                                        <?= sanitize($attachment['name']) ?>
                                    </a>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Navigation Buttons -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <?php if ($prevLesson): ?>
                            <a href="?course=<?= urlencode($courseSlug) ?>&lesson=<?= $prevLesson['id'] ?>"
                               class="px-6 py-3 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition font-medium">
                                <i class="fas fa-chevron-left mr-2"></i>Previous Lesson
                            </a>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>

                        <?php if ($nextLesson): ?>
                            <a href="?course=<?= urlencode($courseSlug) ?>&lesson=<?= $nextLesson['id'] ?>"
                               class="px-6 py-3 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition font-medium">
                                Next Lesson<i class="fas fa-chevron-right ml-2"></i>
                            </a>
                        <?php else: ?>
                            <a href="<?= url('my-courses.php') ?>"
                               class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 transition font-medium">
                                <i class="fas fa-check-circle mr-2"></i>Course Complete
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- No lesson selected -->
            <div class="flex items-center justify-center h-screen">
                <div class="text-center">
                    <i class="fas fa-play-circle text-gray-300 text-6xl mb-4"></i>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Select a lesson to begin</h2>
                    <p class="text-gray-600">Choose a lesson from the sidebar to start learning</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function toggleLessonComplete(lessonId, action) {
    const btn = document.getElementById('mark-complete-btn');
    const btnText = document.getElementById('complete-btn-text');

    fetch('<?= url('api/lesson-progress.php') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            lesson_id: lessonId,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (action === 'complete') {
                btn.className = 'px-6 py-2 rounded-md font-medium transition bg-green-100 text-green-700 hover:bg-green-200';
                btnText.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Completed';
                btn.onclick = function() { toggleLessonComplete(lessonId, 'uncomplete'); };
            } else {
                btn.className = 'px-6 py-2 rounded-md font-medium transition bg-primary-600 text-white hover:bg-primary-700';
                btnText.innerHTML = '<i class="fas fa-circle mr-2"></i>Mark as Complete';
                btn.onclick = function() { toggleLessonComplete(lessonId, 'complete'); };
            }
            // Reload to update progress
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

</body>
</html>
