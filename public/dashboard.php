<?php
/**
 * Edutrack computer training college
 * Student Dashboard
 */

require_once '../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user
$user = User::current();
$userId = $user->getId();

// Get comprehensive dashboard statistics using Statistics class
try {
    $studentStats = Statistics::getStudentStats($userId);
} catch (Exception $e) {
    // If Statistics class or tables don't exist, use default values
    $studentStats = [
        'in_progress_courses' => 0,
        'completed_courses' => 0,
        'enrolled_courses' => 0,
        'total_certificates' => 0,
        'avg_progress' => 0,
        'avg_quiz_score' => 0,
        'assignments_submitted' => 0
    ];
}

// Build stats array for display
$stats = [
    'active_courses' => $studentStats['in_progress_courses'],
    'completed_courses' => $studentStats['completed_courses'],
    'total_courses' => $studentStats['enrolled_courses'],
    'certificates' => $studentStats['total_certificates'],
    'avg_progress' => $studentStats['avg_progress'],
    'avg_quiz_score' => $studentStats['avg_quiz_score'],
    'assignments_submitted' => $studentStats['assignments_submitted']
];

// Get recent enrollments with course details
try {
    $recentEnrollments = $db->fetchAll("
        SELECT e.*, c.title, c.slug, c.thumbnail, c.description,
               e.last_accessed, e.progress_percentage
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE e.user_id = ? AND e.enrollment_status IN ('Enrolled', 'In Progress')
        ORDER BY e.last_accessed DESC, e.enrolled_at DESC
        LIMIT 4
    ", [$userId]);
} catch (Exception $e) {
    $recentEnrollments = [];
}

// Get upcoming deadlines (assignments due in next 7 days)
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
    ", [$userId, $userId]);
} catch (Exception $e) {
    $upcomingDeadlines = [];
}

