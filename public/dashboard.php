<?php
/**
 * Student Dashboard - Modern UI
 * Features: Welcome banner, stat cards, learning progress charts, course cards, upcoming deadlines, notifications
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/PaymentPlan.php';
require_once '../src/classes/RegistrationFee.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

// Get student statistics
$studentStats = Statistics::getStudentStats($userId);
$stats = [
    'active_courses' => $studentStats['in_progress_courses'] ?? 0,
    'completed_courses' => $studentStats['completed_courses'] ?? 0,
    'total_courses' => $studentStats['enrolled_courses'] ?? 0,
    'certificates' => $studentStats['total_certificates'] ?? 0,
    'avg_progress' => $studentStats['avg_progress'] ?? 0,
    'avg_quiz_score' => $studentStats['avg_quiz_score'] ?? 0,
    'assignments_submitted' => $studentStats['assignments_submitted'] ?? 0,
    'total_lessons_completed' => $studentStats['total_lessons_completed'] ?? 0
];

// Get recent enrollments with course details
$recentEnrollments = $db->fetchAll("
    SELECT e.*, c.title, c.slug, c.thumbnail_url, c.description, c.price,
           CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
           e.last_accessed, e.progress as progress_percentage
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN users u ON i.user_id = u.id
    WHERE e.user_id = ? AND e.enrollment_status IN ('Enrolled', 'In Progress')
    ORDER BY e.last_accessed DESC, e.enrolled_at DESC
    LIMIT 3
", [$userId]);

// Get weekly learning activity for chart
$learningActivity = $db->fetchAll("
    SELECT 
        DATE(lp.updated_at) as date,
        COUNT(*) as lessons_completed
    FROM lesson_progress lp
    JOIN enrollments e ON lp.enrollment_id = e.id
    WHERE e.user_id = ? 
    AND lp.status = 'Completed'
    AND lp.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(lp.updated_at)
    ORDER BY date ASC
", [$userId]);

$activityLabels = [];
$activityData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $activityLabels[] = date('D', strtotime($date));
    $found = false;
    foreach ($learningActivity as $activity) {
        if ($activity['date'] === $date) {
            $activityData[] = (int)$activity['lessons_completed'];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $activityData[] = 0;
    }
}

// Get upcoming deadlines
$upcomingDeadlines = $db->fetchAll("
    SELECT a.*, c.title as course_title, c.slug as course_slug,
           DATEDIFF(a.due_date, NOW()) as days_left
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    JOIN enrollments e ON e.course_id = c.id
    WHERE e.user_id = ?
    AND a.due_date > NOW()
    AND a.due_date < DATE_ADD(NOW(), INTERVAL 14 DAY)
    AND a.id NOT IN (
        SELECT assignment_id FROM assignment_submissions WHERE student_id = e.student_id
    )
    ORDER BY a.due_date ASC
    LIMIT 5
", [$userId]);

// Get unread notifications
$unreadNotifications = $db->fetchAll("
    SELECT * FROM notifications
    WHERE user_id = ? AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 5
", [$userId]);

// Get recent achievements/certificates
$recentAchievements = $db->fetchAll("
    SELECT cert.*, c.title as course_title, c.thumbnail_url
    FROM certificates cert
    JOIN courses c ON cert.course_id = c.id
    JOIN enrollments e ON e.course_id = c.id AND e.user_id = ?
    WHERE cert.student_id = e.student_id
    ORDER BY cert.issued_at DESC
    LIMIT 3
", [$userId]);

// Get recommended courses (not enrolled)
$recommendedCourses = $db->fetchAll("
    SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
           cat.name as category_name,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
    FROM courses c
    LEFT JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN users u ON i.user_id = u.id
    LEFT JOIN course_categories cat ON c.category_id = cat.id
    WHERE c.status = 'published'
    AND c.id NOT IN (SELECT course_id FROM enrollments WHERE user_id = ?)
    ORDER BY c.created_at DESC
    LIMIT 3
", [$userId]);

// Check payment status
$totalBalance = 0;
$registrationPaid = true;
try {
    $registrationPaid = RegistrationFee::hasPaid($userId);
    $paymentPlans = PaymentPlan::getByUser($userId);
    foreach ($paymentPlans as $plan) {
        $totalBalance += floatval($plan['balance']);
    }
} catch (Exception $e) {}

$page_title = "Dashboard - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Welcome back, <?= sanitize($user->first_name) ?>! 👋</h1>
                    <p class="text-blue-100 mt-2 text-lg">
                        <?php if ($stats['active_courses'] > 0): ?>
                            You're making great progress! Keep up the momentum.
                        <?php else: ?>
                            Start your learning journey today.
                        <?php endif; ?>
                    </p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="<?= url('courses.php') ?>" 
                       class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 font-semibold rounded-lg shadow-lg hover:bg-gray-100 transition transform hover:-translate-y-0.5">
                        <i class="fas fa-compass mr-2"></i>Explore Courses
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Alerts -->
        <?php if (!$registrationPaid): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-600 mr-3 text-xl"></i>
                    <div>
                        <h3 class="font-semibold text-red-900">Registration Fee Required</h3>
                        <p class="text-sm text-red-700">Please pay your registration fee to access courses.</p>
                    </div>
                </div>
                <a href="<?= url('registration-fee.php') ?>" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 font-medium">
                    Pay Now
                </a>
            </div>
        </div>
        <?php elseif ($totalBalance > 0): ?>
        <div class="mb-6 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-credit-card text-amber-600 mr-3 text-xl"></i>
                    <div>
                        <h3 class="font-semibold text-amber-900">Outstanding Balance: K<?= number_format($totalBalance, 2) ?></h3>
                        <p class="text-sm text-amber-700">Please clear your balance to receive your certificate.</p>
                    </div>
                </div>
                <a href="<?= url('my-payments.php') ?>" class="px-4 py-2 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700 font-medium">
                    View Payments
                </a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($unreadNotifications)): ?>
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <div class="bg-blue-500 text-white rounded-full p-2 mr-3">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3 class="font-semibold text-blue-900"><?= count($unreadNotifications) ?> New Notification<?= count($unreadNotifications) > 1 ? 's' : '' ?></h3>
                </div>
                <button onclick="markAllRead()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Mark all read</button>
            </div>
            <div class="space-y-2">
                <?php foreach (array_slice($unreadNotifications, 0, 2) as $notif): ?>
                <div class="flex items-start bg-white rounded-lg p-3 shadow-sm">
                    <div class="flex-1">
                        <p class="font-medium text-gray-800"><?= sanitize($notif['title']) ?></p>
                        <p class="text-sm text-gray-600"><?= sanitize($notif['message']) ?></p>
                    </div>
                    <span class="text-xs text-gray-400"><?= timeAgo($notif['created_at']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Active Courses</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['active_courses'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-book-open text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="<?= url('my-courses.php') ?>" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        View all →
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Completed</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['completed_courses'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="<?= url('my-courses.php?status=completed') ?>" class="text-sm text-green-600 hover:text-green-700 font-medium">
                        View certificates →
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Avg. Score</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1"><?= round($stats['avg_quiz_score']) ?>%</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full" style="width: <?= round($stats['avg_quiz_score']) ?>%"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Certificates</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['certificates'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-certificate text-amber-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="<?= url('my-certificates.php') ?>" class="text-sm text-amber-600 hover:text-amber-700 font-medium">
                        View all →
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Column -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Learning Activity Chart -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-800">Learning Activity</h2>
                        <span class="text-sm text-gray-500">Last 7 days</span>
                    </div>
                    <div class="h-48">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>

                <!-- Continue Learning -->
                <?php if (!empty($recentEnrollments)): ?>
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800">Continue Learning</h2>
                        <a href="<?= url('my-courses.php') ?>" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View all</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($recentEnrollments as $course): ?>
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex gap-4">
                                <img src="<?= courseThumbnail($course['thumbnail_url']) ?>" 
                                     alt="" class="w-24 h-16 object-cover rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-800 truncate"><?= sanitize($course['title']) ?></h3>
                                    <p class="text-sm text-gray-500"><?= sanitize($course['instructor_name']) ?></p>
                                    <div class="flex items-center gap-3 mt-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full transition-all" 
                                                 style="width: <?= round($course['progress_percentage']) ? round($course['progress_percentage']) : 0 ?>%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-600"><?= round($course['progress_percentage']) ?>%</span>
                                    </div>
                                </div>
                                <a href="<?= url('learn.php?course=' . $course['slug']) ?>" 
                                   class="self-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                    Continue
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-xl shadow-sm border p-8 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book-open text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Start Your Learning Journey</h3>
                    <p class="text-gray-600 mb-4">Enroll in a course to begin tracking your progress.</p>
                    <a href="<?= url('courses.php') ?>" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i>Browse Courses
                    </a>
                </div>
                <?php endif; ?>

                <!-- Recommended Courses -->
                <?php if (!empty($recommendedCourses)): ?>
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-800">Recommended For You</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                        <?php foreach ($recommendedCourses as $course): ?>
                        <div class="p-4 hover:bg-gray-50 transition">
                            <img src="<?= courseThumbnail($course['thumbnail_url']) ?>" 
                                 alt="" class="w-full h-24 object-cover rounded-lg mb-3">
                            <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                <?= sanitize($course['category_name']) ?>
                            </span>
                            <h3 class="font-semibold text-gray-800 mt-2 line-clamp-1"><?= sanitize($course['title']) ?></h3>
                            <p class="text-sm text-gray-500"><?= sanitize($course['instructor_name']) ?></p>
                            <div class="flex items-center justify-between mt-3">
                                <span class="font-bold text-gray-800">K<?= number_format($course['price'], 2) ?></span>
                                <a href="<?= url('course.php?id=' . $course['id']) ?>" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                    Details →
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Profile Summary -->
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="bg-gradient-to-br from-blue-500 to-purple-600 h-20"></div>
                    <div class="px-6 pb-6">
                        <div class="-mt-10 mb-3">
                            <img src="<?= $user->getAvatarUrl() ?>" alt="" 
                                 class="w-20 h-20 rounded-full border-4 border-white shadow-md">
                        </div>
                        <h3 class="text-lg font-bold text-gray-800"><?= sanitize($user->getFullName()) ?></h3>
                        <p class="text-sm text-gray-500"><?= sanitize($user->email) ?></p>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-center">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-lg font-bold text-gray-800"><?= $stats['total_lessons_completed'] ?></p>
                                <p class="text-xs text-gray-500">Lessons Done</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-lg font-bold text-gray-800"><?= round($stats['avg_progress']) ?>%</p>
                                <p class="text-xs text-gray-500">Avg Progress</p>
                            </div>
                        </div>
                        <a href="<?= url('profile.php') ?>" class="block mt-4 text-center py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
                            Edit Profile
                        </a>
                    </div>
                </div>

                <!-- Upcoming Deadlines -->
                <?php if (!empty($upcomingDeadlines)): ?>
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800">Upcoming Deadlines</h3>
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full"><?= count($upcomingDeadlines) ?></span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($upcomingDeadlines as $deadline): 
                            $urgencyClass = $deadline['days_left'] <= 2 ? 'text-red-600 bg-red-50' : ($deadline['days_left'] <= 5 ? 'text-amber-600 bg-amber-50' : 'text-blue-600 bg-blue-50');
                        ?>
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-800 text-sm truncate"><?= sanitize($deadline['title']) ?></h4>
                                    <p class="text-xs text-gray-500"><?= sanitize($deadline['course_title']) ?></p>
                                </div>
                                <span class="text-xs font-medium <?= $urgencyClass ?> px-2 py-1 rounded whitespace-nowrap ml-2">
                                    <?= $deadline['days_left'] == 0 ? 'Today' : ($deadline['days_left'] == 1 ? 'Tomorrow' : $deadline['days_left'] . ' days') ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                        <a href="<?= url('student/assignments.php') ?>" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            View all assignments →
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Links -->
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800">Quick Links</h3>
                    </div>
                    <div class="p-2">
                        <a href="<?= url('my-courses.php') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                            <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-graduation-cap text-blue-600"></i>
                            </div>
                            <span class="text-gray-700 font-medium">My Courses</span>
                        </a>
                        <a href="<?= url('student/assignments.php') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                            <div class="w-9 h-9 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-alt text-orange-600"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Assignments</span>
                            <?php if (!empty($upcomingDeadlines)): ?>
                            <span class="ml-auto text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full"><?= count($upcomingDeadlines) ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= url('student/quizzes.php') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                            <div class="w-9 h-9 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-question-circle text-purple-600"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Quizzes</span>
                        </a>
                        <a href="<?= url('my-certificates.php') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                            <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-certificate text-green-600"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Certificates</span>
                            <?php if ($stats['certificates'] > 0): ?>
                            <span class="ml-auto text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full"><?= $stats['certificates'] ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= url('my-payments.php') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition <?= $totalBalance > 0 ? 'bg-red-50' : '' ?>">
                            <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-credit-card text-red-600"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Payments</span>
                            <?php if ($totalBalance > 0): ?>
                            <span class="ml-auto text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full">K<?= number_format($totalBalance, 0) ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>

                <!-- Recent Achievements -->
                <?php if (!empty($recentAchievements)): ?>
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800">Recent Achievements</h3>
                    </div>
                    <div class="p-4">
                        <?php foreach ($recentAchievements as $cert): ?>
                        <div class="flex items-center gap-3 mb-3 last:mb-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-trophy text-white"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 text-sm truncate"><?= sanitize($cert['course_title']) ?></p>
                                <p class="text-xs text-gray-500"><?= timeAgo($cert['issued_at']) ?></p>
                            </div>
                            <a href="<?= url('download-certificate.php?id=' . $cert['id']) ?>" class="text-amber-600 hover:text-amber-700">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Learning Activity Chart
const ctx = document.getElementById('activityChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($activityLabels) ?>,
        datasets: [{
            label: 'Lessons Completed',
            data: <?= json_encode($activityData) ?>,
            backgroundColor: 'rgba(59, 130, 246, 0.8)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 0,
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

// Mark all notifications as read
function markAllRead() {
    fetch('<?= url('api/notifications.php') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'mark_all_read' })
    }).then(() => location.reload());
}
</script>

<?php require_once '../src/templates/footer.php'; ?>
