<?php
/**
 * Payment Success Page
 *
 * Displayed after successful payment completion
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';
require_once '../src/classes/Payment.php';
require_once '../src/classes/Lenco.php';

// Authentication check
if (!isLoggedIn()) {
    setFlashMessage('Please login to view your payment', 'error');
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$reference = $_GET['reference'] ?? null;
$paymentId = $_GET['payment_id'] ?? null;

$payment = null;
$lencoTransaction = null;
$course = null;
$enrollment = null;

// Try to get payment details
if ($reference) {
    // Check if it's a Lenco transaction
    $lenco = new Lenco();
    $lencoTransaction = $lenco->getPendingTransaction($reference);

    if ($lencoTransaction) {
        // Verify user owns this transaction
        if ($lencoTransaction['user_id'] != $userId) {
            setFlashMessage('Access denied', 'error');
            redirect('my-courses.php');
        }

        // Get course info
        if ($lencoTransaction['course_id']) {
            $course = Course::find($lencoTransaction['course_id']);
        }

        // Get enrollment info
        if ($lencoTransaction['enrollment_id']) {
            $enrollment = Enrollment::find($lencoTransaction['enrollment_id']);
        }
    } else {
        // Try regular payment lookup
        $payment = Payment::findByReference($reference);

        if ($payment && $payment->getUserId() != $userId) {
            setFlashMessage('Access denied', 'error');
            redirect('my-courses.php');
        }

        if ($payment && $payment->getCourseId()) {
            $course = Course::find($payment->getCourseId());
        }
    }
} elseif ($paymentId) {
    $payment = Payment::find($paymentId);

    if ($payment && $payment->getUserId() != $userId) {
        setFlashMessage('Access denied', 'error');
        redirect('my-courses.php');
    }

    if ($payment && $payment->getCourseId()) {
        $course = Course::find($payment->getCourseId());
    }
}

// Check if we have any payment info
if (!$payment && !$lencoTransaction) {
    setFlashMessage('Payment not found', 'error');
    redirect('my-courses.php');
}

// Get payment details
$amount = 0;
$transactionRef = '';
$paymentStatus = '';
$paymentDate = '';

if ($lencoTransaction) {
    $amount = floatval($lencoTransaction['amount']);
    $transactionRef = $lencoTransaction['reference'];
    $paymentStatus = $lencoTransaction['status'];
    $paymentDate = $lencoTransaction['paid_at'] ?? $lencoTransaction['created_at'];
} elseif ($payment) {
    $amount = $payment->getAmount();
    $transactionRef = $payment->getTransactionReference();
    $paymentStatus = $payment->getStatus();
    $paymentDate = $payment->getPaymentDate() ?? $payment->getCreatedAt();
}

$isSuccessful = in_array(strtolower($paymentStatus), ['completed', 'successful', 'success']);

$page_title = 'Payment ' . ($isSuccessful ? 'Successful' : 'Received');
require_once '../src/templates/header.php';
?>

<!-- Confetti Animation for Success -->
<?php if ($isSuccessful): ?>
<style>
@keyframes confetti-fall {
    0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
    100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
}
.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    top: 0;
    animation: confetti-fall 3s ease-out forwards;
    z-index: 50;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
    for (let i = 0; i < 50; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDelay = Math.random() * 2 + 's';
        document.body.appendChild(confetti);
        setTimeout(() => confetti.remove(), 5000);
    }
});
</script>
<?php endif; ?>

<!-- Header -->
<section class="bg-gradient-to-r from-green-600 to-emerald-600 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
            <?php if ($isSuccessful): ?>
                <i class="fas fa-check-circle text-green-500 text-5xl"></i>
            <?php else: ?>
                <i class="fas fa-clock text-yellow-500 text-5xl"></i>
            <?php endif; ?>
        </div>
        <h1 class="text-4xl font-bold mb-2">
            <?= $isSuccessful ? 'Payment Successful!' : 'Payment Received' ?>
        </h1>
        <p class="text-green-100 text-lg">
            <?= $isSuccessful
                ? 'Thank you for your payment. Your course access has been activated.'
                : 'Your payment is being processed. You\'ll be notified once it\'s confirmed.'
            ?>
        </p>
    </div>
</section>

<section class="py-12 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4">

        <!-- Payment Details Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gray-800 text-white px-6 py-4">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <i class="fas fa-receipt"></i>
                    Payment Details
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-sm text-gray-500">Transaction Reference</p>
                        <p class="font-mono font-semibold text-gray-900"><?= sanitize($transactionRef) ?></p>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-sm text-gray-500">Amount Paid</p>
                        <p class="text-2xl font-bold text-green-600">K<?= number_format($amount, 2) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <?php if ($isSuccessful): ?>
                            <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                <i class="fas fa-check-circle mr-1"></i> Completed
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                <i class="fas fa-clock mr-1"></i> <?= ucfirst($paymentStatus) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Date</p>
                        <p class="font-medium text-gray-900">
                            <?= date('F j, Y g:i A', strtotime($paymentDate)) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Info Card -->
        <?php if ($course): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="p-6">
                <div class="flex items-center gap-6">
                    <?php if ($course->getThumbnail()): ?>
                        <img src="<?= url('uploads/courses/' . $course->getThumbnail()) ?>" alt=""
                             class="w-24 h-24 object-cover rounded-lg shadow">
                    <?php else: ?>
                        <div class="w-24 h-24 bg-primary-100 rounded-lg flex items-center justify-center shadow">
                            <i class="fas fa-graduation-cap text-primary-600 text-3xl"></i>
                        </div>
                    <?php endif; ?>
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 mb-1">Course Enrolled</p>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?= sanitize($course->getTitle()) ?></h3>
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-clock mr-1"></i> <?= $course->getDuration() ?>
                            <?php if ($course->getModuleCount()): ?>
                                <span class="mx-2">|</span>
                                <i class="fas fa-book mr-1"></i> <?= $course->getModuleCount() ?> modules
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Next Steps -->
        <div class="bg-blue-50 rounded-xl p-6 mb-8">
            <h3 class="text-lg font-bold text-blue-900 mb-4">
                <i class="fas fa-list-check mr-2"></i>
                What's Next?
            </h3>
            <div class="space-y-3">
                <?php if ($isSuccessful): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-green-500 text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check text-xs"></i>
                        </div>
                        <p class="text-gray-700">Your course access has been activated</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-green-500 text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check text-xs"></i>
                        </div>
                        <p class="text-gray-700">A confirmation email has been sent to your inbox</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-bold">1</span>
                        </div>
                        <p class="text-gray-700">Start learning by clicking "Go to My Courses" below</p>
                    </div>
                <?php else: ?>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-yellow-500 text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-hourglass-half text-xs"></i>
                        </div>
                        <p class="text-gray-700">Your payment is being verified (usually within 30 minutes)</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-gray-300 text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-bold">2</span>
                        </div>
                        <p class="text-gray-700">You'll receive an email once your payment is confirmed</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-gray-300 text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-bold">3</span>
                        </div>
                        <p class="text-gray-700">Your course access will be unlocked automatically</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="my-courses.php" class="btn-primary px-8 py-4 rounded-lg font-bold text-lg text-center shadow-lg hover:shadow-xl transition-all">
                <i class="fas fa-book-open mr-2"></i>
                Go to My Courses
            </a>
            <a href="my-payments.php" class="btn-secondary px-8 py-4 rounded-lg font-bold text-lg text-center">
                <i class="fas fa-receipt mr-2"></i>
                View Payment History
            </a>
        </div>

        <!-- Support Info -->
        <div class="mt-8 text-center text-sm text-gray-600">
            <p>
                Need help? Contact us at
                <a href="mailto:<?= env('SITE_EMAIL') ?>" class="text-primary-600 hover:underline"><?= env('SITE_EMAIL') ?></a>
                or call <a href="tel:<?= env('SITE_PHONE') ?>" class="text-primary-600 hover:underline"><?= env('SITE_PHONE') ?></a>
            </p>
        </div>

    </div>
</section>

<?php require_once '../src/templates/footer.php'; ?>