// Get unread notifications
try {
    $unreadNotifications = $db->fetchAll("
        SELECT * FROM notifications
        WHERE user_id = ? AND is_read = 0
        ORDER BY created_at DESC
        LIMIT 5
    ", [$userId]);
} catch (Exception $e) {
    $unreadNotifications = [];
}

// Get recent quiz attempts
try {
    $recentQuizzes = $db->fetchAll("
        SELECT qa.*, q.title as quiz_title, c.title as course_title, c.slug as course_slug
        FROM quiz_attempts qa
        JOIN quizzes q ON qa.quiz_id = q.id
        JOIN courses c ON q.course_id = c.id
        WHERE qa.user_id = ?
        ORDER BY qa.completed_at DESC
        LIMIT 3
    ", [$userId]);
} catch (Exception $e) {
    $recentQuizzes = [];
}

// Get recent graded assignments
try {
    $recentGradedAssignments = $db->fetchAll("
        SELECT asub.*, a.title as assignment_title, c.title as course_title, c.slug as course_slug
        FROM assignment_submissions asub
        JOIN assignments a ON asub.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        WHERE asub.user_id = ? AND asub.status = 'graded'
        ORDER BY asub.graded_at DESC
        LIMIT 3
    ", [$userId]);
} catch (Exception $e) {
    $recentGradedAssignments = [];
}

$page_title = "Dashboard - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, <?= sanitize($user->first_name) ?>!</h1>
            <p class="text-gray-600 mt-2">Here's what's happening with your learning journey today.</p>
        </div>

        <!-- Announcements -->
        <?php include '../src/templates/announcements.php'; ?>

        <!-- Unread Notifications Alert -->
        <?php if (!empty($unreadNotifications)): ?>
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-bell text-blue-600 mr-3 text-xl"></i>
                    <div>
                        <h3 class="font-semibold text-blue-900">You have <?= count($unreadNotifications) ?> unread notification<?= count($unreadNotifications) > 1 ? 's' : '' ?></h3>
                        <p class="text-sm text-blue-700 mt-1"><?= sanitize($unreadNotifications[0]['title']) ?></p>
                    </div>
                </div>
                <a href="#notifications-section" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    View all <i class="fas fa-arrow-down ml-1"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>
        
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
            
            <!-- Average Progress -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Avg Progress</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            <?= round($stats['avg_progress']) ?>%
                        </p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-chart-line text-yellow-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <!-- Mini progress bar -->
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-yellow-500 h-2 rounded-full transition-all"
                             style="width: <?= round($stats['avg_progress']) ?>%"></div>
                    </div>
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
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 border border-gray-200 rounded-lg hover:border-primary-300 transition">
                                <img src="<?= courseThumbnail($enrollment['thumbnail']) ?>"
                                     alt="<?= sanitize($enrollment['title']) ?>"
                                     class="w-full sm:w-20 h-32 sm:h-20 object-cover rounded">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 mb-1">
                                        <?= sanitize($enrollment['title']) ?>
                                    </h3>
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm text-gray-600">
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
                                   class="btn-primary px-4 py-2 rounded-md w-full sm:w-auto text-center">
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
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                            Upcoming Deadlines
                        </h2>
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

                <!-- Recent Quiz Scores -->
                <?php if (!empty($recentQuizzes)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-question-circle text-blue-500 mr-2"></i>
                            Recent Quiz Results
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($recentQuizzes as $quiz): ?>
                            <?php
                                $scorePercentage = ($quiz['score'] / $quiz['total_score']) * 100;
                                $scoreClass = $scorePercentage >= 80 ? 'text-green-600' : ($scorePercentage >= 60 ? 'text-yellow-600' : 'text-red-600');
                            ?>
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">
                                            <?= sanitize($quiz['quiz_title']) ?>
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            <?= sanitize($quiz['course_title']) ?>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            <?= timeAgo($quiz['completed_at']) ?>
                                        </p>
                                    </div>
                                    <div class="text-right ml-4">
                                        <p class="text-2xl font-bold <?= $scoreClass ?>">
                                            <?= round($scorePercentage) ?>%
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?= $quiz['score'] ?> / <?= $quiz['total_score'] ?> points
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Graded Assignments -->
                <?php if (!empty($recentGradedAssignments)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-file-alt text-green-500 mr-2"></i>
                            Recent Assignment Grades
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($recentGradedAssignments as $assignment): ?>
                            <?php
                                $scorePercentage = ($assignment['points_earned'] / $assignment['max_points']) * 100;
                                $gradeClass = $scorePercentage >= 80 ? 'text-green-600' : ($scorePercentage >= 60 ? 'text-yellow-600' : 'text-red-600');
                            ?>
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">
                                            <?= sanitize($assignment['assignment_title']) ?>
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            <?= sanitize($assignment['course_title']) ?>
                                        </p>
                                        <?php if ($assignment['feedback']): ?>
                                            <p class="text-sm text-gray-700 mt-2 italic">
                                                "<?= sanitize(substr($assignment['feedback'], 0, 100)) ?><?= strlen($assignment['feedback']) > 100 ? '...' : '' ?>"
                                            </p>
                                        <?php endif; ?>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            Graded <?= timeAgo($assignment['graded_at']) ?>
                                        </p>
                                    </div>
                                    <div class="text-right ml-4">
                                        <p class="text-2xl font-bold <?= $gradeClass ?>">
                                            <?= round($scorePercentage) ?>%
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?= $assignment['points_earned'] ?> / <?= $assignment['max_points'] ?> points
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Notifications Section -->
                <?php if (!empty($unreadNotifications)): ?>
                <div id="notifications-section" class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-bell text-blue-500 mr-2"></i>
                            Notifications
                        </h2>
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                            <?= count($unreadNotifications) ?> new
                        </span>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($unreadNotifications as $notification): ?>
                            <div class="p-4 hover:bg-gray-50 transition">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-<?= sanitize($notification['color']) ?>-100 rounded-full flex items-center justify-center">
                                            <i class="<?= sanitize($notification['icon']) ?> text-<?= sanitize($notification['color']) ?>-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="font-semibold text-gray-900">
                                            <?= sanitize($notification['title']) ?>
                                        </h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <?= sanitize($notification['message']) ?>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            <?= timeAgo($notification['created_at']) ?>
                                        </p>
                                    </div>
                                    <?php if ($notification['link']): ?>
                                        <a href="<?= url($notification['link']) ?>"
                                           class="ml-4 text-primary-600 hover:text-primary-700">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    <?php endif; ?>
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
                
                <!-- Learning Stats Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Learning Stats</h2>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Quiz Average</span>
                            <span class="text-lg font-bold text-primary-600">
                                <?= round($stats['avg_quiz_score']) ?>%
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Assignments Submitted</span>
                            <span class="text-lg font-bold text-green-600">
                                <?= $stats['assignments_submitted'] ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Courses</span>
                            <span class="text-lg font-bold text-gray-900">
                                <?= $stats['total_courses'] ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Completion Rate</span>
                            <span class="text-lg font-bold text-purple-600">
                                <?= $stats['total_courses'] > 0 ? round(($stats['completed_courses'] / $stats['total_courses']) * 100) : 0 ?>%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>