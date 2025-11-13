<?php
/**
 * Email Test Script
 * Sends a test email to verify SMTP configuration
 * DELETE THIS FILE BEFORE PRODUCTION!
 */

require_once '../src/bootstrap.php';

// Security check
if (APP_ENV === 'production' && !APP_DEBUG) {
    die('This test file is disabled in production mode.');
}

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_email'])) {
    $testEmail = filter_var($_POST['test_email'], FILTER_VALIDATE_EMAIL);

    if ($testEmail) {
        try {
            // Simple test email
            $to = $testEmail;
            $subject = 'EduTrack LMS - Test Email';
            $body = '<html><body style="font-family: Arial, sans-serif; padding: 20px;">';
            $body .= '<h2 style="color: #2E70DA;">🎉 Email Configuration Successful!</h2>';
            $body .= '<p>If you\'re reading this, your EduTrack LMS email configuration is working correctly.</p>';
            $body .= '<hr style="margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;">';
            $body .= '<p><strong>Email Configuration:</strong></p>';
            $body .= '<ul>';
            $body .= '<li>SMTP Host: ' . htmlspecialchars(env('MAIL_HOST')) . '</li>';
            $body .= '<li>SMTP Port: ' . htmlspecialchars(env('MAIL_PORT')) . '</li>';
            $body .= '<li>From: ' . htmlspecialchars(env('MAIL_FROM_ADDRESS')) . '</li>';
            $body .= '</ul>';
            $body .= '<p style="margin-top: 20px; color: #6b7280; font-size: 14px;">Sent from EduTrack Computer Training College</p>';
            $body .= '</body></html>';

            // Try to send
            $result = sendEmail($to, $subject, $body);

            if ($result) {
                $status = 'success';
                $message = 'Test email sent successfully to ' . htmlspecialchars($testEmail) . '! Check your inbox (and spam folder).';
            } else {
                $status = 'error';
                $message = 'Failed to send email. Please check your SMTP credentials in .env file.';
            }
        } catch (Exception $e) {
            $status = 'error';
            $message = 'Error: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $status = 'error';
        $message = 'Invalid email address provided.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Test - EduTrack</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 50px auto; }
        .card { background: white; border-radius: 8px; padding: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { color: #111827; margin-bottom: 20px; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert.success { background: #d1fae5; border-left: 4px solid #10b981; color: #065f46; }
        .alert.error { background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; }
        .btn { display: inline-block; padding: 10px 20px; background: #2E70DA; color: white; text-decoration: none; border-radius: 6px; margin-top: 15px; }
        .btn:hover { background: #1E4A8A; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>📧 Email Test Result</h1>

            <?php if ($message): ?>
                <div class="alert <?= $status ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <a href="test-setup.php" class="btn">← Back to Setup Test</a>
            <a href="<?= url('index.php') ?>" class="btn" style="background: #059669;">Go to Homepage</a>
        </div>
    </div>
</body>
</html>
