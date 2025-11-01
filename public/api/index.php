<?php
/**
 * API Router / Version Manager
 * Handles API versioning and routing
 */

header('Content-Type: application/json');

// Get requested path
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// Parse the request
$path = str_replace($scriptName, '', $requestUri);
$path = trim($path, '/');
$segments = explode('/', $path);

// Check for version in URL
if (!empty($segments[1]) && preg_match('/^v\d+$/', $segments[1])) {
    $version = $segments[1];
    $endpoint = $segments[2] ?? '';
} else {
    // No version specified - default to latest (v1)
    $version = 'v1';
    $endpoint = $segments[1] ?? '';
}

// Available versions
$availableVersions = ['v1'];

// Version info
if ($path === 'api' || $path === '') {
    echo json_encode([
        'name' => 'Edutrack LMS API',
        'version' => 'latest',
        'current_version' => 'v1',
        'available_versions' => $availableVersions,
        'endpoints' => [
            'v1' => '/api/v1/',
        ],
        'documentation' => '/api/v1/',
        'authentication' => 'Bearer Token or Session-based',
        'support' => 'contact@edutrack.ac.zm'
    ], JSON_PRETTY_PRINT);
    exit;
}

// Route to version-specific handler if exists
$versionPath = __DIR__ . '/' . $version . '/index.php';

if (file_exists($versionPath) && in_array($version, $availableVersions)) {
    require $versionPath;
} else {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'API version not found',
        'requested_version' => $version,
        'available_versions' => $availableVersions,
        'message' => "The requested API version '{$version}' does not exist. Please use one of the available versions."
    ], JSON_PRETTY_PRINT);
}
