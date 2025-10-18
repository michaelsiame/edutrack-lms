<?php
/**
 * Assignment API
 * Handle assignment operations
 */

require_once '../../src/includes/config.php';
require_once '../../src/includes/database.php';
require_once '../../src/includes/functions.php';
require_once '../../src/includes/auth.php';
require_once '../../src/classes/Assignment.php';
require_once '../../src/classes/Submission.php';
require_once '../../src/classes/FileUpload.php';
require_once '../../src/classes/Enrollment.php';

header('Content-Type: application/json');

// Must be logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// GET - Retrieve assignment data
if ($method === 'GET') {
    $assignmentId = $_GET['assignment_id'] ?? null;
    $submissionId = $_GET['submission_id'] ?? null;
    
    if ($submissionId) {
        // Get submission details
        $submission = Submission::find($submissionId);
        
        if (!$submission || $submission->getUserId() != $userId) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Submission not found']);
            exit;
        }
        
        echo json_encode(['success' => true, 'submission' => [
            'id' => $submission->getId(),
            'assignment_id' => $submission->getAssignmentId(),
            'submission_text' => $submission->getSubmissionText(),
            'file_name' => $submission->getFileName(),
            'file_size' => $submission->getFileSize(),
            'status' => $submission->getStatus(),
            'points_earned' => $submission->getPointsEarned(),
            'max_points' => $submission->getMaxPoints(),
            'grade_percentage' => $submission->getGradePercentage(),
            'letter_grade' => $submission->getLetterGrade(),
            'feedback' => $submission->getFeedback(),
            'submitted_at' => $submission->getSubmittedAt(),
            'graded_at' => $submission->getGradedAt(),
            'is_late' => $submission->isLate(),
            'download_url' => $submission->getDownloadUrl()
        ]]);
        exit;
    }
    
    if ($assignmentId) {
        // Get assignment details
        $assignment = Assignment::find($assignmentId);
        if (!$assignment) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Assignment not found']);
            exit;
        }
        
        // Check enrollment
        if (!Enrollment::isEnrolled($userId, $assignment->getCourseId())) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Not enrolled in course']);
            exit;
        }
        
        // Get user's submission
        $submission = $assignment->getUserSubmission($userId);
        
        echo json_encode([
            'success' => true,
            'assignment' => [
                'id' => $assignment->getId(),
                'title' => $assignment->getTitle(),
                'description' => $assignment->getDescription(),
                'instructions' => $assignment->getInstructions(),
                'max_points' => $assignment->getMaxPoints(),
                'due_date' => $assignment->getDueDate(),
                'formatted_due_date' => $assignment->getFormattedDueDate(),
                'allow_late_submission' => $assignment->allowsLateSubmission(),
                'max_file_size' => $assignment->getMaxFileSize(),
                'formatted_file_size' => $assignment->getFormattedFileSize(),
                'allowed_file_types' => $assignment->getAllowedFileTypesArray(),
                'is_overdue' => $assignment->isOverdue(),
                'time_remaining' => $assignment->getTimeRemaining()
            ],
            'submission' => $submission && $submission->exists() ? [
                'id' => $submission->getId(),
                'status' => $submission->getStatus(),
                'submitted_at' => $submission->getSubmittedAt(),
                'is_graded' => $submission->isGraded(),
                'points_earned' => $submission->getPointsEarned(),
                'grade_percentage' => $submission->getGradePercentage()
            ] : null,
            'can_submit' => $assignment->canUserSubmit($userId)
        ]);
        exit;
    }
    
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing assignment or submission ID']);
    exit;
}

// POST - Submit assignment
if ($method === 'POST') {
    // Check if it's a file upload or JSON data
    if (!empty($_FILES['file'])) {
        // File upload submission
        $assignmentId = $_POST['assignment_id'] ?? null;
        $submissionText = $_POST['submission_text'] ?? null;
        
        if (!$assignmentId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing assignment ID']);
            exit;
        }
        
        $assignment = Assignment::find($assignmentId);
        if (!$assignment) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Assignment not found']);
            exit;
        }
        
        // Check enrollment
        if (!Enrollment::isEnrolled($userId, $assignment->getCourseId())) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Not enrolled in course']);
            exit;
        }
        
        // Check if can submit
        if (!$assignment->canUserSubmit($userId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Cannot submit assignment']);
            exit;
        }
        
        // Handle file upload
        $fileUpload = new FileUpload($_FILES['file']);
        $fileUpload->setAllowedTypes($assignment->getAllowedFileTypes())
                   ->setMaxSize($assignment->getMaxFileSize());
        
        $uploadResult = $fileUpload->upload();
        
        if (!$uploadResult) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $fileUpload->getError()]);
            exit;
        }
        
        // Create submission
        $submissionData = [
            'assignment_id' => $assignmentId,
            'user_id' => $userId,
            'course_id' => $assignment->getCourseId(),
            'submission_text' => $submissionText,
            'file_path' => $uploadResult['filepath'],
            'file_name' => $uploadResult['original_name'],
            'file_size' => $uploadResult['size']
        ];
        
        $submissionId = Submission::create($submissionData);
        
        if ($submissionId) {
            echo json_encode([
                'success' => true,
                'submission_id' => $submissionId,
                'message' => 'Assignment submitted successfully'
            ]);
        } else {
            // Delete uploaded file if submission creation failed
            FileUpload::delete($uploadResult['filepath']);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create submission']);
        }
        
    } else {
        // JSON data submission (text only)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit;
        }
        
        $assignmentId = $input['assignment_id'] ?? null;
        $submissionText = $input['submission_text'] ?? null;
        
        if (!$assignmentId || !$submissionText) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }
        
        $assignment = Assignment::find($assignmentId);
        if (!$assignment) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Assignment not found']);
            exit;
        }
        
        // Check enrollment
        if (!Enrollment::isEnrolled($userId, $assignment->getCourseId())) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Not enrolled in course']);
            exit;
        }
        
        // Check if can submit
        if (!$assignment->canUserSubmit($userId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Cannot submit assignment']);
            exit;
        }
        
        // Create submission
        $submissionData = [
            'assignment_id' => $assignmentId,
            'user_id' => $userId,
            'course_id' => $assignment->getCourseId(),
            'submission_text' => $submissionText
        ];
        
        $submissionId = Submission::create($submissionData);
        
        if ($submissionId) {
            echo json_encode([
                'success' => true,
                'submission_id' => $submissionId,
                'message' => 'Assignment submitted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create submission']);
        }
    }
    
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);