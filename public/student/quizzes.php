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
           c.title as course_title, c.slug as course_slug,
           COUNT(DISTINCT qa.id) as attempt_count,
           MAX(qa.score) as best_score,
           MAX(qa.completed_at) as last_attempt,
           AVG(qa.score) as avg_score,
           e.id as enrollment_id
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.user_id = ?
    WHERE q.status = 'published'
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
        WHERE q.status = 'published'
    ", [$userId])),
    'pending' => count($db->fetchAll("
        SELECT q.id FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
        WHERE q.status = 'published'
        AND q.id NOT IN (SELECT DISTINCT quiz_id FROM quiz_attempts WHERE user_id = ?)
    ", [$userId, $userId])),
    'completed' => count($db->fetchAll("
        SELECT DISTINCT q.id FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
        JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.user_id = ?
        WHERE q.status = 'published'
    ", [$userId, $userId]))
];

$page_title = "My Quizzes - Edutrack";
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-question-circle text-primary-600 mr-3"></i>
                My Quizzes
            </h1>
            <p class="text-gray-600 mt-2">Test your knowledge and track your progress</p>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="flex flex-col sm:flex-row border-b border-gray-200">
                <a href="?filter=all"
                   class="flex-1 px-6 py-4 text-center font-medium transition <?= $filter === 'all' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' ?>">
                    All Quizzes
                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?= $filter === 'all' ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $counts['all'] ?>
                    </span>
                </a>
                <a href="?filter=pending"
                   class="flex-1 px-6 py-4 text-center font-medium transition <?= $filter === 'pending' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' ?>">
                    Not Attempted
                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?= $filter === 'pending' ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $counts['pending'] ?>
                    </span>
                </a>
                <a href="?filter=completed"
                   class="flex-1 px-6 py-4 text-center font-medium transition <?= $filter === 'completed' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' ?>">
                    Completed
                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?= $filter === 'completed' ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-600' ?>">
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
                    $scoreClass = '';
                    if ($quiz['best_score'] !== null) {
                        if ($quiz['best_score'] >= 80) $scoreClass = 'text-green-600';
                        elseif ($quiz['best_score'] >= 60) $scoreClass = 'text-yellow-600';
                        else $scoreClass = 'text-red-600';
                    }
                    ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 text-white">
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
                            <p class="text-sm text-gray-600 mb-4">
                                <i class="fas fa-book mr-1"></i><?= sanitize($quiz['course_title']) ?>
                            </p>

                            <?php if ($quiz['description']): ?>
                                <p class="text-sm text-gray-700 mb-4 line-clamp-2"><?= sanitize($quiz['description']) ?></p>
                            <?php endif; ?>

                            <!-- Stats -->
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Pass Score:</span>
                                    <span class="font-semibold text-gray-900"><?= $quiz['pass_score'] ?>%</span>
                                </div>

                                <?php if ($quiz['attempt_count'] > 0): ?>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Attempts:</span>
                                        <span class="font-semibold text-gray-900"><?= $quiz['attempt_count'] ?></span>
                                    </div>

                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Best Score:</span>
                                        <span class="font-bold text-lg <?= $scoreClass ?>">
                                            <?= round($quiz['best_score']) ?>%
                                        </span>
                                    </div>

                                    <?php if ($quiz['attempt_count'] > 1): ?>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">Average:</span>
                                            <span class="font-semibold text-gray-900"><?= round($quiz['avg_score']) ?>%</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="pt-2 border-t border-gray-200">
                                        <p class="text-xs text-gray-500">
                                            Last attempt: <?= timeAgo($quiz['last_attempt']) ?>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-clipboard-question text-gray-300 text-3xl mb-2"></i>
                                        <p class="text-sm text-gray-500">Not yet attempted</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <?php if ($quiz['attempt_count'] > 0): ?>
                                    <a href="<?= url('student/take-quiz.php?id=' . $quiz['id']) ?>"
                                       class="flex-1 text-center py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition font-medium text-sm">
                                        <i class="fas fa-redo mr-1"></i>Retake
                                    </a>
                                    <a href="<?= url('student/quiz-results.php?quiz_id=' . $quiz['id']) ?>"
                                       class="py-2 px-4 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition text-sm"
                                       title="View Results">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('student/take-quiz.php?id=' . $quiz['id']) ?>"
                                       class="flex-1 text-center py-2 px-4 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition font-medium text-sm">
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
            <div class="bg-white rounded-lg shadow-md p-12">
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
