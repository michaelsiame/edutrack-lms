<?php
/**
 * Admin Debug Helper
 * Include this file at the top of admin pages to enable comprehensive debugging
 *
 * Debug output behavior:
 * - Always logs to files (admin_debug.log, admin_errors.log)
 * - HTML comment output only in debug mode (APP_DEBUG=true or APP_ENV=development)
 * - Detailed error display only in debug mode
 *
 * In production:
 * - Errors are logged but not displayed to users
 * - HTML comments with debug info are suppressed
 * - Users see a generic error message instead of stack traces
 */

// Determine if we're in debug mode
// Check APP_DEBUG constant first (set by config), then fall back to environment variable
$isDebugMode = (defined('APP_DEBUG') && APP_DEBUG === true)
    || getenv('APP_DEBUG') === 'true'
    || getenv('APP_DEBUG') === '1'
    || getenv('APP_ENV') === 'development'
    || getenv('APP_ENV') === 'local';

// Enable error display only in debug mode
if ($isDebugMode) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}
ini_set('log_errors', 1);

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/../../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Set error log file
ini_set('error_log', $logDir . '/admin_errors.log');

/**
 * Debug logging function
 * Always writes to log file, but HTML output only in debug mode
 *
 * @param string $message Log message
 * @param mixed $data Optional data to log
 */
if (!function_exists('debugLog')) {
    function debugLog($message, $data = null) {
        // Determine debug mode (re-check in case it changed after initial load)
        $isDebug = (defined('APP_DEBUG') && APP_DEBUG === true)
            || getenv('APP_DEBUG') === 'true'
            || getenv('APP_DEBUG') === '1'
            || getenv('APP_ENV') === 'development'
            || getenv('APP_ENV') === 'local';

        $logFile = __DIR__ . '/../../logs/admin_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message";

        if ($data !== null) {
            $logMessage .= "\n" . print_r($data, true);
        }
        $logMessage .= "\n";

        // Always write to file
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        // Only output HTML comments in debug mode
        if ($isDebug) {
            echo "<!-- DEBUG: $message -->\n";
            if ($data !== null) {
                echo "<!-- DATA: " . htmlspecialchars(print_r($data, true)) . " -->\n";
            }
        }
    }
}

/**
 * Exception handler for uncaught exceptions
 * Shows detailed error in debug mode, generic message in production
 */
set_exception_handler(function($e) {
    // Determine debug mode
    $isDebug = (defined('APP_DEBUG') && APP_DEBUG === true)
        || getenv('APP_DEBUG') === 'true'
        || getenv('APP_DEBUG') === '1'
        || getenv('APP_ENV') === 'development'
        || getenv('APP_ENV') === 'local';

    // Always log the error
    debugLog("FATAL ERROR: " . $e->getMessage());
    debugLog("Stack trace", $e->getTraceAsString());

    if ($isDebug) {
        // Display detailed error in debug mode
        echo "<div style='background: #fee; border: 2px solid #f00; padding: 20px; margin: 20px; font-family: monospace;'>";
        echo "<h1 style='color: #c00;'>Admin Page Error</h1>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "<h3>Stack Trace:</h3>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "<h3>Debug Log Location:</h3>";
        echo "<p>" . __DIR__ . '/../../logs/admin_debug.log' . "</p>";
        echo "</div>";
    } else {
        // Display generic error in production
        http_response_code(500);
        echo "<div style='background: #f8f8f8; border: 1px solid #ddd; padding: 40px; margin: 40px auto; max-width: 500px; text-align: center; font-family: sans-serif;'>";
        echo "<h1 style='color: #333; margin-bottom: 10px;'>Something went wrong</h1>";
        echo "<p style='color: #666;'>We're sorry, but an unexpected error occurred. Our team has been notified.</p>";
        echo "<p style='margin-top: 20px;'><a href='javascript:history.back()' style='color: #2563eb;'>Go back</a></p>";
        echo "</div>";
    }
    exit;
});

/**
 * Error handler for PHP errors
 */
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED',
    ];

    $type = $errorTypes[$errno] ?? 'UNKNOWN';
    debugLog("PHP $type: $errstr in $errfile:$errline");

    // Don't execute PHP internal error handler
    return true;
});

// Log page load (only writes to file in production, HTML comments in debug)
$currentPage = $_SERVER['PHP_SELF'] ?? 'unknown';
debugLog("=== PAGE LOAD: $currentPage ===");
