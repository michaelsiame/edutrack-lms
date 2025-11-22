<?php
/**
 * Admin Enrollments Management
 * View and manage all course enrollments
 */

require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Enrollment.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/User.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    $action = $_POST['action'] ?? null;
    $enrollmentId = $_POST['enrollment_id'] ?? null;
    
    if ($enrollmentId) {
        $enrollment = Enrollment::find($enrollmentId);
        
        if ($enrollment) {
            switch ($action) {
                case 'activate':
                    if ($enrollment->update(['enrollment_status' => 'active'])) {
                        flash('message', 'Enrollment activated successfully', 'success');
                    }
                    break;
                    
                case 'complete':
                    if ($enrollment->complete()) {
                        flash('message', 'Enrollment marked as completed', 'success');
                    }
                    break;
                    
                case 'cancel':
                    if ($enrollment->update(['enrollment_status' => 'cancelled'])) {
                        flash('message', 'Enrollment cancelled', 'success');
                    }
                    break;
                    
                case 'delete':
                    if ($db->delete('enrollments', 'id = ?', [$enrollmentId])) {
                        flash('message', 'Enrollment deleted', 'success');
                    }
                    break;
            }
        }
    }
    
    redirect(url('admin/enrollments/index.php'));
}

// Filters
$status = $_GET['status'] ?? '';
$courseId = $_GET['course'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$sql = "SELECT e.*, 
        c.title as course_title, c.slug as course_slug,
        u.first_name, u.last_name, u.email
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        JOIN users u ON e.user_id = u.id
        WHERE 1=1";

$params = [];

if ($status) {
    $sql .= " AND e.enrollment_status = ?";
    $params[] = $status;
}

if ($courseId) {
    $sql .= " AND e.course_id = ?";
    $params[] = $courseId;
}

if ($search) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR c.title LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Get total count
$countSql = "SELECT COUNT(*) FROM ($sql) as count_table";
$totalEnrollments = $db->fetchColumn($countSql, $params);
$totalPages = ceil($totalEnrollments / $perPage);

// Get enrollments
$sql .= " ORDER BY e.enrolled_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$enrollments = $db->fetchAll($sql, $params);

// Get courses for filter
$courses = Course::all(['order' => 'title ASC']);

// Stats
$stats = [
    'total' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments"),
    'active' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'active'"),
    'completed' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'completed'"),
    'cancelled' => $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'cancelled'")
];

$page_title = 'Manage Enrollments';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-clipboard-list text-primary-600 mr-2"></i>
                Enrollments
            </h1>
            <p class="text-gray-600 mt-1"><?= number_format($totalEnrollments) ?> total enrollments</p>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total']) ?></p>
                </div>
                <i class="fas fa-list text-3xl text-gray-400"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Active</p>
                    <p class="text-2xl font-bold text-green-600"><?= number_format($stats['active']) ?></p>
                </div>
                <i class="fas fa-check-circle text-3xl text-green-400"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-blue-600"><?= number_format($stats['completed']) ?></p>
                </div>
                <i class="fas fa-graduation-cap text-3xl text-blue-400"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Cancelled</p>
                    <p class="text-2xl font-bold text-red-600"><?= number_format($stats['cancelled']) ?></p>
                </div>
                <i class="fas fa-times-circle text-3xl text-red-400"></i>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <form method="GET" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                
                <!-- Search -->
                <div>
                    <input type="text" name="search" value="<?= sanitize($search) ?>"
                           placeholder="Search students or courses..."
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                
                <!-- Status Filter -->
                <div>
                    <select name="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Statuses</option>
                        <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <!-- Course Filter -->
                <div>
                    <select name="course" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= $courseId == $course['id'] ? 'selected' : '' ?>>
                                <?= sanitize($course['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <a href="<?= url('admin/enrollments/index.php') ?>" class="px-6 py-2 border rounded-lg hover:bg-gray-50">
                        Clear
                    </a>
                </div>
                
            </div>
        </form>
    </div>
    
    <!-- Enrollments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($enrollments)): ?>
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-clipboard-list text-5xl mb-4"></i>
                <p>No enrollments found</p>
            </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrolled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($enrollments as $enrollment): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900">
                                    <?= sanitize($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?>
                                </p>
                                <p class="text-sm text-gray-600"><?= sanitize($enrollment['email']) ?></p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="<?= url('course.php?slug=' . $enrollment['course_slug']) ?>" 
                               class="text-primary-600 hover:text-primary-700 font-medium">
                                <?= sanitize($enrollment['course_title']) ?>
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'completed' => 'bg-blue-100 text-blue-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            $color = $statusColors[$enrollment['enrollment_status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $color ?>">
                                <?= ucfirst($enrollment['enrollment_status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-primary-600 h-2 rounded-full"
                                         style="width: <?= $enrollment['progress'] ?>%"></div>
                                </div>
                                <span class="text-sm text-gray-600"><?= $enrollment['progress'] ?>%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?= timeAgo($enrollment['enrolled_at']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                
                                <?php if ($enrollment['enrollment_status'] == 'active'): ?>
                                <form method="POST" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="complete">
                                    <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                    <button type="submit" class="text-blue-600 hover:text-blue-800" title="Mark Complete">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                </form>
                                
                                <form method="POST" class="inline" onsubmit="return confirm('Cancel this enrollment?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-800" title="Cancel">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <?php if ($enrollment['enrollment_status'] == 'cancelled'): ?>
                                <form method="POST" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="activate">
                                    <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                    <button type="submit" class="text-green-600 hover:text-green-800" title="Reactivate">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this enrollment permanently?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Showing <?= number_format($offset + 1) ?> to <?= number_format(min($offset + $perPage, $totalEnrollments)) ?> 
                    of <?= number_format($totalEnrollments) ?> enrollments
                </p>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&status=<?= $status ?>&course=<?= $courseId ?>&search=<?= urlencode($search) ?>" 
                           class="px-4 py-2 border rounded hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?>&status=<?= $status ?>&course=<?= $courseId ?>&search=<?= urlencode($search) ?>"
                           class="px-4 py-2 border rounded <?= $i == $page ? 'bg-primary-600 text-white' : 'hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&status=<?= $status ?>&course=<?= $courseId ?>&search=<?= urlencode($search) ?>" 
                           class="px-4 py-2 border rounded hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this?');
}
</script>

<?php require_once '../../../src/templates/admin-footer.php'; ?>