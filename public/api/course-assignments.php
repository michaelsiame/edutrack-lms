<?php
/**
 * API: Get All Course Assignments
 */

require_once '../../src/middleware/admin-only.php';

header('Content-Type: application/json');

$db = Database::getInstance();

try {
    // Get all course-instructor assignments
    $assignments = $db->fetchAll("
        SELECT
            id,
            course_id,
            instructor_id,
            is_lead
        FROM course_instructors
        ORDER BY course_id, is_lead DESC
    ");

    echo json_encode([
        'success' => true,
        'data' => $assignments
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch course assignments',
        'message' => $e->getMessage()
    ]);
}
