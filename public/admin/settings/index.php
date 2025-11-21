<?php
/**
 * Admin General Settings
 * Configure general application settings
 */

require_once '../../../src/includes/admin-debug.php';
require_once '../../../src/middleware/admin-only.php';

$success = false;
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    
    $settings = [
        'APP_NAME' => trim($_POST['app_name'] ?? ''),
        'SITE_EMAIL' => trim($_POST['site_email'] ?? ''),
        'SITE_PHONE' => trim($_POST['site_phone'] ?? ''),
        'SITE_ADDRESS' => trim($_POST['site_address'] ?? ''),
        'TEVETA_INSTITUTION_CODE' => trim($_POST['teveta_code'] ?? ''),
        'TEVETA_INSTITUTION_NAME' => trim($_POST['teveta_name'] ?? ''),
        'MAINTENANCE_MODE' => isset($_POST['maintenance_mode']) ? 'true' : 'false',
        'CURRENCY' => $_POST['currency'] ?? 'ZMW',
        'CURRENCY_SYMBOL' => $_POST['currency_symbol'] ?? 'K'
    ];
    
    // Validation
    if (empty($settings['APP_NAME'])) {
        $errors['app_name'] = 'Application name is required';
    }
    
    if (empty($settings['SITE_EMAIL']) || !filter_var($settings['SITE_EMAIL'], FILTER_VALIDATE_EMAIL)) {
        $errors['site_email'] = 'Valid email is required';
    }
    
    if (empty($errors)) {
        // Update .env file
        $envFile = ROOT_PATH . '/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            
            foreach ($settings as $key => $value) {
                // Escape quotes in value
                $value = str_replace('"', '\"', $value);
                
                // Update or add the setting
                if (preg_match("/^{$key}=/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $envContent);
                } else {
                    $envContent .= "\n{$key}=\"{$value}\"";
                }
            }
            
            if (file_put_contents($envFile, $envContent)) {
                flash('message', 'Settings updated successfully!', 'success');
                $success = true;
            } else {
                $errors['general'] = 'Failed to update settings file';
            }
        } else {
            $errors['general'] = 'Configuration file not found';
        }
    }
}

$page_title = 'General Settings';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-cog text-primary-600 mr-2"></i>
            General Settings
        </h1>
        <p class="text-gray-600 mt-1">Configure basic application settings</p>
    </div>
    
    <!-- Error Alert -->
    <?php if (!empty($errors['general'])): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
            <p class="text-sm text-red-800"><?= $errors['general'] ?></p>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Settings Form -->
    <div class="bg-white rounded-lg shadow">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            
            <!-- Application Settings -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Application Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Application Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="app_name" value="<?= sanitize(env('APP_NAME', APP_NAME)) ?>" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['app_name']) ? 'border-red-500' : '' ?>">
                        <?php if (isset($errors['app_name'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['app_name'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Currency Code
                            </label>
                            <select name="currency" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                                <option value="ZMW" <?= env('CURRENCY', 'ZMW') == 'ZMW' ? 'selected' : '' ?>>ZMW (Zambian Kwacha)</option>
                                <option value="USD" <?= env('CURRENCY', 'ZMW') == 'USD' ? 'selected' : '' ?>>USD (US Dollar)</option>
                                <option value="EUR" <?= env('CURRENCY', 'ZMW') == 'EUR' ? 'selected' : '' ?>>EUR (Euro)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Currency Symbol
                            </label>
                            <input type="text" name="currency_symbol" value="<?= sanitize(env('CURRENCY_SYMBOL', 'K')) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Contact Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="site_email" value="<?= sanitize(env('SITE_EMAIL', SITE_EMAIL)) ?>" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['site_email']) ? 'border-red-500' : '' ?>">
                        <?php if (isset($errors['site_email'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['site_email'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Phone
                        </label>
                        <input type="text" name="site_phone" value="<?= sanitize(env('SITE_PHONE', SITE_PHONE)) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Address
                        </label>
                        <textarea name="site_address" rows="3"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"><?= sanitize(env('SITE_ADDRESS', SITE_ADDRESS)) ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- TEVETA Settings -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">TEVETA Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Institution Code
                        </label>
                        <input type="text" name="teveta_code" value="<?= sanitize(env('TEVETA_INSTITUTION_CODE', TEVETA_CODE)) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Institution Name
                        </label>
                        <input type="text" name="teveta_name" value="<?= sanitize(env('TEVETA_INSTITUTION_NAME', TEVETA_NAME)) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
            </div>
            
            <!-- System Settings -->
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">System Settings</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1"
                               <?= env('MAINTENANCE_MODE', false) ? 'checked' : '' ?>
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="maintenance_mode" class="ml-2 text-sm text-gray-700">
                            Enable Maintenance Mode
                        </label>
                    </div>
                    <p class="text-sm text-gray-600 ml-6">
                        When enabled, only administrators can access the site
                    </p>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i>
                    Save Changes
                </button>
            </div>
            
        </form>
    </div>
    
    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">Note</p>
                <p>Changes to these settings may require a server restart to take full effect. Some cached values may need to be cleared.</p>
            </div>
        </div>
    </div>
    
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>