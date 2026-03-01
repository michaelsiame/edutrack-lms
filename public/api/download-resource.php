<?php
/**
 * Download Resource
 * Handles secure downloading of lesson resources with download tracking
 */

require_once '../../config/config.php';
require_once BASE_PATH . '/src/classes/Database.php';
require_once BASE_PATH . '/src/classes/Auth.php';
require_once BASE_PATH . '/src/classes/LessonResource.php';
require_once BASE_PATH . '/src/classes/Lesson.php';
require_once BASE_PATH . '/src/classes/Course.php';

// Check authentication
if (!Auth::check()) {
    http_response_code(401);
    die('Unauthorized');
}

$user = Auth::user();
$resourceId = $_GET['id'] ?? null;

if (!$resourceId) {
    http_response_code(400);
    die('Resource ID required');
}

// Get resource
$resource = LessonResource::find($resourceId);
if (!$resource) {
    http_response_code(404);
    die('Resource not found');
}

// Get lesson and verify access
$lesson = Lesson::find($resource->getLessonId());
if (!$lesson) {
    http_response_code(404);
    die('Lesson not found');
}

// Check if user has access to this course
// Students must be enrolled, instructors/admins have access
if ($user['role'] === 'student') {
    $db = Database::getInstance();
    $sql = "SELECT id FROM enrollments
            WHERE user_id = :user_id
            AND course_id = :course_id
            AND status = 'active'";

    $enrollment = $db->query($sql, [
        'user_id' => $user['id'],
        'course_id' => $lesson->getCourseId()
    ])->fetch();

    if (!$enrollment) {
        http_response_code(403);
        die('You must be enrolled in this course to download resources');
    }
}

// External URL - redirect
if (!$resource->isLocalFile()) {
    $resource->incrementDownloadCount();
    header('Location: ' . $resource->getFileUrl());
    exit;
}

// Local file - serve for download
$filePath = $resource->getFilePath();

// Security: Prevent path traversal attacks
// Validate that the resolved path is within the allowed uploads directory
$uploadsBaseDir = realpath(BASE_PATH . '/public/uploads');
$resolvedPath = realpath($filePath);

if ($resolvedPath === false) {
    http_response_code(404);
    die('File not found on server');
}

// Ensure the resolved path is within the uploads directory
if (strpos($resolvedPath, $uploadsBaseDir) !== 0) {
    http_response_code(403);
    error_log('Path traversal attempt blocked: ' . $filePath);
    die('Access denied');
}

if (!file_exists($resolvedPath)) {
    http_response_code(404);
    die('File not found on server');
}

// Use the resolved path for all file operations
$filePath = $resolvedPath;

// Increment download count
$resource->incrementDownloadCount();

// Determine MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// Set headers for download
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . basename($resource->getTitle()) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Output file
readfile($filePath);
exit;
