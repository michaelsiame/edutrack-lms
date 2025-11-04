<?php
/**
 * Submit Assignment Page
 * Student submission form for assignments
 */

require_once '../../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = User::current();
$userId = $user->getId();

// Get assignment ID
$assignmentId = $_GET['id'] ?? null;

if (!$assignmentId) {
    flash('error', 'Assignment not found.', 'error');
    redirect('assignments.php');
}

// Get assignment details
$assignment = $db->fetchOne("
    SELECT a.*,
           c.title as course_title, c.slug as course_slug,
           e.id as enrollment_id,
           asub.id as submission_id,
           asub.status as submission_status,
           asub.submission_text,
           asub.file_path,
           asub.submitted_at,
           asub.points_earned,
           asub.feedback,
           asub.graded_at
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
    LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.user_id = ?
    WHERE a.id = ? AND a.status = 'published'
", [$userId, $userId, $assignmentId]);

if (!$assignment) {
    flash('error', 'Assignment not found or you are not enrolled in this course.', 'error');
    redirect('assignments.php');
}

$errors = [];
$success = false;

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    if (!verifyCsrfToken()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $submissionText = trim($_POST['submission_text'] ?? '');
        $filePath = null;

        // Validate
        if (empty($submissionText) && (!isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] === UPLOAD_ERR_NO_FILE)) {
            $errors[] = 'Please provide either a text submission or upload a file.';
        }

        // Handle file upload
        if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['submission_file'];

            // Validate file
            $allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar'];
            $maxSize = 10 * 1024 * 1024; // 10MB

            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($fileExt, $allowedTypes)) {
                $errors[] = 'Invalid file type. Allowed: PDF, DOC, DOCX, TXT, ZIP, RAR';
            } elseif ($file['size'] > $maxSize) {
                $errors[] = 'File size must not exceed 10MB';
            } else {
                // Create uploads directory if it doesn't exist
                $uploadDir = dirname(__DIR__, 2) . '/uploads/assignments/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Generate unique filename
                $fileName = $userId . '_' . $assignmentId . '_' . time() . '.' . $fileExt;
                $filePath = $uploadDir . $fileName;

                if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                    $errors[] = 'Failed to upload file';
                    $filePath = null;
                } else {
                    $filePath = 'uploads/assignments/' . $fileName;
                }
            }
        }

        if (empty($errors)) {
            try {
                if ($assignment['submission_id']) {
                    // Update existing submission
                    $db->query("
                        UPDATE assignment_submissions
                        SET submission_text = ?,
                            file_path = COALESCE(?, file_path),
                            submitted_at = NOW(),
                            status = 'submitted'
                        WHERE id = ?
                    ", [$submissionText, $filePath, $assignment['submission_id']]);
                } else {
                    // Create new submission
                    $db->query("
                        INSERT INTO assignment_submissions (user_id, assignment_id, submission_text, file_path, status, submitted_at, created_at)
                        VALUES (?, ?, ?, ?, 'submitted', NOW(), NOW())
                    ", [$userId, $assignmentId, $submissionText, $filePath]);
                }

                $success = true;
                flash('success', 'Assignment submitted successfully!', 'success');

                // Send notification to instructor
                if (class_exists('Notification')) {
                    $courseInstructor = $db->fetchColumn("SELECT instructor_id FROM courses WHERE id = ?", [$assignment['course_id']]);
                    if ($courseInstructor) {
                        Notification::create([
                            'user_id' => $courseInstructor,
                            'type' => 'assignment_submission',
                            'title' => 'New Assignment Submission',
                            'message' => $user->getFullName() . ' submitted "' . $assignment['title'] . '"',
                            'link' => 'instructor/assignments.php',
                            'icon' => 'fas fa-file-upload',
                            'color' => 'blue'
                        ]);
                    }
                }

                redirect('submit-assignment.php?id=' . $assignmentId);

            } catch (Exception $e) {
                $errors[] = 'Failed to submit assignment. Please try again.';
            }
        }
    }
}

$isOverdue = $assignment['due_date'] && strtotime($assignment['due_date']) < time();
$isGraded = $assignment['submission_status'] === 'graded';
$scorePercentage = $isGraded && $assignment['max_points'] > 0 ? ($assignment['points_earned'] / $assignment['max_points']) * 100 : 0;

