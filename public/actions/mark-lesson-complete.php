<?php
/**
 * Mark Lesson as Complete
 * Handles marking a lesson as completed and updating progress
 */

require_once '../../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to mark lessons complete', 'error');
    redirect('login.php');
}

// Validate CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

try {
    requireCsrfToken();
} catch (Exception $e) {
    flash('error', 'Invalid security token. Please try again.', 'error');
    redirect($_POST['redirect'] ?? 'my-courses.php');
}

try {
    $user = User::current();
    $userId = $user->getId();

    $courseId = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $lessonId = filter_input(INPUT_POST, 'lesson_id', FILTER_VALIDATE_INT);
    $redirectUrl = $_POST['redirect'] ?? 'my-courses.php';

    if (!$courseId || !$lessonId) {
        flash('error', 'Invalid course or lesson', 'error');
        redirect($redirectUrl);
    }

    // Verify the user is enrolled in the course
    $enrollment = $db->fetchOne("
        SELECT id FROM enrollments
        WHERE user_id = ? AND course_id = ?
    ", [$userId, $courseId]);

    if (!$enrollment) {
        flash('error', 'You are not enrolled in this course', 'error');
        redirect($redirectUrl);
    }

    // Check if progress record exists
    $existingProgress = $db->fetchOne("
        SELECT id, status FROM lesson_progress
        WHERE user_id = ? AND course_id = ? AND lesson_id = ?
    ", [$userId, $courseId, $lessonId]);

    if ($existingProgress) {
        // Update existing progress
        $db->query("
            UPDATE lesson_progress
            SET status = 'completed',
                progress_percentage = 100,
                completed_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ", [$existingProgress['id']]);
    } else {
        // Create new progress record
        $db->query("
            INSERT INTO lesson_progress
            (user_id, course_id, lesson_id, status, progress_percentage, completed_at, created_at, updated_at)
            VALUES (?, ?, ?, 'completed', 100, NOW(), NOW(), NOW())
        ", [$userId, $courseId, $lessonId]);
    }

    // Update overall course progress
    $totalLessons = $db->fetchOne("
        SELECT COUNT(*) as total
        FROM lessons l
        JOIN course_modules m ON l.module_id = m.id
        WHERE m.course_id = ?
    ", [$courseId])['total'];

    $completedLessons = $db->fetchOne("
        SELECT COUNT(*) as completed
        FROM lesson_progress
        WHERE user_id = ? AND course_id = ? AND status = 'completed'
    ", [$userId, $courseId])['completed'];

    $progressPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

    $db->query("
        UPDATE enrollments
        SET progress_percentage = ?,
            last_accessed = NOW(),
            updated_at = NOW()
        WHERE user_id = ? AND course_id = ?
    ", [$progressPercentage, $userId, $courseId]);

    flash('success', 'Lesson marked as complete! Keep up the great work!', 'success');
    redirect($redirectUrl);

} catch (Exception $e) {
    error_log("Error marking lesson complete: " . $e->getMessage());
    flash('error', 'Failed to mark lesson as complete. Please try again.', 'error');
    redirect($_POST['redirect'] ?? 'my-courses.php');
}
