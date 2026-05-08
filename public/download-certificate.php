<?php
/**
 * Download Certificate Page
 * Allows students to download their certificates
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/classes/Certificate.php';
require_once __DIR__ . '/../src/classes/PaymentPlan.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

// Get certificate ID from URL
$certificateId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$certificateId) {
    error_log("[CERT-DEBUG] download-certificate.php: Invalid certificate ID from URL");
    flash('error', 'Invalid certificate', 'error');
    redirect('my-certificates.php');
}

error_log("[CERT-DEBUG] download-certificate.php: Starting for cert_id={$certificateId}, user_id={$userId}");

try {
    // Find the certificate
    error_log("[CERT-DEBUG] download-certificate.php: Calling Certificate::find({$certificateId})");
    $certificate = Certificate::find($certificateId);

    if (!$certificate) {
        error_log("[CERT-DEBUG] download-certificate.php: Certificate not found for id={$certificateId}");
        flash('error', 'Certificate not found', 'error');
        redirect('my-certificates.php');
    }
    error_log("[CERT-DEBUG] download-certificate.php: Certificate found. cert_user_id=" . ($certificate->getUserId() ?? 'NULL') . ", course_id=" . ($certificate->getCourseId() ?? 'NULL'));

    // Verify ownership (allow admins to view any certificate)
    $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    error_log("[CERT-DEBUG] download-certificate.php: Ownership check — cert_user_id=" . ($certificate->getUserId() ?? 'NULL') . ", session_user_id={$userId}, isAdmin=" . ($isAdmin ? 'true' : 'false'));
    if ($certificate->getUserId() != $userId && !$isAdmin) {
        error_log("[CERT-DEBUG] download-certificate.php: Ownership check FAILED. Redirecting.");
        flash('error', 'You do not have permission to access this certificate', 'error');
        redirect('my-certificates.php');
    }
    error_log("[CERT-DEBUG] download-certificate.php: Ownership check passed");

    // Check if fees are fully paid
    $courseId = $certificate->getCourseId();
    error_log("[CERT-DEBUG] download-certificate.php: Checking payment plan for course_id=" . ($courseId ?? 'NULL') . ", user_id={$userId}");
    $paymentPlan = PaymentPlan::getByCourseAndUser($courseId, $userId);

    if ($paymentPlan && $paymentPlan['balance'] > 0) {
        error_log("[CERT-DEBUG] download-certificate.php: Outstanding balance K" . number_format($paymentPlan['balance'], 2) . ". Redirecting to payments.");
        flash('error', 'Please clear your outstanding balance of K' . number_format($paymentPlan['balance'], 2) . ' to download your certificate.', 'error');
        redirect('my-payments.php');
    }
    error_log("[CERT-DEBUG] download-certificate.php: Payment plan check passed (balance=0 or no plan)");

    // Check if download is requested
    $action = $_GET['action'] ?? 'view';
    $debugMode = isset($_GET['debug']) && $_GET['debug'] == '1';
    error_log("[CERT-DEBUG] download-certificate.php: action={$action}, debugMode=" . ($debugMode ? 'true' : 'false'));

    // DEBUG MODE: render raw HTML template instead of PDF
    if ($debugMode) {
        error_log("[CERT-DEBUG] download-certificate.php: Entering DEBUG mode — returning raw HTML preview");
        $html = $certificate->getDebugHtml();
        if ($html === false) {
            error_log("[CERT-DEBUG] download-certificate.php: getDebugHtml() returned false");
            http_response_code(500);
            echo "<h1>Debug Error</h1><p>Could not generate preview HTML. Check error logs.</p>";
            exit;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo "<!DOCTYPE html><html><head><title>Certificate Debug Preview</title></head><body style='background:#333; padding:20px;'>";
        echo "<h2 style='color:#fff; font-family:sans-serif;'>Debug Preview — Certificate ID {$certificateId}</h2>";
        echo "<div style='background:#fff; padding:20px; max-width:1000px; margin:0 auto; border:2px solid #000;'>";
        echo $html;
        echo "</div>";
        echo "<hr style='margin:20px 0;'><pre style='color:#0f0; background:#000; padding:15px; max-width:1000px; margin:0 auto; font-size:12px; overflow:auto;'>";
        echo "Certificate Data:\n";
        print_r($certificate->getData());
        echo "\n\nSession User ID: {$userId}\n";
        echo "Is Admin: " . ($isAdmin ? 'Yes' : 'No') . "\n";
        echo "</pre></body></html>";
        exit;
    }

    if ($action === 'download' || $action === 'pdf') {
        error_log("[CERT-DEBUG] download-certificate.php: Generating PDF...");
        // Generate and download PDF
        $pdfContent = $certificate->generatePDF();

        if ($pdfContent === false) {
            error_log("[CERT-DEBUG] download-certificate.php: generatePDF() returned FALSE. PDF generation failed.");
            // PDF generation not available, show alternative
            flash('warning', 'PDF generation is currently unavailable. Please contact the office to collect your physical certificate.', 'warning');
            redirect('my-certificates.php');
        }

        error_log("[CERT-DEBUG] download-certificate.php: PDF generated successfully. Size=" . strlen($pdfContent) . " bytes");

        // Set headers for PDF download
        $fileName = 'Certificate_' . $certificate->getCertificateNumber() . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdfContent;
        error_log("[CERT-DEBUG] download-certificate.php: PDF streamed to client. Exiting.");
        exit;
    }

    // View certificate details page
    $page_title = 'Certificate - ' . $certificate->getCourseTitle();
    require_once __DIR__ . '/../src/templates/header.php';

} catch (Exception $e) {
    error_log("[CERT-DEBUG] download-certificate.php: EXCEPTION caught: " . get_class($e) . " — " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
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
            <div class="bg-yellow-500 px-6 py-8 text-center">
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
                <img src="<?= asset('images/teveta-logo.svg') ?>" alt="TEVETA" class="w-12 h-12 mr-4">
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

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
