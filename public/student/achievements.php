<?php
/**
 * Student Achievements Page
 * Shows certificates, badges, learning milestones, and progress statistics
 */

require_once '../../src/bootstrap.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = User::current();
$userId = $user->getId();

// Get certificates
$certificates = $db->fetchAll("
    SELECT cert.*, c.title as course_title, c.thumbnail_url, c.slug as course_slug,
           cert.issued_date, cert.certificate_number
    FROM certificates cert
    JOIN enrollments e ON cert.enrollment_id = e.id
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
    ORDER BY cert.issued_date DESC
", [$userId]);

// Get learning stats
$stats = [
    'total_courses' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ?", [$userId]) ?? 0,
    'completed_courses' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND enrollment_status = 'Completed'", [$userId]) ?? 0,
    'total_lessons' => $db->fetchColumn("
        SELECT COUNT(*) FROM lesson_progress lp
        JOIN enrollments e ON lp.enrollment_id = e.id
        WHERE e.user_id = ? AND lp.status = 'Completed'
    ", [$userId]) ?? 0,
    'assignments_submitted' => $db->fetchColumn("
        SELECT COUNT(*) FROM assignment_submissions asub
        JOIN enrollments e ON asub.student_id = e.student_id
        WHERE e.user_id = ?
    ", [$userId]) ?? 0,
    'quizzes_completed' => $db->fetchColumn("
        SELECT COUNT(*) FROM quiz_attempts qa
        JOIN enrollments e ON qa.student_id = e.student_id
        WHERE e.user_id = ? AND qa.status = 'Completed'
    ", [$userId]) ?? 0,
    'avg_quiz_score' => $db->fetchColumn("
        SELECT AVG((qa.score / qa.total_score) * 100) FROM quiz_attempts qa
        JOIN enrollments e ON qa.student_id = e.student_id
        WHERE e.user_id = ? AND qa.status = 'Completed'
    ", [$userId]) ?? 0
];

// Define achievement badges
$badges = [
    [
        'id' => 'first_course',
        'name' => 'Getting Started',
        'description' => 'Enroll in your first course',
        'icon' => 'fa-rocket',
        'color' => 'blue',
        'earned' => $stats['total_courses'] >= 1,
        'progress' => min(100, ($stats['total_courses'] / 1) * 100)
    ],
    [
        'id' => 'course_collector',
        'name' => 'Course Collector',
        'description' => 'Enroll in 5 courses',
        'icon' => 'fa-layer-group',
        'color' => 'indigo',
        'earned' => $stats['total_courses'] >= 5,
        'progress' => min(100, ($stats['total_courses'] / 5) * 100)
    ],
    [
        'id' => 'first_certificate',
        'name' => 'Certificate Earner',
        'description' => 'Complete your first course',
        'icon' => 'fa-certificate',
        'color' => 'green',
        'earned' => $stats['completed_courses'] >= 1,
        'progress' => min(100, ($stats['completed_courses'] / 1) * 100)
    ],
    [
        'id' => 'master_student',
        'name' => 'Master Student',
        'description' => 'Complete 5 courses',
        'icon' => 'fa-graduation-cap',
        'color' => 'purple',
        'earned' => $stats['completed_courses'] >= 5,
        'progress' => min(100, ($stats['completed_courses'] / 5) * 100)
    ],
    [
        'id' => 'lesson_10',
        'name' => 'Lesson Learner',
        'description' => 'Complete 10 lessons',
        'icon' => 'fa-book-reader',
        'color' => 'teal',
        'earned' => $stats['total_lessons'] >= 10,
        'progress' => min(100, ($stats['total_lessons'] / 10) * 100)
    ],
    [
        'id' => 'lesson_50',
        'name' => 'Knowledge Seeker',
        'description' => 'Complete 50 lessons',
        'icon' => 'fa-brain',
        'color' => 'cyan',
        'earned' => $stats['total_lessons'] >= 50,
        'progress' => min(100, ($stats['total_lessons'] / 50) * 100)
    ],
    [
        'id' => 'lesson_100',
        'name' => 'Learning Machine',
        'description' => 'Complete 100 lessons',
        'icon' => 'fa-infinity',
        'color' => 'amber',
        'earned' => $stats['total_lessons'] >= 100,
        'progress' => min(100, ($stats['total_lessons'] / 100) * 100)
    ],
    [
        'id' => 'quiz_whiz',
        'name' => 'Quiz Whiz',
        'description' => 'Complete 10 quizzes',
        'icon' => 'fa-star',
        'color' => 'yellow',
        'earned' => $stats['quizzes_completed'] >= 10,
        'progress' => min(100, ($stats['quizzes_completed'] / 10) * 100)
    ],
    [
        'id' => 'perfect_score',
        'name' => 'Perfect Score',
        'description' => 'Get 100% on any quiz',
        'icon' => 'fa-trophy',
        'color' => 'red',
        'earned' => $db->fetchColumn("
            SELECT COUNT(*) FROM quiz_attempts qa
            JOIN enrollments e ON qa.student_id = e.student_id
            WHERE e.user_id = ? AND qa.score = qa.total_score
        ", [$userId]) > 0,
        'progress' => $db->fetchColumn("
            SELECT COUNT(*) FROM quiz_attempts qa
            JOIN enrollments e ON qa.student_id = e.student_id
            WHERE e.user_id = ? AND qa.score = qa.total_score
        ", [$userId]) > 0 ? 100 : 0
    ],
    [
        'id' => 'assignment_pro',
        'name' => 'Assignment Pro',
        'description' => 'Submit 10 assignments',
        'icon' => 'fa-file-signature',
        'color' => 'orange',
        'earned' => $stats['assignments_submitted'] >= 10,
        'progress' => min(100, ($stats['assignments_submitted'] / 10) * 100)
    ],
    [
        'id' => 'consistent_learner',
        'name' => 'Consistent Learner',
        'description' => 'Learn for 7 consecutive days',
        'icon' => 'fa-fire',
        'color' => 'rose',
        'earned' => false, // Would need daily tracking
        'progress' => 0
    ],
    [
        'id' => 'early_bird',
        'name' => 'Early Bird',
        'description' => 'Complete a lesson before 8 AM',
        'icon' => 'fa-sun',
        'color' => 'yellow',
        'earned' => false,
        'progress' => 0
    ]
];

$earnedBadges = array_filter($badges, fn($b) => $b['earned']);
$lockedBadges = array_filter($badges, fn($b) => !$b['earned']);

// Get recent milestones
$milestones = $db->fetchAll("
    SELECT 'course_complete' as type, c.title, e.completion_date as date, 'Completed course' as description
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ? AND e.enrollment_status = 'Completed' AND e.completion_date IS NOT NULL
    UNION ALL
    SELECT 'certificate' as type, c.title, cert.issued_date as date, 'Earned certificate' as description
    FROM certificates cert
    JOIN enrollments e ON cert.enrollment_id = e.id
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
    UNION ALL
    SELECT 'quiz_high' as type, q.title, qa.completed_at as date, CONCAT('Scored ', ROUND((qa.score/qa.total_score)*100), '% on quiz') as description
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN enrollments e ON qa.student_id = e.student_id
    WHERE e.user_id = ? AND (qa.score/qa.total_score) >= 0.8
    ORDER BY date DESC
    LIMIT 10
", [$userId, $userId, $userId]);

$page_title = "My Achievements - Edutrack";
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900">My Achievements</h1>
            <p class="text-gray-600 mt-2">Track your progress and celebrate your milestones</p>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-gray-800"><?= $stats['completed_courses'] ?></p>
                <p class="text-sm text-gray-500">Courses Completed</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-certificate text-green-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-gray-800"><?= count($certificates) ?></p>
                <p class="text-sm text-gray-500">Certificates</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-book-reader text-purple-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-gray-800"><?= $stats['total_lessons'] ?></p>
                <p class="text-sm text-gray-500">Lessons Completed</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-medal text-yellow-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-gray-800"><?= count($earnedBadges) ?>/<?= count($badges) ?></p>
                <p class="text-sm text-gray-500">Badges Earned</p>
            </div>
        </div>

        <!-- Certificates Section -->
        <?php if (!empty($certificates)): ?>
        <div class="mb-10">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-certificate text-green-600 mr-2"></i>
                My Certificates
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($certificates as $cert): ?>
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-md transition">
                    <div class="h-32 bg-green-500 flex items-center justify-center">
                        <i class="fas fa-certificate text-6xl text-white opacity-90"></i>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-gray-800 mb-1"><?= sanitize($cert['course_title']) ?></h3>
                        <p class="text-sm text-gray-500 mb-3">Issued <?= date('M j, Y', strtotime($cert['issued_at'])) ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono text-gray-400">#<?= $cert['certificate_number'] ?></span>
                            <a href="<?= url('download-certificate.php?id=' . $cert['id']) ?>" 
                               class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-download mr-1"></i>Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Badges Section -->
        <div class="mb-10">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-medal text-yellow-600 mr-2"></i>
                Achievement Badges
            </h2>
            
            <!-- Earned Badges -->
            <?php if (!empty($earnedBadges)): ?>
            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Earned</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($earnedBadges as $badge): ?>
                    <div class="bg-white rounded-xl shadow-sm border p-5 text-center hover:shadow-md transition group">
                        <div class="w-16 h-16 bg-<?= $badge['color'] ?>-100 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                            <i class="fas <?= $badge['icon'] ?> text-<?= $badge['color'] ?>-600 text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 mb-1"><?= $badge['name'] ?></h4>
                        <p class="text-xs text-gray-500"><?= $badge['description'] ?></p>
                        <div class="mt-3">
                            <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                <i class="fas fa-check mr-1"></i>Earned
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Locked Badges -->
            <?php if (!empty($lockedBadges)): ?>
            <div>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">In Progress</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($lockedBadges as $badge): ?>
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 text-center opacity-75 hover:opacity-100 transition">
                        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas <?= $badge['icon'] ?> text-gray-400 text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-600 mb-1"><?= $badge['name'] ?></h4>
                        <p class="text-xs text-gray-400"><?= $badge['description'] ?></p>
                        <div class="mt-3">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-<?= $badge['color'] ?>-400 h-2 rounded-full" style="width: <?= $badge['progress'] ?>"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1"><?= round($badge['progress']) ?>%</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Milestones -->
        <?php if (!empty($milestones)): ?>
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-history text-blue-600 mr-2"></i>
                    Recent Milestones
                </h2>
            </div>
            <div class="divide-y divide-gray-100">
                <?php foreach ($milestones as $milestone): 
                    $icon = match($milestone['type']) {
                        'course_complete' => ['fa-flag-checkered', 'text-green-600', 'bg-green-100'],
                        'certificate' => ['fa-certificate', 'text-yellow-600', 'bg-yellow-100'],
                        'quiz_high' => ['fa-star', 'text-purple-600', 'bg-purple-100'],
                        default => ['fa-circle', 'text-gray-600', 'bg-gray-100']
                    };
                ?>
                <div class="px-6 py-4 flex items-center hover:bg-gray-50 transition">
                    <div class="w-10 h-10 <?= $icon[2] ?> rounded-lg flex items-center justify-center mr-4">
                        <i class="fas <?= $icon[0] ?> <?= $icon[1] ?>"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-800"><?= sanitize($milestone['description']) ?></p>
                        <p class="text-sm text-gray-500"><?= sanitize($milestone['title']) ?></p>
                    </div>
                    <span class="text-sm text-gray-400"><?= timeAgo($milestone['date']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../src/templates/footer.php'; ?>
