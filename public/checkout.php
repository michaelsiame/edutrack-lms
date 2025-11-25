<?php
/**
 * Checkout Page
 * Process course payment via Lenco Payment Gateway
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';

// Must be logged in to checkout
if (!isLoggedIn()) {
    setFlashMessage('Please login to continue with payment', 'error');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Get course ID
$courseId = $_POST['course_id'] ?? $_GET['course_id'] ?? null;

if (!$courseId) {
    setFlashMessage('Invalid course selection', 'error');
    redirect('courses.php');
}

// Get course using Course class (consistent with enroll.php)
$course = Course::find($courseId);

if (!$course || !$course->isPublished()) {
    setFlashMessage('Course not found', 'error');
    redirect('courses.php');
}

// Check if already enrolled using Enrollment class (removes duplicate SQL)
$userId = $_SESSION['user_id'];
if (Enrollment::isEnrolled($userId, $courseId)) {
    setFlashMessage('You are already enrolled in this course', 'info');
    redirect('my-courses.php');
}

// Get database instance for payment record creation
$db = Database::getInstance();

// Handle payment submission
$paymentSubmitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    // Validate CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission';
    } else {
        // Get payment details
        $transactionRef = trim($_POST['transaction_reference'] ?? '');
        $paymentDate = trim($_POST['payment_date'] ?? '');
        $paymentMethod = trim($_POST['payment_method'] ?? '');

        // Validate
        if (empty($transactionRef)) {
            $errors[] = 'Transaction reference is required';
        }
        if (empty($paymentDate)) {
            $errors[] = 'Payment date is required';
        }
        if (empty($paymentMethod)) {
            $errors[] = 'Please select the payment method you used';
        }

        // Handle file upload (payment proof)
        $proofFile = null;
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../public/uploads/payments/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

            if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                $errors[] = 'Invalid file type. Please upload JPG, PNG, or PDF';
            } elseif ($_FILES['payment_proof']['size'] > 5 * 1024 * 1024) {
                $errors[] = 'File size must be less than 5MB';
            } else {
                $proofFile = 'payment_' . $userId . '_' . $courseId . '_' . time() . '.' . $fileExtension;
                $uploadPath = $uploadDir . $proofFile;

                if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadPath)) {
                    $errors[] = 'Failed to upload payment proof';
                    $proofFile = null;
                }
            }
        } else {
            $errors[] = 'Payment proof is required';
        }

        // If no errors, create enrollment and payment record
        if (empty($errors)) {
            try {
                require_once '../src/classes/Enrollment.php';

                // Use Enrollment::create() - handles student record creation automatically
                // Uses correct schema enum values:
                // enrollment_status: 'Enrolled', 'In Progress', 'Completed', 'Dropped', 'Expired'
                // payment_status: 'pending', 'completed', 'failed', 'refunded'
                $enrollmentId = Enrollment::create([
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'enrollment_status' => 'Enrolled',
                    'payment_status' => 'pending',
                    'amount_paid' => 0
                ]);

                if (!$enrollmentId) {
                    throw new Exception('Failed to create enrollment');
                }

                // Get student_id for payment record (Enrollment::create handles this)
                $student = $db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$userId]);
                $studentId = $student['id'];

                // Create payment record (including proof of payment filename)
                $paymentId = $db->insert('payments', [
                    'student_id' => $studentId,
                    'course_id' => $courseId,
                    'enrollment_id' => $enrollmentId,
                    'amount' => $course->getPrice(),
                    'currency' => 'ZMW',
                    'payment_status' => 'Pending',
                    'payment_method' => $paymentMethod,
                    'transaction_id' => $transactionRef,
                    'payment_date' => $paymentDate . ' 00:00:00',
                    'proof_of_payment' => $proofFile
                ]);

                $paymentSubmitted = true;
                setFlashMessage('Payment submitted successfully! Your enrollment is pending verification.', 'success');

            } catch (Exception $e) {
                $errors[] = 'Failed to process payment. Please try again.';
                error_log("Checkout error: " . $e->getMessage());
            }
        }
    }
}

$page_title = 'Checkout - ' . sanitize($course->getTitle());
require_once '../src/templates/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-br from-primary-600 via-blue-700 to-purple-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-2">
                <i class="fas fa-lock mr-2"></i>
                Secure Checkout
            </h1>
            <p class="text-primary-100">Complete your payment to start learning</p>
        </div>
    </div>
</section>

<?php if ($paymentSubmitted): ?>
    <!-- Success Message -->
    <section class="py-12 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check-circle text-green-600 text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Payment Submitted Successfully!</h2>
                <p class="text-gray-700 mb-6">
                    Your payment proof has been submitted and is awaiting verification.
                    You will receive an email confirmation once your payment is verified and your enrollment is activated.
                </p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
                    <h3 class="font-semibold text-gray-900 mb-2">What happens next?</h3>
                    <ul class="text-sm text-gray-700 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                            <span>Our team will verify your payment within 24 hours</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                            <span>You'll receive an email confirmation once verified</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                            <span>Your course access will be activated immediately after verification</span>
                        </li>
                    </ul>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="my-courses.php" class="btn-primary px-6 py-3 rounded-lg inline-block">
                        <i class="fas fa-book mr-2"></i>
                        View My Courses
                    </a>
                    <a href="courses.php" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg inline-block hover:bg-gray-300 transition">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Browse More Courses
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>
    <!-- Checkout Form -->
    <section class="py-12 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Payment Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-md p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Payment via Lenco</h2>

                        <?php if (!empty($errors)): ?>
                            <?php displayValidationErrors($errors); ?>
                        <?php endif; ?>

                        <!-- Lenco Bank Details -->
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 mb-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-university text-blue-600 mr-2"></i>
                                Lenco Payment Details
                            </h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center py-2 border-b border-blue-200">
                                    <span class="text-gray-600 font-medium">Bank Name:</span>
                                    <span class="text-gray-900 font-semibold">Lenco</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-blue-200">
                                    <span class="text-gray-600 font-medium">Account Name:</span>
                                    <span class="text-gray-900 font-semibold">Edutrack computer training college</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-blue-200">
                                    <span class="text-gray-600 font-medium">Account Number:</span>
                                    <span class="text-gray-900 font-semibold text-lg">1234567890</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-gray-600 font-medium">Amount to Pay:</span>
                                    <span class="text-primary-600 font-bold text-xl">ZMW <?= number_format($course->getPrice(), 2) ?></span>
                                </div>
                            </div>
                            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded p-3">
                                <p class="text-xs text-yellow-800">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Important:</strong> Please use your name and course name as the transaction reference when making the transfer.
                                </p>
                            </div>
                        </div>

                        <!-- Payment Proof Form -->
                        <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                            <?= csrfField() ?>
                            <input type="hidden" name="course_id" value="<?= $courseId ?>">

                            <h3 class="text-lg font-bold text-gray-900 mb-4">Submit Payment Proof</h3>

                            <!-- Transaction Reference -->
                            <div>
                                <label for="transaction_reference" class="block text-sm font-medium text-gray-700 mb-2">
                                    Transaction Reference <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="transaction_reference"
                                       name="transaction_reference"
                                       value="<?= htmlspecialchars($_POST['transaction_reference'] ?? '') ?>"
                                       required
                                       placeholder="e.g., LENCO123456789"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <p class="mt-1 text-xs text-gray-500">Enter the transaction reference number from your bank receipt</p>
                            </div>

                            <!-- Payment Date -->
                            <div>
                                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date"
                                       id="payment_date"
                                       name="payment_date"
                                       value="<?= htmlspecialchars($_POST['payment_date'] ?? date('Y-m-d')) ?>"
                                       max="<?= date('Y-m-d') ?>"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Method Used <span class="text-red-500">*</span>
                                </label>
                                <select id="payment_method"
                                        name="payment_method"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">Select payment method</option>
                                    <option value="mtn" <?= ($_POST['payment_method'] ?? '') == 'mtn' ? 'selected' : '' ?>>Mobile Money - MTN</option>
                                    <option value="airtel" <?= ($_POST['payment_method'] ?? '') == 'airtel' ? 'selected' : '' ?>>Mobile Money - Airtel</option>
                                    <option value="zamtel" <?= ($_POST['payment_method'] ?? '') == 'zamtel' ? 'selected' : '' ?>>Mobile Money - Zamtel</option>
                                    <option value="bank_transfer" <?= ($_POST['payment_method'] ?? '') == 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                    <option value="lenco_app" <?= ($_POST['payment_method'] ?? '') == 'lenco_app' ? 'selected' : '' ?>>Lenco App</option>
                                </select>
                            </div>

                            <!-- Payment Proof Upload -->
                            <div>
                                <label for="payment_proof" class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Proof (Screenshot/Receipt) <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-primary-400 transition">
                                    <div class="space-y-1 text-center">
                                        <i class="fas fa-cloud-upload-alt text-gray-400 text-4xl mb-3"></i>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="payment_proof" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none">
                                                <span>Upload a file</span>
                                                <input id="payment_proof"
                                                       name="payment_proof"
                                                       type="file"
                                                       accept=".jpg,.jpeg,.png,.pdf"
                                                       required
                                                       class="sr-only"
                                                       onchange="displayFileName(this)">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                                        <p id="file-name" class="text-sm text-primary-600 font-medium mt-2"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms -->
                            <div>
                                <label class="flex items-start">
                                    <input type="checkbox"
                                           name="agree_terms"
                                           required
                                           class="mt-1 rounded border-gray-300 text-primary-600">
                                    <span class="ml-2 text-sm text-gray-700">
                                        I confirm that I have made the payment and the information provided is accurate.
                                    </span>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex space-x-4 pt-4">
                                <button type="submit"
                                        name="submit_payment"
                                        class="flex-1 bg-primary-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-primary-700 transition">
                                    <i class="fas fa-check mr-2"></i>
                                    Submit Payment Proof
                                </button>
                                <a href="course.php?id=<?= $courseId ?>"
                                   class="flex-1 bg-gray-200 text-gray-700 text-center py-3 px-6 rounded-lg font-semibold hover:bg-gray-300 transition">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h3>

                        <!-- Course Info -->
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-900 mb-2"><?= sanitize($course->getTitle()) ?></h4>
                            <p class="text-sm text-gray-600 mb-2"><?= sanitize($course->getCategoryName()) ?></p>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-clock mr-2"></i>
                                <span><?= $course->getDuration() ?> hours</span>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border-t border-gray-200 pt-4 space-y-3">
                            <div class="flex justify-between text-gray-700">
                                <span>Course Price:</span>
                                <span class="font-semibold">ZMW <?= number_format($course->getPrice(), 2) ?></span>
                            </div>
                            <div class="flex justify-between text-gray-700">
                                <span>Processing Fee:</span>
                                <span class="font-semibold">ZMW 0.00</span>
                            </div>
                            <div class="border-t border-gray-300 pt-3 flex justify-between text-lg font-bold text-gray-900">
                                <span>Total:</span>
                                <span class="text-primary-600">ZMW <?= number_format($course->getPrice(), 2) ?></span>
                            </div>
                        </div>

                        <!-- Security Badge -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex items-start text-sm text-gray-600">
                                <i class="fas fa-shield-alt text-green-600 text-xl mr-3"></i>
                                <div>
                                    <p class="font-semibold text-gray-900">Secure Payment</p>
                                    <p class="text-xs">Your information is protected and encrypted</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<script>
function displayFileName(input) {
    const fileNameDisplay = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        fileNameDisplay.textContent = input.files[0].name;
    }
}
</script>

<?php require_once '../src/templates/footer.php'; ?>
