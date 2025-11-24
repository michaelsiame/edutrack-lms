<?php
/**
 * My Payments
 * Student view of their payment status and history
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/RegistrationFee.php';
require_once '../src/classes/PaymentPlan.php';
require_once '../src/classes/Payment.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$userId = $_SESSION['user_id'];
$user = getCurrentUser();

// Get registration fee status
$registrationFee = RegistrationFee::findByUser($userId);
$registrationRequired = RegistrationFee::isRequired();
$registrationPaid = RegistrationFee::hasPaid($userId);

// Get all payment plans (course fees)
$paymentPlans = PaymentPlan::getByUser($userId);

// Get payment history
$paymentHistory = Payment::getByUser($userId);

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
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Payments</h1>
            <p class="text-gray-600 mt-1">View your payment status and history</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Registration Fee -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Registration Fee</p>
                        <?php if ($registrationPaid): ?>
                        <p class="text-2xl font-bold text-green-600">Paid</p>
                        <?php elseif ($registrationFee && $registrationFee->isPending()): ?>
                        <p class="text-2xl font-bold text-yellow-600">Pending</p>
                        <?php else: ?>
                        <p class="text-2xl font-bold text-red-600">Not Paid</p>
                        <?php endif; ?>
                    </div>
                    <div class="bg-gray-100 p-3 rounded-full">
                        <?php if ($registrationPaid): ?>
                        <i class="fas fa-check text-green-600 text-xl"></i>
                        <?php elseif ($registrationFee && $registrationFee->isPending()): ?>
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        <?php else: ?>
                        <i class="fas fa-times text-red-600 text-xl"></i>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Total Fees -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Course Fees</p>
                        <p class="text-2xl font-bold text-gray-900">K<?= number_format($totalFees, 2) ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-receipt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Paid -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Paid</p>
                        <p class="text-2xl font-bold text-green-600">K<?= number_format($totalPaid, 2) ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-coins text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Outstanding Balance -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Outstanding Balance</p>
                        <p class="text-2xl font-bold <?= $totalBalance > 0 ? 'text-red-600' : 'text-green-600' ?>">
                            K<?= number_format($totalBalance, 2) ?>
                        </p>
                    </div>
                    <div class="<?= $totalBalance > 0 ? 'bg-red-100' : 'bg-green-100' ?> p-3 rounded-full">
                        <i class="fas <?= $totalBalance > 0 ? 'fa-exclamation-circle text-red-600' : 'fa-check-circle text-green-600' ?> text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($totalBalance > 0): ?>
        <!-- Outstanding Balance Alert -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Outstanding Balance</h3>
                    <p class="mt-1 text-sm text-yellow-700">
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
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-user-plus text-primary-600 mr-2"></i>
                    Registration Fee
                </h2>
            </div>
            <div class="p-6">
                <?php if ($registrationFee && $registrationFee->isPending()): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-yellow-500 text-2xl mr-4"></i>
                        <div>
                            <h3 class="font-medium text-yellow-800">Payment Under Review</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                Your registration fee payment is being verified. This usually takes 24 hours.
                            </p>
                            <p class="text-sm text-yellow-700 mt-2">
                                <strong>Reference:</strong> <?= sanitize($registrationFee->getBankReference()) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600">
                            A one-time registration fee of <strong>K<?= number_format(RegistrationFee::getFeeAmount(), 2) ?></strong>
                            is required before you can enroll in courses.
                        </p>
                    </div>
                    <a href="<?= url('registration-fee.php') ?>" class="btn btn-primary">
                        <i class="fas fa-credit-card mr-2"></i>Pay Now
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Course Fees Section -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-graduation-cap text-primary-600 mr-2"></i>
                    Course Fees
                </h2>
            </div>

            <?php if (empty($paymentPlans)): ?>
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-book-open text-4xl mb-2"></i>
                <p>You haven't enrolled in any courses yet.</p>
                <a href="<?= url('courses.php') ?>" class="text-primary-600 hover:underline mt-2 inline-block">
                    Browse courses
                </a>
            </div>
            <?php else: ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($paymentPlans as $planData): ?>
                <?php $plan = new PaymentPlan($planData['id']); ?>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-900"><?= sanitize($plan->getCourseTitle()) ?></h3>
                            <p class="text-sm text-gray-500">
                                Enrolled: <?= date('d M Y', strtotime($plan->getCreatedAt())) ?>
                            </p>
                        </div>
                        <?= $plan->getStatusBadge() ?>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Course Fee</p>
                            <p class="font-semibold text-gray-900"><?= $plan->getFormattedTotalFee() ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Amount Paid</p>
                            <p class="font-semibold text-green-600"><?= $plan->getFormattedTotalPaid() ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Balance</p>
                            <p class="font-semibold <?= $plan->getBalance() > 0 ? 'text-red-600' : 'text-green-600' ?>">
                                <?= $plan->getFormattedBalance() ?>
                            </p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-2">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-500">Payment Progress</span>
                            <span class="text-gray-700 font-medium"><?= $plan->getProgressPercentage() ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-primary-600 h-2.5 rounded-full" style="width: <?= $plan->getProgressPercentage() ?>%"></div>
                        </div>
                    </div>

                    <?php if ($plan->getBalance() > 0): ?>
                    <div class="mt-4 flex items-center text-sm text-yellow-700 bg-yellow-50 rounded-lg p-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        Certificate blocked until balance is cleared. Visit the office to make a payment.
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Payment History -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-history text-primary-600 mr-2"></i>
                    Payment History
                </h2>
            </div>

            <?php if (empty($paymentHistory)): ?>
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-receipt text-4xl mb-2"></i>
                <p>No payment history available.</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($paymentHistory as $payment): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d M Y', strtotime($payment['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= sanitize($payment['course_title'] ?? 'N/A') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                K<?= number_format($payment['amount'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                <?= sanitize($payment['transaction_id'] ?? 'N/A') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'Completed' => 'green',
                                    'Pending' => 'yellow',
                                    'Failed' => 'red'
                                ];
                                $color = $statusColors[$payment['payment_status']] ?? 'gray';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-<?= $color ?>-100 text-<?= $color ?>-800">
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
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-3">
                <i class="fas fa-info-circle mr-2"></i>Payment Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                <div>
                    <h4 class="font-medium mb-2">Payment Methods</h4>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Cash payment at the college office</li>
                        <li>Bank deposit (registration fee only)</li>
                        <li>Mobile Money (MTN, Airtel, Zamtel)</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium mb-2">Important Notes</h4>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Registration fee must be paid via bank deposit</li>
                        <li>Course fees can be paid in installments</li>
                        <li>Certificates are issued only after full payment</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>
