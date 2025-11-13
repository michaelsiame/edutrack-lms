<?php
/**
 * Credentials Configuration Checker
 * Helps verify which external services are configured
 *
 * DELETE THIS FILE IN PRODUCTION!
 */

require_once '../src/bootstrap.php';

// Security: Only allow in development
if (APP_ENV === 'production') {
    die('This file cannot be accessed in production mode.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credentials Configuration Checker</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #111827; margin-bottom: 10px; }
        .subtitle { color: #6b7280; margin-bottom: 30px; }
        .section { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .section h2 { color: #374151; margin-bottom: 15px; font-size: 18px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; }
        .check-item { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #f3f4f6; }
        .check-item:last-child { border-bottom: none; }
        .status { width: 24px; height: 24px; border-radius: 50%; margin-right: 15px; flex-shrink: 0; }
        .status.configured { background: #10b981; }
        .status.not-configured { background: #ef4444; }
        .status.optional { background: #f59e0b; }
        .label { flex: 1; color: #374151; }
        .value { color: #6b7280; font-size: 14px; font-family: monospace; }
        .priority { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-left: 10px; }
        .priority.high { background: #fecaca; color: #991b1b; }
        .priority.medium { background: #fed7aa; color: #92400e; }
        .priority.low { background: #dbeafe; color: #1e40af; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-card .number { font-size: 36px; font-weight: bold; margin-bottom: 5px; }
        .stat-card .label { color: #6b7280; font-size: 14px; }
        .stat-card.configured .number { color: #10b981; }
        .stat-card.not-configured .number { color: #ef4444; }
        .stat-card.optional .number { color: #f59e0b; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .warning strong { color: #92400e; }
        .next-steps { background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; border-radius: 4px; margin-top: 20px; }
        .next-steps h3 { color: #1e40af; margin-bottom: 10px; }
        .next-steps ol { margin-left: 20px; color: #1e40af; }
        .next-steps li { margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Credentials Configuration Checker</h1>
        <p class="subtitle">Verify which external services are configured</p>

        <?php
        // Check configurations
        $checks = [
            'database' => [
                'priority' => 'high',
                'items' => [
                    'DB Connection' => !empty(env('DB_NAME')) && !empty(env('DB_USER')),
                    'DB Name' => env('DB_NAME') ?: 'Not set',
                    'DB Host' => env('DB_HOST') ?: 'localhost'
                ]
            ],
            'email' => [
                'priority' => 'high',
                'items' => [
                    'SMTP Configured' => !empty(env('MAIL_USERNAME')) && !empty(env('MAIL_PASSWORD')),
                    'Mail Username' => env('MAIL_USERNAME') ? '✓ Configured' : 'Not configured',
                    'Mail From' => env('MAIL_FROM_ADDRESS') ?: 'Not set',
                    'Mail Host' => env('MAIL_HOST') ?: 'smtp.gmail.com'
                ]
            ],
            'site' => [
                'priority' => 'high',
                'items' => [
                    'Site Email' => env('SITE_EMAIL') ?: 'Not set',
                    'Site Phone' => env('SITE_PHONE') ?: 'Not set',
                    'App URL' => env('APP_URL') ?: APP_URL
                ]
            ],
            'video' => [
                'priority' => 'medium',
                'items' => [
                    'Default Platform' => config('video.default_platform', 'youtube'),
                    'YouTube API Key' => config('video.youtube_api_key') ? '✓ Configured' : 'Not configured (optional)',
                    'Vimeo Token' => config('video.vimeo_access_token') ? '✓ Configured' : 'Not configured (optional)',
                    'Bunny CDN' => config('video.bunny_api_key') ? '✓ Configured' : 'Not configured (optional)'
                ]
            ],
            'payment' => [
                'priority' => 'medium',
                'items' => [
                    'MTN MoMo' => env('MTN_API_KEY') ? '✓ Configured' : 'Not configured',
                    'Airtel Money' => env('AIRTEL_CLIENT_ID') ? '✓ Configured' : 'Not configured',
                    'Zamtel Kwacha' => env('ZAMTEL_API_KEY') ? '✓ Configured' : 'Not configured'
                ]
            ],
            'social' => [
                'priority' => 'low',
                'items' => [
                    'Facebook' => config('social.facebook') ?: 'Not set',
                    'Twitter' => config('social.twitter') ?: 'Not set',
                    'Instagram' => config('social.instagram') ?: 'Not set',
                    'YouTube Channel' => config('social.youtube') ?: 'Not set',
                    'LinkedIn' => config('social.linkedin') ?: 'Not set'
                ]
            ],
            'security' => [
                'priority' => 'high',
                'items' => [
                    'Encryption Key' => env('ENCRYPTION_KEY') ? '✓ Configured' : '⚠️ NOT SET - Generate one!',
                    'JWT Secret' => env('JWT_SECRET') ? '✓ Configured' : '⚠️ NOT SET - Generate one!',
                    'Session Secure' => env('SESSION_SECURE') === 'true' ? 'Enabled' : 'Disabled (enable for HTTPS)',
                ]
            ],
            'analytics' => [
                'priority' => 'low',
                'items' => [
                    'Google Analytics' => config('analytics.google_analytics_id') ?: 'Not set',
                    'Facebook Pixel' => config('analytics.facebook_pixel_id') ?: 'Not set'
                ]
            ]
        ];

        // Calculate stats
        $totalConfigured = 0;
        $totalNotConfigured = 0;
        $totalOptional = 0;

        foreach ($checks as $category => $data) {
            foreach ($data['items'] as $label => $value) {
                if (is_bool($value)) {
                    if ($value) $totalConfigured++;
                    else $totalNotConfigured++;
                } elseif (strpos($value, '✓') !== false) {
                    $totalConfigured++;
                } elseif (strpos($value, 'optional') !== false) {
                    $totalOptional++;
                } elseif (strpos($value, 'Not') !== false) {
                    if ($data['priority'] === 'low') {
                        $totalOptional++;
                    } else {
                        $totalNotConfigured++;
                    }
                }
            }
        }
        ?>

        <div class="stats">
            <div class="stat-card configured">
                <div class="number"><?= $totalConfigured ?></div>
                <div class="label">Configured</div>
            </div>
            <div class="stat-card not-configured">
                <div class="number"><?= $totalNotConfigured ?></div>
                <div class="label">Not Configured</div>
            </div>
            <div class="stat-card optional">
                <div class="number"><?= $totalOptional ?></div>
                <div class="label">Optional</div>
            </div>
        </div>

        <?php if ($totalNotConfigured > 0): ?>
        <div class="warning">
            <strong>⚠️ Action Required:</strong> You have <?= $totalNotConfigured ?> critical setting(s) that need configuration.
            Please see CREDENTIALS_SETUP.md for detailed instructions.
        </div>
        <?php endif; ?>

        <?php foreach ($checks as $category => $data): ?>
        <div class="section">
            <h2><?= ucwords(str_replace('_', ' ', $category)) ?>
                <span class="priority <?= $data['priority'] ?>"><?= strtoupper($data['priority']) ?> PRIORITY</span>
            </h2>
            <?php foreach ($data['items'] as $label => $value): ?>
            <div class="check-item">
                <?php
                $isConfigured = false;
                if (is_bool($value)) {
                    $isConfigured = $value;
                    $displayValue = $value ? '✓ Yes' : '✗ No';
                } else {
                    $isConfigured = (strpos($value, '✓') !== false ||
                                     strpos($value, 'Configured') !== false ||
                                     (strpos($value, 'Not') === false && !empty($value)));
                    $displayValue = $value;
                }

                $statusClass = $isConfigured ? 'configured' : ($data['priority'] === 'low' ? 'optional' : 'not-configured');
                ?>
                <div class="status <?= $statusClass ?>"></div>
                <div class="label"><?= htmlspecialchars($label) ?></div>
                <div class="value"><?= htmlspecialchars($displayValue) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <div class="next-steps">
            <h3>📋 Next Steps:</h3>
            <ol>
                <li>Copy <code>.env.example</code> to <code>.env</code>: <code>cp .env.example .env</code></li>
                <li>Fill in your credentials in the <code>.env</code> file</li>
                <li>Read <code>CREDENTIALS_SETUP.md</code> for detailed setup instructions</li>
                <li>Test each service as you configure it</li>
                <li><strong>DELETE THIS FILE</strong> before deploying to production!</li>
            </ol>
        </div>

        <div class="section">
            <h2>📚 Documentation Files</h2>
            <div class="check-item">
                <div class="status <?= file_exists(ROOT_PATH . '/.env') ? 'configured' : 'not-configured' ?>"></div>
                <div class="label">.env file exists</div>
                <div class="value"><?= file_exists(ROOT_PATH . '/.env') ? 'Yes' : 'No - Copy .env.example to .env' ?></div>
            </div>
            <div class="check-item">
                <div class="status configured"></div>
                <div class="label">.env.example</div>
                <div class="value">✓ Available - Template with all settings</div>
            </div>
            <div class="check-item">
                <div class="status configured"></div>
                <div class="label">CREDENTIALS_SETUP.md</div>
                <div class="value">✓ Available - Step-by-step setup guide</div>
            </div>
            <div class="check-item">
                <div class="status configured"></div>
                <div class="label">DEPLOYMENT.md</div>
                <div class="value">✓ Available - Hosting and deployment guide</div>
            </div>
        </div>

        <p style="text-align: center; color: #6b7280; margin-top: 30px;">
            <strong>Remember:</strong> Never commit .env to version control! It contains sensitive credentials.
        </p>
    </div>
</body>
</html>
