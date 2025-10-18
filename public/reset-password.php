<?php
/**
 * Edutrack Computer Training College
 * Reset Password Page
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/auth.php';

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    flash('error', 'Invalid reset link', 'error');
    redirect(url('forgot-password.php'));
}

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif ($password !== $password_confirm) {
            $errors[] = 'Passwords do not match';
        } else {
            $passwordCheck = validatePasswordStrength($password);
            if (!$passwordCheck['valid']) {
                $errors = $passwordCheck['errors'];
            } else {
                $result = resetPassword($token, $password);
                if ($result['success']) {
                    $success = true;
                } else {
                    $errors[] = $result['message'];
                }
            }
        }
    }
}

$page_title = "Reset Password - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <i class="fas fa-key text-primary-600 text-5xl mb-4"></i>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Reset Your Password</h2>
            <p class="text-gray-600">Choose a new password for your account</p>
        </div>
        
        <div class="bg-white shadow-lg rounded-lg p-8">
            <?php if ($success): ?>
                <div class="text-center">
                    <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Password Reset Successful!</h3>
                    <p class="text-gray-600 mb-6">
                        You can now login with your new password.
                    </p>
                    <a href="<?= url('login.php') ?>" class="btn-primary px-6 py-3 rounded-md inline-block">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login Now
                    </a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="mb-6">
                        <?php foreach ($errors as $error): ?>
                            <?php errorAlert($error); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?= csrfField() ?>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               class="block w-full px-3 py-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                               placeholder="Enter new password">
                        <div class="mt-2 text-xs text-gray-600">
                            <p class="font-medium mb-1">Password must contain:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>At least 8 characters</li>
                                <li>One uppercase letter</li>
                                <li>One number</li>
                                <li>One special character</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="password_confirm" 
                               name="password_confirm" 
                               required 
                               class="block w-full px-3 py-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                               placeholder="Confirm new password">
                    </div>
                    
                    <button type="submit" class="w-full btn-primary py-3 px-4 rounded-md font-medium">
                        <i class="fas fa-save mr-2"></i>
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>