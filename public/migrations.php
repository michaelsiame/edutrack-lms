<?php
/**
 * Database Migration Runner
 * Run this script to execute pending SQL migrations
 * 
 * SECURITY: Delete this file after running migrations!
 */

// Basic security - check for a simple password or admin session
$access_password = 'edutrack2024'; // Change this to a secure password
$allowed_ips = ['127.0.0.1', '::1']; // Add your IP here if needed

// Check access
$has_access = false;

// Check if running from localhost
if (in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    $has_access = true;
}

// Check password if provided
if (isset($_POST['password']) && $_POST['password'] === $access_password) {
    $has_access = true;
    setcookie('migration_auth', hash('sha256', $access_password), time() + 3600, '/');
}

// Check cookie
if (isset($_COOKIE['migration_auth']) && $_COOKIE['migration_auth'] === hash('sha256', $access_password)) {
    $has_access = true;
}

// Simple auth form
if (!$has_access) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Migration Runner - Authentication</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f3f4f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .auth-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
            h1 { color: #1f2937; margin-bottom: 20px; font-size: 24px; }
            input[type="password"] { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; margin-bottom: 16px; font-size: 16px; box-sizing: border-box; }
            button { width: 100%; padding: 12px; background: #2E70DA; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
            button:hover { background: #1e4fa0; }
            .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin-bottom: 20px; color: #92400e; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="auth-box">
            <h1>🔒 Migration Runner</h1>
            <div class="warning">
                <strong>Security Notice:</strong> Delete this file after running migrations!
            </div>
            <form method="POST">
                <input type="password" name="password" placeholder="Enter access password" required autofocus>
                <button type="submit">Access Migrations</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Load bootstrap
require_once __DIR__ . '/../src/bootstrap.php';

$db = Database::getInstance();
$message = '';
$error = '';

// Create migrations tracking table if not exists
try {
    $db->query("CREATE TABLE IF NOT EXISTS migrations_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        success BOOLEAN DEFAULT TRUE,
        error_message TEXT,
        UNIQUE KEY unique_filename (filename)
    )");
} catch (Exception $e) {
    $error = "Failed to create migrations table: " . $e->getMessage();
}

// Get list of migration files
$migration_files = glob(__DIR__ . '/../migrations/*.sql');
sort($migration_files);

// Get already executed migrations
$executed = [];
try {
    $executed = $db->fetchAll("SELECT filename, executed_at, success FROM migrations_log");
    $executed = array_column($executed, null, 'filename');
} catch (Exception $e) {
    // Table might not exist yet
}

// Handle run request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    $file = $_POST['run_migration'];
    $filepath = __DIR__ . '/../migrations/' . basename($file);
    
    if (file_exists($filepath)) {
        $sql = file_get_contents($filepath);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $success = true;
        $error_msg = '';
        
        foreach ($statements as $statement) {
            if (empty($statement) || strpos($statement, '--') === 0) continue;
            
            try {
                $db->query($statement);
            } catch (Exception $e) {
                $success = false;
                $error_msg = $e->getMessage();
                break;
            }
        }
        
        // Log the migration
        try {
            $db->query(
                "INSERT INTO migrations_log (filename, success, error_message) VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE executed_at = CURRENT_TIMESTAMP, success = ?, error_message = ?",
                [basename($file), $success, $error_msg, $success, $error_msg]
            );
        } catch (Exception $e) {
            // Ignore logging errors
        }
        
        if ($success) {
            $message = "✅ Migration successful: " . basename($file);
        } else {
            $error = "❌ Migration failed: " . basename($file) . "<br>Error: " . $error_msg;
        }
        
        // Refresh executed list
        $executed = $db->fetchAll("SELECT filename, executed_at, success FROM migrations_log");
        $executed = array_column($executed, null, 'filename');
    }
}

// Handle run all request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_all'])) {
    $ran = 0;
    $failed = 0;
    
    foreach ($migration_files as $filepath) {
        $filename = basename($filepath);
        
        // Skip if already executed successfully
        if (isset($executed[$filename]) && $executed[$filename]['success']) {
            continue;
        }
        
        $sql = file_get_contents($filepath);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $success = true;
        $error_msg = '';
        
        foreach ($statements as $statement) {
            if (empty($statement) || strpos($statement, '--') === 0) continue;
            
            try {
                $db->query($statement);
            } catch (Exception $e) {
                $success = false;
                $error_msg = $e->getMessage();
                break;
            }
        }
        
        // Log the migration
        try {
            $db->query(
                "INSERT INTO migrations_log (filename, success, error_message) VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE executed_at = CURRENT_TIMESTAMP, success = ?, error_message = ?",
                [$filename, $success, $error_msg, $success, $error_msg]
            );
        } catch (Exception $e) {
            // Ignore logging errors
        }
        
        if ($success) {
            $ran++;
        } else {
            $failed++;
        }
    }
    
    $message = "✅ Ran $ran migration(s)" . ($failed > 0 ? ", $failed failed" : "");
    
    // Refresh executed list
    $executed = $db->fetchAll("SELECT filename, executed_at, success FROM migrations_log");
    $executed = array_column($executed, null, 'filename');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration Runner - Edutrack</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
            background: #f3f4f6; 
            margin: 0; 
            padding: 20px;
            line-height: 1.6;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #1f2937; margin-bottom: 10px; }
        .subtitle { color: #6b7280; margin-bottom: 30px; }
        
        .alert { 
            padding: 16px; 
            border-radius: 8px; 
            margin-bottom: 20px;
        }
        .alert-success { background: #d1fae5; border-left: 4px solid #10b981; color: #065f46; }
        .alert-error { background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; }
        .alert-warning { background: #fef3c7; border-left: 4px solid #f59e0b; color: #92400e; }
        
        .card { 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header { 
            padding: 20px; 
            background: #f9fafb; 
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-body { padding: 20px; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { 
            padding: 12px 16px; 
            text-align: left; 
            border-bottom: 1px solid #e5e7eb;
        }
        th { 
            background: #f9fafb; 
            font-weight: 600; 
            color: #374151;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        tr:hover { background: #f9fafb; }
        
        .status {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-success { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        
        button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary { background: #2E70DA; color: white; }
        .btn-primary:hover { background: #1e4fa0; }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        
        .danger-zone {
            border: 2px solid #ef4444;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        .danger-zone h2 { color: #dc2626; margin-top: 0; }
        
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Migration Runner</h1>
        <p class="subtitle">Manage and execute SQL migrations for Edutrack LMS</p>
        
        <?php if ($message): ?>
        <div class="alert alert-success">
            <?= $message ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-error">
            <?= $error ?>
        </div>
        <?php endif; ?>
        
        <!-- Summary Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <strong>Migration Summary</strong>
                </div>
                <form method="POST" style="margin: 0;">
                    <button type="submit" name="run_all" class="btn-success" onclick="return confirm('Run all pending migrations?')">
                        🚀 Run All Pending
                    </button>
                </form>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>✅ Executed Successfully</td>
                            <td><?= count(array_filter($executed, fn($e) => $e['success'])) ?></td>
                        </tr>
                        <tr>
                            <td>⏳ Pending</td>
                            <td><?= count($migration_files) - count($executed) ?></td>
                        </tr>
                        <tr>
                            <td>❌ Failed</td>
                            <td><?= count(array_filter($executed, fn($e) => !$e['success'])) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Files</strong></td>
                            <td><strong><?= count($migration_files) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Migrations List -->
        <div class="card">
            <div class="card-header">
                <strong>Migration Files</strong>
            </div>
            <div class="card-body" style="padding: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Status</th>
                            <th>Executed</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($migration_files as $filepath): 
                            $filename = basename($filepath);
                            $is_executed = isset($executed[$filename]);
                            $is_success = $is_executed && $executed[$filename]['success'];
                            $executed_at = $is_executed ? $executed[$filename]['executed_at'] : null;
                        ?>
                        <tr>
                            <td><code><?= htmlspecialchars($filename) ?></code></td>
                            <td>
                                <?php if ($is_success): ?>
                                    <span class="status status-success">✅ Success</span>
                                <?php elseif ($is_executed && !$is_success): ?>
                                    <span class="status status-failed">❌ Failed</span>
                                <?php else: ?>
                                    <span class="status status-pending">⏳ Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $executed_at ? date('Y-m-d H:i:s', strtotime($executed_at)) : '-' ?>
                            </td>
                            <td>
                                <?php if (!$is_success): ?>
                                <form method="POST" style="margin: 0; display: inline;">
                                    <input type="hidden" name="run_migration" value="<?= htmlspecialchars($filename) ?>">
                                    <button type="submit" class="btn-primary btn-sm" onclick="return confirm('Run this migration?')">
                                        Run
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span style="color: #6b7280; font-size: 12px;">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($migration_files)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #6b7280;">
                                No migration files found in <code>migrations/</code> directory
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Danger Zone -->
        <div class="danger-zone">
            <h2>⚠️ Security Warning</h2>
            <p><strong>Delete this file immediately after running migrations!</strong></p>
            <p>Leaving this file accessible could allow unauthorized database modifications.</p>
            
            <h3>How to delete:</h3>
            <ol>
                <li>SSH/FTP into your server</li>
                <li>Navigate to <code>public_html/public/</code></li>
                <li>Run: <code>rm migrations.php</code></li>
            </ol>
            <p>Or use this command:</p>
            <pre style="background: #f3f4f6; padding: 12px; border-radius: 6px; overflow-x: auto;">
cd <?= realpath(__DIR__) ?> && rm migrations.php
            </pre>
        </div>
    </div>
</body>
</html>
