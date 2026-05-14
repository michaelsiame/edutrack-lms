<?php
/**
 * Settings Page - System Configuration
 */

require_once __DIR__ . '/../../../src/includes/security.php';

// All configurable settings
$allSettings = [
    // General
    'site_name', 'site_tagline', 'site_description', 'site_keywords',
    'default_currency', 'timezone',
    // Contact
    'site_email', 'support_email', 'support_phone', 'site_phone2',
    'site_address', 'site_city', 'site_country',
    // Social
    'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url', 'linkedin_url',
    // Intake
    'next_intake_date', 'next_intake_label',
    // TEVETA
    'teveta_enabled', 'teveta_institution_code',
    // Certificates
    'certificate_footer_text', 'certificate_signatory_name', 'certificate_signatory_title',
    // System
    'enable_registration', 'maintenance_mode', 'enable_email_notifications',
    'enable_sms_notifications', 'session_timeout',
    // Payments
    'registration_fee_amount', 'minimum_deposit_percentage',
];

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        validateCsrf();
    } catch (Exception $e) {
        header('Location: ?page=settings&msg=csrf_error');
        exit;
    }

    foreach ($allSettings as $key) {
        $value = '';
        if (in_array($key, ['enable_registration', 'maintenance_mode', 'enable_email_notifications', 'enable_sms_notifications', 'teveta_enabled'])) {
            $value = isset($_POST[$key]) ? 'true' : 'false';
        } elseif (isset($_POST[$key])) {
            $value = trim($_POST[$key]);
        }

        $exists = $db->exists('system_settings', 'setting_key = ?', [$key]);
        if ($exists) {
            $db->update('system_settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
        } else {
            $db->insert('system_settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_type' => in_array($key, ['enable_registration', 'maintenance_mode', 'enable_email_notifications', 'enable_sms_notifications', 'teveta_enabled']) ? 'Boolean' : 'String',
                'is_editable' => 1
            ]);
        }
    }

    header('Location: ?page=settings&msg=saved');
    exit;
}

