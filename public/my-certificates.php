<?php
/**
 * My Certificates Page
 * Student dashboard to view all earned certificates
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/functions.php';
require_once '../src/classes/Certificate.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Get user's certificates
$certificates = Certificate::getUserCertificates($_SESSION['user_id']);

$page_title = 'My Certificates';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-certificate text-indigo-600"></i>
                        My Certificates
                    </h1>
                    <p class="text-gray-600">Your earned certificates and achievements</p>
                </div>
                <div class="flex gap-3">
                    <a href="dashboard.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Dashboard
                    </a>
                </div>
            </div>
        </div>

        <?php if (empty($certificates)): ?>
        
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="bg-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-certificate text-5xl text-gray-400"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">No Certificates Yet</h2>
                <p class="text-gray-600 mb-6">
                    Complete courses to earn certificates and showcase your skills!
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="courses.php" class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                        <i class="fas fa-book mr-2"></i>
                        Browse Courses
                    </a>
                    <a href="my-courses.php" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        My Courses
                    </a>
                </div>
            </div>
        </div>

        <?php else: ?>

        <!-- Stats -->
        <div class="grid sm:grid-cols-3 gap-6 mb-8">
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Certificates</p>
                        <p class="text-3xl font-bold text-indigo-600"><?= count($certificates) ?></p>
                    </div>
                    <div class="bg-indigo-100 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-certificate text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">This Month</p>
                        <p class="text-3xl font-bold text-green-600">
                            <?php 
                            $thisMonth = count(array_filter($certificates, function($cert) {
                                return date('Y-m', strtotime($cert['issue_date'])) === date('Y-m');
                            }));
                            echo $thisMonth;
                            ?>
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Latest Certificate</p>
                        <p class="text-lg font-bold text-purple-600">
                            <?= date('M d', strtotime($certificates[0]['issue_date'])) ?>
                        </p>
                    </div>
                    <div class="bg-purple-100 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-award text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- Certificates Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <?php foreach ($certificates as $cert): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition group">
                
                <!-- Certificate Preview -->
                <div class="relative bg-gradient-to-br from-indigo-500 to-purple-600 p-6 text-white aspect-video flex items-center justify-center">
                    <div class="text-center">
                        <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-award text-3xl"></i>
                        </div>
                        <p class="text-sm opacity-90">Certificate of Completion</p>
                    </div>
                    
                    <!-- Status Badge -->
                    <div class="absolute top-3 right-3">
                        <span class="inline-flex items-center px-2 py-1 bg-green-500 bg-opacity-90 rounded-full text-xs font-semibold">
                            <i class="fas fa-check-circle mr-1"></i>
                            Issued
                        </span>
                    </div>
                </div>

                <!-- Certificate Info -->
                <div class="p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-3 line-clamp-2">
                        <?= htmlspecialchars($cert['course_title']) ?>
                    </h3>
                    
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt w-5 text-indigo-600"></i>
                            <span><?= date('F d, Y', strtotime($cert['issue_date'])) ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-hashtag w-5 text-indigo-600"></i>
                            <span class="font-mono text-xs"><?= htmlspecialchars($cert['certificate_number']) ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-shield-alt w-5 text-indigo-600"></i>
                            <span class="font-mono text-xs"><?= htmlspecialchars($cert['verification_code']) ?></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <a href="download-certificate.php?id=<?= $cert['id'] ?>" 
                           class="flex-1 text-center py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                            <i class="fas fa-download"></i>
                            Download
                        </a>
                        <a href="verify-certificate.php?code=<?= htmlspecialchars($cert['verification_code']) ?>" 
                           target="_blank"
                           class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                           title="Verify">
                            <i class="fas fa-check-circle text-gray-600"></i>
                        </a>
                        <button onclick="shareCertificate('<?= htmlspecialchars($cert['verification_code']) ?>', '<?= htmlspecialchars($cert['course_title'], ENT_QUOTES) ?>')" 
                                class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                                title="Share">
                            <i class="fas fa-share-alt text-gray-600"></i>
                        </button>
                    </div>

                </div>

            </div>
            <?php endforeach; ?>

        </div>

        <!-- Tips Section -->
        <div class="mt-12 bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                Make the Most of Your Certificates
            </h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="font-bold text-gray-800 mb-2">Add to LinkedIn</h3>
                    <p class="text-sm text-gray-600">
                        Showcase your achievements on your professional profile
                    </p>
                </div>

                <div class="border-l-4 border-green-500 pl-4">
                    <h3 class="font-bold text-gray-800 mb-2">Include in Resume</h3>
                    <p class="text-sm text-gray-600">
                        Add certificates to your CV under certifications section
                    </p>
                </div>

                <div class="border-l-4 border-purple-500 pl-4">
                    <h3 class="font-bold text-gray-800 mb-2">Share with Employers</h3>
                    <p class="text-sm text-gray-600">
                        Send verification links to potential employers
                    </p>
                </div>

                <div class="border-l-4 border-orange-500 pl-4">
                    <h3 class="font-bold text-gray-800 mb-2">Keep Learning</h3>
                    <p class="text-sm text-gray-600">
                        Complete more courses to build your portfolio
                    </p>
                </div>

            </div>
        </div>

        <?php endif; ?>

    </div>
</div>

<script>
function shareCertificate(verificationCode, courseTitle) {
    const url = '<?= SITE_URL ?>/verify-certificate.php?code=' + verificationCode;
    const text = `I just earned my certificate for "${courseTitle}"! 🎉`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Certificate of Completion',
            text: text,
            url: url
        }).catch(console.error);
    } else {
        // Fallback - show share options
        const shareModal = document.createElement('div');
        shareModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        shareModal.innerHTML = `
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold mb-4">Share Certificate</h3>
                <div class="space-y-3">
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}" 
                       target="_blank"
                       class="flex items-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fab fa-linkedin mr-3"></i>
                        Share on LinkedIn
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}" 
                       target="_blank"
                       class="flex items-center px-4 py-3 bg-sky-500 text-white rounded-lg hover:bg-sky-600">
                        <i class="fab fa-twitter mr-3"></i>
                        Share on Twitter
                    </a>
                    <button onclick="copyToClipboard('${url}')" 
                            class="w-full flex items-center px-4 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-link mr-3"></i>
                        Copy Link
                    </button>
                </div>
                <button onclick="this.closest('.fixed').remove()" 
                        class="mt-4 w-full py-2 text-gray-600 hover:text-gray-800">
                    Close
                </button>
            </div>
        `;
        document.body.appendChild(shareModal);
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Link copied to clipboard!');
    });
}
</script>

<?php require_once '../src/templates/footer.php'; ?>