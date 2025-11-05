<?php
/**
 * Edutrack computer training college
 * Logout Handler
 */

require_once '../src/bootstrap.php';

// Logout user
logoutUser();

// Set flash message
flash('success', 'You have been logged out successfully.', 'success');

// Redirect to login
redirect(url('login.php'));