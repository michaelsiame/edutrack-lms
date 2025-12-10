<?php
/**
 * Edutrack Computer Training College
 * Checkout Page - Process Course Payments
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';

// 1. Authentication Check
if (!isLoggedIn()) {
    setFlashMessage('Please login to continue with payment', 'error');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance();

// 2. Get Context (Enrollment ID is preferred, Course ID is fallback)
$enrollmentId = $_GET['enrollment_id'] ?? $_POST['enrollment_id'] ?? null;
$courseId = $_GET['course_id'] ?? $_POST['course_id'] ?? null;

$enrollment = null;
$course = null;
$paymentPlan = null;

// Scenario A: User comes from "Enroll" button (Has Enrollment ID)
if ($enrollmentId) {
    $enrollment = Enrollment::find($enrollmentId);
    if (!$enrollment || $enrollment->getUserId() != $userId) {
        setFlashMessage('Invalid enrollment record.', 'error');
        redirect('courses.php');
    }
    $course = Course::find($enrollment->getCourseId());
    
    // Fetch the Payment Plan associated with this enrollment
    $paymentPlan = $db->fetchOne("SELECT * FROM enrollment_payment_plans WHERE enrollment_id = ?", [$enrollmentId]);
} 
// Scenario B: User comes from direct link (Has Course ID)
elseif ($courseId) {
    // Check if enrollment already exists
    $enrollment = Enrollment::findByUserAndCourse($userId, $courseId);
    if ($enrollment) {
        // Redirect to self with enrollment_id to standardize logic
        redirect('checkout.php?enrollment_id=' . $enrollment->getId());
    } else {
        // If no enrollment exists, send back to enroll.php to create the structure
        redirect('enroll.php?course_id=' . $courseId);
    }
} else {
    setFlashMessage('No course selected.', 'error');
    redirect('courses.php');
}

// 3. Determine Amount to Pay
// If plan exists, show balance. If not (shouldn't happen), show full price.
$totalFee = $paymentPlan['total_fee'] ?? $course->getPrice();
$balance = $paymentPlan['balance'] ?? $totalFee;
$minDeposit = $totalFee * 0.30; // 30% rule calculation for display

// Handle Payment Submission
$paymentSubmitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please refresh.';
    } else {
        // Rate Limit
        if (!checkRateLimit('payment_submit_' . $userId, 5, 300)) {
            $errors[] = 'Please wait a few minutes before submitting another payment.';
        }

        $transactionRef = trim($_POST['transaction_reference'] ?? '');
        $paymentDate = trim($_POST['payment_date'] ?? '');
        $paymentMethod = trim($_POST['payment_method'] ?? '');
        
        // Validation
        if (empty($transactionRef)) $errors[] = 'Transaction reference is required';
        if (empty($paymentDate)) $errors[] = 'Payment date is required';
        if (empty($paymentMethod)) $errors[] = 'Please select a payment method';

        // File Upload
        $proofFile = null;
        if (empty($errors) && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../public/uploads/payments/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
                $errors[] = 'Invalid file type. Only JPG, PNG, PDF allowed.';
            } elseif ($_FILES['payment_proof']['size'] > 5 * 1024 * 1024) {
                $errors[] = 'File too large (Max 5MB).';
            } else {
                $proofFile = 'pay_' . $userId . '_' . $enrollmentId . '_' . time() . '.' . $ext;
                if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadDir . $proofFile)) {
                    $errors[] = 'Failed to upload proof file.';
                }
            }
        } elseif (empty($errors)) {
            $errors[] = 'Payment proof screenshot is required.';
        }

        if (empty($errors)) {
            try {
                // Get Payment Method ID
                $methodRow = $db->fetchOne("SELECT payment_method_id FROM payment_methods WHERE method_name LIKE ? OR description LIKE ?", ["%$paymentMethod%", "%$paymentMethod%"]);
                $methodId = $methodRow['payment_method_id'] ?? 5; // Default to Cash/Other if not found

                // Insert Payment Record
                // IMPORTANT: We link payment_plan_id here so the SQL Trigger can auto-update the balance
                $insertData = [
                    'student_id' => $enrollment->getStudentId(),
                    'course_id' => $course->getId(),
                    'enrollment_id' => $enrollmentId,
                    'payment_plan_id' => $paymentPlan['id'] ?? null, // Link to plan
                    'amount' => $balance, 
                    'currency' => 'ZMW',
                    'payment_status' => 'Pending', // Needs admin approval
                    'payment_method_id' => $methodId,
                    'transaction_id' => $transactionRef,
                    'payment_date' => $paymentDate . ' ' . date('H:i:s'),
                    'proof_of_payment' => $proofFile, // Ensure your DB has this column, or use 'notes'
                    'notes' => 'Uploaded Proof: ' . $proofFile
                ];

                $db->insert('payments', $insertData);
                $paymentSubmitted = true;

            } catch (Exception $e) {
                error_log("Checkout Error: " . $e->getMessage());
                $errors[] = 'System error processing payment. Please contact support.';
            }
        }
    }
}

$page_title = 'Checkout - ' . sanitize($course->getTitle());
require_once '../src/templates/header.php';
?>

<!-- Header -->
<section class="bg-primary-900 text-white py-10">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold"><i class="fas fa-lock mr-2"></i> Secure Checkout</h1>
        <p class="text-primary-200 mt-2">Complete payment for <?= sanitize($course->getTitle()) ?></p>
    </div>
</section>

<?php if ($paymentSubmitted): ?>
    <section class="py-12 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-3xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Submitted!</h2>
                <p class="text-gray-600 mb-6">Your transaction reference <strong><?= sanitize($transactionRef) ?></strong> has been recorded. Our finance team will verify it shortly.</p>
                <a href="my-courses.php" class="btn-primary px-6 py-2 rounded">Go to My Courses</a>
            </div>
        </div>
    </section>
<?php else: ?>

    <section class="py-12 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left: Payment Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-6">Upload Payment Proof</h2>
                    
                    <?php if (!empty($errors)): displayValidationErrors($errors); endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <?= csrfField() ?>
                        <input type="hidden" name="enrollment_id" value="<?= $enrollmentId ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Reference *</label>
                                <input type="text" name="transaction_reference" required placeholder="e.g. 23052456432"
                                       class="w-full border-gray-300 rounded focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date Paid *</label>
                                <input type="date" name="payment_date" required max="<?= date('Y-m-d') ?>"
                                       class="w-full border-gray-300 rounded focus:ring-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                            <select name="payment_method" required class="w-full border-gray-300 rounded focus:ring-primary-500">
                                <option value="">Select Method</option>
                                <option value="Airtel Money">Airtel Money</option>
                                <option value="MTN Mobile Money">MTN Mobile Money</option>
                                <option value="Zamtel Kwacha">Zamtel Kwacha</option>
                                <option value="Bank Transfer">Bank Transfer (Lenco)</option>
                                <option value="Cash Deposit">Cash Deposit at Branch</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Screenshot / Receipt *</label>
                            <input type="file" name="payment_proof" required accept=".jpg,.png,.pdf"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                            <p class="text-xs text-gray-500 mt-1">Max 5MB. Clear image of the transaction.</p>
                        </div>

                        <div class="pt-4">
                            <button type="submit" name="submit_payment" class="w-full btn-primary py-3 rounded font-bold">
                                Submit Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                    <h3 class="text-lg font-bold mb-4">Payment Summary</h3>
                    
                    <div class="border-b pb-4 mb-4">
                        <p class="text-sm text-gray-500">Course</p>
                        <p class="font-medium"><?= sanitize($course->getTitle()) ?></p>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Fee:</span>
                            <span class="font-bold">K<?= number_format($totalFee, 2) ?></span>
                        </div>
                        <div class="flex justify-between text-green-600">
                            <span>Already Paid:</span>
                            <span>- K<?= number_format($paymentPlan['total_paid'] ?? 0, 2) ?></span>
                        </div>
                        <div class="flex justify-between border-t pt-2 mt-2 text-lg">
                            <span class="font-bold">Balance Due:</span>
                            <span class="font-bold text-primary-600">K<?= number_format($balance, 2) ?></span>
                        </div>
                    </div>

                    <div class="mt-6 bg-blue-50 p-4 rounded text-sm text-blue-800">
                        <p class="font-bold mb-1"><i class="fas fa-info-circle"></i> Minimum Deposit</p>
                        <p>You must pay at least <strong>K<?= number_format($minDeposit, 2) ?></strong> (30%) to unlock the course content.</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

<?php endif; require_once '../src/templates/footer.php'; ?>