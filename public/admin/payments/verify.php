<?php
/**
 * Admin Payment Verification
 * Verify and approve pending payments
 */

// Include shared admin debug helper (like other admin pages)
require_once '../../../src/includes/admin-debug.php';

debugLog("Loading payment verification page");

require_once '../../../src/middleware/admin-only.php';
debugLog("Middleware loaded");

require_once '../../../src/classes/Payment.php';
debugLog("Payment class loaded");

require_once '../../../src/classes/Enrollment.php';
debugLog("Enrollment class loaded");

require_once '../../../src/classes/Email.php';
debugLog("Email class loaded");

// Debug: Log request info
debugLog("Request info", [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'GET' => $_GET,
    'user_id' => $_SESSION['user_id'] ?? 'not set'
]);

// Handle payment verification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    $action = $_POST['action'] ?? null;
    $paymentId = $_POST['payment_id'] ?? null;
    
    if (!$paymentId) {
        flash('message', 'Invalid payment ID', 'error');
        redirect(url('admin/payments/verify.php'));
    }
    
    $payment = Payment::find($paymentId);
    
    if (!$payment) {
        flash('message', 'Payment not found', 'error');
        redirect(url('admin/payments/verify.php'));
    }
    
    if ($action == 'approve') {
        // Approve payment
        $updateData = [
            'payment_status' => 'Completed',
            'payment_date' => date('Y-m-d H:i:s')
        ];

        if ($payment->update($updateData)) {
            // Create enrollment using Enrollment::create()
            // This handles student record creation automatically (no redundant queries)
            // Uses correct schema enum values:
            // enrollment_status: 'Enrolled', 'In Progress', 'Completed', 'Dropped', 'Expired'
            // payment_status: 'pending', 'completed', 'failed', 'refunded'
            $enrollmentId = Enrollment::create([
                'user_id' => $payment->getUserId(),
                'course_id' => $payment->getCourseId(),
                'enrollment_status' => 'Enrolled',
                'payment_status' => 'completed',
                'amount_paid' => $payment->getAmount()
            ]);

            // Send confirmation email
            Email::sendMail($payment->getUserEmail(), 'Payment Approved - Enrollment Confirmed', [
                'name' => $payment->getUserName(),
                'course_title' => $payment->getCourseTitle(),
                'amount' => formatCurrency($payment->getAmount()),
                'reference' => $payment->getTransactionReference(),
                'course_url' => url('learn.php?course=' . $payment->getCourseSlug())
            ]);

            if ($enrollmentId) {
                flash('message', 'Payment approved and student enrolled', 'success');
            } else {
                flash('message', 'Payment approved but student may already be enrolled', 'info');
            }
        } else {
            flash('message', 'Failed to approve payment', 'error');
        }
        
    } elseif ($action == 'reject') {
        // Reject payment
        if ($payment->update(['payment_status' => 'Failed'])) {
            flash('message', 'Payment rejected', 'success');
        } else {
            flash('message', 'Failed to reject payment', 'error');
        }
    }

    redirect(url('admin/payments/verify.php'));
}

// Get pending payments
debugLog("Fetching pending payments...");
$pendingPayments = Payment::all([
    'status' => 'Pending',
    'order' => 'created_at DESC'
]);
debugLog("Pending payments fetched", [
    'count' => count($pendingPayments),
    'payment_ids' => array_column($pendingPayments, 'payment_id')
]);

