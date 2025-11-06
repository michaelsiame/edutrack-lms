<?php
/**
 * Submit Assignment Action
 * Handles assignment submission with file upload
 */

require_once '../../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    flash('error', 'You must be logged in to submit assignments', 'error');
    redirect('login.php');
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('my-courses.php');
}

// Validate CSRF token
try {
    requireCsrfToken();
} catch (Exception $e) {
    flash('error', 'Invalid security token. Please try again.', 'error');
    redirect('my-courses.php');
}

try {
    $user = User::current();
    $userId = $user->getId();

    $assignmentId = filter_input(INPUT_POST, 'assignment_id', FILTER_VALIDATE_INT);
    $submissionText = trim($_POST['submission_text'] ?? '');

    if (!$assignmentId) {
        flash('error', 'Invalid assignment', 'error');
        redirect('my-courses.php');
    }

    // Get assignment details
    $assignment = $db->fetchOne("
        SELECT a.*, c.slug as course_slug
        FROM assignments a
        JOIN courses c ON a.course_id = c.id
        WHERE a.id = ?
    ", [$assignmentId]);

    if (!$assignment) {
        flash('error', 'Assignment not found', 'error');
        redirect('my-courses.php');
    }

    // Verify enrollment
    $enrollment = $db->fetchOne("
        SELECT id FROM enrollments
        WHERE user_id = ? AND course_id = ?
    ", [$userId, $assignment['course_id']]);

    if (!$enrollment) {
        flash('error', 'You are not enrolled in this course', 'error');
        redirect('my-courses.php');
    }

    // Validate submission (must have text or file)
    if (empty($submissionText) && empty($_FILES['submission_file']['name'])) {
        flash('error', 'Please provide submission text or upload a file', 'error');
        redirect('assignment.php?id=' . $assignmentId);
    }

    // Handle file upload
    $fileName = null;
    $filePath = null;
    $fileSize = 0;

    if (!empty($_FILES['submission_file']['name']) && $_FILES['submission_file']['error'] == 0) {
        $uploadDir = __DIR__ . '/../uploads/assignments/submissions/';

        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileInfo = pathinfo($_FILES['submission_file']['name']);
        $fileExtension = strtolower($fileInfo['extension']);

        // Validate file type
        $allowedExtensions = ['pdf', 'doc', 'docx', 'zip', 'jpg', 'jpeg', 'png', 'txt'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            flash('error', 'Invalid file type. Allowed types: PDF, Word, ZIP, Images, Text', 'error');
            redirect('assignment.php?id=' . $assignmentId);
        }

        // Validate file size (10MB max)
        $maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if ($_FILES['submission_file']['size'] > $maxSize) {
            flash('error', 'File is too large. Maximum size is 10MB', 'error');
            redirect('assignment.php?id=' . $assignmentId);
        }

        // Generate unique filename
        $fileName = $userId . '_' . $assignmentId . '_' . time() . '.' . $fileExtension;
        $filePath = 'assignments/submissions/' . $fileName;
        $fullPath = $uploadDir . $fileName;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['submission_file']['tmp_name'], $fullPath)) {
            flash('error', 'Failed to upload file. Please try again.', 'error');
            redirect('assignment.php?id=' . $assignmentId);
        }

        $fileSize = $_FILES['submission_file']['size'];
    }

    // Insert submission
    $db->query("
        INSERT INTO assignment_submissions
        (assignment_id, user_id, course_id, submission_text, file_name, file_path, file_size, status, submitted_at, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'submitted', NOW(), NOW(), NOW())
    ", [
        $assignmentId,
        $userId,
        $assignment['course_id'],
        $submissionText,
        $_FILES['submission_file']['name'] ?? null,
        $filePath,
        $fileSize
    ]);

    // Send notification email to instructors (optional)
    // You can implement this later

    flash('success', 'Assignment submitted successfully! Your instructor will review it soon.', 'success');
    redirect('assignment.php?id=' . $assignmentId);

} catch (Exception $e) {
    error_log("Assignment Submission Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    flash('error', 'An error occurred while submitting your assignment. Please try again.', 'error');
    redirect('my-courses.php');
}
