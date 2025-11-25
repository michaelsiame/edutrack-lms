<?php
/**
 * Admin Users Management
 */

require_once '../../../src/includes/admin-debug.php';
require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/User.php';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    $action = $_POST['action'] ?? null;
    $userId = $_POST['user_id'] ?? null;
    
    if ($action == 'delete' && $userId) {
        $user = User::find($userId);
        if ($user && $user->getId() != currentUserId()) {
            if ($user->delete()) {
                flash('message', 'User deleted successfully', 'success');
            } else {
                flash('message', 'Failed to delete user', 'error');
            }
        }
    } elseif ($action == 'change_status' && $userId) {
        $status = $_POST['status'] ?? null;
        if (in_array($status, ['active', 'inactive', 'suspended'])) {
            $user = User::find($userId);
            if ($user && $user->update(['status' => $status])) {
                flash('message', 'User status updated', 'success');
            }
        }
    } elseif ($action == 'change_role' && $userId) {
        $role = $_POST['role'] ?? null;
        if (in_array($role, ['student', 'instructor', 'admin'])) {
            $user = User::find($userId);
            if ($user && $user->update(['role' => $role])) {
                flash('message', 'User role updated', 'success');
            }
        }
    }
    
    redirect(url('admin/users/index.php'));
}

// Filters
$roleFilter = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$perPage = 20;

// Build query - use junction table for roles
$baseQuery = "FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id";

$where = [];
$params = [];

