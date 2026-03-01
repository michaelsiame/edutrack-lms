<?php
/**
 * Instructor - Students Management
 * Enhanced student management for class control
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';

$db = Database::getInstance();
$userId = currentUserId();

// Get instructor ID
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : $userId;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_progress' && isset($_POST['enrollment_id'])) {
        $enrollmentId = (int)$_POST['enrollment_id'];
        $progress = (int)$_POST['progress'];
        $progress = max(0, min(100, $progress));
        
        $db->query("UPDATE enrollments SET progress = ? WHERE id = ?", [$progress, $enrollmentId]);
        flash('message', 'Student progress updated successfully', 'success');
    }
    
    if ($action === 'send_message' && isset($_POST['student_id'])) {
        // Placeholder for messaging functionality
        flash('message', 'Message sent to student', 'success');
    }
    
    redirect($_SERVER['REQUEST_URI']);
}

// Get filter parameters
$courseFilter = $_GET['course'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

// Get instructor's courses for filter
$courses = $db->fetchAll("
    SELECT id, title FROM courses
    WHERE instructor_id = ? AND status != 'archived'
    ORDER BY title
", [$instructorId]);

// Build query
$where = ["c.instructor_id = ?"];
$params = [$instructorId];

if ($courseFilter) {
    $where[] = "e.course_id = ?";
    $params[] = $courseFilter;
}

if ($statusFilter) {
    $where[] = "e.enrollment_status = ?";
    $params[] = $statusFilter;
}

if ($search) {
    $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Get total count
$totalStudents = (int) $db->fetchColumn("
    SELECT COUNT(DISTINCT u.id)
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON e.user_id = u.id
    $whereClause
", $params);

$totalPages = ceil($totalStudents / $perPage);
$offset = ($page - 1) * $perPage;

// Get students with details
$students = $db->fetchAll("
    SELECT u.id as user_id, u.first_name, u.last_name, u.email, u.avatar_url, u.created_at as joined_date,
           e.id as enrollment_id, e.enrollment_status, e.progress, e.enrolled_at, e.completed_at,
           c.id as course_id, c.title as course_title, c.slug as course_slug
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON e.user_id = u.id
    $whereClause
    ORDER BY e.enrolled_at DESC
    LIMIT ? OFFSET ?
", array_merge($params, [$perPage, $offset]));

// Statistics
$stats = $db->fetchOne("
    SELECT 
        COUNT(DISTINCT u.id) as total_students,
        COUNT(DISTINCT CASE WHEN e.enrollment_status IN ('Enrolled', 'In Progress') THEN u.id END) as active_students,
        COUNT(DISTINCT CASE WHEN e.enrollment_status = 'Completed' THEN u.id END) as completed_students,
        COUNT(DISTINCT e.id) as total_enrollments
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON e.user_id = u.id
    WHERE c.instructor_id = ?
", [$instructorId]);

$page_title = 'My Students';
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Students</h1>
                <p class="text-gray-500 mt-1">Manage students enrolled in your courses</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center gap-3">
                <button onclick="exportTableToCSV('students-table', 'students-<?= date('Y-m-d') ?>.csv')"
                        class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition">
                    <i class="fas fa-download mr-2"></i>Export
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Students</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total_students'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-blue-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Active</p>
                        <p class="text-2xl font-bold text-green-600"><?= $stats['active_students'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-check text-green-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Completed</p>
                        <p class="text-2xl font-bold text-purple-600"><?= $stats['completed_students'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-purple-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Enrollments</p>
                        <p class="text-2xl font-bold text-orange-600"><?= $stats['total_enrollments'] ?? 0 ?></p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-orange-500"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-card border border-gray-100 p-5 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                               placeholder="Name or email..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select name="course" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= $courseFilter == $course['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($course['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Status</option>
                        <option value="Enrolled" <?= $statusFilter === 'Enrolled' ? 'selected' : '' ?>>Enrolled</option>
                        <option value="In Progress" <?= $statusFilter === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed" <?= $statusFilter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <?php if ($courseFilter || $statusFilter || $search): ?>
                    <a href="students.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-times"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Students Table -->
        <?php if (empty($students)): ?>
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-users text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Students Found</h3>
            <p class="text-gray-500">No students match your search criteria.</p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="students-table" class="min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrolled</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($students as $student): 
                            $statusColors = [
                                'Enrolled' => 'bg-blue-100 text-blue-700',
                                'In Progress' => 'bg-yellow-100 text-yellow-700',
                                'Completed' => 'bg-green-100 text-green-700'
                            ];
                            $statusColor = $statusColors[$student['enrollment_status']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="<?= getGravatar($student['email']) ?>" class="w-10 h-10 rounded-full mr-3">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($student['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="<?= url('instructor/course-edit.php?id=' . $student['course_id']) ?>" 
                                   class="text-primary-600 hover:text-primary-700 font-medium">
                                    <?= htmlspecialchars($student['course_title']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="bg-primary-500 h-2 rounded-full transition-all" style="width: <?= $student['progress'] ?? 0 ?>"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700"><?= $student['progress'] ?? 0 ?>%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusColor ?>">
                                    <?= $student['enrollment_status'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= date('M d, Y', strtotime($student['enrolled_at'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="updateProgress(<?= $student['enrollment_id'] ?>, <?= $student['progress'] ?>)" 
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Update Progress">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                    <button onclick="viewStudent(<?= $student['user_id'] ?>)" 
                                            class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition" title="View Details">
                                        <i class="fas fa-eye"></i>
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
                Showing <?= (($page - 1) * $perPage) + 1 ?> - <?= min($page * $perPage, $totalStudents) ?> of <?= $totalStudents ?> students
            </p>
            <div class="flex items-center gap-2">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&course=<?= $courseFilter ?>&status=<?= $statusFilter ?>&search=<?= urlencode($search) ?>" 
                   class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&course=<?= $courseFilter ?>&status=<?= $statusFilter ?>&search=<?= urlencode($search) ?>" 
                   class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<!-- Update Progress Modal -->
<div id="progressModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">Update Student Progress</h3>
            <button onclick="closeModal('progressModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" id="progressForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_progress">
            <input type="hidden" name="enrollment_id" id="progressEnrollmentId">
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Progress Percentage</label>
                <div class="flex items-center gap-4">
                    <input type="range" name="progress" id="progressRange" min="0" max="100" 
                           class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    <span id="progressValue" class="text-2xl font-bold text-primary-600 w-16 text-right">0%</span>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('progressModal')" 
                        class="flex-1 px-4 py-2 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition">
                    Save Progress
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateProgress(enrollmentId, currentProgress) {
    document.getElementById('progressEnrollmentId').value = enrollmentId;
    document.getElementById('progressRange').value = currentProgress;
    document.getElementById('progressValue').textContent = currentProgress + '%';
    openModal('progressModal');
}

document.getElementById('progressRange').addEventListener('input', function() {
    document.getElementById('progressValue').textContent = this.value + '%';
});

function viewStudent(userId) {
    // Placeholder for viewing student details
    showToast('Student details view coming soon', 'info');
}
</script>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
