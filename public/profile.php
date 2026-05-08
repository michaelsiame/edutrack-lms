<?php
/**
 * Edutrack computer training college
 * View Profile Page
 */

require_once __DIR__ . '/../src/bootstrap.php';

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
    'active_courses' => $studentStats['in_progress_courses'] ?? 0,
    'completed_courses' => $studentStats['completed_courses'] ?? 0,
    'total_courses' => $studentStats['enrolled_courses'] ?? 0,
    'certificates' => $studentStats['total_certificates'] ?? 0,
    'avg_quiz_score' => $studentStats['avg_quiz_score'] ?? 0,
    'member_since' => formatDate($user->created_at ?? null, 'F Y')
];

$page_title = "My Profile - Edutrack";
require_once __DIR__ . '/../src/templates/header.php';
?>

<div class="min-h-screen py-8" style="background-color: var(--surface-primary);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Profile Header -->
        <div class="overflow-hidden mb-6 card-hover" style="background-color: var(--surface-secondary); border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);">
            <!-- Cover Image -->
            <div class="h-32 sm:h-48" style="background: linear-gradient(135deg, var(--accent-primary) 0%, #1E5AB8 100%);"></div>
            
            <!-- Profile Info -->
            <div class="px-6 pb-6">
                <div class="sm:flex sm:items-end sm:space-x-5">
                    <div class="flex -mt-12 sm:-mt-16">
                        <img src="<?= $user->getAvatarUrl() ?>" 
                             alt="Profile Picture"
                             class="w-24 h-24 sm:w-32 sm:h-32 rounded-full shadow-lg bg-white"
                             style="border: 4px solid var(--accent-secondary);">
                    </div>
                    <div class="mt-6 sm:flex-1 sm:min-w-0 sm:flex sm:items-center sm:justify-end sm:space-x-6 sm:pb-1">
                        <div class="flex-1 min-w-0 mt-6 sm:mt-0">
                            <h1 class="text-2xl sm:text-3xl font-bold truncate" style="color: var(--text-primary);">
                                <?= sanitize($user->getFullName()) ?>
                            </h1>
                            <p class="flex items-center mt-2" style="color: var(--text-secondary);">
                                <i class="fas fa-envelope mr-2" style="color: var(--accent-primary);"></i>
                                <?= sanitize($user->email) ?>
                            </p>
                            <?php if ($user->phone): ?>
                                <p class="flex items-center mt-1" style="color: var(--text-secondary);">
                                    <i class="fas fa-phone mr-2" style="color: var(--accent-primary);"></i>
                                    <?= sanitize($user->phone) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 mt-6 sm:mt-0">
                            <a href="<?= url('edit-profile.php') ?>" 
                               class="px-6 py-2 rounded-lg text-center font-medium transition-all duration-200"
                               style="background-color: var(--accent-primary); color: #fff; border-radius: var(--radius-lg);">
                                <i class="fas fa-edit mr-2"></i>Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-card p-4 text-center">
                <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center" style="background-color: rgba(45,95,171,0.12);">
                    <i class="fas fa-book text-xl" style="color: var(--accent-primary);"></i>
                </div>
                <p class="text-2xl font-bold" style="color: var(--text-primary);"><?= $stats['active_courses'] ?></p>
                <p class="text-sm" style="color: var(--text-secondary);">Active Courses</p>
            </div>
            <div class="stat-card p-4 text-center">
                <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center" style="background-color: rgba(34,128,76,0.12);">
                    <i class="fas fa-check-circle text-xl" style="color: var(--status-success);"></i>
                </div>
                <p class="text-2xl font-bold" style="color: var(--text-primary);"><?= $stats['completed_courses'] ?></p>
                <p class="text-sm" style="color: var(--text-secondary);">Completed</p>
            </div>
            <div class="stat-card p-4 text-center">
                <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center" style="background-color: rgba(180,83,9,0.12);">
                    <i class="fas fa-certificate text-xl" style="color: #B45309;"></i>
                </div>
                <p class="text-2xl font-bold" style="color: var(--text-primary);"><?= $stats['certificates'] ?></p>
                <p class="text-sm" style="color: var(--text-secondary);">Certificates</p>
            </div>
            <div class="stat-card p-4 text-center">
                <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center" style="background-color: rgba(107,114,128,0.12);">
                    <i class="fas fa-question-circle text-xl" style="color: #6B7280;"></i>
                </div>
                <p class="text-2xl font-bold" style="color: var(--text-primary);">
                    <?= round($stats['avg_quiz_score']) ?>%
                </p>
                <p class="text-sm" style="color: var(--text-secondary);">Quiz Average</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- About Section -->
            <div class="lg:col-span-2 space-y-6">
                <div class="overflow-hidden card-hover" style="background-color: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl);">
                    <div class="px-6 py-4" style="border-bottom: 1px solid var(--border-primary);">
                        <h2 class="text-xl font-bold" style="color: var(--text-primary);">About Me</h2>
                    </div>
                    <div class="p-6">
                        <?php if ($user->bio): ?>
                            <p class="leading-relaxed" style="color: var(--text-primary);"><?= nl2br(sanitize($user->bio)) ?></p>
                        <?php else: ?>
                            <p style="color: var(--text-muted);">No bio added yet.</p>
                            <a href="<?= url('edit-profile.php') ?>" class="text-sm mt-2 inline-block transition-colors duration-200" style="color: var(--accent-primary);">
                                Add your bio →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Personal Information -->
                <div class="overflow-hidden card-hover" style="background-color: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl);">
                    <div class="px-6 py-4" style="border-bottom: 1px solid var(--border-primary);">
                        <h2 class="text-xl font-bold" style="color: var(--text-primary);">Personal Information</h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php if ($user->date_of_birth): ?>
                            <div>
                                <dt class="text-sm font-medium" style="color: var(--text-secondary);">Date of Birth</dt>
                                <dd class="mt-1" style="color: var(--text-primary);"><?= formatDate($user->date_of_birth) ?></dd>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user->gender): ?>
                            <div>
                                <dt class="text-sm font-medium" style="color: var(--text-secondary);">Gender</dt>
                                <dd class="mt-1" style="color: var(--text-primary);"><?= ucfirst(sanitize($user->gender)) ?></dd>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user->phone): ?>
                            <div>
                                <dt class="text-sm font-medium" style="color: var(--text-secondary);">Phone</dt>
                                <dd class="mt-1" style="color: var(--text-primary);"><?= sanitize($user->phone) ?></dd>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user->nrc_number): ?>
                            <div>
                                <dt class="text-sm font-medium" style="color: var(--text-secondary);">NRC Number</dt>
                                <dd class="mt-1" style="color: var(--text-primary);"><?= sanitize($user->nrc_number) ?></dd>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user->education_level): ?>
                            <div>
                                <dt class="text-sm font-medium" style="color: var(--text-secondary);">Education Level</dt>
                                <dd class="mt-1" style="color: var(--text-primary);"><?= sanitize($user->education_level) ?></dd>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user->occupation): ?>
                            <div>
                                <dt class="text-sm font-medium" style="color: var(--text-secondary);">Occupation</dt>
                                <dd class="mt-1" style="color: var(--text-primary);"><?= sanitize($user->occupation) ?></dd>
                            </div>
                            <?php endif; ?>
                        </dl>
                        
                        <?php if (!$user->date_of_birth && !$user->gender && !$user->nrc_number): ?>
                            <p style="color: var(--text-muted);">No personal information added yet.</p>
                            <a href="<?= url('edit-profile.php') ?>" class="text-sm mt-2 inline-block transition-colors duration-200" style="color: var(--accent-primary);">
                                Complete your profile →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Location -->
                <?php if ($user->address || $user->city || $user->province): ?>
                <div class="overflow-hidden card-hover" style="background-color: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl);">
                    <div class="px-6 py-4" style="border-bottom: 1px solid var(--border-primary);">
                        <h2 class="text-xl font-bold" style="color: var(--text-primary);">Location</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0" style="background-color: rgba(45,95,171,0.10);">
                                <i class="fas fa-map-marker-alt" style="color: var(--accent-primary);"></i>
                            </div>
                            <div>
                                <?php if ($user->address): ?>
                                    <p style="color: var(--text-primary);"><?= sanitize($user->address) ?></p>
                                <?php endif; ?>
                                <p style="color: var(--text-secondary);">
                                    <?= sanitize($user->city ?? '') ?>
                                    <?= $user->city && $user->province ? ', ' : '' ?>
                                    <?= sanitize($user->province ?? '') ?>
                                </p>
                                <p style="color: var(--text-secondary);"><?= sanitize($user->country ?? 'Zambia') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Account Details -->
                <div class="overflow-hidden" style="background-color: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); box-shadow: var(--shadow-card);">
                    <div class="px-6 py-4" style="border-bottom: 1px solid var(--border-primary);">
                        <h2 class="text-lg font-bold" style="color: var(--text-primary);">Account Details</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-sm" style="color: var(--text-muted);">Account Type</p>
                            <p class="font-semibold capitalize" style="color: var(--text-primary);"><?= sanitize($user->role) ?></p>
                        </div>
                        <div>
                            <p class="text-sm" style="color: var(--text-muted);">Member Since</p>
                            <p class="font-semibold" style="color: var(--text-primary);"><?= $stats['member_since'] ?></p>
                        </div>
                        <div>
                            <p class="text-sm" style="color: var(--text-muted);">Email Status</p>
                            <p class="font-semibold">
                                <?php if ($user->isEmailVerified()): ?>
                                    <span style="color: var(--status-success);">
                                        <i class="fas fa-check-circle mr-1"></i>Verified
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--status-warning);">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Not Verified
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm" style="color: var(--text-muted);">Account Status</p>
                            <p class="font-semibold">
                                <?php if ($user->isActive()): ?>
                                    <span style="color: var(--status-success);">
                                        <i class="fas fa-circle mr-1 text-xs"></i>Active
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--status-error);">
                                        <i class="fas fa-circle mr-1 text-xs"></i>Inactive
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- TEVETA Badge -->
                <div class="p-6 text-center text-white" style="background: linear-gradient(135deg, #1a4a8a 0%, #2D5FAB 50%, #4A7FC6 100%); border-radius: var(--radius-xl); box-shadow: var(--shadow-card);">
                    <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center" style="background-color: rgba(255,255,255,0.15); backdrop-filter: blur(4px);">
                        <i class="fas fa-certificate text-2xl" style="color: #F5E6C8;"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2 tracking-wide" style="font-family: Georgia, 'Times New Roman', serif; letter-spacing: 0.05em;">TEVETA REGISTERED</h3>
                    <p class="text-sm mb-4" style="color: rgba(255,255,255,0.85);">
                        You're learning with a government-registered institution
                    </p>
                    <p class="text-xs font-mono rounded-lg px-3 py-2 inline-block" style="background-color: rgba(255,255,255,0.15); color: #F5E6C8;">
                        <?= TEVETA_CODE ?>
                    </p>
                </div>
                
                <!-- Social Links -->
                <?php if ($user->linkedin_url || $user->facebook_url || $user->twitter_url): ?>
                <div class="overflow-hidden" style="background-color: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); box-shadow: var(--shadow-card);">
                    <div class="px-6 py-4" style="border-bottom: 1px solid var(--border-primary);">
                        <h2 class="text-lg font-bold" style="color: var(--text-primary);">Social Links</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <?php if ($user->linkedin_url): ?>
                        <a href="<?= sanitize($user->linkedin_url) ?>" target="_blank" 
                           class="flex items-center transition-colors duration-200 group"
                           style="color: var(--text-secondary);">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center mr-3 transition-colors duration-200" style="background-color: rgba(45,95,171,0.10); color: var(--accent-primary);">
                                <i class="fab fa-linkedin"></i>
                            </div>
                            <span class="group-hover:underline" style="color: var(--text-primary);">LinkedIn</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($user->facebook_url): ?>
                        <a href="<?= sanitize($user->facebook_url) ?>" target="_blank" 
                           class="flex items-center transition-colors duration-200 group"
                           style="color: var(--text-secondary);">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center mr-3 transition-colors duration-200" style="background-color: rgba(45,95,171,0.10); color: var(--accent-primary);">
                                <i class="fab fa-facebook"></i>
                            </div>
                            <span class="group-hover:underline" style="color: var(--text-primary);">Facebook</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($user->twitter_url): ?>
                        <a href="<?= sanitize($user->twitter_url) ?>" target="_blank" 
                           class="flex items-center transition-colors duration-200 group"
                           style="color: var(--text-secondary);">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center mr-3 transition-colors duration-200" style="background-color: rgba(45,95,171,0.10); color: var(--accent-primary);">
                                <i class="fab fa-twitter"></i>
                            </div>
                            <span class="group-hover:underline" style="color: var(--text-primary);">Twitter</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
