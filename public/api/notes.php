<?php
/**
 * Notes API
 * Handle lesson notes
 */

require_once '../../src/bootstrap.php';

header('Content-Type: application/json');

// Must be logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

// Helper: Check enrollment for a course (admins/instructors bypass)
function checkNoteEnrollment($db, $userId, $courseId) {
    $userRoles = $_SESSION['user_roles'] ?? [];
    $isAdmin = in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles);
    $isInstructor = in_array('Instructor', $userRoles);

    if (!$isAdmin && !$isInstructor) {
        $enrolled = $db->fetchOne(
            "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? AND enrollment_status IN ('Enrolled', 'In Progress', 'Completed')",
            [$userId, $courseId]
        );
        if (!$enrolled) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You must be enrolled in this course to access notes']);
            exit;
        }
    }
}

// GET - Retrieve notes
if ($method === 'GET') {
    $lessonId = $_GET['lesson_id'] ?? null;
    $courseId = $_GET['course_id'] ?? null;

    if ($lessonId) {
        // Verify enrollment via lesson -> module -> course
        $lessonCourse = $db->fetchOne(
            "SELECT m.course_id FROM lessons l JOIN modules m ON l.module_id = m.id WHERE l.id = ?",
            [$lessonId]
        );
        if ($lessonCourse) {
            checkNoteEnrollment($db, $userId, $lessonCourse['course_id']);
        }

        // Get notes for specific lesson
        $sql = "SELECT notes, created_at, updated_at
                FROM lesson_notes
                WHERE user_id = :user_id AND lesson_id = :lesson_id";

        $result = $db->query($sql, [
            'user_id' => $userId,
            'lesson_id' => $lessonId
        ])->fetch();

        echo json_encode([
            'success' => true,
            'notes' => $result['notes'] ?? '',
            'created_at' => $result['created_at'] ?? null,
            'updated_at' => $result['updated_at'] ?? null
        ]);
    } elseif ($courseId) {
        // Verify enrollment for course
        checkNoteEnrollment($db, $userId, $courseId);

        // Get all notes for course
        $sql = "SELECT ln.*, l.title as lesson_title
                FROM lesson_notes ln
                JOIN lessons l ON ln.lesson_id = l.id
                JOIN course_modules m ON l.module_id = m.id
                WHERE ln.user_id = :user_id AND m.course_id = :course_id
                ORDER BY ln.updated_at DESC";

        $results = $db->query($sql, [
            'user_id' => $userId,
            'course_id' => $courseId
        ])->fetchAll();

        echo json_encode([
            'success' => true,
            'notes' => $results
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing lesson or course ID']);
    }
    exit;
}

// POST - Save notes
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }
    
    $lessonId = $input['lesson_id'] ?? null;
    $courseId = $input['course_id'] ?? null;
    $notes = $input['notes'] ?? '';
    
    if (!$lessonId || !$courseId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing lesson or course ID']);
        exit;
    }

    // Verify enrollment before saving notes
    checkNoteEnrollment($db, $userId, $courseId);

    // Save or update notes
    $sql = "INSERT INTO lesson_notes (user_id, lesson_id, course_id, notes, created_at, updated_at)
            VALUES (:user_id, :lesson_id, :course_id, :notes, NOW(), NOW())
            ON DUPLICATE KEY UPDATE notes = :notes, updated_at = NOW()";
    
    $result = $db->query($sql, [
        'user_id' => $userId,
        'lesson_id' => $lessonId,
        'course_id' => $courseId,
        'notes' => $notes
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Notes saved successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save notes']);
    }
    exit;
}

// DELETE - Delete notes
if ($method === 'DELETE') {
    $lessonId = $_GET['lesson_id'] ?? null;
    
    if (!$lessonId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing lesson ID']);
        exit;
    }
    
    $sql = "DELETE FROM lesson_notes WHERE user_id = :user_id AND lesson_id = :lesson_id";
    
    $result = $db->query($sql, [
        'user_id' => $userId,
        'lesson_id' => $lessonId
    ]);
    
    echo json_encode([
        'success' => (bool)$result,
        'message' => $result ? 'Notes deleted' : 'Failed to delete notes'
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);