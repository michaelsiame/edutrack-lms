<?php
/**
 * Security Headers
 * Sets important HTTP security headers to protect against common web vulnerabilities
 *
 * Include this file early in bootstrap.php or at the start of each page
 */

// Prevent clickjacking attacks
header('X-Frame-Options: DENY');

// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Enable XSS protection in browsers
header('X-XSS-Protection: 1; mode=block');

// Referrer Policy - don't leak referrer info
header('Referrer-Policy: strict-origin-when-cross-origin');

// Permissions Policy - restrict browser features
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Content Security Policy (CSP) - Restrict resource loading
// Adjust this based on your actual resource needs
$cspDirectives = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tailwindcss.com",
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
    "img-src 'self' data: https: http:",
    "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
    "connect-src 'self'",
    "frame-ancestors 'none'",
    "base-uri 'self'",
    "form-action 'self'"
];

header('Content-Security-Policy: ' . implode('; ', $cspDirectives));

// Strict Transport Security - Force HTTPS (only set if using HTTPS)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    // max-age=31536000 = 1 year
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Additional security: Disable caching for sensitive pages
// Remove this or customize per-page as needed
if (defined('DISABLE_CACHE') && DISABLE_CACHE === true) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Set secure cookie parameters programmatically
if (PHP_VERSION_ID >= 70300) {
    // PHP 7.3+ supports array syntax
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
} else {
    // Fallback for older PHP versions
    session_set_cookie_params(
        0, // lifetime
        '/', // path
        $_SERVER['HTTP_HOST'] ?? '', // domain
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // secure
        true // httponly
    );
}
