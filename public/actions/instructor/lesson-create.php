<?php
/**
 * Create Lesson Action
 */

require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Lesson.php';
require_once '../../../src/classes/Module.php';
require_once '../../../src/classes/Course.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('instructor/courses.php'));
}

validateCSRF();

$moduleId = $_POST['module_id'] ?? null;
$courseId = $_POST['course_id'] ?? null;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$lessonType = $_POST['lesson_type'] ?? 'video';
$videoUrl = trim($_POST['video_url'] ?? '');
$content = trim($_POST['content'] ?? '');
$duration = $_POST['duration'] ? (int)$_POST['duration'] : null;
$isPreview = isset($_POST['is_preview']) ? 1 : 0;

// Validate
if (!$moduleId || !$courseId || !$title) {
    flash('message', 'Module ID, course ID, and title are required', 'error');
    redirect(url('instructor/courses.php'));
}

// Verify module exists
$module = Module::find($moduleId);
if (!$module) {
    flash('message', 'Module not found', 'error');
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
    flash('message', 'You do not have permission to add lessons to this course', 'error');
    redirect(url('instructor/courses.php'));
}

// Get next display order
$maxOrder = $db->fetchColumn("SELECT MAX(display_order) FROM lessons WHERE module_id = ?", [$moduleId]);
$displayOrder = ($maxOrder !== null) ? $maxOrder + 1 : 0;

// Create lesson
$lessonData = [
    'module_id' => $moduleId,
    'title' => $title,
    'slug' => slugify($title),
    'description' => $description,
    'lesson_type' => $lessonType,
    'video_url' => $videoUrl,
    'content' => $content,
    'duration' => $duration,
    'display_order' => $displayOrder,
    'is_preview' => $isPreview
];

$lessonId = Lesson::create($lessonData);

if ($lessonId) {
    flash('message', 'Lesson created successfully!', 'success');
} else {
    flash('message', 'Failed to create lesson', 'error');
}

redirect(url('instructor/courses/modules.php?id=' . $courseId));