$page_title = 'Verify Payments';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-6 lg:py-8 max-w-7xl">

    <!-- Page Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-check-circle text-primary-600"></i>
                <span>Payment Verification</span>
            </h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">
                <?= count($pendingPayments) ?> pending payment<?= count($pendingPayments) != 1 ? 's' : '' ?> awaiting review
            </p>
        </div>
        <a href="<?= url('admin/payments/index.php') ?>" class="btn btn-secondary self-start sm:self-center">
            <i class="fas fa-list"></i>
            <span>All Payments</span>
        </a>
    </div>
    
    <?php if (empty($pendingPayments)): ?>

    <!-- Empty State -->
    <div class="bg-white rounded-xl shadow-sm p-8 sm:p-12 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
            <i class="fas fa-check-circle text-green-500 text-4xl"></i>
        </div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">All Caught Up!</h2>
        <p class="text-gray-600 max-w-md mx-auto">There are no pending payments to verify at the moment. Check back later or view all payment history.</p>
        <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="<?= url('admin/payments/index.php') ?>" class="btn btn-primary">
                <i class="fas fa-list"></i>
                View All Payments
            </a>
            <a href="<?= url('admin/index.php') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
    
    <?php else: ?>

    <div class="space-y-4 sm:space-y-6">
        <?php foreach ($pendingPayments as $index => $paymentData): ?>
            <?php
            debugLog("Processing payment #{$index}", ['payment_id' => $paymentData['payment_id'] ?? 'unknown']);

            $payment = new Payment($paymentData['payment_id']);

            // Skip if payment data couldn't be loaded (e.g., related records deleted)
            if (!$payment->exists()) {
                debugLog("Payment skipped - exists() returned false", ['payment_id' => $paymentData['payment_id']]);
                continue;
            }

            debugLog("Payment loaded successfully", [
                'payment_id' => $payment->getId(),
                'user' => $payment->getUserName(),
                'amount' => $payment->getAmount()
            ]);
            ?>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow duration-200">
                <!-- Payment Header with Status -->
                <div class="px-4 sm:px-6 py-4 bg-gradient-to-r from-yellow-50 to-orange-50 border-b border-yellow-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                <i class="fas fa-clock mr-1.5 text-yellow-600"></i>
                                Pending Verification
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <span class="flex items-center gap-1">
                                <i class="far fa-clock"></i>
                                <?= timeAgo($payment->getCreatedAt()) ?>
                            </span>
                            <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                #<?= $payment->getId() ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment Content -->
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Student Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Student Information</h3>
                            <div class="flex items-center gap-3">
                                <img src="<?= getGravatar($payment->getUserEmail()) ?>" class="h-12 w-12 rounded-full ring-2 ring-white shadow-sm">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-900 truncate"><?= sanitize($payment->getUserName()) ?></p>
                                    <p class="text-sm text-gray-600 truncate"><?= sanitize($payment->getUserEmail()) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Course Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Course Information</h3>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-book text-primary-600"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-900 line-clamp-2"><?= sanitize($payment->getCourseTitle()) ?></p>
                                    <a href="<?= url('course.php?slug=' . $payment->getCourseSlug()) ?>"
                                       target="_blank"
                                       class="inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-700 mt-1">
                                        View course <i class="fas fa-external-link-alt text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                            
                    <!-- Payment Details -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Payment Details</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                                <p class="text-xs text-green-600 font-medium mb-1">Amount</p>
                                <p class="text-xl sm:text-2xl font-bold text-green-700"><?= formatCurrency($payment->getAmount()) ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-medium mb-1">Payment Method</p>
                                <p class="font-semibold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-credit-card text-gray-400"></i>
                                    <?= $payment->getPaymentMethodLabel() ?>
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-medium mb-1">Transaction Reference</p>
                                <p class="font-mono text-sm text-gray-900 break-all"><?= sanitize($payment->getTransactionReference()) ?></p>
                            </div>
                        </div>

                        <?php if ($payment->getPhoneNumber() || $payment->getNotes()): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <?php if ($payment->getPhoneNumber()): ?>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-medium mb-1">Phone Number</p>
                                <p class="font-medium text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-phone text-gray-400"></i>
                                    <?= sanitize($payment->getPhoneNumber()) ?>
                                </p>
                            </div>
                            <?php endif; ?>

                            <?php if ($payment->getNotes()): ?>
                            <div class="bg-gray-50 rounded-lg p-4 <?= $payment->getPhoneNumber() ? '' : 'sm:col-span-2' ?>">
                                <p class="text-xs text-gray-500 font-medium mb-1">Notes</p>
                                <p class="text-sm text-gray-900"><?= sanitize($payment->getNotes()) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Proof of Payment -->
                    <?php if ($payment->hasProofOfPayment()): ?>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <i class="fas fa-file-image text-gray-400"></i>
                            Proof of Payment
                        </h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <?php
                            $proofUrl = $payment->getProofOfPaymentUrl();
                            $proofFile = $payment->getProofOfPayment();
                            $isPdf = strtolower(pathinfo($proofFile, PATHINFO_EXTENSION)) === 'pdf';
                            ?>
                            <?php if ($isPdf): ?>
                                <div class="flex items-center gap-4 p-4 bg-white rounded-lg border border-gray-200">
                                    <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900">PDF Document</p>
                                        <p class="text-sm text-gray-500 truncate"><?= sanitize($proofFile) ?></p>
                                    </div>
                                    <a href="<?= $proofUrl ?>" target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-external-link-alt"></i>
                                        View PDF
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="<?= $proofUrl ?>" target="_blank" class="block group">
                                    <div class="relative overflow-hidden rounded-lg border border-gray-200 bg-white">
                                        <img src="<?= $proofUrl ?>" alt="Payment Proof"
                                             class="w-full max-h-72 object-contain group-hover:opacity-90 transition-opacity">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all flex items-center justify-center">
                                            <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-black bg-opacity-50 text-white px-3 py-1.5 rounded-full text-sm">
                                                <i class="fas fa-search-plus mr-1"></i>View Full Size
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <i class="fas fa-file-image text-gray-400"></i>
                            Proof of Payment
                        </h3>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                            <div class="w-12 h-12 bg-yellow-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
                            </div>
                            <p class="font-medium text-yellow-800">No proof of payment uploaded</p>
                            <p class="text-sm text-yellow-600 mt-1">The student did not attach payment proof</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                        <!-- Reject Button -->
                        <button onclick="showRejectModal(<?= $payment->getId() ?>)"
                                class="btn btn-outline-danger order-2 sm:order-1">
                            <i class="fas fa-times"></i>
                            <span>Reject Payment</span>
                        </button>

                        <!-- Approve Button -->
                        <form method="POST" class="order-1 sm:order-2" onsubmit="return confirm('Approve this payment and enroll the student?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="payment_id" value="<?= $payment->getId() ?>">
                            <button type="submit" class="btn btn-success w-full sm:w-auto">
                                <i class="fas fa-check"></i>
                                <span>Approve & Enroll</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
