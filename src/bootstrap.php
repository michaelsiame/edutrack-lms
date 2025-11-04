<?php
/**
 * Application Bootstrap
 * Central initialization file that loads all core dependencies
 *
 * Usage:
 *   require_once '../src/bootstrap.php';
 *
 * This replaces the need for individual require statements:
 *   - config.php
 *   - database.php
 *   - auth.php
 *   - functions.php
 *   - helpers.php
 *   - security.php
 *   - validation.php
 */

// Prevent direct access
if (!defined('APP_BOOTSTRAPPED')) {
    define('APP_BOOTSTRAPPED', true);
}

// Define base paths if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

if (!defined('SRC_PATH')) {
    define('SRC_PATH', ROOT_PATH . '/src');
}

if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . '/public');
}

if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', ROOT_PATH . '/config');
}

// Set security headers FIRST (before any output)
require_once SRC_PATH . '/includes/security-headers.php';

// Load core includes in order of dependency
require_once SRC_PATH . '/includes/config.php';      // Configuration & constants
require_once SRC_PATH . '/includes/database.php';    // Database connection
require_once SRC_PATH . '/includes/security.php';    // Security functions (CSRF, etc.)
require_once SRC_PATH . '/includes/validation.php';  // Input validation
require_once SRC_PATH . '/includes/functions.php';   // Core helper functions
require_once SRC_PATH . '/includes/helpers.php';     // Additional helpers
require_once SRC_PATH . '/includes/auth.php';        // Authentication functions
require_once SRC_PATH . '/includes/email.php';       // Email helper functions
require_once SRC_PATH . '/templates/alerts.php';     // Alert & notification components

// Auto-load commonly used classes
spl_autoload_register(function ($class) {
    $classFile = SRC_PATH . '/classes/' . $class . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default timezone (APP_TIMEZONE is defined in config.php)
date_default_timezone_set(APP_TIMEZONE ?? 'UTC');

// Set error reporting based on environment
if (APP_ENV === 'production') {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT_PATH . '/storage/logs/php-errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Set memory and execution limits
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');

// Initialize database connection (singleton)
$db = Database::getInstance();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate existing session
if (isset($_SESSION['user_id'])) {
    validateSession();
}

// Log application bootstrap (only in debug mode)
if (APP_DEBUG) {
    error_log('Application bootstrapped at ' . date('Y-m-d H:i:s'));
}