// Fetch current settings
$settingsRows = $db->fetchAll("SELECT setting_key, setting_value FROM system_settings");
$settings = [];
foreach ($settingsRows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$msg = $_GET['msg'] ?? '';
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        <p class="text-gray-500 mt-1">Configure system-wide preferences</p>
    </div>

    <?php if ($msg): ?>
        <div class="mb-6 <?= $msg === 'csrf_error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' ?> border px-4 py-3 rounded-xl">
            <i class="fas <?= $msg === 'csrf_error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?> mr-2"></i>
            <?= $msg === 'csrf_error' ? 'Security check failed. Please refresh and try again.' : 'Settings saved successfully!' ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <?= csrfField(); ?>

        <!-- General Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-globe text-blue-600 text-sm"></i>
                </div>
                General Settings
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'EduTrack LMS') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Tagline</label>
                    <input type="text" name="site_tagline" value="<?= htmlspecialchars($settings['site_tagline'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Description</label>
                    <textarea name="site_description" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                    <select name="default_currency" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="ZMW" <?= ($settings['default_currency'] ?? 'ZMW') === 'ZMW' ? 'selected' : '' ?>>ZMW - Zambian Kwacha</option>
                        <option value="USD" <?= ($settings['default_currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                        <option value="EUR" <?= ($settings['default_currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                        <option value="GBP" <?= ($settings['default_currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                    <select name="timezone" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="Africa/Lusaka" <?= ($settings['timezone'] ?? 'Africa/Lusaka') === 'Africa/Lusaka' ? 'selected' : '' ?>>Africa/Lusaka (CAT, UTC+2)</option>
                        <option value="UTC" <?= ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                        <option value="Africa/Johannesburg" <?= ($settings['timezone'] ?? '') === 'Africa/Johannesburg' ? 'selected' : '' ?>>Africa/Johannesburg</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Contact Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-address-card text-green-600 text-sm"></i>
                </div>
                Contact Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Email</label>
                    <input type="email" name="site_email" value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500" placeholder="edutrackzambia@gmail.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                    <input type="email" name="support_email" value="<?= htmlspecialchars($settings['support_email'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Phone</label>
                    <input type="text" name="support_phone" value="<?= htmlspecialchars($settings['support_phone'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500" placeholder="+260 XXX XXX XXX">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Secondary Phone</label>
                    <input type="text" name="site_phone2" value="<?= htmlspecialchars($settings['site_phone2'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Physical Address</label>
                    <textarea name="site_address" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($settings['site_address'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="site_city" value="<?= htmlspecialchars($settings['site_city'] ?? 'Kalomo') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <input type="text" name="site_country" value="<?= htmlspecialchars($settings['site_country'] ?? 'Zambia') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
        </div>

        <!-- Social Media -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-share-alt text-purple-600 text-sm"></i>
                </div>
                Social Media Links
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                    <input type="url" name="facebook_url" value="<?= htmlspecialchars($settings['facebook_url'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500" placeholder="https://facebook.com/...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Twitter / X</label>
                    <input type="url" name="twitter_url" value="<?= htmlspecialchars($settings['twitter_url'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500" placeholder="https://twitter.com/...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                    <input type="url" name="instagram_url" value="<?= htmlspecialchars($settings['instagram_url'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500" placeholder="https://instagram.com/...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">YouTube</label>
                    <input type="url" name="youtube_url" value="<?= htmlspecialchars($settings['youtube_url'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500" placeholder="https://youtube.com/...">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label>
                    <input type="url" name="linkedin_url" value="<?= htmlspecialchars($settings['linkedin_url'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500" placeholder="https://linkedin.com/...">
                </div>
            </div>
        </div>

        <!-- Next Intake -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-calendar-check text-orange-600 text-sm"></i>
                </div>
                Next Intake
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Intake Date</label>
                    <input type="date" name="next_intake_date" value="<?= htmlspecialchars($settings['next_intake_date'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Intake Label</label>
                    <input type="text" name="next_intake_label" value="<?= htmlspecialchars($settings['next_intake_label'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500" placeholder="e.g. January 2026 Intake">
                </div>
            </div>
        </div>

        <!-- Certificate Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-certificate text-yellow-600 text-sm"></i>
                </div>
                Certificate Settings
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Footer Text</label>
                    <input type="text" name="certificate_footer_text" value="<?= htmlspecialchars($settings['certificate_footer_text'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Signatory Name</label>
                    <input type="text" name="certificate_signatory_name" value="<?= htmlspecialchars($settings['certificate_signatory_name'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Signatory Title</label>
                    <input type="text" name="certificate_signatory_title" value="<?= htmlspecialchars($settings['certificate_signatory_title'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500" placeholder="e.g. Director">
                </div>
            </div>
        </div>

        <!-- TEVETA Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-award text-indigo-600 text-sm"></i>
                </div>
                TEVETA Accreditation
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="teveta_enabled" value="1" <?= ($settings['teveta_enabled'] ?? 'true') === 'true' ? 'checked' : '' ?> class="w-5 h-5 text-primary-600 rounded border-gray-300">
                        <span class="text-sm font-medium text-gray-700">Show TEVETA branding on certificates</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Institution Code</label>
                    <input type="text" name="teveta_institution_code" value="<?= htmlspecialchars($settings['teveta_institution_code'] ?? 'TVA/2064') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
        </div>

        <!-- Payment Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-money-bill-wave text-emerald-600 text-sm"></i>
                </div>
                Payment Settings
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registration Fee (ZMW)</label>
                    <input type="number" name="registration_fee_amount" value="<?= htmlspecialchars($settings['registration_fee_amount'] ?? '150') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Deposit (%)</label>
                    <input type="number" name="minimum_deposit_percentage" value="<?= htmlspecialchars($settings['minimum_deposit_percentage'] ?? '30') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500" min="0" max="100">
                </div>
            </div>
        </div>

        <!-- System Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-cog text-gray-600 text-sm"></i>
                </div>
                System Settings
            </h3>
            <div class="space-y-4">
                <label class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <input type="checkbox" name="enable_registration" value="1" <?= ($settings['enable_registration'] ?? 'true') === 'true' ? 'checked' : '' ?> class="w-5 h-5 text-primary-600 rounded border-gray-300">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Enable User Registration</span>
                        <p class="text-xs text-gray-500">Allow new users to register on the site</p>
                    </div>
                </label>
                <label class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? 'false') === 'true' ? 'checked' : '' ?> class="w-5 h-5 text-primary-600 rounded border-gray-300">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Maintenance Mode</span>
                        <p class="text-xs text-gray-500">Site will be inaccessible to non-admins</p>
                    </div>
                </label>
                <label class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <input type="checkbox" name="enable_email_notifications" value="1" <?= ($settings['enable_email_notifications'] ?? 'true') === 'true' ? 'checked' : '' ?> class="w-5 h-5 text-primary-600 rounded border-gray-300">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Enable Email Notifications</span>
                        <p class="text-xs text-gray-500">Send automated emails for enrollments, payments, etc.</p>
                    </div>
                </label>
                <label class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <input type="checkbox" name="enable_sms_notifications" value="1" <?= ($settings['enable_sms_notifications'] ?? 'false') === 'true' ? 'checked' : '' ?> class="w-5 h-5 text-primary-600 rounded border-gray-300">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Enable SMS Notifications</span>
                        <p class="text-xs text-gray-500">Send SMS alerts to users (requires SMS gateway)</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium shadow-sm">
                <i class="fas fa-save mr-2"></i>Save All Settings
            </button>
        </div>
    </form>

    <!-- Database Stats -->
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Statistics</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-xl">
                <p class="text-2xl font-bold text-gray-900"><?= $db->count('users') ?></p>
                <p class="text-sm text-gray-500">Users</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-xl">
                <p class="text-2xl font-bold text-gray-900"><?= $db->count('courses') ?></p>
                <p class="text-sm text-gray-500">Courses</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-xl">
                <p class="text-2xl font-bold text-gray-900"><?= $db->count('enrollments') ?></p>
                <p class="text-sm text-gray-500">Enrollments</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-xl">
                <p class="text-2xl font-bold text-gray-900"><?= $db->count('payments') ?></p>
                <p class="text-sm text-gray-500">Payments</p>
            </div>
        </div>
    </div>
</div>
