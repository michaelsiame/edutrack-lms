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

$enrollments = $db->fetchAll("
    SELECT e.*, c.title, c.slug, c.thumbnail_url, c.description, c.price,
           c.instructor_id, c.duration_weeks, c.total_hours, c.level,
           CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
           e.enrolled_at, e.last_accessed, e.progress as progress_percentage, e.enrollment_status,
           COUNT(DISTINCT l.id) as total_lessons,
           COUNT(DISTINCT CASE WHEN lp.status = 'Completed' THEN lp.lesson_id END) as completed_lessons,
           COUNT(DISTINCT a.id) as total_assignments,
           COUNT(DISTINCT asub.id) as submitted_assignments,
           COUNT(DISTINCT q.id) as total_quizzes,
           COUNT(DISTINCT qa.id) as attempted_quizzes,
           MAX(lp.updated_at) as last_lesson_date
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN users u ON i.user_id = u.id
    LEFT JOIN modules m ON c.id = m.course_id
    LEFT JOIN lessons l ON m.id = l.module_id
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.enrollment_id = e.id
    LEFT JOIN assignments a ON c.id = a.course_id
    LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.student_id = e.student_id
    LEFT JOIN quizzes q ON c.id = q.course_id AND q.is_published = 1
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.student_id = e.student_id
    WHERE e.user_id = ? $statusFilter
    GROUP BY e.id, c.id
    $sortSql
", [$userId]);

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

// Count by status
$counts = [
    'all' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ?", [$userId]),
    'active' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND enrollment_status IN ('Enrolled', 'In Progress')", [$userId]),
    'completed' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND enrollment_status = 'Completed'", [$userId])
];

// Get overall stats for header
$totalProgress = $db->fetchColumn("SELECT AVG(progress) FROM enrollments WHERE user_id = ?", [$userId]) ?? 0;
$certificatesCount = $db->fetchColumn("
    SELECT COUNT(*) FROM certificates cert
    JOIN enrollments e ON cert.enrollment_id = e.id
    WHERE e.user_id = ?
", [$userId]) ?? 0;

$page_title = "My Courses - Edutrack";
require_once __DIR__ . '/../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header with Overall Stats -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Learning</h1>
                    <p class="text-gray-600 mt-2">Track your progress and continue your learning journey</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-white rounded-lg shadow-sm border px-4 py-2">
                        <span class="text-sm text-gray-500">Overall Progress</span>
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: <?= round($totalProgress) ? round($totalProgress) : 0 ?>%"></div>
                            </div>
                            <span class="font-bold text-gray-800"><?= round($totalProgress) ?>%</span>
                        </div>
                    </div>
                    <?php if ($certificatesCount > 0): ?>
                    <div class="bg-white rounded-lg shadow-sm border px-4 py-2">
                        <span class="text-sm text-gray-500">Certificates</span>
                        <p class="font-bold text-green-600"><?= $certificatesCount ?> <i class="fas fa-certificate text-sm"></i></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Filters & Controls -->
        <div class="bg-white rounded-xl shadow-sm border mb-6">
            <div class="p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Status Tabs -->
                <div class="flex flex-wrap gap-2">
                    <a href="?status=all&view=<?= $view ?>&sort=<?= $sort ?>" 
                       class="px-4 py-2 rounded-lg font-medium transition <?= $status === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                        All <span class="ml-1 opacity-75">(<?= $counts['all'] ?>)</span>
                    </a>
                    <a href="?status=active&view=<?= $view ?>&sort=<?= $sort ?>" 
                       class="px-4 py-2 rounded-lg font-medium transition <?= $status === 'active' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                        In Progress <span class="ml-1 opacity-75">(<?= $counts['active'] ?>)</span>
                    </a>
                    <a href="?status=completed&view=<?= $view ?>&sort=<?= $sort ?>" 
                       class="px-4 py-2 rounded-lg font-medium transition <?= $status === 'completed' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                        Completed <span class="ml-1 opacity-75">(<?= $counts['completed'] ?>)</span>
                    </a>
                </div>

                <!-- View & Sort Controls -->
                <div class="flex items-center gap-3">
                    <!-- Sort Dropdown -->
                    <select onchange="window.location.href='?status=<?= $status ?>&view=<?= $view ?>&sort='+this.value" 
                            class="px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Recently Accessed</option>
                        <option value="progress" <?= $sort === 'progress' ? 'selected' : '' ?>>Progress (High to Low)</option>
                        <option value="alphabetical" <?= $sort === 'alphabetical' ? 'selected' : '' ?>>Alphabetical</option>
                    </select>

                    <!-- View Toggle -->
                    <div class="flex border rounded-lg overflow-hidden">
                        <a href="?status=<?= $status ?>&view=grid&sort=<?= $sort ?>" 
                           class="px-3 py-2 <?= $view === 'grid' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' ?>">
                            <i class="fas fa-th-large"></i>
                        </a>
                        <a href="?status=<?= $status ?>&view=list&sort=<?= $sort ?>" 
                           class="px-3 py-2 <?= $view === 'list' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' ?>">
                            <i class="fas fa-list"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($enrollments)): ?>
            
            <?php if ($view === 'list'): ?>
            <!-- List View -->
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="divide-y divide-gray-100">
                    <?php foreach ($enrollments as $course): ?>
                    <div class="p-6 hover:bg-gray-50 transition">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <!-- Thumbnail -->
                            <div class="lg:w-48 shrink-0">
                                <div class="relative rounded-xl overflow-hidden">
                                    <img src="<?= courseThumbnail($course['thumbnail_url']) ?>" 
                                         alt="" class="w-full h-32 object-cover">
                                    <?php if ($course['enrollment_status'] === 'Completed'): ?>
                                    <div class="absolute inset-0 bg-green-500/90 flex items-center justify-center">
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
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded">
                                                <?= $course['level'] ?>
                                            </span>
                                            <?php if ($course['enrollment_status'] === 'Completed'): ?>
                                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded">
                                                <i class="fas fa-certificate mr-1"></i>Certificate Available
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900 mb-1"><?= sanitize($course['title']) ?></h3>
                                        <p class="text-sm text-gray-500">Instructor: <?= sanitize($course['instructor_name']) ?></p>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex items-center gap-2">
                                        <?php if ($course['enrollment_status'] === 'Completed'): ?>
                                        <a href="<?= url('my-certificates.php') ?>" 
                                           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                                            <i class="fas fa-certificate mr-2"></i>Certificate
                                        </a>
                                        <?php else: ?>
                                        <a href="<?= url('learn.php?course=' . $course['slug']) ?>" 
                                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                            <i class="fas fa-play mr-2"></i>Continue
                                        </a>
                                        <?php endif; ?>
                                        <a href="<?= url('course.php?id=' . $course['course_id']) ?>" 
                                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Progress Section -->
                                <div class="mt-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-600">Course Progress</span>
                                        <span class="text-lg font-bold text-blue-600"><?= round($course['progress_percentage']) ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all" 
                                             style="width: <?= round($course['progress_percentage']) ? round($course['progress_percentage']) : 0 ?>%"></div>
                                    </div>

                                    <!-- Stats Grid -->
                                    <div class="grid grid-cols-3 md:grid-cols-6 gap-4 text-sm">
                                        <div class="bg-blue-50 rounded-lg p-3 text-center">
                                            <p class="font-bold text-blue-700"><?= $course['completed_lessons'] ?>/<?= $course['total_lessons'] ?></p>
                                            <p class="text-xs text-gray-500">Lessons</p>
                                        </div>
                                        <div class="bg-green-50 rounded-lg p-3 text-center">
                                            <p class="font-bold text-green-700"><?= $course['submitted_assignments'] ?>/<?= $course['total_assignments'] ?></p>
                                            <p class="text-xs text-gray-500">Assignments</p>
                                        </div>
                                        <div class="bg-purple-50 rounded-lg p-3 text-center">
                                            <p class="font-bold text-purple-700"><?= $course['attempted_quizzes'] ?>/<?= $course['total_quizzes'] ?></p>
                                            <p class="text-xs text-gray-500">Quizzes</p>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                                            <p class="font-bold text-gray-700"><?= $course['duration_weeks'] ?: 'N/A' ?></p>
                                            <p class="text-xs text-gray-500">Weeks</p>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                                            <p class="font-bold text-gray-700"><?= $course['total_hours'] ?: 'N/A' ?></p>
                                            <p class="text-xs text-gray-500">Hours</p>
                                        </div>
                                        <div class="bg-amber-50 rounded-lg p-3 text-center">
                                            <p class="font-bold text-amber-700"><?= isset($course['estimated_weeks_left']) ? $course['estimated_weeks_left'] . 'w' : 'N/A' ?></p>
                                            <p class="text-xs text-gray-500">Est. Left</p>
                                        </div>
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
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-lg transition group">
                    <!-- Thumbnail -->
                    <div class="relative h-48">
                        <img src="<?= courseThumbnail($course['thumbnail_url']) ?>" 
                             alt="" class="w-full h-full object-cover">
                        
                        <!-- Completion Overlay -->
                        <?php if ($course['enrollment_status'] === 'Completed'): ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-green-600/90 to-green-500/70 flex items-center justify-center">
                            <div class="text-center text-white">
                                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-check text-3xl"></i>
                                </div>
                                <p class="font-bold text-lg">COMPLETED</p>
                                <a href="<?= url('my-certificates.php') ?>" class="inline-flex items-center mt-2 text-sm bg-white text-green-700 px-3 py-1 rounded-full font-medium">
                                    <i class="fas fa-certificate mr-1"></i>View Certificate
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Level Badge -->
                        <div class="absolute top-3 left-3">
                            <span class="px-2 py-1 bg-white/90 text-gray-700 text-xs font-medium rounded-lg">
                                <?= $course['level'] ?>
                            </span>
                        </div>

                        <!-- Progress Circle -->
                        <?php if ($course['enrollment_status'] !== 'Completed'): ?>
                        <div class="absolute bottom-3 right-3">
                            <div class="w-14 h-14 bg-white rounded-full shadow-lg flex items-center justify-center">
                                <span class="text-sm font-bold text-blue-600"><?= round($course['progress_percentage']) ?>%</span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Content -->
                    <div class="p-5">
                        <h3 class="font-bold text-gray-800 text-lg mb-1 line-clamp-1"><?= sanitize($course['title']) ?></h3>
                        <p class="text-sm text-gray-500 mb-3"><?= sanitize($course['instructor_name']) ?></p>

                        <!-- Progress Bar -->
                        <?php if ($course['enrollment_status'] !== 'Completed'): ?>
                        <div class="mb-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all" 
                                     style="width: <?= round($course['progress_percentage']) ? round($course['progress_percentage']) : 0 ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-3 gap-2 mb-4 text-center text-sm">
                            <div class="bg-blue-50 rounded-lg p-2">
                                <p class="font-bold text-blue-700"><?= $course['completed_lessons'] ?></p>
                                <p class="text-xs text-gray-500">Lessons</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-2">
                                <p class="font-bold text-green-700"><?= $course['submitted_assignments'] ?></p>
                                <p class="text-xs text-gray-500">Tasks</p>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-2">
                                <p class="font-bold text-purple-700"><?= $course['attempted_quizzes'] ?></p>
                                <p class="text-xs text-gray-500">Quizzes</p>
                            </div>
                        </div>

                        <!-- Module Progress Dots -->
                        <?php if (!empty($course['modules']) && count($course['modules']) <= 8): ?>
                        <div class="mb-4">
                            <div class="flex gap-1 justify-center">
                                <?php foreach ($course['modules'] as $module):
                                    $modProgress = $module['total_lessons'] > 0 ? ($module['completed_lessons'] / $module['total_lessons']) * 100 : 0;
                                    $dotClass = $modProgress == 100 ? 'bg-green-500' : ($modProgress > 0 ? 'bg-blue-500' : 'bg-gray-300');
                                ?>
                                <div class="group relative">
                                    <div class="w-3 h-3 rounded-full <?= $dotClass ?>" title="<?= sanitize($module['title']) ?>"></div>
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition whitespace-nowrap z-10">
                                        <?= sanitize($module['title']) ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <p class="text-xs text-center text-gray-400 mt-1">Module Progress</p>
                        </div>
                        <?php endif; ?>

                        <!-- Last Activity -->
                        <div class="flex items-center text-xs text-gray-500 mb-4">
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
                               class="flex-1 text-center py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                                <i class="fas fa-redo mr-1"></i>Review
                            </a>
                            <?php else: ?>
                            <a href="<?= url('learn.php?course=' . $course['slug']) ?>" 
                               class="flex-1 text-center py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                <i class="fas fa-play mr-1"></i>Continue
                            </a>
                            <?php endif; ?>
                            <a href="<?= url('course.php?id=' . $course['course_id']) ?>" 
                               class="py-2.5 px-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
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
            <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-book-open text-blue-600 text-3xl"></i>
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
                <h3 class="text-xl font-bold text-gray-800 mb-2"><?= $emptyTitle ?></h3>
                <p class="text-gray-600 mb-6"><?= $emptyMessage ?></p>
                <a href="<?= url('courses.php') ?>" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-search mr-2"></i>Browse Courses
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
