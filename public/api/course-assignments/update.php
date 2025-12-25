<?php
/**
 * API: Update Course Assignments
 * Assign/unassign instructors to a course
 */

require_once '../../../src/middleware/admin-only.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

$courseId = $input['course_id'] ?? null;
$instructorIds = $input['instructor_ids'] ?? [];
$leadInstructorId = $input['lead_instructor_id'] ?? null;

// Validate
if (!$courseId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Course ID is required'
    ]);
    exit;
}

if (!is_array($instructorIds)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Instructor IDs must be an array'
    ]);
    exit;
}

// Validate lead instructor is in the list if set
if ($leadInstructorId && !in_array($leadInstructorId, $instructorIds)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Lead instructor must be in the assigned instructors list'
    ]);
    exit;
}

$db = Database::getInstance();

try {
    // Start transaction
    $db->beginTransaction();

    // Delete existing assignments for this course
    $db->execute("DELETE FROM course_instructors WHERE course_id = ?", [$courseId]);

    // Insert new assignments
    foreach ($instructorIds as $instructorId) {
        $role = ($instructorId == $leadInstructorId) ? 'Lead' : 'Assistant';

        $db->insert('course_instructors', [
            'course_id' => $courseId,
            'instructor_id' => $instructorId,
            'role' => $role,
            'assigned_date' => date('Y-m-d')
        ]);
    }

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Course assignments updated successfully',
        'data' => [
            'course_id' => $courseId,
            'instructor_count' => count($instructorIds),
            'lead_instructor_id' => $leadInstructorId
        ]
    ]);
} catch (Exception $e) {
    // Rollback transaction
    $db->rollback();

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update course assignments',
        'message' => $e->getMessage()
    ]);
}
