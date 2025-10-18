<?php
/**
 * Edutrack Computer Training College
 * Student Dashboard
 */

require_once '../src/middleware/authenticate.php';
require_once '../src/classes/User.php';

// Get current user
$user = User::current();

// Get dashboard statistics
$stats = [
    'active_courses' => $user->getActiveEnrollmentsCount(),
    'completed_courses' => $user->getCompletedCoursesCount(),
    'total_time' => $user->getTotalTimeSpent(),
    'certificates' => count($user->getCertificates())
];

// Get recent enrollments
$recentEnrollments = $user->getEnrollments();
$recentEnrollments = array_slice($recentEnrollments, 0, 4);

// Get upcoming deadlines (assignments/quizzes)
try {
    $upcomingDeadlines = $db->fetchAll("
        SELECT a.*, c.title as course_title, c.slug as course_slug
        FROM assignments a
        JOIN courses c ON a.course_id = c.id
        JOIN enrollments e ON e.course_id = c.id
        WHERE e.user_id = ? 
        AND a.status = 'published'
        AND a.due_date > NOW()
        AND a.due_date < DATE_ADD(NOW(), INTERVAL 7 DAY)
        AND a.id NOT IN (
            SELECT assignment_id FROM assignment_submissions WHERE user_id = ?
        )
        ORDER BY a.due_date ASC
        LIMIT 5
    ", [$user->getId(), $user->getId()]);
} catch (Exception $e) {
    $upcomingDeadlines = [];
}

// Get recent activity
$recentActivity = $user->getRecentActivity(5);

$page_title = "Dashboard - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, <?= sanitize($user->first_name) ?>! 👋</h1>
            <p class="text-gray-600 mt-2">Here's what's happening with your learning journey today.</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Active Courses -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-primary-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Courses</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['active_courses'] ?></p>
                    </div>
                    <div class="bg-primary-100 rounded-full p-3">
                        <i class="fas fa-book text-primary-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= url('my-courses.php') ?>" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                        View all courses →
                    </a>
                </div>
            </div>
            
            <!-- Completed Courses -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Completed</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['completed_courses'] ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= url('my-courses.php?status=completed') ?>" class="text-sm text-green-600 hover:text-green-700 font-medium">
                        View completed →
                    </a>
                </div>
            </div>
            
            <!-- Learning Time -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-secondary-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Learning Time</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            <?= $stats['total_time'] > 60 ? round($stats['total_time'] / 60, 1) . 'h' : $stats['total_time'] . 'm' ?>
                        </p>
                    </div>
                    <div class="bg-secondary-100 rounded-full p-3">
                        <i class="fas fa-clock text-secondary-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-gray-500">Keep learning!</span>
                </div>
            </div>
            
            <!-- Certificates -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Certificates</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['certificates'] ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-certificate text-purple-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= url('my-certificates.php') ?>" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                        View certificates →
                    </a>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Continue Learning -->
                <?php if (!empty($recentEnrollments)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">Continue Learning</h2>
                        <a href="<?= url('my-courses.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">
                            View All
                        </a>
                    </div>
                    <div class="p-6 space-y-4">
                        <?php foreach ($recentEnrollments as $enrollment): ?>
                            <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg hover:border-primary-300 transition">
                                <img src="<?= courseThumbnail($enrollment['thumbnail']) ?>" 
                                     alt="<?= sanitize($enrollment['title']) ?>"
                                     class="w-20 h-20 object-cover rounded">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 mb-1">
                                        <?= sanitize($enrollment['title']) ?>
                                    </h3>
                                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                                        <span>
                                            <i class="fas fa-chart-line text-primary-500 mr-1"></i>
                                            <?= round($enrollment['progress_percentage']) ?>% complete
                                        </span>
                                        <?php if ($enrollment['last_accessed']): ?>
                                            <span>
                                                <i class="fas fa-clock text-gray-400 mr-1"></i>
                                                <?= timeAgo($enrollment['last_accessed']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-primary-600 h-2 rounded-full" 
                                             style="width: <?= round($enrollment['progress_percentage']) ?>%"></div>
                                    </div>
                                </div>
                                <a href="<?= url('learn.php?course=' . $enrollment['slug']) ?>" 
                                   class="btn-primary px-4 py-2 rounded-md whitespace-nowrap">
                                    Continue
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-lg shadow-md p-8">
                    <?php emptyState(
                        'fa-book-open',
                        'No Active Courses',
                        'Start your learning journey by enrolling in a course',
                        url('courses.php'),
                        'Browse Courses'
                    ); ?>
                </div>
                <?php endif; ?>
                
                <!-- Upcoming Deadlines -->
                <?php if (!empty($upcomingDeadlines)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Upcoming Deadlines</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($upcomingDeadlines as $deadline): ?>
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">
                                            <?= sanitize($deadline['title']) ?>
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            <?= sanitize($deadline['course_title']) ?>
                                        </p>
                                    </div>
                                    <div class="text-right ml-4">
                                        <p class="text-sm font-medium text-red-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Due <?= formatDate($deadline['due_date'], 'M j') ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?= timeAgo($deadline['due_date']) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Profile Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-primary-600 to-primary-800 h-24"></div>
                    <div class="px-6 pb-6">
                        <div class="-mt-12 mb-4">
                            <img src="<?= $user->getAvatarUrl() ?>" 
                                 alt="Avatar"
                                 class="w-24 h-24 rounded-full border-4 border-white shadow-lg">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900"><?= sanitize($user->getFullName()) ?></h3>
                        <p class="text-gray-600 text-sm"><?= sanitize($user->email) ?></p>
                        <div class="mt-2">
                            <?php teveta_badge(); ?>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <a href="<?= url('profile.php') ?>" 
                               class="block text-center py-2 px-4 bg-primary-50 text-primary-600 rounded-md hover:bg-primary-100 transition font-medium">
                                <i class="fas fa-user mr-2"></i>View Profile
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Quick Links</h2>
                    </div>
                    <div class="p-4 space-y-2">
                        <a href="<?= url('courses.php') ?>" 
                           class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-book text-primary-600 w-8"></i>
                            <span class="text-gray-700">Browse Courses</span>
                        </a>
                        <a href="<?= url('my-courses.php') ?>" 
                           class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-graduation-cap text-green-600 w-8"></i>
                            <span class="text-gray-700">My Courses</span>
                        </a>
                        <a href="<?= url('my-certificates.php') ?>" 
                           class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-certificate text-purple-600 w-8"></i>
                            <span class="text-gray-700">My Certificates</span>
                        </a>
                        <a href="<?= url('edit-profile.php') ?>" 
                           class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-cog text-gray-600 w-8"></i>
                            <span class="text-gray-700">Settings</span>
                        </a>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <?php if (!empty($recentActivity)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Recent Activity</h2>
                    </div>
                    <div class="p-4 space-y-3">
                        <?php foreach (array_slice($recentActivity, 0, 5) as $activity): ?>
                            <div class="flex items-start space-x-3 text-sm">
                                <i class="fas fa-circle text-primary-600 text-xs mt-1"></i>
                                <div>
                                    <p class="text-gray-700"><?= sanitize($activity['description']) ?></p>
                                    <p class="text-xs text-gray-500"><?= timeAgo($activity['created_at']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>