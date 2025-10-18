<?php
/**
 * Payment Failed Page
 * Shown when payment fails
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/functions.php';
require_once '../src/middleware/authenticate.php';
require_once '../src/classes/Payment.php';
require_once '../src/classes/Course.php';

$reference = $_GET['reference'] ?? null;

if (!$reference) {
    redirect('my-courses.php');
}

$payment = Payment::findByReference($reference);

if (!$payment || $payment->getUserId() != $_SESSION['user_id']) {
    setFlashMessage('Payment not found', 'error');
    redirect('my-courses.php');
}

$course = $payment->getCourseId() ? Course::find($payment->getCourseId()) : null;

$page_title = 'Payment Failed - Edutrack';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Failed Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            
            <!-- Failed Header -->
            <div class="bg-gradient-to-r from-red-500 to-red-600 p-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full mb-4">
                    <i class="fas fa-times text-red-500 text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Payment Failed</h1>
                <p class="text-red-100 text-lg">Your payment could not be processed</p>
            </div>
            
            <!-- Details -->
            <div class="p-8">
                
                <!-- Error Info -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-bold text-red-900 mb-2">What went wrong?</h2>
                    <p class="text-red-700 mb-4">
                        <?php if ($payment->getNotes()): ?>
                            <?= htmlspecialchars($payment->getNotes()) ?>
                        <?php else: ?>
                            The payment was not completed. This could be due to insufficient funds, 
                            network issues, or cancellation.
                        <?php endif; ?>
                    </p>
                    <div class="text-sm text-red-600">
                        Reference: <?= htmlspecialchars($payment->getTransactionReference()) ?>
                    </div>
                </div>
                
                <!-- Course Info -->
                <?php if ($course): ?>
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Course Details</h2>
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                        <img src="<?= $course->getThumbnailUrl() ?>" 
                             alt="<?= htmlspecialchars($course->getTitle()) ?>"
                             class="w-20 h-20 object-cover rounded-lg">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 mb-1">
                                <?= htmlspecialchars($course->getTitle()) ?>
                            </h3>
                            <p class="text-gray-600 text-sm mb-2">
                                Price: <?= $course->getFormattedPrice() ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- What to do next -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">What should I do?</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Check your account balance and try again</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Ensure you have good network connectivity</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Try a different payment method</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Contact your mobile money provider if the issue persists</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Actions -->
                <div class="grid md:grid-cols-2 gap-4">
                    <?php if ($course): ?>
                    <a href="<?= url('enroll.php?course_id=' . $course->getId()) ?>" 
                       class="block text-center px-6 py-3 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition">
                        <i class="fas fa-redo mr-2"></i>
                        Try Again
                    </a>
                    <?php endif; ?>
                    <a href="<?= url('courses.php') ?>" 
                       class="block text-center px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:border-gray-400 transition">
                        <i class="fas fa-book mr-2"></i>
                        Browse Courses
                    </a>
                </div>
                
                <!-- Support -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-600 mb-3">
                        Having trouble with payments?
                    </p>
                    <a href="<?= url('contact.php') ?>" 
                       class="inline-flex items-center text-primary-600 hover:text-primary-700 font-semibold">
                        <i class="fas fa-headset mr-2"></i>
                        Contact Support
                    </a>
                </div>
                
            </div>
            
        </div>
        
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>