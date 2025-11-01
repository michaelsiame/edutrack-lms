<?php
/**
 * Admin Email Settings
 * Configure SMTP and email notifications
 */

require_once '../../../src/middleware/admin-only.php';

$errors = [];
$testResult = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    
    $action = $_POST['action'] ?? 'save';
    
    if ($action == 'test') {
        // Send test email
        $testEmail = $_POST['test_email'] ?? $_SESSION['user_email'];
        
        if (filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Test Email from ' . APP_NAME;
            $message = '<h1>Test Email</h1><p>This is a test email from your LMS. If you received this, your email configuration is working correctly!</p>';
            
            if (sendEmail($testEmail, $subject, $message)) {
                $testResult = ['success' => true, 'message' => 'Test email sent successfully to ' . $testEmail];
            } else {
                $testResult = ['success' => false, 'message' => 'Failed to send test email. Check your SMTP settings.'];
            }
        } else {
            $testResult = ['success' => false, 'message' => 'Invalid email address'];
        }
    } else {
        // Save settings
        $settings = [
            'MAIL_ENABLED' => isset($_POST['mail_enabled']) ? 'true' : 'false',
            'MAIL_HOST' => trim($_POST['mail_host'] ?? ''),
            'MAIL_PORT' => trim($_POST['mail_port'] ?? '587'),
            'MAIL_USERNAME' => trim($_POST['mail_username'] ?? ''),
            'MAIL_PASSWORD' => trim($_POST['mail_password'] ?? ''),
            'MAIL_ENCRYPTION' => $_POST['mail_encryption'] ?? 'tls',
            'MAIL_FROM_ADDRESS' => trim($_POST['mail_from_address'] ?? ''),
            'MAIL_FROM_NAME' => trim($_POST['mail_from_name'] ?? '')
        ];
        
        // Validation
        if ($settings['MAIL_ENABLED'] == 'true') {
            if (empty($settings['MAIL_HOST'])) {
                $errors['mail_host'] = 'SMTP host is required';
            }
            if (empty($settings['MAIL_FROM_ADDRESS']) || !filter_var($settings['MAIL_FROM_ADDRESS'], FILTER_VALIDATE_EMAIL)) {
                $errors['mail_from_address'] = 'Valid from email is required';
            }
        }
        
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
                    flash('message', 'Email settings updated successfully!', 'success');
                } else {
                    flash('message', 'Failed to update settings', 'error');
                }
            }
        }
    }
}

$page_title = 'Email Settings';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-envelope text-primary-600 mr-2"></i>
            Email Settings
        </h1>
        <p class="text-gray-600 mt-1">Configure SMTP and email notifications</p>
    </div>
    
    <!-- Test Result -->
    <?php if ($testResult): ?>
    <div class="mb-6 p-4 rounded-lg <?= $testResult['success'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' ?>">
        <div class="flex items-center">
            <i class="fas <?= $testResult['success'] ? 'fa-check-circle text-green-600' : 'fa-exclamation-circle text-red-600' ?> mr-3"></i>
            <p class="text-sm <?= $testResult['success'] ? 'text-green-800' : 'text-red-800' ?>">
                <?= $testResult['message'] ?>
            </p>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Info Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">Gmail SMTP Setup (Recommended)</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Use your Gmail account for sending emails</li>
                    <li>Enable 2-factor authentication on your Google account</li>
                    <li>Generate an App Password at <a href="https://myaccount.google.com/apppasswords" target="_blank" class="underline">myaccount.google.com/apppasswords</a></li>
                    <li>Use the App Password instead of your regular password</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Settings Form -->
    <div class="bg-white rounded-lg shadow">
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save">
            
            <!-- Email Status -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Email Notifications</h2>
                        <p class="text-sm text-gray-600">Enable or disable email functionality</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="mail_enabled" value="1" class="sr-only peer"
                               <?= env('MAIL_ENABLED', false) ? 'checked' : '' ?>>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                </div>
            </div>
            
            <!-- SMTP Settings -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">SMTP Configuration</h2>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                SMTP Host <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="mail_host" value="<?= sanitize(env('MAIL_HOST', 'smtp.gmail.com')) ?>"
                                   placeholder="smtp.gmail.com"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['mail_host']) ? 'border-red-500' : '' ?>">
                            <?php if (isset($errors['mail_host'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?= $errors['mail_host'] ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                SMTP Port <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="mail_port" value="<?= sanitize(env('MAIL_PORT', '587')) ?>"
                                   placeholder="587"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Username / Email
                        </label>
                        <input type="text" name="mail_username" value="<?= sanitize(env('MAIL_USERNAME', '')) ?>"
                               placeholder="your-email@gmail.com"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Password / App Password
                        </label>
                        <input type="password" name="mail_password" value="<?= sanitize(env('MAIL_PASSWORD', '')) ?>"
                               placeholder="Enter your app password"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        <p class="text-xs text-gray-500 mt-1">For Gmail, use App Password instead of your regular password</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Encryption
                        </label>
                        <select name="mail_encryption" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="tls" <?= env('MAIL_ENCRYPTION', 'tls') == 'tls' ? 'selected' : '' ?>>TLS (Port 587)</option>
                            <option value="ssl" <?= env('MAIL_ENCRYPTION', 'tls') == 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- From Settings -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">From Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            From Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="mail_from_address" value="<?= sanitize(env('MAIL_FROM_ADDRESS', '')) ?>"
                               placeholder="noreply@edutrack.zm"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['mail_from_address']) ? 'border-red-500' : '' ?>">
                        <?php if (isset($errors['mail_from_address'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['mail_from_address'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            From Name
                        </label>
                        <input type="text" name="mail_from_name" value="<?= sanitize(env('MAIL_FROM_NAME', APP_NAME)) ?>"
                               placeholder="Edutrack LMS"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="p-6 bg-gray-50 flex justify-between">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i>
                    Save Settings
                </button>
            </div>
            
        </form>
        
        <!-- Test Email Form -->
        <form method="POST" class="p-6 border-t border-gray-200 bg-gray-50">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="test">
            
            <h3 class="text-lg font-bold text-gray-900 mb-4">Test Email Configuration</h3>
            
            <div class="flex gap-4">
                <div class="flex-1">
                    <input type="email" name="test_email" value="<?= sanitize($_SESSION['user_email'] ?? '') ?>"
                           placeholder="Enter email address"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send Test Email
                </button>
            </div>
        </form>
    </div>
    
    <!-- Common SMTP Providers -->
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Common SMTP Providers</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">Gmail</h4>
                <p class="text-sm text-gray-600">
                    Host: smtp.gmail.com<br>
                    Port: 587 (TLS)<br>
                    Use App Password
                </p>
            </div>
            
            <div class="border rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">Outlook</h4>
                <p class="text-sm text-gray-600">
                    Host: smtp-mail.outlook.com<br>
                    Port: 587 (TLS)<br>
                    Use account password
                </p>
            </div>
            
            <div class="border rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">SendGrid</h4>
                <p class="text-sm text-gray-600">
                    Host: smtp.sendgrid.net<br>
                    Port: 587 (TLS)<br>
                    Use API key
                </p>
            </div>
        </div>
    </div>
    
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>