$page_title = $assignment['title'] . " - Submit Assignment";
require_once '../../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?= url('student/assignments.php') ?>" class="text-primary-600 hover:text-primary-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Assignments
            </a>
        </div>

        <!-- Assignment Details -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2"><?= sanitize($assignment['title']) ?></h1>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-book mr-1"></i><?= sanitize($assignment['course_title']) ?>
                    </p>
                </div>

                <!-- Status Badge -->
                <?php if ($isGraded): ?>
                    <span class="px-4 py-2 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                        <i class="fas fa-check-circle mr-1"></i>Graded
                    </span>
                <?php elseif ($assignment['submission_status'] === 'submitted'): ?>
                    <span class="px-4 py-2 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full">
                        <i class="fas fa-clock mr-1"></i>Awaiting Review
                    </span>
                <?php elseif ($isOverdue): ?>
                    <span class="px-4 py-2 bg-red-100 text-red-800 text-sm font-semibold rounded-full">
                        <i class="fas fa-exclamation-circle mr-1"></i>Overdue
                    </span>
                <?php else: ?>
                    <span class="px-4 py-2 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                        <i class="fas fa-file-alt mr-1"></i>Pending
                    </span>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <?php if ($assignment['due_date']): ?>
                    <div class="bg-gray-50 rounded-md p-4">
                        <p class="text-sm text-gray-600 mb-1">Due Date</p>
                        <p class="font-semibold <?= $isOverdue ? 'text-red-600' : 'text-gray-900' ?>">
                            <?= formatDate($assignment['due_date']) ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="bg-gray-50 rounded-md p-4">
                    <p class="text-sm text-gray-600 mb-1">Max Points</p>
                    <p class="font-semibold text-gray-900"><?= $assignment['max_points'] ?> points</p>
                </div>

                <?php if ($isGraded): ?>
                    <div class="bg-gray-50 rounded-md p-4">
                        <p class="text-sm text-gray-600 mb-1">Your Score</p>
                        <p class="font-semibold <?= $scorePercentage >= 70 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $assignment['points_earned'] ?> / <?= $assignment['max_points'] ?> (<?= round($scorePercentage) ?>%)
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="prose max-w-none mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Instructions</h3>
                <p class="text-gray-700"><?= nl2br(sanitize($assignment['description'])) ?></p>
            </div>

            <?php if ($assignment['instructions']): ?>
                <div class="bg-blue-50 rounded-md p-4 mb-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>Additional Instructions
                    </h3>
                    <div class="text-blue-800 prose max-w-none">
                        <?= $assignment['instructions'] ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Graded Feedback -->
            <?php if ($isGraded && $assignment['feedback']): ?>
                <div class="bg-green-50 rounded-md p-4 border-l-4 border-green-500">
                    <h3 class="text-lg font-semibold text-green-900 mb-2 flex items-center">
                        <i class="fas fa-comment-alt mr-2"></i>Instructor Feedback
                    </h3>
                    <p class="text-green-800"><?= nl2br(sanitize($assignment['feedback'])) ?></p>
                    <p class="text-sm text-green-600 mt-2">
                        Graded <?= timeAgo($assignment['graded_at']) ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Submission Form -->
        <?php if (!$isGraded): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <?= $assignment['submission_id'] ? 'Update Submission' : 'Submit Assignment' ?>
                </h2>

                <?php if (!empty($errors)): ?>
                    <div class="mb-6">
                        <?php displayValidationErrors($errors); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <?= csrfField() ?>

                    <!-- Text Submission -->
                    <div class="mb-6">
                        <label for="submission_text" class="block text-sm font-medium text-gray-700 mb-2">
                            Text Submission
                        </label>
                        <textarea id="submission_text"
                                  name="submission_text"
                                  rows="8"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Type your submission here..."><?= sanitize($assignment['submission_text'] ?? '') ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">You can provide your answer directly here or upload a file below.</p>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-6">
                        <label for="submission_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Upload File (Optional)
                        </label>
                        <input type="file"
                               id="submission_file"
                               name="submission_file"
                               accept=".pdf,.doc,.docx,.txt,.zip,.rar"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <p class="text-xs text-gray-500 mt-1">Allowed: PDF, DOC, DOCX, TXT, ZIP, RAR (Max 10MB)</p>

                        <?php if ($assignment['file_path']): ?>
                            <div class="mt-3 p-3 bg-gray-50 rounded-md">
                                <p class="text-sm text-gray-700">
                                    <i class="fas fa-file text-gray-500 mr-2"></i>
                                    Current file: <a href="<?= url($assignment['file_path']) ?>" target="_blank" class="text-primary-600 hover:text-primary-700"><?= basename($assignment['file_path']) ?></a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-between">
                        <a href="<?= url('student/assignments.php') ?>" class="text-gray-600 hover:text-gray-900">
                            Cancel
                        </a>
                        <button type="submit"
                                name="submit_assignment"
                                class="px-8 py-3 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition font-medium">
                            <i class="fas fa-paper-plane mr-2"></i>
                            <?= $assignment['submission_id'] ? 'Update Submission' : 'Submit Assignment' ?>
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Already Graded - Show Submission -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Your Submission</h2>

                <?php if ($assignment['submission_text']): ?>
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Text Submission:</h3>
                        <div class="bg-gray-50 rounded-md p-4">
                            <p class="text-gray-700 whitespace-pre-wrap"><?= sanitize($assignment['submission_text']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($assignment['file_path']): ?>
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Attached File:</h3>
                        <a href="<?= url($assignment['file_path']) ?>"
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-primary-50 text-primary-700 rounded-md hover:bg-primary-100">
                            <i class="fas fa-download mr-2"></i>
                            <?= basename($assignment['file_path']) ?>
                        </a>
                    </div>
                <?php endif; ?>

                <p class="text-sm text-gray-500">
                    Submitted <?= timeAgo($assignment['submitted_at']) ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../src/templates/footer.php'; ?>
