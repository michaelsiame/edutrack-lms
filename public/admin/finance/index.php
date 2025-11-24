<?php
/**
 * Finance Dashboard
 * Overview of all financial activities
 */

require_once '../../../src/middleware/finance-only.php';
require_once '../../../src/classes/Payment.php';
require_once '../../../src/classes/RegistrationFee.php';
require_once '../../../src/classes/PaymentPlan.php';

// Get statistics
$registrationStats = RegistrationFee::getStats();
$paymentStats = Payment::getStats();
$planStats = PaymentPlan::getStats();

// Get pending items
$pendingRegistrations = RegistrationFee::getPending();
$pendingPayments = Payment::all(['status' => 'Pending', 'limit' => 10]);
$outstandingBalances = PaymentPlan::getWithBalance();

$page_title = 'Finance Dashboard';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-calculator text-primary-600 mr-2"></i>
            Finance Dashboard
        </h1>
        <p class="text-gray-600 mt-1">Manage payments, verify fees, and track student balances</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Registration Fees -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pending Registrations</p>
                    <p class="text-3xl font-bold text-yellow-600"><?= $registrationStats['pending'] ?? 0 ?></p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-user-plus text-yellow-600 text-xl"></i>
                </div>
            </div>
            <a href="<?= url('admin/finance/registration-fees.php') ?>" class="text-sm text-primary-600 hover:underline mt-2 inline-block">
                View all <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <!-- Pending Course Payments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pending Payments</p>
                    <p class="text-3xl font-bold text-orange-600"><?= Payment::getPendingCount() ?></p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
            <a href="<?= url('admin/finance/verify-payments.php') ?>" class="text-sm text-primary-600 hover:underline mt-2 inline-block">
                Verify now <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <!-- Outstanding Balances -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Outstanding Balance</p>
                    <p class="text-3xl font-bold text-red-600">K<?= number_format($planStats['total_outstanding'] ?? 0, 2) ?></p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                </div>
            </div>
            <a href="<?= url('admin/finance/balances.php') ?>" class="text-sm text-primary-600 hover:underline mt-2 inline-block">
                View details <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <!-- Total Collected -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Revenue</p>
                    <p class="text-3xl font-bold text-green-600">K<?= number_format(($paymentStats['total_revenue'] ?? 0) + ($registrationStats['total_collected'] ?? 0), 2) ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-coins text-green-600 text-xl"></i>
                </div>
            </div>
            <a href="<?= url('admin/reports/revenue.php') ?>" class="text-sm text-primary-600 hover:underline mt-2 inline-block">
                View reports <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="<?= url('admin/finance/record-payment.php') ?>"
           class="bg-primary-600 text-white rounded-lg shadow p-6 hover:bg-primary-700 transition">
            <div class="flex items-center">
                <div class="bg-white bg-opacity-20 p-3 rounded-full mr-4">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Record Cash Payment</h3>
                    <p class="text-primary-100 text-sm">Log a cash payment received at office</p>
                </div>
            </div>
        </a>

        <a href="<?= url('admin/finance/registration-fees.php') ?>"
           class="bg-yellow-500 text-white rounded-lg shadow p-6 hover:bg-yellow-600 transition">
            <div class="flex items-center">
                <div class="bg-white bg-opacity-20 p-3 rounded-full mr-4">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Verify Registrations</h3>
                    <p class="text-yellow-100 text-sm"><?= count($pendingRegistrations) ?> awaiting verification</p>
                </div>
            </div>
        </a>

        <a href="<?= url('admin/finance/student-accounts.php') ?>"
           class="bg-blue-600 text-white rounded-lg shadow p-6 hover:bg-blue-700 transition">
            <div class="flex items-center">
                <div class="bg-white bg-opacity-20 p-3 rounded-full mr-4">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Student Accounts</h3>
                    <p class="text-blue-100 text-sm">View all student balances</p>
                </div>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Pending Registration Fees -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-user-clock text-yellow-500 mr-2"></i>
                    Pending Registration Fees
                </h2>
                <a href="<?= url('admin/finance/registration-fees.php') ?>" class="text-sm text-primary-600 hover:underline">
                    View all
                </a>
            </div>
            <div class="p-6">
                <?php if (empty($pendingRegistrations)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                    <p>No pending registration fees</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach (array_slice($pendingRegistrations, 0, 5) as $reg): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900"><?= sanitize($reg['first_name'] . ' ' . $reg['last_name']) ?></p>
                            <p class="text-sm text-gray-500">Ref: <?= sanitize($reg['bank_reference'] ?? 'N/A') ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">K<?= number_format($reg['amount'], 2) ?></p>
                            <p class="text-xs text-gray-500"><?= timeAgo($reg['created_at']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Outstanding Balances -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    Students with Outstanding Balance
                </h2>
                <a href="<?= url('admin/finance/balances.php') ?>" class="text-sm text-primary-600 hover:underline">
                    View all
                </a>
            </div>
            <div class="p-6">
                <?php if (empty($outstandingBalances)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                    <p>All students are fully paid</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach (array_slice($outstandingBalances, 0, 5) as $balance): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900"><?= sanitize($balance['first_name'] . ' ' . $balance['last_name']) ?></p>
                            <p class="text-sm text-gray-500"><?= sanitize($balance['course_title']) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-red-600">K<?= number_format($balance['balance'], 2) ?></p>
                            <p class="text-xs text-gray-500">of K<?= number_format($balance['total_fee'], 2) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="mt-8 bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-history text-gray-500 mr-2"></i>
                Recent Payments
            </h2>
            <a href="<?= url('admin/payments/index.php') ?>" class="text-sm text-primary-600 hover:underline">
                View all
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $recentPayments = Payment::all(['limit' => 10]);
                    foreach ($recentPayments as $payment):
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= sanitize($payment['first_name'] . ' ' . $payment['last_name']) ?></div>
                            <div class="text-sm text-gray-500"><?= sanitize($payment['email']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= sanitize($payment['course_title'] ?? 'N/A') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            K<?= number_format($payment['amount'], 2) ?>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('d M Y', strtotime($payment['created_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
