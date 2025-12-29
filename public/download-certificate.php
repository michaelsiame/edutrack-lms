<?php
/**
 * Download Certificate Page
 * Allows students to download their certificates
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Certificate.php';
require_once '../src/classes/PaymentPlan.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

// Get certificate ID from URL
$certificateId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$certificateId) {
    flash('error', 'Invalid certificate', 'error');
    redirect('my-certificates.php');
}

try {
    // Find the certificate
    $certificate = Certificate::find($certificateId);

    if (!$certificate) {
        flash('error', 'Certificate not found', 'error');
        redirect('my-certificates.php');
    }

    // Verify ownership
    if ($certificate->getUserId() != $userId) {
        flash('error', 'You do not have permission to access this certificate', 'error');
        redirect('my-certificates.php');
    }

    // Check if fees are fully paid
    $courseId = $certificate->getCourseId();
    $paymentPlan = PaymentPlan::getByCourseAndUser($courseId, $userId);

    if ($paymentPlan && $paymentPlan['balance'] > 0) {
        flash('error', 'Please clear your outstanding balance of K' . number_format($paymentPlan['balance'], 2) . ' to download your certificate.', 'error');
        redirect('my-payments.php');
    }

    // Check if download is requested
    $action = $_GET['action'] ?? 'view';

    if ($action === 'download' || $action === 'pdf') {
        // Generate and download PDF
        $pdfContent = $certificate->generatePDF();

        if ($pdfContent === false) {
            // PDF generation not available, show alternative
            flash('warning', 'PDF generation is currently unavailable. Please contact the office to collect your physical certificate.', 'warning');
            redirect('my-certificates.php');
        }

        // Set headers for PDF download
        $fileName = 'Certificate_' . $certificate->getCertificateNumber() . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdfContent;
        exit;
    }

    // View certificate details page
    $page_title = 'Certificate - ' . $certificate->getCourseTitle();
    require_once '../src/templates/header.php';

} catch (Exception $e) {
    error_log("Download Certificate Error: " . $e->getMessage());
    flash('error', 'An error occurred loading the certificate', 'error');
    redirect('my-certificates.php');
}
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="<?= url('dashboard.php') ?>" class="hover:text-primary-600">Dashboard</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?= url('my-certificates.php') ?>" class="hover:text-primary-600">My Certificates</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-gray-900"><?= sanitize($certificate->getCourseTitle()) ?></li>
            </ol>
        </nav>

        <!-- Certificate Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-8 text-center">
                <div class="w-20 h-20 bg-white rounded-full mx-auto flex items-center justify-center mb-4">
                    <i class="fas fa-certificate text-yellow-500 text-4xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white">Certificate of Completion</h1>
                <p class="text-yellow-100 mt-2">TEVETA Registered Training</p>
            </div>

            <!-- Certificate Details -->
            <div class="p-6">
                <div class="text-center mb-6">
                    <p class="text-gray-600">This certifies that</p>
                    <h2 class="text-3xl font-bold text-gray-900 my-2"><?= sanitize($user->getFullName()) ?></h2>
                    <p class="text-gray-600">has successfully completed the course</p>
                    <h3 class="text-xl font-semibold text-primary-600 mt-2"><?= sanitize($certificate->getCourseTitle()) ?></h3>
                </div>

                <!-- Certificate Info Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-sm text-gray-600">Certificate Number</div>
                        <div class="font-mono font-bold text-gray-900"><?= sanitize($certificate->getCertificateNumber()) ?></div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-sm text-gray-600">Issue Date</div>
                        <div class="font-bold text-gray-900"><?= date('M d, Y', strtotime($certificate->getIssuedAt())) ?></div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-sm text-gray-600">Final Score</div>
                        <div class="font-bold text-green-600"><?= round($certificate->getFinalScore()) ?>%</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-sm text-gray-600">Verification Code</div>
                        <div class="font-mono font-bold text-gray-900 text-sm"><?= sanitize($certificate->getVerificationCode()) ?></div>
                    </div>
                </div>

                <!-- Verification Link -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-shield-alt text-blue-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-blue-900">Verify this Certificate</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                This certificate can be verified at:
                                <a href="<?= url('verify-certificate.php?code=' . urlencode($certificate->getVerificationCode())) ?>"
                                   class="underline font-medium">
                                    <?= url('verify-certificate.php?code=' . urlencode($certificate->getVerificationCode())) ?>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Download Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?= url('download-certificate.php?id=' . $certificateId . '&action=download') ?>"
                       class="flex-1 py-3 px-6 bg-primary-600 text-white text-center font-semibold rounded-lg hover:bg-primary-700 transition">
                        <i class="fas fa-download mr-2"></i>Download PDF
                    </a>
                    <a href="<?= url('my-certificates.php') ?>"
                       class="flex-1 py-3 px-6 bg-gray-200 text-gray-700 text-center font-semibold rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Certificates
                    </a>
                </div>
            </div>
        </div>

        <!-- TEVETA Info -->
        <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-start">
                <img src="<?= asset('images/teveta-logo.png') ?>" alt="TEVETA" class="w-12 h-12 mr-4">
                <div>
                    <h4 class="font-semibold text-green-900">TEVETA Registered Certification</h4>
                    <p class="text-sm text-green-700 mt-1">
                        This certificate is issued by Edutrack Computer Training College, a TEVETA registered institution
                        (Code: <?= defined('TEVETA_CODE') ? TEVETA_CODE : 'N/A' ?>).
                        This certification is recognized by the Technical Education, Vocational and Entrepreneurship Training Authority of Zambia.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>
