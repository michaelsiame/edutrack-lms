<?php
/**
 * Edutrack computer training college
 * Authentication Middleware
 * 
 * Require user to be logged in
 */

// Load security headers first (sets session cookie params)
require_once dirname(__DIR__) . '/includes/security-headers.php';

// Load dependencies
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/security.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isLoggedIn()) {
    // Check if this is an AJAX/API request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Authentication Required',
            'message' => 'Please login to access this resource.'
        ]);
        exit;
    }

    // Store current URL for redirect after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    // Flash message
    flash('error', 'Please login to access this page', 'warning');

    // Redirect to login
    redirect(url('login.php'));
    exit;
}

// Validate session
if (!validateSession()) {
    // Check if this is an AJAX/API request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Session Expired',
            'message' => 'Your session has expired. Please login again.'
        ]);
        exit;
    }

    flash('error', 'Your session has expired. Please login again.', 'warning');
    redirect(url('login.php'));
    exit;
}