if ($roleFilter) {
    $roleMapping = ['student' => 'Student', 'instructor' => 'Instructor', 'admin' => 'Admin'];
    $where[] = "r.role_name = ?";
    $params[] = $roleMapping[$roleFilter] ?? $roleFilter;
}
if ($status) {
    $where[] = "u.status = ?";
    $params[] = $status;
}
if ($search) {
    $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$totalUsers = $db->fetchColumn("SELECT COUNT(DISTINCT u.id) $baseQuery $whereClause", $params);
$totalPages = ceil($totalUsers / $perPage);
$offset = ($page - 1) * $perPage;

// Get users with role info
$sql = "SELECT DISTINCT u.*, COALESCE(r.role_name, 'Student') as role_name $baseQuery $whereClause ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$users = $db->fetchAll($sql, $params);

// Get stats
require_once '../../../src/classes/Statistics.php';
$stats = [
    'total' => $db->fetchColumn("SELECT COUNT(*) FROM users"),
    'students' => Statistics::getTotalStudents(),
    'instructors' => Statistics::getTotalInstructors(),
    'admins' => Statistics::getTotalAdmins(),
];

$page_title = 'Manage Users';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-6 lg:py-8 max-w-7xl">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-users text-primary-600"></i>
                <span>Manage Users</span>
            </h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">
                Total: <?= number_format($totalUsers) ?> user<?= $totalUsers != 1 ? 's' : '' ?>
            </p>
        </div>
        <a href="<?= url('admin/users/create.php') ?>" class="btn btn-primary self-start sm:self-center">
            <i class="fas fa-plus"></i>
            <span>Add User</span>
        </a>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-gray-400 hover:shadow-md transition-shadow">
            <p class="text-xs sm:text-sm text-gray-500 font-medium">Total Users</p>
            <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1"><?= number_format($stats['total']) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500 hover:shadow-md transition-shadow">
            <p class="text-xs sm:text-sm text-gray-500 font-medium">Students</p>
            <p class="text-xl sm:text-2xl font-bold text-blue-600 mt-1"><?= number_format($stats['students']) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500 hover:shadow-md transition-shadow">
            <p class="text-xs sm:text-sm text-gray-500 font-medium">Instructors</p>
            <p class="text-xl sm:text-2xl font-bold text-purple-600 mt-1"><?= number_format($stats['instructors']) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500 hover:shadow-md transition-shadow">
            <p class="text-xs sm:text-sm text-gray-500 font-medium">Admins</p>
            <p class="text-xl sm:text-2xl font-bold text-red-600 mt-1"><?= number_format($stats['admins']) ?></p>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm mb-6 p-4">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Search users..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>

                <select name="role" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white transition-colors">
                    <option value="">All Roles</option>
                    <option value="student" <?= $roleFilter == 'student' ? 'selected' : '' ?>>Students</option>
                    <option value="instructor" <?= $roleFilter == 'instructor' ? 'selected' : '' ?>>Instructors</option>
                    <option value="admin" <?= $roleFilter == 'admin' ? 'selected' : '' ?>>Admins</option>
                </select>

                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white transition-colors">
                    <option value="">All Status</option>
                    <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= $status == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 btn btn-primary">
                        <i class="fas fa-filter"></i>
                        <span class="hidden sm:inline">Filter</span>
                    </button>
                    <a href="<?= url('admin/users/index.php') ?>" class="btn btn-secondary px-3" title="Reset filters">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Role</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Joined</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Last Login</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php foreach ($users as $userData): ?>
                        <?php
                        $userRole = strtolower($userData['role_name'] ?? 'student');
                        $userStatus = $userData['status'] ?? 'active';
                        $roleColors = [
                            'admin' => 'bg-red-100 text-red-800',
                            'instructor' => 'bg-purple-100 text-purple-800',
                            'student' => 'bg-blue-100 text-blue-800',
                        ];
                        $roleClass = $roleColors[$userRole] ?? 'bg-blue-100 text-blue-800';
                        $statusColors = [
                            'active' => 'bg-green-100 text-green-800',
                            'inactive' => 'bg-gray-100 text-gray-800',
                            'suspended' => 'bg-red-100 text-red-800',
                        ];
                        $statusClass = $statusColors[$userStatus] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="<?= getGravatar($userData['email']) ?>"
                                         alt="<?= sanitize($userData['first_name']) ?>"
                                         class="h-10 w-10 rounded-full ring-2 ring-gray-100 flex-shrink-0">
                                    <div class="min-w-0">
                                        <div class="font-medium text-gray-900 truncate"><?= sanitize($userData['first_name'] . ' ' . $userData['last_name']) ?></div>
                                        <div class="text-sm text-gray-500 truncate"><?= sanitize($userData['email']) ?></div>
                                        <!-- Mobile: Show role badge -->
                                        <div class="sm:hidden mt-1">
                                            <span class="inline-flex text-xs rounded-full px-2 py-0.5 font-semibold <?= $roleClass ?>">
                                                <?= ucfirst($userRole) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 hidden sm:table-cell">
                                <span class="inline-flex text-xs rounded-full px-3 py-1 font-semibold <?= $roleClass ?>">
                                    <?= ucfirst($userRole) ?>
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 hidden md:table-cell">
                                <form method="POST" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="change_status">
                                    <input type="hidden" name="user_id" value="<?= $userData['id'] ?>">
                                    <select name="status" onchange="this.form.submit()"
                                            class="text-xs rounded-full px-3 py-1.5 font-semibold border-0 cursor-pointer <?= $statusClass ?> hover:ring-2 hover:ring-offset-1 hover:ring-gray-300 transition-all">
                                        <option value="active" <?= $userStatus == 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $userStatus == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        <option value="suspended" <?= $userStatus == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 hidden lg:table-cell whitespace-nowrap"><?= timeAgo($userData['created_at']) ?></td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 hidden lg:table-cell whitespace-nowrap"><?= !empty($userData['last_login_at']) ? timeAgo($userData['last_login_at']) : 'Never' ?></td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="<?= url('admin/users/edit.php?id=' . $userData['id']) ?>"
                                       class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="Edit user">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($userData['id'] != currentUserId()): ?>
                                    <form method="POST" class="inline" onsubmit="return confirmDelete('Delete this user and all their data?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $userData['id'] ?>">
                                        <button type="submit"
                                                class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Delete user">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-600 order-2 sm:order-1">
                    Showing <span class="font-medium"><?= number_format($offset + 1) ?></span> to
                    <span class="font-medium"><?= number_format(min($offset + $perPage, $totalUsers)) ?></span> of
                    <span class="font-medium"><?= number_format($totalUsers) ?></span> users
                </p>
                <div class="flex items-center gap-2 order-1 sm:order-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&role=<?= $roleFilter ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"
                           class="btn btn-secondary btn-sm">
                            <i class="fas fa-chevron-left"></i>
                            <span class="hidden sm:inline">Previous</span>
                        </a>
                    <?php endif; ?>

                    <span class="px-3 py-1.5 text-sm text-gray-600">
                        Page <?= $page ?> of <?= $totalPages ?>
                    </span>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&role=<?= $roleFilter ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"
                           class="btn btn-secondary btn-sm">
                            <span class="hidden sm:inline">Next</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>