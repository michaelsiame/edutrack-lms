<?php
/**
 * Edutrack computer training college
 * My Courses Page
 */

require_once '../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user
$user = User::current();
$userId = $user->getId();

// Get filter
$status = $_GET['status'] ?? 'all';

// Get enrollments with detailed statistics
$statusFilter = $status === 'all' ? '' : "AND e.status = ?";
$params = $status === 'all' ? [$userId] : [$userId, $status];

$enrollments = $db->fetchAll("
    SELECT e.*, c.title, c.slug, c.thumbnail, c.description, c.price,
           c.instructor_id,
           u.first_name as instructor_first_name, u.last_name as instructor_last_name,
           e.enrolled_at, e.last_accessed, e.progress_percentage, e.status as enrollment_status,
           COUNT(DISTINCT l.id) as total_lessons,
           COUNT(DISTINCT lp.lesson_id) as completed_lessons,
           COUNT(DISTINCT a.id) as total_assignments,
           COUNT(DISTINCT asub.id) as submitted_assignments,
           COUNT(DISTINCT q.id) as total_quizzes,
           COUNT(DISTINCT qa.id) as attempted_quizzes
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN users u ON c.instructor_id = u.id
    LEFT JOIN modules m ON c.id = m.course_id
    LEFT JOIN lessons l ON m.id = l.module_id
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = e.user_id AND lp.status = 'completed'
    LEFT JOIN assignments a ON c.id = a.course_id AND a.status = 'published'
    LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.user_id = e.user_id
    LEFT JOIN quizzes q ON c.id = q.course_id AND q.status = 'published'
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.user_id = e.user_id
    WHERE e.user_id = ? $statusFilter
    GROUP BY e.id, c.id
    ORDER BY e.last_accessed DESC, e.enrolled_at DESC
", $params);

// Count by status
$counts = [
    'all' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ?", [$userId]),
    'active' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND status = 'active'", [$userId]),
    'completed' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND status = 'completed'", [$userId])
];

$page_title = "My Courses - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Courses</h1>
            <p class="text-gray-600 mt-2">Track your learning progress and continue where you left off</p>
        </div>
        
        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="flex flex-col sm:flex-row border-b border-gray-200">
                <a href="?status=all" 
                   class="flex-1 px-6 py-4 text-center font-medium transition <?= $status === 'all' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' ?>">
                    All Courses
                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?= $status === 'all' ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $counts['all'] ?>
                    </span>
                </a>
                <a href="?status=active" 
                   class="flex-1 px-6 py-4 text-center font-medium transition <?= $status === 'active' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' ?>">
                    In Progress
                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?= $status === 'active' ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $counts['active'] ?>
                    </span>
                </a>
                <a href="?status=completed" 
                   class="flex-1 px-6 py-4 text-center font-medium transition <?= $status === 'completed' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' ?>">
                    Completed
                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?= $status === 'completed' ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $counts['completed'] ?>
                    </span>
                </a>
            </div>
        </div>
        
        <?php if (!empty($enrollments)): ?>
            <!-- Courses Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($enrollments as $enrollment): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <!-- Thumbnail -->
                        <div class="relative h-48 bg-gray-200">
                            <img src="<?= courseThumbnail($enrollment['thumbnail']) ?>" 
                                 alt="<?= sanitize($enrollment['title']) ?>"
                                 class="w-full h-full object-cover">
                            
                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                <?php if ($enrollment['enrollment_status'] === 'completed'): ?>
                                    <span class="px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i>Completed
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-primary-500 text-white text-xs font-semibold rounded-full">
                                        <i class="fas fa-play-circle mr-1"></i>In Progress
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                                <?= sanitize($enrollment['title']) ?>
                            </h3>

                            <p class="text-xs text-gray-500 mb-4">
                                Instructor: <?= sanitize($enrollment['instructor_first_name'] . ' ' . $enrollment['instructor_last_name']) ?>
                            </p>

                            <!-- Overall Progress -->
                            <div class="mb-4">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-600 font-medium">Overall Progress</span>
                                    <span class="font-bold text-primary-600">
                                        <?= round($enrollment['progress_percentage']) ?>%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-3 rounded-full transition-all shadow-sm"
                                         style="width: <?= round($enrollment['progress_percentage']) ?>%"></div>
                                </div>
                            </div>

                            <!-- Detailed Stats Grid -->
                            <div class="grid grid-cols-3 gap-2 mb-4 text-xs">
                                <!-- Lessons -->
                                <div class="bg-blue-50 rounded-md p-2 text-center">
                                    <div class="text-blue-600 font-bold">
                                        <?= $enrollment['completed_lessons'] ?>/<?= $enrollment['total_lessons'] ?>
                                    </div>
                                    <div class="text-gray-600 mt-1">
                                        <i class="fas fa-play-circle mr-1"></i>Lessons
                                    </div>
                                </div>

                                <!-- Assignments -->
                                <div class="bg-green-50 rounded-md p-2 text-center">
                                    <div class="text-green-600 font-bold">
                                        <?= $enrollment['submitted_assignments'] ?>/<?= $enrollment['total_assignments'] ?>
                                    </div>
                                    <div class="text-gray-600 mt-1">
                                        <i class="fas fa-file-alt mr-1"></i>Tasks
                                    </div>
                                </div>

                                <!-- Quizzes -->
                                <div class="bg-purple-50 rounded-md p-2 text-center">
                                    <div class="text-purple-600 font-bold">
                                        <?= $enrollment['attempted_quizzes'] ?>/<?= $enrollment['total_quizzes'] ?>
                                    </div>
                                    <div class="text-gray-600 mt-1">
                                        <i class="fas fa-question-circle mr-1"></i>Quizzes
                                    </div>
                                </div>
                            </div>

                            <!-- Last Activity -->
                            <div class="text-xs text-gray-500 mb-4 flex items-center">
                                <i class="fas fa-clock mr-1"></i>
                                <?php if ($enrollment['last_accessed']): ?>
                                    Last active <?= timeAgo($enrollment['last_accessed']) ?>
                                <?php else: ?>
                                    Enrolled <?= timeAgo($enrollment['enrolled_at']) ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <?php if ($enrollment['enrollment_status'] === 'completed'): ?>
                                    <a href="<?= url('my-certificates.php') ?>" 
                                       class="flex-1 text-center py-2 px-4 bg-green-50 text-green-600 rounded-md hover:bg-green-100 transition font-medium text-sm">
                                        <i class="fas fa-certificate mr-1"></i>Certificate
                                    </a>
                                    <a href="<?= url('course.php?id=' . $enrollment['course_id']) ?>" 
                                       class="flex-1 text-center py-2 px-4 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition font-medium text-sm">
                                        <i class="fas fa-eye mr-1"></i>Review
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('learn.php?course=' . $enrollment['slug']) ?>" 
                                       class="flex-1 text-center py-2 px-4 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition font-medium text-sm">
                                        <i class="fas fa-play mr-1"></i>Continue
                                    </a>
                                    <a href="<?= url('course.php?id=' . $enrollment['course_id']) ?>" 
                                       class="py-2 px-4 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-12">
                <?php
                $emptyTitle = $status === 'completed' ? 'No Completed Courses Yet' : ($status === 'active' ? 'No Active Courses' : 'No Courses Yet');
                $emptyMessage = $status === 'completed' ? 'Complete a course to see it here' : 'Start your learning journey by enrolling in a course';
                emptyState(
                    'fa-book-open',
                    $emptyTitle,
                    $emptyMessage,
                    url('courses.php'),
                    'Browse Courses'
                );
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>