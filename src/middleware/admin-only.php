<?php
/**
 * Edutrack Computer Training College
 * Admin Only Middleware
 *
 * Require user to be admin
 */

// First check authentication
require_once dirname(__FILE__) . '/authenticate.php';

// Load access control functions
require_once dirname(__DIR__) . '/includes/access-control.php';

// Check if user is admin
if (!hasRole('admin')) {
    accessDenied('admin');
}