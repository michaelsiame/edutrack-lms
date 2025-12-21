<?php
/**
 * EduTrack LMS - Setup Verification Script
 *
 * This script checks if everything is configured correctly for the admin panel.
 * Access: https://yourdomain.com/verify-setup.php
 *
 * ⚠️ DELETE THIS FILE AFTER VERIFICATION FOR SECURITY!
 */

// Prevent running in production if not explicitly allowed
$allowVerification = true; // Set to false after verification

if (!$allowVerification && (getenv('APP_ENV') === 'production' || !getenv('APP_DEBUG'))) {
    die('Verification disabled for security. Set $allowVerification = true to enable.');
}

$results = [];
$allPassed = true;

// Helper function to add test result
function addResult($category, $test, $passed, $message, $fix = '') {
    global $results, $allPassed;
    $results[] = [
        'category' => $category,
        'test' => $test,
        'passed' => $passed,
        'message' => $message,
        'fix' => $fix
    ];
    if (!$passed) $allPassed = false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack LMS - Setup Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content { padding: 30px; }
        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            flex: 1;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-card.success { background: #d4edda; color: #155724; }
        .summary-card.warning { background: #fff3cd; color: #856404; }
        .summary-card.danger { background: #f8d7da; color: #721c24; }
        .summary-card .number { font-size: 36px; font-weight: bold; margin-bottom: 5px; }
        .summary-card .label { font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .category {
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        .category-header {
            background: #f8f9fa;
            padding: 15px 20px;
            font-weight: bold;
            border-bottom: 1px solid #e0e0e0;
            font-size: 16px;
        }
        .test-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        .test-item:last-child { border-bottom: none; }
        .test-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        .test-icon.pass { background: #d4edda; color: #155724; }
        .test-icon.fail { background: #f8d7da; color: #721c24; }
        .test-details { flex: 1; }
        .test-name { font-weight: 600; margin-bottom: 5px; }
        .test-message { color: #666; font-size: 14px; margin-bottom: 5px; }
        .test-fix {
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            padding: 10px;
            margin-top: 10px;
            font-size: 13px;
            border-radius: 4px;
        }
        .test-fix strong { display: block; margin-bottom: 5px; color: #856404; }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .warning-box strong { color: #856404; display: block; margin-bottom: 10px; font-size: 18px; }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 EduTrack LMS - Setup Verification</h1>
            <p>Checking if everything is configured correctly...</p>
        </div>

        <div class="content">
            <div class="warning-box">
                <strong>⚠️ Security Warning</strong>
                Delete this file (<code>verify-setup.php</code>) after verification to prevent unauthorized access to system information.
            </div>

<?php

// ============================================
// 1. FILE STRUCTURE TESTS
// ============================================
addResult('File Structure', 'Bootstrap file exists',
    file_exists('../src/bootstrap.php'),
    'src/bootstrap.php found',
    'Ensure all source files are uploaded correctly'
);

addResult('File Structure', 'Database class exists',
    file_exists('../src/includes/database.php'),
    'src/includes/database.php found',
    'Upload missing source files'
);

addResult('File Structure', 'Environment file exists',
    file_exists('../.env'),
    '.env configuration file found',
    'Copy .env.example to .env and configure it'
);

addResult('File Structure', 'Admin panel exists',
    file_exists(__DIR__ . '/admin/index.html'),
    'Admin panel found at /public/admin/',
    'Upload admin panel files'
);

addResult('File Structure', 'API endpoints exist',
    file_exists(__DIR__ . '/api/users.php'),
    'API endpoints found in /public/api/',
    'Upload all API endpoint files'
);

// ============================================
// 2. PHP CONFIGURATION TESTS
// ============================================
addResult('PHP Configuration', 'PHP Version',
    version_compare(PHP_VERSION, '7.2.0', '>='),
    'PHP ' . PHP_VERSION . ' (requires 7.2+)',
    'Contact Hostinger to upgrade PHP version'
);

addResult('PHP Configuration', 'PDO Extension',
    extension_loaded('pdo'),
    'PDO extension loaded',
    'Enable PDO extension in PHP settings'
);

addResult('PHP Configuration', 'PDO MySQL Driver',
    extension_loaded('pdo_mysql'),
    'PDO MySQL driver loaded',
    'Enable pdo_mysql extension in PHP settings'
);

addResult('PHP Configuration', 'JSON Extension',
    extension_loaded('json'),
    'JSON extension loaded',
    'Enable JSON extension in PHP settings'
);

addResult('PHP Configuration', 'Session Support',
    function_exists('session_start'),
    'Session support available',
    'Enable session support in PHP settings'
);

// ============================================
// 3. ENVIRONMENT CONFIGURATION TESTS
// ============================================
if (file_exists('../.env')) {
    require_once '../src/bootstrap.php';

    addResult('Environment', 'APP_URL configured',
        !empty(getenv('APP_URL')),
        'APP_URL: ' . getenv('APP_URL'),
        'Set APP_URL in .env file'
    );

    addResult('Environment', 'Database host configured',
        !empty(getenv('DB_HOST')),
        'DB_HOST: ' . getenv('DB_HOST'),
        'Set DB_HOST in .env file (should be "localhost" on Hostinger)'
    );

    addResult('Environment', 'Database name configured',
        !empty(getenv('DB_NAME')),
        'DB_NAME: ' . getenv('DB_NAME'),
        'Set DB_NAME in .env file'
    );

    addResult('Environment', 'APP_ENV set to production',
        getenv('APP_ENV') === 'production',
        'APP_ENV: ' . getenv('APP_ENV') . ' (should be "production" for live site)',
        'Set APP_ENV=production in .env for production'
    );

    addResult('Environment', 'APP_DEBUG disabled',
        getenv('APP_DEBUG') === 'false' || getenv('APP_DEBUG') === false,
        'APP_DEBUG: ' . (getenv('APP_DEBUG') ? 'true' : 'false') . ' (should be false for production)',
        'Set APP_DEBUG=false in .env for production'
    );
}

// ============================================
// 4. DATABASE CONNECTION TESTS
// ============================================
if (file_exists('../src/bootstrap.php')) {
    try {
        require_once '../src/bootstrap.php';
        $db = Database::getInstance();

        addResult('Database', 'Connection successful',
            true,
            'Successfully connected to database',
            ''
        );

        // Test if users table exists
        try {
            $userCount = $db->count('users');
            addResult('Database', 'Users table exists',
                true,
                "Users table found with {$userCount} records",
                ''
            );
        } catch (Exception $e) {
            addResult('Database', 'Users table exists',
                false,
                'Users table not found',
                'Import database/complete_lms_schema.sql via phpMyAdmin'
            );
        }

        // Test if courses table exists
        try {
            $courseCount = $db->count('courses');
            addResult('Database', 'Courses table exists',
                true,
                "Courses table found with {$courseCount} records",
                ''
            );
        } catch (Exception $e) {
            addResult('Database', 'Courses table exists',
                false,
                'Courses table not found',
                'Import database/complete_lms_schema.sql via phpMyAdmin'
            );
        }

        // Check for admin user
        try {
            $adminCount = $db->fetchColumn(
                "SELECT COUNT(*) FROM users u
                 INNER JOIN user_roles ur ON u.id = ur.user_id
                 WHERE ur.role_id = 1"
            );
            addResult('Database', 'Admin user exists',
                $adminCount > 0,
                "Found {$adminCount} admin user(s)",
                'Create an admin user - see HOSTINGER_DEPLOYMENT.md Step 7'
            );
        } catch (Exception $e) {
            addResult('Database', 'Admin user check',
                false,
                'Could not check for admin users: ' . $e->getMessage(),
                ''
            );
        }

    } catch (Exception $e) {
        addResult('Database', 'Connection failed',
            false,
            'Database connection error: ' . $e->getMessage(),
            'Check database credentials in .env file. Ensure DB_HOST=localhost, DB_NAME, DB_USER, and DB_PASS are correct'
        );
    }
}

// ============================================
// 5. FILE PERMISSIONS TESTS
// ============================================
$storageWritable = is_writable('../storage');
addResult('Permissions', 'Storage directory writable',
    $storageWritable,
    $storageWritable ? 'Storage directory is writable' : 'Storage directory not writable',
    'Run: chmod -R 775 storage/'
);

$logsWritable = is_writable('../storage/logs');
addResult('Permissions', 'Logs directory writable',
    $logsWritable,
    $logsWritable ? 'Logs directory is writable' : 'Logs directory not writable',
    'Run: chmod -R 775 storage/logs/'
);

$uploadsWritable = is_writable(__DIR__ . '/uploads');
addResult('Permissions', 'Uploads directory writable',
    $uploadsWritable,
    $uploadsWritable ? 'Uploads directory is writable' : 'Uploads directory not writable',
    'Run: chmod -R 775 public/uploads/'
);

// ============================================
// 6. SECURITY TESTS
// ============================================
addResult('Security', 'HTTPS enabled',
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443,
    'Site is using HTTPS',
    'Enable SSL certificate in Hostinger control panel'
);

addResult('Security', '.env not publicly accessible',
    !file_exists(__DIR__ . '/.env'),
    '.env file not in public directory (correct)',
    'Move .env file to parent directory (above public/)'
);

// ============================================
// DISPLAY RESULTS
// ============================================

// Count results
$passed = count(array_filter($results, function($r) { return $r['passed']; }));
$failed = count(array_filter($results, function($r) { return !$r['passed']; }));
$total = count($results);

?>
            <div class="summary">
                <div class="summary-card <?php echo $failed == 0 ? 'success' : 'danger'; ?>">
                    <div class="number"><?php echo $total; ?></div>
                    <div class="label">Total Tests</div>
                </div>
                <div class="summary-card success">
                    <div class="number"><?php echo $passed; ?></div>
                    <div class="label">Passed</div>
                </div>
                <div class="summary-card <?php echo $failed > 0 ? 'danger' : 'success'; ?>">
                    <div class="number"><?php echo $failed; ?></div>
                    <div class="label">Failed</div>
                </div>
            </div>

<?php
// Group results by category
$grouped = [];
foreach ($results as $result) {
    $grouped[$result['category']][] = $result;
}

// Display each category
foreach ($grouped as $category => $tests) {
    echo '<div class="category">';
    echo '<div class="category-header">' . htmlspecialchars($category) . '</div>';

    foreach ($tests as $test) {
        $icon = $test['passed'] ? '✓' : '✗';
        $iconClass = $test['passed'] ? 'pass' : 'fail';

        echo '<div class="test-item">';
        echo '<div class="test-icon ' . $iconClass . '">' . $icon . '</div>';
        echo '<div class="test-details">';
        echo '<div class="test-name">' . htmlspecialchars($test['test']) . '</div>';
        echo '<div class="test-message">' . htmlspecialchars($test['message']) . '</div>';

        if (!$test['passed'] && !empty($test['fix'])) {
            echo '<div class="test-fix"><strong>How to fix:</strong>' . htmlspecialchars($test['fix']) . '</div>';
        }

        echo '</div>';
        echo '</div>';
    }

    echo '</div>';
}
?>

            <?php if ($allPassed): ?>
            <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; text-align: center; margin-top: 30px;">
                <strong style="color: #155724; font-size: 24px; display: block; margin-bottom: 10px;">🎉 All Tests Passed!</strong>
                <p style="color: #155724; margin-bottom: 15px;">Your EduTrack LMS is correctly configured and ready to use.</p>
                <p style="color: #155724;"><strong>Next Steps:</strong></p>
                <ol style="text-align: left; max-width: 600px; margin: 15px auto; color: #155724;">
                    <li style="margin: 10px 0;">Access admin panel: <code><?php echo getenv('APP_URL'); ?>/admin/</code></li>
                    <li style="margin: 10px 0;">Login with your admin credentials</li>
                    <li style="margin: 10px 0;"><strong>DELETE this file (verify-setup.php) for security!</strong></li>
                    <li style="margin: 10px 0;">Set APP_DEBUG=false and APP_ENV=production in .env</li>
                </ol>
            </div>
            <?php else: ?>
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; text-align: center; margin-top: 30px;">
                <strong style="color: #721c24; font-size: 20px; display: block; margin-bottom: 10px;">⚠️ Some Tests Failed</strong>
                <p style="color: #721c24;">Please fix the issues above before proceeding.</p>
                <p style="color: #721c24; margin-top: 15px;">See <code>HOSTINGER_DEPLOYMENT.md</code> for detailed instructions.</p>
            </div>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 1px solid #e0e0e0; color: #666;">
                <p>For help, see: <code>HOSTINGER_DEPLOYMENT.md</code></p>
                <p style="margin-top: 10px; font-size: 14px;">EduTrack LMS Admin Panel Database Integration</p>
            </div>
        </div>
    </div>
</body>
</html>
