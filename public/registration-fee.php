<?php
/**
 * Registration Fee Payment Page - Lenco Mobile Money Collection
 * K150 one-time registration fee
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/classes/RegistrationFee.php';
require_once __DIR__ . '/../src/classes/Lenco.php';

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

// Check for pending Lenco collection
$lenco = new Lenco();
$pendingCollections = $lenco->getPendingCollections($userId);
$hasPendingCollection = !empty($pendingCollections);

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

        if (empty($errors)) {
            $data = [
                'user_id' => $userId,
                'amount' => $feeAmount,
                'payment_method' => 'bank_deposit',
                'bank_reference' => $bankReference,
                'bank_name' => $bankName,
                'deposit_date' => $depositDate,
                'notes' => $notes
            ];

            if ($hasPending) {
                $existingFee->update($data);
            } else {
                RegistrationFee::create($data);
            }

            setFlashMessage('Bank payment details submitted! Your payment will be verified within 24 hours.', 'success');
            redirect('registration-fee.php');
        }
    } else {
        // Mobile money via Lenco Collection API
        if (empty($phoneNumber)) {
            $errors[] = 'Your mobile money phone number is required';
        }

        if (empty($errors)) {
            // Initiate Lenco mobile money collection
            $collectionResult = $lenco->initiateMobileMoneyCollection([
                'amount' => $feeAmount,
                'currency' => 'ZMW',
                'phone' => $phoneNumber,
                'country' => 'ZM',
                'user_id' => $userId,
                'bearer' => 'customer',
                'metadata' => [
                    'type' => 'registration_fee',
                    'user_name' => $user['first_name'] . ' ' . $user['last_name']
                ]
            ]);

            if ($collectionResult['success']) {
                // Store registration fee record linked to collection
                $data = [
                    'user_id' => $userId,
                    'amount' => $feeAmount,
                    'payment_method' => 'mobile_money',
                    'bank_reference' => $collectionResult['reference'],
                    'bank_name' => 'Lenco Mobile Money',
                    'deposit_date' => date('Y-m-d'),
                    'phone_number' => $phoneNumber,
                    'notes' => $notes
                ];

                if ($hasPending) {
                    $existingFee->update($data);
                } else {
                    RegistrationFee::create($data);
                }

                setFlashMessage('Payment initiated! Please approve the payment on your mobile phone.', 'success');
                redirect('registration-fee.php?status=pending');
            } else {
                setFlashMessage('Failed to initiate mobile money payment: ' . ($collectionResult['error'] ?? 'Please try again'), 'error');
            }
        }
    }

    if (!empty($errors)) {
        setFlashMessage(implode('<br>', $errors), 'error');
    }
}

// Check status of pending collection
$collectionStatus = null;
if ($hasPendingCollection && isset($_GET['check_status'])) {
    $latestCollection = $pendingCollections[0];
    $statusResult = $lenco->checkMobileMoneyCollectionStatus($latestCollection['lenco_collection_id']);
    if ($statusResult['success'] && $statusResult['status'] === 'successful') {
        // Mark registration fee as paid
        if ($existingFee) {
            $existingFee->verify(0); // System verified
        }
        setFlashMessage('Payment successful! Your registration is complete.', 'success');
        redirect('dashboard.php');
    }
    $collectionStatus = $statusResult;
}

$page_title = 'Registration Fee Payment';
require_once __DIR__ . '/../src/templates/header.php';
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

        <?php if ($hasPending || $hasPendingCollection): ?>
        <!-- Pending Status Alert -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6">
            <div class="flex items-start">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-semibold text-amber-900">Payment in Progress</h3>
                    <p class="mt-1 text-amber-700">
                        <?php if ($hasPendingCollection): ?>
                        We've sent a payment request to your mobile phone. Please approve it to complete your registration.
                        <?php else: ?>
                        Your registration fee payment is being verified. This usually takes 24 hours during business days.
                        <?php endif; ?>
                    </p>
                    
                    <?php if ($hasPendingCollection): ?>
                    <div class="mt-4 bg-white rounded-lg p-4 border border-amber-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Phone Number:</p>
                                <p class="font-medium text-gray-900"><?= sanitize($pendingCollections[0]['phone']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Amount:</p>
                                <p class="font-bold text-gray-900">K<?= number_format($pendingCollections[0]['amount'], 2) ?></p>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <a href="registration-fee.php?check_status=1" class="flex-1 bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-sync-alt mr-1"></i> Check Status
                            </a>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 text-center">
                            Didn't receive the prompt? <a href="registration-fee.php" class="text-blue-600 hover:underline">Try again</a>
                        </p>
                    </div>
                    <?php endif; ?>
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
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <div class="flex items-start">
                                <i class="fas fa-mobile-alt text-green-600 mt-1 mr-3 text-xl"></i>
                                <div>
                                    <p class="font-medium text-green-800">Pay with Mobile Money</p>
                                    <p class="mt-1 text-sm text-green-700">
                                        Enter your mobile money number below. You'll receive a prompt on your phone to approve the payment.
                                    </p>
                                </div>
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
                            <p class="mt-1 text-xs text-gray-500">Supported: MTN Mobile Money, Airtel Money</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                How it works:
                            </h4>
                            <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                                <li>Enter your mobile money number above</li>
                                <li>Click "Pay Now" to initiate the payment</li>
                                <li>You'll receive a prompt on your phone</li>
                                <li>Enter your PIN to approve the payment</li>
                                <li>Your registration will be completed automatically</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Bank Transfer Section (Hidden by default) -->
                    <div id="section-bank" class="payment-section space-y-6 hidden">
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
                                        <span class="font-mono font-medium text-gray-900 mr-2"><?= sanitize($bankDetails['account_number'] ?? 'Contact Admin') ?></span>
                                        <?php if (!empty($bankDetails['account_number'])): ?>
                                        <button type="button" class="copy-btn text-blue-600 hover:text-blue-800 text-xs" data-copy="<?= $bankDetails['account_number'] ?>">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="bank_reference" class="block text-sm font-medium text-gray-700 mb-1">
                                    Deposit Reference <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="bank_reference" name="bank_reference"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       placeholder="e.g., DEP123456789">
                            </div>
                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Bank Name <span class="text-red-500">*</span>
                                </label>
                                <select id="bank_name" name="bank_name"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="">Select bank...</option>
                                    <option value="Zanaco">Zanaco</option>
                                    <option value="Stanbic Bank">Stanbic Bank</option>
                                    <option value="Standard Chartered">Standard Chartered</option>
                                    <option value="FNB">FNB Zambia</option>
                                    <option value="Absa">Absa Bank</option>
                                    <option value="Access Bank">Access Bank</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="deposit_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Deposit Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="deposit_date" name="deposit_date"
                                       value="<?= date('Y-m-d') ?>"
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
                        <textarea id="notes" name="notes" rows="2"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                  placeholder="Any additional information..."><?= sanitize($existingFee ? $existingFee->getNotes() : '') ?></textarea>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition flex items-center justify-center">
                        <i class="fas fa-lock mr-2"></i>
                        <span id="submit-text">Pay K<?= number_format($feeAmount, 2) ?> Now</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const sections = document.querySelectorAll('.payment-section');
    const paymentMethodInput = document.getElementById('payment_method');
    const submitText = document.getElementById('submit-text');

    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tab = this.getAttribute('data-tab');
            
            tabBtns.forEach(function(b) {
                b.classList.remove('border-blue-600', 'text-blue-600');
                b.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-600', 'text-blue-600');
            
            sections.forEach(function(section) {
                section.classList.add('hidden');
            });
            document.getElementById('section-' + tab).classList.remove('hidden');
            
            paymentMethodInput.value = tab;
            
            // Update button text
            if (tab === 'bank') {
                submitText.textContent = 'Submit Bank Payment Details';
            } else {
                submitText.textContent = 'Pay K<?= number_format($feeAmount, 2) ?> Now';
            }
        });
    });

    // Copy buttons
    document.querySelectorAll('.copy-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const text = this.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(function() {
                alert('Copied: ' + text);
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
