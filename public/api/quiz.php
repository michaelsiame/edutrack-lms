<?php
/**
 * API: Quiz Management
 * GET /api/quiz.php - Get quiz details
 * POST /api/quiz.php - Submit quiz attempt
 */

header('Content-Type: application/json');

require_once '../../src/includes/config.php';
require_once '../../src/includes/database.php';
require_once '../../src/includes/functions.php';
require_once '../../src/classes/Quiz.php';
require_once '../../src/classes/Question.php';

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

// GET: Retrieve quiz
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $quizId = $_GET['quiz_id'] ?? null;
    
    if (!$quizId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Quiz ID is required']);
        exit;
    }
    
    $quiz = Quiz::find($quizId);
    
    if (!$quiz || !$quiz->exists()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Quiz not found']);
        exit;
    }
    
    // Verify enrollment
    require_once '../../src/classes/Enrollment.php';
    if (!Enrollment::isEnrolled($userId, $quiz->getCourseId())) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not enrolled in this course']);
        exit;
    }
    
    // Get questions
    $questions = Question::getByQuiz($quizId);
    
    // Remove correct answers from questions
    $questionsForDisplay = array_map(function($q) {
        unset($q['correct_answer']);
        return $q;
    }, $questions);
    
    // Get user's previous attempts
    $attempts = $quiz->getUserAttempts($userId);
    
    // Check if user can attempt
    $canAttempt = true;
    $attemptsLeft = null;
    
    if ($quiz->getMaxAttempts() > 0) {
        $attemptsLeft = $quiz->getMaxAttempts() - count($attempts);
        $canAttempt = $attemptsLeft > 0;
    }
    
    echo json_encode([
        'success' => true,
        'quiz' => [
            'id' => $quiz->getId(),
            'title' => $quiz->getTitle(),
            'description' => $quiz->getDescription(),
            'time_limit' => $quiz->getTimeLimit(),
            'passing_score' => $quiz->getPassingScore(),
            'max_attempts' => $quiz->getMaxAttempts(),
            'total_questions' => count($questions),
            'total_points' => array_sum(array_column($questions, 'points'))
        ],
        'questions' => $questionsForDisplay,
        'attempts' => $attempts,
        'can_attempt' => $canAttempt,
        'attempts_left' => $attemptsLeft
    ]);
    exit;
}

// POST: Submit quiz attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $quizId = $input['quiz_id'] ?? null;
    $answers = $input['answers'] ?? [];
    
    if (!$quizId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Quiz ID is required']);
        exit;
    }
    
    if (empty($answers)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No answers provided']);
        exit;
    }
    
    $quiz = Quiz::find($quizId);
    
    if (!$quiz || !$quiz->exists()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Quiz not found']);
        exit;
    }
    
    // Verify enrollment
    require_once '../../src/classes/Enrollment.php';
    if (!Enrollment::isEnrolled($userId, $quiz->getCourseId())) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not enrolled in this course']);
        exit;
    }
    
    // Check if user can attempt
    $attempts = $quiz->getUserAttempts($userId);
    
    if ($quiz->getMaxAttempts() > 0 && count($attempts) >= $quiz->getMaxAttempts()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Maximum attempts reached']);
        exit;
    }
    
    // Get questions
    $questions = Question::getByQuiz($quizId);
    
    // Calculate score
    $totalPoints = 0;
    $earnedPoints = 0;
    $correctAnswers = 0;
    $results = [];
    
    foreach ($questions as $question) {
        $questionId = $question['id'];
        $points = $question['points'];
        $totalPoints += $points;
        
        $userAnswer = $answers[$questionId] ?? null;
        $correctAnswer = $question['correct_answer'];
        $isCorrect = false;
        
        if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'true_false') {
            $isCorrect = $userAnswer === $correctAnswer;
        } elseif ($question['question_type'] === 'short_answer') {
            // Case-insensitive comparison, trim whitespace
            $isCorrect = strcasecmp(trim($userAnswer), trim($correctAnswer)) === 0;
        }
        
        if ($isCorrect) {
            $earnedPoints += $points;
            $correctAnswers++;
        }
        
        $results[] = [
            'question_id' => $questionId,
            'question' => $question['question_text'],
            'user_answer' => $userAnswer,
            'correct_answer' => $correctAnswer,
            'is_correct' => $isCorrect,
            'points' => $points,
            'earned_points' => $isCorrect ? $points : 0
        ];
    }
    
    $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
    $passed = $score >= $quiz->getPassingScore();
    
    // Save attempt
    $attemptId = $quiz->saveAttempt([
        'user_id' => $userId,
        'score' => $score,
        'total_points' => $totalPoints,
        'earned_points' => $earnedPoints,
        'answers' => json_encode($answers),
        'results' => json_encode($results),
        'passed' => $passed ? 1 : 0
    ]);
    
    if (!$attemptId) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save quiz attempt']);
        exit;
    }
    
    // Update course progress if quiz passed
    if ($passed && $quiz->getLessonId()) {
        require_once '../../src/classes/Progress.php';
        $progress = new Progress();
        $progress->completeLesson($userId, $quiz->getLessonId());
    }
    
    echo json_encode([
        'success' => true,
        'attempt_id' => $attemptId,
        'score' => $score,
        'total_points' => $totalPoints,
        'earned_points' => $earnedPoints,
        'correct_answers' => $correctAnswers,
        'total_questions' => count($questions),
        'passed' => $passed,
        'passing_score' => $quiz->getPassingScore(),
        'results' => $results,
        'message' => $passed ? 'Congratulations! You passed the quiz.' : 'You did not pass. Please try again.'
    ]);
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);