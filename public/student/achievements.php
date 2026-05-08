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

<div class="min-h-screen py-8" style="background-color: var(--surface-primary);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, var(--color-secondary-100), var(--surface-warm));">
                <i class="fas fa-trophy text-2xl" style="color: var(--accent-secondary-hover);"></i>
            </div>
            <h1 class="text-3xl font-bold" style="color: var(--text-primary);">My Achievements</h1>
            <p class="mt-2" style="color: var(--text-muted);">Track your progress and celebrate your milestones</p>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <div class="stat-card text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3" style="background: linear-gradient(135deg, var(--color-primary-100), var(--color-primary-200)); color: var(--accent-primary);">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
                <p class="text-2xl font-bold" style="color: var(--text-primary);"><?= $stats['completed_courses'] ?></p>
                <p class="text-sm" style="color: var(--text-muted);">Courses Completed</p>
            </div>
            <div class="stat-card text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3" style="background: linear-gradient(135deg, var(--surface-success), #D1FAE5); color: var(--status-success);">
                    <i class="fas fa-certificate text-xl"></i>
                </div>
                <p class="text-2xl font-bold" style="color: var(--text-primary);"><?= count($certificates) ?></p>
                <p class="text-sm" style="color: var(--text-muted);">Certificates</p>
            </div>
            <div class="stat-card text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3" style="background: linear-gradient(135deg, var(--surface-info), #DBEAFE); color: var(--status-info);">
                    <i class="fas fa-book-reader text-xl"></i>
                </div>
                <p class="text-2xl font-bold" style="color: var(--text-primary);"><?= $stats['total_lessons'] ?></p>
                <p class="text-sm" style="color: var(--text-muted);">Lessons Completed</p>
            </div>
            <div class="stat-card text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3" style="background: linear-gradient(135deg, var(--surface-warning), #FDE68A); color: #B45309;">
                    <i class="fas fa-medal text-xl"></i>
                </div>
                <p class="text-2xl font-bold" style="color: var(--text-primary);"><?= count($earnedBadges) ?>/<?= count($badges) ?></p>
                <p class="text-sm" style="color: var(--text-muted);">Badges Earned</p>
            </div>
        </div>

        <!-- Certificates Section -->
        <?php if (!empty($certificates)): ?>
        <div class="mb-10">
            <h2 class="text-xl font-bold mb-4 flex items-center" style="color: var(--text-primary);">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3" style="background: linear-gradient(135deg, var(--surface-success), #D1FAE5);">
                    <i class="fas fa-certificate text-sm" style="color: var(--status-success);"></i>
                </div>
                My Certificates
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($certificates as $cert): ?>
                <div class="overflow-hidden card-hover" style="background: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); box-shadow: var(--shadow-card);">
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
                    <div class="p-5">
                        <h3 class="font-bold mb-1" style="color: var(--text-primary);"><?= sanitize($cert['course_title']) ?></h3>
                        <p class="text-sm mb-3" style="color: var(--text-muted);">Issued <?= date('M j, Y', strtotime($cert['issued_date'])) ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono" style="color: var(--text-tertiary);">#<?= $cert['certificate_number'] ?></span>
                            <a href="<?= url('download-certificate.php?id=' . $cert['certificate_id']) ?>"
                               class="btn-primary text-sm inline-flex items-center">
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
            <h2 class="text-xl font-bold mb-4 flex items-center" style="color: var(--text-primary);">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3" style="background: linear-gradient(135deg, var(--surface-warning), #FDE68A);">
                    <i class="fas fa-medal text-sm" style="color: #B45309;"></i>
                </div>
                Achievement Badges
            </h2>
            
            <!-- Earned Badges -->
            <?php if (!empty($earnedBadges)): ?>
            <div class="mb-6">
                <h3 class="text-sm font-medium uppercase tracking-wide mb-3" style="color: var(--text-muted);">Earned</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($earnedBadges as $badge): ?>
                    <div class="p-5 text-center card-hover celebration-pop" style="background: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); box-shadow: var(--shadow-card);">
                        <div class="w-16 h-16 bg-<?= $badge['color'] ?>-100 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform" style="box-shadow: 0 0 20px rgba(246, 183, 69, 0.2), 0 4px 12px rgba(0,0,0,0.06);">
                            <i class="fas <?= $badge['icon'] ?> text-<?= $badge['color'] ?>-600 text-2xl"></i>
                        </div>
                        <h4 class="font-bold mb-1" style="color: var(--text-primary);"><?= $badge['name'] ?></h4>
                        <p class="text-xs" style="color: var(--text-muted);"><?= $badge['description'] ?></p>
                        <div class="mt-3">
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full" style="background: var(--surface-success); color: #065F46;">
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
                <h3 class="text-sm font-medium uppercase tracking-wide mb-3" style="color: var(--text-muted);">In Progress</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($lockedBadges as $badge): ?>
                    <div class="rounded-xl border p-5 text-center transition" style="background: var(--surface-tertiary); border-color: var(--border-primary); filter: grayscale(0.6); opacity: 0.8;">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3" style="background: var(--surface-secondary);">
                            <i class="fas <?= $badge['icon'] ?> text-2xl" style="color: var(--text-tertiary);"></i>
                        </div>
                        <h4 class="font-bold mb-1" style="color: var(--text-secondary);"><?= $badge['name'] ?></h4>
                        <p class="text-xs" style="color: var(--text-muted);"><?= $badge['description'] ?></p>
                        <div class="mt-3">
                            <div class="w-full rounded-full h-2" style="background: var(--surface-tertiary); border: 1px solid var(--border-primary);">
                                <div class="h-2 rounded-full" style="width: <?= $badge['progress'] ?>%; background: var(--accent-primary);"></div>
                            </div>
                            <p class="text-xs mt-1" style="color: var(--text-muted);"><?= round($badge['progress']) ?>%</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Milestones -->
        <?php if (!empty($milestones)): ?>
        <div class="overflow-hidden" style="background: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); box-shadow: var(--shadow-card);">
            <div class="px-6 py-4 border-b" style="border-color: var(--border-secondary);">
                <h2 class="text-xl font-bold flex items-center" style="color: var(--text-primary);">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3" style="background: linear-gradient(135deg, var(--surface-info), #DBEAFE);">
                        <i class="fas fa-history text-sm" style="color: var(--status-info);"></i>
                    </div>
                    Recent Milestones
                </h2>
            </div>
            <div class="divide-y" style="border-color: var(--border-secondary);">
                <?php foreach ($milestones as $milestone): 
                    $icon = match($milestone['type']) {
                        'course_complete' => ['fa-flag-checkered', 'var(--status-success)', 'var(--surface-success)'],
                        'certificate' => ['fa-certificate', '#B45309', 'var(--surface-warning)'],
                        'quiz_high' => ['fa-star', 'var(--accent-primary)', 'var(--surface-info)'],
                        default => ['fa-circle', 'var(--text-muted)', 'var(--surface-tertiary)']
                    };
                ?>
                <div class="px-6 py-4 flex items-center transition" style="border-color: var(--border-secondary);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-4 flex-shrink-0" style="background: <?= $icon[2] ?>;">
                        <i class="fas <?= $icon[0] ?>" style="color: <?= $icon[1] ?>;"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium" style="color: var(--text-primary);"><?= sanitize($milestone['description']) ?></p>
                        <p class="text-sm" style="color: var(--text-muted);"><?= sanitize($milestone['title']) ?></p>
                    </div>
                    <span class="text-sm flex-shrink-0 ml-4" style="color: var(--text-tertiary);"><?= timeAgo($milestone['date']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../src/templates/footer.php'; ?>
