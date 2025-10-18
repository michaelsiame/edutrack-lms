<?php
/**
 * Edutrack Computer Training College
 * Logout Handler
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/auth.php';

// Logout user
logoutUser();

// Set flash message
flash('success', 'You have been logged out successfully.', 'success');

// Redirect to login
redirect(url('login.php'));