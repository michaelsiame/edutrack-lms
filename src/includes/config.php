<?php
/**
 * Edutrack Computer Training College
 * Configuration Loader and Global Constants
 */

// Prevent direct access
if (!defined('EDUTRACK_INIT')) {
    define('EDUTRACK_INIT', true);
}

// NOTE: session_start() has been moved down

// Define base paths
define('ROOT_PATH', dirname(__DIR__, 2));
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Load environment variables
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) { continue; }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Load application config
$appConfig = require CONFIG_PATH . '/app.php';

// Define application constants
define('APP_NAME', $appConfig['name']);
define('APP_URL', $appConfig['url']);
define('APP_ENV', $appConfig['env']);
define('APP_DEBUG', $appConfig['debug']);
define('APP_TIMEZONE', $appConfig['timezone']);

// Brand colors
define('PRIMARY_COLOR', $appConfig['colors']['primary']);
define('SECONDARY_COLOR', $appConfig['colors']['secondary']);
define('SUCCESS_COLOR', $appConfig['colors']['success']);
define('DANGER_COLOR', $appConfig['colors']['danger']);
define('WARNING_COLOR', $appConfig['colors']['warning']);
define('INFO_COLOR', $appConfig['colors']['info']);

// TEVETA constants
define('TEVETA_CODE', $appConfig['teveta']['institution_code']);
define('TEVETA_NAME', $appConfig['teveta']['institution_name']);
define('TEVETA_VERIFIED', $appConfig['teveta']['verified']);

// Site information
define('SITE_EMAIL', $appConfig['site']['email']);
define('SITE_PHONE', $appConfig['site']['phone']);
define('SITE_ADDRESS', $appConfig['site']['address']);
define('CURRENCY', $appConfig['site']['currency']);
define('CURRENCY_SYMBOL', $appConfig['site']['currency_symbol']);

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set PHP configuration
ini_set('upload_max_filesize', $appConfig['upload']['max_size_mb'] . 'M');
ini_set('post_max_size', ($appConfig['upload']['max_size_mb'] + 2) . 'M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

// Session configuration
ini_set('session.cookie_httponly', $appConfig['session']['httponly']);
ini_set('session.cookie_secure', $appConfig['session']['secure']);
ini_set('session.cookie_samesite', $appConfig['session']['samesite']);
ini_set('session.gc_maxlifetime', $appConfig['session']['lifetime']);

// Configure session save path
$sessionPath = STORAGE_PATH . '/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
}
ini_set('session.save_path', $sessionPath);

// --- CORRECTED SECTION ---
// Now that all session settings are configured, start the session.
if (session_status() === PHP_SESSION_NONE) {
    session_name($appConfig['session']['name']); // It's good practice to set a custom session name
    session_start();
}
// --- END OF CORRECTION ---

/**
 * Get configuration value
 * 
 * @param string $key Configuration key (dot notation supported)
 * @param mixed $default Default value if not found
 * @return mixed
 */
function config($key, $default = null) {
    global $appConfig;
    $keys = explode('.', $key);
    $value = $appConfig;
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    return $value;
}

/**
 * Get environment variable
 * 
 * @param string $key Environment variable name
 * @param mixed $default Default value if not found
 * @return mixed
 */
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) { return $default; }
    if (strtolower($value) === 'true' || $value === '1') { return true; }
    if (strtolower($value) === 'false' || $value === '0') { return false; }
    return $value;
}

/**
 * Get full URL path
 * 
 * @param string $path Path to append
 * @return string
 */
function url($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Get asset URL
 * 
 * @param string $path Asset path
 * @return string
 */
function asset($path) {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Get upload URL
 * 
 * @param string $path Upload path
 * @return string
 */
function uploadUrl($path) {
    return url('uploads/' . ltrim($path, '/'));
}

/**
 * Redirect to URL
 * 
 * @param string $url URL to redirect to
 * @param int $code HTTP status code
 */
function redirect($url, $code = 302) {
    header("Location: $url", true, $code);
    exit;
}

/**
 * Redirect back to previous page
 */
function redirectBack() {
    $referer = $_SERVER['HTTP_REFERER'] ?? url();
    redirect($referer);
}

/**
 * Check if maintenance mode is enabled
 * 
 * @return bool
 */
function isMaintenanceMode() {
    return config('maintenance.enabled', false);
}

/**
 * Check if user is admin (bypass maintenance)
 * 
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Display maintenance page if enabled
 */
function checkMaintenanceMode() {
    if (isMaintenanceMode() && !isAdmin()) {
        http_response_code(503);
        include PUBLIC_PATH . '/maintenance.php';
        exit;
    }
}

// Auto-load required files
require_once SRC_PATH . '/includes/functions.php';
require_once SRC_PATH . '/includes/security.php';
require_once SRC_PATH . '/includes/validation.php';

// Make configuration globally available
$GLOBALS['config'] = $appConfig;