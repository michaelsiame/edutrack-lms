<?php
/**
 * Enrollments API Endpoint
 * Handles enrollment management operations
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            // Get all enrollments with student and course info
            $sql = "SELECT
                        e.id,
                        e.user_id,
                        e.student_id,
                        e.course_id,
                        e.enrolled_at,
                        e.start_date,
                        e.progress,
                        e.enrollment_status,
                        e.completion_date,
                        CONCAT(u.first_name, ' ', u.last_name) as student_name,
                        c.title as course_title,
                        c.price as course_price,
                        s.student_id as student_number
                    FROM enrollments e
                    INNER JOIN users u ON e.user_id = u.id
                    INNER JOIN courses c ON e.course_id = c.id
                    LEFT JOIN students s ON e.student_id = s.id
                    ORDER BY e.enrolled_at DESC";

            $enrollments = $db->fetchAll($sql);

            echo json_encode([
                'success' => true,
                'data' => $enrollments
            ]);
            break;

        case 'POST':
            // Create new enrollment
            if (empty($input['user_id']) || empty($input['course_id'])) {
                throw new Exception('User ID and Course ID are required');
            }

            // Check if user is already enrolled
            if ($db->exists('enrollments', 'user_id = ? AND course_id = ?', [$input['user_id'], $input['course_id']])) {
                throw new Exception('User is already enrolled in this course');
            }

            // Get or create student record
            $student = $db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$input['user_id']]);

            if (!$student) {
                // Create student record
                $studentId = $db->insert('students', [
                    'user_id' => $input['user_id'],
                    'student_id' => 'STU-' . date('Y') . '-' . str_pad($input['user_id'], 6, '0', STR_PAD_LEFT),
                    'enrollment_date' => date('Y-m-d'),
                    'academic_status' => 'Active',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $studentId = $student['id'];
            }

            // Create enrollment
            $enrollmentId = $db->insert('enrollments', [
                'user_id' => $input['user_id'],
                'student_id' => $studentId,
                'course_id' => $input['course_id'],
                'enrolled_at' => date('Y-m-d'),
                'start_date' => $input['start_date'] ?? date('Y-m-d'),
                'enrollment_status' => 'Enrolled',
                'progress' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Update course enrollment count
            $db->query(
                "UPDATE courses SET enrollment_count = enrollment_count + 1 WHERE id = ?",
                [$input['course_id']]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Enrollment created successfully',
                'data' => ['id' => $enrollmentId]
            ]);
            break;

        case 'PUT':
            // Update enrollment status
            if (empty($input['id'])) {
                throw new Exception('Enrollment ID is required');
            }

            $enrollmentId = $input['id'];
            $updateData = [];

            if (isset($input['enrollment_status']) || isset($input['status'])) {
                $updateData['enrollment_status'] = $input['enrollment_status'] ?? $input['status'];

                // If status is Completed, set completion date
                if ($updateData['enrollment_status'] === 'Completed') {
                    $updateData['completion_date'] = date('Y-m-d');
                    $updateData['progress'] = 100;
                }
            }

            if (isset($input['progress'])) {
                $updateData['progress'] = $input['progress'];
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $db->update('enrollments', $updateData, 'id = ?', [$enrollmentId]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Enrollment updated successfully'
            ]);
            break;

        case 'DELETE':
            // Delete enrollment
            parse_str(file_get_contents('php://input'), $params);
            $enrollmentId = $params['id'] ?? $_GET['id'] ?? null;

            if (empty($enrollmentId)) {
                throw new Exception('Enrollment ID is required');
            }

            // Get course ID before deleting
            $enrollment = $db->fetchOne("SELECT course_id FROM enrollments WHERE id = ?", [$enrollmentId]);

            // Delete enrollment
            $db->delete('enrollments', 'id = ?', [$enrollmentId]);

            // Update course enrollment count
            if ($enrollment) {
                $db->query(
                    "UPDATE courses SET enrollment_count = GREATEST(0, enrollment_count - 1) WHERE id = ?",
                    [$enrollment['course_id']]
                );
            }

            echo json_encode([
                'success' => true,
                'message' => 'Enrollment deleted successfully'
            ]);
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    if ($db->getConnection()->inTransaction()) {
        $db->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
