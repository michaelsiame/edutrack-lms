<?php
/**
 * Edutrack computer training college
 * Forgot Password Page
 */

require_once '../src/bootstrap.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(url('dashboard.php'));
}

$errors = [];
$success = false;
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Rate limiting: Prevent password reset spam (3 attempts per 15 minutes per IP)
        $clientIp = getClientIp();
        if (!checkRateLimit('password_reset_' . $clientIp, 3, 900)) {
            $errors[] = 'Too many password reset attempts. Please try again in 15 minutes.';
        } else {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!validateEmail($email)) {
                $errors[] = 'Please enter a valid email address';
            } else {
                $result = requestPasswordReset($email);
                $success = $result['success'];
                if (!$success) {
                    $errors[] = $result['message'];
                }
            }
        }
    }
}

$page_title = "Forgot Password - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <i class="fas fa-lock text-primary-600 text-5xl mb-4"></i>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Forgot Password?</h2>
            <p class="text-gray-600">Enter your email and we'll send you reset instructions</p>
        </div>
        
        <div class="bg-white shadow-lg rounded-lg p-8">
            <?php if ($success): ?>
                <div class="text-center">
                    <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Check Your Email</h3>
                    <p class="text-gray-600 mb-6">
                        If an account exists with this email, you will receive password reset instructions shortly.
                    </p>
                    <a href="<?= url('login.php') ?>" class="btn-primary px-6 py-3 rounded-md inline-block">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Login
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
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?= sanitize($email) ?>"
                                   required 
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                   placeholder="your.email@example.com">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full btn-primary py-3 px-4 rounded-md font-medium">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Reset Instructions
                    </button>
                </form>
                
                <div class="mt-6 text-center">
                    <a href="<?= url('login.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>