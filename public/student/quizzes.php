<?php
/**
 * Student Quizzes Page
 * View all quizzes and their attempts
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
$havingCondition = '';

if ($filter === 'pending') {
    $havingCondition = 'HAVING attempt_count = 0';
} elseif ($filter === 'completed') {
    $havingCondition = 'HAVING attempt_count > 0';
}

// Get all quizzes from enrolled courses
$quizzes = $db->fetchAll("
    SELECT q.*,
           q.time_limit_minutes as time_limit,
           q.passing_score as pass_score,
           c.title as course_title, c.slug as course_slug,
           COUNT(DISTINCT qa.id) as attempt_count,
           MAX(qa.score) as best_score,
           MAX(qa.completed_at) as last_attempt,
           AVG(qa.score) as avg_score,
           e.id as enrollment_id
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.student_id = ?
    WHERE q.is_published = 1
    GROUP BY q.id
    $havingCondition
    ORDER BY q.created_at DESC
", [$userId, $userId]);

// Count quizzes by status
$counts = [
    'all' => count($db->fetchAll("
        SELECT q.id FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
        WHERE q.is_published = 1
    ", [$userId])),
    'pending' => count($db->fetchAll("
        SELECT q.id FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
        WHERE q.is_published = 1
        AND q.id NOT IN (SELECT DISTINCT quiz_id FROM quiz_attempts WHERE student_id = ?)
    ", [$userId, $userId])),
    'completed' => count($db->fetchAll("
        SELECT DISTINCT q.id FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
        JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.student_id = ?
        WHERE q.is_published = 1
    ", [$userId, $userId]))
];

$page_title = "My Quizzes - Edutrack";
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen py-8" style="background: var(--surface-primary);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold flex items-center" style="color: var(--text-primary);">
                <i class="fas fa-question-circle mr-3" style="color: var(--accent-primary);"></i>
                My Quizzes
            </h1>
            <p class="mt-2" style="color: var(--text-muted);">Test your knowledge and track your progress</p>
        </div>

        <!-- Filter Tabs -->
        <div class="rounded-lg mb-6" style="background: var(--surface-secondary); box-shadow: var(--shadow-card);">
            <div class="flex flex-col sm:flex-row border-b" style="border-color: var(--border-primary);">
                <a href="?filter=all"
                   class="flex-1 px-6 py-4 text-center font-medium transition"
                   style="<?= $filter === 'all' ? 'background: var(--accent-primary); color: var(--text-inverse);' : 'background: var(--surface-tertiary); color: var(--text-muted);' ?>">
                    All Quizzes
                    <span class="ml-2 px-2 py-1 text-xs rounded-full"
                          style="<?= $filter === 'all' ? 'background: rgba(255,255,255,0.2); color: var(--text-inverse);' : 'background: var(--surface-secondary); color: var(--text-muted);' ?>">
                        <?= $counts['all'] ?>
                    </span>
                </a>
                <a href="?filter=pending"
                   class="flex-1 px-6 py-4 text-center font-medium transition"
                   style="<?= $filter === 'pending' ? 'background: var(--accent-primary); color: var(--text-inverse);' : 'background: var(--surface-tertiary); color: var(--text-muted);' ?>">
                    Not Attempted
                    <span class="ml-2 px-2 py-1 text-xs rounded-full"
                          style="<?= $filter === 'pending' ? 'background: rgba(255,255,255,0.2); color: var(--text-inverse);' : 'background: var(--surface-secondary); color: var(--text-muted);' ?>">
                        <?= $counts['pending'] ?>
                    </span>
                </a>
                <a href="?filter=completed"
                   class="flex-1 px-6 py-4 text-center font-medium transition"
                   style="<?= $filter === 'completed' ? 'background: var(--accent-primary); color: var(--text-inverse);' : 'background: var(--surface-tertiary); color: var(--text-muted);' ?>">
                    Completed
                    <span class="ml-2 px-2 py-1 text-xs rounded-full"
                          style="<?= $filter === 'completed' ? 'background: rgba(255,255,255,0.2); color: var(--text-inverse);' : 'background: var(--surface-secondary); color: var(--text-muted);' ?>">
                        <?= $counts['completed'] ?>
                    </span>
                </a>
            </div>
        </div>

        <?php if (!empty($quizzes)): ?>
            <!-- Quizzes Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($quizzes as $quiz): ?>
                    <?php
                    $scoreColor = '';
                    if ($quiz['best_score'] !== null) {
                        if ($quiz['best_score'] >= 80) $scoreColor = 'var(--status-success)';
                        elseif ($quiz['best_score'] >= 60) $scoreColor = 'var(--status-warning)';
                        else $scoreColor = 'var(--status-error)';
                    }
                    ?>
                    <div class="card-hover rounded-lg overflow-hidden" style="background: var(--surface-secondary); box-shadow: var(--shadow-card);">
                        <!-- Header -->
                        <div class="p-4 text-white" style="background: linear-gradient(135deg, var(--accent-primary) 0%, var(--color-primary-600) 100%);">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold uppercase tracking-wide">Quiz</span>
                                <?php if ($quiz['time_limit']): ?>
                                    <span class="flex items-center text-xs">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?= $quiz['time_limit'] ?> min
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-lg font-bold"><?= sanitize($quiz['title']) ?></h3>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <p class="text-sm mb-4" style="color: var(--text-muted);">
                                <i class="fas fa-book mr-1"></i><?= sanitize($quiz['course_title']) ?>
                            </p>

                            <?php if ($quiz['description']): ?>
                                <p class="text-sm mb-4 line-clamp-2" style="color: var(--text-secondary);"><?= sanitize($quiz['description']) ?></p>
                            <?php endif; ?>

                            <!-- Stats -->
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: var(--text-muted);">Pass Score:</span>
                                    <span class="font-semibold" style="color: var(--text-primary);"><?= $quiz['pass_score'] ?>%</span>
                                </div>

                                <?php if ($quiz['attempt_count'] > 0): ?>
                                    <div class="flex items-center justify-between text-sm">
                                        <span style="color: var(--text-muted);">Attempts:</span>
                                        <span class="font-semibold" style="color: var(--text-primary);"><?= $quiz['attempt_count'] ?></span>
                                    </div>

                                    <div class="flex items-center justify-between text-sm">
                                        <span style="color: var(--text-muted);">Best Score:</span>
                                        <span class="font-bold text-lg" style="color: <?= $scoreColor ?>">
                                            <?= round($quiz['best_score']) ?>%
                                        </span>
                                    </div>

                                    <?php if ($quiz['attempt_count'] > 1): ?>
                                        <div class="flex items-center justify-between text-sm">
                                            <span style="color: var(--text-muted);">Average:</span>
                                            <span class="font-semibold" style="color: var(--text-primary);"><?= round($quiz['avg_score']) ?>%</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="pt-2 border-t" style="border-color: var(--border-primary);">
                                        <p class="text-xs" style="color: var(--text-muted);">
                                            Last attempt: <?= timeAgo($quiz['last_attempt']) ?>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4 rounded-lg" style="background: var(--surface-tertiary);">
                                        <i class="fas fa-clipboard-question text-3xl mb-2" style="color: var(--text-muted);"></i>
                                        <p class="text-sm" style="color: var(--text-muted);">Not yet attempted</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <?php if ($quiz['attempt_count'] > 0): ?>
                                    <a href="<?= url('student/take-quiz.php?id=' . $quiz['id']) ?>"
                                       class="flex-1 text-center btn-primary text-sm">
                                        <i class="fas fa-redo mr-1"></i>Retake
                                    </a>
                                    <a href="<?= url('student/quiz-results.php?quiz_id=' . $quiz['id']) ?>"
                                       class="py-2 px-4 rounded-lg text-sm transition flex items-center justify-center"
                                       style="background: var(--surface-tertiary); color: var(--text-secondary); border: 1px solid var(--border-primary);"
                                       title="View Results">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('student/take-quiz.php?id=' . $quiz['id']) ?>"
                                       class="flex-1 text-center btn-primary text-sm">
                                        <i class="fas fa-play mr-1"></i>Start Quiz
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
                $emptyTitle = 'No Quizzes';
                $emptyMessage = 'You have no quizzes at this time';
                if ($filter === 'pending') {
                    $emptyTitle = 'No Pending Quizzes';
                    $emptyMessage = 'Great job! You\'ve attempted all available quizzes';
                } elseif ($filter === 'completed') {
                    $emptyTitle = 'No Completed Quizzes';
                    $emptyMessage = 'You haven\'t completed any quizzes yet';
                }
                emptyState(
                    'fa-question-circle',
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
