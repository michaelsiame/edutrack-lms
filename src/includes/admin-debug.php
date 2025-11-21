<?php
/**
 * Admin Debug Helper
 * Include this file at the top of admin pages to enable comprehensive debugging
 */

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
 * Writes to both file and HTML comments
 */
if (!function_exists('debugLog')) {
    function debugLog($message, $data = null) {
        $logFile = __DIR__ . '/../../logs/admin_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message";

        if ($data !== null) {
            $logMessage .= "\n" . print_r($data, true);
        }
        $logMessage .= "\n";

        file_put_contents($logFile, $logMessage, FILE_APPEND);

        // Also echo to browser for immediate feedback
        echo "<!-- DEBUG: $message -->\n";
        if ($data !== null) {
            echo "<!-- DATA: " . htmlspecialchars(print_r($data, true)) . " -->\n";
        }
    }
}

/**
 * Exception handler for uncaught exceptions
 */
set_exception_handler(function($e) {
    debugLog("FATAL ERROR: " . $e->getMessage());
    debugLog("Stack trace", $e->getTraceAsString());

    // Display error to user
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

// Log page load
$currentPage = $_SERVER['PHP_SELF'] ?? 'unknown';
debugLog("=== PAGE LOAD: $currentPage ===");
