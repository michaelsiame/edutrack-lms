<?php
/**
 * Progress Tracking API
 * Handles automatic progress tracking for lessons
 */

header('Content-Type: application/json');

require_once '../../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $user = User::current();
    $userId = $user->getId();

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    $courseId = filter_var($input['course_id'] ?? null, FILTER_VALIDATE_INT);
    $lessonId = filter_var($input['lesson_id'] ?? null, FILTER_VALIDATE_INT);
    $progressPercentage = filter_var($input['progress_percentage'] ?? 0, FILTER_VALIDATE_INT);
    $action = $input['action'] ?? 'update'; // 'update' or 'complete'

    // Validate inputs
    if (!$courseId || !$lessonId) {
        throw new Exception('Missing course_id or lesson_id');
    }

    // Ensure progress percentage is between 0 and 100
    $progressPercentage = max(0, min(100, $progressPercentage));

    // Verify the user is enrolled in the course
    $enrollment = $db->fetchOne("
        SELECT id FROM enrollments
        WHERE user_id = ? AND course_id = ?
    ", [$userId, $courseId]);

    if (!$enrollment) {
        throw new Exception('You are not enrolled in this course');
    }

    // Check if progress record exists
    $existingProgress = $db->fetchOne("
        SELECT id, status, progress_percentage FROM lesson_progress
        WHERE user_id = ? AND course_id = ? AND lesson_id = ?
    ", [$userId, $courseId, $lessonId]);

    $status = ($action === 'complete' || $progressPercentage >= 100) ? 'completed' : 'in_progress';
    $completedAt = ($status === 'completed') ? 'NOW()' : 'NULL';

    if ($existingProgress) {
        // Don't downgrade progress - only update if new progress is higher
        if ($progressPercentage > $existingProgress['progress_percentage'] || $action === 'complete') {
            $db->query("
                UPDATE lesson_progress
                SET status = ?,
                    progress_percentage = ?,
                    completed_at = " . $completedAt . ",
                    updated_at = NOW()
                WHERE id = ?
            ", [$status, $progressPercentage, $existingProgress['id']]);
        }
    } else {
        // Create new progress record
        if ($completedAt === 'NOW()') {
            $db->query("
                INSERT INTO lesson_progress
                (user_id, course_id, lesson_id, status, progress_percentage, completed_at, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW(), NOW())
            ", [$userId, $courseId, $lessonId, $status, $progressPercentage]);
        } else {
            $db->query("
                INSERT INTO lesson_progress
                (user_id, course_id, lesson_id, status, progress_percentage, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ", [$userId, $courseId, $lessonId, $status, $progressPercentage]);
        }
    }

    // Update overall course progress
    $totalLessons = $db->fetchOne("
        SELECT COUNT(*) as total
        FROM lessons l
        JOIN modules m ON l.module_id = m.id
        WHERE m.course_id = ?
    ", [$courseId])['total'];

    $completedLessons = $db->fetchOne("
        SELECT COUNT(*) as completed
        FROM lesson_progress
        WHERE user_id = ? AND course_id = ? AND status = 'completed'
    ", [$userId, $courseId])['completed'];

    $courseProgressPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

    $db->query("
        UPDATE enrollments
        SET progress_percentage = ?,
            last_accessed = NOW(),
            updated_at = NOW()
        WHERE user_id = ? AND course_id = ?
    ", [$courseProgressPercentage, $userId, $courseId]);

    echo json_encode([
        'success' => true,
        'message' => 'Progress updated successfully',
        'lesson_progress' => $progressPercentage,
        'course_progress' => $courseProgressPercentage,
        'status' => $status,
        'completed' => ($status === 'completed')
    ]);

} catch (Exception $e) {
    error_log("Progress tracking error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
