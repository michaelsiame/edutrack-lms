<?php
/**
 * Registration Fee Payment Page - Modern Design
 * K150 one-time registration fee
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

    $paymentMethod = $_POST['payment_method'] ?? 'mobile';
    $bankReference = trim($_POST['bank_reference'] ?? '');
    $bankName = trim($_POST['bank_name'] ?? '');
    $depositDate = $_POST['deposit_date'] ?? '';
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    $errors = [];

    if ($paymentMethod === 'bank') {
        if (empty($bankReference)) {
            $errors[] = 'Bank reference/deposit slip number is required';
        }
        if (empty($bankName)) {
            $errors[] = 'Bank name is required';
        }
        if (empty($depositDate)) {
            $errors[] = 'Deposit date is required';
        }
    } else {
        // Mobile money validation
        if (empty($phoneNumber)) {
            $errors[] = 'Phone number is required for mobile money payment';
        }
    }

    if (empty($errors)) {
        $data = [
            'user_id' => $userId,
            'amount' => $feeAmount,
            'payment_method' => $paymentMethod === 'bank' ? 'bank_deposit' : 'mobile_money',
            'bank_reference' => $bankReference,
            'bank_name' => $bankName,
            'deposit_date' => $depositDate,
            'phone_number' => $phoneNumber,
            'notes' => $notes
        ];

        if ($hasPending) {
            $existingFee->update($data);
            $feeId = $existingFee->getId();
        } else {
            $feeId = RegistrationFee::create($data);
        }

        if ($feeId) {
            setFlashMessage('Payment details submitted successfully! Your payment will be verified within 24 hours.', 'success');
            redirect('registration-fee.php');
        } else {
            setFlashMessage('Failed to submit payment details. Please try again.', 'error');
        }
    } else {
        setFlashMessage(implode('<br>', $errors), 'error');
    }
}

$page_title = 'Registration Fee Payment';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-check text-blue-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Complete Your Registration</h1>
            <p class="mt-2 text-gray-600">Pay the one-time registration fee to access all courses</p>
        </div>

        <?php if ($hasPending): ?>
        <!-- Pending Status Alert -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6">
            <div class="flex items-start">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-semibold text-amber-900">Payment Verification in Progress</h3>
                    <p class="mt-1 text-amber-700">
                        Your registration fee payment has been submitted and is being verified by our team. 
                        This usually takes 24 hours during business days.
                    </p>
                    <div class="mt-4 bg-white rounded-lg p-4 border border-amber-200">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Reference:</span>
                                <p class="font-medium text-gray-900"><?= sanitize($existingFee->getBankReference() ?: $existingFee->getPhoneNumber()) ?></p>
                            </div>
                            <div>
                                <span class="text-gray-500">Amount:</span>
                                <p class="font-medium text-gray-900"><?= $existingFee->getFormattedAmount() ?></p>
                            </div>
                            <div>
                                <span class="text-gray-500">Method:</span>
                                <p class="font-medium text-gray-900"><?= ucwords(str_replace('_', ' ', $existingFee->getPaymentMethod())) ?></p>
                            </div>
                            <div>
                                <span class="text-gray-500">Submitted:</span>
                                <p class="font-medium text-gray-900"><?= date('d M Y', strtotime($existingFee->getCreatedAt())) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fee Summary Card -->
        <div class="bg-white rounded-xl border overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-900">Registration Fee Summary</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">One-time Registration Fee</p>
                        <p class="text-xs text-gray-400 mt-1">Required before enrolling in any course</p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-blue-600">K<?= number_format($feeAmount, 2) ?></p>
                        <p class="text-xs text-gray-400">ZMW</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        <span>This fee is payable once and allows you to enroll in unlimited courses</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="bg-white rounded-xl border overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Select Payment Method</h2>
            </div>
            
            <div class="p-6">
                <!-- Payment Method Tabs -->
                <div class="flex space-x-4 mb-6 border-b border-gray-200" id="payment-tabs">
                    <button type="button" data-tab="mobile" 
                            class="tab-btn pb-3 px-2 text-sm font-medium border-b-2 border-blue-600 text-blue-600 transition">
                        <i class="fas fa-mobile-alt mr-2"></i>Mobile Money
                    </button>
                    <button type="button" data-tab="bank" 
                            class="tab-btn pb-3 px-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                        <i class="fas fa-university mr-2"></i>Bank Transfer
                    </button>
                </div>

                <form method="POST" class="space-y-6" id="payment-form">
                    <?= csrfField() ?>
                    <input type="hidden" name="payment_method" id="payment_method" value="mobile">

                    <!-- Mobile Money Section (Default) -->
                    <div id="section-mobile" class="payment-section space-y-6">
                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                            <h3 class="font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-mobile-screen text-gray-400 mr-2"></i>
                                Mobile Money Payment
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-center p-3 bg-white rounded-lg border">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-money-bill-wave text-yellow-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">MTN Mobile Money</p>
                                        <p class="text-sm text-gray-500">Send to: <span class="font-mono"><?= SITE_PHONE ?? '+260 XXX XXX XXX' ?></span></p>
                                    </div>
                                    <button type="button" class="copy-btn text-blue-600 hover:text-blue-800 text-sm" data-copy="<?= SITE_PHONE ?? '+260 XXX XXX XXX' ?>">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <div class="flex items-center p-3 bg-white rounded-lg border">
                                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-money-bill-wave text-red-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">Airtel Money</p>
                                        <p class="text-sm text-gray-500">Send to: <span class="font-mono"><?= SITE_PHONE ?? '+260 XXX XXX XXX' ?></span></p>
                                    </div>
                                    <button type="button" class="copy-btn text-blue-600 hover:text-blue-800 text-sm" data-copy="<?= SITE_PHONE ?? '+260 XXX XXX XXX' ?>">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-lightbulb mr-1"></i>
                                    <strong>Reference:</strong> Include your full name "<?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?>" in the message
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">
                                Mobile Money Number <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="phone_number" name="phone_number"
                                   value="<?= sanitize($existingFee ? $existingFee->getPhoneNumber() : '') ?>"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                   placeholder="e.g., +260 97X XXX XXX">
                            <p class="mt-1 text-xs text-gray-500">The phone number used to make the payment</p>
                        </div>
                    </div>

                    <!-- Bank Transfer Section (Hidden by default) -->
                    <div id="section-bank" class="payment-section space-y-6 hidden">
                        <!-- Bank Details -->
                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                            <h3 class="font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-building-columns text-gray-400 mr-2"></i>
                                Bank Account Details
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-sm text-gray-600">Account Name</span>
                                    <span class="font-medium text-gray-900"><?= sanitize($bankDetails['account_name'] ?? 'EDUTRACK Computer Training') ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-sm text-gray-600">Bank</span>
                                    <span class="font-medium text-gray-900"><?= sanitize($bankDetails['name'] ?? 'Contact Admin') ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-sm text-gray-600">Account Number</span>
                                    <div class="flex items-center">
                                        <span class="font-mono font-medium text-gray-900 mr-2" id="account-number"><?= sanitize($bankDetails['account_number'] ?? 'Contact Admin') ?></span>
                                        <?php if (!empty($bankDetails['account_number'])): ?>
                                        <button type="button" class="copy-btn text-blue-600 hover:text-blue-800 text-xs" data-copy="<?= $bankDetails['account_number'] ?>">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-600">Branch</span>
                                    <span class="font-medium text-gray-900"><?= sanitize($bankDetails['branch'] ?? 'Main Branch') ?></span>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-lightbulb mr-1"></i>
                                    <strong>Reference:</strong> Use your full name "<?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?>" when making the deposit
                                </p>
                            </div>
                        </div>

                        <!-- Bank Payment Form -->
                        <div class="space-y-4">
                            <div>
                                <label for="bank_reference" class="block text-sm font-medium text-gray-700 mb-1">
                                    Deposit Slip / Reference Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="bank_reference" name="bank_reference"
                                       value="<?= sanitize($existingFee ? $existingFee->getBankReference() : '') ?>"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       placeholder="e.g., DEP123456789">
                                <p class="mt-1 text-xs text-gray-500">Found on your deposit slip or bank receipt</p>
                            </div>

                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Bank Used <span class="text-red-500">*</span>
                                </label>
                                <select id="bank_name" name="bank_name"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="">Select bank...</option>
                                    <option value="Zanaco" <?= ($existingFee && $existingFee->getBankName() === 'Zanaco') ? 'selected' : '' ?>>Zanaco</option>
                                    <option value="Stanbic Bank" <?= ($existingFee && $existingFee->getBankName() === 'Stanbic Bank') ? 'selected' : '' ?>>Stanbic Bank</option>
                                    <option value="Standard Chartered" <?= ($existingFee && $existingFee->getBankName() === 'Standard Chartered') ? 'selected' : '' ?>>Standard Chartered</option>
                                    <option value="FNB" <?= ($existingFee && $existingFee->getBankName() === 'FNB') ? 'selected' : '' ?>>FNB Zambia</option>
                                    <option value="Absa" <?= ($existingFee && $existingFee->getBankName() === 'Absa') ? 'selected' : '' ?>>Absa Bank</option>
                                    <option value="Access Bank" <?= ($existingFee && $existingFee->getBankName() === 'Access Bank') ? 'selected' : '' ?>>Access Bank</option>
                                    <option value="Indo Zambia Bank" <?= ($existingFee && $existingFee->getBankName() === 'Indo Zambia Bank') ? 'selected' : '' ?>>Indo Zambia Bank</option>
                                    <option value="Atlas Mara" <?= ($existingFee && $existingFee->getBankName() === 'Atlas Mara') ? 'selected' : '' ?>>Atlas Mara</option>
                                    <option value="Bank of Zambia" <?= ($existingFee && $existingFee->getBankName() === 'Bank of Zambia') ? 'selected' : '' ?>>Bank of Zambia</option>
                                    <option value="Other" <?= ($existingFee && $existingFee->getBankName() === 'Other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="deposit_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Date of Deposit <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="deposit_date" name="deposit_date"
                                       value="<?= sanitize($existingFee ? $existingFee->getDepositDate() : date('Y-m-d')) ?>"
                                       max="<?= date('Y-m-d') ?>"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>
                        </div>
                    </div>

                    <!-- Common Fields -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Additional Notes (Optional)
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                  placeholder="Any additional information to help us verify your payment..."><?= sanitize($existingFee ? $existingFee->getNotes() : '') ?></textarea>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-2"></i>
                        <?= $hasPending ? 'Update Payment Details' : 'Submit for Verification' ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-8 bg-white rounded-xl border overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-circle-question text-gray-400 mr-2"></i>
                    Frequently Asked Questions
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <details class="group">
                        <summary class="flex justify-between items-center cursor-pointer font-medium text-gray-900 hover:text-blue-600 transition">
                            <span>Is this a one-time fee?</span>
                            <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <p class="mt-2 text-gray-600 text-sm">Yes, the K150 registration fee is a one-time payment that allows you to enroll in any number of courses on the platform.</p>
                    </details>
                    <hr class="border-gray-100">
                    <details class="group">
                        <summary class="flex justify-between items-center cursor-pointer font-medium text-gray-900 hover:text-blue-600 transition">
                            <span>How long does verification take?</span>
                            <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <p class="mt-2 text-gray-600 text-sm">Payment verification typically takes 24 hours during business days. You will receive an email notification once your payment is verified.</p>
                    </details>
                    <hr class="border-gray-100">
                    <details class="group">
                        <summary class="flex justify-between items-center cursor-pointer font-medium text-gray-900 hover:text-blue-600 transition">
                            <span>What if my payment is not verified?</span>
                            <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <p class="mt-2 text-gray-600 text-sm">If there's an issue with your payment verification, our team will contact you via email or phone. You can also reach out to us at <?= SITE_EMAIL ?>.</p>
                    </details>
                    <hr class="border-gray-100">
                    <details class="group">
                        <summary class="flex justify-between items-center cursor-pointer font-medium text-gray-900 hover:text-blue-600 transition">
                            <span>Need help?</span>
                            <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <p class="mt-2 text-gray-600 text-sm">Contact us at <a href="mailto:<?= SITE_EMAIL ?>" class="text-blue-600 hover:underline"><?= SITE_EMAIL ?></a> or call <?= SITE_PHONE ?> for assistance.</p>
                    </details>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const sections = document.querySelectorAll('.payment-section');
    const paymentMethodInput = document.getElementById('payment_method');

    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tab = this.getAttribute('data-tab');
            
            // Update active tab styling
            tabBtns.forEach(function(b) {
                b.classList.remove('border-blue-600', 'text-blue-600');
                b.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-600', 'text-blue-600');
            
            // Show/hide sections
            sections.forEach(function(section) {
                section.classList.add('hidden');
            });
            document.getElementById('section-' + tab).classList.remove('hidden');
            
            // Update hidden input
            paymentMethodInput.value = tab;
        });
    });

    // Copy to clipboard functionality
    const copyBtns = document.querySelectorAll('.copy-btn');
    copyBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const text = this.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(function() {
                alert('Copied to clipboard: ' + text);
            }).catch(function(err) {
                console.error('Failed to copy:', err);
            });
        });
    });
});
</script>

<?php require_once '../src/templates/footer.php'; ?>
