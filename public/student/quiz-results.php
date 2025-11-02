<?php
/**
 * Quiz Results Page
 * Display quiz attempt results with detailed feedback
 */

require_once '../../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

// Get parameters
$attemptId = $_GET['attempt_id'] ?? null;
$quizId = $_GET['quiz_id'] ?? null;

if (!$attemptId && !$quizId) {
    flash('error', 'Invalid request.', 'error');
    redirect('quizzes.php');
}

// If quiz_id provided, get latest attempt
if ($quizId && !$attemptId) {
    $attempt = $db->fetchOne("
        SELECT * FROM quiz_attempts
        WHERE user_id = ? AND quiz_id = ?
        ORDER BY completed_at DESC
        LIMIT 1
    ", [$userId, $quizId]);

    if ($attempt) {
        $attemptId = $attempt['id'];
    } else {
        flash('error', 'No attempts found for this quiz.', 'error');
        redirect('quizzes.php');
    }
}

// Get attempt details
$attempt = $db->fetchOne("
    SELECT qa.*,
           q.title as quiz_title, q.pass_score, q.time_limit,
           c.title as course_title, c.slug as course_slug
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN courses c ON q.course_id = c.id
    WHERE qa.id = ? AND qa.user_id = ?
", [$attemptId, $userId]);

if (!$attempt) {
    flash('error', 'Quiz attempt not found.', 'error');
    redirect('quizzes.php');
}

$quizId = $attempt['quiz_id'];

// Get all attempts for this quiz
$allAttempts = $db->fetchAll("
    SELECT * FROM quiz_attempts
    WHERE user_id = ? AND quiz_id = ?
    ORDER BY completed_at DESC
", [$userId, $quizId]);

// Get questions with answers
$questions = $db->fetchAll("
    SELECT qq.*,
           qaa.selected_option_id,
           qaa.is_correct
    FROM quiz_questions qq
    LEFT JOIN quiz_attempt_answers qaa ON qq.id = qaa.question_id AND qaa.attempt_id = ?
    WHERE qq.quiz_id = ?
    ORDER BY qq.order_index ASC, qq.id ASC
", [$attemptId, $quizId]);

// Get options for each question
foreach ($questions as &$question) {
    $question['options'] = $db->fetchAll("
        SELECT * FROM quiz_question_options
        WHERE question_id = ?
        ORDER BY id ASC
    ", [$question['id']]);
}

$passed = $attempt['score'] >= $attempt['pass_score'];
$scoreClass = $passed ? 'text-green-600' : 'text-red-600';

$page_title = "Quiz Results - " . $attempt['quiz_title'];
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?= url('student/quizzes.php') ?>" class="text-primary-600 hover:text-primary-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Quizzes
            </a>
        </div>

        <!-- Results Summary -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="bg-gradient-to-r <?= $passed ? 'from-green-500 to-green-600' : 'from-red-500 to-red-600' ?> p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-2"><?= sanitize($attempt['quiz_title']) ?></h1>
                        <p class="text-sm opacity-90"><?= sanitize($attempt['course_title']) ?></p>
                    </div>
                    <div class="text-center">
                        <div class="text-5xl font-bold mb-2">
                            <?= round($attempt['score']) ?>%
                        </div>
                        <div class="text-sm font-semibold uppercase">
                            <?= $passed ? 'Passed' : 'Failed' ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-1">Score</p>
                        <p class="text-2xl font-bold <?= $scoreClass ?>">
                            <?= $attempt['correct_answers'] ?>/<?= $attempt['total_questions'] ?>
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-1">Pass Score</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $attempt['pass_score'] ?>%</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-1">Time Taken</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?= floor($attempt['time_spent'] / 60) ?>:<?= str_pad($attempt['time_spent'] % 60, 2, '0', STR_PAD_LEFT) ?>
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-1">Completed</p>
                        <p class="text-sm font-semibold text-gray-900"><?= formatDate($attempt['completed_at']) ?></p>
                    </div>
                </div>

                <!-- Status Message -->
                <div class="bg-<?= $passed ? 'green' : 'red' ?>-50 border-l-4 border-<?= $passed ? 'green' : 'red' ?>-500 p-4 rounded-md">
                    <div class="flex items-center">
                        <i class="fas fa-<?= $passed ? 'check-circle' : 'times-circle' ?> text-<?= $passed ? 'green' : 'red' ?>-600 text-2xl mr-3"></i>
                        <div>
                            <p class="font-semibold text-<?= $passed ? 'green' : 'red' ?>-900">
                                <?= $passed ? 'Congratulations! You passed this quiz.' : 'You did not pass this quiz.' ?>
                            </p>
                            <p class="text-sm text-<?= $passed ? 'green' : 'red' ?>-800 mt-1">
                                <?= $passed ? 'You have successfully demonstrated your understanding of the material.' : 'You need to score at least ' . $attempt['pass_score'] . '% to pass. Review the material and try again.' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="<?= url('student/take-quiz.php?id=' . $quizId) ?>"
                       class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition font-medium">
                        <i class="fas fa-redo mr-2"></i>Retake Quiz
                    </a>
                    <a href="<?= url('learn.php?course=' . $attempt['course_slug']) ?>"
                       class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition font-medium">
                        <i class="fas fa-book mr-2"></i>Back to Course
                    </a>
                </div>
            </div>
        </div>

        <!-- Attempt History -->
        <?php if (count($allAttempts) > 1): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-history text-primary-600 mr-2"></i>
                Attempt History
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attempt</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($allAttempts as $index => $att): ?>
                            <?php
                            $attPassed = $att['score'] >= $att['pass_score'];
                            $isCurrent = $att['id'] == $attemptId;
                            ?>
                            <tr class="<?= $isCurrent ? 'bg-blue-50' : '' ?>">
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    Attempt #<?= count($allAttempts) - $index ?>
                                    <?= $isCurrent ? '<span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Current</span>' : '' ?>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold <?= $attPassed ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= round($att['score']) ?>%
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 <?= $attPassed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> text-xs font-semibold rounded-full">
                                        <?= $attPassed ? 'Passed' : 'Failed' ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?= formatDate($att['completed_at']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <?php if (!$isCurrent): ?>
                                        <a href="?attempt_id=<?= $att['id'] ?>" class="text-primary-600 hover:text-primary-700">
                                            View
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Detailed Review -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-clipboard-list text-primary-600 mr-2"></i>
                Detailed Review
            </h2>

            <div class="space-y-6">
                <?php foreach ($questions as $index => $question): ?>
                    <?php
                    $isCorrect = $question['is_correct'];
                    $borderColor = $isCorrect ? 'border-green-500' : 'border-red-500';
                    $bgColor = $isCorrect ? 'bg-green-50' : 'bg-red-50';
                    ?>
                    <div class="border-l-4 <?= $borderColor ?> <?= $bgColor ?> p-6 rounded-r-lg">
                        <!-- Question Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <span class="text-sm font-semibold text-gray-600">Question <?= $index + 1 ?></span>
                                    <span class="px-2 py-1 <?= $isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> text-xs font-semibold rounded-full">
                                        <i class="fas fa-<?= $isCorrect ? 'check' : 'times' ?> mr-1"></i>
                                        <?= $isCorrect ? 'Correct' : 'Incorrect' ?>
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900"><?= sanitize($question['question_text']) ?></h3>
                            </div>
                            <span class="text-sm text-gray-600 ml-4"><?= $question['points'] ?> pt<?= $question['points'] > 1 ? 's' : '' ?></span>
                        </div>

                        <!-- Options -->
                        <div class="space-y-2">
                            <?php foreach ($question['options'] as $option): ?>
                                <?php
                                $isCorrectOption = $option['is_correct'];
                                $isSelected = $option['id'] == $question['selected_option_id'];

                                $optionClass = '';
                                $icon = '';

                                if ($isCorrectOption) {
                                    $optionClass = 'bg-green-100 border-green-500 text-green-900';
                                    $icon = '<i class="fas fa-check-circle text-green-600 mr-2"></i>';
                                } elseif ($isSelected && !$isCorrectOption) {
                                    $optionClass = 'bg-red-100 border-red-500 text-red-900';
                                    $icon = '<i class="fas fa-times-circle text-red-600 mr-2"></i>';
                                } else {
                                    $optionClass = 'bg-white border-gray-200 text-gray-700';
                                }
                                ?>
                                <div class="flex items-center p-3 border-2 <?= $optionClass ?> rounded-md">
                                    <?= $icon ?>
                                    <span><?= sanitize($option['option_text']) ?></span>
                                    <?php if ($isCorrectOption): ?>
                                        <span class="ml-auto text-xs font-semibold text-green-700">Correct Answer</span>
                                    <?php elseif ($isSelected): ?>
                                        <span class="ml-auto text-xs font-semibold text-red-700">Your Answer</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Explanation -->
                        <?php if ($question['explanation']): ?>
                            <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-md">
                                <p class="text-sm font-semibold text-blue-900 mb-1">
                                    <i class="fas fa-lightbulb mr-1"></i>Explanation
                                </p>
                                <p class="text-sm text-blue-800"><?= sanitize($question['explanation']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../src/templates/footer.php'; ?>
