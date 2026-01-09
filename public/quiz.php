<?php
/**
 * Quiz Page
 * Shows quiz details and redirects to take-quiz.php
 */

require_once '../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

// Get quiz ID from URL
$quizId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$quizId) {
    flash('error', 'Invalid quiz', 'error');
    redirect('my-courses.php');
}

try {
    // Get quiz details with course info
    $quiz = $db->fetchOne("
        SELECT q.*,
               q.time_limit_minutes as time_limit,
               c.title as course_title,
               c.slug as course_slug,
               c.id as course_id
        FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        WHERE q.id = ?
    ", [$quizId]);

    if (!$quiz) {
        flash('error', 'Quiz not found', 'error');
        redirect('my-courses.php');
    }

    // Check if quiz is published
    if (!$quiz['is_published']) {
        flash('error', 'This quiz is not available yet', 'error');
        redirect('learn.php?course=' . urlencode($quiz['course_slug']));
    }

    // Verify enrollment
    $enrollment = $db->fetchOne("
        SELECT id FROM enrollments
        WHERE user_id = ? AND course_id = ?
    ", [$userId, $quiz['course_id']]);

    if (!$enrollment) {
        flash('error', 'You must be enrolled in this course to access this quiz', 'error');
        redirect('course.php?slug=' . urlencode($quiz['course_slug']));
    }

    // Get attempt statistics
    $attemptStats = $db->fetchOne("
        SELECT COUNT(*) as attempt_count,
               MAX(score) as best_score,
               MAX(percentage) as best_percentage,
               MAX(passed) as has_passed
        FROM quiz_attempts
        WHERE quiz_id = ? AND user_id = ?
    ", [$quizId, $userId]);

    // Get question count
    $questionCount = $db->fetchOne("
        SELECT COUNT(*) as count FROM quiz_questions WHERE quiz_id = ?
    ", [$quizId])['count'];

    // Check if can take quiz
    $canTakeQuiz = true;
    $quizMessage = '';

    if ($quiz['max_attempts'] > 0 && $attemptStats['attempt_count'] >= $quiz['max_attempts']) {
        $canTakeQuiz = false;
        $quizMessage = 'You have reached the maximum number of attempts for this quiz.';
    }

    $page_title = $quiz['title'] . ' - Quiz';
    require_once '../src/templates/header.php';
} catch (Exception $e) {
    error_log("Quiz.php Error: " . $e->getMessage());
    flash('error', 'An error occurred loading the quiz', 'error');
    redirect('my-courses.php');
}
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="<?= url('my-courses.php') ?>" class="hover:text-primary-600">My Courses</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?= url('learn.php?course=' . urlencode($quiz['course_slug'])) ?>" class="hover:text-primary-600"><?= sanitize($quiz['course_title']) ?></a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-gray-900"><?= sanitize($quiz['title']) ?></li>
            </ol>
        </nav>

        <!-- Quiz Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8 text-white">
                <div class="flex items-center mb-4">
                    <i class="fas fa-question-circle text-4xl mr-4"></i>
                    <div>
                        <h1 class="text-2xl font-bold"><?= sanitize($quiz['title']) ?></h1>
                        <p class="text-indigo-100"><?= sanitize($quiz['course_title']) ?></p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <?php if ($quiz['description']): ?>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Description</h2>
                    <p class="text-gray-600"><?= nl2br(sanitize($quiz['description'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Quiz Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900"><?= $questionCount ?></div>
                        <div class="text-sm text-gray-600">Questions</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900"><?= $quiz['time_limit'] ?: '' ?></div>
                        <div class="text-sm text-gray-600">Minutes</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900"><?= $quiz['passing_score'] ?>%</div>
                        <div class="text-sm text-gray-600">Passing Score</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900">
                            <?= $quiz['max_attempts'] > 0 ? $quiz['max_attempts'] : '' ?>
                        </div>
                        <div class="text-sm text-gray-600">Max Attempts</div>
                    </div>
                </div>

                <!-- Previous Attempts -->
                <?php if ($attemptStats['attempt_count'] > 0): ?>
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="font-semibold text-blue-900 mb-2">Your Progress</h3>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-xl font-bold text-blue-600"><?= $attemptStats['attempt_count'] ?></div>
                            <div class="text-xs text-blue-700">Attempts</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold <?= $attemptStats['best_percentage'] >= $quiz['passing_score'] ? 'text-green-600' : 'text-red-600' ?>">
                                <?= round($attemptStats['best_percentage']) ?>%
                            </div>
                            <div class="text-xs text-blue-700">Best Score</div>
                        </div>
                        <div>
                            <?php if ($attemptStats['has_passed']): ?>
                            <div class="text-xl font-bold text-green-600"><i class="fas fa-check-circle"></i></div>
                            <div class="text-xs text-green-700">Passed</div>
                            <?php else: ?>
                            <div class="text-xl font-bold text-orange-600"><i class="fas fa-clock"></i></div>
                            <div class="text-xs text-orange-700">In Progress</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <?php if ($canTakeQuiz): ?>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?= url('take-quiz.php?quiz_id=' . $quizId) ?>"
                       class="flex-1 py-3 px-6 bg-indigo-600 text-white text-center font-semibold rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-play mr-2"></i>
                        <?= $attemptStats['attempt_count'] > 0 ? 'Retry Quiz' : 'Start Quiz' ?>
                    </a>
                    <a href="<?= url('learn.php?course=' . urlencode($quiz['course_slug'])) ?>"
                       class="flex-1 py-3 px-6 bg-gray-200 text-gray-700 text-center font-semibold rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Course
                    </a>
                </div>
                <?php else: ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                        <p class="text-yellow-800"><?= $quizMessage ?></p>
                    </div>
                </div>
                <a href="<?= url('learn.php?course=' . urlencode($quiz['course_slug'])) ?>"
                   class="block w-full py-3 px-6 bg-gray-200 text-gray-700 text-center font-semibold rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Course
                </a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>
