<?php
/**
 * My Certificates Page
 * Student dashboard to view all earned certificates
 */

require_once '../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user
$user = User::current();
$userId = $user->getId();

// Get all certificates for the student (certificates link to enrollments, not directly to courses/users)
$certificates = $db->fetchAll("
    SELECT cert.certificate_id as id, cert.certificate_number, cert.issued_date,
           cert.certificate_url, cert.verification_code, cert.is_verified, cert.expiry_date,
           cert.issued_date as issued_at,
           c.title as course_title, c.slug as course_slug,
           c.thumbnail_url, c.instructor_id,
           u.first_name as instructor_first_name, u.last_name as instructor_last_name,
           e.enrolled_at, e.completion_date as course_completed_at
    FROM certificates cert
    JOIN enrollments e ON cert.enrollment_id = e.id
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN users u ON c.instructor_id = u.id
    WHERE e.user_id = ?
    ORDER BY cert.issued_date DESC
", [$userId]);

$page_title = "My Certificates - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-certificate text-primary-600 mr-3"></i>
                My Certificates
            </h1>
            <p class="text-gray-600 mt-2">Your achievements and earned certificates from completed courses</p>
        </div>

        <?php if (!empty($certificates)): ?>
            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-lg shadow-md p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-primary-100 text-sm font-medium">Total Certificates</p>
                            <p class="text-4xl font-bold mt-2"><?= count($certificates) ?></p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-award text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-600 to-green-800 rounded-lg shadow-md p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Courses Completed</p>
                            <p class="text-4xl font-bold mt-2"><?= count($certificates) ?></p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-check-circle text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-600 to-purple-800 rounded-lg shadow-md p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">TEVETA REGISTERED</p>
                            <p class="text-lg font-bold mt-2"><?= TEVETA_CODE ?></p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-stamp text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificates Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($certificates as $certificate): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group">
                        <!-- Certificate Preview -->
                        <div class="relative bg-gradient-to-br from-primary-50 to-secondary-50 p-6 border-b-4 border-primary-600">
                            <div class="text-center">
                                <i class="fas fa-certificate text-primary-600 text-6xl mb-4 opacity-20"></i>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <i class="fas fa-award text-primary-600 text-5xl mb-2"></i>
                                        <p class="text-xs text-gray-600 font-semibold mt-2">CERTIFICATE</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Certificate Details -->
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                                <?= sanitize($certificate['course_title']) ?>
                            </h3>

                            <div class="space-y-2 mb-4 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-user text-primary-500 w-5 mr-2"></i>
                                    <span><?= sanitize($certificate['instructor_first_name'] . ' ' . $certificate['instructor_last_name']) ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-primary-500 w-5 mr-2"></i>
                                    <span>Issued <?= formatDate($certificate['issued_at']) ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-hashtag text-primary-500 w-5 mr-2"></i>
                                    <span class="font-mono text-xs"><?= sanitize($certificate['certificate_number']) ?></span>
                                </div>
                            </div>

                            <!-- Verification Badge -->
                            <div class="flex items-center justify-between mb-4 p-3 bg-green-50 rounded-md">
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                    <span class="text-green-800 font-medium">Verified Certificate</span>
                                </div>
                                <i class="fas fa-shield-alt text-green-600"></i>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <a href="<?= url('download-certificate.php?id=' . $certificate['id']) ?>"
                                   class="flex-1 text-center py-2 px-4 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition font-medium text-sm">
                                    <i class="fas fa-download mr-1"></i>Download
                                </a>
                                <a href="<?= url('certificate-verify.php?number=' . urlencode($certificate['certificate_number'])) ?>"
                                   class="py-2 px-4 border border-primary-600 text-primary-600 rounded-md hover:bg-primary-50 transition text-sm"
                                   title="Verify Certificate">
                                    <i class="fas fa-shield-alt"></i>
                                </a>
                                <button onclick="shareCertificate('<?= sanitize($certificate['certificate_number']) ?>')"
                                        class="py-2 px-4 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm"
                                        title="Share Certificate">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Share Instructions -->
            <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-6 rounded-md">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h3 class="font-semibold text-blue-900 mb-2">Share Your Achievements</h3>
                        <p class="text-blue-800 text-sm mb-3">
                            Add your certificates to your professional profiles to showcase your skills and accomplishments.
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <span class="inline-flex items-center px-3 py-1 bg-white rounded-md text-sm">
                                <i class="fab fa-linkedin text-blue-600 mr-2"></i>LinkedIn
                            </span>
                            <span class="inline-flex items-center px-3 py-1 bg-white rounded-md text-sm">
                                <i class="fab fa-facebook text-blue-600 mr-2"></i>Facebook
                            </span>
                            <span class="inline-flex items-center px-3 py-1 bg-white rounded-md text-sm">
                                <i class="fab fa-twitter text-blue-400 mr-2"></i>Twitter
                            </span>
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
                    'Complete a course to earn your first certificate',
                    url('my-courses.php'),
                    'View My Courses'
                ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function shareCertificate(certificateNumber) {
    const verifyUrl = '<?= url('certificate-verify.php?number=') ?>' + encodeURIComponent(certificateNumber);
    const shareText = `I've earned a new certificate from Edutrack computer training college! Verify it here: ${verifyUrl}`;

    if (navigator.share) {
        navigator.share({
            title: 'My Certificate',
            text: shareText,
            url: verifyUrl
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: Copy to clipboard
        navigator.clipboard.writeText(verifyUrl).then(() => {
            alert('Certificate verification link copied to clipboard!');
        });
    }
}
</script>

<?php require_once '../src/templates/footer.php'; ?>
