<?php
/**
 * Create Module Action
 */

require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Module.php';
require_once '../../../src/classes/Course.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('instructor/courses.php'));
}

validateCSRF();

$courseId = $_POST['course_id'] ?? null;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

// Validate
if (!$courseId || !$title) {
    flash('message', 'Course ID and title are required', 'error');
    redirect(url('instructor/courses.php'));
}

// Verify course exists and user has permission
$course = Course::find($courseId);
if (!$course) {
    flash('message', 'Course not found', 'error');
    redirect(url('instructor/courses.php'));
}

// Check ownership
$db = Database::getInstance();
$userId = currentUserId();
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : null;

$canEdit = hasRole('admin') ||
           ($instructorId && $course->getInstructorId() == $instructorId) ||
           ($course->getInstructorId() == $userId);

if (!$canEdit) {
    flash('message', 'You do not have permission to edit this course', 'error');
    redirect(url('instructor/courses.php'));
}

// Get next order index
$maxOrder = $db->fetchColumn("SELECT MAX(order_index) FROM modules WHERE course_id = ?", [$courseId]);
$orderIndex = ($maxOrder !== null) ? $maxOrder + 1 : 0;

// Create module
$moduleData = [
    'course_id' => $courseId,
    'title' => $title,
    'description' => $description,
    'order_index' => $orderIndex
];

$moduleId = Module::create($moduleData);

if ($moduleId) {
    flash('message', 'Module created successfully!', 'success');
} else {
    flash('message', 'Failed to create module', 'error');
}

redirect(url('instructor/courses/modules.php?id=' . $courseId));
