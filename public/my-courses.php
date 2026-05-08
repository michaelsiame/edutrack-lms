<?php
/**
 * My Courses - Enhanced with Modern UI
 * Features: Grid/List view, detailed progress, course timeline, skill tags
 */

require_once __DIR__ . '/../src/bootstrap.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

// Get filter and view mode
$status = $_GET['status'] ?? 'all';
$view = $_GET['view'] ?? 'grid';
$sort = $_GET['sort'] ?? 'recent';

// Build filter condition
$statusFilter = match($status) {
    'active' => "AND e.enrollment_status IN ('Enrolled', 'In Progress')",
    'completed' => "AND e.enrollment_status = 'Completed'",
    default => ""
};

// Build sort condition
$sortSql = match($sort) {
    'progress' => "ORDER BY e.progress DESC",
    'alphabetical' => "ORDER BY c.title ASC",
    default => "ORDER BY e.last_accessed DESC, e.enrolled_at DESC"
};

// Get basic enrollment data (avoid massive JOIN)
$enrollments = $db->fetchAll("
    SELECT e.*, c.title, c.slug, c.thumbnail_url, c.description, c.price,
           c.instructor_id, c.duration_weeks, c.total_hours, c.level,
           CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
           e.enrolled_at, e.last_accessed, e.progress as progress_percentage, e.enrollment_status
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN users u ON i.user_id = u.id
    WHERE e.user_id = ? $statusFilter
    $sortSql
", [$userId]);

// Get lesson counts per enrollment in a single query
$enrollmentIds = array_column($enrollments, 'id');
$lessonCounts = [];
$assignmentCounts = [];
$quizCounts = [];

if (!empty($enrollmentIds)) {
    $placeholders = implode(',', array_fill(0, count($enrollmentIds), '?'));
    
    // Lesson counts
    $lessonData = $db->fetchAll("
        SELECT 
            e.id as enrollment_id,
            COUNT(DISTINCT l.id) as total_lessons,
            COUNT(DISTINCT CASE WHEN lp.status = 'Completed' THEN lp.lesson_id END) as completed_lessons,
            MAX(lp.updated_at) as last_lesson_date
        FROM enrollments e
        JOIN modules m ON e.course_id = m.course_id
        LEFT JOIN lessons l ON m.id = l.module_id
        LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.enrollment_id = e.id
        WHERE e.id IN ($placeholders)
        GROUP BY e.id
    ", $enrollmentIds);
    foreach ($lessonData as $row) {
        $lessonCounts[$row['enrollment_id']] = $row;
    }
    
    // Assignment counts
    $assignmentData = $db->fetchAll("
        SELECT 
            e.id as enrollment_id,
            COUNT(DISTINCT a.id) as total_assignments,
            COUNT(DISTINCT asub.id) as submitted_assignments
        FROM enrollments e
        LEFT JOIN assignments a ON e.course_id = a.course_id
        LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.student_id = e.student_id
        WHERE e.id IN ($placeholders)
        GROUP BY e.id
    ", $enrollmentIds);
    foreach ($assignmentData as $row) {
        $assignmentCounts[$row['enrollment_id']] = $row;
    }
    
    // Quiz counts
    $quizData = $db->fetchAll("
        SELECT 
            e.id as enrollment_id,
            COUNT(DISTINCT q.id) as total_quizzes,
            COUNT(DISTINCT qa.id) as attempted_quizzes
        FROM enrollments e
        LEFT JOIN quizzes q ON e.course_id = q.course_id AND q.is_published = 1
        LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.student_id = e.student_id
        WHERE e.id IN ($placeholders)
        GROUP BY e.id
    ", $enrollmentIds);
    foreach ($quizData as $row) {
        $quizCounts[$row['enrollment_id']] = $row;
    }
}

// Merge counts into enrollments
foreach ($enrollments as &$enrollment) {
    $eid = $enrollment['id'];
    $enrollment['total_lessons'] = $lessonCounts[$eid]['total_lessons'] ?? 0;
    $enrollment['completed_lessons'] = $lessonCounts[$eid]['completed_lessons'] ?? 0;
    $enrollment['total_assignments'] = $assignmentCounts[$eid]['total_assignments'] ?? 0;
    $enrollment['submitted_assignments'] = $assignmentCounts[$eid]['submitted_assignments'] ?? 0;
    $enrollment['total_quizzes'] = $quizCounts[$eid]['total_quizzes'] ?? 0;
    $enrollment['attempted_quizzes'] = $quizCounts[$eid]['attempted_quizzes'] ?? 0;
    $enrollment['last_lesson_date'] = $lessonCounts[$eid]['last_lesson_date'] ?? null;
}
unset($enrollment);

// Get all module completion data in a single query (fix N+1)
$enrollmentIds = array_column($enrollments, 'id');
$courseIds = array_column($enrollments, 'course_id');

if (!empty($enrollmentIds) && !empty($courseIds)) {
    $placeholders = implode(',', array_fill(0, count($enrollmentIds), '?'));
    $allModuleProgress = $db->fetchAll("
        SELECT m.id, m.title, m.course_id, e.id as enrollment_id,
               COUNT(DISTINCT l.id) as total_lessons,
               COUNT(DISTINCT CASE WHEN lp.status = 'Completed' THEN lp.lesson_id END) as completed_lessons
        FROM modules m
        INNER JOIN enrollments e ON m.course_id = e.course_id
        LEFT JOIN lessons l ON m.id = l.module_id
        LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.enrollment_id = e.id
        WHERE e.id IN ($placeholders)
        GROUP BY m.id, e.id
        ORDER BY m.display_order ASC
    ", $enrollmentIds);
    
    // Group module progress by enrollment_id
    $moduleProgressByEnrollment = [];
    foreach ($allModuleProgress as $progress) {
        $moduleProgressByEnrollment[$progress['enrollment_id']][] = $progress;
    }
}

// Assign module progress to each enrollment and calculate estimated completion
foreach ($enrollments as &$enrollment) {
    $enrollment['modules'] = $moduleProgressByEnrollment[$enrollment['id']] ?? [];
    
    // Calculate estimated completion
    if ($enrollment['progress_percentage'] > 0 && $enrollment['duration_weeks']) {
        $remainingPercent = 100 - $enrollment['progress_percentage'];
        $completedPercent = $enrollment['progress_percentage'];
        $weeksSpent = (strtotime('now') - strtotime($enrollment['enrolled_at'])) / (7 * 24 * 60 * 60);
        if ($weeksSpent > 0 && $completedPercent > 0) {
            $enrollment['estimated_weeks_left'] = ceil(($remainingPercent / $completedPercent) * $weeksSpent);
        } else {
            $enrollment['estimated_weeks_left'] = $enrollment['duration_weeks'];
        }
    }
}
unset($enrollment);

// Count by status + overall stats in single query
$headerStats = $db->fetchOne("
    SELECT 
        COUNT(*) as all_count,
        SUM(CASE WHEN enrollment_status IN ('Enrolled', 'In Progress') THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN enrollment_status = 'Completed' THEN 1 ELSE 0 END) as completed_count,
        AVG(progress) as avg_progress
    FROM enrollments 
    WHERE user_id = ?
", [$userId]);

$counts = [
    'all' => (int) ($headerStats['all_count'] ?? 0),
    'active' => (int) ($headerStats['active_count'] ?? 0),
    'completed' => (int) ($headerStats['completed_count'] ?? 0)
];

$totalProgress = (float) ($headerStats['avg_progress'] ?? 0);

$certificatesCount = $db->fetchColumn("
    SELECT COUNT(*) FROM certificates cert
    JOIN enrollments e ON cert.enrollment_id = e.id
    WHERE e.user_id = ?
", [$userId]) ?? 0;

$page_title = "My Courses - Edutrack";
require_once __DIR__ . '/../src/templates/header.php';
?>
<style>
    .tab-active {
        background: var(--accent-primary);
        color: var(--text-inverse);
    }
    .tab-inactive {
        background: var(--surface-tertiary);
        color: var(--text-secondary);
    }
    .tab-inactive:hover {
        background: var(--border-primary);
    }
    .view-toggle-active {
        background: var(--accent-primary);
        color: var(--text-inverse);
    }
    .view-toggle-inactive {
        background: var(--surface-secondary);
        color: var(--text-secondary);
    }
    .view-toggle-inactive:hover {
        background: var(--surface-tertiary);
    }
    .row-divider > * + * {
        border-top: 1px solid var(--border-primary);
    }
    .list-row {
        transition: background-color var(--duration-normal) var(--easing-default);
    }
    .list-row:hover {
        background-color: var(--surface-tertiary);
    }
    .course-card {
        box-shadow: var(--shadow-card);
    }
    .btn-secondary {
        background: var(--surface-secondary);
        color: var(--text-secondary);
        border: 1px solid var(--border-primary);
        border-radius: var(--radius-lg);
        transition: background-color var(--duration-fast) var(--easing-default);
    }
    .btn-secondary:hover {
        background: var(--surface-tertiary);
    }
    .select-token:focus {
        outline: none;
        border-color: var(--border-focus);
        box-shadow: 0 0 0 3px rgba(46, 112, 218, 0.15);
    }
    .module-dots > .group:nth-child(8n+1) .module-dot { background-color: var(--accent-primary) !important; }
    .module-dots > .group:nth-child(8n+2) .module-dot { background-color: var(--color-primary-400) !important; }
    .module-dots > .group:nth-child(8n+3) .module-dot { background-color: var(--color-primary-300) !important; }
    .module-dots > .group:nth-child(8n+4) .module-dot { background-color: var(--accent-secondary) !important; }
    .module-dots > .group:nth-child(8n+5) .module-dot { background-color: var(--color-secondary-400) !important; }
    .module-dots > .group:nth-child(8n+6) .module-dot { background-color: var(--status-success) !important; }
    .module-dots > .group:nth-child(8n+7) .module-dot { background-color: var(--status-warning) !important; }
    .module-dots > .group:nth-child(8n+8) .module-dot { background-color: var(--status-info) !important; }
</style>

<div class="min-h-screen py-8" style="background: var(--surface-primary);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header with Overall Stats -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold" style="color: var(--text-primary);">My Learning</h1>
                    <p class="mt-2" style="color: var(--text-secondary);">Track your progress and continue your learning journey</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3" style="background: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); padding: var(--space-4); box-shadow: var(--shadow-card);">
                        <div class="progress-ring-container progress-ring-md">
                            <svg class="progress-ring-svg" width="56" height="56" viewBox="0 0 56 56">
                                <circle class="progress-ring-bg" cx="28" cy="28" r="24" stroke-width="4"/>
                                <circle class="progress-ring-fill <?= $totalProgress == 100 ? 'progress-ring-fill-success' : '' ?>" cx="28" cy="28" r="24" stroke-width="4"
                                        stroke-dasharray="150.8" stroke-dashoffset="<?= 150.8 - (150.8 * ((round($totalProgress) ? round($totalProgress) : 0)) / 100) ?>"/>
                            </svg>
                            <span class="progress-ring-text"><?= round($totalProgress) ?>%</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium" style="color: var(--text-muted);">Overall Progress</span>
                            <p class="text-lg font-bold" style="color: var(--text-primary);"><?= round($totalProgress) ?>%</p>
                        </div>
                    </div>
                    <?php if ($certificatesCount > 0): ?>
                    <div class="flex items-center gap-3" style="background: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); padding: var(--space-4); box-shadow: var(--shadow-card);">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: var(--surface-success);">
                            <i class="fas fa-certificate" style="color: var(--status-success);"></i>
                        </div>
                        <div>
                            <span class="text-sm font-medium" style="color: var(--text-muted);">Certificates</span>
                            <p class="text-lg font-bold" style="color: var(--status-success);"><?= $certificatesCount ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Filters & Controls -->
        <div class="mb-6" style="background: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); box-shadow: var(--shadow-card);">
            <div class="p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Status Tabs -->
                <div class="flex flex-wrap gap-2">
                    <a href="?status=all&view=<?= $view ?>&sort=<?= $sort ?>" 
                       class="px-4 py-2 rounded-lg font-medium transition <?= $status === 'all' ? 'tab-active' : 'tab-inactive' ?>">
                        All <span class="ml-1 opacity-75">(<?= $counts['all'] ?>)</span>
                    </a>
                    <a href="?status=active&view=<?= $view ?>&sort=<?= $sort ?>" 
                       class="px-4 py-2 rounded-lg font-medium transition <?= $status === 'active' ? 'tab-active' : 'tab-inactive' ?>">
                        In Progress <span class="ml-1 opacity-75">(<?= $counts['active'] ?>)</span>
                    </a>
                    <a href="?status=completed&view=<?= $view ?>&sort=<?= $sort ?>" 
                       class="px-4 py-2 rounded-lg font-medium transition <?= $status === 'completed' ? 'tab-active' : 'tab-inactive' ?>">
                        Completed <span class="ml-1 opacity-75">(<?= $counts['completed'] ?>)</span>
                    </a>
                </div>

                <!-- View & Sort Controls -->
                <div class="flex items-center gap-3">
                    <!-- Sort Dropdown -->
                    <select onchange="window.location.href='?status=<?= $status ?>&view=<?= $view ?>&sort='+this.value" 
                            class="px-3 py-2 rounded-lg text-sm select-token" style="background: var(--surface-secondary); border: 1px solid var(--border-primary); color: var(--text-secondary);">
                        <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Recently Accessed</option>
                        <option value="progress" <?= $sort === 'progress' ? 'selected' : '' ?>>Progress (High to Low)</option>
                        <option value="alphabetical" <?= $sort === 'alphabetical' ? 'selected' : '' ?>>Alphabetical</option>
                    </select>

                    <!-- View Toggle -->
                    <div class="flex overflow-hidden" style="border: 1px solid var(--border-primary); border-radius: var(--radius-lg);">
                        <a href="?status=<?= $status ?>&view=grid&sort=<?= $sort ?>" 
                           class="px-3 py-2 transition <?= $view === 'grid' ? 'view-toggle-active' : 'view-toggle-inactive' ?>">
                            <i class="fas fa-th-large"></i>
                        </a>
                        <a href="?status=<?= $status ?>&view=list&sort=<?= $sort ?>" 
                           class="px-3 py-2 transition <?= $view === 'list' ? 'view-toggle-active' : 'view-toggle-inactive' ?>">
                            <i class="fas fa-list"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($enrollments)): ?>
            
            <?php if ($view === 'list'): ?>
            <!-- List View -->
            <div style="background: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); box-shadow: var(--shadow-card); overflow: hidden;">
                <div class="row-divider">
                    <?php foreach ($enrollments as $course): ?>
                    <div class="p-6 list-row">
                        <div class="flex flex-col lg:flex-row gap-6 items-start">
                            <!-- Thumbnail -->
                            <div class="lg:w-48 shrink-0">
                                <div class="relative overflow-hidden" style="border-radius: var(--radius-xl);">
                                    <img src="<?= courseThumbnail($course['thumbnail_url']) ?>" 
                                         alt="" class="w-full h-32 object-cover">
                                    <?php if ($course['enrollment_status'] === 'Completed'): ?>
                                    <div class="absolute inset-0 flex items-center justify-center" style="background: rgba(16, 185, 129, 0.9);">
                                        <div class="text-center text-white">
                                            <i class="fas fa-check-circle text-3xl mb-1"></i>
                                            <p class="text-sm font-bold">COMPLETED</p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-2 py-1 text-xs font-medium rounded" style="background: var(--surface-info); color: var(--status-info);">
                                                <?= $course['level'] ?>
                                            </span>
                                            <?php if ($course['enrollment_status'] === 'Completed'): ?>
                                            <span class="px-2 py-1 text-xs font-medium rounded" style="background: var(--surface-success); color: var(--status-success);">
                                                <i class="fas fa-certificate mr-1"></i>Certificate Available
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="text-xl font-bold mb-1" style="color: var(--text-primary);"><?= sanitize($course['title']) ?></h3>
                                        <p class="text-sm" style="color: var(--text-muted);">Instructor: <?= sanitize($course['instructor_name']) ?></p>
                                    </div>
                                    
                                    <!-- Right side: Progress Ring + Actions -->
                                    <div class="flex items-center gap-4 shrink-0">
                                        <div class="progress-ring-container progress-ring-md">
                                            <svg class="progress-ring-svg" width="56" height="56" viewBox="0 0 56 56">
                                                <circle class="progress-ring-bg" cx="28" cy="28" r="24" stroke-width="4"/>
                                                <circle class="progress-ring-fill <?= $course['progress_percentage'] == 100 ? 'progress-ring-fill-success' : '' ?>" cx="28" cy="28" r="24" stroke-width="4"
                                                        stroke-dasharray="150.8" stroke-dashoffset="<?= 150.8 - (150.8 * ((round($course['progress_percentage']) ? round($course['progress_percentage']) : 0)) / 100) ?>"/>
                                            </svg>
                                            <span class="progress-ring-text"><?= round($course['progress_percentage']) ?>%</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <?php if ($course['enrollment_status'] === 'Completed'): ?>
                                            <a href="<?= url('my-certificates.php') ?>" class="btn-primary">
                                                <i class="fas fa-certificate mr-2"></i>Certificate
                                            </a>
                                            <?php else: ?>
                                            <a href="<?= url('learn.php?course=' . $course['slug']) ?>" class="btn-primary">
                                                <i class="fas fa-play mr-2"></i>Continue
                                            </a>
                                            <?php endif; ?>
                                            <a href="<?= url('course.php?id=' . $course['course_id']) ?>" class="px-4 py-2 btn-secondary">
                                                <i class="fas fa-info-circle"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stats Grid -->
                                <div class="mt-4 grid grid-cols-3 md:grid-cols-6 gap-4 text-sm">
                                    <div class="p-3 text-center rounded-lg" style="background: var(--surface-info);">
                                        <p class="font-bold" style="color: var(--status-info);"><?= $course['completed_lessons'] ?>/<?= $course['total_lessons'] ?></p>
                                        <p class="text-xs" style="color: var(--text-muted);">Lessons</p>
                                    </div>
                                    <div class="p-3 text-center rounded-lg" style="background: var(--surface-success);">
                                        <p class="font-bold" style="color: var(--status-success);"><?= $course['submitted_assignments'] ?>/<?= $course['total_assignments'] ?></p>
                                        <p class="text-xs" style="color: var(--text-muted);">Assignments</p>
                                    </div>
                                    <div class="p-3 text-center rounded-lg" style="background: var(--surface-warning);">
                                        <p class="font-bold" style="color: var(--status-warning);"><?= $course['attempted_quizzes'] ?>/<?= $course['total_quizzes'] ?></p>
                                        <p class="text-xs" style="color: var(--text-muted);">Quizzes</p>
                                    </div>
                                    <div class="p-3 text-center rounded-lg" style="background: var(--surface-tertiary);">
                                        <p class="font-bold" style="color: var(--text-primary);"><?= $course['duration_weeks'] ?: 'N/A' ?></p>
                                        <p class="text-xs" style="color: var(--text-muted);">Weeks</p>
                                    </div>
                                    <div class="p-3 text-center rounded-lg" style="background: var(--surface-tertiary);">
                                        <p class="font-bold" style="color: var(--text-primary);"><?= $course['total_hours'] ?: 'N/A' ?></p>
                                        <p class="text-xs" style="color: var(--text-muted);">Hours</p>
                                    </div>
                                    <div class="p-3 text-center rounded-lg" style="background: var(--surface-warm);">
                                        <p class="font-bold" style="color: var(--accent-secondary-hover);"><?= isset($course['estimated_weeks_left']) ? $course['estimated_weeks_left'] . 'w' : 'N/A' ?></p>
                                        <p class="text-xs" style="color: var(--text-muted);">Est. Left</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php else: ?>
            <!-- Grid View -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php foreach ($enrollments as $course): ?>
                <div class="course-card">
                    <!-- Thumbnail -->
                    <div class="relative h-48">
                        <img src="<?= courseThumbnail($course['thumbnail_url']) ?>" 
                             alt="" class="w-full h-full object-cover">
                        
                        <!-- Completion Overlay -->
                        <?php if ($course['enrollment_status'] === 'Completed'): ?>
                        <div class="absolute inset-0 flex items-center justify-center" style="background: rgba(16, 185, 129, 0.85);">
                            <div class="text-center text-white">
                                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-2" style="background: rgba(255,255,255,0.2);">
                                    <i class="fas fa-check text-3xl"></i>
                                </div>
                                <p class="font-bold text-lg">COMPLETED</p>
                                <a href="<?= url('my-certificates.php') ?>" class="inline-flex items-center mt-2 text-sm font-medium px-3 py-1 rounded-full" style="background: var(--surface-secondary); color: var(--status-success);">
                                    <i class="fas fa-certificate mr-1"></i>View Certificate
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Level Badge -->
                        <div class="absolute top-3 left-3">
                            <span class="px-2 py-1 text-xs font-medium rounded-lg" style="background: rgba(255,255,255,0.9); color: var(--text-primary);">
                                <?= $course['level'] ?>
                            </span>
                        </div>

                        <!-- Progress Ring -->
                        <?php if ($course['enrollment_status'] !== 'Completed'): ?>
                        <div class="absolute bottom-3 right-3">
                            <div class="progress-ring-container progress-ring-md">
                                <svg class="progress-ring-svg" width="56" height="56" viewBox="0 0 56 56">
                                    <circle class="progress-ring-bg" cx="28" cy="28" r="24" stroke-width="4"/>
                                    <circle class="progress-ring-fill" cx="28" cy="28" r="24" stroke-width="4"
                                            stroke-dasharray="150.8" stroke-dashoffset="<?= 150.8 - (150.8 * ((round($course['progress_percentage']) ? round($course['progress_percentage']) : 0)) / 100) ?>"/>
                                </svg>
                                <span class="progress-ring-text"><?= round($course['progress_percentage']) ?>%</span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Content -->
                    <div class="p-5">
                        <h3 class="font-bold text-lg mb-1 line-clamp-1" style="color: var(--text-primary);"><?= sanitize($course['title']) ?></h3>
                        <p class="text-sm mb-3" style="color: var(--text-muted);"><?= sanitize($course['instructor_name']) ?></p>

                        <!-- Progress Bar -->
                        <?php if ($course['enrollment_status'] !== 'Completed'): ?>
                        <div class="mb-4">
                            <div class="w-full rounded-full h-2" style="background: var(--surface-tertiary);">
                                <div class="h-2 rounded-full transition-all" style="background: var(--accent-primary); width: <?= round($course['progress_percentage']) ? round($course['progress_percentage']) : 0 ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-3 gap-2 mb-4 text-center text-sm">
                            <div class="p-2 rounded-lg" style="background: var(--surface-info);">
                                <p class="font-bold" style="color: var(--status-info);"><?= $course['completed_lessons'] ?></p>
                                <p class="text-xs" style="color: var(--text-muted);">Lessons</p>
                            </div>
                            <div class="p-2 rounded-lg" style="background: var(--surface-success);">
                                <p class="font-bold" style="color: var(--status-success);"><?= $course['submitted_assignments'] ?></p>
                                <p class="text-xs" style="color: var(--text-muted);">Tasks</p>
                            </div>
                            <div class="p-2 rounded-lg" style="background: var(--surface-warning);">
                                <p class="font-bold" style="color: var(--status-warning);"><?= $course['attempted_quizzes'] ?></p>
                                <p class="text-xs" style="color: var(--text-muted);">Quizzes</p>
                            </div>
                        </div>

                        <!-- Module Progress Dots -->
                        <?php if (!empty($course['modules']) && count($course['modules']) <= 8): ?>
                        <div class="mb-4">
                            <div class="flex gap-1 justify-center module-dots">
                                <?php foreach ($course['modules'] as $module):
                                    $modProgress = $module['total_lessons'] > 0 ? ($module['completed_lessons'] / $module['total_lessons']) * 100 : 0;
                                    $dotClass = $modProgress == 100 ? 'bg-green-500' : ($modProgress > 0 ? 'bg-blue-500' : 'bg-gray-300');
                                ?>
                                <div class="group relative">
                                    <div class="w-3 h-3 rounded-full module-dot <?= $dotClass ?>" title="<?= sanitize($module['title']) ?>"></div>
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition whitespace-nowrap z-10" style="background: var(--text-primary); color: var(--text-inverse);">
                                        <?= sanitize($module['title']) ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <p class="text-xs text-center mt-1" style="color: var(--text-muted);">Module Progress</p>
                        </div>
                        <?php endif; ?>

                        <!-- Last Activity -->
                        <div class="flex items-center text-xs mb-4" style="color: var(--text-muted);">
                            <i class="fas fa-clock mr-1"></i>
                            <?php if ($course['last_accessed']): ?>
                                Last active <?= timeAgo($course['last_accessed']) ?>
                            <?php else: ?>
                                Enrolled <?= timeAgo($course['enrolled_at']) ?>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <?php if ($course['enrollment_status'] === 'Completed'): ?>
                            <a href="<?= url('course.php?id=' . $course['course_id']) ?>" 
                               class="flex-1 text-center py-2.5 font-medium btn-secondary">
                                <i class="fas fa-redo mr-1"></i>Review
                            </a>
                            <?php else: ?>
                            <a href="<?= url('learn.php?course=' . $course['slug']) ?>" 
                               class="flex-1 text-center py-2.5 font-medium transition btn-primary">
                                <i class="fas fa-play mr-1"></i>Continue
                            </a>
                            <?php endif; ?>
                            <a href="<?= url('course.php?id=' . $course['course_id']) ?>" 
                               class="py-2.5 px-3 btn-secondary">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state" style="background: var(--surface-secondary); border: 1px solid var(--border-primary); border-radius: var(--radius-xl); box-shadow: var(--shadow-card);">
                <div class="empty-state-icon" style="background: var(--surface-warm);">
                    <i class="fas fa-book-open text-3xl" style="color: var(--accent-secondary);"></i>
                </div>
                <?php
                $emptyTitle = match($status) {
                    'completed' => 'No Completed Courses Yet',
                    'active' => 'No Active Courses',
                    default => 'No Courses Yet'
                };
                $emptyMessage = match($status) {
                    'completed' => 'Complete a course to see it here!',
                    'active' => 'Start your learning journey by enrolling in a course.',
                    default => 'Start your learning journey by enrolling in a course.'
                };
                ?>
                <h3 class="text-xl font-bold mb-2" style="color: var(--text-primary);"><?= $emptyTitle ?></h3>
                <p class="mb-6" style="color: var(--text-secondary);"><?= $emptyMessage ?></p>
                <a href="<?= url('courses.php') ?>" class="inline-flex items-center btn-primary">
                    <i class="fas fa-search mr-2"></i>Browse Courses
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
