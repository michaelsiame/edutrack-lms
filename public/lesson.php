<?php
/**
 * Lesson Page
 * Redirects to the learn.php interface with the specified lesson
 */

require_once '../src/bootstrap.php';

// Get lesson ID from URL
$lessonId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$lessonId) {
    flash('error', 'Invalid lesson', 'error');
    redirect('my-courses.php');
}

try {
    // Get lesson details with course info
    $lesson = $db->fetchOne("
        SELECT l.*, m.course_id, c.slug as course_slug
        FROM lessons l
        JOIN modules m ON l.module_id = m.id
        JOIN courses c ON m.course_id = c.id
        WHERE l.id = ?
    ", [$lessonId]);

    if (!$lesson) {
        flash('error', 'Lesson not found', 'error');
        redirect('my-courses.php');
    }

    // Redirect to learn.php with course and lesson parameters
    redirect('learn.php?course=' . urlencode($lesson['course_slug']) . '&lesson=' . $lessonId);

} catch (Exception $e) {
    error_log("Lesson.php Error: " . $e->getMessage());
    flash('error', 'An error occurred loading the lesson', 'error');
    redirect('my-courses.php');
}
