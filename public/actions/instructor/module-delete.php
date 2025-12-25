<?php
/**
 * Delete Module Action
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

// Validate
if (!$moduleId || !$courseId) {
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
    flash('message', 'You do not have permission to delete this module', 'error');
    redirect(url('instructor/courses.php'));
}

// Delete module (will also delete lessons via FK cascade or module->delete() method)
if ($module->delete()) {
    flash('message', 'Module deleted successfully!', 'success');
} else {
    flash('message', 'Failed to delete module', 'error');
}

redirect(url('instructor/courses/modules.php?id=' . $courseId));
