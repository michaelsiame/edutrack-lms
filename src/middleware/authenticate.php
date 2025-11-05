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
require_once dirname(__DIR__) . '/includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isLoggedIn()) {
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
    flash('error', 'Your session has expired. Please login again.', 'warning');
    redirect(url('login.php'));
    exit;
}