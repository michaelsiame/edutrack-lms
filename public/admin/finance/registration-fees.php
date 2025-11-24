<?php
/**
 * Registration Fees Management
 * Verify and manage registration fee payments
 */

require_once '../../../src/middleware/finance-only.php';
require_once '../../../src/classes/RegistrationFee.php';

// Handle verification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();

    $action = $_POST['action'] ?? '';
    $feeId = $_POST['fee_id'] ?? null;

    if (!$feeId) {
        flash('message', 'Invalid registration fee ID', 'error');
        redirect(url('admin/finance/registration-fees.php'));
    }

    $fee = RegistrationFee::find($feeId);

    if (!$fee) {
        flash('message', 'Registration fee not found', 'error');
        redirect(url('admin/finance/registration-fees.php'));
    }

    if ($action === 'approve') {
        if ($fee->verify($_SESSION['user_id'])) {
            flash('message', 'Registration fee verified successfully. Student can now enroll in courses.', 'success');
        } else {
            flash('message', 'Failed to verify registration fee', 'error');
        }
    } elseif ($action === 'reject') {
        $notes = $_POST['notes'] ?? '';
        if ($fee->reject($notes)) {
            flash('message', 'Registration fee rejected', 'success');
        } else {
            flash('message', 'Failed to reject registration fee', 'error');
        }
    }

    redirect(url('admin/finance/registration-fees.php'));
}

// Get filter
$filter = $_GET['status'] ?? 'pending';

// Get registration fees based on filter
if ($filter === 'all') {
    $registrationFees = RegistrationFee::all();
} else {
    $registrationFees = RegistrationFee::all(['status' => $filter]);
}

$stats = RegistrationFee::getStats();

$page_title = 'Registration Fees';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-user-plus text-primary-600 mr-2"></i>
                Registration Fees
            </h1>
            <p class="text-gray-600 mt-1">Verify student registration fee payments (K150 bank deposits)</p>
        </div>
        <a href="<?= url('admin/finance/index.php') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Finance
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-3xl font-bold text-yellow-600"><?= $stats['pending'] ?? 0 ?></p>
            <p class="text-sm text-gray-500">Pending</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-3xl font-bold text-green-600"><?= $stats['completed'] ?? 0 ?></p>
            <p class="text-sm text-gray-500">Verified</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-3xl font-bold text-red-600"><?= $stats['failed'] ?? 0 ?></p>
            <p class="text-sm text-gray-500">Rejected</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-3xl font-bold text-primary-600">K<?= number_format($stats['total_collected'] ?? 0, 2) ?></p>
            <p class="text-sm text-gray-500">Collected</p>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <a href="?status=pending"
                   class="px-6 py-4 border-b-2 text-sm font-medium <?= $filter === 'pending' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                    Pending <span class="ml-2 px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 text-xs"><?= $stats['pending'] ?? 0 ?></span>
                </a>
                <a href="?status=completed"
                   class="px-6 py-4 border-b-2 text-sm font-medium <?= $filter === 'completed' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                    Verified
                </a>
                <a href="?status=failed"
                   class="px-6 py-4 border-b-2 text-sm font-medium <?= $filter === 'failed' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                    Rejected
                </a>
                <a href="?status=all"
                   class="px-6 py-4 border-b-2 text-sm font-medium <?= $filter === 'all' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                    All
                </a>
            </nav>
        </div>
    </div>

    <!-- Registration Fees List -->
    <?php if (empty($registrationFees)): ?>
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <div class="inline-block bg-gray-100 rounded-full p-6 mb-4">
            <i class="fas fa-inbox text-gray-400 text-5xl"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">No Registration Fees Found</h2>
        <p class="text-gray-600">There are no registration fees matching this filter.</p>
    </div>
    <?php else: ?>

    <div class="space-y-4">
        <?php foreach ($registrationFees as $feeData): ?>
        <?php $fee = new RegistrationFee($feeData['id']); ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Status Badge -->
                        <div class="flex items-center space-x-3 mb-4">
                            <?= $fee->getStatusBadge() ?>
                            <span class="text-sm text-gray-500">
                                <?= timeAgo($fee->getCreatedAt()) ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Student Info -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Student Information</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <img src="<?= getGravatar($fee->getUserEmail()) ?>" class="h-10 w-10 rounded-full mr-3">
                                        <div>
                                            <p class="font-medium text-gray-900"><?= sanitize($fee->getUserName()) ?></p>
                                            <p class="text-sm text-gray-600"><?= sanitize($fee->getUserEmail()) ?></p>
                                        </div>
                                    </div>
                                    <?php if ($fee->getUserPhone()): ?>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-phone mr-2"></i><?= sanitize($fee->getUserPhone()) ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Payment Details -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Payment Details</h3>
                                <div class="space-y-1">
                                    <p class="text-lg font-bold text-gray-900"><?= $fee->getFormattedAmount() ?></p>
                                    <p class="text-sm text-gray-600">
                                        <strong>Bank:</strong> <?= sanitize($fee->getBankName() ?: 'Not specified') ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <strong>Reference:</strong> <?= sanitize($fee->getBankReference() ?: 'Not provided') ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <strong>Deposit Date:</strong> <?= $fee->getDepositDate() ? date('d M Y', strtotime($fee->getDepositDate())) : 'Not specified' ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Notes</h3>
                                <p class="text-sm text-gray-600"><?= sanitize($fee->getNotes() ?: 'No notes') ?></p>

                                <?php if ($fee->isPaid() && $fee->getVerifiedBy()): ?>
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-xs text-gray-500">
                                        Verified on <?= date('d M Y H:i', strtotime($fee->getVerifiedAt())) ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($fee->isPending()): ?>
            <!-- Action Buttons -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                <button onclick="showRejectModal(<?= $fee->getId() ?>)"
                        class="btn btn-danger" style="background-color: transparent !important; color: #dc2626 !important; border: 2px solid #dc2626 !important;">
                    <i class="fas fa-times mr-2"></i>Reject
                </button>

                <form method="POST" class="inline" onsubmit="return confirm('Verify this registration fee? The student will be able to enroll in courses.')">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="fee_id" value="<?= $fee->getId() ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-2"></i>Verify Payment
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">Reject Registration Fee</h3>
        </div>
        <form method="POST" id="rejectForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="fee_id" id="reject_fee_id">

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
function showRejectModal(feeId) {
    document.getElementById('reject_fee_id').value = feeId;
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
}
</script>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
