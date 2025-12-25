<?php
/**
 * Update Lesson Action
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
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$lessonType = $_POST['lesson_type'] ?? 'video';
$videoUrl = trim($_POST['video_url'] ?? '');
$content = trim($_POST['content'] ?? '');
$duration = $_POST['duration'] ? (int)$_POST['duration'] : null;
$isPreview = isset($_POST['is_preview']) ? 1 : 0;

// Validate
if (!$lessonId || !$courseId || !$title) {
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
    flash('message', 'You do not have permission to edit this lesson', 'error');
    redirect(url('instructor/courses.php'));
}

// Update lesson
$updateData = [
    'title' => $title,
    'slug' => slugify($title),
    'description' => $description,
    'lesson_type' => $lessonType,
    'video_url' => $videoUrl,
    'content' => $content,
    'duration' => $duration,
    'is_preview' => $isPreview
];

if ($lesson->update($updateData)) {
    flash('message', 'Lesson updated successfully!', 'success');
} else {
    flash('message', 'Failed to update lesson', 'error');
}

redirect(url('instructor/courses/modules.php?id=' . $courseId));
