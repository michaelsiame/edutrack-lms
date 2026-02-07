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
        // Verify enrollment (admins/instructors can bypass)
        $userRoles = $_SESSION['user_roles'] ?? [];
        $isAdmin = in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles);
        $isInstructor = in_array('Instructor', $userRoles);

        if (!$isAdmin && !$isInstructor) {
            $db = Database::getInstance();
            $enrolled = $db->fetchOne(
                "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? AND enrollment_status IN ('Enrolled', 'In Progress', 'Completed')",
                [$userId, $courseId]
            );
            if (!$enrolled) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'You must be enrolled in this course to access lessons']);
                exit;
            }
        }

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
