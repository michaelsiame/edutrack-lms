<?php
/**
 * Edutrack Computer Training College
 * My Certificates Page
 */

require_once '../src/middleware/authenticate.php';
require_once '../src/classes/User.php';

// Get current user
$user = User::current();

// Get certificates
$certificates = $user->getCertificates();

$page_title = "My Certificates - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Certificates</h1>
            <p class="text-gray-600 mt-2">Your TEVETA-certified achievements and credentials</p>
        </div>
        
        <!-- Stats Banner -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-lg shadow-md p-6 text-white mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div>
                    <i class="fas fa-certificate text-secondary-500 text-4xl mb-2"></i>
                    <p class="text-3xl font-bold"><?= count($certificates) ?></p>
                    <p class="text-primary-100">Certificates Earned</p>
                </div>
                <div>
                    <i class="fas fa-award text-secondary-500 text-4xl mb-2"></i>
                    <p class="text-3xl font-bold"><?= $user->getCompletedCoursesCount() ?></p>
                    <p class="text-primary-100">Courses Completed</p>
                </div>
                <div>
                    <i class="fas fa-graduation-cap text-secondary-500 text-4xl mb-2"></i>
                    <p class="text-3xl font-bold"><?= TEVETA_CODE ?></p>
                    <p class="text-primary-100">TEVETA Registered</p>
                </div>
            </div>
        </div>
        
        <?php if (!empty($certificates)): ?>
            <!-- Certificates Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($certificates as $certificate): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <!-- Certificate Preview -->
                        <div class="relative h-48 bg-gradient-to-br from-primary-100 to-secondary-100 flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-certificate text-primary-600 text-6xl mb-3"></i>
                                <p class="text-primary-800 font-bold text-sm">TEVETA CERTIFIED</p>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                <?php if ($certificate['status'] === 'issued'): ?>
                                    <span class="px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i>Issued
                                    </span>
                                <?php elseif ($certificate['status'] === 'expired'): ?>
                                    <span class="px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-full">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Expired
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                                <?= sanitize($certificate['course_title']) ?>
                            </h3>
                            
                            <!-- Certificate Details -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-hashtag text-gray-400 mr-2 w-4"></i>
                                    <span class="font-mono"><?= sanitize($certificate['certificate_number']) ?></span>
                                </div>
                                
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-calendar text-gray-400 mr-2 w-4"></i>
                                    <span>Issued: <?= formatDate($certificate['issue_date']) ?></span>
                                </div>
                                
                                <?php if ($certificate['grade']): ?>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-star text-yellow-400 mr-2 w-4"></i>
                                        <span>Grade: <strong><?= sanitize($certificate['grade']) ?></strong></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($certificate['teveta_registration_number']): ?>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-certificate text-secondary-500 mr-2 w-4"></i>
                                        <span class="font-mono text-xs"><?= sanitize($certificate['teveta_registration_number']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Verification Code -->
                            <?php if ($certificate['verification_code']): ?>
                                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                    <p class="text-xs text-gray-500 mb-1">Verification Code</p>
                                    <p class="font-mono text-sm font-bold text-gray-900">
                                        <?= sanitize($certificate['verification_code']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <?php if ($certificate['certificate_file_path'] && file_exists(UPLOAD_PATH . '/' . $certificate['certificate_file_path'])): ?>
                                    <a href="<?= uploadUrl($certificate['certificate_file_path']) ?>" 
                                       target="_blank"
                                       class="flex-1 text-center py-2 px-4 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition font-medium text-sm">
                                        <i class="fas fa-download mr-1"></i>Download
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?= url('certificate-verify.php?code=' . $certificate['verification_code']) ?>" 
                                   target="_blank"
                                   class="flex-1 text-center py-2 px-4 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition font-medium text-sm">
                                    <i class="fas fa-shield-alt mr-1"></i>Verify
                                </a>
                                
                                <button onclick="shareCertificate('<?= sanitize($certificate['certificate_number']) ?>')"
                                        class="py-2 px-4 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Certificate Information -->
            <div class="mt-12 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">About Your Certificates</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex items-start mb-4">
                            <i class="fas fa-check-circle text-green-600 text-xl mr-3 mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">TEVETA Certified</h3>
                                <p class="text-sm text-gray-600">
                                    All certificates are issued by Edutrack Computer Training College, 
                                    a TEVETA-registered institution (<?= TEVETA_CODE ?>).
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start mb-4">
                            <i class="fas fa-shield-alt text-primary-600 text-xl mr-3 mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Verifiable</h3>
                                <p class="text-sm text-gray-600">
                                    Each certificate has a unique verification code that employers 
                                    can use to confirm authenticity.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-start mb-4">
                            <i class="fas fa-briefcase text-secondary-600 text-xl mr-3 mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Employer Recognized</h3>
                                <p class="text-sm text-gray-600">
                                    TEVETA certificates are recognized by employers across Zambia 
                                    and internationally.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start mb-4">
                            <i class="fas fa-download text-purple-600 text-xl mr-3 mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Digital & Printable</h3>
                                <p class="text-sm text-gray-600">
                                    Download your certificates in PDF format for printing or 
                                    sharing with potential employers.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-12">
                <?php emptyState(
                    'fa-certificate',
                    'No Certificates Yet',
                    'Complete your courses to earn TEVETA-certified certificates that employers recognize',
                    url('my-courses.php'),
                    'View My Courses'
                ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Share certificate
function shareCertificate(certificateNumber) {
    const verifyUrl = '<?= url("certificate-verify.php?code=") ?>' + certificateNumber;
    
    if (navigator.share) {
        navigator.share({
            title: 'My TEVETA Certificate',
            text: 'Check out my TEVETA-certified certificate from Edutrack Computer Training College!',
            url: verifyUrl
        });
    } else {
        // Fallback: Copy to clipboard
        const tempInput = document.createElement('input');
        document.body.appendChild(tempInput);
        tempInput.value = verifyUrl;
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        alert('Verification link copied to clipboard!');
    }
}
</script>

<?php require_once '../src/templates/footer.php'; ?>