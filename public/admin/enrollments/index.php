<?php
/**
 * Admin Enrollments Management
 * View and manage all course enrollments
 */

require_once '../../../src/includes/admin-debug.php';
require_once '../../../src/middleware/admin-only.php';

// Filters
$courseId = $_GET['course_id'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build WHERE clause
$where = "WHERE 1=1";
$params = [];

if ($courseId) {
    $where .= " AND e.course_id = ?";
    $params[] = $courseId;
}

if ($status) {
    // Map filter values to database enum values
    $statusMapping = [
        'enrolled' => 'Enrolled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'dropped' => 'Dropped',
        'expired' => 'Expired',
    ];
    $where .= " AND e.enrollment_status = ?";
    $params[] = $statusMapping[$status] ?? $status;
}

if ($search) {
    $where .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR c.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get total count (simplified query without all the columns)
$countSql = "SELECT COUNT(DISTINCT e.id)
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             JOIN users u ON e.user_id = u.id
             $where";
$totalEnrollments = (int) $db->fetchColumn($countSql, $params);
$totalPages = ceil($totalEnrollments / $perPage);

// Build main query
$sql = "SELECT e.*,
        c.title as course_title, c.slug as course_slug,
        u.first_name, u.last_name, u.email,
        p.amount, p.payment_status
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        JOIN users u ON e.user_id = u.id
        LEFT JOIN payments p ON e.id = p.enrollment_id
        $where";

// Get enrollments
$sql .= " ORDER BY e.enrolled_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$enrollments = $db->fetchAll($sql, $params);

// Get statistics - use correct capitalized enum values
$stats = [
    'total' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments"),
    'active' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status IN ('Enrolled', 'In Progress')"),
    'completed' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'Completed'"),
    'dropped' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrollment_status = 'Dropped'"),
];

// Get courses for filter
$courses = $db->fetchAll("SELECT id, title FROM courses WHERE status = 'published' ORDER BY title");

$page_title = 'Manage Enrollments';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container-fluid px-4 py-6">

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Enrollments Management</h1>
        <p class="text-gray-600 mt-1">View and manage all course enrollments</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Total Enrollments</p>
            <p class="text-3xl font-bold text-gray-900 mt-2"><?= number_format($stats['total']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Active</p>
            <p class="text-3xl font-bold text-blue-600 mt-2"><?= number_format($stats['active']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Completed</p>
            <p class="text-3xl font-bold text-green-600 mt-2"><?= number_format($stats['completed']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Dropped</p>
            <p class="text-3xl font-bold text-red-600 mt-2"><?= number_format($stats['dropped']) ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                           placeholder="Student name, email, or course..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Course Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                    <select name="course_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>" <?= $courseId == $course['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="enrolled" <?= $status == 'enrolled' ? 'selected' : '' ?>>Enrolled</option>
                        <option value="in_progress" <?= $status == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="dropped" <?= $status == 'dropped' ? 'selected' : '' ?>>Dropped</option>
                        <option value="expired" <?= $status == 'expired' ? 'selected' : '' ?>>Expired</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search mr-2"></i>Apply Filters
                    </button>
                    <a href="<?= url('admin/enrollments/index.php') ?>" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Enrollments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrolled Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($enrollments)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        No enrollments found
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($enrollments as $enrollment): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?>
                            </div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($enrollment['email']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900"><?= htmlspecialchars($enrollment['course_title']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('M d, Y', strtotime($enrollment['enrolled_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $enrollStatus = $enrollment['enrollment_status'] ?? 'Enrolled';
                            if (in_array($enrollStatus, ['Enrolled', 'In Progress'])): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"><?= $enrollStatus ?></span>
                            <?php elseif ($enrollStatus == 'Completed'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                            <?php elseif ($enrollStatus == 'Dropped'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Dropped</span>
                            <?php elseif ($enrollStatus == 'Expired'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Expired</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800"><?= htmlspecialchars($enrollStatus) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $enrollment['progress'] ?>%"></div>
                                </div>
                                <span class="text-sm text-gray-600"><?= round($enrollment['progress']) ?>%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($enrollment['payment_status'] == 'completed'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                            <?php elseif ($enrollment['payment_status'] == 'pending'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="<?= url('admin/students/view.php?id=' . $enrollment['user_id']) ?>"
                               class="text-blue-600 hover:text-blue-900">
                                View Student
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $totalEnrollments) ?> of <?= $totalEnrollments ?> enrollments
        </div>
        <div class="flex space-x-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&course_id=<?= urlencode($courseId) ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                Previous
            </a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>&course_id=<?= urlencode($courseId) ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm <?= $i == $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-50' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&course_id=<?= urlencode($courseId) ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                Next
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
