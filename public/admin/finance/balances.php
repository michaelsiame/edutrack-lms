<?php
/**
 * Student Balances
 * View all students with outstanding balances
 */

require_once '../../../src/middleware/finance-only.php';
require_once '../../../src/classes/PaymentPlan.php';

// Get all plans with outstanding balances
$outstandingPlans = PaymentPlan::getWithBalance();

// Get statistics
$stats = PaymentPlan::getStats();

$page_title = 'Outstanding Balances';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                Outstanding Balances
            </h1>
            <p class="text-gray-600 mt-1">Students with unpaid course fee balances</p>
        </div>
        <a href="<?= url('admin/finance/index.php') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Finance
        </a>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Total Outstanding</p>
            <p class="text-2xl font-bold text-red-600">K<?= number_format($stats['total_outstanding'] ?? 0, 2) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Students with Balance</p>
            <p class="text-2xl font-bold text-gray-900"><?= count($outstandingPlans) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Total Collected</p>
            <p class="text-2xl font-bold text-green-600">K<?= number_format($stats['total_collected'] ?? 0, 2) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Fully Paid Enrollments</p>
            <p class="text-2xl font-bold text-primary-600"><?= $stats['fully_paid'] ?? 0 ?></p>
        </div>
    </div>

    <?php if (empty($outstandingPlans)): ?>
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <div class="inline-block bg-green-100 rounded-full p-6 mb-4">
            <i class="fas fa-check-circle text-green-600 text-5xl"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">All Clear!</h2>
        <p class="text-gray-600">All students are fully paid. No outstanding balances.</p>
    </div>
    <?php else: ?>

    <!-- Balances Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Fee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($outstandingPlans as $planData): ?>
                    <?php $plan = new PaymentPlan($planData['id']); ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img src="<?= getGravatar($plan->getUserEmail()) ?>" class="h-10 w-10 rounded-full mr-3">
                                <div>
                                    <p class="font-medium text-gray-900"><?= sanitize($plan->getUserName()) ?></p>
                                    <p class="text-sm text-gray-500"><?= sanitize($plan->getUserEmail()) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="text-gray-900"><?= sanitize($plan->getCourseTitle()) ?></p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                            <?= $plan->getFormattedTotalFee() ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-green-600 font-medium">
                            <?= $plan->getFormattedTotalPaid() ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-bold text-red-600"><?= $plan->getFormattedBalance() ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="w-32">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-500"><?= $plan->getProgressPercentage() ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full" style="width: <?= $plan->getProgressPercentage() ?>%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="<?= url('admin/finance/record-payment.php?user_id=' . $plan->getUserId()) ?>"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-plus mr-1"></i>Record Payment
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
