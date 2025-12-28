<?php
/**
 * Edutrack Computer Training College
 * Checkout Page - Process Course Payments
 *
 * Supports multiple payment methods:
 * - Lenco Bank Transfer (automated)
 * - Manual Payment Proof Upload
 * - Mobile Money (MTN, Airtel, Zamtel)
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';
require_once '../src/classes/Lenco.php';

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
$totalFee = $paymentPlan['total_fee'] ?? $course->getPrice();
$balance = $paymentPlan['balance'] ?? $totalFee;
$totalPaid = $paymentPlan['total_paid'] ?? 0;
$minDeposit = $totalFee * 0.30; // 30% rule calculation for display

// Check if Lenco is configured
$lenco = new Lenco();
$lencoEnabled = $lenco->isConfigured();

// Handle Payment Submission
$paymentSubmitted = false;
$lencoPaymentInitiated = false;
$lencoReference = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired. Please refresh.';
    } else {
        // Rate Limit
        if (!checkRateLimit('payment_submit_' . $userId, 5, 300)) {
            $errors[] = 'Please wait a few minutes before submitting another payment.';
        }

        $paymentMethod = trim($_POST['payment_method'] ?? '');
        $paymentAmount = floatval($_POST['payment_amount'] ?? $balance);

        // Validate amount
        if ($paymentAmount < $minDeposit && $totalPaid == 0) {
            $errors[] = "Minimum first payment is K" . number_format($minDeposit, 2) . " (30% deposit)";
        } elseif ($paymentAmount <= 0) {
            $errors[] = 'Please enter a valid payment amount';
        } elseif ($paymentAmount > $balance) {
            $errors[] = 'Payment amount cannot exceed balance due';
        }

        if (empty($errors)) {

            // LENCO BANK TRANSFER
            if ($paymentMethod === 'lenco' && isset($_POST['pay_with_lenco'])) {

                if (!$lencoEnabled) {
                    $errors[] = 'Lenco payment is currently unavailable. Please try another method.';
                } else {
                    // Initialize Lenco payment
                    $result = $lenco->initializePayment([
                        'user_id' => $userId,
                        'enrollment_id' => $enrollmentId,
                        'course_id' => $course->getId(),
                        'amount' => $paymentAmount,
                        'currency' => 'ZMW'
                    ]);

                    if ($result['success']) {
                        // Redirect to Lenco checkout page
                        redirect('lenco-checkout.php?reference=' . $result['reference']);
                    } else {
                        $errors[] = $result['error'] ?? 'Failed to initialize Lenco payment. Please try another method.';
                    }
                }
            }
            // MANUAL PAYMENT PROOF UPLOAD
            elseif (isset($_POST['submit_payment'])) {

                $transactionRef = trim($_POST['transaction_reference'] ?? '');
                $paymentDate = trim($_POST['payment_date'] ?? '');

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
                        $insertData = [
                            'student_id' => $enrollment->getStudentId(),
                            'course_id' => $course->getId(),
                            'enrollment_id' => $enrollmentId,
                            'payment_plan_id' => $paymentPlan['id'] ?? null,
                            'amount' => $paymentAmount,
                            'currency' => 'ZMW',
                            'payment_status' => 'Pending', // Needs admin approval
                            'payment_method_id' => $methodId,
                            'transaction_id' => $transactionRef,
                            'payment_date' => $paymentDate . ' ' . date('H:i:s'),
                            'proof_of_payment' => $proofFile,
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
    }
}

$page_title = 'Checkout - ' . sanitize($course->getTitle());
require_once '../src/templates/header.php';
?>

<!-- Header -->
<section class="bg-gradient-to-r from-primary-900 to-primary-700 text-white py-10">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                <i class="fas fa-lock text-3xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold">Secure Checkout</h1>
                <p class="text-primary-200 mt-1">Complete payment for <?= sanitize($course->getTitle()) ?></p>
            </div>
        </div>
    </div>
</section>

<?php if ($paymentSubmitted): ?>
    <section class="py-12 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Submitted!</h2>
                <p class="text-gray-600 mb-6">
                    Your transaction reference <strong class="text-primary-600"><?= sanitize($transactionRef ?? '') ?></strong> has been recorded.
                    Our finance team will verify it within 24 hours.
                </p>
                <div class="bg-blue-50 p-4 rounded-lg text-left mb-6">
                    <h3 class="font-semibold text-blue-900 mb-2"><i class="fas fa-info-circle mr-2"></i>What's Next?</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>You'll receive an email once your payment is verified</li>
                        <li>After verification, your course access will be unlocked</li>
                        <li>Check your email spam folder if you don't receive confirmation</li>
                    </ul>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="my-courses.php" class="btn-primary px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-book mr-2"></i>Go to My Courses
                    </a>
                    <a href="my-payments.php" class="btn-secondary px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-receipt mr-2"></i>View Payment History
                    </a>
                </div>
            </div>
        </div>
    </section>

<?php else: ?>

    <section class="py-12 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4">

            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <h3 class="font-bold mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Please fix the following errors:</h3>
                    <ul class="list-disc list-inside text-sm">
                        <?php foreach ($errors as $error): ?>
                            <li><?= sanitize($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left: Payment Options -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Payment Amount Selection -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                            <i class="fas fa-money-bill-wave text-primary-600"></i>
                            Payment Amount
                        </h2>

                        <div class="space-y-4">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="amount_option" value="full" class="hidden peer" checked onchange="updatePaymentAmount()">
                                    <div class="p-4 border-2 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 transition-all">
                                        <div class="font-semibold text-gray-900">Pay Full Balance</div>
                                        <div class="text-2xl font-bold text-primary-600">K<?= number_format($balance, 2) ?></div>
                                    </div>
                                </label>

                                <?php if ($totalPaid == 0 && $minDeposit < $balance): ?>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="amount_option" value="deposit" class="hidden peer" onchange="updatePaymentAmount()">
                                    <div class="p-4 border-2 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 transition-all">
                                        <div class="font-semibold text-gray-900">Pay 30% Deposit</div>
                                        <div class="text-2xl font-bold text-green-600">K<?= number_format($minDeposit, 2) ?></div>
                                    </div>
                                </label>
                                <?php endif; ?>

                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="amount_option" value="custom" class="hidden peer" onchange="updatePaymentAmount()">
                                    <div class="p-4 border-2 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 transition-all">
                                        <div class="font-semibold text-gray-900">Custom Amount</div>
                                        <div class="text-sm text-gray-500">Min: K<?= number_format($totalPaid == 0 ? $minDeposit : 10, 2) ?></div>
                                    </div>
                                </label>
                            </div>

                            <div id="custom-amount-input" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Enter Amount (ZMW)</label>
                                <input type="number" id="custom_amount" step="0.01" min="<?= $totalPaid == 0 ? $minDeposit : 10 ?>" max="<?= $balance ?>"
                                       value="<?= $balance ?>" placeholder="Enter amount"
                                       class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-lg font-semibold"
                                       onchange="validateCustomAmount()">
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                            <i class="fas fa-credit-card text-primary-600"></i>
                            Select Payment Method
                        </h2>

                        <!-- Payment Method Tabs -->
                        <div class="border-b mb-6">
                            <nav class="flex gap-4" id="payment-tabs">
                                <?php if ($lencoEnabled): ?>
                                <button type="button" onclick="selectPaymentTab('lenco')"
                                        class="payment-tab py-3 px-4 border-b-2 border-primary-500 text-primary-600 font-semibold" data-tab="lenco">
                                    <i class="fas fa-university mr-2"></i>Bank Transfer
                                </button>
                                <?php endif; ?>
                                <button type="button" onclick="selectPaymentTab('manual')"
                                        class="payment-tab py-3 px-4 border-b-2 <?= $lencoEnabled ? 'border-transparent text-gray-500' : 'border-primary-500 text-primary-600 font-semibold' ?>" data-tab="manual">
                                    <i class="fas fa-upload mr-2"></i>Upload Proof
                                </button>
                            </nav>
                        </div>

                        <?php if ($lencoEnabled): ?>
                        <!-- Lenco Bank Transfer Option -->
                        <div id="lenco-payment" class="payment-content">
                            <form method="POST" id="lenco-form">
                                <?= csrfField() ?>
                                <input type="hidden" name="enrollment_id" value="<?= $enrollmentId ?>">
                                <input type="hidden" name="payment_method" value="lenco">
                                <input type="hidden" name="payment_amount" id="lenco_payment_amount" value="<?= $balance ?>">

                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-6">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-bolt text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 mb-1">Instant Bank Transfer</h3>
                                            <p class="text-sm text-gray-600 mb-3">
                                                Get a unique account number to make your payment. Your course will be unlocked automatically once payment is received.
                                            </p>
                                            <ul class="text-sm text-gray-600 space-y-1">
                                                <li><i class="fas fa-check text-green-500 mr-2"></i>No manual verification required</li>
                                                <li><i class="fas fa-check text-green-500 mr-2"></i>Instant course access after payment</li>
                                                <li><i class="fas fa-check text-green-500 mr-2"></i>Secure & encrypted transaction</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" name="pay_with_lenco" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-4 rounded-lg font-bold text-lg transition-all transform hover:scale-[1.02]">
                                    <i class="fas fa-university mr-2"></i>
                                    Pay K<span id="lenco-amount-display"><?= number_format($balance, 2) ?></span> via Bank Transfer
                                </button>

                                <p class="text-center text-sm text-gray-500 mt-3">
                                    <i class="fas fa-lock mr-1"></i> Secured by Lenco
                                </p>
                            </form>
                        </div>
                        <?php endif; ?>

                        <!-- Manual Payment Proof Upload -->
                        <div id="manual-payment" class="payment-content <?= $lencoEnabled ? 'hidden' : '' ?>">
                            <form method="POST" enctype="multipart/form-data" id="manual-form">
                                <?= csrfField() ?>
                                <input type="hidden" name="enrollment_id" value="<?= $enrollmentId ?>">
                                <input type="hidden" name="payment_amount" id="manual_payment_amount" value="<?= $balance ?>">

                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                                    <p class="text-sm text-yellow-800">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Make your payment using any method below, then upload proof. Verification takes up to 24 hours.
                                    </p>
                                </div>

                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                                        <select name="payment_method" required class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                            <option value="">Select Payment Method</option>
                                            <option value="Airtel Money">Airtel Money</option>
                                            <option value="MTN Mobile Money">MTN Mobile Money</option>
                                            <option value="Zamtel Kwacha">Zamtel Kwacha</option>
                                            <option value="Bank Transfer">Bank Transfer</option>
                                            <option value="Cash Deposit">Cash Deposit at Branch</option>
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Reference *</label>
                                            <input type="text" name="transaction_reference" required placeholder="e.g. 23052456432"
                                                   class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Paid *</label>
                                            <input type="date" name="payment_date" required max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>"
                                                   class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Proof (Screenshot/Receipt) *</label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors">
                                            <input type="file" name="payment_proof" id="payment_proof" required accept=".jpg,.jpeg,.png,.pdf"
                                                   class="hidden" onchange="previewFile(this)">
                                            <label for="payment_proof" class="cursor-pointer">
                                                <div id="file-preview" class="hidden mb-4"></div>
                                                <div id="file-upload-text">
                                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                                    <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG or PDF (Max 5MB)</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <button type="submit" name="submit_payment" class="w-full bg-primary-600 hover:bg-primary-700 text-white py-4 rounded-lg font-bold text-lg transition-all">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Submit Payment Proof
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>

                    <!-- Bank Details for Manual Transfer -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <i class="fas fa-building text-gray-600"></i>
                            Bank Account Details
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">If paying via bank transfer, use these details:</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php
                            $banks = config('payment.bank_transfer.banks', []);
                            foreach ($banks as $bank):
                            ?>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 mb-2"><?= sanitize($bank['name']) ?></h4>
                                <div class="text-sm space-y-1">
                                    <p><span class="text-gray-500">Account:</span> <span class="font-mono font-medium"><?= sanitize($bank['account_number']) ?></span></p>
                                    <p><span class="text-gray-500">Name:</span> <?= sanitize($bank['account_name']) ?></p>
                                    <p><span class="text-gray-500">Branch:</span> <?= sanitize($bank['branch']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

                <!-- Right: Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-lg p-6 sticky top-4">
                        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <i class="fas fa-receipt text-primary-600"></i>
                            Order Summary
                        </h3>

                        <!-- Course Info -->
                        <div class="flex items-center gap-4 pb-4 border-b">
                            <?php if ($course->getThumbnail()): ?>
                                <img src="<?= url('uploads/courses/' . $course->getThumbnail()) ?>" alt="" class="w-16 h-16 object-cover rounded-lg">
                            <?php else: ?>
                                <div class="w-16 h-16 bg-primary-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-graduation-cap text-primary-600 text-xl"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="font-semibold text-gray-900"><?= sanitize($course->getTitle()) ?></p>
                                <p class="text-sm text-gray-500"><?= $course->getDuration() ?></p>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="py-4 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Course Fee:</span>
                                <span class="font-medium">K<?= number_format($totalFee, 2) ?></span>
                            </div>
                            <?php if ($totalPaid > 0): ?>
                            <div class="flex justify-between text-sm text-green-600">
                                <span>Already Paid:</span>
                                <span class="font-medium">- K<?= number_format($totalPaid, 2) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="flex justify-between pt-3 border-t text-lg">
                                <span class="font-bold text-gray-900">Balance Due:</span>
                                <span class="font-bold text-primary-600">K<?= number_format($balance, 2) ?></span>
                            </div>
                        </div>

                        <!-- You're Paying -->
                        <div class="bg-primary-50 rounded-lg p-4 mb-4">
                            <p class="text-sm text-primary-800 mb-1">You're paying:</p>
                            <p class="text-2xl font-bold text-primary-600" id="paying-amount">K<?= number_format($balance, 2) ?></p>
                        </div>

                        <!-- Minimum Deposit Notice -->
                        <?php if ($totalPaid == 0): ?>
                        <div class="bg-blue-50 p-4 rounded-lg text-sm">
                            <p class="font-bold text-blue-900 mb-1">
                                <i class="fas fa-info-circle mr-1"></i> 30% Deposit Rule
                            </p>
                            <p class="text-blue-800">
                                Pay at least <strong>K<?= number_format($minDeposit, 2) ?></strong> to unlock course content.
                                You can pay the rest later.
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Security Badges -->
                        <div class="mt-6 pt-4 border-t text-center">
                            <div class="flex items-center justify-center gap-4 text-gray-400">
                                <i class="fas fa-lock text-xl"></i>
                                <i class="fas fa-shield-alt text-xl"></i>
                                <i class="fas fa-credit-card text-xl"></i>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Secure & encrypted payment</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

<script>
const balance = <?= $balance ?>;
const minDeposit = <?= $totalPaid == 0 ? $minDeposit : 10 ?>;

function updatePaymentAmount() {
    const option = document.querySelector('input[name="amount_option"]:checked').value;
    let amount = balance;

    const customInput = document.getElementById('custom-amount-input');
    const customAmount = document.getElementById('custom_amount');

    if (option === 'deposit') {
        amount = <?= $minDeposit ?>;
        customInput.classList.add('hidden');
    } else if (option === 'custom') {
        customInput.classList.remove('hidden');
        amount = parseFloat(customAmount.value) || balance;
    } else {
        customInput.classList.add('hidden');
        amount = balance;
    }

    updateAmountDisplays(amount);
}

function validateCustomAmount() {
    const customAmount = document.getElementById('custom_amount');
    let amount = parseFloat(customAmount.value) || 0;

    if (amount < minDeposit) {
        amount = minDeposit;
        customAmount.value = minDeposit;
    } else if (amount > balance) {
        amount = balance;
        customAmount.value = balance;
    }

    updateAmountDisplays(amount);
}

function updateAmountDisplays(amount) {
    const formatted = amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Update all amount displays
    document.getElementById('paying-amount').textContent = 'K' + formatted;

    // Update hidden form fields
    const lencoAmountField = document.getElementById('lenco_payment_amount');
    if (lencoAmountField) lencoAmountField.value = amount;

    const manualAmountField = document.getElementById('manual_payment_amount');
    if (manualAmountField) manualAmountField.value = amount;

    // Update Lenco button text
    const lencoDisplay = document.getElementById('lenco-amount-display');
    if (lencoDisplay) lencoDisplay.textContent = formatted;
}

function selectPaymentTab(tab) {
    // Update tab styles
    document.querySelectorAll('.payment-tab').forEach(t => {
        t.classList.remove('border-primary-500', 'text-primary-600', 'font-semibold');
        t.classList.add('border-transparent', 'text-gray-500');
    });

    const activeTab = document.querySelector(`.payment-tab[data-tab="${tab}"]`);
    if (activeTab) {
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeTab.classList.add('border-primary-500', 'text-primary-600', 'font-semibold');
    }

    // Show/hide content
    document.querySelectorAll('.payment-content').forEach(c => c.classList.add('hidden'));
    const content = document.getElementById(tab + '-payment');
    if (content) content.classList.remove('hidden');
}

function previewFile(input) {
    const preview = document.getElementById('file-preview');
    const uploadText = document.getElementById('file-upload-text');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            if (file.type.startsWith('image/')) {
                preview.innerHTML = `<img src="${e.target.result}" class="max-h-32 mx-auto rounded">`;
            } else {
                preview.innerHTML = `<div class="text-primary-600"><i class="fas fa-file-pdf text-4xl"></i><p class="text-sm mt-2">${file.name}</p></div>`;
            }
            preview.classList.remove('hidden');
            uploadText.classList.add('hidden');
        };

        reader.readAsDataURL(file);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updatePaymentAmount();
});
</script>

<?php endif; require_once '../src/templates/footer.php'; ?>
