<?php
/**
 * Admin Payment Gateway Settings
 * Configure payment methods (MTN, Airtel, Bank Transfer)
 */

require_once '../../../src/middleware/admin-only.php';

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    
    $settings = [
        // MTN Mobile Money
        'MTN_ENABLED' => isset($_POST['mtn_enabled']) ? 'true' : 'false',
        'MTN_API_KEY' => trim($_POST['mtn_api_key'] ?? ''),
        'MTN_API_SECRET' => trim($_POST['mtn_api_secret'] ?? ''),
        'MTN_SUBSCRIPTION_KEY' => trim($_POST['mtn_subscription_key'] ?? ''),
        
        // Airtel Money
        'AIRTEL_ENABLED' => isset($_POST['airtel_enabled']) ? 'true' : 'false',
        'AIRTEL_CLIENT_ID' => trim($_POST['airtel_client_id'] ?? ''),
        'AIRTEL_CLIENT_SECRET' => trim($_POST['airtel_client_secret'] ?? ''),
        
        // Zamtel Kwacha
        'ZAMTEL_ENABLED' => isset($_POST['zamtel_enabled']) ? 'true' : 'false',
        'ZAMTEL_MERCHANT_ID' => trim($_POST['zamtel_merchant_id'] ?? ''),
        'ZAMTEL_API_KEY' => trim($_POST['zamtel_api_key'] ?? ''),
        
        // Bank Transfer
        'BANK_TRANSFER_ENABLED' => isset($_POST['bank_enabled']) ? 'true' : 'false'
    ];
    
    if (empty($errors)) {
        // Update .env file
        $envFile = ROOT_PATH . '/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            
            foreach ($settings as $key => $value) {
                $value = str_replace('"', '\"', $value);
                
                if (preg_match("/^{$key}=/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $envContent);
                } else {
                    $envContent .= "\n{$key}=\"{$value}\"";
                }
            }
            
            if (file_put_contents($envFile, $envContent)) {
                flash('message', 'Payment gateway settings updated successfully!', 'success');
            } else {
                flash('message', 'Failed to update settings', 'error');
            }
        }
    }
}

$page_title = 'Payment Gateway Settings';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-credit-card text-primary-600 mr-2"></i>
            Payment Gateway Settings
        </h1>
        <p class="text-gray-600 mt-1">Configure payment methods for course enrollment</p>
    </div>
    
    <!-- Info Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">Payment Gateway Setup</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Register with each payment provider to get API credentials</li>
                    <li>Test in sandbox/demo mode before going live</li>
                    <li>Keep API keys secure and never share them</li>
                    <li>Enable at least one payment method for students to enroll</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Settings Form -->
    <form method="POST">
        <?= csrfField() ?>
        
        <div class="space-y-6">
            
            <!-- MTN Mobile Money -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="bg-yellow-100 rounded-lg p-3 mr-4">
                                <i class="fas fa-mobile-alt text-yellow-600 text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">MTN Mobile Money</h2>
                                <p class="text-sm text-gray-600">Accept payments via MTN MoMo</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="mtn_enabled" value="1" class="sr-only peer"
                                   <?= env('MTN_ENABLED', false) ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                </div>
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                        <input type="text" name="mtn_api_key" value="<?= sanitize(env('MTN_API_KEY', '')) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Secret</label>
                        <input type="password" name="mtn_api_secret" value="<?= sanitize(env('MTN_API_SECRET', '')) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subscription Key</label>
                        <input type="text" name="mtn_subscription_key" value="<?= sanitize(env('MTN_SUBSCRIPTION_KEY', '')) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">
                            <strong>How to get credentials:</strong><br>
                            1. Visit <a href="https://momodeveloper.mtn.com" target="_blank" class="text-primary-600 hover:underline">MTN MoMo Developer Portal</a><br>
                            2. Register and create an application<br>
                            3. Get your API credentials from the dashboard
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Airtel Money -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="bg-red-100 rounded-lg p-3 mr-4">
                                <i class="fas fa-mobile-alt text-red-600 text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Airtel Money</h2>
                                <p class="text-sm text-gray-600">Accept payments via Airtel Money</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="airtel_enabled" value="1" class="sr-only peer"
                                   <?= env('AIRTEL_ENABLED', false) ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                </div>
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Client ID</label>
                        <input type="text" name="airtel_client_id" value="<?= sanitize(env('AIRTEL_CLIENT_ID', '')) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Client Secret</label>
                        <input type="password" name="airtel_client_secret" value="<?= sanitize(env('AIRTEL_CLIENT_SECRET', '')) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">
                            <strong>How to get credentials:</strong><br>
                            1. Visit <a href="https://developers.airtel.africa" target="_blank" class="text-primary-600 hover:underline">Airtel Developer Portal</a><br>
                            2. Register your business and create an app<br>
                            3. Get your Client ID and Secret
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Zamtel Kwacha -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="bg-green-100 rounded-lg p-3 mr-4">
                                <i class="fas fa-mobile-alt text-green-600 text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Zamtel Kwacha</h2>
                                <p class="text-sm text-gray-600">Accept payments via Zamtel Kwacha</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="zamtel_enabled" value="1" class="sr-only peer"
                                   <?= env('ZAMTEL_ENABLED', false) ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                </div>
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Merchant ID</label>
                        <input type="text" name="zamtel_merchant_id" value="<?= sanitize(env('ZAMTEL_MERCHANT_ID', '')) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                        <input type="password" name="zamtel_api_key" value="<?= sanitize(env('ZAMTEL_API_KEY', '')) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">
                            <strong>How to get credentials:</strong><br>
                            Contact Zamtel business support to register as a merchant and get your API credentials.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Bank Transfer -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="bg-blue-100 rounded-lg p-3 mr-4">
                                <i class="fas fa-university text-blue-600 text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Bank Transfer</h2>
                                <p class="text-sm text-gray-600">Accept manual bank transfers</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="bank_enabled" value="1" class="sr-only peer"
                                   <?= env('BANK_TRANSFER_ENABLED', true) ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">
                            Bank transfer is always available for students. They can pay directly to your bank account and upload proof of payment for manual verification. Update your bank details in the payment configuration file.
                        </p>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Submit Button -->
        <div class="mt-6 flex justify-end gap-3">
            <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                <i class="fas fa-save mr-2"></i>
                Save Payment Settings
            </button>
        </div>
        
    </form>
    
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>