<?php
/**
 * API: Get All Instructors (including team members)
 */

require_once '../../src/middleware/admin-only.php';

header('Content-Type: application/json');

$db = Database::getInstance();

try {
    // Get all instructors with user information and check if they're team members
    $instructors = $db->fetchAll("
        SELECT
            i.id,
            i.user_id,
            u.name,
            u.email,
            i.bio,
            i.specialization,
            i.is_verified,
            tm.position,
            CASE WHEN tm.id IS NOT NULL THEN 1 ELSE 0 END as is_team_member
        FROM instructors i
        JOIN users u ON i.user_id = u.id
        LEFT JOIN team_members tm ON tm.user_id = u.id
        ORDER BY u.name ASC
    ");

    echo json_encode([
        'success' => true,
        'data' => $instructors
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch instructors',
        'message' => $e->getMessage()
    ]);
}
