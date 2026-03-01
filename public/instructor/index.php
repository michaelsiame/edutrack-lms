<?php
/**
 * Instructor Dashboard
 * Modern dashboard with class management focus
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/User.php';
require_once '../../src/classes/Statistics.php';

$db = Database::getInstance();
$user = User::current();
if (!$user) {
    redirect(url('login.php'));
    exit;
}

$userId = $user->getId();

// Get instructor ID from instructors table
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : $userId;

// Get comprehensive instructor statistics
$stats = Statistics::getInstructorStats($instructorId);

// Get recent enrollments
$recentEnrollments = $db->fetchAll("
    SELECT e.*, c.title as course_title, c.slug as course_slug,
           u.first_name, u.last_name, u.email, u.avatar_url
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON e.user_id = u.id
    WHERE c.instructor_id = ?
    ORDER BY e.enrolled_at DESC
    LIMIT 6
", [$instructorId]);

// Get pending assignment submissions
$pendingAssignments = $db->fetchAll("
    SELECT asub.*, a.title as assignment_title, a.max_points, a.due_date,
           c.title as course_title, c.slug as course_slug,
           u.first_name, u.last_name, u.email
    FROM assignment_submissions asub
    JOIN assignments a ON asub.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN students st ON asub.student_id = st.id
    JOIN users u ON st.user_id = u.id
    WHERE c.instructor_id = ? AND asub.status = 'submitted'
    ORDER BY asub.submitted_at DESC
    LIMIT 5
", [$instructorId]);

// Get instructor's courses with detailed metrics
$courses = $db->fetchAll("
    SELECT c.*, cat.name as category_name,
           COUNT(DISTINCT e.id) as student_count,
           COUNT(DISTINCT m.id) as module_count,
           COUNT(DISTINCT l.id) as lesson_count,
           AVG(e.progress) as avg_progress
    FROM courses c
    LEFT JOIN course_categories cat ON c.category_id = cat.id
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN modules m ON c.id = m.course_id
    LEFT JOIN lessons l ON m.id = l.module_id
    WHERE c.instructor_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 4
", [$instructorId]);

// Get upcoming live sessions
$upcomingSessions = $db->fetchAll("
    SELECT ls.*, l.title as lesson_title, c.title as course_title, c.slug as course_slug
    FROM live_sessions ls
    JOIN lessons l ON ls.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE c.instructor_id = ? AND ls.status IN ('scheduled', 'live')
    AND ls.scheduled_start_time >= NOW()
    ORDER BY ls.scheduled_start_time ASC
    LIMIT 3
", [$instructorId]);

// Get recent quiz attempts
$recentQuizAttempts = $db->fetchAll("
    SELECT qa.*, q.title as quiz_title, c.title as course_title,
           u.first_name, u.last_name
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN lessons l ON q.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    JOIN students st ON qa.student_id = st.id
    JOIN users u ON st.user_id = u.id
    WHERE c.instructor_id = ? AND qa.status = 'completed'
    ORDER BY qa.completed_at DESC
    LIMIT 5
", [$instructorId]);

// Calculate revenue
$revenue = $db->fetchOne("
    SELECT
        COALESCE(SUM(CASE WHEN e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN c.price ELSE 0 END), 0) as monthly_revenue,
        COALESCE(SUM(c.price), 0) as total_revenue
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ? AND e.payment_status = 'completed'
", [$instructorId]);

$page_title = 'Dashboard';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Welcome Section with Date and Help -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Welcome back, <?= htmlspecialchars($user->first_name) ?>! 👋
                </h1>
                <p class="text-gray-500 mt-1">Here's what's happening with your classes today.</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center gap-4">
                <a href="<?= url('instructor/help.php') ?>" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm font-medium">
                    <i class="fas fa-question-circle mr-2 text-blue-500"></i>Help
                </a>
                <div class="text-right">
                    <p class="text-sm text-gray-500"><?= date('l, F j, Y') ?></p>
                    <p class="text-xs text-gray-400"><?= date('g:i A') ?></p>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Courses -->
            <div class="stat-card bg-white rounded-2xl p-6 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Courses</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?= $stats['total_courses'] ?? 0 ?></p>
                        <p class="text-xs text-green-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i><?= $stats['published_courses'] ?? 0 ?> Published
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-book text-blue-500 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Students -->
            <div class="stat-card bg-white rounded-2xl p-6 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Students</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?= $stats['total_students'] ?? 0 ?></p>
                        <p class="text-xs text-purple-600 mt-1">
                            <i class="fas fa-users mr-1"></i>Active learners
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-users text-purple-500 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Reviews -->
            <div class="stat-card bg-white rounded-2xl p-6 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pending Grading</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?= count($pendingAssignments) ?></p>
                        <p class="text-xs text-orange-600 mt-1">
                            <i class="fas fa-clock mr-1"></i>Needs attention
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-clipboard-check text-orange-500 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="stat-card bg-white rounded-2xl p-6 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Monthly Revenue</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">
                            K<?= number_format($revenue['monthly_revenue'] ?? 0, 0) ?>
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            Total: K<?= number_format($revenue['total_revenue'] ?? 0, 0) ?>
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-wallet text-green-500 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Main Content Column -->
            <div class="xl:col-span-2 space-y-8">

                <!-- Quick Actions Bar -->
                <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-6 text-white shadow-lg">
                    <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="<?= url('instructor/courses/create.php') ?>" 
                           class="flex flex-col items-center p-4 bg-white/10 rounded-xl hover:bg-white/20 transition text-center">
                            <i class="fas fa-plus-circle text-2xl mb-2"></i>
                            <span class="text-sm font-medium">New Course</span>
                        </a>
                        <a href="<?= url('instructor/live-sessions.php') ?>" 
                           class="flex flex-col items-center p-4 bg-white/10 rounded-xl hover:bg-white/20 transition text-center">
                            <i class="fas fa-video text-2xl mb-2"></i>
                            <span class="text-sm font-medium">Schedule Live</span>
                        </a>
                        <a href="<?= url('instructor/assignments.php') ?>" 
                           class="flex flex-col items-center p-4 bg-white/10 rounded-xl hover:bg-white/20 transition text-center">
                            <i class="fas fa-tasks text-2xl mb-2"></i>
                            <span class="text-sm font-medium">Grade Work</span>
                        </a>
                        <a href="<?= url('instructor/students.php') ?>" 
                           class="flex flex-col items-center p-4 bg-white/10 rounded-xl hover:bg-white/20 transition text-center">
                            <i class="fas fa-user-plus text-2xl mb-2"></i>
                            <span class="text-sm font-medium">View Students</span>
                        </a>
                    </div>
                </div>

                <!-- My Courses Section -->
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-3">
                                <i class="fas fa-book text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">My Courses</h2>
                                <p class="text-sm text-gray-500">Manage your active courses</p>
                            </div>
                        </div>
                        <a href="<?= url('instructor/courses.php') ?>" 
                           class="text-sm font-medium text-primary-600 hover:text-primary-700">
                            View All <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <?php if (empty($courses)): ?>
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-book-open text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Courses Yet</h3>
                        <p class="text-gray-500 mb-6">Create your first course to start teaching</p>
                        <a href="<?= url('instructor/courses/create.php') ?>" 
                           class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-500/30">
                            <i class="fas fa-plus mr-2"></i> Create Your First Course
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($courses as $course): ?>
                        <div class="p-6 hover:bg-gray-50/50 transition">
                            <div class="flex flex-col md:flex-row md:items-center gap-4">
                                <!-- Course Thumbnail -->
                                <div class="w-full md:w-48 h-28 rounded-xl overflow-hidden bg-gradient-to-br from-primary-400 to-primary-600 flex-shrink-0">
                                    <?php if (!empty($course['thumbnail_url'])): ?>
                                    <img src="<?= htmlspecialchars($course['thumbnail_url']) ?>" 
                                         alt="<?= htmlspecialchars($course['title']) ?>"
                                         class="w-full h-full object-cover">
                                    <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-book text-white text-3xl"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Course Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-1 rounded-lg text-xs font-medium <?= $course['status'] == 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                            <?= ucfirst($course['status']) ?>
                                        </span>
                                        <?php if ($course['category_name']): ?>
                                        <span class="text-xs text-gray-500"><?= htmlspecialchars($course['category_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="font-semibold text-gray-900 text-lg mb-1 truncate">
                                        <?= htmlspecialchars($course['title']) ?>
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                        <span><i class="fas fa-users text-purple-500 mr-1"></i><?= $course['student_count'] ?> students</span>
                                        <span><i class="fas fa-folder text-blue-500 mr-1"></i><?= $course['module_count'] ?> modules</span>
                                        <span><i class="fas fa-play-circle text-green-500 mr-1"></i><?= $course['lesson_count'] ?> lessons</span>
                                        <?php if ($course['avg_progress']): ?>
                                        <span><i class="fas fa-chart-line text-primary-500 mr-1"></i><?= round($course['avg_progress']) ?>% avg progress</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center gap-2">
                                    <a href="<?= url('instructor/course-edit.php?id=' . $course['id']) ?>"
                                       class="px-4 py-2 bg-primary-50 text-primary-600 rounded-lg hover:bg-primary-100 transition text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </a>
                                    <a href="<?= url('course.php?slug=' . $course['slug']) ?>"
                                       target="_blank"
                                       class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Pending Assignments Section -->
                <?php if (!empty($pendingAssignments)): ?>
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-orange-50/50">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center mr-3">
                                <i class="fas fa-clipboard-list text-orange-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Pending Submissions</h2>
                                <p class="text-sm text-gray-500">Assignments waiting for your review</p>
                            </div>
                        </div>
                        <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                            <?= count($pendingAssignments) ?> pending
                        </span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php foreach (array_slice($pendingAssignments, 0, 3) as $submission): 
                            $isLate = $submission['due_date'] && strtotime($submission['submitted_at']) > strtotime($submission['due_date']);
                        ?>
                        <div class="p-5 hover:bg-gray-50/50 transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <img src="<?= getGravatar($submission['email']) ?>" class="w-10 h-10 rounded-full">
                                    <div>
                                        <h4 class="font-medium text-gray-900"><?= htmlspecialchars($submission['assignment_title']) ?></h4>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($submission['course_title']) ?></p>
                                        <div class="flex items-center gap-3 mt-1 text-sm">
                                            <span class="text-gray-600">
                                                <i class="fas fa-user mr-1"></i>
                                                <?= htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']) ?>
                                            </span>
                                            <span class="text-gray-400">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?= timeAgo($submission['submitted_at']) ?>
                                            </span>
                                            <?php if ($isLate): ?>
                                            <span class="text-red-500 text-xs font-medium">
                                                <i class="fas fa-exclamation-circle mr-1"></i>Late
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <a href="<?= url('instructor/assignments.php?submission=' . $submission['id']) ?>"
                                   class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition text-sm font-medium whitespace-nowrap">
                                    <i class="fas fa-check-circle mr-1"></i>Grade
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($pendingAssignments) > 3): ?>
                    <div class="px-6 py-4 bg-gray-50 text-center">
                        <a href="<?= url('instructor/assignments.php') ?>" class="text-sm font-medium text-primary-600 hover:text-primary-700">
                            View all <?= count($pendingAssignments) ?> pending submissions <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>

            <!-- Sidebar Column -->
            <div class="space-y-8">

                <!-- Upcoming Live Sessions -->
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center mr-3">
                                <i class="fas fa-video text-red-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Upcoming Live Sessions</h3>
                                <p class="text-sm text-gray-500">Your scheduled classes</p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($upcomingSessions)): ?>
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-calendar text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-gray-500 text-sm">No upcoming sessions</p>
                        <a href="<?= url('instructor/live-sessions.php') ?>" class="text-primary-600 text-sm font-medium mt-2 inline-block">
                            Schedule one now
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($upcomingSessions as $session): 
                            $startTime = new DateTime($session['scheduled_start_time']);
                            $now = new DateTime();
                            $isLive = $session['status'] === 'live' || ($startTime <= $now && $session['status'] !== 'ended');
                        ?>
                        <div class="p-5 hover:bg-gray-50/50 transition">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 text-center min-w-[3.5rem]">
                                    <div class="text-xs font-semibold text-primary-600 uppercase"><?= $startTime->format('M') ?></div>
                                    <div class="text-2xl font-bold text-gray-900"><?= $startTime->format('j') ?></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 truncate"><?= htmlspecialchars($session['lesson_title']) ?></h4>
                                    <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars($session['course_title']) ?></p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-xs text-gray-500">
                                            <i class="far fa-clock mr-1"></i><?= $startTime->format('g:i A') ?> (<?= $session['duration_minutes'] ?> min)
                                        </span>
                                        <?php if ($isLive): ?>
                                        <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full animate-pulse">
                                            LIVE
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a href="<?= url('live-session.php?session_id=' . $session['id']) ?>" 
                                   target="_blank"
                                   class="flex-shrink-0 w-10 h-10 bg-primary-100 text-primary-600 rounded-xl flex items-center justify-center hover:bg-primary-200 transition">
                                    <i class="fas fa-play"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="px-6 py-4 bg-gray-50">
                        <a href="<?= url('instructor/live-sessions.php') ?>" class="text-sm font-medium text-primary-600 hover:text-primary-700 text-center block">
                            Manage all sessions
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Enrollments -->
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mr-3">
                                    <i class="fas fa-user-plus text-green-600"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Recent Enrollments</h3>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($recentEnrollments)): ?>
                    <div class="p-8 text-center">
                        <p class="text-gray-500 text-sm">No enrollments yet</p>
                    </div>
                    <?php else: ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($recentEnrollments as $enrollment): ?>
                        <div class="p-4 hover:bg-gray-50/50 transition">
                            <div class="flex items-center gap-3">
                                <img src="<?= getGravatar($enrollment['email']) ?>" class="w-10 h-10 rounded-full">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 text-sm truncate">
                                        <?= htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($enrollment['course_title']) ?></p>
                                </div>
                                <span class="text-xs text-gray-400 whitespace-nowrap">
                                    <?= timeAgo($enrollment['enrolled_at']) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="px-6 py-4 bg-gray-50">
                        <a href="<?= url('instructor/students.php') ?>" class="text-sm font-medium text-primary-600 hover:text-primary-700 text-center block">
                            View all students
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Links Card -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-6 text-white">
                    <h3 class="text-lg font-bold mb-4">
                        <i class="fas fa-bolt text-yellow-400 mr-2"></i>Quick Links
                    </h3>
                    <div class="space-y-3">
                        <a href="<?= url('instructor/courses/create.php') ?>" 
                           class="flex items-center p-3 bg-white/10 rounded-xl hover:bg-white/20 transition">
                            <i class="fas fa-plus w-6"></i>
                            <span>Create New Course</span>
                            <i class="fas fa-arrow-right ml-auto text-sm opacity-50"></i>
                        </a>
                        <a href="<?= url('instructor/quizzes.php') ?>" 
                           class="flex items-center p-3 bg-white/10 rounded-xl hover:bg-white/20 transition">
                            <i class="fas fa-question-circle w-6"></i>
                            <span>Manage Quizzes</span>
                            <i class="fas fa-arrow-right ml-auto text-sm opacity-50"></i>
                        </a>
                        <a href="<?= url('instructor/analytics.php') ?>" 
                           class="flex items-center p-3 bg-white/10 rounded-xl hover:bg-white/20 transition">
                            <i class="fas fa-chart-line w-6"></i>
                            <span>View Analytics</span>
                            <i class="fas fa-arrow-right ml-auto text-sm opacity-50"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
