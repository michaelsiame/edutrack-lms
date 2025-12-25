<?php
/**
 * Lesson Resources API
 * Handles uploading and managing lesson resources (PDFs, documents, etc.)
 */

require_once '../../config/config.php';
require_once BASE_PATH . '/src/classes/Database.php';
require_once BASE_PATH . '/src/classes/Auth.php';
require_once BASE_PATH . '/src/classes/LessonResource.php';
require_once BASE_PATH . '/src/classes/Lesson.php';
require_once BASE_PATH . '/src/classes/FileUpload.php';

header('Content-Type: application/json');

// Check authentication
if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = Auth::user();
$method = $_SERVER['REQUEST_METHOD'];

// =====================================================
// GET - Retrieve resources for a lesson
// =====================================================
if ($method === 'GET') {
    if (!isset($_GET['lesson_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Lesson ID required']);
        exit;
    }

    $lessonId = (int)$_GET['lesson_id'];
    $resources = LessonResource::getByLesson($lessonId);

    echo json_encode([
        'success' => true,
        'resources' => $resources
    ]);
    exit;
}

// =====================================================
// POST - Upload new resource
// =====================================================
if ($method === 'POST') {
    // Check if instructor or admin
    if ($user['role'] !== 'instructor' && $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only instructors can upload resources']);
        exit;
    }

    $lessonId = $_POST['lesson_id'] ?? null;
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $resourceType = $_POST['resource_type'] ?? 'Other';
    $uploadType = $_POST['upload_type'] ?? 'file'; // 'file' or 'url'

    if (!$lessonId || !$title) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Lesson ID and title are required']);
        exit;
    }

    // Verify lesson exists and instructor has permission
    $lesson = Lesson::find($lessonId);
    if (!$lesson) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Lesson not found']);
        exit;
    }

    // Handle URL-based resource
    if ($uploadType === 'url') {
        $fileUrl = $_POST['file_url'] ?? null;

        if (!$fileUrl) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File URL is required']);
            exit;
        }

        // Validate URL
        if (!filter_var($fileUrl, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid URL']);
            exit;
        }

        $resourceId = LessonResource::create([
            'lesson_id' => $lessonId,
            'title' => $title,
            'description' => $description,
            'resource_type' => $resourceType,
            'file_url' => $fileUrl,
            'file_size_kb' => null
        ]);

        if ($resourceId) {
            echo json_encode([
                'success' => true,
                'message' => 'Resource added successfully',
                'resource_id' => $resourceId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add resource']);
        }
        exit;
    }

    // Handle file upload
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        exit;
    }

    // Determine upload directory based on resource type
    $uploadSubDir = 'courses/lessons/resources';

    try {
        $fileUpload = new FileUpload($_FILES['file'], $uploadSubDir);

        // Set allowed file types based on resource type
        $allowedMimes = [
            'PDF' => ['application/pdf'],
            'Document' => [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain'
            ],
            'Spreadsheet' => [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ],
            'Presentation' => [
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ],
            'Video' => ['video/mp4', 'video/mpeg', 'video/quicktime'],
            'Audio' => ['audio/mpeg', 'audio/wav', 'audio/mp3'],
            'Archive' => ['application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed']
        ];

        // Set max file size (default 50MB, 100MB for videos)
        $maxSize = ($resourceType === 'Video') ? 100 : 50;
        $fileUpload->setMaxFileSize($maxSize * 1024 * 1024);

        // Upload file
        $result = $fileUpload->upload();

        if ($result['success']) {
            // Get file size in KB
            $fileSizeKb = round($result['size'] / 1024);

            // Create resource record
            $resourceId = LessonResource::create([
                'lesson_id' => $lessonId,
                'title' => $title,
                'description' => $description,
                'resource_type' => $resourceType,
                'file_url' => $result['filepath'],
                'file_size_kb' => $fileSizeKb
            ]);

            if ($resourceId) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Resource uploaded successfully',
                    'resource_id' => $resourceId,
                    'file_info' => [
                        'filename' => $result['filename'],
                        'size' => $fileSizeKb . ' KB',
                        'type' => $resourceType
                    ]
                ]);
            } else {
                // Delete uploaded file if database insert failed
                if (file_exists(UPLOAD_DIR . '/' . $result['filepath'])) {
                    unlink(UPLOAD_DIR . '/' . $result['filepath']);
                }
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save resource']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $result['error']]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Upload error: ' . $e->getMessage()]);
    }
    exit;
}

// =====================================================
// DELETE - Remove resource
// =====================================================
if ($method === 'DELETE') {
    // Check if instructor or admin
    if ($user['role'] !== 'instructor' && $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only instructors can delete resources']);
        exit;
    }

    // Get resource ID from query string or request body
    parse_str(file_get_contents("php://input"), $data);
    $resourceId = $_GET['id'] ?? $data['id'] ?? null;

    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Resource ID required']);
        exit;
    }

    $resource = LessonResource::find($resourceId);
    if (!$resource) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Resource not found']);
        exit;
    }

    if ($resource->delete()) {
        echo json_encode([
            'success' => true,
            'message' => 'Resource deleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete resource']);
    }
    exit;
}

// Invalid method
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
