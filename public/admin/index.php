<?php
/**
 * Admin Dashboard Entry Point
 * Routes to production build (dist/) if available, or development index.html
 */

// Check if user is logged in and is admin
require_once '../../src/bootstrap.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isLoggedIn()) {
    flash('error', 'Please login to access the admin dashboard.', 'warning');
    redirect(url('login.php'));
    exit;
}

// Check admin role
require_once '../../src/includes/access-control.php';
if (!hasRole('admin')) {
    accessDenied('admin', 'You must be an administrator to access the admin dashboard.');
}

// Check if production build exists
$distPath = __DIR__ . '/dist/index.html';
if (file_exists($distPath)) {
    // Production mode - redirect to built version
    header('Location: /admin/dist/index.html');
    exit;
}

// Development mode - serve the root index.html
$devPath = __DIR__ . '/index.html';
if (file_exists($devPath)) {
    readfile($devPath);
    exit;
}

// Neither exists - show error
http_response_code(500);
echo '<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Build Required</title>
    <style>
        body { font-family: sans-serif; padding: 40px; max-width: 600px; margin: 0 auto; }
        .error { background: #fee; border: 1px solid #fcc; padding: 20px; border-radius: 8px; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="error">
        <h1>⚠️ Admin Dashboard Not Built</h1>
        <p>The admin dashboard needs to be built before it can be accessed.</p>
        <h3>To build the admin dashboard:</h3>
        <ol>
            <li>Navigate to the admin directory: <code>cd public/admin</code></li>
            <li>Install dependencies: <code>npm install</code></li>
            <li>Build the app: <code>npm run build</code></li>
        </ol>
        <p>After building, the dashboard will be available at this URL.</p>
    </div>
</body>
</html>';
exit;