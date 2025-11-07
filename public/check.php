<?php
/**
 * Health Check Endpoint
 * Used by Railway to verify the service is running
 */

// Simple health check - just return OK
http_response_code(200);
header('Content-Type: application/json');

$status = [
    'status' => 'ok',
    'timestamp' => time(),
    'service' => 'edutrack-lms'
];

// Optional: Check if critical files exist
$criticalFiles = [
    __DIR__ . '/index.php',
    __DIR__ . '/../src/config.php'
];

$allFilesExist = true;
foreach ($criticalFiles as $file) {
    if (!file_exists($file)) {
        $allFilesExist = false;
        $status['status'] = 'degraded';
        $status['missing_files'][] = basename($file);
    }
}

// Don't check database in health check to avoid delays
// Just verify the service can respond

echo json_encode($status);
exit(0);
