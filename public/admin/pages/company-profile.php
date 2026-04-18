<?php
/**
 * Company Profile View Page
 * Displays current company configuration
 */

// Prevent direct access - must go through admin index
if (!defined('ADMIN_ACCESS')) {
    die('Direct access not permitted');
}

// Load current configuration
$companyData = [
    'Basic Information' => [
        'Company Name' => APP_NAME,
        'Trading Name' => SITE_NAME,
        'Website URL' => APP_URL,
        'Environment' => APP_ENV,
        'Timezone' => APP_TIMEZONE,
    ],
    'Contact Details' => [
        'Primary Email' => SITE_EMAIL,
        'Alternate Email' => SITE_ALT_EMAIL,
        'Primary Phone' => SITE_PHONE,
        'Secondary Phone' => SITE_PHONE2 ?: '<span class="text-gray-400">(not set)</span>',
        'Physical Address' => SITE_ADDRESS,
    ],
    'TEVETA Registration' => [
        'Institution Code' => TEVETA_CODE,
        'Institution Name' => TEVETA_NAME,
        'Registration URL' => '<a href="https://www.teveta.org.zm" target="_blank" class="text-blue-600 hover:underline">teveta.org.zm</a>',
        'Verified Status' => TEVETA_VERIFIED ? '<span class="text-green-600 font-semibold">✓ Verified</span>' : '<span class="text-yellow-600">Pending</span>',
    ],
    'Brand Colors' => [
        'Primary Color' => '<span class="inline-block w-6 h-6 rounded mr-2 align-middle" style="background-color: ' . PRIMARY_COLOR . '"></span>' . PRIMARY_COLOR,
        'Secondary Color' => '<span class="inline-block w-6 h-6 rounded mr-2 align-middle" style="background-color: ' . SECONDARY_COLOR . '"></span>' . SECONDARY_COLOR,
        'Success Color' => '<span class="inline-block w-6 h-6 rounded mr-2 align-middle" style="background-color: ' . SUCCESS_COLOR . '"></span>' . SUCCESS_COLOR,
        'Danger Color' => '<span class="inline-block w-6 h-6 rounded mr-2 align-middle" style="background-color: ' . DANGER_COLOR . '"></span>' . DANGER_COLOR,
    ],
    'Social Media' => [
        'Facebook' => config('social.facebook') ?: '<span class="text-gray-400">(not set)</span>',
        'Twitter' => config('social.twitter') ?: '<span class="text-gray-400">(not set)</span>',
        'Instagram' => config('social.instagram') ?: '<span class="text-gray-400">(not set)</span>',
        'LinkedIn' => config('social.linkedin') ?: '<span class="text-gray-400">(not set)</span>',
        'YouTube' => config('social.youtube') ?: '<span class="text-gray-400">(not set)</span>',
    ],
    'System Settings' => [
        'Currency' => CURRENCY . ' (' . CURRENCY_SYMBOL . ')',
        'Upload Max Size' => config('upload.max_size_mb') . ' MB',
        'Session Lifetime' => config('session.lifetime') . ' seconds',
        'Debug Mode' => APP_DEBUG ? '<span class="text-red-600 font-semibold">Enabled</span>' : '<span class="text-green-600">Disabled</span>',
    ],
];

// Check for issues
$issues = [];
if (strpos(TEVETA_CODE, 'XXX') !== false) {
    $issues[] = [
        'type' => 'error',
        'message' => 'TEVETA Institution Code is using placeholder value (TEVETA/XXX/2024). Please update with actual registration number.',
        'fix' => 'Set TEVETA_INSTITUTION_CODE in .env file or config/app.php'
    ];
}
if (empty(SITE_PHONE)) {
    $issues[] = [
        'type' => 'error',
        'message' => 'Primary phone number is not set. This is critical for student contact.',
        'fix' => 'Set SITE_PHONE in .env file (e.g., +260770666937)'
    ];
}
if (empty(SITE_PHONE2)) {
    $issues[] = [
        'type' => 'warning',
        'message' => 'Secondary phone number is not set.',
        'fix' => 'Set SITE_PHONE2 in .env file if you have an alternate contact number.'
    ];
}
if (empty(SITE_EMAIL)) {
    $issues[] = [
        'type' => 'error',
        'message' => 'Primary email is not set. This is critical for system notifications.',
        'fix' => 'Set SITE_EMAIL in .env file (e.g., info@edutrackzambia.com)'
    ];
}
if (empty(config('social.facebook')) && empty(config('social.linkedin'))) {
    $issues[] = [
        'type' => 'warning',
        'message' => 'No social media links configured.',
        'fix' => 'Set at least one social media URL (Facebook recommended for reach).'
    ];
}
?>

