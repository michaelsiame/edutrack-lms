<?php
/**
 * Certificates API Endpoint
 * Uses Certificate class for database operations
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';
require_once '../../src/classes/Certificate.php';
require_once '../../src/classes/Enrollment.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
            $certificates = Certificate::all();
            
            $formattedCertificates = array_map(function($cert) {
                return [
                    'id' => $cert['certificate_id'],
                    'code' => $cert['certificate_number'],
                    'student' => $cert['student_name'] ?? 'Unknown',
                    'course' => $cert['course_title'] ?? 'Unknown',
                    'date' => $cert['issued_date'],
                    'verified' => (bool)$cert['is_verified'],
                    'verification_code' => $cert['verification_code']
                ];
            }, $certificates);

            echo json_encode(['success' => true, 'data' => $formattedCertificates]);
            break;

        case 'POST':
            // Issue new certificate
            if (empty($input['enrollment_id'])) {
                throw new Exception('Enrollment ID required');
            }

            $enrollment = Enrollment::find($input['enrollment_id']);
            if (!$enrollment) {
                throw new Exception('Enrollment not found');
            }

            // Check if certificate already exists
            if (Certificate::findByEnrollment($input['enrollment_id'])) {
                throw new Exception('Certificate already exists for this enrollment');
            }

            $certificateId = Certificate::issue($input['enrollment_id']);

            if ($certificateId) {
                $certificate = Certificate::find($certificateId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Certificate issued successfully',
                    'data' => [
                        'id' => $certificateId,
                        'certificate_number' => $certificate->getCertificateNumber(),
                        'verification_code' => $certificate->getVerificationCode()
                    ]
                ]);
            } else {
                throw new Exception('Failed to issue certificate');
            }
            break;

        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
