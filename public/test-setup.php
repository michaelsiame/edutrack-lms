<?php
/**
 * Quick Setup Test
 * Tests database, email, and video configuration
 * DELETE THIS FILE BEFORE PRODUCTION!
 */

require_once '../src/bootstrap.php';

// Security check
if (APP_ENV === 'production' && !APP_DEBUG) {
    die('This test file is disabled in production mode.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack Setup Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #111827; margin-bottom: 30px; }
        .test-section { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .test-section h2 { color: #374151; margin-bottom: 15px; font-size: 18px; }
        .test-item { padding: 12px; border-left: 4px solid #e5e7eb; margin-bottom: 10px; background: #f9fafb; }
        .test-item.success { border-color: #10b981; background: #d1fae5; }
        .test-item.error { border-color: #ef4444; background: #fee2e2; }
        .test-item.warning { border-color: #f59e0b; background: #fef3c7; }
        .test-label { font-weight: 600; color: #374151; margin-bottom: 5px; }
        .test-value { color: #6b7280; font-size: 14px; font-family: monospace; }
        .status-icon { display: inline-block; margin-right: 10px; }
        .btn { display: inline-block; padding: 10px 20px; background: #2E70DA; color: white; text-decoration: none; border-radius: 6px; margin-top: 15px; }
        .btn:hover { background: #1E4A8A; }
        pre { background: #f3f4f6; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 EduTrack Setup Test</h1>

        <!-- Database Test -->
        <div class="test-section">
            <h2>1. Database Connection</h2>
            <?php
            try {
                $db = Database::getInstance();
                $result = $db->query("SELECT COUNT(*) as count FROM users")->fetch();
                echo '<div class="test-item success">';
                echo '<div class="test-label"><span class="status-icon">✅</span>Database Connected</div>';
                echo '<div class="test-value">Host: ' . env('DB_HOST') . ' | Database: ' . env('DB_NAME') . '</div>';
                echo '<div class="test-value">Users in database: ' . $result['count'] . '</div>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="test-item error">';
                echo '<div class="test-label"><span class="status-icon">❌</span>Database Connection Failed</div>';
                echo '<div class="test-value">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- Security Keys Test -->
        <div class="test-section">
            <h2>2. Security Configuration</h2>
            <?php
            $encKey = env('ENCRYPTION_KEY');
            $jwtSecret = env('JWT_SECRET');

            // Check encryption key
            if ($encKey && $encKey !== 'base64:your-32-character-encryption-key-here' && strlen($encKey) > 20) {
                echo '<div class="test-item success">';
                echo '<div class="test-label"><span class="status-icon">✅</span>Encryption Key Configured</div>';
                echo '<div class="test-value">Length: ' . strlen($encKey) . ' characters</div>';
                echo '</div>';
            } else {
                echo '<div class="test-item error">';
                echo '<div class="test-label"><span class="status-icon">❌</span>Encryption Key Not Set</div>';
                echo '<div class="test-value">Generate with: openssl rand -base64 32</div>';
                echo '</div>';
            }

            // Check JWT secret
            if ($jwtSecret && $jwtSecret !== 'your-jwt-secret-key-here' && strlen($jwtSecret) > 20) {
                echo '<div class="test-item success">';
                echo '<div class="test-label"><span class="status-icon">✅</span>JWT Secret Configured</div>';
                echo '<div class="test-value">Length: ' . strlen($jwtSecret) . ' characters</div>';
                echo '</div>';
            } else {
                echo '<div class="test-item error">';
                echo '<div class="test-label"><span class="status-icon">❌</span>JWT Secret Not Set</div>';
                echo '<div class="test-value">Generate with: openssl rand -base64 64</div>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- Email Configuration Test -->
        <div class="test-section">
            <h2>3. Email Configuration</h2>
            <?php
            $mailUser = env('MAIL_USERNAME');
            $mailPass = env('MAIL_PASSWORD');
            $mailHost = env('MAIL_HOST');

            if ($mailUser && $mailPass && strlen($mailPass) > 10) {
                echo '<div class="test-item success">';
                echo '<div class="test-label"><span class="status-icon">✅</span>Email Credentials Configured</div>';
                echo '<div class="test-value">Host: ' . htmlspecialchars($mailHost) . '</div>';
                echo '<div class="test-value">Username: ' . htmlspecialchars($mailUser) . '</div>';
                echo '<div class="test-value">Password: ' . str_repeat('*', strlen($mailPass)) . ' (hidden)</div>';
                echo '</div>';

                // Test email sending
                echo '<div class="test-item warning">';
                echo '<div class="test-label"><span class="status-icon">⚠️</span>Email Sending Test</div>';
                echo '<div class="test-value">Click button below to send test email</div>';
                echo '<form method="POST" action="test-email.php" style="margin-top: 10px;">';
                echo '<input type="email" name="test_email" placeholder="your-email@example.com" required style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; width: 300px;">';
                echo '<button type="submit" style="padding: 8px 16px; background: #2E70DA; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 5px;">Send Test Email</button>';
                echo '</form>';
                echo '</div>';
            } else {
                echo '<div class="test-item error">';
                echo '<div class="test-label"><span class="status-icon">❌</span>Email Not Configured</div>';
                echo '<div class="test-value">Set MAIL_USERNAME and MAIL_PASSWORD in .env</div>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- Video Configuration Test -->
        <div class="test-section">
            <h2>4. Video Platform Configuration</h2>
            <?php
            $videoPlatform = config('video.default_platform', 'youtube');
            $youtubeKey = config('video.youtube_api_key');

            echo '<div class="test-item success">';
            echo '<div class="test-label"><span class="status-icon">✅</span>Default Platform: ' . ucfirst($videoPlatform) . '</div>';
            echo '<div class="test-value">Videos will be embedded from ' . ucfirst($videoPlatform) . '</div>';
            echo '</div>';

            if ($youtubeKey) {
                echo '<div class="test-item success">';
                echo '<div class="test-label"><span class="status-icon">✅</span>YouTube API Key Configured</div>';
                echo '<div class="test-value">Advanced features enabled</div>';
                echo '</div>';
            } else {
                echo '<div class="test-item warning">';
                echo '<div class="test-label"><span class="status-icon">⚠️</span>YouTube API Key Not Set</div>';
                echo '<div class="test-value">Basic embedding works, but advanced features disabled (optional)</div>';
                echo '</div>';
            }

            // Test video embedding
            echo '<div class="test-item">';
            echo '<div class="test-label">Video Embedding Test</div>';
            echo '<div class="test-value">Testing YouTube URL: https://www.youtube.com/watch?v=dQw4w9WgXcQ</div>';
            $testUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
            if (isYouTube($testUrl)) {
                $videoId = getYouTubeId($testUrl);
                echo '<div style="margin-top: 10px; background: #f9fafb; padding: 10px; border-radius: 4px;">';
                echo '<div class="test-value" style="color: #10b981;">✅ Video ID extracted: ' . $videoId . '</div>';
                echo '<div style="margin-top: 10px; max-width: 100%; aspect-ratio: 16/9;">';
                echo getVideoEmbed($testUrl);
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
            ?>
        </div>

        <!-- Environment Check -->
        <div class="test-section">
            <h2>5. Environment Configuration</h2>
            <?php
            $env = APP_ENV;
            $debug = APP_DEBUG;

            if ($env === 'production' && $debug) {
                echo '<div class="test-item error">';
                echo '<div class="test-label"><span class="status-icon">❌</span>SECURITY RISK!</div>';
                echo '<div class="test-value">APP_ENV is "production" but APP_DEBUG is enabled!</div>';
                echo '<div class="test-value">This exposes sensitive errors. Set APP_DEBUG=false in production.</div>';
                echo '</div>';
            } elseif ($env === 'production' && !$debug) {
                echo '<div class="test-item success">';
                echo '<div class="test-label"><span class="status-icon">✅</span>Production Mode (Secure)</div>';
                echo '<div class="test-value">Debug disabled, ready for live deployment</div>';
                echo '</div>';
            } else {
                echo '<div class="test-item success">';
                echo '<div class="test-label"><span class="status-icon">✅</span>Development Mode</div>';
                echo '<div class="test-value">Debug enabled for testing (OK for local development)</div>';
                echo '</div>';
            }

            echo '<div class="test-item">';
            echo '<div class="test-label">Current Settings</div>';
            echo '<div class="test-value">Environment: ' . $env . '</div>';
            echo '<div class="test-value">Debug: ' . ($debug ? 'Enabled' : 'Disabled') . '</div>';
            echo '<div class="test-value">Timezone: ' . APP_TIMEZONE . '</div>';
            echo '<div class="test-value">App URL: ' . APP_URL . '</div>';
            echo '</div>';
            ?>
        </div>

        <!-- File Upload Test -->
        <div class="test-section">
            <h2>6. File Upload Configuration</h2>
            <?php
            $uploadPath = PUBLIC_PATH . '/uploads';
            $coursesPath = $uploadPath . '/courses/thumbnails';

            if (is_dir($uploadPath) && is_writable($uploadPath)) {
                echo '<div class="test-item success">';
                echo '<div class="test-label"><span class="status-icon">✅</span>Upload Directory Exists</div>';
                echo '<div class="test-value">Path: ' . $uploadPath . '</div>';
                echo '<div class="test-value">Writable: Yes</div>';
                echo '</div>';
            } else {
                echo '<div class="test-item error">';
                echo '<div class="test-label"><span class="status-icon">❌</span>Upload Directory Issue</div>';
                echo '<div class="test-value">Path: ' . $uploadPath . '</div>';
                echo '<div class="test-value">Create with: mkdir -p ' . $uploadPath . ' && chmod 777 ' . $uploadPath . '</div>';
                echo '</div>';
            }

            // Check courses thumbnails directory
            if (is_dir($coursesPath) && is_writable($coursesPath)) {
                echo '<div class="test-item success">';
                echo '<div class="test-label"><span class="status-icon">✅</span>Course Thumbnails Directory OK</div>';
                echo '</div>';
            } else {
                echo '<div class="test-item warning">';
                echo '<div class="test-label"><span class="status-icon">⚠️</span>Course Thumbnails Directory Missing</div>';
                echo '<div class="test-value">Create with: mkdir -p ' . $coursesPath . ' && chmod 777 ' . $coursesPath . '</div>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- Next Steps -->
        <div class="test-section">
            <h2>✅ Next Steps</h2>
            <ol style="margin-left: 20px; color: #374151; line-height: 1.8;">
                <li>Fix any errors shown above</li>
                <li>Send a test email to verify SMTP works</li>
                <li>Upload a course with YouTube video URL to test embedding</li>
                <li>Configure payment gateways when ready (optional)</li>
                <li><strong>DELETE this test file before going live!</strong></li>
            </ol>

            <div style="margin-top: 20px;">
                <a href="<?= url('index.php') ?>" class="btn">Go to Homepage</a>
                <a href="<?= url('check-credentials.php') ?>" class="btn" style="background: #059669;">View Full Config</a>
            </div>
        </div>

        <p style="text-align: center; color: #6b7280; margin-top: 20px; font-size: 14px;">
            <strong>⚠️ Security Warning:</strong> Delete test-setup.php and test-email.php before deploying to production!
        </p>
    </div>
</body>
</html>
