<?php
/**
 * My Payments
 * Student view of their payment status and history
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/classes/RegistrationFee.php';
require_once __DIR__ . '/../src/classes/PaymentPlan.php';
require_once __DIR__ . '/../src/classes/Payment.php';
require_once __DIR__ . '/../src/classes/Lenco.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$userId = $_SESSION['user_id'];
$user = User::current();

// Get registration fee status
$registrationFee = RegistrationFee::findByUser($userId);
$registrationRequired = RegistrationFee::isRequired();
$registrationPaid = RegistrationFee::hasPaid($userId);

// Get all payment plans (course fees)
$paymentPlans = PaymentPlan::getByUser($userId);

// Get payment history
$paymentHistory = Payment::getByUser($userId);

// Get Lenco transactions (pending and successful)
$lenco = new Lenco();
$lencoTransactions = [];
try {
    $lencoTransactions = $lenco->getUserTransactions($userId);
} catch (Exception $e) {
    // Lenco table might not exist yet
}

// Calculate totals
$totalFees = 0;
$totalPaid = 0;
$totalBalance = 0;

foreach ($paymentPlans as $plan) {
    $totalFees += floatval($plan['total_fee']);
    $totalPaid += floatval($plan['total_paid']);
    $totalBalance += floatval($plan['balance']);
}

$page_title = 'My Payments';
require_once __DIR__ . '/../src/templates/header.php';
?>

<div class="min-h-screen py-8" style="background: var(--surface-primary);">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold" style="color: var(--text-primary);">My Payments</h1>
            <p class="mt-1" style="color: var(--text-muted);">View your payment status and history</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Registration Fee -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm" style="color: var(--text-muted);">Registration Fee</p>
                        <?php if ($registrationPaid): ?>
                        <p class="text-2xl font-bold" style="color: var(--status-success);">Paid</p>
                        <?php elseif ($registrationFee && $registrationFee->isPending()): ?>
                        <p class="text-2xl font-bold" style="color: var(--status-warning);">Pending</p>
                        <?php else: ?>
                        <p class="text-2xl font-bold" style="color: var(--status-error);">Not Paid</p>
                        <?php endif; ?>
                    </div>
                    <div class="stat-card-icon" style="background: var(--surface-tertiary);">
                        <?php if ($registrationPaid): ?>
                        <i class="fas fa-check text-xl" style="color: var(--status-success);"></i>
                        <?php elseif ($registrationFee && $registrationFee->isPending()): ?>
                        <i class="fas fa-clock text-xl" style="color: var(--status-warning);"></i>
                        <?php else: ?>
                        <i class="fas fa-times text-xl" style="color: var(--status-error);"></i>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Total Fees -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm" style="color: var(--text-muted);">Total Course Fees</p>
                        <p class="text-2xl font-bold" style="color: var(--text-primary);">K<?= number_format($totalFees, 2) ?></p>
                    </div>
                    <div class="stat-card-icon" style="background: var(--color-primary-100);">
                        <i class="fas fa-receipt text-xl" style="color: var(--accent-primary);"></i>
                    </div>
                </div>
            </div>

            <!-- Total Paid -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm" style="color: var(--text-muted);">Total Paid</p>
                        <p class="text-2xl font-bold" style="color: var(--status-success);">K<?= number_format($totalPaid, 2) ?></p>
                    </div>
                    <div class="stat-card-icon" style="background: var(--status-success-bg);">
                        <i class="fas fa-coins text-xl" style="color: var(--status-success);"></i>
                    </div>
                </div>
            </div>

            <!-- Outstanding Balance -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm" style="color: var(--text-muted);">Outstanding Balance</p>
                        <p class="text-2xl font-bold" style="color: <?= $totalBalance > 0 ? 'var(--status-error)' : 'var(--status-success)' ?>;">
                            K<?= number_format($totalBalance, 2) ?>
                        </p>
                    </div>
                    <div class="stat-card-icon" style="background: <?= $totalBalance > 0 ? 'var(--status-error-bg)' : 'var(--status-success-bg)' ?>;">
                        <i class="fas <?= $totalBalance > 0 ? 'fa-exclamation-circle' : 'fa-check-circle' ?> text-xl" style="color: <?= $totalBalance > 0 ? 'var(--status-error)' : 'var(--status-success)' ?>;"></i>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($totalBalance > 0): ?>
        <!-- Outstanding Balance Alert -->
        <div class="rounded-lg p-4 mb-8" style="background: var(--status-warning-bg); border: 1px solid var(--status-warning);">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-xl" style="color: var(--status-warning);"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium" style="color: var(--text-primary);">Outstanding Balance</h3>
                    <p class="mt-1 text-sm" style="color: var(--text-secondary);">
                        You have an outstanding balance of <strong>K<?= number_format($totalBalance, 2) ?></strong>.
                        Please make a payment at the office or via bank transfer to clear your balance.
                        Note: You will not be able to receive your certificate until your fees are fully paid.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!$registrationPaid): ?>
        <!-- Registration Fee Section -->
        <div class="rounded-lg mb-8" style="background: var(--surface-secondary); box-shadow: var(--shadow-card);">
            <div class="px-6 py-4 border-b" style="border-color: var(--border-primary);">
                <h2 class="text-lg font-semibold" style="color: var(--text-primary);">
                    <i class="fas fa-user-plus mr-2" style="color: var(--accent-primary);"></i>
                    Registration Fee
                </h2>
            </div>
            <div class="p-6">
                <?php if ($registrationFee && $registrationFee->isPending()): ?>
                <div class="rounded-lg p-4" style="background: var(--status-warning-bg); border: 1px solid var(--status-warning);">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-2xl mr-4" style="color: var(--status-warning);"></i>
                        <div>
                            <h3 class="font-medium" style="color: var(--text-primary);">Payment Under Review</h3>
                            <p class="text-sm mt-1" style="color: var(--text-secondary);">
                                Your registration fee payment is being verified. This usually takes 24 hours.
                            </p>
                            <p class="text-sm mt-2" style="color: var(--text-secondary);">
                                <strong>Reference:</strong> <?= sanitize($registrationFee->getBankReference()) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="flex items-center justify-between">
                    <div>
                        <p style="color: var(--text-secondary);">
                            A one-time registration fee of <strong>K<?= number_format(RegistrationFee::getFeeAmount(), 2) ?></strong>
                            is required before you can enroll in courses.
                        </p>
                    </div>
                    <a href="<?= url('registration-fee.php') ?>" class="btn-primary inline-flex items-center">
                        <i class="fas fa-credit-card mr-2"></i>Pay Now
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Course Fees Section -->
        <div class="rounded-lg mb-8" style="background: var(--surface-secondary); box-shadow: var(--shadow-card);">
            <div class="px-6 py-4 border-b" style="border-color: var(--border-primary);">
                <h2 class="text-lg font-semibold" style="color: var(--text-primary);">
                    <i class="fas fa-graduation-cap mr-2" style="color: var(--accent-primary);"></i>
                    Course Fees
                </h2>
            </div>

            <?php if (empty($paymentPlans)): ?>
            <div class="empty-state">
                <div class="empty-state-icon" style="background: var(--surface-tertiary);">
                    <i class="fas fa-book-open text-2xl" style="color: var(--text-muted);"></i>
                </div>
                <p style="color: var(--text-secondary);">You haven't enrolled in any courses yet.</p>
                <a href="<?= url('courses.php') ?>" class="btn-primary mt-4 inline-flex items-center">
                    Browse courses
                </a>
            </div>
            <?php else: ?>
            <div class="divide-y" style="border-color: var(--border-primary);">
                <?php foreach ($paymentPlans as $planData): ?>
                <?php $plan = new PaymentPlan($planData['id']); ?>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-semibold" style="color: var(--text-primary);"><?= sanitize($plan->getCourseTitle()) ?></h3>
                            <p class="text-sm" style="color: var(--text-muted);">
                                Enrolled: <?= date('d M Y', strtotime($plan->getCreatedAt())) ?>
                            </p>
                        </div>
                        <?= $plan->getStatusBadge() ?>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <p class="text-sm" style="color: var(--text-muted);">Course Fee</p>
                            <p class="font-semibold" style="color: var(--text-primary);"><?= $plan->getFormattedTotalFee() ?></p>
                        </div>
                        <div>
                            <p class="text-sm" style="color: var(--text-muted);">Amount Paid</p>
                            <p class="font-semibold" style="color: var(--status-success);"><?= $plan->getFormattedTotalPaid() ?></p>
                        </div>
                        <div>
                            <p class="text-sm" style="color: var(--text-muted);">Balance</p>
                            <p class="font-semibold" style="color: <?= $plan->getBalance() > 0 ? 'var(--status-error)' : 'var(--status-success)' ?>">
                                <?= $plan->getFormattedBalance() ?>
                            </p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-2">
                        <div class="flex justify-between text-sm mb-1">
                            <span style="color: var(--text-muted);">Payment Progress</span>
                            <span class="font-medium" style="color: var(--text-secondary);"><?= $plan->getProgressPercentage() ?>%</span>
                        </div>
                        <div class="w-full rounded-full h-2.5" style="background: var(--surface-tertiary);">
                            <div class="h-2.5 rounded-full" style="width: <?= $plan->getProgressPercentage() ?>%; background: var(--accent-primary);"></div>
                        </div>
                    </div>

                    <?php if ($plan->getBalance() > 0): ?>
                    <div class="mt-4 flex items-center text-sm rounded-lg p-3" style="background: var(--status-warning-bg); color: var(--status-warning);">
                        <i class="fas fa-info-circle mr-2"></i>
                        Certificate blocked until balance is cleared. Visit the office to make a payment.
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pending Lenco Transactions -->
        <?php
        $pendingLenco = array_filter($lencoTransactions, function($tx) {
            return $tx['status'] === 'pending';
        });
        if (!empty($pendingLenco)):
        ?>
        <div class="rounded-lg mb-8" style="background: var(--surface-secondary); box-shadow: var(--shadow-card);">
            <div class="px-6 py-4 border-b" style="background: var(--status-warning-bg); border-color: var(--status-warning);">
                <h2 class="text-lg font-semibold" style="color: var(--text-primary);">
                    <i class="fas fa-clock mr-2" style="color: var(--status-warning);"></i>
                    Pending Bank Transfers
                </h2>
            </div>
            <div class="divide-y" style="border-color: var(--border-primary);">
                <?php foreach ($pendingLenco as $tx): ?>
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background: var(--status-warning-bg); color: var(--status-warning);">
                                    <i class="fas fa-hourglass-half mr-1"></i>Awaiting Payment
                                </span>
                                <span class="text-sm font-mono" style="color: var(--text-muted);"><?= sanitize($tx['reference']) ?></span>
                            </div>
                            <p class="font-semibold" style="color: var(--text-primary);"><?= sanitize($tx['course_title'] ?? 'Course Payment') ?></p>
                            <div class="mt-2 text-sm" style="color: var(--text-secondary);">
                                <p><strong>Amount:</strong> K<?= number_format($tx['amount'], 2) ?></p>
                                <?php if ($tx['virtual_account_number']): ?>
                                <p><strong>Account:</strong> <?= sanitize($tx['virtual_account_number']) ?> (<?= sanitize($tx['virtual_account_bank'] ?? 'Lenco') ?>)</p>
                                <?php endif; ?>
                                <p><strong>Expires:</strong> <?= date('d M Y, g:i A', strtotime($tx['expires_at'])) ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold" style="color: var(--accent-primary);">K<?= number_format($tx['amount'], 2) ?></p>
                            <a href="lenco-checkout.php?reference=<?= urlencode($tx['reference']) ?>"
                               class="inline-block mt-2 text-sm font-medium" style="color: var(--accent-primary);">
                                View Payment Details <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Payment History -->
        <div class="rounded-lg" style="background: var(--surface-secondary); box-shadow: var(--shadow-card);">
            <div class="px-6 py-4 border-b" style="border-color: var(--border-primary);">
                <h2 class="text-lg font-semibold" style="color: var(--text-primary);">
                    <i class="fas fa-history mr-2" style="color: var(--accent-primary);"></i>
                    Payment History
                </h2>
            </div>

            <?php if (empty($paymentHistory) && empty($lencoTransactions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon" style="background: var(--surface-tertiary);">
                    <i class="fas fa-receipt text-2xl" style="color: var(--text-muted);"></i>
                </div>
                <p style="color: var(--text-secondary);">No payment history available.</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y" style="border-color: var(--border-primary);">
                    <thead style="background: var(--surface-tertiary);">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-muted);">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-muted);">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-muted);">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-muted);">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-muted);">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-primary);">
                        <?php
                        // Combine regular payments with successful Lenco transactions
                        $allPayments = $paymentHistory;

                        // Add successful Lenco transactions that aren't already in payments
                        $successfulLenco = array_filter($lencoTransactions, function($tx) {
                            return $tx['status'] === 'successful';
                        });
                        foreach ($successfulLenco as $tx) {
                            $allPayments[] = [
                                'created_at' => $tx['paid_at'] ?? $tx['created_at'],
                                'course_title' => $tx['course_title'] ?? 'Course Payment',
                                'amount' => $tx['amount'],
                                'transaction_id' => $tx['reference'],
                                'payment_status' => 'Completed',
                                'payment_type' => 'Lenco'
                            ];
                        }

                        // Sort by date descending
                        usort($allPayments, function($a, $b) {
                            return strtotime($b['created_at']) - strtotime($a['created_at']);
                        });
                        ?>

                        <?php foreach ($allPayments as $payment): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-primary);">
                                <?= date('d M Y', strtotime($payment['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-primary);">
                                <?= sanitize($payment['course_title'] ?? 'N/A') ?>
                                <?php if (isset($payment['payment_type']) && $payment['payment_type'] === 'Lenco'): ?>
                                <span class="ml-1 text-xs" style="color: var(--accent-primary);"><i class="fas fa-university"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold" style="color: var(--text-primary);">
                                K<?= number_format($payment['amount'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono" style="color: var(--text-muted);">
                                <?= sanitize($payment['transaction_id'] ?? 'N/A') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusStyles = [
                                    'Completed' => 'background: var(--status-success-bg); color: var(--status-success);',
                                    'Pending' => 'background: var(--status-warning-bg); color: var(--status-warning);',
                                    'Failed' => 'background: var(--status-error-bg); color: var(--status-error);',
                                    'Refunded' => 'background: var(--status-info-bg); color: var(--status-info);'
                                ];
                                $style = $statusStyles[$payment['payment_status']] ?? 'background: var(--surface-tertiary); color: var(--text-muted);';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" style="<?= $style ?>">
                                    <?= sanitize($payment['payment_status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Payment Info -->
        <div class="mt-8 rounded-lg p-6" style="background: var(--status-info-bg); border: 1px solid var(--status-info);">
            <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary);">
                <i class="fas fa-info-circle mr-2" style="color: var(--status-info);"></i>Payment Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm" style="color: var(--text-secondary);">
                <div>
                    <h4 class="font-medium mb-2" style="color: var(--text-primary);">Payment Methods</h4>
                    <ul class="list-disc list-inside space-y-1">
                        <li><i class="fas fa-university mr-1" style="color: var(--accent-primary);"></i> <strong>Lenco Bank Transfer</strong> (Instant - Recommended)</li>
                        <li>Cash payment at the college office</li>
                        <li>Bank deposit with proof upload</li>
                        <li>Mobile Money (MTN, Airtel, Zamtel)</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium mb-2" style="color: var(--text-primary);">Important Notes</h4>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Lenco payments are verified automatically</li>
                        <li>Course fees can be paid in installments (min 30%)</li>
                        <li>Certificates are issued only after full payment</li>
                        <li>Upload payment proof for manual verification</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
