<?php
/**
 * Edutrack Computer Training College
 * Instructor Only Middleware
 *
 * Require user to be instructor or admin
 */

// First check authentication
require_once dirname(__FILE__) . '/authenticate.php';

// Load access control functions
require_once dirname(__DIR__) . '/includes/access-control.php';

// Check if user is instructor or admin
if (!hasRole(['instructor', 'admin'])) {
    accessDenied('instructor');
}