<?php
/**
 * Registration Fee Payment Page
 * K150 one-time registration fee (must be paid to bank)
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/RegistrationFee.php';

// Must be logged in
if (!isLoggedIn()) {
    setFlashMessage('Please login to continue', 'error');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$userId = $_SESSION['user_id'];
$user = User::current();

// Check if already paid
if (RegistrationFee::hasPaid($userId)) {
    setFlashMessage('Your registration fee has already been paid.', 'success');

    // Redirect to intended course if set
    if (isset($_SESSION['intended_course_id'])) {
        $courseId = $_SESSION['intended_course_id'];
        unset($_SESSION['intended_course_id']);
        redirect('enroll.php?course_id=' . $courseId);
    }
    redirect('dashboard.php');
}

// Check for existing pending submission
$existingFee = RegistrationFee::findByUser($userId);
$hasPending = $existingFee && $existingFee->isPending();

// Get registration fee details
$feeAmount = RegistrationFee::getFeeAmount();
$bankDetails = RegistrationFee::getBankDetails();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();

    $bankReference = trim($_POST['bank_reference'] ?? '');
    $bankName = trim($_POST['bank_name'] ?? '');
    $depositDate = $_POST['deposit_date'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    // Validation
    $errors = [];

    if (empty($bankReference)) {
        $errors[] = 'Bank reference/deposit slip number is required';
    }

    if (empty($bankName)) {
        $errors[] = 'Bank name is required';
    }

    if (empty($depositDate)) {
        $errors[] = 'Deposit date is required';
    }

    if (empty($errors)) {
        $data = [
            'user_id' => $userId,
            'amount' => $feeAmount,
            'bank_reference' => $bankReference,
            'bank_name' => $bankName,
            'deposit_date' => $depositDate,
            'notes' => $notes
        ];

        if ($hasPending) {
            // Update existing record
            $existingFee->update($data);
            $feeId = $existingFee->getId();
        } else {
            // Create new record
            $feeId = RegistrationFee::create($data);
        }

        if ($feeId) {
            setFlashMessage('Registration fee submitted successfully! Your payment will be verified within 24 hours.', 'success');
            redirect('registration-fee.php');
        } else {
            setFlashMessage('Failed to submit registration fee. Please try again.', 'error');
        }
    } else {
        setFlashMessage(implode('<br>', $errors), 'error');
    }
}

$page_title = 'Registration Fee';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Registration Fee</h1>
            <p class="mt-2 text-gray-600">One-time registration fee required before course enrollment</p>
        </div>

        <?php if ($hasPending): ?>
        <!-- Pending Status -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-yellow-800">Payment Pending Verification</h3>
                    <p class="mt-1 text-yellow-700">
                        Your registration fee payment is being verified. This usually takes 24 hours.
                    </p>
                    <div class="mt-4 text-sm text-yellow-700">
                        <p><strong>Reference:</strong> <?= sanitize($existingFee->getBankReference()) ?></p>
                        <p><strong>Amount:</strong> K<?= number_format($existingFee->getAmount(), 2) ?></p>
                        <p><strong>Submitted:</strong> <?= date('d M Y, H:i', strtotime($existingFee->getCreatedAt())) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Bank Details Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-university text-primary-600 mr-2"></i>
                    Bank Payment Details
                </h2>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-800 mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Please make payment to the following bank account:
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Account Name</span>
                        <span class="font-semibold text-gray-900"><?= sanitize($bankDetails['account_name'] ?? 'EDUTRACK Computer Training') ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Bank Name</span>
                        <span class="font-semibold text-gray-900"><?= sanitize($bankDetails['name'] ?? 'Contact Admin') ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Account Number</span>
                        <span class="font-semibold text-gray-900"><?= sanitize($bankDetails['account_number'] ?? 'Contact Admin') ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600">Branch</span>
                        <span class="font-semibold text-gray-900"><?= sanitize($bankDetails['branch'] ?? 'Main Branch') ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600">Amount</span>
                        <span class="text-2xl font-bold text-primary-600">K<?= number_format($feeAmount, 2) ?></span>
                    </div>
                </div>

                <div class="mt-6 bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Payment Reference</h4>
                    <p class="text-sm text-gray-600">
                        When making your deposit, please use your full name as the reference:
                    </p>
                    <p class="mt-2 font-mono bg-white px-3 py-2 rounded border border-gray-200">
                        <?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?>
                    </p>
                </div>
            </div>

            <!-- Payment Confirmation Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-receipt text-primary-600 mr-2"></i>
                    <?= $hasPending ? 'Update Payment Details' : 'Confirm Payment' ?>
                </h2>

                <p class="text-gray-600 mb-6">
                    After making your bank deposit, please fill in the details below so we can verify your payment.
                </p>

                <form method="POST" class="space-y-6">
                    <?= csrfField() ?>

                    <div>
                        <label for="bank_reference" class="block text-sm font-medium text-gray-700 mb-1">
                            Deposit Slip Number / Reference <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="bank_reference" name="bank_reference"
                               value="<?= sanitize($existingFee ? $existingFee->getBankReference() : '') ?>"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="e.g., DEP123456789">
                        <p class="mt-1 text-xs text-gray-500">Found on your deposit slip or bank receipt</p>
                    </div>

                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Bank Name <span class="text-red-500">*</span>
                        </label>
                        <select id="bank_name" name="bank_name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select bank...</option>
                            <option value="Zanaco" <?= ($existingFee && $existingFee->getBankName() === 'Zanaco') ? 'selected' : '' ?>>Zanaco</option>
                            <option value="Stanbic Bank" <?= ($existingFee && $existingFee->getBankName() === 'Stanbic Bank') ? 'selected' : '' ?>>Stanbic Bank</option>
                            <option value="Standard Chartered" <?= ($existingFee && $existingFee->getBankName() === 'Standard Chartered') ? 'selected' : '' ?>>Standard Chartered</option>
                            <option value="FNB" <?= ($existingFee && $existingFee->getBankName() === 'FNB') ? 'selected' : '' ?>>FNB Zambia</option>
                            <option value="Barclays/Absa" <?= ($existingFee && $existingFee->getBankName() === 'Barclays/Absa') ? 'selected' : '' ?>>Barclays/Absa</option>
                            <option value="Access Bank" <?= ($existingFee && $existingFee->getBankName() === 'Access Bank') ? 'selected' : '' ?>>Access Bank</option>
                            <option value="Indo Zambia Bank" <?= ($existingFee && $existingFee->getBankName() === 'Indo Zambia Bank') ? 'selected' : '' ?>>Indo Zambia Bank</option>
                            <option value="Atlas Mara" <?= ($existingFee && $existingFee->getBankName() === 'Atlas Mara') ? 'selected' : '' ?>>Atlas Mara</option>
                            <option value="Other" <?= ($existingFee && $existingFee->getBankName() === 'Other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="deposit_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Date of Deposit <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="deposit_date" name="deposit_date"
                               value="<?= sanitize($existingFee ? $existingFee->getDepositDate() : date('Y-m-d')) ?>"
                               required
                               max="<?= date('Y-m-d') ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Additional Notes (Optional)
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                  placeholder="Any additional information..."><?= sanitize($existingFee ? $existingFee->getNotes() : '') ?></textarea>
                    </div>

                    <button type="submit" class="w-full btn btn-primary py-3">
                        <i class="fas fa-paper-plane mr-2"></i>
                        <?= $hasPending ? 'Update Payment Details' : 'Submit for Verification' ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Help Section -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-question-circle text-primary-600 mr-2"></i>
                Frequently Asked Questions
            </h3>
            <div class="space-y-4">
                <div>
                    <h4 class="font-medium text-gray-900">Is this a one-time fee?</h4>
                    <p class="text-gray-600 text-sm">Yes, the K150 registration fee is a one-time payment that allows you to enroll in any course.</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">How long does verification take?</h4>
                    <p class="text-gray-600 text-sm">Verification usually takes 24 hours during business days.</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Why can't I pay with mobile money?</h4>
                    <p class="text-gray-600 text-sm">Registration fees must be paid directly to our bank account for record-keeping purposes. Course fees can be paid via mobile money.</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Need help?</h4>
                    <p class="text-gray-600 text-sm">Contact us at <a href="mailto:info@edutrack.edu" class="text-primary-600 hover:underline">info@edutrack.edu</a> or call +260 XXX XXX XXX</p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>
