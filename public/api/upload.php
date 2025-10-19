<?php
/**
 * API: File Upload
 * POST /api/upload.php - Upload files
 */

header('Content-Type: application/json');

require_once '../../src/includes/config.php';
require_once '../../src/includes/database.php';
require_once '../../src/includes/functions.php';
require_once '../../src/classes/FileUpload.php';

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRF($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$uploadType = $_POST['type'] ?? null;
$userId = $_SESSION['user_id'];

if (!$uploadType) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Upload type is required']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = 'No file uploaded';
    
    if (isset($_FILES['file']['error'])) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $errorMessage = $errors[$_FILES['file']['error']] ?? 'Unknown upload error';
    }
    
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $errorMessage]);
    exit;
}

// Handle different upload types
$allowedTypes = [];
$uploadDir = '';
$maxSize = 10 * 1024 * 1024; // 10MB default

switch ($uploadType) {
    case 'avatar':
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $uploadDir = 'users/avatars';
        $maxSize = 2 * 1024 * 1024; // 2MB
        break;
        
    case 'course_thumbnail':
        // Verify user is instructor or admin
        if (!hasRole(['instructor', 'admin'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Insufficient permissions']);
            exit;
        }
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        $uploadDir = 'courses/thumbnails';
        $maxSize = 5 * 1024 * 1024; // 5MB
        break;
        
    case 'course_resource':
        // Verify user is instructor or admin
        if (!hasRole(['instructor', 'admin'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Insufficient permissions']);
            exit;
        }
        $allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'txt'];
        $uploadDir = 'courses/resources';
        $maxSize = 20 * 1024 * 1024; // 20MB
        break;
        
    case 'assignment_submission':
        $allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'zip'];
        $uploadDir = 'assignments/submissions';
        $maxSize = 10 * 1024 * 1024; // 10MB
        break;
        
    case 'payment_proof':
        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
        $uploadDir = 'payments/proofs';
        $maxSize = 5 * 1024 * 1024; // 5MB
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid upload type']);
        exit;
}

// Perform upload
$upload = new FileUpload($_FILES['file'], $uploadDir, $allowedTypes, $maxSize);
$result = $upload->upload();

if ($result && isset($result['filepath'])) {
    // Log upload in database
    $sql = "INSERT INTO file_uploads (user_id, file_type, filename, filepath, filesize, upload_type) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $db = Database::getInstance();
    $db->query($sql, [
        $userId,
        $result['type'],
        $result['filename'],
        $result['filepath'],
        $result['size'],
        $uploadType
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully',
        'file' => [
            'filename' => $result['filename'],
            'filepath' => $result['filepath'],
            'url' => uploadUrl($result['filepath']),
            'size' => $result['size'],
            'size_formatted' => formatFileSize($result['size']),
            'type' => $result['type']
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $upload->getError() ?? 'Failed to upload file'
    ]);
}