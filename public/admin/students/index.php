<?php
/**
 * Admin Students Management
 * View and manage all students
 */

require_once '../../../src/middleware/admin-only.php';

// Handle student actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('message', 'Invalid request', 'error');
        redirect(url('admin/students/index.php'));
    }

    $action = $_POST['action'] ?? null;
    $userId = $_POST['user_id'] ?? null;

    if ($action == 'delete' && $userId) {
        // Don't delete users, just deactivate them
        $db->update('users', ['status' => 'inactive'], 'id = ?', [$userId]);
        flash('message', 'Student deactivated successfully', 'success');
    } elseif ($action == 'activate' && $userId) {
        $db->update('users', ['status' => 'active'], 'id = ?', [$userId]);
        flash('message', 'Student activated successfully', 'success');
    } elseif ($action == 'suspend' && $userId) {
        $db->update('users', ['status' => 'suspended'], 'id = ?', [$userId]);
        flash('message', 'Student suspended successfully', 'success');
    }

    redirect(url('admin/students/index.php'));
}

// Filters
$status = $_GET['status'] ?? 'active';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$sql = "SELECT u.*,
        COUNT(DISTINCT e.id) as enrollment_count,
        COUNT(DISTINCT CASE WHEN e.status = 'completed' THEN e.id END) as completed_courses,
        SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END) as total_spent
        FROM users u
        LEFT JOIN enrollments e ON u.id = e.user_id
        LEFT JOIN payments p ON u.id = p.user_id
        WHERE u.role = 'student'";

$params = [];

if ($status && $status != 'all') {
    $sql .= " AND u.status = ?";
    $params[] = $status;
}

if ($search) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " GROUP BY u.id";

// Get total count
$countSql = "SELECT COUNT(*) FROM ($sql) as count_table";
$totalStudents = (int) $db->fetchColumn($countSql, $params);
$totalPages = ceil($totalStudents / $perPage);

// Get students
$sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$students = $db->fetchAll($sql, $params);

// Get statistics
$stats = [
    'total' => (int) $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'student'"),
    'active' => (int) $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'active'"),
    'inactive' => (int) $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'inactive'"),
    'suspended' => (int) $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'suspended'"),
];

$page_title = 'Manage Students';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container-fluid px-4 py-6">

    <!-- Page Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Students</h1>
            <p class="text-gray-600 mt-1">Manage student accounts and enrollments</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Students</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Active</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['active']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-ban text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Suspended</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['suspended']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-gray-100 text-gray-600">
                    <i class="fas fa-user-slash text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Inactive</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['inactive']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <input type="text"
                           name="search"
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Search students..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Status Filter -->
                <div class="min-w-[150px]">
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="suspended" <?= $status == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>

                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>

                <?php if ($search || $status): ?>
                <a href="<?= url('admin/students/index.php') ?>" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollments</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($students)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-3 text-gray-300"></i>
                        <p>No students found</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-semibold">
                                            <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">ID: <?= $student['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900"><?= htmlspecialchars($student['email']) ?></div>
                            <?php if ($student['phone']): ?>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($student['phone']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?= $student['enrollment_count'] ?> courses
                            </div>
                            <div class="text-sm text-gray-500">
                                <?= $student['completed_courses'] ?> completed
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ZMW <?= number_format($student['total_spent'], 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($student['status'] == 'active'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            <?php elseif ($student['status'] == 'suspended'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Suspended</span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('M d, Y', strtotime($student['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?= url('admin/students/view.php?id=' . $student['id']) ?>"
                               class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye"></i>
                            </a>

                            <div class="inline-block relative" x-data="{ open: false }">
                                <button @click="open = !open" class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div x-show="open"
                                     @click.away="open = false"
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                    <form method="POST" class="py-1">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="user_id" value="<?= $student['id'] ?>">

                                        <?php if ($student['status'] == 'active'): ?>
                                        <button type="submit" name="action" value="suspend"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-ban mr-2"></i>Suspend
                                        </button>
                                        <?php else: ?>
                                        <button type="submit" name="action" value="activate"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-check mr-2"></i>Activate
                                        </button>
                                        <?php endif; ?>

                                        <button type="submit" name="action" value="delete"
                                                onclick="return confirm('Are you sure you want to deactivate this student?')"
                                                class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                            <i class="fas fa-trash mr-2"></i>Deactivate
                                        </button>
                                    </form>
                                </div>
                            </div>
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
            Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $totalStudents) ?> of <?= $totalStudents ?> students
        </div>
        <div class="flex space-x-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                Previous
            </a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm <?= $i == $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-50' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                Next
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
