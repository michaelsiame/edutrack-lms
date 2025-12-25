<?php
/**
 * Delete Lesson Action
 */

require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Lesson.php';
require_once '../../../src/classes/Course.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('instructor/courses.php'));
}

validateCSRF();

$lessonId = $_POST['lesson_id'] ?? null;
$courseId = $_POST['course_id'] ?? null;

// Validate
if (!$lessonId || !$courseId) {
    flash('message', 'Invalid data provided', 'error');
    redirect(url('instructor/courses.php'));
}

// Get lesson and verify ownership
$lesson = Lesson::find($lessonId);
if (!$lesson) {
    flash('message', 'Lesson not found', 'error');
    redirect(url('instructor/courses.php'));
}

// Verify course ownership
$course = Course::find($courseId);
if (!$course) {
    flash('message', 'Course not found', 'error');
    redirect(url('instructor/courses.php'));
}

$db = Database::getInstance();
$userId = currentUserId();
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : null;

$canEdit = hasRole('admin') ||
           ($instructorId && $course->getInstructorId() == $instructorId) ||
           ($course->getInstructorId() == $userId);

if (!$canEdit) {
    flash('message', 'You do not have permission to delete this lesson', 'error');
    redirect(url('instructor/courses.php'));
}

// Delete lesson
if ($lesson->delete()) {
    flash('message', 'Lesson deleted successfully!', 'success');
} else {
    flash('message', 'Failed to delete lesson', 'error');
}

redirect(url('instructor/courses/modules.php?id=' . $courseId));
