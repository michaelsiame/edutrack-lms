<?php
/**
 * Registration Fees Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$total = $db->fetchColumn("SELECT COUNT(*) FROM registration_fees");
$totalPages = ceil($total / $per_page);

$fees = $db->fetchAll("SELECT rf.*, CONCAT(u.first_name, ' ', u.last_name) as student_name
    FROM registration_fees rf
    LEFT JOIN users u ON rf.user_id = u.id
    ORDER BY rf.created_at DESC LIMIT $per_page OFFSET $offset");
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Registration Fees</h1>
            <p class="text-gray-500 mt-1">Track K150 registration fee payments</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Collected</p>
            <p class="text-2xl font-bold text-gray-900">K<?= number_format($db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM registration_fees WHERE payment_status = 'completed'"), 2) ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Paid Registrations</p>
            <p class="text-2xl font-bold text-green-600"><?= $db->fetchColumn("SELECT COUNT(*) FROM registration_fees WHERE payment_status = 'completed'") ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600"><?= $db->fetchColumn("SELECT COUNT(*) FROM registration_fees WHERE payment_status = 'pending'") ?></p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bank Ref</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($fees as $f): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($f['student_name'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">K<?= number_format($f['amount'], 2) ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= 
                            $f['payment_status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                            ($f['payment_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                            <?= ucfirst($f['payment_status'] ?? 'Pending') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 capitalize"><?= str_replace('_', ' ', htmlspecialchars($f['payment_method'] ?? '-')) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600 font-mono"><?= htmlspecialchars($f['bank_reference'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= $f['created_at'] ? date('M j, Y', strtotime($f['created_at'])) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($fees)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-receipt text-4xl mb-3 text-gray-300"></i><p>No registration fees recorded</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=registration-fees&p=<?= $i ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