<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Company Profile</h1>
        <p class="text-gray-600 mt-2">View current company configuration and branding settings</p>
    </div>

    <!-- Issues Alert Box -->
    <?php if (!empty($issues)): ?>
    <div class="mb-8 space-y-4">
        <?php foreach ($issues as $issue): ?>
        <div class="rounded-lg p-4 <?= $issue['type'] === 'error' ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200' ?>">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-<?= $issue['type'] === 'error' ? 'exclamation-circle text-red-400' : 'exclamation-triangle text-yellow-400' ?> text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium <?= $issue['type'] === 'error' ? 'text-red-800' : 'text-yellow-800' ?>">
                        <?= $issue['type'] === 'error' ? 'Critical Issue' : 'Warning' ?>
                    </h3>
                    <div class="mt-2 text-sm <?= $issue['type'] === 'error' ? 'text-red-700' : 'text-yellow-700' ?>">
                        <p><?= $issue['message'] ?></p>
                        <p class="mt-1 font-medium">Fix: <?= $issue['fix'] ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Configuration Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <?php foreach ($companyData as $section => $items): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($section) ?></h2>
            </div>
            <div class="divide-y divide-gray-200">
                <?php foreach ($items as $label => $value): ?>
                <div class="px-6 py-4 flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600"><?= htmlspecialchars($label) ?></span>
                    <span class="text-sm text-gray-900"><?= $value ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Edit Instructions -->
    <div class="mt-8 bg-blue-50 rounded-lg p-6 border border-blue-200">
        <h3 class="text-lg font-semibold text-blue-900 mb-4">
            <i class="fas fa-info-circle mr-2"></i>How to Update Company Information
        </h3>
        <div class="prose prose-blue text-blue-800">
            <p><strong>Option 1: Environment Variables (Recommended)</strong></p>
            <p>Edit the <code>.env</code> file in the root directory and set the appropriate values:</p>
            <pre class="bg-white p-4 rounded border border-blue-200 overflow-x-auto"><code>TEVETA_INSTITUTION_CODE="TEVETA/ACTUAL/CODE/2024"
SITE_PHONE2="+260XXXXXXXX"
FACEBOOK_URL="https://facebook.com/edutrackzambia"
LINKEDIN_URL="https://linkedin.com/company/edutrack"</code></pre>
            
            <p class="mt-4"><strong>Option 2: Config File</strong></p>
            <p>Edit <code>config/app.php</code> and update the default values directly.</p>
            
            <p class="mt-4"><strong>After making changes:</strong></p>
            <ul class="list-disc list-inside">
                <li>Clear any cache if enabled</li>
                <li>Refresh this page to see updated values</li>
                <li>Changes will appear immediately on all pages</li>
            </ul>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="https://www.teveta.org.zm" target="_blank" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                    <i class="fas fa-external-link-alt text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">TEVETA Website</h3>
                    <p class="text-sm text-gray-500">Verify registration</p>
                </div>
            </div>
        </a>
        
        <a href="../" target="_blank" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                    <i class="fas fa-globe text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">View Website</h3>
                    <p class="text-sm text-gray-500">See public-facing site</p>
                </div>
            </div>
        </a>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-full p-3">
                    <i class="fas fa-palette text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Brand Colors</h3>
                    <p class="text-sm text-gray-500">Primary: <?= PRIMARY_COLOR ?>, Secondary: <?= SECONDARY_COLOR ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
