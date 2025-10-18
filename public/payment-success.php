<?php
/**
 * Payment Success Page
 * Shown after successful payment
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

$page_title = 'Payment Successful - Edutrack';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Success Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            
            <!-- Success Header -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full mb-4">
                    <i class="fas fa-check text-green-500 text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Payment Successful!</h1>
                <p class="text-green-100 text-lg">Your enrollment has been confirmed</p>
            </div>
            
            <!-- Payment Details -->
            <div class="p-8">
                
                <!-- Transaction Info -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Transaction Details</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Reference Number:</span>
                            <span class="font-mono font-semibold"><?= htmlspecialchars($payment->getTransactionReference()) ?></span>
                        </div>
                        <?php if ($payment->getTransactionId()): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Transaction ID:</span>
                            <span class="font-mono"><?= htmlspecialchars($payment->getTransactionId()) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Method:</span>
                            <span class="font-semibold"><?= htmlspecialchars($payment->getPaymentMethodLabel()) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount Paid:</span>
                            <span class="font-bold text-primary-600 text-xl"><?= $payment->getFormattedAmount() ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date & Time:</span>
                            <span><?= date('F j, Y g:i A', strtotime($payment->getCreatedAt())) ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Course Info -->
                <?php if ($course): ?>
                <div class="border-t border-gray-200 pt-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Course Enrollment</h2>
                    <div class="flex items-start space-x-4">
                        <img src="<?= $course->getThumbnailUrl() ?>" 
                             alt="<?= htmlspecialchars($course->getTitle()) ?>"
                             class="w-24 h-24 object-cover rounded-lg">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 text-lg mb-2">
                                <?= htmlspecialchars($course->getTitle()) ?>
                            </h3>
                            <p class="text-gray-600 text-sm mb-3">
                                You now have full access to all course materials
                            </p>
                            <a href="<?= url('learn.php?course=' . $course->getSlug()) ?>" 
                               class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                                <i class="fas fa-play-circle mr-2"></i>
                                Start Learning Now
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- What's Next -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">What happens next?</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">You've been automatically enrolled in the course</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">A confirmation email has been sent to your inbox</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Your invoice is available in your dashboard</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700">Start learning immediately - no waiting!</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Actions -->
                <div class="grid md:grid-cols-2 gap-4">
                    <?php if ($course): ?>
                    <a href="<?= url('learn.php?course=' . $course->getSlug()) ?>" 
                       class="block text-center px-6 py-3 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition">
                        <i class="fas fa-play-circle mr-2"></i>
                        Go to Course
                    </a>
                    <?php endif; ?>
                    <a href="<?= url('my-courses.php') ?>" 
                       class="block text-center px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:border-gray-400 transition">
                        <i class="fas fa-book mr-2"></i>
                        My Courses
                    </a>
                </div>
                
                <!-- Support -->
                <div class="mt-8 text-center text-sm text-gray-500">
                    <p>Need help? <a href="<?= url('contact.php') ?>" class="text-primary-600 hover:text-primary-700">Contact Support</a></p>
                    <p class="mt-2">Save this page or take a screenshot for your records</p>
                </div>
                
            </div>
            
        </div>
        
    </div>
</div>

<script>
// Confetti celebration
if (typeof confetti !== 'undefined') {
    confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 }
    });
}
</script>

<?php require_once '../src/templates/footer.php'; ?>