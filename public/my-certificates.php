<?php
/**
 * My Certificates Page
 * Student dashboard to view all earned certificates
 */

require_once __DIR__ . '/../src/bootstrap.php';

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
           cert.verification_code, cert.is_verified, cert.expiry_date,
           cert.issued_date as issued_at,
           c.title as course_title, c.slug as course_slug,
           c.thumbnail_url, c.instructor_id,
           CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
           e.enrolled_at, e.completion_date as course_completed_at
    FROM certificates cert
    JOIN enrollments e ON cert.enrollment_id = e.id
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN users u ON i.user_id = u.id
    WHERE e.user_id = ?
    ORDER BY cert.issued_date DESC
", [$userId]);

$page_title = "My Certificates - Edutrack";
require_once __DIR__ . '/../src/templates/header.php';
?>

<div class="min-h-screen py-8" style="background-color: var(--surface-primary);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold flex items-center" style="color: var(--text-primary);">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mr-4" style="background: linear-gradient(135deg, var(--color-secondary-100), var(--surface-warm));">
                    <i class="fas fa-certificate text-xl" style="color: var(--accent-secondary-hover);"></i>
                </div>
                My Certificates
            </h1>
            <p class="mt-2" style="color: var(--text-muted);">Your achievements and earned certificates from completed courses</p>
        </div>

        <?php if (!empty($certificates)): ?>
            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium" style="color: var(--text-muted);">Total Certificates</p>
                            <p class="text-4xl font-bold mt-2" style="color: var(--text-primary);"><?= count($certificates) ?></p>
                        </div>
                        <div class="stat-card-icon" style="background: linear-gradient(135deg, var(--color-primary-100), var(--color-primary-200)); color: var(--accent-primary);">
                            <i class="fas fa-award text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium" style="color: var(--text-muted);">Courses Completed</p>
                            <p class="text-4xl font-bold mt-2" style="color: var(--text-primary);"><?= count($certificates) ?></p>
                        </div>
                        <div class="stat-card-icon" style="background: linear-gradient(135deg, var(--surface-success), #D1FAE5); color: var(--status-success);">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium" style="color: var(--text-muted);">TEVETA REGISTERED</p>
                            <p class="text-lg font-bold mt-2" style="color: var(--text-primary);"><?= TEVETA_CODE ?></p>
                        </div>
                        <div class="stat-card-icon" style="background: linear-gradient(135deg, var(--surface-warning), #FDE68A); color: #B45309;">
                            <i class="fas fa-stamp text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificates Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($certificates as $certificate): ?>
                    <div class="overflow-hidden card-hover" style="background: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); box-shadow: var(--shadow-card);">
                        <!-- Certificate Preview -->
                        <div class="relative p-6 border-b" style="background: linear-gradient(135deg, var(--surface-warm) 0%, var(--color-secondary-50) 50%, var(--surface-warm) 100%); border-color: var(--border-primary);">
                            <div class="text-center">
                                <i class="fas fa-certificate text-6xl mb-4 opacity-20" style="color: var(--accent-secondary);"></i>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <i class="fas fa-award text-5xl mb-2" style="color: var(--accent-secondary-hover);"></i>
                                        <p class="text-xs font-semibold mt-2" style="color: var(--text-secondary);">CERTIFICATE</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Certificate Details -->
                        <div class="p-6">
                            <h3 class="text-lg font-bold mb-2 line-clamp-2" style="color: var(--text-primary);">
                                <?= sanitize($certificate['course_title']) ?>
                            </h3>

                            <div class="space-y-2 mb-4 text-sm" style="color: var(--text-muted);">
                                <div class="flex items-center">
                                    <i class="fas fa-user w-5 mr-2" style="color: var(--accent-primary);"></i>
                                    <span><?= sanitize($certificate['instructor_name']) ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar w-5 mr-2" style="color: var(--accent-primary);"></i>
                                    <span>Issued <?= formatDate($certificate['issued_at']) ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-hashtag w-5 mr-2" style="color: var(--accent-primary);"></i>
                                    <span class="font-mono text-xs"><?= sanitize($certificate['certificate_number']) ?></span>
                                </div>
                            </div>

                            <!-- Verification Badge -->
                            <div class="flex items-center justify-between mb-4 p-3 rounded-lg" style="background: var(--surface-success);">
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-check-circle mr-2" style="color: var(--status-success);"></i>
                                    <span class="font-medium" style="color: #065F46;">Verified Certificate</span>
                                </div>
                                <i class="fas fa-shield-alt" style="color: var(--status-success);"></i>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <a href="<?= url('download-certificate.php?id=' . $certificate['id']) ?>"
                                   class="btn-primary flex-1 text-center text-sm">
                                    <i class="fas fa-download mr-1"></i>Download
                                </a>
                                <a href="<?= url('certificate-verify.php?code=' . urlencode($certificate['verification_code'])) ?>"
                                   class="py-2 px-4 text-sm font-medium transition"
                                   style="border: 1px solid var(--accent-primary); color: var(--accent-primary); border-radius: var(--radius-lg);"
                                   title="Verify Certificate">
                                    <i class="fas fa-shield-alt"></i>
                                </a>
                                <button onclick="shareCertificate('<?= sanitize($certificate['certificate_number']) ?>')"
                                        class="py-2 px-4 text-sm font-medium transition"
                                        style="border: 1px solid var(--border-primary); color: var(--text-secondary); border-radius: var(--radius-lg);"
                                        title="Share Certificate">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Share Instructions -->
            <div class="mt-8 p-6 rounded-xl" style="background: var(--surface-info); border: 1px solid var(--color-primary-200);">
                <div class="flex items-start">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-4 flex-shrink-0" style="background: var(--color-primary-100);">
                        <i class="fas fa-info-circle text-lg" style="color: var(--accent-primary);"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-2" style="color: var(--color-primary-900);">Share Your Achievements</h3>
                        <p class="text-sm mb-3" style="color: var(--color-primary-800);">
                            Add your certificates to your professional profiles to showcase your skills and accomplishments.
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium" style="background: var(--surface-secondary); color: var(--text-secondary); border: 1px solid var(--border-primary);">
                                <i class="fab fa-linkedin mr-2" style="color: #0077B5;"></i>LinkedIn
                            </span>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium" style="background: var(--surface-secondary); color: var(--text-secondary); border: 1px solid var(--border-primary);">
                                <i class="fab fa-facebook mr-2" style="color: #1877F2;"></i>Facebook
                            </span>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium" style="background: var(--surface-secondary); color: var(--text-secondary); border: 1px solid var(--border-primary);">
                                <i class="fab fa-twitter mr-2" style="color: #1DA1F2;"></i>Twitter
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state" style="background: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); box-shadow: var(--shadow-card);">
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

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
