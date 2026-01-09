<?php
/**
 * Student Assignments Page
 * View all assignments and their status
 */

require_once '../../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Build filter condition
$filterCondition = '';
$params = [$userId];
if ($filter === 'pending') {
    $filterCondition = 'AND a.id NOT IN (SELECT assignment_id FROM assignment_submissions WHERE user_id = ?)';
    $params[] = $userId;
} elseif ($filter === 'submitted') {
    $filterCondition = 'AND asub.status = "Submitted"';
} elseif ($filter === 'graded') {
    $filterCondition = 'AND asub.status = "Graded"';
} elseif ($filter === 'overdue') {
    $filterCondition = 'AND a.due_date < NOW() AND a.id NOT IN (SELECT assignment_id FROM assignment_submissions WHERE user_id = ?)';
    $params[] = $userId;
}

// Get all assignments from enrolled courses
$assignments = $db->fetchAll("
    SELECT a.*,
           c.title as course_title, c.slug as course_slug,
           asub.id as submission_id,
           asub.status as submission_status,
           asub.submitted_at,
           asub.points_earned,
           asub.feedback,
           asub.graded_at,
           asub.file_path as submission_file,
           e.id as enrollment_id
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
    LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.user_id = ?
    WHERE 1=1 $filterCondition
    ORDER BY a.due_date ASC, a.created_at DESC
", array_merge([$userId, $userId], array_slice($params, 1)));

// Count assignments by status
$counts = [
    'all' => count($db->fetchAll("
        SELECT a.id FROM assignments a
        JOIN courses c ON a.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
    ", [$userId])),
    'pending' => count($db->fetchAll("
        SELECT a.id FROM assignments a
        JOIN courses c ON a.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
        WHERE a.id NOT IN (SELECT assignment_id FROM assignment_submissions WHERE user_id = ?)
    ", [$userId, $userId])),
    'submitted' => count($db->fetchAll("
        SELECT a.id FROM assignments a
        JOIN courses c ON a.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
        JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.user_id = ?
        WHERE asub.status = 'Submitted'
    ", [$userId, $userId])),
    'graded' => count($db->fetchAll("
        SELECT a.id FROM assignments a
        JOIN courses c ON a.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
        JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.user_id = ?
        WHERE asub.status = 'Graded'
    ", [$userId, $userId]))
];

$page_title = "My Assignments - Edutrack";
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-file-alt text-primary-600 mr-3"></i>
                My Assignments
            </h1>
            <p class="text-gray-600 mt-2">View and submit assignments for your courses</p>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="flex flex-col sm:flex-row border-b border-gray-200">
                <a href="?filter=all"
                   class="flex-1 px-6 py-4 text-center font-medium transition <?= $filter === 'all' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' ?>">
                    All Assignments
                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?= $filter === 'all' ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $counts['all'] ?>
                    </span>
                </a>
                <a href="?filter=pending"
                   class="flex-1 px-6 py-4 text-center font-medium transition <?= $filter === 'pending' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' ?>">
                    Pending
                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?= $filter === 'pending' ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $counts['pending'] ?>
                    </span>
                </a>
                <a href="?filter=submitted"
                   class="flex-1 px-6 py-4 text-center font-medium transition <?= $filter === 'submitted' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' ?>">
                    Submitted
                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?= $filter === 'submitted' ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $counts['submitted'] ?>
                    </span>
                </a>
                <a href="?filter=graded"
                   class="flex-1 px-6 py-4 text-center font-medium transition <?= $filter === 'graded' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' ?>">
                    Graded
                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?= $filter === 'graded' ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $counts['graded'] ?>
                    </span>
                </a>
            </div>
        </div>

        <?php if (!empty($assignments)): ?>
            <!-- Assignments List -->
            <div class="space-y-4">
                <?php foreach ($assignments as $assignment): ?>
                    <?php
                    $isOverdue = $assignment['due_date'] && strtotime($assignment['due_date']) < time() && !$assignment['submission_id'];
                    $isGraded = $assignment['submission_status'] === 'Graded';
                    $isSubmitted = $assignment['submission_status'] === 'Submitted';
                    $scorePercentage = $isGraded && $assignment['max_points'] > 0 ? ($assignment['points_earned'] / $assignment['max_points']) * 100 : 0;
                    ?>
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-xl font-bold text-gray-900">
                                        <?= sanitize($assignment['title']) ?>
                                    </h3>

                                    <!-- Status Badge -->
                                    <?php if ($isGraded): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                            <i class="fas fa-check-circle mr-1"></i>Graded
                                        </span>
                                    <?php elseif ($isSubmitted): ?>
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                            <i class="fas fa-clock mr-1"></i>Awaiting Review
                                        </span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                                            <i class="fas fa-exclamation-circle mr-1"></i>Overdue
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                            <i class="fas fa-file-alt mr-1"></i>Pending
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-sm text-gray-600 mb-3">
                                    <i class="fas fa-book mr-1"></i><?= sanitize($assignment['course_title']) ?>
                                </p>

                                <p class="text-gray-700 mb-4"><?= sanitize($assignment['description']) ?></p>

                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                    <?php if ($assignment['due_date']): ?>
                                        <div class="flex items-center <?= $isOverdue ? 'text-red-600 font-semibold' : '' ?>">
                                            <i class="fas fa-calendar mr-2"></i>
                                            Due: <?= formatDate($assignment['due_date']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex items-center">
                                        <i class="fas fa-star mr-2"></i>
                                        <?= $assignment['max_points'] ?> points
                                    </div>

                                    <?php if ($isSubmitted): ?>
                                        <div class="flex items-center text-blue-600">
                                            <i class="fas fa-check mr-2"></i>
                                            Submitted <?= timeAgo($assignment['submitted_at']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($isGraded): ?>
                                        <div class="flex items-center <?= $scorePercentage >= 70 ? 'text-green-600' : 'text-red-600' ?> font-semibold">
                                            <i class="fas fa-trophy mr-2"></i>
                                            Score: <?= $assignment['points_earned'] ?>/<?= $assignment['max_points'] ?> (<?= round($scorePercentage) ?>%)
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Feedback -->
                                <?php if ($isGraded && $assignment['feedback']): ?>
                                    <div class="mt-4 p-4 bg-blue-50 rounded-md border-l-4 border-blue-500">
                                        <p class="text-sm font-semibold text-blue-900 mb-2">
                                            <i class="fas fa-comment-alt mr-2"></i>Instructor Feedback
                                        </p>
                                        <p class="text-sm text-blue-800"><?= nl2br(sanitize($assignment['feedback'])) ?></p>
                                        <p class="text-xs text-blue-600 mt-2">
                                            Graded <?= timeAgo($assignment['graded_at']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="ml-6">
                                <?php if (!$assignment['submission_id']): ?>
                                    <a href="<?= url('student/submit-assignment.php?id=' . $assignment['id']) ?>"
                                       class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition font-medium">
                                        <i class="fas fa-upload mr-2"></i>Submit
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('student/submit-assignment.php?id=' . $assignment['id']) ?>"
                                       class="px-6 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition font-medium">
                                        <i class="fas fa-eye mr-2"></i>View
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
                $emptyTitle = 'No Assignments';
                $emptyMessage = 'You have no assignments at this time';
                if ($filter === 'pending') {
                    $emptyTitle = 'No Pending Assignments';
                    $emptyMessage = 'Great job! You have no pending assignments';
                } elseif ($filter === 'submitted') {
                    $emptyTitle = 'No Submitted Assignments';
                    $emptyMessage = 'You haven\'t submitted any assignments yet';
                } elseif ($filter === 'graded') {
                    $emptyTitle = 'No Graded Assignments';
                    $emptyMessage = 'No assignments have been graded yet';
                }
                emptyState(
                    'fa-file-alt',
                    $emptyTitle,
                    $emptyMessage,
                    url('my-courses.php'),
                    'View My Courses'
                );
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../src/templates/footer.php'; ?>
