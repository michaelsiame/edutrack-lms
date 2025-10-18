<?php
/**
 * Download Certificate Page
 * Allows students to download their certificates
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/functions.php';
require_once '../src/classes/Certificate.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$certificateId = $_GET['id'] ?? null;

if (!$certificateId) {
    redirect('my-certificates.php');
}

// Get certificate
$certificate = Certificate::find($certificateId);
$data = $certificate->getData();

// Check if certificate belongs to logged-in user
if (!$data || $data['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = 'Certificate not found or access denied';
    redirect('my-certificates.php');
}

// If action is download, serve the PDF
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    $pdfPath = '../public/certificates/' . $data['pdf_path'];
    
    if (!file_exists($pdfPath)) {
        // Generate PDF if not exists
        $certificate->generatePDF();
        $data = $certificate->getData();
        $pdfPath = '../public/certificates/' . $data['pdf_path'];
    }
    
    if (file_exists($pdfPath)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $data['pdf_path'] . '"');
        header('Content-Length: ' . filesize($pdfPath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        readfile($pdfPath);
        exit;
    } else {
        $_SESSION['error'] = 'Certificate PDF not found';
        redirect('my-certificates.php');
    }
}

$page_title = 'Download Certificate';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        
        <div class="max-w-4xl mx-auto">
            
            <!-- Back Button -->
            <a href="my-certificates.php" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 mb-6">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to My Certificates
            </a>

            <!-- Certificate Preview -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold mb-2">🎉 Your Certificate</h1>
                            <p class="opacity-90">Certificate of Completion</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 rounded-full text-sm font-semibold">
                                <i class="fas fa-check-circle mr-2"></i>
                                Issued
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Certificate Details -->
                <div class="p-8">
                    
                    <!-- Course Info -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">
                            <?= htmlspecialchars($data['course_title']) ?>
                        </h2>
                        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt mr-2 text-indigo-600"></i>
                                Issued: <?= date('F d, Y', strtotime($data['issue_date'])) ?>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-hashtag mr-2 text-indigo-600"></i>
                                <?= htmlspecialchars($data['certificate_number']) ?>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-shield-alt mr-2 text-indigo-600"></i>
                                Verified
                            </div>
                        </div>
                    </div>

                    <!-- Certificate Details Grid -->
                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                        
                        <div class="bg-gray-50 rounded-lg p-6">
                            <p class="text-sm text-gray-500 mb-2">Recipient</p>
                            <p class="text-lg font-semibold text-gray-800">
                                <?= htmlspecialchars($data['first_name'] . ' ' . $data['last_name']) ?>
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-6">
                            <p class="text-sm text-gray-500 mb-2">Verification Code</p>
                            <p class="text-lg font-mono font-semibold text-gray-800">
                                <?= htmlspecialchars($data['verification_code']) ?>
                            </p>
                        </div>

                    </div>

                    <!-- Actions -->
                    <div class="grid sm:grid-cols-2 gap-4 mb-8">
                        
                        <a href="?id=<?= $certificateId ?>&action=download" 
                           class="flex items-center justify-center bg-indigo-600 text-white py-4 rounded-lg font-semibold hover:bg-indigo-700 transition">
                            <i class="fas fa-download mr-2"></i>
                            Download PDF
                        </a>

                        <a href="verify-certificate.php?code=<?= htmlspecialchars($data['verification_code']) ?>" 
                           target="_blank"
                           class="flex items-center justify-center bg-white border-2 border-indigo-600 text-indigo-600 py-4 rounded-lg font-semibold hover:bg-indigo-50 transition">
                            <i class="fas fa-check-circle mr-2"></i>
                            Verify Online
                        </a>

                    </div>

                    <!-- Share Section -->
                    <div class="border-t pt-8">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Share Your Achievement</h3>
                        
                        <div class="flex flex-wrap gap-3">
                            
                            <button onclick="shareLinkedIn()" 
                                    class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fab fa-linkedin mr-2"></i>
                                LinkedIn
                            </button>

                            <button onclick="shareTwitter()" 
                                    class="flex items-center px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition">
                                <i class="fab fa-twitter mr-2"></i>
                                Twitter
                            </button>

                            <button onclick="shareFacebook()" 
                                    class="flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                                <i class="fab fa-facebook mr-2"></i>
                                Facebook
                            </button>

                            <button onclick="copyLink()" 
                                    class="flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                                <i class="fas fa-link mr-2"></i>
                                Copy Link
                            </button>

                        </div>
                    </div>

                    <!-- Verification Info -->
                    <div class="mt-8 bg-blue-50 border border-blue-100 rounded-lg p-6">
                        <h4 class="font-bold text-blue-900 mb-3">
                            <i class="fas fa-info-circle"></i> About This Certificate
                        </h4>
                        <div class="text-sm text-blue-800 space-y-2">
                            <p>
                                <i class="fas fa-check mr-2"></i>
                                This certificate is digitally signed and verifiable
                            </p>
                            <p>
                                <i class="fas fa-check mr-2"></i>
                                Employers can verify authenticity using the verification code
                            </p>
                            <p>
                                <i class="fas fa-check mr-2"></i>
                                TEVETA-compliant certificate recognized nationwide
                            </p>
                            <p>
                                <i class="fas fa-check mr-2"></i>
                                Add to your resume, LinkedIn profile, or portfolio
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Tips Section -->
            <div class="mt-8 grid md:grid-cols-3 gap-6">
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="bg-green-100 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-briefcase text-green-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold mb-2">Add to Resume</h3>
                    <p class="text-sm text-gray-600">
                        Include this certificate in your CV under certifications or education
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="bg-blue-100 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fab fa-linkedin text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold mb-2">Share on LinkedIn</h3>
                    <p class="text-sm text-gray-600">
                        Add to your LinkedIn profile to showcase your skills to employers
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="bg-purple-100 rounded-full w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold mb-2">Share with Network</h3>
                    <p class="text-sm text-gray-600">
                        Let your professional network know about your achievement
                    </p>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
const certificateUrl = '<?= SITE_URL ?>/verify-certificate.php?code=<?= htmlspecialchars($data['verification_code']) ?>';
const courseName = <?= json_encode($data['course_title']) ?>;

function shareLinkedIn() {
    const url = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(certificateUrl)}`;
    window.open(url, '_blank', 'width=600,height=600');
}

function shareTwitter() {
    const text = `I just completed "${courseName}" and earned my certificate! 🎉`;
    const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(certificateUrl)}`;
    window.open(url, '_blank', 'width=600,height=600');
}

function shareFacebook() {
    const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(certificateUrl)}`;
    window.open(url, '_blank', 'width=600,height=600');
}

function copyLink() {
    navigator.clipboard.writeText(certificateUrl).then(() => {
        alert('Verification link copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}
</script>

<?php require_once '../src/templates/footer.php'; ?>