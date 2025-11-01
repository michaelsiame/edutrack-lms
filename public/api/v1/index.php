<?php
/**
 * API v1 Index
 * Lists available v1 endpoints
 */

header('Content-Type: application/json');

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/api/v1";

echo json_encode([
    'version' => '1.0.0',
    'api' => 'Edutrack LMS API',
    'documentation' => $baseUrl . '/docs',
    'endpoints' => [
        'auth' => [
            'url' => $baseUrl . '/auth.php',
            'methods' => ['POST', 'GET', 'DELETE'],
            'description' => 'Authentication (login, register, token refresh)'
        ],
        'notifications' => [
            'url' => $baseUrl . '/notifications.php',
            'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'description' => 'User notifications management'
        ],
        'courses' => [
            'url' => $baseUrl . '/courses.php',
            'methods' => ['GET'],
            'description' => 'Course listing and details'
        ],
        'enrollment' => [
            'url' => $baseUrl . '/enroll.php',
            'methods' => ['POST'],
            'description' => 'Course enrollment'
        ],
        'progress' => [
            'url' => $baseUrl . '/progress.php',
            'methods' => ['GET', 'POST'],
            'description' => 'Student progress tracking'
        ],
        'quizzes' => [
            'url' => $baseUrl . '/quiz.php',
            'methods' => ['GET', 'POST'],
            'description' => 'Quiz retrieval and submission'
        ],
        'assignments' => [
            'url' => $baseUrl . '/assignment.php',
            'methods' => ['GET', 'POST'],
            'description' => 'Assignment submissions'
        ],
        'payments' => [
            'url' => $baseUrl . '/payment.php',
            'methods' => ['POST'],
            'description' => 'Payment initiation'
        ],
        'notes' => [
            'url' => $baseUrl . '/notes.php',
            'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'description' => 'Student study notes'
        ],
        'upload' => [
            'url' => $baseUrl . '/upload.php',
            'methods' => ['POST'],
            'description' => 'File uploads'
        ]
    ],
    'authentication' => [
        'type' => 'Bearer Token or Session',
        'header' => 'Authorization: Bearer {token}',
        'note' => 'Obtain token via /api/v1/auth.php login endpoint'
    ],
    'rate_limiting' => [
        'enabled' => false,
        'note' => 'Rate limiting will be implemented in future versions'
    ]
], JSON_PRETTY_PRINT);
