<?php
/**
 * Certificate Verification Page
 * Public page to verify certificates
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/functions.php';
require_once '../src/classes/Certificate.php';

$code = $_GET['code'] ?? '';
$certificate = null;
$error = '';

if ($code) {
    $certificate = Certificate::verify($code);
    if (!$certificate) {
        $error = 'Invalid or revoked certificate';
    }
}

$page_title = 'Verify Certificate';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12">
    <div class="container mx-auto px-4">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-block bg-white rounded-full p-4 mb-4 shadow-lg">
                <i class="fas fa-certificate text-5xl text-indigo-600"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Certificate Verification</h1>
            <p class="text-gray-600">Verify the authenticity of TEVETA certificates</p>
        </div>

        <!-- Verification Form -->
        <?php if (!$certificate): ?>
        <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold mb-6 text-center">Enter Verification Code</h2>
            
            <form method="GET" action="" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Verification Code
                    </label>
                    <input type="text" 
                           name="code" 
                           value="<?= htmlspecialchars($code) ?>"
                           placeholder="Enter 16-character verification code"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center text-lg font-mono"
                           required
                           maxlength="16"
                           pattern="[A-Z0-9]{16}">
                    <p class="mt-2 text-sm text-gray-500">
                        <i class="fas fa-info-circle"></i> 
                        The verification code can be found on the certificate
                    </p>
                </div>
                
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <button type="submit" 
                        class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-search"></i> Verify Certificate
                </button>
            </form>
            
            <div class="mt-8 pt-8 border-t">
                <h3 class="font-semibold mb-3">How to verify:</h3>
                <ol class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <span class="bg-indigo-100 text-indigo-600 rounded-full w-6 h-6 flex items-center justify-center mr-3 flex-shrink-0">1</span>
                        <span>Locate the verification code on the certificate</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-indigo-100 text-indigo-600 rounded-full w-6 h-6 flex items-center justify-center mr-3 flex-shrink-0">2</span>
                        <span>Enter the 16-character code in the field above</span>
                    </li>
                    <li class="flex items-start">
                        <span class="bg-indigo-100 text-indigo-600 rounded-full w-6 h-6 flex items-center justify-center mr-3 flex-shrink-0">3</span>
                        <span>Scan the QR code on the certificate with your phone</span>
                    </li>
                </ol>
            </div>
        </div>
        <?php endif; ?>

        <!-- Verification Result -->
        <?php if ($certificate): ?>
        <div class="max-w-3xl mx-auto">
            
            <!-- Success Banner -->
            <div class="bg-green-50 border-l-4 border-green-500 p-6 mb-8 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-4xl text-green-500"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-2xl font-bold text-green-800">✅ Valid Certificate</h2>
                        <p class="text-green-700">This certificate is authentic and has been issued by our platform</p>
                    </div>
                </div>
            </div>

            <!-- Certificate Details -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                
                <!-- Header -->
                <div class="text-center pb-6 border-b">
                    <div class="inline-block bg-indigo-100 rounded-full p-3 mb-4">
                        <i class="fas fa-award text-3xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Certificate of Completion</h3>
                </div>

                <!-- Student Info -->
                <div class="mt-8">
                    <div class="grid md:grid-cols-2 gap-6">
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Recipient</p>
                                <p class="text-lg font-semibold text-gray-800">
                                    <?= htmlspecialchars($certificate['first_name'] . ' ' . $certificate['last_name']) ?>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Course</p>
                                <p class="text-lg font-semibold text-gray-800">
                                    <?= htmlspecialchars($certificate['course_title']) ?>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Issue Date</p>
                                <p class="text-lg font-semibold text-gray-800">
                                    <?= date('F d, Y', strtotime($certificate['issue_date'])) ?>
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Certificate Number</p>
                                <p class="text-lg font-mono font-semibold text-gray-800">
                                    <?= htmlspecialchars($certificate['certificate_number']) ?>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Verification Code</p>
                                <p class="text-lg font-mono font-semibold text-gray-800">
                                    <?= htmlspecialchars($certificate['verification_code']) ?>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Status</p>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Valid
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Verification Details -->
                <div class="mt-8 pt-6 border-t">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 mb-2">
                            <i class="fas fa-shield-alt"></i> Verification Details
                        </h4>
                        <div class="text-sm text-blue-800 space-y-1">
                            <p>✓ Certificate authenticity confirmed</p>
                            <p>✓ Issued by authorized institution</p>
                            <p>✓ Student identity verified</p>
                            <p>✓ Course completion verified</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <button onclick="window.print()" 
                            class="flex-1 bg-gray-100 text-gray-800 py-3 rounded-lg font-semibold hover:bg-gray-200 transition">
                        <i class="fas fa-print"></i> Print Verification
                    </button>
                    <button onclick="shareVerification()" 
                            class="flex-1 bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        <i class="fas fa-share-alt"></i> Share Verification
                    </button>
                </div>

            </div>

            <!-- Footer Info -->
            <div class="mt-8 text-center text-sm text-gray-600">
                <p>
                    <i class="fas fa-info-circle"></i>
                    This verification was performed on <?= date('F d, Y \a\t H:i:s') ?>
                </p>
                <p class="mt-2">
                    For questions about this certificate, please contact us at 
                    <a href="mailto:certificates@<?= parse_url(SITE_URL, PHP_URL_HOST) ?>" class="text-indigo-600 hover:underline">
                        certificates@<?= parse_url(SITE_URL, PHP_URL_HOST) ?>
                    </a>
                </p>
            </div>

        </div>
        <?php endif; ?>

        <!-- Additional Info -->
        <div class="max-w-4xl mx-auto mt-12 grid md:grid-cols-3 gap-6">
            
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-2xl text-blue-600"></i>
                </div>
                <h3 class="font-bold mb-2">Secure Verification</h3>
                <p class="text-sm text-gray-600">
                    All certificates are protected with unique codes and encrypted data
                </p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-certificate text-2xl text-green-600"></i>
                </div>
                <h3 class="font-bold mb-2">TEVETA Approved</h3>
                <p class="text-sm text-gray-600">
                    Certificates comply with TEVETA standards and regulations
                </p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-globe text-2xl text-purple-600"></i>
                </div>
                <h3 class="font-bold mb-2">Globally Recognized</h3>
                <p class="text-sm text-gray-600">
                    Our certificates are recognized by employers worldwide
                </p>
            </div>

        </div>

    </div>
</div>

<script>
function shareVerification() {
    const url = window.location.href;
    const text = 'Verified TEVETA Certificate';
    
    if (navigator.share) {
        navigator.share({
            title: text,
            url: url
        }).catch(console.error);
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            alert('Verification URL copied to clipboard!');
        });
    }
}
</script>

<?php require_once '../src/templates/footer.php'; ?>