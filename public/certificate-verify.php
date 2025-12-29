<?php
/**
 * Certificate Verification Page
 * Redirects to main verification page
 */

require_once '../src/bootstrap.php';

// Forward any verification code to the main page
$code = $_GET['code'] ?? '';

if (!empty($code)) {
    redirect('verify-certificate.php?code=' . urlencode($code));
} else {
    redirect('verify-certificate.php');
}
