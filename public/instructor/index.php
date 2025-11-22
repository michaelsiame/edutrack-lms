<?php
/**
 * Instructor Dashboard
 * Main dashboard showing overview, stats, and recent activities
 */

require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Statistics.php';

$user = User::current();
$instructorId = $user->getId();

// Get comprehensive instructor statistics
$stats = Statistics::getInstructorStats($instructorId);

// Get recent enrollments in instructor's courses
$recentEnrollments = $db->fetchAll("
    SELECT e.*, c.title as course_title, c.slug as course_slug,
           u.first_name, u.last_name, u.email
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON e.user_id = u.id
    WHERE c.instructor_id = ?
    ORDER BY e.enrolled_at DESC
    LIMIT 10
", [$instructorId]);

// Get pending assignment submissions
$pendingAssignments = $db->fetchAll("
    SELECT asub.*, a.title as assignment_title, a.max_points,
           c.title as course_title, c.slug as course_slug,
           u.first_name, u.last_name
    FROM assignment_submissions asub
    JOIN assignments a ON asub.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN students st ON asub.student_id = st.id
    JOIN users u ON st.user_id = u.id
    WHERE c.instructor_id = ? AND asub.status = 'Submitted'
    ORDER BY asub.submitted_at DESC
    LIMIT 10
", [$instructorId]);

// Get instructor's courses with enrollment count
$courses = $db->fetchAll("
    SELECT c.*, COUNT(DISTINCT e.id) as student_count,
           COUNT(DISTINCT m.id) as module_count,
           COUNT(DISTINCT l.id) as lesson_count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN course_modules m ON c.id = m.course_id
    LEFT JOIN lessons l ON m.id = l.module_id
    WHERE c.instructor_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 6
", [$instructorId]);

// Get recent reviews
$recentReviews = $db->fetchAll("
    SELECT cr.*, c.title as course_title, c.slug as course_slug,
           u.first_name, u.last_name
    FROM course_reviews cr
    JOIN courses c ON cr.course_id = c.id
    JOIN users u ON cr.user_id = u.id
    WHERE c.instructor_id = ?
    ORDER BY cr.created_at DESC
    LIMIT 5
", [$instructorId]);

// Calculate revenue (if applicable)
$revenue = $db->fetchOne("
    SELECT
        COALESCE(SUM(CASE WHEN e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN c.price ELSE 0 END), 0) as monthly_revenue,
        COALESCE(SUM(c.price), 0) as total_revenue
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ? AND e.payment_status = 'paid'
", [$instructorId]);

$page_title = 'Instructor Dashboard - Edutrack';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Welcome back, <?= htmlspecialchars($user->first_name) ?>!
            </h1>
            <p class="text-gray-600 mt-2">Here's what's happening with your courses today.</p>
        </div>

        <!-- Announcements -->
        <?php include '../../src/templates/announcements.php'; ?>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Courses -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Courses</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_courses'] ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-book text-blue-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-sm">
                    <span class="text-green-600 font-medium"><?= $stats['published_courses'] ?> Published</span>
                </div>
            </div>

            <!-- Total Students -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_students'] ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-users text-purple-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= url('instructor/students.php') ?>" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                        View all students →
                    </a>
                </div>
            </div>

            <!-- Pending Reviews -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending Grading</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= count($pendingAssignments) ?></p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-tasks text-orange-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= url('instructor/assignments.php') ?>" class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                        Review assignments →
                    </a>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Monthly Revenue</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            K<?= number_format($revenue['monthly_revenue'] ?? 0, 2) ?>
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    Total: K<?= number_format($revenue['total_revenue'] ?? 0, 2) ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">

                <!-- My Courses -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">My Courses</h2>
                        <a href="<?= url('instructor/courses.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">
                            View All
                        </a>
                    </div>

                    <?php if (empty($courses)): ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Courses Yet</h3>
                        <p class="text-gray-500 mb-6">Create your first course to start teaching</p>
                        <a href="<?= url('instructor/courses/create.php') ?>" class="btn-primary px-6 py-3 rounded-lg inline-block">
                            <i class="fas fa-plus mr-2"></i> Create Your First Course
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="p-6 space-y-4">
                        <?php foreach ($courses as $course): ?>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 border border-gray-200 rounded-lg hover:border-primary-300 transition">
                            <div class="w-full sm:w-20 h-32 sm:h-20 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center">
                                <?php if ($course['thumbnail']): ?>
                                <img src="<?= courseThumbnail($course['thumbnail']) ?>"
                                     alt="<?= htmlspecialchars($course['title']) ?>"
                                     class="w-full h-full object-cover rounded-lg">
                                <?php else: ?>
                                <i class="fas fa-book text-white text-3xl"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">
                                    <?= htmlspecialchars($course['title']) ?>
                                </h3>
                                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                                    <span>
                                        <i class="fas fa-users text-purple-500 mr-1"></i>
                                        <?= $course['student_count'] ?> students
                                    </span>
                                    <span>
                                        <i class="fas fa-book-open text-blue-500 mr-1"></i>
                                        <?= $course['lesson_count'] ?> lessons
                                    </span>
                                    <span class="px-2 py-1 rounded-full text-xs <?= $course['status'] == 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= ucfirst($course['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2 w-full sm:w-auto">
                                <a href="<?= url('instructor/course-edit.php?id=' . $course['id']) ?>"
                                   class="flex-1 sm:flex-initial text-center px-4 py-2 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition text-sm font-medium">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <a href="<?= url('course.php?slug=' . $course['slug']) ?>"
                                   class="flex-1 sm:flex-initial text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Pending Assignment Submissions -->
                <?php if (!empty($pendingAssignments)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-clipboard-list text-orange-500 mr-2"></i>
                            Pending Reviews
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php foreach (array_slice($pendingAssignments, 0, 5) as $submission): ?>
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">
                                        <?= htmlspecialchars($submission['assignment_title']) ?>
                                    </h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <?= htmlspecialchars($submission['course_title']) ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="fas fa-user mr-1"></i>
                                        <?= htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <i class="fas fa-clock mr-1"></i>
                                        Submitted <?= timeAgo($submission['submitted_at']) ?>
                                    </p>
                                </div>
                                <a href="<?= url('instructor/assignments.php?submission=' . $submission['id']) ?>"
                                   class="px-4 py-2 bg-orange-50 text-orange-600 rounded-md hover:bg-orange-100 transition text-sm font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>Review
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Recent Enrollments -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Recent Enrollments</h3>
                    </div>
                    <?php if (empty($recentEnrollments)): ?>
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-user-plus text-3xl mb-2"></i>
                        <p class="text-sm">No enrollments yet</p>
                    </div>
                    <?php else: ?>
                    <div class="divide-y divide-gray-200">
                        <?php foreach (array_slice($recentEnrollments, 0, 5) as $enrollment): ?>
                        <div class="p-4">
                            <p class="font-medium text-gray-900 text-sm">
                                <?= htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?>
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                                <?= htmlspecialchars($enrollment['course_title']) ?>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                <i class="fas fa-clock mr-1"></i>
                                <?= timeAgo($enrollment['enrolled_at']) ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Reviews -->
                <?php if (!empty($recentReviews)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Recent Reviews</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($recentReviews as $review): ?>
                        <div class="p-4">
                            <div class="flex items-center mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' ?> text-sm"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-sm text-gray-700 mb-2 line-clamp-2">
                                "<?= htmlspecialchars($review['review_text']) ?>"
                            </p>
                            <p class="text-xs text-gray-500">
                                - <?= htmlspecialchars($review['first_name']) ?> on <?= htmlspecialchars($review['course_title']) ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="bg-gradient-to-br from-primary-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
                    <h3 class="text-lg font-bold mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="<?= url('instructor/courses/create.php') ?>"
                           class="block w-full px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg transition text-center backdrop-blur-sm">
                            <i class="fas fa-plus mr-2"></i>Create New Course
                        </a>
                        <a href="<?= url('instructor/assignments.php') ?>"
                           class="block w-full px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg transition text-center backdrop-blur-sm">
                            <i class="fas fa-tasks mr-2"></i>Review Assignments
                        </a>
                        <a href="<?= url('instructor/students.php') ?>"
                           class="block w-full px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg transition text-center backdrop-blur-sm">
                            <i class="fas fa-users mr-2"></i>View Students
                        </a>
                        <a href="<?= url('instructor/analytics.php') ?>"
                           class="block w-full px-4 py-3 bg-white/20 hover:bg-white/30 rounded-lg transition text-center backdrop-blur-sm">
                            <i class="fas fa-chart-line mr-2"></i>View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../../src/templates/footer.php'; ?>
