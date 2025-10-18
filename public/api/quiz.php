<?php
/**
 * Quiz API
 * Handle quiz operations
 */

require_once '../../src/includes/config.php';
require_once '../../src/includes/database.php';
require_once '../../src/includes/functions.php';
require_once '../../src/includes/auth.php';
require_once '../../src/classes/Quiz.php';
require_once '../../src/classes/Question.php';
require_once '../../src/classes/Enrollment.php';

header('Content-Type: application/json');

// Must be logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// GET - Retrieve quiz data
if ($method === 'GET') {
    $quizId = $_GET['quiz_id'] ?? null;
    $attemptId = $_GET['attempt_id'] ?? null;
    
    if ($attemptId) {
        // Get attempt details
        $db = Database::getInstance();
        $sql = "SELECT * FROM quiz_attempts WHERE id = :id AND user_id = :user_id";
        $attempt = $db->query($sql, ['id' => $attemptId, 'user_id' => $userId])->fetch();
        
        if (!$attempt) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Attempt not found']);
            exit;
        }
        
        // Get answers if completed
        if ($attempt['status'] == 'completed') {
            $sql = "SELECT qa.*, qq.question_text, qq.question_type, qq.options, qq.correct_answer, qq.explanation
                    FROM quiz_answers qa
                    JOIN quiz_questions qq ON qa.question_id = qq.id
                    WHERE qa.attempt_id = :attempt_id";
            $answers = $db->query($sql, ['attempt_id' => $attemptId])->fetchAll();
            $attempt['answers'] = $answers;
        }
        
        echo json_encode(['success' => true, 'attempt' => $attempt]);
        exit;
    }
    
    if ($quizId) {
        // Get quiz details
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Quiz not found']);
            exit;
        }
        
        // Check enrollment
        if (!Enrollment::isEnrolled($userId, $quiz->getCourseId())) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Not enrolled in course']);
            exit;
        }
        
        // Get questions
        $questions = $quiz->getQuestions();
        
        // Hide correct answers until after submission
        foreach ($questions as &$question) {
            unset($question['correct_answer']);
        }
        
        // Get user's attempts
        $attempts = $quiz->getUserAttempts($userId);
        
        echo json_encode([
            'success' => true,
            'quiz' => [
                'id' => $quiz->getId(),
                'title' => $quiz->getTitle(),
                'description' => $quiz->getDescription(),
                'passing_score' => $quiz->getPassingScore(),
                'time_limit' => $quiz->getTimeLimit(),
                'max_attempts' => $quiz->getMaxAttempts(),
                'question_count' => count($questions)
            ],
            'questions' => $questions,
            'attempts' => $attempts,
            'can_attempt' => $quiz->canUserTake($userId),
            'has_passed' => $quiz->hasUserPassed($userId)
        ]);
        exit;
    }
    
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing quiz or attempt ID']);
    exit;
}

// POST - Start or submit quiz
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }
    
    $action = $input['action'] ?? null;
    $quizId = $input['quiz_id'] ?? null;
    
    if (!$quizId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing quiz ID']);
        exit;
    }
    
    $quiz = Quiz::find($quizId);
    if (!$quiz) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Quiz not found']);
        exit;
    }
    
    // Check enrollment
    if (!Enrollment::isEnrolled($userId, $quiz->getCourseId())) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not enrolled in course']);
        exit;
    }
    
    switch ($action) {
        case 'start':
            // Start new attempt
            if (!$quiz->canUserTake($userId)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Maximum attempts reached']);
                exit;
            }
            
            $attemptId = $quiz->startAttempt($userId);
            if ($attemptId) {
                echo json_encode([
                    'success' => true,
                    'attempt_id' => $attemptId,
                    'message' => 'Quiz started'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to start quiz']);
            }
            break;
            
        case 'submit':
            // Submit quiz answers
            $attemptId = $input['attempt_id'] ?? null;
            $answers = $input['answers'] ?? [];
            
            if (!$attemptId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing attempt ID']);
                exit;
            }
            
            $result = $quiz->submitAttempt($attemptId, $answers);
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'result' => $result,
                    'message' => $result['passed'] ? 'Congratulations! You passed!' : 'You did not pass. Please try again.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to submit quiz']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);