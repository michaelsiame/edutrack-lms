<?php
/**
 * Student Accounts Overview
 * View all students' registration and payment status
 */

require_once '../../../src/middleware/finance-only.php';
require_once '../../../src/classes/RegistrationFee.php';
require_once '../../../src/classes/PaymentPlan.php';

$db = Database::getInstance();

// Get search/filter parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';

// Build query
$sql = "
    SELECT
        u.id as user_id,
        u.username,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        s.id as student_id,
        rf.payment_status as registration_status,
        rf.amount as registration_amount,
        COUNT(DISTINCT e.id) as total_enrollments,
        COALESCE(SUM(epp.total_fee), 0) as total_fees,
        COALESCE(SUM(epp.total_paid), 0) as total_paid,
        COALESCE(SUM(epp.balance), 0) as total_balance
    FROM users u
    JOIN user_roles ur ON ur.user_id = u.id AND ur.role_id = 4
    LEFT JOIN students s ON s.user_id = u.id
    LEFT JOIN registration_fees rf ON rf.user_id = u.id
    LEFT JOIN enrollments e ON e.user_id = u.id
    LEFT JOIN enrollment_payment_plans epp ON epp.user_id = u.id
    WHERE 1=1
";

$params = [];

if ($search) {
    $sql .= " AND (u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
    $params['search'] = "%$search%";
}

$sql .= " GROUP BY u.id ORDER BY u.first_name, u.last_name";

$students = $db->query($sql, $params)->fetchAll();

// Filter by status after grouping
if ($status === 'registration_pending') {
    $students = array_filter($students, fn($s) => $s['registration_status'] !== 'completed' || !$s['registration_status']);
} elseif ($status === 'with_balance') {
    $students = array_filter($students, fn($s) => $s['total_balance'] > 0);
} elseif ($status === 'cleared') {
    $students = array_filter($students, fn($s) => $s['registration_status'] === 'completed' && $s['total_balance'] <= 0);
}

$page_title = 'Student Accounts';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-users text-primary-600 mr-2"></i>
                Student Accounts
            </h1>
            <p class="text-gray-600 mt-1">View registration and payment status for all students</p>
        </div>
        <a href="<?= url('admin/finance/index.php') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Finance
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="<?= sanitize($search) ?>"
                       placeholder="Search by name or email..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Students</option>
                <option value="registration_pending" <?= $status === 'registration_pending' ? 'selected' : '' ?>>Registration Pending</option>
                <option value="with_balance" <?= $status === 'with_balance' ? 'selected' : '' ?>>With Outstanding Balance</option>
                <option value="cleared" <?= $status === 'cleared' ? 'selected' : '' ?>>Fully Cleared</option>
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search mr-2"></i>Search
            </button>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Fees</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            No students found matching your criteria
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($students as $student): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img src="<?= getGravatar($student['email']) ?>" class="h-10 w-10 rounded-full mr-3">
                                <div>
                                    <p class="font-medium text-gray-900"><?= sanitize($student['first_name'] . ' ' . $student['last_name']) ?></p>
                                    <p class="text-sm text-gray-500"><?= sanitize($student['email']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($student['registration_status'] === 'completed'): ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i>Paid
                            </span>
                            <?php elseif ($student['registration_status'] === 'pending'): ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                            <?php else: ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i>Not Paid
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                            <?= $student['total_enrollments'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                            K<?= number_format($student['total_fees'], 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-green-600 font-medium">
                            K<?= number_format($student['total_paid'], 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($student['total_balance'] > 0): ?>
                            <span class="font-bold text-red-600">K<?= number_format($student['total_balance'], 2) ?></span>
                            <?php else: ?>
                            <span class="text-green-600">K0.00</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $isCleared = $student['registration_status'] === 'completed' && $student['total_balance'] <= 0;
                            ?>
                            <?php if ($isCleared): ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Cleared
                            </span>
                            <?php elseif ($student['total_balance'] > 0): ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Balance Owing
                            </span>
                            <?php else: ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
