<?php
/**
 * Take Quiz Page
 * Interface for taking a quiz
 */

require_once '../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

$quizId = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);

if (!$quizId) {
    flash('error', 'Invalid quiz', 'error');
    redirect('my-courses.php');
}

try {
    // Get quiz details with course info
    $quiz = $db->fetchOne("
        SELECT q.*,
               c.title as course_title,
               c.slug as course_slug,
               c.id as course_id
        FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        WHERE q.id = ? AND q.status = 'published'
    ", [$quizId]);

    if (!$quiz) {
        flash('error', 'Quiz not found', 'error');
        redirect('my-courses.php');
    }

    // Verify enrollment
    $enrollment = $db->fetchOne("
        SELECT id FROM enrollments
        WHERE user_id = ? AND course_id = ?
    ", [$userId, $quiz['course_id']]);

    if (!$enrollment) {
        flash('error', 'You must be enrolled in this course to take the quiz', 'error');
        redirect('course.php?slug=' . urlencode($quiz['course_slug']));
    }

    // Check attempt count
    $attemptCount = $db->fetchOne("
        SELECT COUNT(*) as count
        FROM quiz_attempts
        WHERE quiz_id = ? AND user_id = ?
    ", [$quizId, $userId])['count'];

    if ($quiz['max_attempts'] > 0 && $attemptCount >= $quiz['max_attempts']) {
        flash('error', 'You have reached the maximum number of attempts for this quiz', 'error');
        redirect('learn.php?course=' . urlencode($quiz['course_slug']));
    }

    // Get quiz questions with answers
    $questions = $db->fetchAll("
        SELECT q.*, GROUP_CONCAT(CONCAT(a.id, ':', a.answer_text, ':', a.display_order) ORDER BY a.display_order SEPARATOR '||') as answers_data
        FROM quiz_questions q
        LEFT JOIN quiz_answers a ON q.id = a.question_id
        WHERE q.quiz_id = ?
        GROUP BY q.id
        ORDER BY q.display_order ASC
    ", [$quizId]);

    // Process answers data
    foreach ($questions as &$question) {
        $answersData = explode('||', $question['answers_data']);
        $answers = [];
        foreach ($answersData as $answerData) {
            if ($answerData) {
                list($id, $text, $order) = explode(':', $answerData, 3);
                $answers[] = [
                    'id' => $id,
                    'answer_text' => $text,
                    'display_order' => $order
                ];
            }
        }
        $question['answers'] = $answers;
    }

    // Get previous attempts
    $previousAttempts = $db->fetchAll("
        SELECT *
        FROM quiz_attempts
        WHERE quiz_id = ? AND user_id = ?
        ORDER BY created_at DESC
    ", [$quizId, $userId]);

    $page_title = $quiz['title'] . ' - ' . $quiz['course_title'];

} catch (Exception $e) {
    error_log("Take Quiz Error: " . $e->getMessage());
    flash('error', 'An error occurred loading the quiz', 'error');
    redirect('my-courses.php');
}

require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4">

        <!-- Quiz Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <a href="<?= url('learn.php?course=' . urlencode($quiz['course_slug'])) ?>"
                   class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Course
                </a>
                <span class="text-sm text-gray-600">
                    <?= $attemptCount ?> / <?= $quiz['max_attempts'] > 0 ? $quiz['max_attempts'] : '∞' ?> Attempts
                </span>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($quiz['title']) ?></h1>
            <p class="text-gray-600 mb-4"><?= htmlspecialchars($quiz['description']) ?></p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                <div class="text-center">
                    <i class="fas fa-clock text-blue-600 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Time Limit</p>
                    <p class="font-bold"><?= $quiz['time_limit_minutes'] ?> min</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Passing Score</p>
                    <p class="font-bold"><?= $quiz['passing_score'] ?>%</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-question-circle text-purple-600 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Questions</p>
                    <p class="font-bold"><?= count($questions) ?></p>
                </div>
                <div class="text-center">
                    <i class="fas fa-redo text-orange-600 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Attempts Left</p>
                    <p class="font-bold">
                        <?= $quiz['max_attempts'] > 0 ? ($quiz['max_attempts'] - $attemptCount) : '∞' ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Previous Attempts -->
        <?php if (!empty($previousAttempts)): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-history mr-2"></i>Previous Attempts
            </h3>
            <div class="space-y-3">
                <?php foreach ($previousAttempts as $attempt): ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold <?= $attempt['passed'] ? 'text-green-600' : 'text-red-600' ?>">
                                <?= round($attempt['percentage']) ?>%
                            </div>
                            <div class="text-xs text-gray-600">Score</div>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Attempt #<?= $attempt['attempt_number'] ?></p>
                            <p class="text-sm text-gray-600">
                                <?= date('M j, Y g:i A', strtotime($attempt['completed_at'])) ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <?php if ($attempt['passed']): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                            <i class="fas fa-check mr-1"></i>Passed
                        </span>
                        <?php else: ?>
                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                            <i class="fas fa-times mr-1"></i>Failed
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quiz Form -->
        <form method="POST" action="<?= url('actions/submit-quiz.php') ?>" id="quizForm">
            <input type="hidden" name="quiz_id" value="<?= $quizId ?>">
            <input type="hidden" name="start_time" value="<?= time() ?>">
            <?= csrfField() ?>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Quiz Questions</h3>

                <?php foreach ($questions as $index => $question): ?>
                <div class="mb-8 p-6 border-2 border-gray-200 rounded-lg <?= $index < count($questions) - 1 ? 'border-b' : '' ?>">
                    <div class="flex items-start mb-4">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-full font-bold mr-3">
                            <?= $index + 1 ?>
                        </span>
                        <div class="flex-1">
                            <p class="text-lg font-medium text-gray-900 mb-4">
                                <?= htmlspecialchars($question['question_text']) ?>
                                <span class="text-sm text-gray-500 ml-2">(<?= $question['points'] ?> points)</span>
                            </p>

                            <?php if ($question['question_type'] == 'multiple_choice' && !empty($question['answers'])): ?>
                            <div class="space-y-3">
                                <?php foreach ($question['answers'] as $answer): ?>
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition">
                                    <input type="radio"
                                           name="question_<?= $question['id'] ?>"
                                           value="<?= $answer['id'] ?>"
                                           class="mr-3 w-4 h-4"
                                           required>
                                    <span class="text-gray-800"><?= htmlspecialchars($answer['answer_text']) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>

                            <?php elseif ($question['question_type'] == 'true_false'): ?>
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition">
                                    <input type="radio"
                                           name="question_<?= $question['id'] ?>"
                                           value="true"
                                           class="mr-3 w-4 h-4"
                                           required>
                                    <span class="text-gray-800">True</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition">
                                    <input type="radio"
                                           name="question_<?= $question['id'] ?>"
                                           value="false"
                                           class="mr-3 w-4 h-4"
                                           required>
                                    <span class="text-gray-800">False</span>
                                </label>
                            </div>

                            <?php elseif ($question['question_type'] == 'short_answer'): ?>
                            <textarea name="question_<?= $question['id'] ?>"
                                      rows="4"
                                      class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none"
                                      placeholder="Enter your answer here..."
                                      required></textarea>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Submit Button -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Make sure you've answered all questions before submitting
                    </p>
                    <button type="submit"
                            class="px-8 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Quiz
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
// Timer countdown
<?php if ($quiz['time_limit_minutes'] > 0): ?>
let timeLeft = <?= $quiz['time_limit_minutes'] * 60 ?>; // Convert to seconds
const timerDisplay = document.createElement('div');
timerDisplay.className = 'fixed top-4 right-4 bg-white shadow-lg rounded-lg p-4 z-50 border-2 border-orange-500';
timerDisplay.innerHTML = '<div class="text-center"><i class="fas fa-clock text-orange-500 text-xl mb-1"></i><div class="text-sm font-bold text-gray-700">Time Remaining</div><div class="text-2xl font-bold text-orange-500" id="timerValue">--:--</div></div>';
document.body.appendChild(timerDisplay);

function updateTimer() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    document.getElementById('timerValue').textContent =
        minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');

    if (timeLeft <= 300) { // Last 5 minutes
        timerDisplay.classList.add('animate-pulse');
        timerDisplay.querySelector('#timerValue').classList.add('text-red-600');
    }

    if (timeLeft <= 0) {
        alert('Time is up! Your quiz will be submitted automatically.');
        document.getElementById('quizForm').submit();
    }

    timeLeft--;
}

updateTimer();
setInterval(updateTimer, 1000);
<?php endif; ?>

// Confirm before leaving page
window.addEventListener('beforeunload', function (e) {
    e.preventDefault();
    e.returnValue = '';
});

// Remove confirmation on form submit
document.getElementById('quizForm').addEventListener('submit', function() {
    window.removeEventListener('beforeunload', function() {});
});
</script>

<?php require_once '../src/templates/footer.php'; ?>
