<?php
/**
 * Security Headers
 * Sets important HTTP security headers to protect against common web vulnerabilities
 *
 * Include this file early in bootstrap.php or at the start of each page
 */

// Skip headers in CLI mode or when explicitly disabled
if (php_sapi_name() === 'cli' || (defined('SKIP_SECURITY_HEADERS') && SKIP_SECURITY_HEADERS) || headers_sent()) {
    return;
}

// Prevent clickjacking attacks
// DENY is safe for your main site (prevents others from embedding YOUR site)
header('X-Frame-Options: DENY');

// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Enable XSS protection
header('X-XSS-Protection: 1; mode=block');

// Referrer Policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// --------------------------------------------------------------------------
// CORRECTION: Permissions Policy
// --------------------------------------------------------------------------
// Original code disabled Camera/Microphone, which BREAKS Jitsi Live Lessons.
// We must allow them for 'self' (your site) and the Jitsi domain.
header('Permissions-Policy: geolocation=(), microphone=(self "https://meet.jit.si"), camera=(self "https://meet.jit.si")');

// --------------------------------------------------------------------------
// CORRECTION: Content Security Policy (CSP)
// --------------------------------------------------------------------------
// We need to allow YouTube, Vimeo, and Jitsi to load inside your site.
$cspDirectives = [
    "default-src 'self'",
    
    // Script Src: Added meet.jit.si for live lessons
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tailwindcss.com https://meet.jit.si",
    
    // Style Src: Standard CDNs
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
    
    // Img Src: Allow images from anywhere (often needed for user avatars/course thumbnails)
    "img-src 'self' data: https: http:",
    
    // Font Src: Google Fonts
    "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
    
    // Connect Src: Allow AJAX to self and Jitsi
    "connect-src 'self' https://meet.jit.si",
    
    // Frame Src: CRITICAL for YouTube, Vimeo, and Jitsi integration
    "frame-src 'self' https://www.youtube.com https://player.vimeo.com https://meet.jit.si",
    
    // Prevent your site from being embedded elsewhere
    "frame-ancestors 'none'",
    
    "base-uri 'self'",
    "form-action 'self'"
];

header('Content-Security-Policy: ' . implode('; ', $cspDirectives));

// Strict Transport Security - Force HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Disable caching for sensitive pages if requested
if (defined('DISABLE_CACHE') && constant('DISABLE_CACHE') === true) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Set secure cookie parameters
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '', 
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
} else {
    session_set_cookie_params(
        0, 
        '/', 
        '', 
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', 
        true
    );
}