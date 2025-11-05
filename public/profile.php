<?php
/**
 * Edutrack computer training college
 * View Profile Page
 */

require_once '../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user
$user = User::current();
$userId = $user->getId();

// Get user statistics from Statistics class
$studentStats = Statistics::getStudentStats($userId);

$stats = [
    'active_courses' => $studentStats['in_progress_courses'],
    'completed_courses' => $studentStats['completed_courses'],
    'total_courses' => $studentStats['enrolled_courses'],
    'certificates' => $studentStats['total_certificates'],
    'avg_quiz_score' => $studentStats['avg_quiz_score'],
    'member_since' => formatDate($user->created_at, 'F Y')
];

$page_title = "My Profile - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Profile Header -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <!-- Cover Image -->
            <div class="bg-gradient-to-r from-primary-600 via-primary-700 to-primary-800 h-32 sm:h-48"></div>
            
            <!-- Profile Info -->
            <div class="px-6 pb-6">
                <div class="sm:flex sm:items-end sm:space-x-5">
                    <div class="flex -mt-12 sm:-mt-16">
                        <img src="<?= $user->getAvatarUrl() ?>" 
                             alt="Profile Picture"
                             class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-white shadow-lg bg-white">
                    </div>
                    <div class="mt-6 sm:flex-1 sm:min-w-0 sm:flex sm:items-center sm:justify-end sm:space-x-6 sm:pb-1">
                        <div class="flex-1 min-w-0 mt-6 sm:mt-0">
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 truncate">
                                <?= sanitize($user->getFullName()) ?>
                            </h1>
                            <p class="text-gray-600 flex items-center mt-2">
                                <i class="fas fa-envelope mr-2"></i>
                                <?= sanitize($user->email) ?>
                            </p>
                            <?php if ($user->phone): ?>
                                <p class="text-gray-600 flex items-center mt-1">
                                    <i class="fas fa-phone mr-2"></i>
                                    <?= sanitize($user->phone) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 mt-6 sm:mt-0">
                            <a href="<?= url('edit-profile.php') ?>" 
                               class="btn-primary px-6 py-2 rounded-md text-center">
                                <i class="fas fa-edit mr-2"></i>Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <i class="fas fa-book text-primary-600 text-3xl mb-2"></i>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['active_courses'] ?></p>
                <p class="text-sm text-gray-600">Active Courses</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <i class="fas fa-check-circle text-green-600 text-3xl mb-2"></i>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['completed_courses'] ?></p>
                <p class="text-sm text-gray-600">Completed</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <i class="fas fa-certificate text-purple-600 text-3xl mb-2"></i>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['certificates'] ?></p>
                <p class="text-sm text-gray-600">Certificates</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <i class="fas fa-question-circle text-blue-600 text-3xl mb-2"></i>
                <p class="text-2xl font-bold text-gray-900">
                    <?= round($stats['avg_quiz_score']) ?>%
                </p>
                <p class="text-sm text-gray-600">Quiz Average</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- About Section -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">About Me</h2>
                    </div>
                    <div class="p-6">
                        <?php if ($user->bio): ?>
                            <p class="text-gray-700 leading-relaxed"><?= nl2br(sanitize($user->bio)) ?></p>
                        <?php else: ?>
                            <p class="text-gray-500 italic">No bio added yet.</p>
                            <a href="<?= url('edit-profile.php') ?>" class="text-primary-600 hover:text-primary-700 text-sm mt-2 inline-block">
                                Add your bio →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Personal Information -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Personal Information</h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php if ($user->date_of_birth): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                                <dd class="mt-1 text-gray-900"><?= formatDate($user->date_of_birth) ?></dd>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user->gender): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                <dd class="mt-1 text-gray-900"><?= ucfirst(sanitize($user->gender)) ?></dd>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user->phone): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-gray-900"><?= sanitize($user->phone) ?></dd>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user->nrc_number): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">NRC Number</dt>
                                <dd class="mt-1 text-gray-900"><?= sanitize($user->nrc_number) ?></dd>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user->education_level): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Education Level</dt>
                                <dd class="mt-1 text-gray-900"><?= sanitize($user->education_level) ?></dd>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user->occupation): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Occupation</dt>
                                <dd class="mt-1 text-gray-900"><?= sanitize($user->occupation) ?></dd>
                            </div>
                            <?php endif; ?>
                        </dl>
                        
                        <?php if (!$user->date_of_birth && !$user->gender && !$user->nrc_number): ?>
                            <p class="text-gray-500 italic">No personal information added yet.</p>
                            <a href="<?= url('edit-profile.php') ?>" class="text-primary-600 hover:text-primary-700 text-sm mt-2 inline-block">
                                Complete your profile →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Location -->
                <?php if ($user->address || $user->city || $user->province): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Location</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-primary-600 text-xl mr-3 mt-1"></i>
                            <div>
                                <?php if ($user->address): ?>
                                    <p class="text-gray-900"><?= sanitize($user->address) ?></p>
                                <?php endif; ?>
                                <p class="text-gray-600">
                                    <?= sanitize($user->city ?? '') ?>
                                    <?= $user->city && $user->province ? ', ' : '' ?>
                                    <?= sanitize($user->province ?? '') ?>
                                </p>
                                <p class="text-gray-600"><?= sanitize($user->country ?? 'Zambia') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Account Details -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Account Details</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Account Type</p>
                            <p class="font-semibold text-gray-900 capitalize"><?= sanitize($user->role) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Member Since</p>
                            <p class="font-semibold text-gray-900"><?= $stats['member_since'] ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email Status</p>
                            <p class="font-semibold">
                                <?php if ($user->isEmailVerified()): ?>
                                    <span class="text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i>Verified
                                    </span>
                                <?php else: ?>
                                    <span class="text-yellow-600">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Not Verified
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Account Status</p>
                            <p class="font-semibold">
                                <?php if ($user->isActive()): ?>
                                    <span class="text-green-600">
                                        <i class="fas fa-circle mr-1 text-xs"></i>Active
                                    </span>
                                <?php else: ?>
                                    <span class="text-red-600">
                                        <i class="fas fa-circle mr-1 text-xs"></i>Inactive
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- TEVETA Badge -->
                <div class="bg-gradient-to-br from-primary-600 to-primary-800 rounded-lg shadow-md p-6 text-center text-white">
                    <i class="fas fa-certificate text-secondary-500 text-4xl mb-3"></i>
                    <h3 class="font-bold text-lg mb-2">TEVETA Certified</h3>
                    <p class="text-sm text-primary-100 mb-4">
                        You're learning with a government-registered institution
                    </p>
                    <p class="text-xs font-mono bg-white bg-opacity-20 rounded px-3 py-2 inline-block">
                        <?= TEVETA_CODE ?>
                    </p>
                </div>
                
                <!-- Social Links -->
                <?php if ($user->linkedin_url || $user->facebook_url || $user->twitter_url): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Social Links</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <?php if ($user->linkedin_url): ?>
                        <a href="<?= sanitize($user->linkedin_url) ?>" target="_blank" 
                           class="flex items-center text-gray-700 hover:text-primary-600 transition">
                            <i class="fab fa-linkedin text-xl mr-3 w-8"></i>
                            <span>LinkedIn</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($user->facebook_url): ?>
                        <a href="<?= sanitize($user->facebook_url) ?>" target="_blank" 
                           class="flex items-center text-gray-700 hover:text-primary-600 transition">
                            <i class="fab fa-facebook text-xl mr-3 w-8"></i>
                            <span>Facebook</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($user->twitter_url): ?>
                        <a href="<?= sanitize($user->twitter_url) ?>" target="_blank" 
                           class="flex items-center text-gray-700 hover:text-primary-600 transition">
                            <i class="fab fa-twitter text-xl mr-3 w-8"></i>
                            <span>Twitter</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>