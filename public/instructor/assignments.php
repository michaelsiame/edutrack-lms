<?php
/**
 * Instructor - Assignment Submissions & Grading
 */

// Debug initialization
$DEBUG_MODE = defined('DEBUG_MODE') ? DEBUG_MODE : ($_ENV['DEBUG_MODE'] ?? false);
$page_start_time = microtime(true);
$page_start_memory = memory_get_usage();
$debug_data = [
    'page' => 'instructor/assignments.php',
    'timestamp' => date('Y-m-d H:i:s'),
    'queries' => [],
    'errors' => []
];

// Error handler for debugging
if ($DEBUG_MODE) {
    set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$debug_data) {
        $debug_data['errors'][] = [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ];
        return false;
    });
}

require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Assignment.php';
require_once '../../src/classes/Submission.php';

// Debug: Log user info
if ($DEBUG_MODE) {
    $debug_data['user'] = [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role' => $_SESSION['user_role'] ?? null
    ];
}

$db = Database::getInstance();
$instructorId = currentUserId();

// Debug: Log instructor ID
if ($DEBUG_MODE) {
    $debug_data['instructor_id'] = $instructorId;
}

// Handle grading submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'grade') {
    validateCSRF();

    $submissionId = $_POST['submission_id'] ?? null;
    $pointsEarned = $_POST['points_earned'] ?? null;
    $feedback = trim($_POST['feedback'] ?? '');

    if (!$submissionId || !is_numeric($pointsEarned)) {
        flash('message', 'Invalid submission or points', 'error');
    } else {
        $submission = Submission::find($submissionId);

        if (!$submission) {
            flash('message', 'Submission not found', 'error');
        } else {
            // Verify instructor owns the course
            $assignment = Assignment::find($submission->getAssignmentId());
            $course = Course::find($assignment->getCourseId());

            if ($course->getInstructorId() != $instructorId && !hasRole('admin')) {
                flash('message', 'Unauthorized', 'error');
            } else {
                if ($submission->grade($pointsEarned, $feedback)) {
                    flash('message', 'Submission graded successfully!', 'success');

                    // Send notification to student
                    if (class_exists('Notification')) {
                        Notification::notifyAssignmentGraded(
                            $submission->getUserId(),
                            $assignment->getTitle(),
                            "$pointsEarned / " . $submission->getMaxPoints(),
                            $submissionId
                        );
                    }

                    // Send email notification
                    if (class_exists('Email')) {
                        try {
                            $email = new Email();
                            $user = User::find($submission->getUserId());
                            if ($user) {
                                $email->sendAssignmentGraded([
                                    'email' => $user->getEmail(),
                                    'first_name' => $user->getFirstName()
                                ], $submission);
                            }
                        } catch (Exception $e) {
                            error_log("Failed to send grading email: " . $e->getMessage());
                        }
                    }
                } else {
                    flash('message', 'Failed to grade submission', 'error');
                }
            }
        }
    }

    redirect($_SERVER['REQUEST_URI']);
}

