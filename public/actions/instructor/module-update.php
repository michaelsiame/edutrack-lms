<?php
/**
 * Update Module Action
 */

require_once '../../../src/middleware/instructor-only.php';
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

// Validate
if (!$moduleId || !$courseId || !$title) {
    flash('message', 'Invalid data provided', 'error');
    redirect(url('instructor/courses.php'));
}

// Get module and verify ownership
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
    flash('message', 'You do not have permission to edit this module', 'error');
    redirect(url('instructor/courses.php'));
}

// Update module
$updateData = [
    'title' => $title,
    'description' => $description
];

if ($module->update($updateData)) {
    flash('message', 'Module updated successfully!', 'success');
} else {
    flash('message', 'Failed to update module', 'error');
}

redirect(url('instructor/courses/modules.php?id=' . $courseId));
