<?php
/**
 * Quiz Result Page
 * Display quiz attempt results
 */

require_once '../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

$attemptId = filter_input(INPUT_GET, 'attempt_id', FILTER_VALIDATE_INT);

if (!$attemptId) {
    flash('error', 'Invalid quiz attempt', 'error');
    redirect('my-courses.php');
}

try {
    // Get attempt details
    $attempt = $db->fetchOne("
        SELECT qa.*,
               q.title as quiz_title,
               q.passing_score,
               c.title as course_title,
               c.slug as course_slug
        FROM quiz_attempts qa
        JOIN quizzes q ON qa.quiz_id = q.id
        JOIN courses c ON q.course_id = c.id
        WHERE qa.id = ? AND qa.user_id = ?
    ", [$attemptId, $userId]);

    if (!$attempt) {
        flash('error', 'Quiz attempt not found', 'error');
        redirect('my-courses.php');
    }

    // Get responses with questions
    $responses = $db->fetchAll("
        SELECT qr.*,
               qq.question_text,
               qq.question_type,
               qq.points as question_points,
               qa_user.answer_text as user_answer_text,
               qa_correct.answer_text as correct_answer_text
        FROM quiz_responses qr
        JOIN quiz_questions qq ON qr.question_id = qq.id
        LEFT JOIN quiz_answers qa_user ON qr.answer_id = qa_user.id
        LEFT JOIN quiz_answers qa_correct ON qq.id = qa_correct.question_id AND qa_correct.is_correct = 1
        WHERE qr.attempt_id = ?
        ORDER BY qq.display_order ASC
    ", [$attemptId]);

    $page_title = 'Quiz Results - ' . $attempt['quiz_title'];

} catch (Exception $e) {
    error_log("Quiz Result Error: " . $e->getMessage());
    flash('error', 'An error occurred loading quiz results', 'error');
    redirect('my-courses.php');
}

require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4">

        <!-- Result Header -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-6">
            <div class="text-center mb-6">
                <div class="inline-block p-6 rounded-full <?= $attempt['passed'] ? 'bg-green-100' : 'bg-red-100' ?> mb-4">
                    <i class="fas <?= $attempt['passed'] ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' ?> text-6xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <?= $attempt['passed'] ? 'Congratulations! You Passed!' : 'Keep Trying!' ?>
                </h1>
                <p class="text-gray-600"><?= htmlspecialchars($attempt['quiz_title']) ?></p>
            </div>

            <!-- Score Display -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6 bg-gray-50 rounded-lg">
                <div class="text-center">
                    <div class="text-4xl font-bold <?= $attempt['passed'] ? 'text-green-600' : 'text-red-600' ?> mb-2">
                        <?= round($attempt['percentage']) ?>%
                    </div>
                    <p class="text-sm text-gray-600">Your Score</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-blue-600 mb-2">
                        <?= round($attempt['score']) ?>/<?= round($attempt['total_points']) ?>
                    </div>
                    <p class="text-sm text-gray-600">Points Earned</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-purple-600 mb-2">
                        <?= $attempt['passing_score'] ?>%
                    </div>
                    <p class="text-sm text-gray-600">Passing Score</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-orange-600 mb-2">
                        <?= gmdate("i:s", $attempt['time_spent']) ?>
                    </div>
                    <p class="text-sm text-gray-600">Time Taken</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center space-x-4 mt-6">
                <a href="<?= url('learn.php?course=' . urlencode($attempt['course_slug'])) ?>"
                   class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Course
                </a>
                <?php if (!$attempt['passed']): ?>
                <a href="<?= url('take-quiz.php?quiz_id=' . $attempt['quiz_id']) ?>"
                   class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-redo mr-2"></i>Try Again
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Question Review -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-list-check mr-2"></i>Question Review
            </h2>

            <div class="space-y-6">
                <?php foreach ($responses as $index => $response): ?>
                <div class="p-6 border-2 <?= $response['is_correct'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' ?> rounded-lg">
                    <!-- Question Number and Result -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start">
                            <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center <?= $response['is_correct'] ? 'bg-green-600' : 'bg-red-600' ?> text-white rounded-full font-bold mr-3">
                                <?= $index + 1 ?>
                            </span>
                            <div>
                                <p class="text-lg font-medium text-gray-900 mb-2">
                                    <?= htmlspecialchars($response['question_text']) ?>
                                </p>
                                <span class="text-sm font-medium <?= $response['is_correct'] ? 'text-green-700' : 'text-red-700' ?>">
                                    <?= $response['points_earned'] ?> / <?= $response['question_points'] ?> points
                                </span>
                            </div>
                        </div>
                        <div>
                            <?php if ($response['is_correct']): ?>
                            <span class="px-3 py-1 bg-green-600 text-white rounded-full text-sm font-bold">
                                <i class="fas fa-check mr-1"></i>Correct
                            </span>
                            <?php else: ?>
                            <span class="px-3 py-1 bg-red-600 text-white rounded-full text-sm font-bold">
                                <i class="fas fa-times mr-1"></i>Incorrect
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Your Answer -->
                    <div class="ml-11 space-y-3">
                        <div class="p-4 bg-white rounded-lg border-2 border-gray-200">
                            <p class="text-sm font-medium text-gray-600 mb-1">Your Answer:</p>
                            <p class="text-gray-900">
                                <?= htmlspecialchars($response['user_answer_text'] ?: $response['answer_text'] ?: 'No answer provided') ?>
                            </p>
                        </div>

                        <!-- Correct Answer (if wrong) -->
                        <?php if (!$response['is_correct'] && $response['correct_answer_text']): ?>
                        <div class="p-4 bg-green-100 rounded-lg border-2 border-green-300">
                            <p class="text-sm font-medium text-green-800 mb-1">Correct Answer:</p>
                            <p class="text-green-900 font-medium">
                                <?= htmlspecialchars($response['correct_answer_text']) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Summary and Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <div class="text-center">
                <?php if ($attempt['passed']): ?>
                <div class="mb-4">
                    <i class="fas fa-trophy text-yellow-500 text-5xl mb-2"></i>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Excellent Work!</h3>
                    <p class="text-gray-600 mb-4">
                        You've successfully passed this quiz. Your achievement has been recorded.
                    </p>
                </div>
                <?php else: ?>
                <div class="mb-4">
                    <i class="fas fa-book-reader text-blue-500 text-5xl mb-2"></i>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Don't Give Up!</h3>
                    <p class="text-gray-600 mb-4">
                        Review the course materials and try again. You can do it!
                    </p>
                </div>
                <?php endif; ?>

                <div class="flex justify-center space-x-4">
                    <a href="<?= url('learn.php?course=' . urlencode($attempt['course_slug'])) ?>"
                       class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-book mr-2"></i>Continue Learning
                    </a>
                    <?php if (!$attempt['passed']): ?>
                    <a href="<?= url('take-quiz.php?quiz_id=' . $attempt['quiz_id']) ?>"
                       class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-redo mr-2"></i>Retake Quiz
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>
