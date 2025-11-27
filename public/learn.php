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
               e.progress as progress_percentage,
               e.enrollment_status,
               e.student_id
        FROM courses c
        LEFT JOIN instructors i ON c.instructor_id = i.id
        LEFT JOIN users u ON i.user_id = u.id
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
    $quizzes = [];
    $assignments = [];
    $liveSessions = [];

    try {
        // Get all modules with their lessons
        $modules = $db->fetchAll("
            SELECT m.*,
                   COUNT(DISTINCT l.id) as lesson_count
            FROM modules m
            LEFT JOIN lessons l ON m.id = l.module_id
            WHERE m.course_id = ?
            GROUP BY m.id
            ORDER BY m.display_order ASC, m.id ASC
        ", [$courseId]);

        // Get lessons for each module
        foreach ($modules as $module) {
            $lessonsGrouped[$module['id']] = $db->fetchAll("
                SELECT l.*
                FROM lessons l
                WHERE l.module_id = ?
                ORDER BY l.display_order ASC, l.id ASC
            ", [$module['id']]);
        }

        // Get quizzes for this course
        $studentId = $course['student_id'] ?? null;
        $quizzes = $db->fetchAll("
            SELECT q.*,
                   COUNT(DISTINCT qa.id) as attempt_count,
                   MAX(qa.percentage) as best_score,
                   MAX(qa.passed) as has_passed
            FROM quizzes q
            LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.student_id = ?
            WHERE q.course_id = ? AND q.is_published = 1
            GROUP BY q.id
            ORDER BY q.created_at ASC
        ", [$studentId, $courseId]);

        // Get assignments for this course
        $assignments = $db->fetchAll("
            SELECT a.*,
                   COUNT(DISTINCT s.id) as submission_count,
                   MAX(s.score) as best_score,
                   MAX(s.status) as submission_status
            FROM assignments a
            LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.student_id = ?
            WHERE a.course_id = ?
            GROUP BY a.id
            ORDER BY a.created_at ASC
        ", [$studentId, $courseId]);

        // Get upcoming live sessions for this course
        $liveSessions = $db->fetchAll("
            SELECT ls.*, l.title as lesson_title, u.name as instructor_name
            FROM live_sessions ls
            JOIN lessons l ON ls.lesson_id = l.id
            JOIN modules m ON l.module_id = m.id
            LEFT JOIN users u ON ls.instructor_id = u.id
            WHERE m.course_id = ?
            AND ls.status IN ('scheduled', 'live')
            AND ls.scheduled_start_time >= NOW()
            ORDER BY ls.scheduled_start_time ASC
        ", [$courseId]);

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
    $previousLesson = null;
    $nextLesson = null;

    if ($lessonId) {
        try {
            $currentLesson = $db->fetchOne("
                SELECT l.*,
                       m.title as module_title,
                       m.id as module_id
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                WHERE l.id = ? AND m.course_id = ?
            ", [$lessonId, $courseId]);

            if ($currentLesson) {
                $currentModule = $db->fetchOne("SELECT * FROM modules WHERE id = ?", [$currentLesson['module_id']]);

                // Get all lessons in order to find previous and next
                $allLessons = $db->fetchAll("
                    SELECT l.id, l.title, m.display_order as module_order, l.display_order as lesson_order
                    FROM lessons l
                    JOIN modules m ON l.module_id = m.id
                    WHERE m.course_id = ?
                    ORDER BY m.display_order ASC, l.display_order ASC
                ", [$courseId]);

                // Find current lesson index and get prev/next
                foreach ($allLessons as $index => $lesson) {
                    if ($lesson['id'] == $lessonId) {
                        if ($index > 0) {
                            $previousLesson = $allLessons[$index - 1];
                        }
                        if ($index < count($allLessons) - 1) {
                            $nextLesson = $allLessons[$index + 1];
                        }
                        break;
                    }
                }
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
                                <?php foreach ($lessonsGrouped[$module['id']] as $lesson):
                                    $icon = 'fa-play-circle';
                                    if ($lesson['lesson_type'] == 'text') $icon = 'fa-file-alt';
                                    elseif ($lesson['lesson_type'] == 'quiz') $icon = 'fa-question-circle';
                                    elseif ($lesson['lesson_type'] == 'assignment') $icon = 'fa-tasks';
                                    elseif ($lesson['lesson_type'] == 'Live Session') $icon = 'fa-video';
                                ?>
                                <li>
                                    <a href="<?= url('learn.php?course=' . urlencode($courseSlug) . '&lesson=' . $lesson['id']) ?>"
                                       class="flex items-center justify-between text-sm <?= $lessonId == $lesson['id'] ? 'text-blue-600 font-semibold' : 'text-gray-700 hover:text-blue-600' ?>">
                                        <span class="flex items-center">
                                            <i class="fas <?= $icon ?> mr-2"></i>
                                            <?= htmlspecialchars($lesson['title']) ?>
                                        </span>
                                        <?php if ($lesson['duration_minutes']): ?>
                                        <span class="text-xs text-gray-500"><?= $lesson['duration_minutes'] ?>m</span>
                                        <?php endif; ?>
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

                    <!-- Quizzes Section -->
                    <?php if (!empty($quizzes)): ?>
                    <div class="p-4 border-t bg-blue-50">
                        <h3 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                            Quizzes
                        </h3>
                        <ul class="space-y-2 ml-4">
                            <?php foreach ($quizzes as $quiz): ?>
                            <li>
                                <a href="<?= url('take-quiz.php?quiz_id=' . $quiz['id']) ?>"
                                   class="flex items-center justify-between text-sm text-gray-700 hover:text-blue-600">
                                    <span class="flex items-center">
                                        <i class="fas fa-clipboard-list mr-2"></i>
                                        <?= htmlspecialchars($quiz['title']) ?>
                                    </span>
                                    <?php if ($quiz['has_passed']): ?>
                                    <span class="text-xs text-green-600">
                                        <i class="fas fa-check-circle"></i> <?= round($quiz['best_score']) ?>%
                                    </span>
                                    <?php elseif ($quiz['attempt_count'] > 0): ?>
                                    <span class="text-xs text-orange-600">
                                        <?= $quiz['attempt_count'] ?> attempt(s)
                                    </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Assignments Section -->
                    <?php if (!empty($assignments)): ?>
                    <div class="p-4 border-t bg-green-50">
                        <h3 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-tasks text-green-600 mr-2"></i>
                            Assignments
                        </h3>
                        <ul class="space-y-2 ml-4">
                            <?php foreach ($assignments as $assignment): ?>
                            <li>
                                <a href="<?= url('assignment.php?id=' . $assignment['id']) ?>"
                                   class="flex items-center justify-between text-sm text-gray-700 hover:text-green-600">
                                    <span class="flex items-center">
                                        <i class="fas fa-file-alt mr-2"></i>
                                        <?= htmlspecialchars($assignment['title']) ?>
                                    </span>
                                    <?php if ($assignment['submission_status'] == 'graded'): ?>
                                    <span class="text-xs text-green-600">
                                        <i class="fas fa-check-circle"></i> <?= round($assignment['best_score']) ?>
                                    </span>
                                    <?php elseif ($assignment['submission_count'] > 0): ?>
                                    <span class="text-xs text-blue-600">
                                        <i class="fas fa-clock"></i> Submitted
                                    </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Live Sessions Section -->
                    <?php if (!empty($liveSessions)): ?>
                    <div class="p-4 border-t bg-red-50">
                        <h3 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-video text-red-600 mr-2"></i>
                            Upcoming Live Sessions
                        </h3>
                        <ul class="space-y-2 ml-4">
                            <?php foreach ($liveSessions as $liveSession):
                                $sessionStart = new DateTime($liveSession['scheduled_start_time']);
                                $now = new DateTime();
                                $isLive = ($liveSession['status'] === 'live');
                            ?>
                            <li>
                                <a href="<?= url('live-session.php?session_id=' . $liveSession['id']) ?>"
                                   class="flex items-center justify-between text-sm text-gray-700 hover:text-red-600"
                                   target="_blank">
                                    <span class="flex items-center">
                                        <i class="fas fa-broadcast-tower mr-2 <?= $isLive ? 'text-red-600 animate-pulse' : '' ?>"></i>
                                        <?= htmlspecialchars($liveSession['lesson_title']) ?>
                                    </span>
                                    <?php if ($isLive): ?>
                                    <span class="text-xs text-red-600 font-bold">
                                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i> LIVE
                                    </span>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-500">
                                        <?= $sessionStart->format('M d, g:i A') ?>
                                    </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
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
                        <?php if ($currentLesson['lesson_type'] == 'Live Session'): ?>
                        <!-- Live Session -->
                        <?php
                        // Get live session for this lesson
                        $liveSessionData = $db->fetchOne("
                            SELECT ls.*, u.name as instructor_name
                            FROM live_sessions ls
                            LEFT JOIN users u ON ls.instructor_id = u.id
                            WHERE ls.lesson_id = ?
                            ORDER BY ls.scheduled_start_time DESC
                            LIMIT 1
                        ", [$currentLesson['id']]);

                        if ($liveSessionData):
                            $sessionStart = new DateTime($liveSessionData['scheduled_start_time']);
                            $sessionEnd = new DateTime($liveSessionData['scheduled_end_time']);
                            $now = new DateTime();
                            $bufferBefore = (int)($liveSessionData['buffer_minutes_before'] ?? 15);
                            $bufferAfter = (int)($liveSessionData['buffer_minutes_after'] ?? 30);
                            $canJoinTime = clone $sessionStart;
                            $canJoinTime->modify("-{$bufferBefore} minutes");
                            $endJoinTime = clone $sessionEnd;
                            $endJoinTime->modify("+{$bufferAfter} minutes");
                            $canJoinNow = ($now >= $canJoinTime && $now <= $endJoinTime && $liveSessionData['status'] !== 'cancelled');
                            $isLive = ($liveSessionData['status'] === 'live');
                        ?>
                        <div class="bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-200 rounded-lg p-6 mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <i class="fas fa-video text-red-600 text-3xl mr-4"></i>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">Live Session</h3>
                                        <p class="text-gray-600">Interactive online class with your instructor</p>
                                    </div>
                                </div>
                                <?php if ($isLive): ?>
                                <span class="px-4 py-2 bg-red-600 text-white font-bold rounded-full animate-pulse">
                                    <i class="fas fa-circle text-xs"></i> LIVE NOW
                                </span>
                                <?php elseif ($liveSessionData['status'] === 'scheduled'): ?>
                                <span class="px-4 py-2 bg-blue-600 text-white font-bold rounded-full">
                                    <i class="fas fa-calendar"></i> Scheduled
                                </span>
                                <?php elseif ($liveSessionData['status'] === 'ended'): ?>
                                <span class="px-4 py-2 bg-gray-600 text-white font-bold rounded-full">
                                    <i class="fas fa-check"></i> Ended
                                </span>
                                <?php elseif ($liveSessionData['status'] === 'cancelled'): ?>
                                <span class="px-4 py-2 bg-gray-600 text-white font-bold rounded-full">
                                    <i class="fas fa-times"></i> Cancelled
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div class="bg-white rounded p-3">
                                    <div class="text-sm text-gray-600">Date & Time</div>
                                    <div class="font-bold text-gray-900">
                                        <i class="fas fa-calendar-alt text-red-600 mr-2"></i>
                                        <?= $sessionStart->format('l, F j, Y') ?>
                                    </div>
                                    <div class="text-gray-700">
                                        <i class="fas fa-clock text-red-600 mr-2"></i>
                                        <?= $sessionStart->format('g:i A') ?> - <?= $sessionEnd->format('g:i A') ?>
                                    </div>
                                </div>
                                <div class="bg-white rounded p-3">
                                    <div class="text-sm text-gray-600">Duration</div>
                                    <div class="font-bold text-gray-900">
                                        <i class="fas fa-hourglass-half text-red-600 mr-2"></i>
                                        <?= $liveSessionData['duration_minutes'] ?> minutes
                                    </div>
                                    <?php if ($liveSessionData['instructor_name']): ?>
                                    <div class="text-gray-700">
                                        <i class="fas fa-user text-red-600 mr-2"></i>
                                        <?= htmlspecialchars($liveSessionData['instructor_name']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($liveSessionData['description']): ?>
                            <div class="bg-white rounded p-3 mb-4">
                                <div class="text-sm text-gray-600 mb-1">About this session</div>
                                <div class="text-gray-700"><?= htmlspecialchars($liveSessionData['description']) ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if ($canJoinNow): ?>
                            <a href="<?= url('live-session.php?session_id=' . $liveSessionData['id']) ?>"
                               target="_blank"
                               class="block w-full py-4 bg-red-600 text-white text-center font-bold rounded-lg hover:bg-red-700 transition">
                                <i class="fas fa-video mr-2"></i>
                                <?= $isLive ? 'JOIN LIVE SESSION NOW' : 'JOIN SESSION' ?>
                            </a>
                            <?php elseif ($liveSessionData['status'] === 'scheduled'): ?>
                            <div class="bg-yellow-100 border border-yellow-300 rounded p-4 text-center">
                                <i class="fas fa-info-circle text-yellow-700 mr-2"></i>
                                <span class="text-yellow-700">
                                    This session will be available to join <?= $bufferBefore ?> minutes before start time
                                </span>
                            </div>
                            <?php elseif ($liveSessionData['status'] === 'ended' && $liveSessionData['recording_url']): ?>
                            <a href="<?= htmlspecialchars($liveSessionData['recording_url']) ?>"
                               target="_blank"
                               class="block w-full py-4 bg-blue-600 text-white text-center font-bold rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-play mr-2"></i>
                                WATCH RECORDING
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="bg-yellow-100 border border-yellow-300 rounded-lg p-6 mb-6 text-center">
                            <i class="fas fa-calendar-times text-yellow-700 text-3xl mb-3"></i>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">No Live Session Scheduled</h3>
                            <p class="text-gray-700">Your instructor hasn't scheduled a live session for this lesson yet.</p>
                        </div>
                        <?php endif; ?>

                        <?php elseif ($currentLesson['lesson_type'] == 'video' && $currentLesson['video_url']): ?>
                        <!-- Video Player -->
                        <div class="aspect-video bg-black rounded-lg mb-6">
                            <iframe width="100%" height="100%"
                                    src="https://www.youtube.com/embed/<?= htmlspecialchars($currentLesson['video_url']) ?>"
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
                        <div class="flex justify-between items-center">
                            <?php if ($previousLesson): ?>
                            <a href="<?= url('learn.php?course=' . urlencode($courseSlug) . '&lesson=' . $previousLesson['id']) ?>"
                               class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                                <i class="fas fa-arrow-left mr-2"></i>Previous Lesson
                            </a>
                            <?php else: ?>
                            <div></div>
                            <?php endif; ?>

                            <form method="POST" action="<?= url('actions/mark-lesson-complete.php') ?>" class="inline">
                                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                                <input type="hidden" name="lesson_id" value="<?= $lessonId ?>">
                                <input type="hidden" name="redirect" value="<?= urlencode('learn.php?course=' . $courseSlug . '&lesson=' . $lessonId) ?>">
                                <?= csrfField() ?>
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                    <i class="fas fa-check mr-2"></i>Mark as Complete
                                </button>
                            </form>

                            <?php if ($nextLesson): ?>
                            <a href="<?= url('learn.php?course=' . urlencode($courseSlug) . '&lesson=' . $nextLesson['id']) ?>"
                               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                Next Lesson<i class="fas fa-arrow-right ml-2"></i>
                            </a>
                            <?php else: ?>
                            <div></div>
                            <?php endif; ?>
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
