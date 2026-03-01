<?php
/**
 * Student Hub - Central Navigation Page
 * Quick access to all student features
 */

require_once '../../src/bootstrap.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = User::current();
$userId = $user->getId();

// Get quick stats
$stats = [
    'active_courses' => $db->fetchColumn("
        SELECT COUNT(*) FROM enrollments 
        WHERE user_id = ? AND enrollment_status IN ('Enrolled', 'In Progress')
    ", [$userId]) ?? 0,
    'pending_assignments' => $db->fetchColumn("
        SELECT COUNT(*) FROM assignments a
        JOIN courses c ON a.course_id = c.id
        JOIN enrollments e ON e.course_id = c.id
        WHERE e.user_id = ? AND a.due_date > NOW()
        AND a.id NOT IN (SELECT assignment_id FROM assignment_submissions WHERE student_id = e.student_id)
    ", [$userId]) ?? 0,
    'certificates' => $db->fetchColumn("
        SELECT COUNT(*) FROM certificates cert
        JOIN enrollments e ON cert.student_id = e.student_id
        WHERE e.user_id = ?
    ", [$userId]) ?? 0,
    'avg_progress' => $db->fetchColumn("
        SELECT AVG(progress) FROM enrollments WHERE user_id = ?
    ", [$userId]) ?? 0
];

$page_title = "Student Hub - Edutrack";
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Welcome Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900">Student Hub</h1>
            <p class="text-gray-600 mt-2">Welcome back, <?= sanitize($user->first_name) ?>! What would you like to do today?</p>
        </div>

        <!-- Quick Stats Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <p class="text-3xl font-bold text-blue-600"><?= $stats['active_courses'] ?></p>
                <p class="text-sm text-gray-500">Active Courses</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <p class="text-3xl font-bold text-orange-600"><?= $stats['pending_assignments'] ?></p>
                <p class="text-sm text-gray-500">Pending Tasks</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <p class="text-3xl font-bold text-purple-600"><?= round($stats['avg_progress']) ?>%</p>
                <p class="text-sm text-gray-500">Avg Progress</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <p class="text-3xl font-bold text-green-600"><?= $stats['certificates'] ?></p>
                <p class="text-sm text-gray-500">Certificates</p>
            </div>
        </div>

        <!-- Main Navigation Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- My Learning -->
            <a href="<?= url('my-courses.php') ?>" class="group bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-600 transition-colors">
                        <i class="fas fa-graduation-cap text-blue-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">My Courses</h3>
                    <p class="text-gray-600 text-sm">Continue learning, track progress, and manage your enrolled courses.</p>
                    <div class="mt-4 flex items-center text-blue-600 font-medium">
                        <span>Go to Courses</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <!-- Assignments -->
            <a href="<?= url('student/assignments.php') ?>" class="group bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6">
                    <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-orange-600 transition-colors">
                        <i class="fas fa-file-alt text-orange-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Assignments</h3>
                    <p class="text-gray-600 text-sm">View pending assignments, submit work, and check your grades.</p>
                    <?php if ($stats['pending_assignments'] > 0): ?>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-orange-600 font-medium"><?= $stats['pending_assignments'] ?> pending</span>
                        <i class="fas fa-arrow-right text-orange-600 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                    <?php else: ?>
                    <div class="mt-4 flex items-center text-orange-600 font-medium">
                        <span>View Assignments</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                    <?php endif; ?>
                </div>
            </a>

            <!-- Quizzes -->
            <a href="<?= url('student/quizzes.php') ?>" class="group bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-purple-600 transition-colors">
                        <i class="fas fa-question-circle text-purple-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Quizzes</h3>
                    <p class="text-gray-600 text-sm">Take quizzes, review results, and track your performance.</p>
                    <div class="mt-4 flex items-center text-purple-600 font-medium">
                        <span>View Quizzes</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <!-- Dashboard -->
            <a href="<?= url('dashboard.php') ?>" class="group bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 transition-colors">
                        <i class="fas fa-chart-line text-indigo-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dashboard</h3>
                    <p class="text-gray-600 text-sm">View your learning analytics, progress charts, and recent activity.</p>
                    <div class="mt-4 flex items-center text-indigo-600 font-medium">
                        <span>Open Dashboard</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <!-- Certificates -->
            <a href="<?= url('my-certificates.php') ?>" class="group bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-green-600 transition-colors">
                        <i class="fas fa-certificate text-green-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Certificates</h3>
                    <p class="text-gray-600 text-sm">Download your earned certificates and view achievements.</p>
                    <?php if ($stats['certificates'] > 0): ?>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-green-600 font-medium"><?= $stats['certificates'] ?> earned</span>
                        <i class="fas fa-arrow-right text-green-600 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                    <?php else: ?>
                    <div class="mt-4 flex items-center text-green-600 font-medium">
                        <span>View Certificates</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                    <?php endif; ?>
                </div>
            </a>

            <!-- Browse Courses -->
            <a href="<?= url('courses.php') ?>" class="group bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6">
                    <div class="w-14 h-14 bg-teal-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-teal-600 transition-colors">
                        <i class="fas fa-search text-teal-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Browse Courses</h3>
                    <p class="text-gray-600 text-sm">Discover new courses and expand your knowledge.</p>
                    <div class="mt-4 flex items-center text-teal-600 font-medium">
                        <span>Explore Courses</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <!-- Payments -->
            <a href="<?= url('my-payments.php') ?>" class="group bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-red-600 transition-colors">
                        <i class="fas fa-credit-card text-red-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Payments</h3>
                    <p class="text-gray-600 text-sm">View payment history, pending balances, and make payments.</p>
                    <div class="mt-4 flex items-center text-red-600 font-medium">
                        <span>Manage Payments</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <!-- Profile -->
            <a href="<?= url('profile.php') ?>" class="group bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6">
                    <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-gray-600 transition-colors">
                        <i class="fas fa-user text-gray-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">My Profile</h3>
                    <p class="text-gray-600 text-sm">Update your personal information, avatar, and preferences.</p>
                    <div class="mt-4 flex items-center text-gray-600 font-medium">
                        <span>Edit Profile</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <!-- Support/Help -->
            <a href="<?= url('contact.php') ?>" class="group bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-lg transition-all duration-300">
                <div class="p-6">
                    <div class="w-14 h-14 bg-pink-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-pink-600 transition-colors">
                        <i class="fas fa-headset text-pink-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Support</h3>
                    <p class="text-gray-600 text-sm">Need help? Contact our support team for assistance.</p>
                    <div class="mt-4 flex items-center text-pink-600 font-medium">
                        <span>Get Help</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recent Activity Preview -->
        <?php
        $recentActivity = $db->fetchAll("
            SELECT 'enrollment' as type, c.title, e.enrolled_at as date
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE e.user_id = ?
            UNION ALL
            SELECT 'quiz' as type, q.title, qa.completed_at as date
            FROM quiz_attempts qa
            JOIN quizzes q ON qa.quiz_id = q.id
            JOIN enrollments e ON qa.student_id = e.student_id
            WHERE e.user_id = ?
            UNION ALL
            SELECT 'certificate' as type, c.title, cert.issued_at as date
            FROM certificates cert
            JOIN courses c ON cert.course_id = c.id
            JOIN enrollments e ON cert.student_id = e.student_id
            WHERE e.user_id = ?
            ORDER BY date DESC
            LIMIT 5
        ", [$userId, $userId, $userId]);
        ?>

        <?php if (!empty($recentActivity)): ?>
        <div class="mt-10 bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Recent Activity</h2>
            </div>
            <div class="divide-y divide-gray-100">
                <?php foreach ($recentActivity as $activity): 
                    $icon = match($activity['type']) {
                        'enrollment' => ['fa-book', 'text-blue-600', 'bg-blue-100', 'Enrolled in'],
                        'quiz' => ['fa-question-circle', 'text-purple-600', 'bg-purple-100', 'Completed quiz'],
                        'certificate' => ['fa-certificate', 'text-green-600', 'bg-green-100', 'Earned certificate for'],
                        default => ['fa-circle', 'text-gray-600', 'bg-gray-100', '']
                    };
                ?>
                <div class="px-6 py-4 flex items-center">
                    <div class="w-10 h-10 <?= $icon[2] ?> rounded-lg flex items-center justify-center mr-4">
                        <i class="fas <?= $icon[0] ?> <?= $icon[1] ?>"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-800">
                            <span class="font-medium"><?= $icon[3] ?></span> <?= sanitize($activity['title']) ?>
                        </p>
                        <p class="text-sm text-gray-500"><?= timeAgo($activity['date']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../src/templates/footer.php'; ?>
