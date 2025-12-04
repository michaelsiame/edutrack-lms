<?php
/**
 * EduTrack LMS - Web-based Installation Helper
 *
 * SECURITY WARNING:
 * DELETE THIS FILE IMMEDIATELY AFTER INSTALLATION!
 *
 * This file helps you:
 * - Verify server requirements
 * - Test database connection
 * - Create admin account
 * - Check file permissions
 */

// Prevent access after installation is complete
$lockFile = __DIR__ . '/../.install.lock';
if (file_exists($lockFile)) {
    die('<h1>Installation Complete</h1><p>This installer has been locked for security. Delete this file.</p>');
}

// Load environment
require_once __DIR__ . '/../src/bootstrap.php';

$step = $_GET['step'] ?? 'welcome';
$errors = [];
$success = [];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? 'welcome';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack LMS - Installation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        h2 {
            color: #333;
            margin: 20px 0 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #721c24;
        }
        .check-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item.pass {
            background: #d4edda;
        }
        .check-item.fail {
            background: #f8d7da;
        }
        .check-item.warning {
            background: #fff3cd;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge.pass {
            background: #28a745;
            color: white;
        }
        .badge.fail {
            background: #dc3545;
            color: white;
        }
        .badge.warning {
            background: #ffc107;
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px 10px 0;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            margin: 0 5px;
            border-radius: 4px;
            font-size: 14px;
        }
        .step.active {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 EduTrack LMS</h1>
        <p style="color: #666; margin-bottom: 20px;">Installation Helper</p>

        <div class="steps">
            <div class="step <?= $step === 'welcome' ? 'active' : '' ?>">Welcome</div>
            <div class="step <?= $step === 'requirements' ? 'active' : '' ?>">Requirements</div>
            <div class="step <?= $step === 'database' ? 'active' : '' ?>">Database</div>
            <div class="step <?= $step === 'admin' ? 'active' : '' ?>">Admin</div>
            <div class="step <?= $step === 'complete' ? 'active' : '' ?>">Complete</div>
        </div>

        <?php if ($step === 'welcome'): ?>
            <h2>Welcome to EduTrack LMS Installation</h2>
            <p>This installer will help you set up your Learning Management System.</p>

            <div class="warning">
                <strong>⚠️ IMPORTANT SECURITY WARNING</strong><br>
                Delete this file (<code>install.php</code>) immediately after installation!
            </div>

            <h2>Before You Begin</h2>
            <p>Make sure you have:</p>
            <ul style="margin: 10px 0 20px 20px; line-height: 1.8;">
                <li>Created a MySQL database in Hostinger</li>
                <li>Imported the database schema (<code>complete_lms_schema.sql</code>)</li>
                <li>Configured your <code>.env</code> file</li>
                <li>Set proper file permissions</li>
            </ul>

            <a href="?step=requirements" class="btn">Start Installation →</a>

        <?php elseif ($step === 'requirements'): ?>
            <h2>Server Requirements Check</h2>

            <?php
            $checks = [];

            // PHP Version
            $phpVersion = phpversion();
            $checks[] = [
                'name' => 'PHP Version',
                'value' => $phpVersion,
                'status' => version_compare($phpVersion, '8.0.0', '>=') ? 'pass' : 'fail',
                'required' => '>= 8.0',
            ];

            // PDO Extension
            $checks[] = [
                'name' => 'PDO Extension',
                'value' => extension_loaded('pdo') ? 'Installed' : 'Not Installed',
                'status' => extension_loaded('pdo') ? 'pass' : 'fail',
                'required' => 'Required',
            ];

            // PDO MySQL
            $checks[] = [
                'name' => 'PDO MySQL Driver',
                'value' => extension_loaded('pdo_mysql') ? 'Installed' : 'Not Installed',
                'status' => extension_loaded('pdo_mysql') ? 'pass' : 'fail',
                'required' => 'Required',
            ];

            // OpenSSL
            $checks[] = [
                'name' => 'OpenSSL Extension',
                'value' => extension_loaded('openssl') ? 'Installed' : 'Not Installed',
                'status' => extension_loaded('openssl') ? 'pass' : 'warning',
                'required' => 'Recommended',
            ];

            // GD Library
            $checks[] = [
                'name' => 'GD Library',
                'value' => extension_loaded('gd') ? 'Installed' : 'Not Installed',
                'status' => extension_loaded('gd') ? 'pass' : 'warning',
                'required' => 'For image handling',
            ];

            // cURL
            $checks[] = [
                'name' => 'cURL Extension',
                'value' => extension_loaded('curl') ? 'Installed' : 'Not Installed',
                'status' => extension_loaded('curl') ? 'pass' : 'warning',
                'required' => 'For payment gateways',
            ];

            // File permissions
            $storagePath = __DIR__ . '/../storage/logs/';
            $uploadsPath = __DIR__ . '/uploads/';

            $checks[] = [
                'name' => 'Storage Directory Writable',
                'value' => is_writable($storagePath) ? 'Writable' : 'Not Writable',
                'status' => is_writable($storagePath) ? 'pass' : 'fail',
                'required' => 'Required (755)',
            ];

            $checks[] = [
                'name' => 'Uploads Directory Writable',
                'value' => is_writable($uploadsPath) ? 'Writable' : 'Not Writable',
                'status' => is_writable($uploadsPath) ? 'pass' : 'fail',
                'required' => 'Required (755)',
            ];

            $allPassed = true;
            foreach ($checks as $check):
                if ($check['status'] === 'fail') $allPassed = false;
            ?>
                <div class="check-item <?= $check['status'] ?>">
                    <div>
                        <strong><?= $check['name'] ?></strong><br>
                        <small><?= $check['required'] ?></small>
                    </div>
                    <div>
                        <?= $check['value'] ?>
                        <span class="badge <?= $check['status'] ?>">
                            <?= strtoupper($check['status']) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($allPassed): ?>
                <div class="success">
                    <strong>✓ All critical requirements met!</strong>
                </div>
                <a href="?step=database" class="btn">Next: Test Database →</a>
            <?php else: ?>
                <div class="error">
                    <strong>✗ Some requirements are not met</strong><br>
                    Please fix the issues above before continuing.
                </div>
                <a href="?step=requirements" class="btn">Recheck</a>
            <?php endif; ?>

        <?php elseif ($step === 'database'): ?>
            <h2>Database Connection Test</h2>

            <?php
            try {
                $db = Database::getInstance();

                // Test connection
                $result = $db->query("SELECT DATABASE() as db_name, VERSION() as db_version")->fetch();

                // Count tables
                $tables = $db->query("SHOW TABLES")->fetchAll();
                $tableCount = count($tables);

                echo '<div class="success">';
                echo '<strong>✓ Database connection successful!</strong><br>';
                echo '<strong>Database:</strong> ' . htmlspecialchars($result['db_name']) . '<br>';
                echo '<strong>MySQL Version:</strong> ' . htmlspecialchars($result['db_version']) . '<br>';
                echo '<strong>Tables Found:</strong> ' . $tableCount;
                echo '</div>';

                if ($tableCount < 44) {
                    echo '<div class="warning">';
                    echo '<strong>⚠️ Warning: Only ' . $tableCount . ' tables found</strong><br>';
                    echo 'Expected: 44 tables<br>';
                    echo 'Please make sure you imported <code>complete_lms_schema.sql</code>';
                    echo '</div>';
                } else {
                    echo '<div class="success">';
                    echo '<strong>✓ All database tables found!</strong>';
                    echo '</div>';
                }

                echo '<a href="?step=admin" class="btn">Next: Create Admin →</a>';

            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>✗ Database connection failed!</strong><br>';
                echo 'Error: ' . htmlspecialchars($e->getMessage()) . '<br><br>';
                echo '<strong>Troubleshooting:</strong><br>';
                echo '1. Check your <code>.env</code> file<br>';
                echo '2. Verify database credentials<br>';
                echo '3. Make sure database exists<br>';
                echo '4. Check database user has proper permissions';
                echo '</div>';

                echo '<a href="?step=database" class="btn">Retry</a>';
            }
            ?>

        <?php elseif ($step === 'admin'): ?>
            <h2>Create Admin Account</h2>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                // Validation
                if (empty($username)) {
                    $errors[] = 'Username is required';
                }
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Valid email is required';
                }
                if (strlen($password) < 8) {
                    $errors[] = 'Password must be at least 8 characters';
                }
                if ($password !== $confirmPassword) {
                    $errors[] = 'Passwords do not match';
                }

                if (empty($errors)) {
                    try {
                        $db = Database::getInstance();

                        // Check if user already exists
                        $existing = $db->query(
                            "SELECT id FROM users WHERE username = ? OR email = ?",
                            [$username, $email]
                        )->fetch();

                        if ($existing) {
                            $errors[] = 'Username or email already exists';
                        } else {
                            // Create admin user
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                            $db->query(
                                "INSERT INTO users (username, email, password, role, status, created_at)
                                 VALUES (?, ?, ?, 'admin', 'active', NOW())",
                                [$username, $email, $hashedPassword]
                            );

                            $success[] = 'Admin account created successfully!';

                            // Create lock file
                            file_put_contents($lockFile, date('Y-m-d H:i:s'));

                            // Redirect to complete
                            echo '<meta http-equiv="refresh" content="2;url=?step=complete">';
                        }
                    } catch (Exception $e) {
                        $errors[] = 'Failed to create admin: ' . $e->getMessage();
                    }
                }
            }

            if (!empty($errors)):
                echo '<div class="error">';
                foreach ($errors as $error) {
                    echo '✗ ' . htmlspecialchars($error) . '<br>';
                }
                echo '</div>';
            endif;

            if (!empty($success)):
                echo '<div class="success">';
                foreach ($success as $msg) {
                    echo '✓ ' . htmlspecialchars($msg) . '<br>';
                }
                echo 'Redirecting to completion...';
                echo '</div>';
            endif;
            ?>

            <form method="POST">
                <input type="hidden" name="step" value="admin">
                <input type="hidden" name="create_admin" value="1">

                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required minlength="8">
                    <small style="color: #666;">Minimum 8 characters</small>
                </div>

                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" required minlength="8">
                </div>

                <button type="submit" class="btn">Create Admin Account</button>
            </form>

        <?php elseif ($step === 'complete'): ?>
            <h2>🎉 Installation Complete!</h2>

            <div class="success">
                <strong>✓ EduTrack LMS has been successfully installed!</strong>
            </div>

            <h2>⚠️ CRITICAL SECURITY STEP</h2>
            <div class="error">
                <strong>DELETE THIS FILE IMMEDIATELY!</strong><br>
                File to delete: <code>public/install.php</code><br><br>
                This installer is now locked, but you must delete this file for security.
            </div>

            <h2>Next Steps</h2>
            <ol style="margin: 10px 0 20px 20px; line-height: 2;">
                <li>Delete <code>public/install.php</code> (this file)</li>
                <li>Login to your admin account</li>
                <li>Configure email settings in <code>.env</code></li>
                <li>Setup SSL certificate (if not done)</li>
                <li>Create course categories</li>
                <li>Add your first course</li>
                <li>Invite instructors</li>
            </ol>

            <h2>Access Your Site</h2>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0;">
                <strong>Homepage:</strong> <a href="../index.php" target="_blank">Visit Homepage</a><br>
                <strong>Login:</strong> <a href="../login.php" target="_blank">Admin Login</a><br>
                <strong>Dashboard:</strong> <a href="../admin/index.php" target="_blank">Admin Dashboard</a>
            </div>

            <h2>Need Help?</h2>
            <p>Check these resources:</p>
            <ul style="margin: 10px 0 20px 20px; line-height: 1.8;">
                <li><code>HOSTINGER_DEPLOYMENT_GUIDE.md</code> - Full deployment guide</li>
                <li><code>DEPLOYMENT_CHECKLIST.md</code> - Deployment checklist</li>
                <li><code>storage/logs/</code> - Error logs</li>
            </ul>

            <a href="../index.php" class="btn">Visit Homepage</a>
            <a href="../login.php" class="btn">Login</a>

        <?php endif; ?>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 14px;">
            EduTrack LMS &copy; <?= date('Y') ?> | Installation Helper v1.0
        </div>
    </div>
</body>
</html>
