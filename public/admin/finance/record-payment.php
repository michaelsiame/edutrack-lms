<?php
/**
 * Record Cash Payment
 * Finance staff can record cash payments received at office
 */

require_once '../../../src/middleware/finance-only.php';
require_once '../../../src/classes/Payment.php';
require_once '../../../src/classes/PaymentPlan.php';
require_once '../../../src/classes/Enrollment.php';
require_once '../../../src/classes/Course.php';

$db = Database::getInstance();

// Get students with outstanding balances
$studentsWithBalance = $db->query("
    SELECT DISTINCT u.id, u.username, u.first_name, u.last_name, u.email
    FROM users u
    JOIN enrollment_payment_plans epp ON epp.user_id = u.id
    WHERE epp.balance > 0
    ORDER BY u.first_name, u.last_name
")->fetchAll();

// Get all students for new enrollments
$allStudents = $db->query("
    SELECT u.id, u.username, u.first_name, u.last_name, u.email
    FROM users u
    JOIN user_roles ur ON ur.user_id = u.id AND ur.role_id = 4
    ORDER BY u.first_name, u.last_name
")->fetchAll();

// Handle AJAX request for student payment plans
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_plans') {
    $userId = $_GET['user_id'] ?? null;
    if ($userId) {
        $plans = PaymentPlan::getByUser($userId);
        header('Content-Type: application/json');
        echo json_encode($plans);
    }
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();

    $paymentType = $_POST['payment_type'] ?? '';
    $userId = $_POST['user_id'] ?? null;
    $amount = floatval($_POST['amount'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $receiptNumber = trim($_POST['receipt_number'] ?? '');

    $errors = [];

    if (!$userId) {
        $errors[] = 'Please select a student';
    }

    if ($amount <= 0) {
        $errors[] = 'Amount must be greater than 0';
    }

    if ($paymentType === 'existing' && empty($_POST['plan_id'])) {
        $errors[] = 'Please select an enrollment to apply payment to';
    }

    if ($paymentType === 'new' && empty($_POST['course_id'])) {
        $errors[] = 'Please select a course for new enrollment';
    }

    if (empty($errors)) {
        // Get student_id from user_id
        $student = $db->query("SELECT id FROM students WHERE user_id = :user_id",
                             ['user_id' => $userId])->fetch();

        if (!$student) {
            $errors[] = 'Student record not found';
        } else {
            $studentId = $student['id'];

            if ($paymentType === 'existing') {
                // Apply to existing payment plan
                $planId = $_POST['plan_id'];
                $plan = PaymentPlan::find($planId);

                if (!$plan) {
                    $errors[] = 'Payment plan not found';
                } else {
                    // Create payment record
                    $sql = "INSERT INTO payments (
                        student_id, course_id, enrollment_id, payment_plan_id,
                        amount, currency, payment_method_id, payment_type,
                        payment_status, transaction_id, recorded_by, notes, payment_date
                    ) VALUES (
                        :student_id, :course_id, :enrollment_id, :plan_id,
                        :amount, 'ZMW', 5, 'partial_payment',
                        'Completed', :reference, :recorded_by, :notes, NOW()
                    )";

                    $params = [
                        'student_id' => $studentId,
                        'course_id' => $plan->getCourseId(),
                        'enrollment_id' => $plan->getEnrollmentId(),
                        'plan_id' => $planId,
                        'amount' => $amount,
                        'reference' => 'CASH-' . date('Ymd') . '-' . ($receiptNumber ?: uniqid()),
                        'recorded_by' => $_SESSION['user_id'],
                        'notes' => $notes
                    ];

                    if ($db->query($sql, $params)) {
                        // Update payment plan
                        $plan->recordPayment($amount, $db->lastInsertId());
                        flash('message', 'Cash payment of K' . number_format($amount, 2) . ' recorded successfully.', 'success');
                    } else {
                        flash('message', 'Failed to record payment', 'error');
                    }
                }
            } else {
                // New enrollment payment
                $courseId = $_POST['course_id'];
                $course = Course::find($courseId);

                if (!$course) {
                    $errors[] = 'Course not found';
                } else {
                    // Check if already enrolled
                    if (Enrollment::isEnrolled($userId, $courseId)) {
                        $errors[] = 'Student is already enrolled in this course';
                    } else {
                        // Create enrollment
                        $enrollmentData = [
                            'user_id' => $userId,
                            'course_id' => $courseId,
                            'enrollment_status' => 'Enrolled',
                            'payment_status' => $amount >= $course->getPrice() ? 'completed' : 'pending',
                            'amount_paid' => $amount
                        ];

                        $enrollmentId = Enrollment::create($enrollmentData);

                        if ($enrollmentId) {
                            // Create payment plan
                            $planData = [
                                'enrollment_id' => $enrollmentId,
                                'user_id' => $userId,
                                'course_id' => $courseId,
                                'total_fee' => $course->getPrice(),
                                'total_paid' => $amount,
                                'payment_status' => $amount >= $course->getPrice() ? 'completed' : 'partial'
                            ];

                            $planId = PaymentPlan::create($planData);

                            // Create payment record
                            $sql = "INSERT INTO payments (
                                student_id, course_id, enrollment_id, payment_plan_id,
                                amount, currency, payment_method_id, payment_type,
                                payment_status, transaction_id, recorded_by, notes, payment_date
                            ) VALUES (
                                :student_id, :course_id, :enrollment_id, :plan_id,
                                :amount, 'ZMW', 5, 'course_fee',
                                'Completed', :reference, :recorded_by, :notes, NOW()
                            )";

                            $params = [
                                'student_id' => $studentId,
                                'course_id' => $courseId,
                                'enrollment_id' => $enrollmentId,
                                'plan_id' => $planId,
                                'amount' => $amount,
                                'reference' => 'CASH-' . date('Ymd') . '-' . ($receiptNumber ?: uniqid()),
                                'recorded_by' => $_SESSION['user_id'],
                                'notes' => $notes
                            ];

                            $db->query($sql, $params);

                            flash('message', 'Student enrolled and payment of K' . number_format($amount, 2) . ' recorded.', 'success');
                        } else {
                            flash('message', 'Failed to create enrollment', 'error');
                        }
                    }
                }
            }

            if (empty($errors)) {
                redirect(url('admin/finance/record-payment.php'));
            }
        }
    }

    if (!empty($errors)) {
        flash('message', implode('<br>', $errors), 'error');
    }
}

$page_title = 'Record Cash Payment';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>
                Record Cash Payment
            </h1>
            <p class="text-gray-600 mt-1">Log cash payments received at the office</p>
        </div>
        <a href="<?= url('admin/finance/index.php') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Finance
        </a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" id="paymentForm">
                <?= csrfField() ?>

                <!-- Payment Type -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Payment Type</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                            <input type="radio" name="payment_type" value="existing" class="sr-only" checked>
                            <span class="flex flex-1">
                                <span class="flex flex-col">
                                    <span class="block text-sm font-medium text-gray-900">Existing Balance</span>
                                    <span class="mt-1 flex items-center text-sm text-gray-500">Pay towards outstanding course fee</span>
                                </span>
                            </span>
                            <span class="pointer-events-none absolute -inset-px rounded-lg border-2 border-primary-500 type-indicator"></span>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                            <input type="radio" name="payment_type" value="new" class="sr-only">
                            <span class="flex flex-1">
                                <span class="flex flex-col">
                                    <span class="block text-sm font-medium text-gray-900">New Enrollment</span>
                                    <span class="mt-1 flex items-center text-sm text-gray-500">Enroll student and record payment</span>
                                </span>
                            </span>
                            <span class="pointer-events-none absolute -inset-px rounded-lg border-2 border-transparent type-indicator"></span>
                        </label>
                    </div>
                </div>

                <!-- Student Selection (Existing Balance) -->
                <div id="existingSection">
                    <div class="mb-6">
                        <label for="student_existing" class="block text-sm font-medium text-gray-700 mb-1">
                            Select Student <span class="text-red-500">*</span>
                        </label>
                        <select id="student_existing" name="user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Select a student...</option>
                            <?php foreach ($studentsWithBalance as $student): ?>
                            <option value="<?= $student['id'] ?>">
                                <?= sanitize($student['first_name'] . ' ' . $student['last_name']) ?> (<?= sanitize($student['email']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Payment Plan Selection -->
                    <div class="mb-6" id="planSection" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Select Course/Enrollment <span class="text-red-500">*</span>
                        </label>
                        <div id="plansList" class="space-y-2">
                            <!-- Populated via JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Student Selection (New Enrollment) -->
                <div id="newSection" style="display: none;">
                    <div class="mb-6">
                        <label for="student_new" class="block text-sm font-medium text-gray-700 mb-1">
                            Select Student <span class="text-red-500">*</span>
                        </label>
                        <select id="student_new" name="user_id_new" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Select a student...</option>
                            <?php foreach ($allStudents as $student): ?>
                            <option value="<?= $student['id'] ?>">
                                <?= sanitize($student['first_name'] . ' ' . $student['last_name']) ?> (<?= sanitize($student['email']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="course_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Select Course <span class="text-red-500">*</span>
                        </label>
                        <select id="course_id" name="course_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Select a course...</option>
                            <?php
                            $courses = Course::all(['status' => 'published']);
                            foreach ($courses as $courseData):
                                $course = new Course($courseData['id']);
                            ?>
                            <option value="<?= $course->getId() ?>" data-price="<?= $course->getPrice() ?>">
                                <?= sanitize($course->getTitle()) ?> - K<?= number_format($course->getPrice(), 2) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Amount -->
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                        Amount (ZMW) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-2 text-gray-500">K</span>
                        <input type="number" id="amount" name="amount" step="0.01" min="0" required
                               class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="0.00">
                    </div>
                    <p id="balanceInfo" class="mt-1 text-sm text-gray-500" style="display: none;"></p>
                </div>

                <!-- Receipt Number -->
                <div class="mb-6">
                    <label for="receipt_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Receipt Number (Optional)
                    </label>
                    <input type="text" id="receipt_number" name="receipt_number"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                           placeholder="e.g., RCP-001">
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Notes (Optional)
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                              placeholder="Any additional notes about this payment..."></textarea>
                </div>

                <button type="submit" class="w-full btn btn-primary py-3">
                    <i class="fas fa-check mr-2"></i>Record Payment
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentTypeRadios = document.querySelectorAll('input[name="payment_type"]');
    const existingSection = document.getElementById('existingSection');
    const newSection = document.getElementById('newSection');
    const studentExisting = document.getElementById('student_existing');
    const studentNew = document.getElementById('student_new');
    const planSection = document.getElementById('planSection');
    const plansList = document.getElementById('plansList');
    const balanceInfo = document.getElementById('balanceInfo');

    // Toggle payment type sections
    paymentTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const indicators = document.querySelectorAll('.type-indicator');
            indicators.forEach(i => i.classList.remove('border-primary-500'));
            this.closest('label').querySelector('.type-indicator').classList.add('border-primary-500');

            if (this.value === 'existing') {
                existingSection.style.display = 'block';
                newSection.style.display = 'none';
                studentExisting.name = 'user_id';
                studentNew.name = 'user_id_new';
            } else {
                existingSection.style.display = 'none';
                newSection.style.display = 'block';
                studentExisting.name = 'user_id_existing';
                studentNew.name = 'user_id';
            }
        });
    });

    // Load payment plans when student selected
    studentExisting.addEventListener('change', function() {
        const userId = this.value;
        if (!userId) {
            planSection.style.display = 'none';
            return;
        }

        fetch('?ajax=get_plans&user_id=' + userId)
            .then(response => response.json())
            .then(plans => {
                plansList.innerHTML = '';
                if (plans.length === 0) {
                    plansList.innerHTML = '<p class="text-gray-500">No outstanding balances found</p>';
                } else {
                    plans.forEach((plan, index) => {
                        const html = `
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="plan_id" value="${plan.id}" class="mr-3" ${index === 0 ? 'checked' : ''}
                                       data-balance="${plan.balance}" data-total="${plan.total_fee}">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">${plan.course_title}</p>
                                    <p class="text-sm text-gray-500">
                                        Paid: K${parseFloat(plan.total_paid).toFixed(2)} / K${parseFloat(plan.total_fee).toFixed(2)}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-red-600">K${parseFloat(plan.balance).toFixed(2)}</p>
                                    <p class="text-xs text-gray-500">Balance</p>
                                </div>
                            </label>
                        `;
                        plansList.innerHTML += html;
                    });

                    // Update balance info when plan selected
                    document.querySelectorAll('input[name="plan_id"]').forEach(radio => {
                        radio.addEventListener('change', updateBalanceInfo);
                    });

                    updateBalanceInfo();
                }
                planSection.style.display = 'block';
            });
    });

    function updateBalanceInfo() {
        const selectedPlan = document.querySelector('input[name="plan_id"]:checked');
        if (selectedPlan) {
            const balance = parseFloat(selectedPlan.dataset.balance);
            balanceInfo.textContent = `Outstanding balance: K${balance.toFixed(2)}`;
            balanceInfo.style.display = 'block';
        } else {
            balanceInfo.style.display = 'none';
        }
    }

    // Update balance info when course selected (new enrollment)
    document.getElementById('course_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected && selected.dataset.price) {
            balanceInfo.textContent = `Course fee: K${parseFloat(selected.dataset.price).toFixed(2)}`;
            balanceInfo.style.display = 'block';
        }
    });
});
</script>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
