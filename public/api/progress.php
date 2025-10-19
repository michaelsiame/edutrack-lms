<?php
/**
 * API: Progress Tracking
 * GET /api/progress.php - Get progress
 * POST /api/progress.php - Update progress
 */

header('Content-Type: application/json');

require_once '../../src/includes/config.php';
require_once '../../src/includes/database.php';
require_once '../../src/includes/functions.php';
require_once '../../src/classes/Progress.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Lesson.php';

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$progress = new Progress();
$userId = $_SESSION['user_id'];

// GET: Retrieve progress
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $courseId = $_GET['course_id'] ?? null;
    $lessonId = $_GET['lesson_id'] ?? null;
    
    if ($courseId) {
        // Get course progress
        $courseProgress = $progress->getCourseProgress($userId, $courseId);
        $lessonsProgress = $progress->getCourseLessonsProgress($userId, $courseId);
        
        echo json_encode([
            'success' => true,
            'course_progress' => $courseProgress,
            'lessons_progress' => $lessonsProgress
        ]);
        exit;
    }
    
    if ($lessonId) {
        // Get lesson progress
        $lessonProgress = $progress->getLessonProgress($userId, $lessonId);
        
        echo json_encode([
            'success' => true,
            'lesson_progress' => $lessonProgress
        ]);
        exit;
    }
    
    // Get all user progress
    $userProgress = $progress->getUserProgress($userId);
    
    echo json_encode([
        'success' => true,
        'progress' => $userProgress
    ]);
    exit;
}

// POST: Update progress
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $action = $input['action'] ?? null;
    $lessonId = $input['lesson_id'] ?? null;
    $courseId = $input['course_id'] ?? null;
    
    if (!$lessonId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Lesson ID is required']);
        exit;
    }
    
    // Verify lesson exists
    $lesson = Lesson::find($lessonId);
    if (!$lesson) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Lesson not found']);
        exit;
    }
    
    // Verify user is enrolled
    require_once '../../src/classes/Enrollment.php';
    if (!Enrollment::isEnrolled($userId, $lesson->getCourseId())) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not enrolled in this course']);
        exit;
    }
    
    $result = false;
    
    switch ($action) {
        case 'start':
            // Mark lesson as started
            $result = $progress->startLesson($userId, $lessonId);
            $message = 'Lesson started';
            break;
            
        case 'complete':
            // Mark lesson as completed
            $result = $progress->completeLesson($userId, $lessonId);
            $message = 'Lesson completed';
            
            // Get updated course progress
            $courseProgress = $progress->getCourseProgress($userId, $lesson->getCourseId());
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'course_progress' => $courseProgress,
                'course_completed' => $courseProgress['is_completed']
            ]);
            exit;
            
        case 'update_time':
            // Update time spent
            $seconds = (int)($input['seconds'] ?? 0);
            if ($seconds > 0) {
                $result = $progress->updateTimeSpent($userId, $lessonId, $seconds);
                $message = 'Time updated';
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            exit;
    }
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $message ?? 'Progress updated'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update progress']);
    }
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);