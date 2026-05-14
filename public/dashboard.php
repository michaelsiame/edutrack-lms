<?php
/**
 * Student Dashboard - Modern Clean UI
 * Features: Welcome banner, stat cards, learning progress, course cards, upcoming deadlines, notifications
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/classes/PaymentPlan.php';
require_once __DIR__ . '/../src/classes/RegistrationFee.php';

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

// Get active enrollments
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

// Get completed courses
$completedCourses = $db->fetchAll("
    SELECT e.*, c.title, c.slug, c.thumbnail_url, c.description,
           CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
           e.completion_date, e.final_grade
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN users u ON i.user_id = u.id
    WHERE e.user_id = ? AND e.enrollment_status = 'Completed'
    ORDER BY e.completion_date DESC
    LIMIT 3
", [$userId]);

// Check if user is new (no enrollments at all)
$isNewStudent = empty($recentEnrollments) && empty($completedCourses);

// Check registration fee status
$registrationPaid = RegistrationFee::hasPaid($userId);

// Onboarding checklist for new students
$onboardingSteps = [
    ['label' => 'Complete your profile', 'done' => !empty($user->first_name) && !empty($user->last_name), 'url' => 'edit-profile.php', 'icon' => 'fa-user'],
    ['label' => 'Pay registration fee', 'done' => $registrationPaid, 'url' => 'registration-fee.php', 'icon' => 'fa-credit-card'],
    ['label' => 'Enroll in a course', 'done' => !empty($recentEnrollments) || !empty($completedCourses), 'url' => 'courses.php', 'icon' => 'fa-book'],
    ['label' => 'Complete your first lesson', 'done' => ($stats['total_lessons_completed'] > 0), 'url' => 'my-courses.php', 'icon' => 'fa-play-circle'],
    ['label' => 'Earn your first certificate', 'done' => ($stats['certificates'] > 0), 'url' => 'my-courses.php', 'icon' => 'fa-certificate'],
];
$onboardingProgress = count(array_filter($onboardingSteps, fn($s) => $s['done']));
$onboardingTotal = count($onboardingSteps);

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
    JOIN enrollments e ON cert.enrollment_id = e.id
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
    ORDER BY cert.issued_date DESC
    LIMIT 3
", [$userId]);

// Get recommended courses (not enrolled) - with thumbnail
$recommendedCourses = $db->fetchAll("
    SELECT c.*, c.thumbnail_url, CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
           cat.name as category_name,
           COALESCE(ec.enrollment_count, 0) as enrollment_count
    FROM courses c
    LEFT JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN users u ON i.user_id = u.id
    LEFT JOIN course_categories cat ON c.category_id = cat.id
    LEFT JOIN (
        SELECT course_id, COUNT(*) as enrollment_count 
        FROM enrollments 
        GROUP BY course_id
    ) ec ON c.id = ec.course_id
    WHERE c.status = 'published'
    AND c.id NOT IN (SELECT course_id FROM enrollments WHERE user_id = ?)
    ORDER BY c.is_featured DESC, c.enrollment_count DESC, c.created_at DESC
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
require_once __DIR__ . '/../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Clean Welcome Header -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Welcome back,</p>
                    <h1 class="text-2xl font-bold text-gray-900"><?= sanitize($user->first_name) ?> <?= sanitize($user->last_name) ?></h1>
                </div>
                <div class="mt-4 md:mt-0 flex items-center gap-3">
                    <a href="<?= url('courses.php') ?>" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i>Find Courses
                    </a>
                    <a href="<?= url('student/help.php') ?>" 
                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-question-circle mr-2"></i>Help
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Alerts -->
        <?php if (!$registrationPaid): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-red-900">Registration Fee Required</h3>
                        <p class="text-sm text-red-700">Please pay your registration fee to access courses.</p>
                    </div>
                </div>
                <a href="<?= url('registration-fee.php') ?>" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                    Pay Now
                </a>
            </div>
        </div>
        <?php elseif ($totalBalance > 0): ?>
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-credit-card text-amber-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-amber-900">Outstanding Balance: K<?= number_format($totalBalance, 2) ?></h3>
                        <p class="text-sm text-amber-700">Please clear your balance to receive your certificate.</p>
                    </div>
                </div>
                <a href="<?= url('my-payments.php') ?>" class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700">
                    View Payments
                </a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($unreadNotifications)): ?>
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-bell text-blue-600"></i>
                    </div>
                    <h3 class="font-semibold text-blue-900"><?= count($unreadNotifications) ?> New Notification<?= count($unreadNotifications) > 1 ? 's' : '' ?></h3>
                </div>
                <button onclick="markAllRead()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Mark all read</button>
            </div>
            <div class="space-y-2">
                <?php foreach (array_slice($unreadNotifications, 0, 2) as $notif): ?>
                <div class="flex items-start bg-white rounded-lg p-3 border">
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
            <div class="stat-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium" style="color: var(--text-secondary);">Active Courses</p>
                        <p class="text-3xl font-bold mt-2" style="color: var(--text-primary);"><?= $stats['active_courses'] ?></p>
                    </div>
                    <div class="stat-card-icon" style="background: var(--color-primary-50);">
                        <i class="fas fa-book-open" style="color: var(--accent-primary);"></i>
                    </div>
                </div>
                <a href="<?= url('my-courses.php') ?>" class="inline-flex items-center mt-4 text-sm font-medium" style="color: var(--accent-primary);">
                    View all <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>

            <div class="stat-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium" style="color: var(--text-secondary);">Completed</p>
                        <p class="text-3xl font-bold mt-2" style="color: var(--text-primary);"><?= $stats['completed_courses'] ?></p>
                    </div>
                    <div class="stat-card-icon" style="background: var(--surface-success);">
                        <i class="fas fa-check-circle" style="color: var(--status-success);"></i>
                    </div>
                </div>
                <a href="<?= url('my-courses.php?status=completed') ?>" class="inline-flex items-center mt-4 text-sm font-medium" style="color: var(--status-success);">
                    View certificates <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>

            <div class="stat-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium" style="color: var(--text-secondary);">Avg. Score</p>
                        <p class="text-3xl font-bold mt-2" style="color: var(--text-primary);"><?= round($stats['avg_quiz_score']) ?>%</p>
                    </div>
                    <div class="stat-card-icon" style="background: var(--surface-warning);">
                        <i class="fas fa-chart-line" style="color: var(--status-warning);"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full rounded-full h-2" style="background: var(--surface-tertiary);">
                        <div class="h-2 rounded-full transition-all" style="width: <?= round($stats['avg_quiz_score']) ?>%; background: var(--status-warning);"></div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium" style="color: var(--text-secondary);">Certificates</p>
                        <p class="text-3xl font-bold mt-2" style="color: var(--text-primary);"><?= $stats['certificates'] ?></p>
                    </div>
                    <div class="stat-card-icon" style="background: var(--color-secondary-50);">
                        <i class="fas fa-certificate" style="color: var(--accent-secondary);"></i>
                    </div>
                </div>
                <a href="<?= url('my-certificates.php') ?>" class="inline-flex items-center mt-4 text-sm font-medium" style="color: var(--accent-secondary);">
                    View all <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Column -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Onboarding Checklist (for new students) -->
                <?php if ($isNewStudent || $onboardingProgress < $onboardingTotal): ?>
                <div class="course-card mb-6" style="background: var(--surface-warm);">
                    <div class="px-6 py-4 border-b" style="border-color: var(--border-primary);">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold" style="color: var(--text-primary);">Getting Started</h2>
                                <p class="text-sm" style="color: var(--text-secondary);">Complete these steps to get the most out of Edutrack</p>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-bold" style="color: var(--accent-primary);"><?= $onboardingProgress ?></span>
                                <span style="color: var(--text-tertiary);">/<?= $onboardingTotal ?></span>
                            </div>
                        </div>
                        <div class="mt-3 w-full rounded-full h-2" style="background: var(--surface-tertiary);">
                            <div class="h-2 rounded-full transition-all" 
                                 style="width: <?= $onboardingTotal > 0 ? round(($onboardingProgress / $onboardingTotal) * 100) : 0 ?>%; background: var(--accent-primary);"></div>
                        </div>
                    </div>
                    <div class="divide-y" style="border-color: var(--border-secondary);">
                        <?php foreach ($onboardingSteps as $step): ?>
                        <div class="checklist-item <?= $step['done'] ? 'checklist-item-done' : '' ?>">
                            <div class="checklist-icon <?= $step['done'] ? '' : '' ?>" style="background: <?= $step['done'] ? 'var(--surface-success)' : 'var(--surface-tertiary)' ?>; color: <?= $step['done'] ? 'var(--status-success)' : 'var(--text-tertiary)' ?>;">
                                <i class="fas <?= $step['done'] ? 'fa-check' : $step['icon'] ?>"></i>
                            </div>
                            <div class="flex-1">
                                <span class="font-medium <?= $step['done'] ? 'line-through' : '' ?>" style="color: <?= $step['done'] ? 'var(--text-tertiary)' : 'var(--text-primary)' ?>;">
                                    <?= $step['label'] ?>
                                </span>
                            </div>
                            <?php if (!$step['done']): ?>
                            <a href="<?= url($step['url']) ?>" class="btn-primary text-sm" style="padding: 0.375rem 0.875rem;">
                                Start
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Continue Learning -->
                <?php if (!empty($recentEnrollments)): ?>
                <div class="course-card">
                    <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color: var(--border-primary);">
                        <h2 class="text-lg font-bold" style="color: var(--text-primary);">Continue Learning</h2>
                        <a href="<?= url('my-courses.php') ?>" class="text-sm font-medium" style="color: var(--accent-primary);">View all</a>
                    </div>
                    <div class="divide-y" style="border-color: var(--border-secondary);">
                        <?php foreach ($recentEnrollments as $course):
                            $progress = round($course['progress_percentage'] ?? 0);
                            $circumference = 2 * pi() * 18;
                            $strokeDashoffset = $circumference - ($progress / 100) * $circumference;
                        ?>
                        <div class="p-4 transition" style="background: var(--surface-secondary);" onmouseover="this.style.background='var(--surface-tertiary)'" onmouseout="this.style.background='var(--surface-secondary)'">
                            <div class="flex gap-4">
                                <div class="w-24 h-16 flex-shrink-0 rounded-lg overflow-hidden" style="background: var(--surface-tertiary);">
                                    <img src="<?= courseThumbnail($course['thumbnail_url']) ?>" 
                                         alt="" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold truncate" style="color: var(--text-primary);"><?= sanitize($course['title']) ?></h3>
                                    <p class="text-sm" style="color: var(--text-secondary);"><?= sanitize($course['instructor_name']) ?></p>
                                    <div class="flex items-center gap-3 mt-2">
                                        <div class="flex-1 rounded-full h-2" style="background: var(--surface-tertiary);">
                                            <div class="h-2 rounded-full transition-all" 
                                                 style="width: <?= $progress ?>%; background: var(--accent-primary);"></div>
                                        </div>
                                        <span class="text-sm font-medium" style="color: var(--text-secondary);"><?= $progress ?>%</span>
                                    </div>
                                </div>
                                <a href="<?= url('learn.php?course=' . $course['slug']) ?>" 
                                   class="self-center px-4 py-2 text-white text-sm font-medium rounded-lg transition"
                                   style="background: var(--accent-primary);"
                                   onmouseover="this.style.background='var(--accent-primary-hover)'"
                                   onmouseout="this.style.background='var(--accent-primary)'">
                                    Continue
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recently Completed -->
                <?php if (!empty($completedCourses)): ?>
                <div class="course-card course-card-completed mt-6">
                    <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color: #A7F3D0;">
                        <h2 class="text-lg font-bold flex items-center" style="color: var(--text-primary);">
                            <i class="fas fa-check-circle mr-2" style="color: var(--status-success);"></i>
                            Recently Completed
                        </h2>
                        <a href="<?= url('my-courses.php?status=completed') ?>" class="text-sm font-medium" style="color: var(--status-success);">View all</a>
                    </div>
                    <div class="divide-y" style="border-color: #A7F3D0;">
                        <?php foreach ($completedCourses as $course): ?>
                        <div class="p-4 transition" style="background: transparent;" onmouseover="this.style.background='rgba(16, 185, 129, 0.04)'" onmouseout="this.style.background='transparent'">
                            <div class="flex gap-4">
                                <div class="w-24 h-16 flex-shrink-0 rounded-lg overflow-hidden" style="background: var(--surface-tertiary);">
                                    <img src="<?= courseThumbnail($course['thumbnail_url']) ?>" 
                                         alt="" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold truncate" style="color: var(--text-primary);"><?= sanitize($course['title']) ?></h3>
                                    <p class="text-sm" style="color: var(--text-secondary);"><?= sanitize($course['instructor_name']) ?></p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-xs px-2 py-1 rounded-full font-medium" style="background: var(--surface-success); color: var(--status-success);">
                                            Completed <?= date('M j, Y', strtotime($course['completion_date'])) ?>
                                        </span>
                                        <span class="text-xs px-2 py-1 rounded-full font-medium" style="background: var(--color-secondary-50); color: var(--accent-secondary-hover);">
                                            <?= round($course['final_grade']) ?>% Final Grade
                                        </span>
                                    </div>
                                </div>
                                <a href="<?= url('my-certificates.php') ?>" 
                                   class="self-center px-4 py-2 text-white text-sm font-medium rounded-lg transition"
                                   style="background: var(--status-success);"
                                   onmouseover="this.style.background='#059669'"
                                   onmouseout="this.style.background='var(--status-success)'">
                                    <i class="fas fa-certificate mr-1"></i>Certificate
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (empty($recentEnrollments) && empty($completedCourses)): ?>
                <div class="empty-state course-card">
                    <div class="empty-state-icon" style="background: var(--color-primary-50);">
                        <i class="fas fa-book-open text-2xl" style="color: var(--accent-primary);"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2" style="color: var(--text-primary);">Start Your Learning Journey</h3>
                    <p class="mb-4" style="color: var(--text-secondary);">Enroll in a course to begin tracking your progress.</p>
                    <a href="<?= url('courses.php') ?>" class="btn-primary inline-flex items-center px-6 py-3 font-medium">
                        <i class="fas fa-search mr-2"></i>Browse Courses
                    </a>
                </div>
                <?php endif; ?>

                <!-- Learning Activity Chart -->
                <div class="bg-white rounded-xl border p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Learning Activity</h2>
                            <p class="text-sm text-gray-500">Lessons completed in the last 7 days</p>
                        </div>
                    </div>
                    <?php if (array_sum($activityData) > 0): ?>
                    <div class="h-48">
                        <canvas id="activityChart"></canvas>
                    </div>
                    <?php else: ?>
                    <div class="h-48 flex flex-col items-center justify-center text-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-chart-bar text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-gray-500 text-sm">No activity yet this week</p>
                        <p class="text-gray-400 text-xs mt-1">Complete lessons to see your progress here</p>
                        <?php if (!empty($recentEnrollments)): ?>
                        <a href="<?= url('learn.php?course=' . $recentEnrollments[0]['slug']) ?>" class="mt-3 text-sm text-blue-600 hover:text-blue-700 font-medium">
                            Continue learning →
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recommended Courses -->
                <?php if (!empty($recommendedCourses)): ?>
                <div class="bg-white rounded-xl border overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-900">Recommended For You</h2>
                        <p class="text-sm text-gray-500 mt-1">Based on your interests and learning history</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                        <?php foreach ($recommendedCourses as $course): ?>
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="aspect-video rounded-lg overflow-hidden bg-gray-100 mb-3">
                                <img src="<?= courseThumbnail($course['thumbnail_url']) ?>" 
                                     alt="<?= sanitize($course['title']) ?>" 
                                     class="w-full h-full object-cover">
                            </div>
                            <span class="inline-block text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded mb-2">
                                <?= sanitize($course['category_name'] ?: 'General') ?>
                            </span>
                            <h3 class="font-semibold text-gray-900 line-clamp-2 mb-1"><?= sanitize($course['title']) ?></h3>
                            <p class="text-sm text-gray-500 mb-3"><?= sanitize($course['instructor_name']) ?></p>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-900">K<?= number_format($course['price'], 2) ?></span>
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
                <div class="course-card overflow-hidden">
                    <div class="h-16" style="background: linear-gradient(135deg, var(--color-primary-500), var(--color-primary-700));"></div>
                    <div class="px-6 pb-6">
                        <div class="-mt-8 mb-3">
                            <img src="<?= $user->getAvatarUrl() ?>" alt="" 
                                 class="w-16 h-16 rounded-full border-4 shadow-sm" style="border-color: var(--surface-secondary);">
                        </div>
                        <h3 class="text-lg font-bold" style="color: var(--text-primary);"><?= sanitize($user->getFullName()) ?></h3>
                        <p class="text-sm" style="color: var(--text-secondary);"><?= sanitize($user->email) ?></p>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-center">
                            <div class="rounded-lg p-3" style="background: var(--surface-tertiary);">
                                <p class="text-lg font-bold" style="color: var(--text-primary);"><?= $stats['total_lessons_completed'] ?></p>
                                <p class="text-xs" style="color: var(--text-secondary);">Lessons</p>
                            </div>
                            <div class="rounded-lg p-3" style="background: var(--surface-tertiary);">
                                <p class="text-lg font-bold" style="color: var(--text-primary);"><?= round($stats['avg_progress']) ?>%</p>
                                <p class="text-xs" style="color: var(--text-secondary);">Progress</p>
                            </div>
                        </div>
                        <a href="<?= url('profile.php') ?>" class="block mt-4 text-center py-2 rounded-lg text-sm font-medium transition" style="border: 1px solid var(--border-primary); color: var(--text-secondary);" onmouseover="this.style.background='var(--surface-tertiary)'" onmouseout="this.style.background='transparent'">
                            Edit Profile
                        </a>
                    </div>
                </div>

                <!-- Upcoming Deadlines -->
                <?php if (!empty($upcomingDeadlines)): ?>
                <div class="bg-white rounded-xl border overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900">Upcoming Deadlines</h3>
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full"><?= count($upcomingDeadlines) ?></span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($upcomingDeadlines as $deadline): 
                            $urgencyClass = $deadline['days_left'] <= 2 ? 'text-red-600 bg-red-50' : ($deadline['days_left'] <= 5 ? 'text-amber-600 bg-amber-50' : 'text-blue-600 bg-blue-50');
                        ?>
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 text-sm truncate"><?= sanitize($deadline['title']) ?></h4>
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
                <div class="course-card overflow-hidden">
                    <div class="px-5 py-4 border-b" style="border-color: var(--border-primary);">
                        <h3 class="font-bold" style="color: var(--text-primary);">Quick Links</h3>
                    </div>
                    <div class="p-2">
                        <a href="<?= url('my-courses.php') ?>" class="flex items-center gap-3 p-3 rounded-lg transition" style="color: var(--text-primary);" onmouseover="this.style.background='var(--surface-tertiary)'" onmouseout="this.style.background='transparent'">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: var(--color-primary-50);">
                                <i class="fas fa-graduation-cap" style="color: var(--accent-primary);"></i>
                            </div>
                            <span class="font-medium">My Courses</span>
                        </a>
                        <a href="<?= url('student/assignments.php') ?>" class="flex items-center gap-3 p-3 rounded-lg transition" style="color: var(--text-primary);" onmouseover="this.style.background='var(--surface-tertiary)'" onmouseout="this.style.background='transparent'">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: var(--surface-warning);">
                                <i class="fas fa-file-alt" style="color: var(--status-warning);"></i>
                            </div>
                            <span class="font-medium">Assignments</span>
                            <?php if (!empty($upcomingDeadlines)): ?>
                            <span class="ml-auto text-xs px-2 py-1 rounded-full" style="background: var(--surface-warning); color: var(--status-warning);"><?= count($upcomingDeadlines) ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= url('student/quizzes.php') ?>" class="flex items-center gap-3 p-3 rounded-lg transition" style="color: var(--text-primary);" onmouseover="this.style.background='var(--surface-tertiary)'" onmouseout="this.style.background='transparent'">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: #F3E8FF;">
                                <i class="fas fa-question-circle" style="color: #9333EA;"></i>
                            </div>
                            <span class="font-medium">Quizzes</span>
                        </a>
                        <a href="<?= url('my-certificates.php') ?>" class="flex items-center gap-3 p-3 rounded-lg transition" style="color: var(--text-primary);" onmouseover="this.style.background='var(--surface-tertiary)'" onmouseout="this.style.background='transparent'">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: var(--surface-success);">
                                <i class="fas fa-certificate" style="color: var(--status-success);"></i>
                            </div>
                            <span class="font-medium">Certificates</span>
                            <?php if ($stats['certificates'] > 0): ?>
                            <span class="ml-auto text-xs px-2 py-1 rounded-full" style="background: var(--surface-success); color: var(--status-success);"><?= $stats['certificates'] ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= url('my-payments.php') ?>" class="flex items-center gap-3 p-3 rounded-lg transition" style="color: var(--text-primary); <?= $totalBalance > 0 ? 'background: var(--surface-error);' : '' ?>" onmouseover="this.style.background='<?= $totalBalance > 0 ? 'var(--surface-error)' : 'var(--surface-tertiary)' ?>'" onmouseout="this.style.background='<?= $totalBalance > 0 ? 'var(--surface-error)' : 'transparent' ?>'">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: var(--surface-error);">
                                <i class="fas fa-credit-card" style="color: var(--status-error);"></i>
                            </div>
                            <span class="font-medium">Payments</span>
                            <?php if ($totalBalance > 0): ?>
                            <span class="ml-auto text-xs px-2 py-1 rounded-full" style="background: var(--surface-error); color: var(--status-error);">K<?= number_format($totalBalance, 0) ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>

                <!-- Recent Achievements -->
                <?php if (!empty($recentAchievements)): ?>
                <div class="bg-white rounded-xl border overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-900">Recent Achievements</h3>
                    </div>
                    <div class="p-4">
                        <?php foreach ($recentAchievements as $cert): ?>
                        <div class="flex items-center gap-3 mb-3 last:mb-0">
                            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-trophy text-amber-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 text-sm truncate"><?= sanitize($cert['course_title']) ?></p>
                                <p class="text-xs text-gray-500"><?= timeAgo($cert['issued_date']) ?></p>
                            </div>
                            <a href="<?= url('download-certificate.php?id=' . $cert['certificate_id']) ?>" class="text-amber-600 hover:text-amber-700">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
            backgroundColor: '#2563EB',
            borderRadius: 4,
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
                ticks: { stepSize: 1, precision: 0 },
                grid: { color: '#f3f4f6' }
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
        body: JSON.stringify({ action: 'mark_all_as_read' })
    }).then(() => location.reload());
}
</script>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
