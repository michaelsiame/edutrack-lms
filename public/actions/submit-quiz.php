<?php
/**
 * Submit Quiz Action
 * Handles quiz submission and grading
 */

require_once '../../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to submit quizzes', 'error');
    redirect('login.php');
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('my-courses.php');
}

// Validate CSRF token
try {
    requireCsrfToken();
} catch (Exception $e) {
    flash('error', 'Invalid security token. Please try again.', 'error');
    redirect('my-courses.php');
}

try {
    $user = User::current();
    $userId = $user->getId();

    $quizId = filter_input(INPUT_POST, 'quiz_id', FILTER_VALIDATE_INT);
    $startTime = filter_input(INPUT_POST, 'start_time', FILTER_VALIDATE_INT);

    if (!$quizId) {
        flash('error', 'Invalid quiz', 'error');
        redirect('my-courses.php');
    }

    // Get quiz details
    $quiz = $db->fetchOne("
        SELECT q.*, c.slug as course_slug
        FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        WHERE q.id = ?
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
        flash('error', 'You are not enrolled in this course', 'error');
        redirect('my-courses.php');
    }

    // Check attempt limit
    $attemptCount = $db->fetchOne("
        SELECT COUNT(*) as count
        FROM quiz_attempts
        WHERE quiz_id = ? AND user_id = ?
    ", [$quizId, $userId])['count'];

    $attemptNumber = $attemptCount + 1;

    if ($quiz['max_attempts'] > 0 && $attemptCount >= $quiz['max_attempts']) {
        flash('error', 'You have reached the maximum number of attempts', 'error');
        redirect('learn.php?course=' . urlencode($quiz['course_slug']));
    }

    // Get all quiz questions with correct answers
    $questions = $db->fetchAll("
        SELECT q.*, a.id as answer_id, a.is_correct
        FROM quiz_questions q
        LEFT JOIN quiz_answers a ON q.id = a.question_id
        WHERE q.quiz_id = ?
        ORDER BY q.display_order ASC
    ", [$quizId]);

    // Group questions and answers
    $questionsMap = [];
    foreach ($questions as $row) {
        if (!isset($questionsMap[$row['id']])) {
            $questionsMap[$row['id']] = [
                'id' => $row['id'],
                'question_text' => $row['question_text'],
                'question_type' => $row['question_type'],
                'points' => $row['points'],
                'answers' => []
            ];
        }
        if ($row['answer_id']) {
            $questionsMap[$row['id']]['answers'][$row['answer_id']] = $row['is_correct'];
        }
    }

    // Calculate score
    $totalPoints = 0;
    $earnedPoints = 0;
    $responses = [];

    foreach ($questionsMap as $questionId => $questionData) {
        $totalPoints += $questionData['points'];
        $userAnswer = $_POST['question_' . $questionId] ?? null;

        $isCorrect = false;
        $pointsEarned = 0;

        if ($userAnswer) {
            if ($questionData['question_type'] == 'multiple_choice') {
                // Check if selected answer is correct
                $isCorrect = $questionData['answers'][$userAnswer] ?? false;
                if ($isCorrect) {
                    $pointsEarned = $questionData['points'];
                    $earnedPoints += $pointsEarned;
                }

                $responses[] = [
                    'question_id' => $questionId,
                    'answer_id' => $userAnswer,
                    'answer_text' => null,
                    'is_correct' => $isCorrect ? 1 : 0,
                    'points_earned' => $pointsEarned
                ];
            } elseif ($questionData['question_type'] == 'true_false') {
                // For true/false, check against correct answer
                // Note: You'll need to adjust this based on how true/false answers are stored
                $responses[] = [
                    'question_id' => $questionId,
                    'answer_id' => null,
                    'answer_text' => $userAnswer,
                    'is_correct' => 0, // Manual grading needed
                    'points_earned' => 0
                ];
            } elseif ($questionData['question_type'] == 'short_answer') {
                // Short answers need manual grading
                $responses[] = [
                    'question_id' => $questionId,
                    'answer_id' => null,
                    'answer_text' => $userAnswer,
                    'is_correct' => 0,
                    'points_earned' => 0
                ];
            }
        }
    }

    // Calculate percentage
    $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
    $passed = $percentage >= $quiz['passing_score'];

    // Calculate time spent
    $timeSpent = $startTime ? (time() - $startTime) : 0;

    // Create quiz attempt record
    $db->query("
        INSERT INTO quiz_attempts
        (user_id, quiz_id, course_id, attempt_number, score, total_points, percentage, status, time_spent, started_at, completed_at, passed)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'completed', ?, FROM_UNIXTIME(?), NOW(), ?)
    ", [
        $userId,
        $quizId,
        $quiz['course_id'],
        $attemptNumber,
        $earnedPoints,
        $totalPoints,
        $percentage,
        $timeSpent,
        $startTime,
        $passed ? 1 : 0
    ]);

    $attemptId = $db->lastInsertId();

    // Save individual responses
    foreach ($responses as $response) {
        $db->query("
            INSERT INTO quiz_responses
            (attempt_id, question_id, answer_id, answer_text, is_correct, points_earned)
            VALUES (?, ?, ?, ?, ?, ?)
        ", [
            $attemptId,
            $response['question_id'],
            $response['answer_id'],
            $response['answer_text'],
            $response['is_correct'],
            $response['points_earned']
        ]);
    }

    // Show result message
    if ($passed) {
        flash('success', sprintf('Congratulations! You passed with a score of %.1f%% 🎉', $percentage), 'success');
    } else {
        flash('warning', sprintf('You scored %.1f%%. You need %.1f%% to pass. Keep studying and try again!', $percentage, $quiz['passing_score']), 'warning');
    }

    redirect('quiz-result.php?attempt_id=' . $attemptId);

} catch (Exception $e) {
    error_log("Quiz Submission Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    flash('error', 'An error occurred while submitting your quiz. Please try again.', 'error');
    redirect('my-courses.php');
}
