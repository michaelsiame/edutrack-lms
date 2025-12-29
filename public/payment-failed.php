<?php
/**
 * Payment Failed Page
 * Shown when payment fails
 */

require_once '../src/bootstrap.php';

// Get error details from query params
$errorCode = $_GET['code'] ?? 'unknown';
$reference = $_GET['ref'] ?? null;
$courseId = filter_input(INPUT_GET, 'course', FILTER_VALIDATE_INT);

// Error messages mapping
$errorMessages = [
    'cancelled' => 'You cancelled the payment process.',
    'declined' => 'Your payment was declined. Please check your payment details and try again.',
    'insufficient' => 'Insufficient funds in your account.',
    'timeout' => 'The payment request timed out. Please try again.',
    'network' => 'A network error occurred. Please check your connection and try again.',
    'invalid' => 'Invalid payment details provided.',
    'expired' => 'The payment session has expired. Please start a new payment.',
    'unknown' => 'An unexpected error occurred during payment processing.'
];

$errorMessage = $errorMessages[$errorCode] ?? $errorMessages['unknown'];

// Get course info if provided
$course = null;
if ($courseId) {
    $course = $db->fetchOne("SELECT id, title, slug FROM courses WHERE id = ?", [$courseId]);
}

$page_title = 'Payment Failed';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-lg mx-auto px-4">

        <!-- Error Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Error Header -->
            <div class="bg-red-600 px-6 py-8 text-center">
                <div class="w-20 h-20 bg-white rounded-full mx-auto flex items-center justify-center mb-4">
                    <i class="fas fa-times-circle text-red-600 text-5xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white">Payment Failed</h1>
                <p class="text-red-100 mt-2">We couldn't process your payment</p>
            </div>

            <!-- Error Details -->
            <div class="p-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-600 mt-0.5 mr-3"></i>
                        <div>
                            <h3 class="font-semibold text-red-900">What happened?</h3>
                            <p class="text-red-700 mt-1"><?= sanitize($errorMessage) ?></p>
                        </div>
                    </div>
                </div>

                <?php if ($reference): ?>
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="text-sm text-gray-600">Reference Number</div>
                    <div class="font-mono text-gray-900"><?= sanitize($reference) ?></div>
                </div>
                <?php endif; ?>

                <!-- What to do next -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-3">What can you do?</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Check that your payment details are correct</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Ensure you have sufficient funds in your account</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Try a different payment method</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Contact your bank if the problem persists</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <?php if ($course): ?>
                    <a href="<?= url('checkout.php?course_id=' . $course['id']) ?>"
                       class="block w-full py-3 px-4 bg-primary-600 text-white text-center font-semibold rounded-lg hover:bg-primary-700 transition">
                        <i class="fas fa-redo mr-2"></i>Try Again
                    </a>
                    <a href="<?= url('course.php?slug=' . urlencode($course['slug'])) ?>"
                       class="block w-full py-3 px-4 bg-gray-200 text-gray-700 text-center font-semibold rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Course
                    </a>
                    <?php else: ?>
                    <a href="<?= url('my-payments.php') ?>"
                       class="block w-full py-3 px-4 bg-primary-600 text-white text-center font-semibold rounded-lg hover:bg-primary-700 transition">
                        <i class="fas fa-credit-card mr-2"></i>View My Payments
                    </a>
                    <a href="<?= url('courses.php') ?>"
                       class="block w-full py-3 px-4 bg-gray-200 text-gray-700 text-center font-semibold rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-book mr-2"></i>Browse Courses
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Support Info -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-headset text-blue-600 text-xl mr-3"></i>
                <div>
                    <h3 class="font-semibold text-blue-900">Need Help?</h3>
                    <p class="text-blue-700 text-sm mt-1">
                        If you continue to experience issues, please contact our support team:
                    </p>
                    <div class="mt-2 space-y-1 text-sm">
                        <p class="text-blue-800">
                            <i class="fas fa-envelope mr-2"></i><?= SITE_EMAIL ?>
                        </p>
                        <p class="text-blue-800">
                            <i class="fas fa-phone mr-2"></i><?= SITE_PHONE ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>
