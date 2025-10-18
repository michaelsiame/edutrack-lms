<?php
/**
 * Progress API
 * Handle progress tracking requests
 */

require_once '../../src/includes/config.php';
require_once '../../src/includes/database.php';
require_once '../../src/includes/functions.php';
require_once '../../src/includes/auth.php';
require_once '../../src/classes/Progress.php';
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

// GET - Retrieve progress
if ($method === 'GET') {
    $courseId = $_GET['course_id'] ?? null;
    $lessonId = $_GET['lesson_id'] ?? null;
    
    $progress = new Progress();
    
    if ($lessonId) {
        // Get lesson progress
        $data = $progress->getLessonProgress($userId, $lessonId);
        echo json_encode(['success' => true, 'progress' => $data]);
    } elseif ($courseId) {
        // Get course progress
        $data = $progress->getCourseProgress($userId, $courseId);
        echo json_encode(['success' => true, 'progress' => $data]);
    } else {
        // Get user stats
        $data = $progress->getUserStats($userId);
        echo json_encode(['success' => true, 'stats' => $data]);
    }
    exit;
}

// POST - Update progress
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }
    
    $action = $input['action'] ?? null;
    $courseId = $input['course_id'] ?? null;
    $lessonId = $input['lesson_id'] ?? null;
    
    if (!$courseId || !$lessonId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing course or lesson ID']);
        exit;
    }
    
    // Verify enrollment
    if (!Enrollment::isEnrolled($userId, $courseId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not enrolled in course']);
        exit;
    }
    
    $progress = new Progress();
    
    switch ($action) {
        case 'mark_complete':
            // Mark lesson as complete
            $result = $progress->markLessonComplete($userId, $courseId, $lessonId);
            
            if ($result) {
                // Get updated progress
                $courseProgress = $progress->getCourseProgress($userId, $courseId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Lesson marked as complete',
                    'progress_percentage' => $courseProgress['progress_percentage'] ?? 0
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to mark complete']);
            }
            break;
            
        case 'update_progress':
            // Update video progress
            $progressSeconds = $input['progress_seconds'] ?? 0;
            $lastPosition = $input['last_position'] ?? 0;
            
            $data = [
                'progress_seconds' => $progressSeconds,
                'last_position' => $lastPosition,
                'completed' => 0
            ];
            
            $result = $progress->updateLessonProgress($userId, $courseId, $lessonId, $data);
            
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Progress updated' : 'Failed to update progress'
            ]);
            break;
            
        case 'add_time':
            // Add time spent
            $seconds = $input['seconds'] ?? 0;
            $result = $progress->addTimeSpent($userId, $courseId, $seconds);
            
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Time tracked' : 'Failed to track time'
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);