<?php
/**
 * Admin Student Details
 * View detailed information about a specific student
 */

require_once '../../../src/middleware/admin-only.php';

$studentId = $_GET['id'] ?? null;

if (!$studentId) {
    flash('message', 'Student not found', 'error');
    redirect(url('admin/students/index.php'));
}

// Get student details
$student = $db->fetchOne("
    SELECT u.*
    FROM users u
    INNER JOIN user_roles ur ON u.id = ur.user_id
    INNER JOIN roles r ON ur.role_id = r.id
    WHERE u.id = ? AND r.role_name = 'Student'
", [$studentId]);

if (!$student) {
    flash('message', 'Student not found', 'error');
    redirect(url('admin/students/index.php'));
}

// Get student enrollments
$enrollments = $db->fetchAll("
    SELECT e.*, c.title as course_title, c.slug, c.thumbnail,
           p.amount, p.status as payment_status, p.transaction_reference
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN payments p ON e.id = p.enrollment_id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
", [$studentId]);

// Get payment history
$payments = $db->fetchAll("
    SELECT p.*, c.title as course_title
    FROM payments p
    LEFT JOIN courses c ON p.course_id = c.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
", [$studentId]);

// Get certificates
$certificates = $db->fetchAll("
    SELECT cert.*, c.title as course_title
    FROM certificates cert
    JOIN courses c ON cert.course_id = c.id
    WHERE cert.user_id = ?
    ORDER BY cert.issued_at DESC
", [$studentId]);

// Calculate statistics
$stats = [
    'total_enrollments' => count($enrollments),
    'active_enrollments' => count(array_filter($enrollments, fn($e) => $e['status'] == 'active')),
    'completed_enrollments' => count(array_filter($enrollments, fn($e) => $e['status'] == 'completed')),
    'total_spent' => array_sum(array_map(fn($p) => $p['status'] == 'completed' ? $p['amount'] : 0, $payments)),
    'certificates_earned' => count($certificates),
];

$page_title = 'Student Details - ' . $student['first_name'] . ' ' . $student['last_name'];
require_once '../../../src/templates/admin-header.php';
?>

<div class="container-fluid px-4 py-6">

    <!-- Back Button -->
    <div class="mb-6">
        <a href="<?= url('admin/students/index.php') ?>" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Students
        </a>
    </div>

    <!-- Student Header -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex items-center">
                    <div class="h-20 w-20 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-600 font-bold text-2xl">
                            <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                        </span>
                    </div>
                    <div class="ml-6">
                        <h1 class="text-3xl font-bold text-gray-900">
                            <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                        </h1>
                        <p class="text-gray-600 mt-1"><?= htmlspecialchars($student['email']) ?></p>
                        <div class="mt-2 flex items-center space-x-4">
                            <?php if ($student['status'] == 'active'): ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            <?php elseif ($student['status'] == 'suspended'): ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Suspended</span>
                            <?php else: ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            <?php endif; ?>
                            <span class="text-sm text-gray-500">
                                Joined <?= date('M d, Y', strtotime($student['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <a href="mailto:<?= htmlspecialchars($student['email']) ?>"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-envelope mr-2"></i>Send Email
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <p class="text-sm text-gray-600">Total Enrollments</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_enrollments'] ?></p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <p class="text-sm text-gray-600">Active Courses</p>
                <p class="text-3xl font-bold text-blue-600 mt-2"><?= $stats['active_enrollments'] ?></p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <p class="text-sm text-gray-600">Completed</p>
                <p class="text-3xl font-bold text-green-600 mt-2"><?= $stats['completed_enrollments'] ?></p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <p class="text-sm text-gray-600">Certificates</p>
                <p class="text-3xl font-bold text-purple-600 mt-2"><?= $stats['certificates_earned'] ?></p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <p class="text-sm text-gray-600">Total Spent</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">ZMW <?= number_format($stats['total_spent']) ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Details & Enrollments -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Contact Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($student['email']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="text-gray-900 font-medium"><?= ucfirst($student['status']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrollments -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Course Enrollments</h2>
                </div>
                <div class="p-6">
                    <?php if (empty($enrollments)): ?>
                        <p class="text-center text-gray-500 py-8">No enrollments yet</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($enrollments as $enrollment): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900">
                                            <?= htmlspecialchars($enrollment['course_title']) ?>
                                        </h3>
                                        <div class="mt-2 flex items-center space-x-4 text-sm text-gray-600">
                                            <span>
                                                <i class="fas fa-calendar mr-1"></i>
                                                Enrolled: <?= date('M d, Y', strtotime($enrollment['enrolled_at'])) ?>
                                            </span>
                                            <?php if ($enrollment['status'] == 'completed' && $enrollment['completed_at']): ?>
                                            <span>
                                                <i class="fas fa-check-circle mr-1 text-green-600"></i>
                                                Completed: <?= date('M d, Y', strtotime($enrollment['completed_at'])) ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2">
                                            <div class="flex items-center">
                                                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                                    <div class="bg-blue-600 h-2 rounded-full"
                                                         style="width: <?= $enrollment['progress_percentage'] ?>%"></div>
                                                </div>
                                                <span class="text-sm text-gray-600"><?= round($enrollment['progress_percentage']) ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <?php if ($enrollment['status'] == 'active'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Active</span>
                                        <?php elseif ($enrollment['status'] == 'completed'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                        <?php elseif ($enrollment['status'] == 'dropped'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Dropped</span>
                                        <?php endif; ?>

                                        <?php if ($enrollment['payment_status']): ?>
                                        <div class="mt-2 text-xs">
                                            <?php if ($enrollment['payment_status'] == 'completed'): ?>
                                                <span class="text-green-600"><i class="fas fa-check"></i> Paid</span>
                                            <?php elseif ($enrollment['payment_status'] == 'pending'): ?>
                                                <span class="text-yellow-600"><i class="fas fa-clock"></i> Pending</span>
                                            <?php else: ?>
                                                <span class="text-red-600"><i class="fas fa-times"></i> Unpaid</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Right Column: Payments & Certificates -->
        <div class="space-y-6">

            <!-- Payment History -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Payment History</h2>
                </div>
                <div class="p-6">
                    <?php if (empty($payments)): ?>
                        <p class="text-center text-gray-500 py-4">No payments yet</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($payments as $payment): ?>
                            <div class="border-l-4 <?= $payment['status'] == 'completed' ? 'border-green-500' : 'border-yellow-500' ?> pl-4 py-2">
                                <p class="text-sm font-medium text-gray-900">
                                    ZMW <?= number_format($payment['amount'], 2) ?>
                                </p>
                                <p class="text-xs text-gray-600 mt-1">
                                    <?= htmlspecialchars($payment['course_title']) ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?= date('M d, Y', strtotime($payment['created_at'])) ?>
                                </p>
                                <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded <?= $payment['status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= ucfirst($payment['status']) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Certificates -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Certificates</h2>
                </div>
                <div class="p-6">
                    <?php if (empty($certificates)): ?>
                        <p class="text-center text-gray-500 py-4">No certificates yet</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($certificates as $cert): ?>
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-certificate text-yellow-500 text-2xl"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($cert['course_title']) ?>
                                        </p>
                                        <p class="text-xs text-gray-600 mt-1">
                                            <?= $cert['certificate_number'] ?>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Issued: <?= date('M d, Y', strtotime($cert['issued_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Account Info</h2>
                </div>
                <div class="p-6 space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">User ID</p>
                        <p class="text-gray-900 font-mono"><?= $student['id'] ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email Verified</p>
                        <p class="text-gray-900">
                            <?= $student['email_verified'] ? '<span class="text-green-600"><i class="fas fa-check-circle"></i> Yes</span>' : '<span class="text-red-600"><i class="fas fa-times-circle"></i> No</span>' ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Last Login</p>
                        <p class="text-gray-900">
                            <?= $student['last_login'] ? date('M d, Y g:i A', strtotime($student['last_login'])) : 'Never' ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Account Created</p>
                        <p class="text-gray-900"><?= date('M d, Y', strtotime($student['created_at'])) ?></p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
