<?php
/**
 * Admin Payment Verification
 * Verify and approve pending payments
 */

require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Payment.php';
require_once '../../../src/classes/Enrollment.php';
require_once '../../../src/classes/Email.php';

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
            // Get student_id from user_id
            $db = Database::getInstance();
            $student = $db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$payment->getUserId()]);

            // Create enrollment (enrollments table has both user_id AND student_id)
            if ($student) {
                Enrollment::create([
                    'user_id' => $payment->getUserId(),
                    'student_id' => $student['id'],
                    'course_id' => $payment->getCourseId(),
                    'enrollment_status' => 'Enrolled',
                    'payment_status' => 'completed',
                    'amount_paid' => $payment->getAmount()
                ]);
            }
            
            // Send confirmation email
            Email::sendMail($payment->getUserEmail(), 'Payment Approved - Enrollment Confirmed', [
                'name' => $payment->getUserName(),
                'course_title' => $payment->getCourseTitle(),
                'amount' => formatCurrency($payment->getAmount()),
                'reference' => $payment->getTransactionReference(),
                'course_url' => url('learn.php?course=' . $payment->getCourseSlug())
            ]);
            
            flash('message', 'Payment approved and student enrolled', 'success');
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
$pendingPayments = Payment::all([
    'status' => 'Pending',
    'order' => 'created_at DESC'
]);

$page_title = 'Verify Payments';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-check-circle text-primary-600 mr-2"></i>
            Payment Verification
        </h1>
        <p class="text-gray-600 mt-1"><?= count($pendingPayments) ?> pending payments</p>
    </div>
    
    <?php if (empty($pendingPayments)): ?>
    
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <div class="inline-block bg-green-100 rounded-full p-6 mb-4">
            <i class="fas fa-check-circle text-green-600 text-5xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">All Caught Up!</h2>
        <p class="text-gray-600">There are no pending payments to verify at the moment.</p>
        <a href="<?= url('admin/payments/index.php') ?>" class="inline-block mt-6 text-primary-600 hover:text-primary-700">
            View all payments →
        </a>
    </div>
    
    <?php else: ?>
    
    <div class="space-y-6">
        <?php foreach ($pendingPayments as $paymentData): ?>
            <?php $payment = new Payment($paymentData['payment_id']); ?>
            
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        
                        <!-- Payment Details -->
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-4">
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                    Pending Verification
                                </span>
                                <span class="text-sm text-gray-500">
                                    <?= timeAgo($payment->getCreatedAt()) ?>
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Student Info -->
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-2">Student Information</h3>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <img src="<?= getGravatar($payment->getUserEmail()) ?>" class="h-10 w-10 rounded-full mr-3">
                                            <div>
                                                <p class="font-medium text-gray-900"><?= sanitize($payment->getUserName()) ?></p>
                                                <p class="text-sm text-gray-600"><?= sanitize($payment->getUserEmail()) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Course Info -->
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-2">Course Information</h3>
                                    <div class="space-y-1">
                                        <p class="font-medium text-gray-900"><?= sanitize($payment->getCourseTitle()) ?></p>
                                        <a href="<?= url('course.php?slug=' . $payment->getCourseSlug()) ?>" 
                                           target="_blank"
                                           class="text-sm text-primary-600 hover:text-primary-700">
                                            View course <i class="fas fa-external-link-alt text-xs ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Details -->
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h3 class="text-sm font-medium text-gray-500 mb-3">Payment Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Amount</p>
                                        <p class="text-lg font-bold text-gray-900"><?= formatCurrency($payment->getAmount()) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Payment Method</p>
                                        <p class="font-medium text-gray-900"><?= $payment->getPaymentMethodLabel() ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Transaction Reference</p>
                                        <p class="font-mono text-sm text-gray-900"><?= sanitize($payment->getTransactionReference()) ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($payment->getPhoneNumber()): ?>
                                <div class="mt-3">
                                    <p class="text-sm text-gray-600">Phone Number</p>
                                    <p class="font-medium text-gray-900"><?= sanitize($payment->getPhoneNumber()) ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($payment->getNotes()): ?>
                                <div class="mt-3">
                                    <p class="text-sm text-gray-600">Notes</p>
                                    <p class="text-sm text-gray-900"><?= sanitize($payment->getNotes()) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">

                    <!-- Reject Button -->
                    <button onclick="showRejectModal(<?= $payment->getId() ?>)"
                            class="btn btn-danger" style="background-color: transparent !important; color: #dc2626 !important; border: 2px solid #dc2626 !important;">
                        <i class="fas fa-times mr-2"></i>Reject
                    </button>

                    <!-- Approve Button -->
                    <form method="POST" class="inline" onsubmit="return confirm('Approve this payment and enroll the student?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="payment_id" value="<?= $payment->getId() ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check mr-2"></i>Approve & Enroll
                        </button>
                    </form>

                </div>
            </div>
            
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">Reject Payment</h3>
        </div>
        <form method="POST" id="rejectForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="payment_id" id="reject_payment_id">
            
            <div class="px-6 py-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Rejection
                </label>
                <textarea name="notes" rows="4" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                          placeholder="Enter reason for rejecting this payment..."></textarea>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                <button type="button" onclick="closeRejectModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-danger">
                    Reject Payment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(paymentId) {
    document.getElementById('reject_payment_id').value = paymentId;
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
}
</script>

<?php require_once '../../../src/templates/admin-footer.php'; ?>