// Get filter parameters
$courseFilter = $_GET['course'] ?? '';
$assignmentFilter = $_GET['assignment'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Get instructor's courses
$courses = Course::getByInstructor($instructorId);

// Build query
$where = [];
$params = [];

if ($courseFilter) {
    $where[] = 'a.course_id = ?';
    $params[] = $courseFilter;
} else {
    // Only show submissions from instructor's courses
    $courseIds = array_map(function($c) { return $c['id']; }, $courses);
    if (empty($courseIds)) {
        $courseIds = [0]; // No courses
    }
    $where[] = 'a.course_id IN (' . implode(',', array_fill(0, count($courseIds), '?')) . ')';
    $params = array_merge($params, $courseIds);
}

if ($assignmentFilter) {
    $where[] = 's.assignment_id = ?';
    $params[] = $assignmentFilter;
}

if ($statusFilter) {
    $where[] = 's.status = ?';
    $params[] = $statusFilter;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get submissions
$submissions = $db->fetchAll("
    SELECT s.*,
           a.title as assignment_title, a.max_points, a.due_date,
           c.title as course_title, c.slug as course_slug,
           u.first_name, u.last_name, u.email
    FROM assignment_submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN students st ON s.student_id = st.id
    JOIN users u ON st.user_id = u.id
    $whereClause
    ORDER BY s.submitted_at DESC
", $params);

// Get assignments for filter dropdown
$assignments = [];
if ($courseFilter) {
    $assignments = $db->fetchAll("
        SELECT id, title FROM assignments
        WHERE course_id = ?
        ORDER BY title
    ", [$courseFilter]);
}

// Statistics
$stats = [
    'total' => count($submissions),
    'pending' => count(array_filter($submissions, fn($s) => $s['status'] == 'submitted')),
    'graded' => count(array_filter($submissions, fn($s) => $s['status'] == 'graded'))
];

// Debug: Log submissions and stats data
if ($DEBUG_MODE) {
    $debug_data['data'] = [
        'submissions_count' => count($submissions),
        'courses_count' => count($courses),
        'assignments_count' => count($assignments),
        'stats' => $stats
    ];
    $debug_data['filters'] = [
        'course' => $courseFilter,
        'assignment' => $assignmentFilter,
        'status' => $statusFilter
    ];
}

$page_title = 'Assignment Submissions';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-file-alt text-primary-600 mr-3"></i>
                Assignment Submissions
            </h1>
            <p class="text-gray-600 mt-2">Grade and provide feedback on student submissions</p>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Submissions</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?= $stats['total'] ?></p>
                    </div>
                    <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Pending Grading</p>
                        <p class="text-3xl font-bold text-orange-600 mt-1"><?= $stats['pending'] ?></p>
                    </div>
                    <div class="h-12 w-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Graded</p>
                        <p class="text-3xl font-bold text-green-600 mt-1"><?= $stats['graded'] ?></p>
                    </div>
                    <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select name="course" onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= $courseFilter == $course['id'] ? 'selected' : '' ?>>
                                <?= sanitize($course['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!empty($assignments)): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assignment</label>
                    <select name="assignment" onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Assignments</option>
                        <?php foreach ($assignments as $assignment): ?>
                            <option value="<?= $assignment['id'] ?>" <?= $assignmentFilter == $assignment['id'] ? 'selected' : '' ?>>
                                <?= sanitize($assignment['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Statuses</option>
                        <option value="submitted" <?= $statusFilter == 'submitted' ? 'selected' : '' ?>>Pending</option>
                        <option value="graded" <?= $statusFilter == 'graded' ? 'selected' : '' ?>>Graded</option>
                    </select>
                </div>

                <?php if ($courseFilter || $assignmentFilter || $statusFilter): ?>
                <div class="flex items-end">
                    <a href="assignments.php" class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-center">
                        <i class="fas fa-times mr-2"></i>Clear Filters
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Submissions List -->
        <?php if (empty($submissions)): ?>
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Submissions Found</h3>
            <p class="text-gray-600">There are no assignment submissions matching your filters.</p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($submissions as $sub):
                            $isLate = $sub['due_date'] && strtotime($sub['submitted_at']) > strtotime($sub['due_date']);
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="<?= getGravatar($sub['email']) ?>" class="h-10 w-10 rounded-full mr-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= sanitize($sub['first_name'] . ' ' . $sub['last_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500"><?= sanitize($sub['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?= sanitize($sub['assignment_title']) ?></div>
                                <?php if ($sub['file_name']): ?>
                                    <div class="text-sm text-gray-500">
                                        <i class="fas fa-paperclip mr-1"></i><?= sanitize($sub['file_name']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= sanitize($sub['course_title']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= date('M j, Y', strtotime($sub['submitted_at'])) ?></div>
                                <div class="text-sm text-gray-500"><?= date('g:i A', strtotime($sub['submitted_at'])) ?></div>
                                <?php if ($isLate): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mt-1">
                                        <i class="fas fa-clock mr-1"></i>Late
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($sub['status'] == 'graded'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Graded
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($sub['status'] == 'graded'): ?>
                                    <div class="text-sm font-bold text-gray-900">
                                        <?= $sub['points_earned'] ?> / <?= $sub['max_points'] ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= round(($sub['points_earned'] / $sub['max_points']) * 100, 1) ?>%
                                    </div>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">Not graded</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="viewSubmission(<?= $sub['id'] ?>)"
                                        class="text-primary-600 hover:text-primary-900 mr-3">
                                    <i class="fas fa-eye mr-1"></i>View
                                </button>
                                <button onclick="gradeSubmission(<?= $sub['id'] ?>)"
                                        class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit mr-1"></i><?= $sub['status'] == 'graded' ? 'Edit Grade' : 'Grade' ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- View Submission Modal -->
<div id="viewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Submission Details</h3>
            <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="viewModalContent" class="p-6">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<!-- Grade Submission Modal -->
<div id="gradeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Grade Submission</h3>
            <button onclick="closeModal('gradeModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="gradeModalContent" class="p-6">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<script>
const submissions = <?= json_encode($submissions) ?>;

function viewSubmission(id) {
    const submission = submissions.find(s => s.id == id);
    if (!submission) return;

    let html = `
        <div class="space-y-4">
            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Student</h4>
                <p class="text-gray-700">${escapeHtml(submission.first_name + ' ' + submission.last_name)}</p>
                <p class="text-sm text-gray-500">${escapeHtml(submission.email)}</p>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Assignment</h4>
                <p class="text-gray-700">${escapeHtml(submission.assignment_title)}</p>
                <p class="text-sm text-gray-500">Course: ${escapeHtml(submission.course_title)}</p>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Submission Date</h4>
                <p class="text-gray-700">${new Date(submission.submitted_at).toLocaleString()}</p>
            </div>
    `;

    if (submission.submission_text) {
        html += `
            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Submission Text</h4>
                <div class="bg-gray-50 p-4 rounded border border-gray-200 whitespace-pre-wrap">${escapeHtml(submission.submission_text)}</div>
            </div>
        `;
    }

    if (submission.file_name) {
        html += `
            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Attached File</h4>
                <a href="<?= url('api/download.php') ?>?type=submission&id=${submission.id}"
                   class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-download mr-2"></i>
                    ${escapeHtml(submission.file_name)}
                </a>
                <p class="text-sm text-gray-500 mt-1">Size: ${formatFileSize(submission.file_size)}</p>
            </div>
        `;
    }

    if (submission.status == 'graded') {
        html += `
            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Grade</h4>
                <p class="text-2xl font-bold text-gray-900">${submission.points_earned} / ${submission.max_points}</p>
                <p class="text-gray-600">${Math.round((submission.points_earned / submission.max_points) * 100)}%</p>
            </div>
        `;

        if (submission.feedback) {
            html += `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Feedback</h4>
                    <div class="bg-gray-50 p-4 rounded border border-gray-200 whitespace-pre-wrap">${escapeHtml(submission.feedback)}</div>
                </div>
            `;
        }
    }

    html += `</div>`;

    document.getElementById('viewModalContent').innerHTML = html;
    document.getElementById('viewModal').classList.remove('hidden');
}

function gradeSubmission(id) {
    const submission = submissions.find(s => s.id == id);
    if (!submission) return;

    const html = `
        <form method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="grade">
            <input type="hidden" name="submission_id" value="${submission.id}">

            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Student</h4>
                <p class="text-gray-700">${escapeHtml(submission.first_name + ' ' + submission.last_name)}</p>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Assignment</h4>
                <p class="text-gray-700">${escapeHtml(submission.assignment_title)}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Points Earned <span class="text-red-500">*</span>
                </label>
                <input type="number" name="points_earned" required min="0" max="${submission.max_points}"
                       step="0.5" value="${submission.points_earned || ''}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                       placeholder="0 - ${submission.max_points}">
                <p class="text-sm text-gray-500 mt-1">Maximum points: ${submission.max_points}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Feedback</label>
                <textarea name="feedback" rows="6"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                          placeholder="Provide feedback to the student...">${submission.feedback || ''}</textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="closeModal('gradeModal')"
                        class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-check mr-2"></i>Save Grade
                </button>
            </div>
        </form>
    `;

    document.getElementById('gradeModalContent').innerHTML = html;
    document.getElementById('gradeModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}
</script>

<?php
// Debug panel output
if ($DEBUG_MODE) {
    $debug_data['performance'] = [
        'execution_time' => round((microtime(true) - $page_start_time) * 1000, 2) . 'ms',
        'memory_used' => round((memory_get_usage() - $page_start_memory) / 1024, 2) . 'KB',
        'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB'
    ];
?>
<!-- Debug Panel -->
<div id="debug-panel" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white text-xs z-50 max-h-96 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-between p-2 bg-gray-800 sticky top-0">
        <span class="font-bold"><i class="fas fa-bug mr-2"></i>Debug Panel - <?= $debug_data['page'] ?></span>
        <div class="flex items-center space-x-4">
            <span class="text-green-400">Time: <?= $debug_data['performance']['execution_time'] ?></span>
            <span class="text-blue-400">Memory: <?= $debug_data['performance']['memory_used'] ?></span>
            <button onclick="document.getElementById('debug-panel').style.display='none'" class="text-red-400 hover:text-red-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <h4 class="font-bold text-yellow-400 mb-2">User Info</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto"><?= json_encode($debug_data['user'] ?? [], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <div>
            <h4 class="font-bold text-yellow-400 mb-2">Data</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto"><?= json_encode($debug_data['data'] ?? [], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <div>
            <h4 class="font-bold text-yellow-400 mb-2">Filters</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto"><?= json_encode($debug_data['filters'] ?? [], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <?php if (!empty($debug_data['errors'])): ?>
        <div>
            <h4 class="font-bold text-red-400 mb-2">Errors (<?= count($debug_data['errors']) ?>)</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto text-red-300"><?= json_encode($debug_data['errors'], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <?php endif; ?>
    </div>
</div>
<button onclick="document.getElementById('debug-panel').style.display = document.getElementById('debug-panel').style.display === 'none' ? 'block' : 'none'"
        class="fixed bottom-4 right-4 bg-gray-900 text-white p-3 rounded-full shadow-lg hover:bg-gray-700 z-50" title="Toggle Debug Panel">
    <i class="fas fa-bug"></i>
</button>
<?php } ?>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
