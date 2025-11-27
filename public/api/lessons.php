<?php
/**
 * Lessons API
 * Get lessons for courses and modules
 */

require_once '../../src/bootstrap.php';

header('Content-Type: application/json');

// Ensure user is authenticated
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = User::current();
$userId = $user->getId();

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $courseId = $_GET['course_id'] ?? null;
    $moduleId = $_GET['module_id'] ?? null;

    if ($courseId) {
        // Get lessons for a course
        $lessons = Lesson::getByCourse($courseId);

        echo json_encode([
            'success' => true,
            'lessons' => $lessons
        ]);
        exit;
    }

    if ($moduleId) {
        // Get lessons for a module
        $lessons = Lesson::getByModule($moduleId);

        echo json_encode([
            'success' => true,
            'lessons' => $lessons
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Course ID or Module ID is required']);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
