<?php
/**
 * Admin Debug Page
 * Shows system status and helps diagnose issues
 */

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Admin System Debug</h1>";
echo "<style>body{font-family:sans-serif;padding:20px} .ok{color:green} .error{color:red} pre{background:#f5f5f5;padding:10px;border-radius:5px}</style>";

echo "<h2>1. Bootstrap Test</h2>";
try {
    require_once '../../src/bootstrap.php';
    echo "<p class='ok'>✓ Bootstrap loaded successfully</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Bootstrap failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

echo "<h2>2. Database Connection</h2>";
try {
    $test = $db->fetchOne("SELECT 1 as test");
    echo "<p class='ok'>✓ Database connection working</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Check your database credentials in config/database.php</p>";
    exit;
}

echo "<h2>3. Required Tables Check</h2>";
$requiredTables = ['users', 'courses', 'enrollments', 'payments', 'categories', 'course_modules', 'lessons'];
$missingTables = [];

foreach ($requiredTables as $table) {
    try {
        $exists = $db->fetchAll("SHOW TABLES LIKE '$table'");
        if (empty($exists)) {
            $missingTables[] = $table;
            echo "<p class='error'>✗ Table '$table' is MISSING</p>";
        } else {
            echo "<p class='ok'>✓ Table '$table' exists</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error checking table '$table': " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

if (!empty($missingTables)) {
    echo "<h2 style='color:red'>⚠️ CRITICAL: Missing Tables</h2>";
    echo "<p>The following tables are missing:</p>";
    echo "<ul>";
    foreach ($missingTables as $table) {
        echo "<li><strong>$table</strong></li>";
    }
    echo "</ul>";

    if (in_array('categories', $missingTables)) {
        echo "<div style='background:#fff3cd;border:2px solid #ffc107;padding:15px;margin:20px 0;border-radius:5px'>";
        echo "<h3>⚠️ Categories Table Missing - ACTION REQUIRED</h3>";
        echo "<p><strong>This is why your admin pages are blank!</strong></p>";
        echo "<p>You MUST apply the categories migration:</p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
        echo "<li>Select the <strong>edutrack_lms</strong> database</li>";
        echo "<li>Click the <strong>SQL</strong> tab</li>";
        echo "<li>Open this file: <code>database/migrations/create_categories_table.sql</code></li>";
        echo "<li>Copy ALL the SQL content</li>";
        echo "<li>Paste it in phpMyAdmin and click <strong>Go</strong></li>";
        echo "</ol>";
        echo "</div>";
    }
}

echo "<h2>4. User Authentication</h2>";
if (isLoggedIn()) {
    $userId = $_SESSION['user_id'] ?? 'unknown';
    $userRole = $_SESSION['user_role'] ?? 'unknown';
    echo "<p class='ok'>✓ User is logged in</p>";
    echo "<p>User ID: $userId</p>";
    echo "<p>Role: $userRole</p>";

    if ($userRole !== 'admin') {
        echo "<p class='error'>✗ Current user is NOT an admin. Admin pages require admin role.</p>";
    } else {
        echo "<p class='ok'>✓ User has admin role</p>";
    }
} else {
    echo "<p class='error'>✗ No user logged in</p>";
    echo "<p>You need to be logged in as an admin to access admin pages</p>";
}

echo "<h2>5. Configuration</h2>";
echo "<pre>";
echo "APP_NAME: " . (defined('APP_NAME') ? APP_NAME : 'NOT DEFINED') . "\n";
echo "APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'NOT DEFINED') . "\n";
echo "APP_DEBUG: " . (defined('APP_DEBUG') ? (APP_DEBUG ? 'true' : 'false') : 'NOT DEFINED') . "\n";
echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "\n";
echo "</pre>";

echo "<h2>6. PHP Info</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Error Reporting: " . error_reporting() . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
echo "Session Started: " . (session_status() === PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "\n";
echo "</pre>";

if (empty($missingTables)) {
    echo "<h2 style='color:green'>✓ All Systems Ready!</h2>";
    echo "<p>If admin pages are still blank, check:</p>";
    echo "<ul>";
    echo "<li>Make sure you're logged in as an admin</li>";
    echo "<li>Check browser console for JavaScript errors</li>";
    echo "<li>Try clearing browser cache</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>Go to Admin Dashboard →</a></p>";
}
