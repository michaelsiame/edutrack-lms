<?php
/**
 * Edutrack Computer Training College
 * Email Verification Page
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/auth.php';

// Get token from URL
$token = $_GET['token'] ?? '';

$success = false;
$message = '';

if (empty($token)) {
    $message = 'Invalid verification link';
} else {
    $result = verifyEmail($token);
    $success = $result['success'];
    $message = $result['message'];
}

$page_title = "Verify Email - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-white shadow-lg rounded-lg p-8 text-center">
            <?php if ($success): ?>
                <i class="fas fa-check-circle text-green-500 text-6xl mb-6"></i>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Email Verified!</h2>
                <p class="text-gray-600 mb-8"><?= sanitize($message) ?></p>
                <a href="<?= url('login.php') ?>" class="btn-primary px-8 py-3 rounded-md inline-block">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Login to Your Account
                </a>
            <?php else: ?>
                <i class="fas fa-times-circle text-red-500 text-6xl mb-6"></i>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Verification Failed</h2>
                <p class="text-gray-600 mb-8"><?= sanitize($message) ?></p>
                <div class="space-y-3">
                    <a href="<?= url('login.php') ?>" class="block btn-primary px-6 py-3 rounded-md">
                        Go to Login
                    </a>
                    <a href="<?= url() ?>" class="block text-primary-600 hover:text-primary-700">
                        Return to Homepage
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>