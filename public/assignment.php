<?php
/**
 * Assignment Page
 * View assignment details and submit work
 */

require_once '../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

$assignmentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$assignmentId) {
    flash('error', 'Invalid assignment', 'error');
    redirect('my-courses.php');
}

try {
    // Get assignment details
    $assignment = $db->fetchOne("
        SELECT a.*,
               c.title as course_title,
               c.slug as course_slug,
               c.id as course_id,
               l.title as lesson_title
        FROM assignments a
        JOIN courses c ON a.course_id = c.id
        LEFT JOIN lessons l ON a.lesson_id = l.id
        WHERE a.id = ? AND a.status = 'published'
    ", [$assignmentId]);

    if (!$assignment) {
        flash('error', 'Assignment not found', 'error');
        redirect('my-courses.php');
    }

    // Verify enrollment
    $enrollment = $db->fetchOne("
        SELECT id FROM enrollments
        WHERE user_id = ? AND course_id = ?
    ", [$userId, $assignment['course_id']]);

    if (!$enrollment) {
        flash('error', 'You must be enrolled in this course to view assignments', 'error');
        redirect('course.php?slug=' . urlencode($assignment['course_slug']));
    }

    // Get existing submissions
    $submissions = $db->fetchAll("
        SELECT s.*,
               u.first_name as grader_first_name,
               u.last_name as grader_last_name
        FROM assignment_submissions s
        LEFT JOIN users u ON s.graded_by = u.id
        WHERE s.assignment_id = ? AND s.user_id = ?
        ORDER BY s.submitted_at DESC
    ", [$assignmentId, $userId]);

    $latestSubmission = !empty($submissions) ? $submissions[0] : null;

    // Check if assignment is overdue
    $isOverdue = $assignment['due_date'] && strtotime($assignment['due_date']) < time();
    $daysUntilDue = $assignment['due_date'] ? ceil((strtotime($assignment['due_date']) - time()) / 86400) : null;

    $page_title = $assignment['title'] . ' - ' . $assignment['course_title'];

} catch (Exception $e) {
    error_log("Assignment Page Error: " . $e->getMessage());
    flash('error', 'An error occurred loading the assignment', 'error');
    redirect('my-courses.php');
}

require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-5xl mx-auto px-4">

        <!-- Assignment Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <a href="<?= url('learn.php?course=' . urlencode($assignment['course_slug'])) ?>"
               class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Back to Course
            </a>

            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        <?= htmlspecialchars($assignment['title']) ?>
                    </h1>
                    <p class="text-gray-600"><?= htmlspecialchars($assignment['course_title']) ?></p>
                    <?php if ($assignment['lesson_title']): ?>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-book mr-1"></i>Related to: <?= htmlspecialchars($assignment['lesson_title']) ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Status Badge -->
                <?php if ($latestSubmission): ?>
                    <?php if ($latestSubmission['status'] == 'graded'): ?>
                    <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full font-bold">
                        <i class="fas fa-check-circle mr-1"></i>Graded
                    </span>
                    <?php elseif ($latestSubmission['status'] == 'submitted'): ?>
                    <span class="px-4 py-2 bg-blue-100 text-blue-800 rounded-full font-bold">
                        <i class="fas fa-clock mr-1"></i>Pending Review
                    </span>
                    <?php endif; ?>
                <?php elseif ($isOverdue): ?>
                <span class="px-4 py-2 bg-red-100 text-red-800 rounded-full font-bold">
                    <i class="fas fa-exclamation-triangle mr-1"></i>Overdue
                </span>
                <?php else: ?>
                <span class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full font-bold">
                    <i class="fas fa-edit mr-1"></i>Not Submitted
                </span>
                <?php endif; ?>
            </div>

            <!-- Assignment Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                <div class="text-center">
                    <i class="fas fa-star text-yellow-500 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Points</p>
                    <p class="font-bold text-gray-900"><?= $assignment['max_points'] ?></p>
                </div>
                <div class="text-center">
                    <i class="fas fa-check-double text-green-500 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Passing Score</p>
                    <p class="font-bold text-gray-900"><?= $assignment['passing_score'] ?>%</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-calendar text-blue-500 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Due Date</p>
                    <p class="font-bold text-gray-900">
                        <?= $assignment['due_date'] ? date('M j, Y', strtotime($assignment['due_date'])) : 'No deadline' ?>
                    </p>
                </div>
                <div class="text-center">
                    <i class="fas fa-clock text-purple-500 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Time Left</p>
                    <p class="font-bold <?= $daysUntilDue && $daysUntilDue < 3 ? 'text-red-600' : 'text-gray-900' ?>">
                        <?php if (!$assignment['due_date']): ?>
                            No limit
                        <?php elseif ($isOverdue): ?>
                            Overdue
                        <?php elseif ($daysUntilDue == 0): ?>
                            Today!
                        <?php else: ?>
                            <?= $daysUntilDue ?> day<?= $daysUntilDue != 1 ? 's' : '' ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Description -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Description
                    </h2>
                    <div class="prose max-w-none text-gray-700">
                        <?= $assignment['description'] ?>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-list-check text-green-600 mr-2"></i>Instructions
                    </h2>
                    <div class="prose max-w-none text-gray-700">
                        <?= $assignment['instructions'] ?>
                    </div>
                </div>

                <!-- Submission Form -->
                <?php if (!$latestSubmission || $latestSubmission['status'] != 'graded'): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-upload text-purple-600 mr-2"></i>Submit Your Work
                    </h2>

                    <?php if ($isOverdue): ?>
                    <div class="p-4 bg-red-50 border-l-4 border-red-500 mb-4">
                        <p class="text-red-700">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Note:</strong> This assignment is overdue. Late submissions may receive reduced points.
                        </p>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= url('actions/submit-assignment.php') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="assignment_id" value="<?= $assignmentId ?>">
                        <?= csrfField() ?>

                        <!-- Text Submission -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Submission Text
                            </label>
                            <textarea name="submission_text"
                                      rows="6"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Enter your submission text here or describe the work you're uploading..."><?= $latestSubmission ? htmlspecialchars($latestSubmission['submission_text']) : '' ?></textarea>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Upload File (Optional)
                            </label>
                            <input type="file"
                                   name="submission_file"
                                   accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">
                                Accepted formats: PDF, Word documents, ZIP files, Images (Max 10MB)
                            </p>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="<?= url('learn.php?course=' . urlencode($assignment['course_slug'])) ?>"
                               class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-paper-plane mr-2"></i>Submit Assignment
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Current Score (if graded) -->
                <?php if ($latestSubmission && $latestSubmission['status'] == 'graded'): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Your Score</h3>
                    <div class="text-center">
                        <div class="text-5xl font-bold <?= ($latestSubmission['score'] / $assignment['max_points'] * 100) >= $assignment['passing_score'] ? 'text-green-600' : 'text-red-600' ?> mb-2">
                            <?= round($latestSubmission['score']) ?>
                        </div>
                        <p class="text-gray-600 mb-4">out of <?= $assignment['max_points'] ?> points</p>
                        <div class="text-3xl font-bold <?= ($latestSubmission['score'] / $assignment['max_points'] * 100) >= $assignment['passing_score'] ? 'text-green-600' : 'text-orange-600' ?>">
                            <?= round(($latestSubmission['score'] / $assignment['max_points']) * 100) ?>%
                        </div>
                        <?php if (($latestSubmission['score'] / $assignment['max_points'] * 100) >= $assignment['passing_score']): ?>
                        <p class="text-green-600 font-medium mt-2">
                            <i class="fas fa-check-circle mr-1"></i>Passed
                        </p>
                        <?php else: ?>
                        <p class="text-orange-600 font-medium mt-2">
                            <i class="fas fa-info-circle mr-1"></i>Below Passing Score
                        </p>
                        <?php endif; ?>
                    </div>

                    <?php if ($latestSubmission['feedback']): ?>
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                        <h4 class="font-bold text-blue-900 mb-2">Instructor Feedback</h4>
                        <p class="text-blue-800 text-sm"><?= nl2br(htmlspecialchars($latestSubmission['feedback'])) ?></p>
                        <?php if ($latestSubmission['grader_first_name']): ?>
                        <p class="text-xs text-blue-600 mt-2">
                            - <?= htmlspecialchars($latestSubmission['grader_first_name'] . ' ' . $latestSubmission['grader_last_name']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Submission History -->
                <?php if (!empty($submissions)): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <i class="fas fa-history text-gray-600 mr-2"></i>Submission History
                    </h3>
                    <div class="space-y-3">
                        <?php foreach ($submissions as $index => $submission): ?>
                        <div class="p-4 bg-gray-50 rounded-lg border-l-4 <?= $submission['status'] == 'graded' ? 'border-green-500' : 'border-blue-500' ?>">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-900">Submission #<?= count($submissions) - $index ?></span>
                                <?php if ($submission['status'] == 'graded'): ?>
                                <span class="text-green-600 font-bold"><?= round($submission['score']) ?> pts</span>
                                <?php else: ?>
                                <span class="text-blue-600 text-sm">Pending</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-600">
                                <i class="fas fa-clock mr-1"></i>
                                <?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?>
                            </p>
                            <?php if ($submission['file_name']): ?>
                            <p class="text-xs text-gray-600 mt-1">
                                <i class="fas fa-file mr-1"></i>
                                <?= htmlspecialchars($submission['file_name']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>
