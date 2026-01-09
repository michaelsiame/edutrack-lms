<?php
/**
 * Settings Page
 */

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName = trim($_POST['site_name'] ?? '');
    $currency = trim($_POST['currency'] ?? 'ZMW');
    $supportEmail = trim($_POST['support_email'] ?? '');
    $supportPhone = trim($_POST['support_phone'] ?? '');
    $enableRegistration = isset($_POST['enable_registration']) ? 1 : 0;
    $maintenanceMode = isset($_POST['maintenance_mode']) ? 1 : 0;

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
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    header('Location: ?page=settings&msg=saved');
    exit;
}

// Fetch current settings
$settings = $db->fetchOne("SELECT * FROM settings WHERE id = 1");
$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Settings</h2>

    <?php if ($msg === 'saved'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            Settings saved successfully!
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <!-- General Settings -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">General Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'EduTrack LMS') ?>" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                    <select name="currency" class="w-full px-4 py-2 border rounded-lg">
                        <option value="ZMW" <?= ($settings['currency'] ?? 'ZMW') === 'ZMW' ? 'selected' : '' ?>>ZMW - Zambian Kwacha</option>
                        <option value="USD" <?= ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                        <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                        <option value="GBP" <?= ($settings['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Contact Settings -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Contact Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                    <input type="email" name="support_email" value="<?= htmlspecialchars($settings['support_email'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="support@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
                    <input type="text" name="support_phone" value="<?= htmlspecialchars($settings['support_phone'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="+260 XXX XXX XXX">
                </div>
            </div>
        </div>

        <!-- System Settings -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">System Settings</h3>
            <div class="space-y-4">
                <label class="flex items-center">
                    <input type="checkbox" name="enable_registration" value="1" <?= ($settings['enable_registration'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-2 text-gray-700">Enable User Registration</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? 0) ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-2 text-gray-700">Maintenance Mode</span>
                    <span class="ml-2 text-xs text-gray-500">(Site will be inaccessible to non-admins)</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Save Settings
            </button>
        </div>
    </form>

    <!-- Database Stats -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Database Statistics</h3>
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
                <p class="text-2xl font-bold text-gray-800"><?= $db->count('transactions') ?></p>
                <p class="text-sm text-gray-500">Transactions</p>
            </div>
        </div>
    </div>
</div>
