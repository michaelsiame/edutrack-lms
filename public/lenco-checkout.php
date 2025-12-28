<?php
/**
 * Lenco Payment Checkout Page
 *
 * Displays virtual bank account details for Lenco bank transfer payments.
 * Polls for payment confirmation and redirects on success.
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Lenco.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';

// Authentication check
if (!isLoggedIn()) {
    setFlashMessage('Please login to continue with payment', 'error');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$userId = $_SESSION['user_id'];
$reference = $_GET['reference'] ?? null;

if (!$reference) {
    setFlashMessage('Invalid payment reference', 'error');
    redirect('my-courses.php');
}

// Get Lenco instance and transaction
$lenco = new Lenco();
$transaction = $lenco->getPendingTransaction($reference);

if (!$transaction) {
    setFlashMessage('Payment transaction not found', 'error');
    redirect('my-courses.php');
}

// Verify user owns this transaction
if ($transaction['user_id'] != $userId) {
    setFlashMessage('Access denied', 'error');
    redirect('my-courses.php');
}

// Check if already paid
if ($transaction['status'] === 'successful') {
    setFlashMessage('Payment already completed!', 'success');
    redirect('payment-success.php?reference=' . $reference);
}

// Check if expired
$isExpired = strtotime($transaction['expires_at']) < time();
if ($isExpired && $transaction['status'] === 'pending') {
    $lenco->updateTransactionStatus($reference, 'expired');
    setFlashMessage('Payment session has expired. Please try again.', 'error');
    redirect('checkout.php?enrollment_id=' . $transaction['enrollment_id']);
}

// Get course details
$course = null;
if ($transaction['course_id']) {
    $course = Course::find($transaction['course_id']);
}

// Calculate time remaining
$expiresAt = strtotime($transaction['expires_at']);
$timeRemaining = max(0, $expiresAt - time());
$hoursRemaining = floor($timeRemaining / 3600);
$minutesRemaining = floor(($timeRemaining % 3600) / 60);

$page_title = 'Complete Payment - Lenco Bank Transfer';
require_once '../src/templates/header.php';
?>

<!-- Header -->
<section class="bg-gradient-to-r from-primary-900 to-primary-700 text-white py-10">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                <i class="fas fa-university text-3xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold">Bank Transfer Payment</h1>
                <p class="text-primary-200 mt-1">Complete your payment via bank transfer</p>
            </div>
        </div>
    </div>
</section>

<!-- Payment Status Banner -->
<div id="status-banner" class="hidden">
    <div class="bg-green-500 text-white py-4 px-4 text-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="status-message">Payment received! Redirecting...</span>
    </div>
</div>

<section class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4">

        <!-- Payment Instructions Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="bg-primary-600 text-white px-6 py-4">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    Payment Instructions
                </h2>
            </div>
            <div class="p-6">
                <div class="flex items-start gap-4 mb-6">
                    <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-bold flex-shrink-0">1</div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Transfer the exact amount</h3>
                        <p class="text-gray-600">Use the bank details below to make your transfer. Ensure you transfer the exact amount shown.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 mb-6">
                    <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-bold flex-shrink-0">2</div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Wait for confirmation</h3>
                        <p class="text-gray-600">Once your payment is received, this page will automatically update. This usually takes a few minutes.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-bold flex-shrink-0">3</div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Access your course</h3>
                        <p class="text-gray-600">After payment confirmation, you'll be redirected to your course. You'll also receive an email confirmation.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- Bank Account Details -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gray-800 text-white px-6 py-4">
                    <h2 class="text-xl font-bold flex items-center gap-2">
                        <i class="fas fa-credit-card"></i>
                        Bank Account Details
                    </h2>
                </div>
                <div class="p-6 space-y-6">

                    <!-- Account Number -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm text-gray-500 mb-1">Account Number</label>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-mono font-bold text-gray-900" id="account-number">
                                <?= sanitize($transaction['virtual_account_number'] ?? 'Loading...') ?>
                            </span>
                            <button onclick="copyToClipboard('account-number')" class="text-primary-600 hover:text-primary-700" title="Copy">
                                <i class="fas fa-copy text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Bank Name -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm text-gray-500 mb-1">Bank Name</label>
                        <span class="text-xl font-semibold text-gray-900">
                            <?= sanitize($transaction['virtual_account_bank'] ?? 'Lenco Bank') ?>
                        </span>
                    </div>

                    <!-- Account Name -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm text-gray-500 mb-1">Account Name</label>
                        <span class="text-lg font-medium text-gray-900">
                            <?= sanitize($transaction['virtual_account_name'] ?? 'EduTrack Payment') ?>
                        </span>
                    </div>

                    <!-- Amount -->
                    <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4">
                        <label class="block text-sm text-green-600 mb-1">Amount to Pay</label>
                        <div class="flex items-center justify-between">
                            <span class="text-3xl font-bold text-green-700" id="amount">
                                K<?= number_format($transaction['amount'], 2) ?>
                            </span>
                            <button onclick="copyToClipboard('amount')" class="text-green-600 hover:text-green-700" title="Copy">
                                <i class="fas fa-copy text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Reference -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <label class="block text-sm text-yellow-700 mb-1">Payment Reference</label>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-mono font-medium text-yellow-800" id="reference">
                                <?= sanitize($reference) ?>
                            </span>
                            <button onclick="copyToClipboard('reference')" class="text-yellow-600 hover:text-yellow-700" title="Copy">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="text-xs text-yellow-600 mt-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Use this reference in your transfer narration
                        </p>
                    </div>

                </div>
            </div>

            <!-- Payment Summary -->
            <div class="space-y-6">

                <!-- Course Info -->
                <?php if ($course): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Course Details</h3>
                        <div class="flex items-center gap-4">
                            <?php if ($course->getThumbnail()): ?>
                                <img src="<?= url('uploads/courses/' . $course->getThumbnail()) ?>" alt="" class="w-20 h-20 object-cover rounded-lg">
                            <?php else: ?>
                                <div class="w-20 h-20 bg-primary-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-graduation-cap text-primary-600 text-2xl"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h4 class="font-semibold text-gray-900"><?= sanitize($course->getTitle()) ?></h4>
                                <p class="text-sm text-gray-500"><?= $course->getDuration() ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Timer -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clock text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Time Remaining</h3>
                        <div id="countdown" class="text-3xl font-mono font-bold text-orange-600">
                            <?= sprintf('%02d:%02d:00', $hoursRemaining, $minutesRemaining) ?>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Complete your transfer before this expires</p>
                    </div>
                </div>

                <!-- Status Indicator -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6 text-center">
                        <div id="status-indicator" class="mb-4">
                            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto animate-pulse">
                                <i class="fas fa-hourglass-half text-3xl"></i>
                            </div>
                        </div>
                        <h3 id="status-text" class="text-lg font-bold text-gray-900">Awaiting Payment</h3>
                        <p id="status-subtext" class="text-sm text-gray-500 mt-1">We're checking for your payment...</p>
                    </div>
                </div>

                <!-- Help -->
                <div class="bg-blue-50 rounded-xl p-6">
                    <h3 class="font-bold text-blue-900 mb-2">
                        <i class="fas fa-question-circle mr-2"></i>
                        Need Help?
                    </h3>
                    <p class="text-sm text-blue-800 mb-3">
                        If you've made the transfer but it's not showing up, please wait a few minutes. Bank transfers can take up to 30 minutes to process.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <a href="mailto:<?= env('SITE_EMAIL') ?>" class="inline-flex items-center text-sm text-blue-700 hover:text-blue-800">
                            <i class="fas fa-envelope mr-1"></i> <?= env('SITE_EMAIL') ?>
                        </a>
                        <a href="tel:<?= env('SITE_PHONE') ?>" class="inline-flex items-center text-sm text-blue-700 hover:text-blue-800">
                            <i class="fas fa-phone mr-1"></i> <?= env('SITE_PHONE') ?>
                        </a>
                    </div>
                </div>

                <!-- Cancel Button -->
                <div class="text-center">
                    <button onclick="cancelPayment()" class="text-gray-500 hover:text-gray-700 text-sm">
                        <i class="fas fa-times mr-1"></i> Cancel and go back
                    </button>
                </div>

            </div>

        </div>

    </div>
</section>

<script>
const reference = '<?= sanitize($reference) ?>';
const checkInterval = 10000; // Check every 10 seconds
const expiresAt = <?= $expiresAt ?> * 1000;
let checkTimer;
let countdownTimer;

// Copy to clipboard
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.innerText.replace('K', '').trim();

    navigator.clipboard.writeText(text).then(() => {
        // Show toast
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        toast.innerHTML = '<i class="fas fa-check mr-2"></i>Copied to clipboard!';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    });
}

// Check payment status
async function checkPaymentStatus() {
    try {
        const response = await fetch(`/api/lenco-payment.php?action=status&reference=${reference}`);
        const data = await response.json();

        if (data.success && data.status === 'successful') {
            // Payment received!
            clearInterval(checkTimer);
            clearInterval(countdownTimer);
            showPaymentSuccess();
        } else if (data.status === 'expired' || data.status === 'failed') {
            clearInterval(checkTimer);
            clearInterval(countdownTimer);
            showPaymentFailed(data.status);
        }
    } catch (error) {
        console.error('Error checking payment status:', error);
    }
}

// Show payment success
function showPaymentSuccess() {
    document.getElementById('status-banner').classList.remove('hidden');
    document.getElementById('status-indicator').innerHTML = `
        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto">
            <i class="fas fa-check text-3xl"></i>
        </div>
    `;
    document.getElementById('status-text').textContent = 'Payment Received!';
    document.getElementById('status-text').className = 'text-lg font-bold text-green-600';
    document.getElementById('status-subtext').textContent = 'Redirecting to your course...';

    // Redirect after 3 seconds
    setTimeout(() => {
        window.location.href = '/payment-success.php?reference=' + reference;
    }, 3000);
}

// Show payment failed
function showPaymentFailed(status) {
    document.getElementById('status-indicator').innerHTML = `
        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto">
            <i class="fas fa-times text-3xl"></i>
        </div>
    `;
    document.getElementById('status-text').textContent = status === 'expired' ? 'Payment Expired' : 'Payment Failed';
    document.getElementById('status-text').className = 'text-lg font-bold text-red-600';
    document.getElementById('status-subtext').innerHTML = '<a href="/checkout.php?enrollment_id=<?= $transaction['enrollment_id'] ?>" class="text-primary-600 hover:underline">Try again</a>';
}

// Update countdown timer
function updateCountdown() {
    const now = Date.now();
    const remaining = Math.max(0, expiresAt - now);

    if (remaining <= 0) {
        document.getElementById('countdown').textContent = '00:00:00';
        clearInterval(countdownTimer);
        showPaymentFailed('expired');
        return;
    }

    const hours = Math.floor(remaining / (1000 * 60 * 60));
    const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((remaining % (1000 * 60)) / 1000);

    document.getElementById('countdown').textContent =
        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

    // Change color when less than 5 minutes
    if (remaining < 5 * 60 * 1000) {
        document.getElementById('countdown').className = 'text-3xl font-mono font-bold text-red-600';
    }
}

// Cancel payment
async function cancelPayment() {
    if (!confirm('Are you sure you want to cancel this payment?')) return;

    try {
        const response = await fetch('/api/lenco-payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= generateCsrfToken() ?>'
            },
            body: JSON.stringify({
                action: 'cancel',
                reference: reference,
                csrf_token: '<?= generateCsrfToken() ?>'
            })
        });

        const data = await response.json();

        if (data.success) {
            window.location.href = '/my-courses.php';
        } else {
            alert(data.error || 'Failed to cancel payment');
        }
    } catch (error) {
        console.error('Error:', error);
        window.location.href = '/my-courses.php';
    }
}

// Start polling and countdown
checkTimer = setInterval(checkPaymentStatus, checkInterval);
countdownTimer = setInterval(updateCountdown, 1000);

// Initial check
checkPaymentStatus();
updateCountdown();
</script>

<?php require_once '../src/templates/footer.php'; ?>
