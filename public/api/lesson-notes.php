<?php
/**
 * Lesson Notes API
 * Save and retrieve student notes for lessons
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

// Handle GET requests - Fetch notes
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lessonId = $_GET['lesson_id'] ?? null;

    if ($lessonId) {
        // Get notes for a specific lesson
        $notes = $db->fetchAll("
            SELECT ln.*
            FROM lesson_notes ln
            JOIN lessons l ON ln.lesson_id = l.id
            JOIN course_modules m ON l.module_id = m.id
            JOIN enrollments e ON m.course_id = e.course_id
            WHERE ln.user_id = ? AND ln.lesson_id = ? AND e.user_id = ?
            ORDER BY ln.created_at DESC
        ", [$userId, $lessonId, $userId]);

        echo json_encode([
            'success' => true,
            'notes' => $notes
        ]);
    } else {
        // Get all notes for the user
        $notes = $db->fetchAll("
            SELECT ln.*, l.title as lesson_title, c.title as course_title
            FROM lesson_notes ln
            JOIN lessons l ON ln.lesson_id = l.id
            JOIN course_modules m ON l.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE ln.user_id = ?
            ORDER BY ln.created_at DESC
            LIMIT 50
        ", [$userId]);

        echo json_encode([
            'success' => true,
            'notes' => $notes
        ]);
    }
}

// Handle POST requests - Create note
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_GET['csrf_token'] ?? null;
    if (!$csrfToken || !verifyCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $lessonId = $input['lesson_id'] ?? null;
    $noteText = trim($input['note_text'] ?? '');

    if (!$lessonId || empty($noteText)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    // Verify lesson exists and user is enrolled
    $lesson = $db->fetchOne("
        SELECT l.*, m.course_id
        FROM lessons l
        JOIN course_modules m ON l.module_id = m.id
        JOIN enrollments e ON m.course_id = e.course_id
        WHERE l.id = ? AND e.user_id = ?
    ", [$lessonId, $userId]);

    if (!$lesson) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Lesson not found or not enrolled']);
        exit;
    }

    try {
        $db->query("
            INSERT INTO lesson_notes (user_id, lesson_id, note_text, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ", [$userId, $lessonId, $noteText]);

        $noteId = $db->lastInsertId();

        $note = $db->fetchOne("SELECT * FROM lesson_notes WHERE id = ?", [$noteId]);

        echo json_encode([
            'success' => true,
            'message' => 'Note saved successfully',
            'note' => $note
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save note']);
    }
}

// Handle PUT requests - Update note
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Verify CSRF token
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_GET['csrf_token'] ?? null;
    if (!$csrfToken || !verifyCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $noteId = $input['note_id'] ?? null;
    $noteText = trim($input['note_text'] ?? '');

    if (!$noteId || empty($noteText)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    // Verify note belongs to user
    $note = $db->fetchOne("
        SELECT * FROM lesson_notes WHERE id = ? AND user_id = ?
    ", [$noteId, $userId]);

    if (!$note) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Note not found']);
        exit;
    }

    try {
        $db->query("
            UPDATE lesson_notes
            SET note_text = ?, updated_at = NOW()
            WHERE id = ?
        ", [$noteText, $noteId]);

        $updatedNote = $db->fetchOne("SELECT * FROM lesson_notes WHERE id = ?", [$noteId]);

        echo json_encode([
            'success' => true,
            'message' => 'Note updated successfully',
            'note' => $updatedNote
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update note']);
    }
}

// Handle DELETE requests - Delete note
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Verify CSRF token
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_GET['csrf_token'] ?? null;
    if (!$csrfToken || !verifyCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $noteId = $input['note_id'] ?? $_GET['note_id'] ?? null;

    if (!$noteId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing note_id']);
        exit;
    }

    // Verify note belongs to user
    $note = $db->fetchOne("
        SELECT * FROM lesson_notes WHERE id = ? AND user_id = ?
    ", [$noteId, $userId]);

    if (!$note) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Note not found']);
        exit;
    }

    try {
        $db->query("DELETE FROM lesson_notes WHERE id = ?", [$noteId]);

        echo json_encode([
            'success' => true,
            'message' => 'Note deleted successfully'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete note']);
    }
}

else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
