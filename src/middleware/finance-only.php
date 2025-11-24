<?php
/**
 * Finance/Accountant Only Middleware
 * Require user to be finance staff or admin
 */

require_once dirname(__FILE__) . '/authenticate.php';
require_once dirname(__DIR__) . '/includes/access-control.php';

// Check if user is finance or admin
if (!hasRole(['finance', 'admin'])) {
    accessDenied('permission', 'This page is restricted to Finance staff and Administrators only.');
}
