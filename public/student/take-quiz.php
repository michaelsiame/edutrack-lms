<?php
/**
 * Take Quiz Page
 * Interactive quiz taking interface with timer
 */

require_once '../../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

// Get quiz ID
$quizId = $_GET['id'] ?? null;

if (!$quizId) {
    flash('error', 'Quiz not found.', 'error');
    redirect('quizzes.php');
}

// Get quiz details
$quiz = $db->fetchOne("
    SELECT q.*,
           c.title as course_title, c.slug as course_slug,
           e.id as enrollment_id
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
    WHERE q.id = ? AND q.status = 'published'
", [$userId, $quizId]);

if (!$quiz) {
    flash('error', 'Quiz not found or you are not enrolled in this course.', 'error');
    redirect('quizzes.php');
}

// Get quiz questions with their options
$questions = $db->fetchAll("
    SELECT * FROM quiz_questions
    WHERE quiz_id = ?
    ORDER BY order_index ASC, id ASC
", [$quizId]);

// Get options for each question
foreach ($questions as &$question) {
    $question['options'] = $db->fetchAll("
        SELECT * FROM quiz_question_options
        WHERE question_id = ?
        ORDER BY id ASC
    ", [$question['id']]);
}

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    if (!verifyCsrfToken()) {
        flash('error', 'Invalid request. Please try again.', 'error');
        redirect('take-quiz.php?id=' . $quizId);
    }

    $answers = $_POST['answers'] ?? [];
    $timeSpent = intval($_POST['time_spent'] ?? 0);

    $totalQuestions = count($questions);
    $correctAnswers = 0;
    $totalPoints = 0;
    $earnedPoints = 0;

    // Calculate score
    foreach ($questions as $question) {
        $questionId = $question['id'];
        $userAnswer = $answers[$questionId] ?? null;
        $totalPoints += $question['points'];

        // Get correct answer
        $correctOption = $db->fetchOne("
            SELECT id FROM quiz_question_options
            WHERE question_id = ? AND is_correct = 1
        ", [$questionId]);

        if ($correctOption && $userAnswer == $correctOption['id']) {
            $correctAnswers++;
            $earnedPoints += $question['points'];
        }
    }

    $scorePercentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;

    // Save quiz attempt
    try {
        $db->execute("
            INSERT INTO quiz_attempts (user_id, quiz_id, score, total_score, total_questions, correct_answers, time_spent, completed_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", [$userId, $quizId, $scorePercentage, 100, $totalQuestions, $correctAnswers, $timeSpent]);

        $attemptId = $db->lastInsertId();

        // Save individual answers
        foreach ($questions as $question) {
            $questionId = $question['id'];
            $userAnswer = $answers[$questionId] ?? null;

            $correctOption = $db->fetchOne("
                SELECT id FROM quiz_question_options
                WHERE question_id = ? AND is_correct = 1
            ", [$questionId]);

            $isCorrect = $correctOption && $userAnswer == $correctOption['id'];

            $db->execute("
                INSERT INTO quiz_attempt_answers (attempt_id, question_id, selected_option_id, is_correct, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ", [$attemptId, $questionId, $userAnswer, $isCorrect ? 1 : 0]);
        }

        flash('success', 'Quiz submitted successfully!', 'success');
        redirect('quiz-results.php?attempt_id=' . $attemptId);

    } catch (Exception $e) {
        flash('error', 'Failed to submit quiz. Please try again.', 'error');
    }
}

$page_title = $quiz['title'] . " - Take Quiz";
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
        .quiz-container { max-width: 800px; margin: 0 auto; }
        .timer-warning { animation: pulse 1s infinite; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="bg-gray-100">

<!-- Top Bar -->
<div class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <div>
                <h1 class="text-lg font-bold text-gray-900"><?= sanitize($quiz['title']) ?></h1>
                <p class="text-xs text-gray-600"><?= sanitize($quiz['course_title']) ?></p>
            </div>
            <?php if ($quiz['time_limit']): ?>
                <div id="timer" class="flex items-center space-x-2 bg-blue-50 px-4 py-2 rounded-md">
                    <i class="fas fa-clock text-blue-600"></i>
                    <span class="font-bold text-blue-900" id="timer-display"><?= $quiz['time_limit'] ?>:00</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="min-h-screen py-8">
    <div class="quiz-container px-4">

        <!-- Quiz Instructions -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-md mb-6">
            <h2 class="font-bold text-blue-900 mb-3 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                Quiz Instructions
            </h2>
            <ul class="text-blue-800 text-sm space-y-2">
                <li><i class="fas fa-check mr-2"></i>Total Questions: <strong><?= count($questions) ?></strong></li>
                <li><i class="fas fa-check mr-2"></i>Pass Score: <strong><?= $quiz['pass_score'] ?>%</strong></li>
                <?php if ($quiz['time_limit']): ?>
                    <li><i class="fas fa-check mr-2"></i>Time Limit: <strong><?= $quiz['time_limit'] ?> minutes</strong></li>
                <?php endif; ?>
                <li><i class="fas fa-check mr-2"></i>You can only select one answer per question</li>
                <?php if ($quiz['time_limit']): ?>
                    <li class="text-red-700"><i class="fas fa-exclamation-triangle mr-2"></i>The quiz will auto-submit when time expires</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Quiz Form -->
        <form id="quiz-form" method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="time_spent" id="time-spent-input" value="0">

            <?php foreach ($questions as $index => $question): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <!-- Question Number & Text -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-primary-600">Question <?= $index + 1 ?> of <?= count($questions) ?></span>
                            <span class="text-sm text-gray-600"><?= $question['points'] ?> point<?= $question['points'] > 1 ? 's' : '' ?></span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900"><?= sanitize($question['question_text']) ?></h3>
                    </div>

                    <!-- Options -->
                    <div class="space-y-3">
                        <?php foreach ($question['options'] as $option): ?>
                            <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-300 hover:bg-primary-50 transition">
                                <input type="radio"
                                       name="answers[<?= $question['id'] ?>]"
                                       value="<?= $option['id'] ?>"
                                       class="mt-1 w-4 h-4 text-primary-600 focus:ring-primary-500"
                                       required>
                                <span class="ml-3 text-gray-700"><?= sanitize($option['option_text']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Submit Button -->
            <div class="bg-white rounded-lg shadow-md p-6 sticky bottom-0">
                <div class="flex items-center justify-between">
                    <button type="button"
                            onclick="if(confirm('Are you sure you want to exit? Your progress will be lost.')) window.location.href='<?= url('student/quizzes.php') ?>'"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition font-medium">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit"
                            name="submit_quiz"
                            id="submit-btn"
                            class="px-8 py-3 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition font-medium">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Quiz
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Timer functionality
<?php if ($quiz['time_limit']): ?>
let timeLimit = <?= $quiz['time_limit'] * 60 ?>; // Convert to seconds
let timeRemaining = timeLimit;
let timerInterval;

function updateTimer() {
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    const display = `${minutes}:${seconds.toString().padStart(2, '0')}`;

    document.getElementById('timer-display').textContent = display;
    document.getElementById('time-spent-input').value = timeLimit - timeRemaining;

    // Warning when less than 5 minutes
    if (timeRemaining <= 300) {
        document.getElementById('timer').classList.add('bg-red-100', 'timer-warning');
        document.getElementById('timer').classList.remove('bg-blue-50');
        document.querySelector('#timer i').classList.add('text-red-600');
        document.querySelector('#timer i').classList.remove('text-blue-600');
        document.getElementById('timer-display').classList.add('text-red-900');
        document.getElementById('timer-display').classList.remove('text-blue-900');
    }

    if (timeRemaining <= 0) {
        clearInterval(timerInterval);
        alert('Time is up! The quiz will be submitted automatically.');
        document.getElementById('quiz-form').submit();
    }

    timeRemaining--;
}

// Start timer
timerInterval = setInterval(updateTimer, 1000);
updateTimer();
<?php endif; ?>

// Prevent accidental page exit
window.addEventListener('beforeunload', function (e) {
    e.preventDefault();
    e.returnValue = '';
});

// Remove warning when submitting
document.getElementById('quiz-form').addEventListener('submit', function() {
    window.removeEventListener('beforeunload', arguments.callee);
    <?php if ($quiz['time_limit']): ?>
    clearInterval(timerInterval);
    <?php endif; ?>
});

// Track answer selection
document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove previous selection styling
        this.closest('.space-y-3').querySelectorAll('label').forEach(label => {
            label.classList.remove('border-primary-500', 'bg-primary-50');
            label.classList.add('border-gray-200');
        });

        // Add selection styling
        this.closest('label').classList.add('border-primary-500', 'bg-primary-50');
        this.closest('label').classList.remove('border-gray-200');
    });
});
</script>

</body>
</html>
