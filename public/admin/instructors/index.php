<?php
/**
 * Instructor Dashboard
 */

require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/User.php';
require_once '../../src/classes/Course.php';

$user = User::current();
$instructorId = $user->getId();

// Get instructor statistics
$stats = [
    'total_courses' => $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE instructor_id = ?", [$instructorId]),
    'published_courses' => $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE instructor_id = ? AND status = 'published'", [$instructorId]),
    'total_students' => $db->fetchColumn("
        SELECT COUNT(DISTINCT e.user_id) 
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE c.instructor_id = ?
    ", [$instructorId]),
    'total_revenue' => $db->fetchColumn("
        SELECT SUM(p.amount)
        FROM payments p
        JOIN courses c ON p.course_id = c.id
        WHERE c.instructor_id = ? AND p.payment_status = 'Completed'
    ", [$instructorId]) ?? 0,
];

// Get instructor's courses
$myCourses = Course::all(['instructor_id' => $instructorId, 'limit' => 5]);

// Recent enrollments
$recentEnrollments = $db->fetchAll("
    SELECT e.*, u.first_name, u.last_name, u.email, c.title as course_title, c.slug as course_slug
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY e.enrolled_at DESC
    LIMIT 10
", [$instructorId]);

// Pending assignment submissions
$pendingSubmissions = $db->fetchAll("
    SELECT
        s.id, s.submitted_at, s.status,
        u.first_name, u.last_name,
        a.title as assignment_title,
        c.title as course_title
    FROM assignment_submissions s
    JOIN students st ON s.student_id = st.id
    JOIN users u ON st.user_id = u.id
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    WHERE c.instructor_id = ? AND s.status = 'Submitted'
    ORDER BY s.submitted_at ASC
    LIMIT 10
", [$instructorId]);

$page_title = 'Instructor Dashboard';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-chalkboard-teacher text-primary-600 mr-2"></i>
                Instructor Dashboard
            </h1>
            <p class="text-gray-600 mt-1">Welcome back, <?= sanitize($user->first_name) ?>!</p>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">My Courses</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_courses'] ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?= $stats['published_courses'] ?> published</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-book text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= number_format($stats['total_students']) ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-users text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= formatCurrency($stats['total_revenue']) ?></p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-dollar-sign text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending Reviews</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?= count($pendingSubmissions) ?></p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fas fa-clipboard-check text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- My Courses -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900">My Courses</h2>
                        <a href="<?= url('instructor/courses/create.php') ?>" class="text-sm text-primary-600 hover:text-primary-700">
                            <i class="fas fa-plus mr-1"></i>Create New
                        </a>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php if (empty($myCourses)): ?>
                            <div class="p-12 text-center text-gray-500">
                                <i class="fas fa-book text-4xl mb-3"></i>
                                <p>No courses yet</p>
                                <a href="<?= url('instructor/courses/create.php') ?>" class="text-primary-600 hover:text-primary-700 mt-2 inline-block">
                                    Create your first course
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($myCourses as $courseData): ?>
                                <?php $course = new Course($courseData['id']); ?>
                                <div class="p-4 hover:bg-gray-50">
                                    <div class="flex items-center space-x-4">
                                        <img src="<?= courseThumbnail($course->getThumbnail()) ?>" 
                                             alt="<?= sanitize($course->getTitle()) ?>"
                                             class="h-16 w-20 object-cover rounded">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900"><?= sanitize($course->getTitle()) ?></h3>
                                            <div class="flex items-center space-x-4 text-sm text-gray-600 mt-1">
                                                <span><i class="fas fa-users mr-1"></i><?= $course->getStudentCount() ?> students</span>
                                                <span><?= ucfirst($course->getStatus()) ?></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="<?= url('instructor/courses/edit.php?id=' . $course->getId()) ?>" 
                                               class="px-3 py-1 bg-primary-600 text-white text-sm rounded hover:bg-primary-700">
                                                Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="p-4 text-center">
                                <a href="<?= url('instructor/courses/index.php') ?>" class="text-primary-600 hover:text-primary-700 text-sm">
                                    View all courses →
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Enrollments -->
                <div class="bg-white rounded-lg shadow mt-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Recent Enrollments</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php if (empty($recentEnrollments)): ?>
                            <div class="p-8 text-center text-gray-500">
                                <p>No enrollments yet</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentEnrollments as $enrollment): ?>
                                <div class="p-4 hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900"><?= sanitize($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?></p>
                                            <p class="text-sm text-gray-600"><?= sanitize($enrollment['course_title']) ?></p>
                                        </div>
                                        <span class="text-xs text-gray-500"><?= timeAgo($enrollment['enrolled_at']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Pending Submissions -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Pending Reviews</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php if (empty($pendingSubmissions)): ?>
                            <div class="p-8 text-center text-gray-500">
                                <i class="fas fa-check-circle text-4xl mb-2 text-green-500"></i>
                                <p class="text-sm">All caught up!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($pendingSubmissions, 0, 5) as $submission): ?>
                                <div class="p-4 hover:bg-gray-50">
                                    <p class="text-sm font-medium text-gray-900"><?= sanitize($submission['assignment_title']) ?></p>
                                    <p class="text-xs text-gray-600"><?= sanitize($submission['first_name'] . ' ' . $submission['last_name']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?= timeAgo($submission['submitted_at']) ?></p>
                                    <a href="<?= url('instructor/assignments/grade.php?id=' . $submission['id']) ?>" 
                                       class="text-xs text-primary-600 hover:text-primary-700 mt-2 inline-block">
                                        Review →
                                    </a>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($pendingSubmissions) > 5): ?>
                                <div class="p-4 text-center">
                                    <a href="<?= url('instructor/assignments/index.php?status=submitted') ?>" 
                                       class="text-sm text-primary-600 hover:text-primary-700">
                                        View all (<?= count($pendingSubmissions) ?>) →
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow mt-6 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="<?= url('instructor/courses/create.php') ?>" 
                           class="block w-full bg-primary-600 text-white text-center px-4 py-2 rounded hover:bg-primary-700">
                            <i class="fas fa-plus mr-2"></i>Create Course
                        </a>
                        <a href="<?= url('instructor/students/index.php') ?>" 
                           class="block w-full bg-gray-600 text-white text-center px-4 py-2 rounded hover:bg-gray-700">
                            <i class="fas fa-users mr-2"></i>View Students
                        </a>
                        <a href="<?= url('instructor/analytics/index.php') ?>" 
                           class="block w-full bg-green-600 text-white text-center px-4 py-2 rounded hover:bg-green-700">
                            <i class="fas fa-chart-line mr-2"></i>Analytics
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../../src/templates/instructor-footer.php'; ?>