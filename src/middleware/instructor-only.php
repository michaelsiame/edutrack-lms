<?php
/**
 * Edutrack computer training college
 * Instructor Only Middleware
 *
 * Require user to be instructor or admin
 */

// First check authentication
require_once dirname(__FILE__) . '/authenticate.php';

// Load User class (required by hasRole() function)
require_once dirname(__DIR__) . '/classes/User.php';

// Load access control functions
require_once dirname(__DIR__) . '/includes/access-control.php';

// Check if user is instructor or admin
if (!hasRole(['instructor', 'admin'])) {
    accessDenied('instructor');
}