</div>

<!-- Reject Modal -->
<div id="rejectModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"
     onclick="if(event.target === this) closeRejectModal()">
    <div class="bg-white rounded-xl max-w-md w-full shadow-xl transform transition-all"
         onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-times-circle text-red-500"></i>
                Reject Payment
            </h3>
            <button type="button" onclick="closeRejectModal()"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" id="rejectForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="payment_id" id="reject_payment_id">

            <div class="px-6 py-5">
                <p class="text-sm text-gray-600 mb-4">
                    Please provide a reason for rejecting this payment. The student will be notified of the rejection.
                </p>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Rejection <span class="text-red-500">*</span>
                </label>
                <textarea name="notes" rows="4" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors resize-none"
                          placeholder="e.g., Payment amount does not match course price, Invalid transaction reference..."></textarea>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                <button type="button" onclick="closeRejectModal()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Cancel
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i>
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(paymentId) {
    document.getElementById('reject_payment_id').value = paymentId;
    const modal = document.getElementById('rejectModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    // Focus on textarea
    setTimeout(() => {
        modal.querySelector('textarea').focus();
    }, 100);
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
    // Reset form
    document.getElementById('rejectForm').reset();
}

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('rejectModal').classList.contains('hidden')) {
        closeRejectModal();
    }
});
</script>

<?php require_once '../../../src/templates/admin-footer.php'; ?>