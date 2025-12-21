<?php
/**
 * Enrollments API Endpoint
 * Uses Enrollment class for database operations
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';
require_once '../../src/classes/Enrollment.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            // Get all enrollments
            $enrollments = Enrollment::all();

            $formattedEnrollments = array_map(function($e) {
                return [
                    'id' => $e['id'],
                    'user_id' => $e['user_id'],
                    'student_id' => $e['student_id'],
                    'course_id' => $e['course_id'],
                    'enrolled_at' => $e['enrolled_at'],
                    'start_date' => $e['start_date'],
                    'progress' => (float)$e['progress'],
                    'enrollment_status' => $e['enrollment_status'],
                    'completion_date' => $e['completion_date'],
                    'student_name' => trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? '')),
                    'course_title' => $e['course_title'] ?? 'Unknown',
                    'course_price' => (float)($e['course_price'] ?? 0)
                ];
            }, $enrollments);

            echo json_encode(['success' => true, 'data' => $formattedEnrollments]);
            break;

        case 'POST':
            // Create enrollment using Enrollment::create()
            if (empty($input['user_id']) || empty($input['course_id'])) {
                throw new Exception('User ID and Course ID required');
            }

            // Check if already enrolled
            if (Enrollment::isEnrolled($input['user_id'], $input['course_id'])) {
                throw new Exception('User already enrolled in this course');
            }

            $enrollmentId = Enrollment::create([
                'user_id' => $input['user_id'],
                'course_id' => $input['course_id'],
                'start_date' => $input['start_date'] ?? date('Y-m-d')
            ]);

            if ($enrollmentId) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Enrollment created successfully',
                    'data' => ['id' => $enrollmentId]
                ]);
            } else {
                throw new Exception('Failed to create enrollment');
            }
            break;

        case 'PUT':
            // Update enrollment status
            if (empty($input['id'])) {
                throw new Exception('Enrollment ID required');
            }

            $enrollment = Enrollment::find($input['id']);
            if (!$enrollment) {
                throw new Exception('Enrollment not found');
            }

            $updateData = [];
            if (isset($input['enrollment_status']) || isset($input['status'])) {
                $updateData['enrollment_status'] = $input['enrollment_status'] ?? $input['status'];
                
                if ($updateData['enrollment_status'] === 'Completed') {
                    $updateData['completion_date'] = date('Y-m-d');
                    $updateData['progress'] = 100;
                }
            }

            if (isset($input['progress'])) {
                $updateData['progress'] = $input['progress'];
            }

            if ($enrollment->update($updateData)) {
                echo json_encode(['success' => true, 'message' => 'Enrollment updated']);
            } else {
                throw new Exception('Failed to update enrollment');
            }
            break;

        case 'DELETE':
            parse_str(file_get_contents('php://input'), $params);
            $enrollmentId = $params['id'] ?? $_GET['id'] ?? null;
            if (!$enrollmentId) throw new Exception('Enrollment ID required');

            $enrollment = Enrollment::find($enrollmentId);
            if (!$enrollment) throw new Exception('Enrollment not found');

            if ($enrollment->delete()) {
                echo json_encode(['success' => true, 'message' => 'Enrollment deleted']);
            } else {
                throw new Exception('Failed to delete enrollment');
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
