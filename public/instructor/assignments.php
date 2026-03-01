<?php
/**
 * Instructor - Assignment Management & Grading
 * Enhanced assignment review and grading system
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Assignment.php';
require_once '../../src/classes/Submission.php';

$db = Database::getInstance();
$userId = currentUserId();

// Get instructor ID
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : $userId;

// Handle grading submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'grade') {
    validateCSRF();

    $submissionId = $_POST['submission_id'] ?? null;
    $pointsEarned = $_POST['points_earned'] ?? null;
    $feedback = trim($_POST['feedback'] ?? '');

    if ($submissionId && is_numeric($pointsEarned)) {
        $submission = Submission::find($submissionId);
        
        if ($submission) {
            $assignment = Assignment::find($submission->getAssignmentId());
            $course = Course::find($assignment->getCourseId());

            // Verify instructor owns the course
            if ($course->getInstructorId() == $instructorId || hasRole('admin')) {
                if ($submission->grade($pointsEarned, $feedback)) {
                    flash('message', 'Submission graded successfully!', 'success');
                    
                    // Send notification
                    if (class_exists('Notification')) {
                        Notification::notifyAssignmentGraded(
                            $submission->getUserId(),
                            $assignment->getTitle(),
                            "$pointsEarned / " . $assignment->getMaxPoints(),
                            $submissionId
                        );
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
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

// Get instructor's courses
$courses = Course::getByInstructor($instructorId);

// Build query
$where = [];
$params = [];

// Only show submissions from instructor's courses
$courseIds = array_map(fn($c) => $c['id'], $courses);
if (empty($courseIds)) {
    $courseIds = [0];
}
$where[] = 'a.course_id IN (' . implode(',', array_fill(0, count($courseIds), '?')) . ')';
$params = array_merge($params, $courseIds);

if ($courseFilter && in_array($courseFilter, $courseIds)) {
    $where[] = 'a.course_id = ?';
    $params[] = $courseFilter;
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

// Get total count
$totalSubmissions = (int) $db->fetchColumn("
    SELECT COUNT(*)
    FROM assignment_submissions s
    JOIN assignments a ON s.assignment_id = a.id
    $whereClause
", $params);

$totalPages = ceil($totalSubmissions / $perPage);
$offset = ($page - 1) * $perPage;

// Get submissions
$submissions = $db->fetchAll("
    SELECT s.*,
           a.title as assignment_title, a.max_points, a.due_date,
           c.title as course_title, c.slug as course_slug,
           u.first_name, u.last_name, u.email, u.avatar_url
    FROM assignment_submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN students st ON s.student_id = st.id
    JOIN users u ON st.user_id = u.id
    $whereClause
    ORDER BY s.submitted_at DESC
    LIMIT ? OFFSET ?
", array_merge($params, [$perPage, $offset]));

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
$stats = $db->fetchOne("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN s.status = 'submitted' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN s.status = 'graded' THEN 1 ELSE 0 END) as graded
    FROM assignment_submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    WHERE c.instructor_id = ?
", [$instructorId]);

$page_title = 'Assignment Submissions';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Assignment Submissions</h1>
                <p class="text-gray-500 mt-1">Review and grade student work</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="<?= url('instructor/courses.php') ?>" 
                   class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Courses
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-orange-600"><?= $stats['pending'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clock text-orange-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Graded</p>
                        <p class="text-2xl font-bold text-green-600"><?= $stats['graded'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-card border border-gray-100 p-5 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select name="course" onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= $courseFilter == $course['id'] ? 'selected' : '' ?>>
                                <?= sanitize($course['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assignment</label>
                    <select name="assignment" onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 <?= empty($assignments) ? 'opacity-50' : '' ?>">
                        <option value="">All Assignments</option>
                        <?php foreach ($assignments as $assignment): ?>
                            <option value="<?= $assignment['id'] ?>" <?= $assignmentFilter == $assignment['id'] ? 'selected' : '' ?>>
                                <?= sanitize($assignment['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Status</option>
                        <option value="submitted" <?= $statusFilter == 'submitted' ? 'selected' : '' ?>>Pending</option>
                        <option value="graded" <?= $statusFilter == 'graded' ? 'selected' : '' ?>>Graded</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <?php if ($courseFilter || $assignmentFilter || $statusFilter): ?>
                    <a href="assignments.php" class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-center">
                        <i class="fas fa-times mr-2"></i>Clear Filters
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Submissions List -->
        <?php if (empty($submissions)): ?>
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-inbox text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Submissions Found</h3>
            <p class="text-gray-500">There are no assignment submissions matching your filters.</p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Assignment</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Submitted</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Score</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($submissions as $sub):
                            $isLate = $sub['due_date'] && strtotime($sub['submitted_at']) > strtotime($sub['due_date']);
                        ?>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="<?= getGravatar($sub['email']) ?>" class="w-10 h-10 rounded-full mr-3">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            <?= sanitize($sub['first_name'] . ' ' . $sub['last_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500"><?= sanitize($sub['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?= sanitize($sub['assignment_title']) ?></div>
                                <div class="text-sm text-gray-500"><?= sanitize($sub['course_title']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?= date('M j, Y', strtotime($sub['submitted_at'])) ?></div>
                                <div class="text-sm text-gray-500"><?= date('g:i A', strtotime($sub['submitted_at'])) ?></div>
                                <?php if ($isLate): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700 mt-1">
                                        <i class="fas fa-clock mr-1"></i>Late
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($sub['status'] == 'graded'): ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <i class="fas fa-check mr-1"></i>Graded
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($sub['status'] == 'graded'): ?>
                                    <div class="font-bold text-gray-900">
                                        <?= $sub['points_earned'] ?> / <?= $sub['max_points'] ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= round(($sub['points_earned'] / $sub['max_points']) * 100, 1) ?>%
                                    </div>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">Not graded</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="viewSubmission(<?= $sub['id'] ?>)"
                                            class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="gradeSubmission(<?= $sub['id'] ?>)"
                                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="Grade">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Showing <?= (($page - 1) * $perPage) + 1 ?> - <?= min($page * $perPage, $totalSubmissions) ?> of <?= $totalSubmissions ?> submissions
            </p>
            <div class="flex items-center gap-2">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&course=<?= $courseFilter ?>&assignment=<?= $assignmentFilter ?>&status=<?= $statusFilter ?>" 
                   class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&course=<?= $courseFilter ?>&assignment=<?= $assignmentFilter ?>&status=<?= $statusFilter ?>" 
                   class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<!-- View Submission Modal -->
<div id="viewModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
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
<div id="gradeModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
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
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-sm text-gray-500 mb-1">Student</p>
                    <div class="flex items-center">
                        <img src="${getGravatar(submission.email)}" class="w-8 h-8 rounded-full mr-2">
                        <span class="font-medium text-gray-900">${escapeHtml(submission.first_name + ' ' + submission.last_name)}</span>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-sm text-gray-500 mb-1">Submitted</p>
                    <p class="font-medium text-gray-900">${new Date(submission.submitted_at).toLocaleString()}</p>
                </div>
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Assignment</h4>
                <p class="text-gray-700">${escapeHtml(submission.assignment_title)}</p>
                <p class="text-sm text-gray-500">Course: ${escapeHtml(submission.course_title)}</p>
            </div>
    `;

    if (submission.submission_text) {
        html += `
            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Submission Text</h4>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 whitespace-pre-wrap">${escapeHtml(submission.submission_text)}</div>
            </div>
        `;
    }

    if (submission.file_name) {
        html += `
            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Attached File</h4>
                <a href="<?= url('api/download.php') ?>?type=submission&id=${submission.id}"
                   class="inline-flex items-center px-4 py-2 bg-primary-50 text-primary-600 rounded-xl hover:bg-primary-100 transition">
                    <i class="fas fa-download mr-2"></i>
                    ${escapeHtml(submission.file_name)}
                </a>
            </div>
        `;
    }

    if (submission.status == 'graded') {
        html += `
            <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                <h4 class="font-semibold text-green-900 mb-2">Grade</h4>
                <div class="flex items-center gap-4">
                    <span class="text-3xl font-bold text-green-700">${submission.points_earned} / ${submission.max_points}</span>
                    <span class="text-lg text-green-600">${Math.round((submission.points_earned / submission.max_points) * 100)}%</span>
                </div>
            </div>
        `;

        if (submission.feedback) {
            html += `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Feedback</h4>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 whitespace-pre-wrap">${escapeHtml(submission.feedback)}</div>
                </div>
            `;
        }
    }

    html += `</div>`;

    document.getElementById('viewModalContent').innerHTML = html;
    openModal('viewModal');
}

function gradeSubmission(id) {
    const submission = submissions.find(s => s.id == id);
    if (!submission) return;

    const html = `
        <form method="POST" class="space-y-6">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="grade">
            <input type="hidden" name="submission_id" value="${submission.id}">

            <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-center mb-3">
                    <img src="${getGravatar(submission.email)}" class="w-10 h-10 rounded-full mr-3">
                    <div>
                        <p class="font-medium text-gray-900">${escapeHtml(submission.first_name + ' ' + submission.last_name)}</p>
                        <p class="text-sm text-gray-500">${escapeHtml(submission.assignment_title)}</p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Points Earned <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-4">
                    <input type="number" name="points_earned" required min="0" max="${submission.max_points}"
                           step="0.5" value="${submission.points_earned || ''}"
                           class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 text-lg font-semibold"
                           placeholder="0">
                    <span class="text-xl text-gray-500 font-medium">/ ${submission.max_points}</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Maximum points: ${submission.max_points}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Feedback</label>
                <textarea name="feedback" rows="4"
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"
                          placeholder="Provide constructive feedback to the student...">${submission.feedback || ''}</textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal('gradeModal')"
                        class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium transition">
                    <i class="fas fa-check mr-2"></i>Save Grade
                </button>
            </div>
        </form>
    `;

    document.getElementById('gradeModalContent').innerHTML = html;
    openModal('gradeModal');
}

function getGravatar(email) {
    return 'https://www.gravatar.com/avatar/' + (email ? btoa(email.toLowerCase().trim()) : '') + '?d=mp&s=100';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
