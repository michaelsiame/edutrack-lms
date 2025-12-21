<?php
/**
 * Certificates API Endpoint
 * Handles certificate management
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
            // Get all certificates
            $sql = "SELECT
                        cert.certificate_id as id,
                        cert.enrollment_id,
                        cert.certificate_number as code,
                        cert.issued_date as date,
                        cert.certificate_url,
                        cert.verification_code,
                        cert.is_verified as verified,
                        cert.expiry_date,
                        CONCAT(u.first_name, ' ', u.last_name) as student,
                        c.title as course
                    FROM certificates cert
                    INNER JOIN enrollments e ON cert.enrollment_id = e.id
                    INNER JOIN users u ON e.user_id = u.id
                    INNER JOIN courses c ON e.course_id = c.id
                    ORDER BY cert.issued_date DESC";

            $certificates = $db->fetchAll($sql);

            echo json_encode([
                'success' => true,
                'data' => $certificates
            ]);
            break;

        case 'POST':
            // Issue new certificate
            if (empty($input['enrollment_id'])) {
                throw new Exception('Enrollment ID is required');
            }

            // Check if certificate already exists for this enrollment
            if ($db->exists('certificates', 'enrollment_id = ?', [$input['enrollment_id']])) {
                throw new Exception('Certificate already exists for this enrollment');
            }

            // Get enrollment details
            $enrollment = $db->fetchOne(
                "SELECT e.*, c.title, u.first_name, u.last_name
                 FROM enrollments e
                 INNER JOIN courses c ON e.course_id = c.id
                 INNER JOIN users u ON e.user_id = u.id
                 WHERE e.id = ?",
                [$input['enrollment_id']]
            );

            if (!$enrollment) {
                throw new Exception('Enrollment not found');
            }

            // Generate certificate number
            $year = date('Y');
            $lastCert = $db->fetchOne(
                "SELECT certificate_number FROM certificates
                 WHERE certificate_number LIKE ?
                 ORDER BY certificate_id DESC LIMIT 1",
                ["EDTRK-{$year}-%"]
            );

            $nextNum = 1;
            if ($lastCert) {
                $parts = explode('-', $lastCert['certificate_number']);
                $nextNum = ((int)end($parts)) + 1;
            }

            $certificateNumber = sprintf('EDTRK-%s-%06d', $year, $nextNum);
            $verificationCode = 'VRF-' . $nextNum . '-' . strtoupper(substr(md5(uniqid()), 0, 8));

            // Create certificate
            $certificateId = $db->insert('certificates', [
                'enrollment_id' => $input['enrollment_id'],
                'certificate_number' => $certificateNumber,
                'issued_date' => date('Y-m-d'),
                'verification_code' => $verificationCode,
                'is_verified' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Update enrollment status to completed
            $db->update(
                'enrollments',
                [
                    'enrollment_status' => 'Completed',
                    'completion_date' => date('Y-m-d'),
                    'progress' => 100
                ],
                'id = ?',
                [$input['enrollment_id']]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Certificate issued successfully',
                'data' => [
                    'id' => $certificateId,
                    'certificate_number' => $certificateNumber,
                    'verification_code' => $verificationCode
                ]
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
