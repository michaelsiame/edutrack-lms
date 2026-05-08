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
           asub.file_url as submission_file,
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

<div class="min-h-screen py-8" style="background: var(--surface-primary);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold flex items-center" style="color: var(--text-primary);">
                <i class="fas fa-file-alt mr-3" style="color: var(--accent-primary);"></i>
                My Assignments
            </h1>
            <p class="mt-2" style="color: var(--text-muted);">View and submit assignments for your courses</p>
        </div>

        <!-- Filter Tabs -->
        <div class="rounded-lg mb-6" style="background: var(--surface-secondary); box-shadow: var(--shadow-card);">
            <div class="flex flex-col sm:flex-row border-b" style="border-color: var(--border-primary);">
                <a href="?filter=all"
                   class="flex-1 px-6 py-4 text-center font-medium transition"
                   style="<?= $filter === 'all' ? 'background: var(--accent-primary); color: var(--text-inverse);' : 'background: var(--surface-tertiary); color: var(--text-muted);' ?>">
                    All Assignments
                    <span class="ml-2 px-2 py-1 text-xs rounded-full"
                          style="<?= $filter === 'all' ? 'background: rgba(255,255,255,0.2); color: var(--text-inverse);' : 'background: var(--surface-secondary); color: var(--text-muted);' ?>">
                        <?= $counts['all'] ?>
                    </span>
                </a>
                <a href="?filter=pending"
                   class="flex-1 px-6 py-4 text-center font-medium transition"
                   style="<?= $filter === 'pending' ? 'background: var(--accent-primary); color: var(--text-inverse);' : 'background: var(--surface-tertiary); color: var(--text-muted);' ?>">
                    Pending
                    <span class="ml-2 px-2 py-1 text-xs rounded-full"
                          style="<?= $filter === 'pending' ? 'background: rgba(255,255,255,0.2); color: var(--text-inverse);' : 'background: var(--surface-secondary); color: var(--text-muted);' ?>">
                        <?= $counts['pending'] ?>
                    </span>
                </a>
                <a href="?filter=submitted"
                   class="flex-1 px-6 py-4 text-center font-medium transition"
                   style="<?= $filter === 'submitted' ? 'background: var(--accent-primary); color: var(--text-inverse);' : 'background: var(--surface-tertiary); color: var(--text-muted);' ?>">
                    Submitted
                    <span class="ml-2 px-2 py-1 text-xs rounded-full"
                          style="<?= $filter === 'submitted' ? 'background: rgba(255,255,255,0.2); color: var(--text-inverse);' : 'background: var(--surface-secondary); color: var(--text-muted);' ?>">
                        <?= $counts['submitted'] ?>
                    </span>
                </a>
                <a href="?filter=graded"
                   class="flex-1 px-6 py-4 text-center font-medium transition"
                   style="<?= $filter === 'graded' ? 'background: var(--accent-primary); color: var(--text-inverse);' : 'background: var(--surface-tertiary); color: var(--text-muted);' ?>">
                    Graded
                    <span class="ml-2 px-2 py-1 text-xs rounded-full"
                          style="<?= $filter === 'graded' ? 'background: rgba(255,255,255,0.2); color: var(--text-inverse);' : 'background: var(--surface-secondary); color: var(--text-muted);' ?>">
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
                    <div class="card-hover rounded-lg p-6" style="background: var(--surface-secondary); box-shadow: var(--shadow-card);">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-xl font-bold" style="color: var(--text-primary);">
                                        <?= sanitize($assignment['title']) ?>
                                    </h3>

                                    <!-- Status Badge -->
                                    <?php if ($isGraded): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background: var(--status-success-bg); color: var(--status-success);">
                                            <i class="fas fa-check-circle mr-1"></i>Graded
                                        </span>
                                    <?php elseif ($isSubmitted): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background: var(--status-warning-bg); color: var(--status-warning);">
                                            <i class="fas fa-clock mr-1"></i>Awaiting Review
                                        </span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background: var(--status-error-bg); color: var(--status-error);">
                                            <i class="fas fa-exclamation-circle mr-1"></i>Overdue
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background: var(--status-info-bg); color: var(--status-info);">
                                            <i class="fas fa-file-alt mr-1"></i>Pending
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-sm mb-3" style="color: var(--text-muted);">
                                    <i class="fas fa-book mr-1"></i><?= sanitize($assignment['course_title']) ?>
                                </p>

                                <p class="mb-4" style="color: var(--text-secondary);"><?= sanitize($assignment['description']) ?></p>

                                <div class="flex flex-wrap items-center gap-4 text-sm" style="color: var(--text-muted);">
                                    <?php if ($assignment['due_date']): ?>
                                        <div class="flex items-center <?= $isOverdue ? 'font-semibold' : '' ?>" style="<?= $isOverdue ? 'color: var(--status-error);' : '' ?>">
                                            <i class="fas fa-calendar mr-2"></i>
                                            Due: <?= formatDate($assignment['due_date']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex items-center">
                                        <i class="fas fa-star mr-2"></i>
                                        <?= $assignment['max_points'] ?> points
                                    </div>

                                    <?php if ($isSubmitted): ?>
                                        <div class="flex items-center" style="color: var(--accent-primary);">
                                            <i class="fas fa-check mr-2"></i>
                                            Submitted <?= timeAgo($assignment['submitted_at']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($isGraded): ?>
                                        <?php
                                        $scoreColor = $scorePercentage >= 70 ? 'var(--status-success)' : ($scorePercentage < 60 ? 'var(--status-error)' : 'var(--status-warning)');
                                        ?>
                                        <div class="flex items-center font-semibold" style="color: <?= $scoreColor ?>;">
                                            <i class="fas fa-trophy mr-2"></i>
                                            Score: <?= $assignment['points_earned'] ?>/<?= $assignment['max_points'] ?> (<?= round($scorePercentage) ?>%)
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Feedback -->
                                <?php if ($isGraded && $assignment['feedback']): ?>
                                    <div class="mt-4 p-4 rounded-md" style="background: var(--status-info-bg); border-left: 4px solid var(--status-info);">
                                        <p class="text-sm font-semibold mb-2" style="color: var(--text-primary);">
                                            <i class="fas fa-comment-alt mr-2" style="color: var(--status-info);"></i>Instructor Feedback
                                        </p>
                                        <p class="text-sm" style="color: var(--text-secondary);"><?= nl2br(sanitize($assignment['feedback'])) ?></p>
                                        <p class="text-xs mt-2" style="color: var(--text-muted);">
                                            Graded <?= timeAgo($assignment['graded_at']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="ml-6">
                                <?php if (!$assignment['submission_id']): ?>
                                    <a href="<?= url('student/submit-assignment.php?id=' . $assignment['id']) ?>"
                                       class="btn-primary inline-flex items-center">
                                        <i class="fas fa-upload mr-2"></i>Submit
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('student/submit-assignment.php?id=' . $assignment['id']) ?>"
                                       class="inline-flex items-center px-6 py-2 rounded-lg font-medium transition"
                                       style="background: var(--surface-tertiary); color: var(--text-secondary);"
                                       onmouseover="this.style.background='var(--border-primary)';"
                                       onmouseout="this.style.background='var(--surface-tertiary)';">
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
            <div class="empty-state rounded-lg" style="background: var(--surface-secondary); box-shadow: var(--shadow-card);">
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
