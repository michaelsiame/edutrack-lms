<?php
/**
 * Certificate Verification
 * Public page to verify TEVETA certificates
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Certificate.php';

// Get verification code from URL or form
$verificationCode = trim($_GET['code'] ?? $_POST['code'] ?? '');
$certificate = null;
$verified = false;
$error = '';

if (!empty($verificationCode)) {
    // Clean up the verification code
    $verificationCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $verificationCode));

    // Find certificate by verification code
    $certificate = Certificate::findByVerificationCode($verificationCode);

    if ($certificate) {
        $verified = true;
    } else {
        $error = 'No certificate found with this verification code. Please check and try again.';
    }
}

$page_title = 'Verify Certificate - Edutrack';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center items-center space-x-4 mb-4">
                <img src="<?= asset('images/logo.png') ?>" alt="Edutrack" class="h-16">
                <img src="<?= asset('images/teveta-logo.png') ?>" alt="TEVETA" class="h-16">
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Certificate Verification</h1>
            <p class="text-gray-600 mt-2">Verify the authenticity of Edutrack certificates</p>
        </div>

        <!-- Verification Form -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
            <div class="bg-primary-600 px-6 py-4">
                <h2 class="text-white font-semibold flex items-center">
                    <i class="fas fa-shield-alt mr-2"></i>
                    Enter Verification Code
                </h2>
            </div>
            <div class="p-6">
                <form method="GET" action="">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <input type="text"
                               name="code"
                               value="<?= sanitize($verificationCode) ?>"
                               placeholder="Enter 16-character verification code"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono uppercase"
                               maxlength="20"
                               required>
                        <button type="submit"
                                class="px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition">
                            <i class="fas fa-search mr-2"></i>Verify
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        The verification code can be found on the certificate (e.g., AB12CD34EF56GH78)
                    </p>
                </form>
            </div>
        </div>

        <?php if ($error): ?>
        <!-- Error Message -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-red-900">Certificate Not Found</h3>
                    <p class="text-red-700"><?= sanitize($error) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($verified && $certificate): ?>
        <!-- Verified Certificate -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Verified Header -->
            <div class="bg-green-600 px-6 py-8 text-center">
                <div class="w-20 h-20 bg-white rounded-full mx-auto flex items-center justify-center mb-4">
                    <i class="fas fa-check-circle text-green-600 text-5xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">Certificate Verified</h2>
                <p class="text-green-100 mt-2">This is an authentic Edutrack certificate</p>
            </div>

            <!-- Certificate Details -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Certificate Holder</h3>
                        <p class="text-xl font-bold text-gray-900 mt-1">
                            <?= sanitize($certificate->getStudentName()) ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Course Completed</h3>
                        <p class="text-xl font-bold text-primary-600 mt-1">
                            <?= sanitize($certificate->getCourseTitle()) ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Certificate Number</h3>
                        <p class="font-mono text-gray-900 mt-1">
                            <?= sanitize($certificate->getCertificateNumber()) ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Issue Date</h3>
                        <p class="text-gray-900 mt-1">
                            <?= date('F j, Y', strtotime($certificate->getIssuedAt())) ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Final Score</h3>
                        <p class="text-gray-900 mt-1">
                            <span class="text-2xl font-bold text-green-600"><?= round($certificate->getFinalScore()) ?>%</span>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Verification Code</h3>
                        <p class="font-mono text-gray-900 mt-1">
                            <?= sanitize($certificate->getVerificationCode()) ?>
                        </p>
                    </div>
                </div>

                <!-- Institution Info -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex items-center">
                        <img src="<?= asset('images/logo.png') ?>" alt="Edutrack" class="h-12 mr-4">
                        <div>
                            <h4 class="font-semibold text-gray-900">Edutrack Computer Training College</h4>
                            <p class="text-sm text-gray-600">TEVETA Registered Institution (<?= defined('TEVETA_CODE') ? TEVETA_CODE : 'N/A' ?>)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Info Section -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-semibold text-blue-900 mb-3 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                About Certificate Verification
            </h3>
            <ul class="space-y-2 text-blue-800 text-sm">
                <li class="flex items-start">
                    <i class="fas fa-check text-blue-600 mt-1 mr-2"></i>
                    <span>All certificates issued by Edutrack are digitally verified and can be authenticated using this system.</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check text-blue-600 mt-1 mr-2"></i>
                    <span>The verification code is unique to each certificate and cannot be duplicated.</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check text-blue-600 mt-1 mr-2"></i>
                    <span>Edutrack is registered with TEVETA (Technical Education, Vocational and Entrepreneurship Training Authority).</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check text-blue-600 mt-1 mr-2"></i>
                    <span>For any questions about certificate verification, contact us at <?= SITE_EMAIL ?>.</span>
                </li>
            </ul>
        </div>

    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>
