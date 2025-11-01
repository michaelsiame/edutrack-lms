<?php
/**
 * Lesson Progress API
 * Track student progress through lessons
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

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $lessonId = $input['lesson_id'] ?? null;
    $action = $input['action'] ?? null;

    if (!$lessonId || !$action) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    // Verify lesson exists and user is enrolled in the course
    $lesson = $db->fetchOne("
        SELECT l.*, m.course_id
        FROM lessons l
        JOIN modules m ON l.module_id = m.id
        JOIN enrollments e ON m.course_id = e.course_id
        WHERE l.id = ? AND e.user_id = ?
    ", [$lessonId, $userId]);

    if (!$lesson) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Lesson not found or not enrolled']);
        exit;
    }

    $courseId = $lesson['course_id'];

    try {
        if ($action === 'complete') {
            // Check if progress record exists
            $progress = $db->fetchOne("
                SELECT * FROM lesson_progress
                WHERE user_id = ? AND lesson_id = ?
            ", [$userId, $lessonId]);

            if ($progress) {
                // Update existing record
                $db->execute("
                    UPDATE lesson_progress
                    SET status = 'completed', completed_at = NOW()
                    WHERE user_id = ? AND lesson_id = ?
                ", [$userId, $lessonId]);
            } else {
                // Create new progress record
                $db->execute("
                    INSERT INTO lesson_progress (user_id, lesson_id, status, completed_at, created_at)
                    VALUES (?, ?, 'completed', NOW(), NOW())
                ", [$userId, $lessonId]);
            }

        } elseif ($action === 'uncomplete') {
            // Mark as in progress or delete
            $db->execute("
                UPDATE lesson_progress
                SET status = 'in_progress', completed_at = NULL
                WHERE user_id = ? AND lesson_id = ?
            ", [$userId, $lessonId]);

        } elseif ($action === 'start') {
            // Mark as in progress
            $progress = $db->fetchOne("
                SELECT * FROM lesson_progress
                WHERE user_id = ? AND lesson_id = ?
            ", [$userId, $lessonId]);

            if (!$progress) {
                $db->execute("
                    INSERT INTO lesson_progress (user_id, lesson_id, status, created_at)
                    VALUES (?, ?, 'in_progress', NOW())
                ", [$userId, $lessonId]);
            }
        }

        // Recalculate course progress
        updateCourseProgress($userId, $courseId);

        echo json_encode(['success' => true, 'message' => 'Progress updated']);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update progress']);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get progress for a lesson
    $lessonId = $_GET['lesson_id'] ?? null;

    if (!$lessonId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing lesson_id']);
        exit;
    }

    $progress = $db->fetchOne("
        SELECT * FROM lesson_progress
        WHERE user_id = ? AND lesson_id = ?
    ", [$userId, $lessonId]);

    echo json_encode([
        'success' => true,
        'progress' => $progress
    ]);

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

/**
 * Update course progress percentage based on completed lessons
 */
function updateCourseProgress($userId, $courseId) {
    global $db;

    // Get total lessons in course
    $totalLessons = (int) $db->fetchColumn("
        SELECT COUNT(DISTINCT l.id)
        FROM lessons l
        JOIN modules m ON l.module_id = m.id
        WHERE m.course_id = ?
    ", [$courseId]);

    if ($totalLessons === 0) {
        return;
    }

    // Get completed lessons
    $completedLessons = (int) $db->fetchColumn("
        SELECT COUNT(DISTINCT lp.lesson_id)
        FROM lesson_progress lp
        JOIN lessons l ON lp.lesson_id = l.id
        JOIN modules m ON l.module_id = m.id
        WHERE lp.user_id = ? AND lp.status = 'completed' AND m.course_id = ?
    ", [$userId, $courseId]);

    // Calculate percentage
    $progressPercentage = ($completedLessons / $totalLessons) * 100;

    // Update enrollment
    $db->execute("
        UPDATE enrollments
        SET progress_percentage = ?
        WHERE user_id = ? AND course_id = ?
    ", [$progressPercentage, $userId, $courseId]);

    // Check if course is completed (100% progress)
    if ($progressPercentage >= 100) {
        $enrollment = $db->fetchOne("
            SELECT * FROM enrollments
            WHERE user_id = ? AND course_id = ?
        ", [$userId, $courseId]);

        if ($enrollment && $enrollment['status'] !== 'completed') {
            // Mark enrollment as completed
            $db->execute("
                UPDATE enrollments
                SET status = 'completed', completed_at = NOW()
                WHERE user_id = ? AND course_id = ?
            ", [$userId, $courseId]);

            // Check if certificate should be issued
            $certificateExists = $db->fetchOne("
                SELECT * FROM certificates
                WHERE user_id = ? AND course_id = ?
            ", [$userId, $courseId]);

            if (!$certificateExists) {
                // Generate certificate number
                $certificateNumber = 'EDTK-' . strtoupper(substr(md5($userId . $courseId . time()), 0, 12));

                // Issue certificate
                $db->execute("
                    INSERT INTO certificates (user_id, course_id, certificate_number, issued_at, created_at)
                    VALUES (?, ?, ?, NOW(), NOW())
                ", [$userId, $courseId, $certificateNumber]);

                // Send notification
                $course = $db->fetchOne("SELECT title FROM courses WHERE id = ?", [$courseId]);
                if ($course && class_exists('Notification')) {
                    Notification::notifyCertificateIssued($userId, $course['title'], $certificateNumber);
                }
            }
        }
    }
}
