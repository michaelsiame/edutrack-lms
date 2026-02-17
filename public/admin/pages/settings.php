<?php
/**
 * Settings Page - with CSRF protection and Student ID prefix
 */

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        header('Location: ?page=settings&msg=csrf_error');
        exit;
    }

    $siteName = trim($_POST['site_name'] ?? '');
    $currency = trim($_POST['currency'] ?? 'ZMW');
    $supportEmail = trim($_POST['support_email'] ?? '');
    $supportPhone = trim($_POST['support_phone'] ?? '');
    $enableRegistration = isset($_POST['enable_registration']) ? 1 : 0;
    $maintenanceMode = isset($_POST['maintenance_mode']) ? 1 : 0;
    $studentIdPrefix = strtoupper(trim($_POST['student_id_prefix'] ?? 'EST'));

    // Validate prefix (letters only, max 5 chars)
    $studentIdPrefix = preg_replace('/[^A-Z]/', '', $studentIdPrefix);
    if (empty($studentIdPrefix)) $studentIdPrefix = 'EST';
    $studentIdPrefix = substr($studentIdPrefix, 0, 5);

    // Check if settings exist
    $exists = $db->exists('settings', 'id = 1');

    if ($exists) {
        $db->update('settings', [
            'site_name' => $siteName,
            'currency' => $currency,
            'support_email' => $supportEmail,
            'support_phone' => $supportPhone,
            'enable_registration' => $enableRegistration,
            'maintenance_mode' => $maintenanceMode,
            'student_id_prefix' => $studentIdPrefix,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = 1');
    } else {
        $db->insert('settings', [
            'id' => 1,
            'site_name' => $siteName,
            'currency' => $currency,
            'support_email' => $supportEmail,
            'support_phone' => $supportPhone,
            'enable_registration' => $enableRegistration,
            'maintenance_mode' => $maintenanceMode,
            'student_id_prefix' => $studentIdPrefix,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    header('Location: ?page=settings&msg=saved');
    exit;
}

// Fetch current settings from system_settings (legacy) and settings table
$settings = $db->fetchOne("SELECT * FROM system_settings WHERE setting_id = 1");
$appSettings = $db->fetchOne("SELECT * FROM settings WHERE id = 1");
$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Settings</h2>

    <?php if ($msg === 'saved'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas fa-check-circle"></i> Settings saved successfully!
        </div>
    <?php elseif ($msg === 'csrf_error'): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> Security token expired. Please try again.
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <?= csrfField() ?>

        <!-- General Settings -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-cog text-gray-400 mr-2"></i>General Settings
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($appSettings['site_name'] ?? $settings['site_name'] ?? 'EduTrack LMS') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                    <select name="currency" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <?php $curr = $appSettings['currency'] ?? $settings['currency'] ?? 'ZMW'; ?>
                        <option value="ZMW" <?= $curr === 'ZMW' ? 'selected' : '' ?>>ZMW - Zambian Kwacha</option>
                        <option value="USD" <?= $curr === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                        <option value="EUR" <?= $curr === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                        <option value="GBP" <?= $curr === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Student ID Settings -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-id-card text-gray-400 mr-2"></i>Student ID Settings
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student ID Prefix</label>
                    <input type="text" name="student_id_prefix" value="<?= htmlspecialchars($appSettings['student_id_prefix'] ?? 'EST') ?>" maxlength="5" pattern="[A-Za-z]+" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 uppercase" placeholder="EST">
                    <p class="text-xs text-gray-500 mt-1">Letters only, max 5 characters. Format: PREFIX-YYYY-NNNN (e.g., EST-2026-0001)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preview</label>
                    <div class="px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-lg font-mono text-indigo-700 font-semibold" id="idPreview">
                        <?= htmlspecialchars($appSettings['student_id_prefix'] ?? 'EST') ?>-<?= date('Y') ?>-0001
                    </div>
                    <p class="text-xs text-gray-500 mt-1">New students will receive IDs in this format</p>
                </div>
            </div>
        </div>

        <!-- Contact Settings -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-address-book text-gray-400 mr-2"></i>Contact Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                    <input type="email" name="support_email" value="<?= htmlspecialchars($appSettings['support_email'] ?? $settings['support_email'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="support@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
                    <input type="text" name="support_phone" value="<?= htmlspecialchars($appSettings['support_phone'] ?? $settings['support_phone'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="+260 XXX XXX XXX">
                </div>
            </div>
        </div>

        <!-- System Settings -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-sliders-h text-gray-400 mr-2"></i>System Settings
            </h3>
            <div class="space-y-4">
                <label class="flex items-center">
                    <input type="checkbox" name="enable_registration" value="1" <?= ($appSettings['enable_registration'] ?? $settings['enable_registration'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="ml-2 text-gray-700">Enable User Registration</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="maintenance_mode" value="1" <?= ($appSettings['maintenance_mode'] ?? $settings['maintenance_mode'] ?? 0) ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="ml-2 text-gray-700">Maintenance Mode</span>
                    <span class="ml-2 text-xs text-gray-500">(Site will be inaccessible to non-admins)</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 shadow-sm">
                <i class="fas fa-save mr-2"></i>Save Settings
            </button>
        </div>
    </form>

    <!-- Database Stats -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-database text-gray-400 mr-2"></i>Database Statistics
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-gray-800"><?= $db->count('users') ?></p>
                <p class="text-sm text-gray-500">Users</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-gray-800"><?= $db->count('courses') ?></p>
                <p class="text-sm text-gray-500">Courses</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-gray-800"><?= $db->count('enrollments') ?></p>
                <p class="text-sm text-gray-500">Enrollments</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-gray-800"><?= $db->count('students') ?></p>
                <p class="text-sm text-gray-500">Students</p>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('input[name="student_id_prefix"]').addEventListener('input', function() {
    var prefix = this.value.toUpperCase().replace(/[^A-Z]/g, '').substring(0, 5);
    this.value = prefix;
    document.getElementById('idPreview').textContent = (prefix || 'EST') + '-<?= date('Y') ?>-0001';
});
</script>
