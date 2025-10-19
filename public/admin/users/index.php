<?php
/**
 * Admin Users Management
 */

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
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$perPage = 20;

// Build query
$where = [];
$params = [];

if ($role) {
    $where[] = "role = ?";
    $params[] = $role;
}
if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}
if ($search) {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$totalUsers = $db->fetchColumn("SELECT COUNT(*) FROM users $whereClause", $params);
$totalPages = ceil($totalUsers / $perPage);
$offset = ($page - 1) * $perPage;

// Get users
$sql = "SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$users = $db->fetchAll($sql, $params);

// Get stats
$stats = [
    'total' => $db->fetchColumn("SELECT COUNT(*) FROM users"),
    'students' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'student'"),
    'instructors' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'instructor'"),
    'admins' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'admin'"),
];

$page_title = 'Manage Users';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-users text-primary-600 mr-2"></i>Manage Users
            </h1>
            <p class="text-gray-600 mt-1">Total: <?= number_format($totalUsers) ?> users</p>
        </div>
        <a href="<?= url('admin/users/create.php') ?>" class="btn-primary px-6 py-3 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Add User
        </a>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Total Users</p>
            <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Students</p>
            <p class="text-2xl font-bold text-blue-600"><?= number_format($stats['students']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Instructors</p>
            <p class="text-2xl font-bold text-purple-600"><?= number_format($stats['instructors']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-600">Admins</p>
            <p class="text-2xl font-bold text-red-600"><?= number_format($stats['admins']) ?></p>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Search users..." 
                   class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            
            <select name="role" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Roles</option>
                <option value="student" <?= $role == 'student' ? 'selected' : '' ?>>Students</option>
                <option value="instructor" <?= $role == 'instructor' ? 'selected' : '' ?>>Instructors</option>
                <option value="admin" <?= $role == 'admin' ? 'selected' : '' ?>>Admins</option>
            </select>
            
            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Status</option>
                <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="suspended" <?= $status == 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
            
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="<?= url('admin/users/index.php') ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>
    
    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($users as $userData): ?>
                    <?php $user = new User($userData['id']); ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <img src="<?= getGravatar($user->email) ?>" class="h-10 w-10 rounded-full mr-3">
                                <div>
                                    <div class="font-medium text-gray-900"><?= sanitize($user->first_name . ' ' . $user->last_name) ?></div>
                                    <div class="text-sm text-gray-500"><?= sanitize($user->email) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="change_role">
                                <input type="hidden" name="user_id" value="<?= $user->getId() ?>">
                                <select name="role" onchange="this.form.submit()" 
                                        class="text-xs rounded-full px-3 py-1 font-semibold border-0
                                        <?php
                                        switch($user->role) {
                                            case 'admin': echo 'bg-red-100 text-red-800'; break;
                                            case 'instructor': echo 'bg-purple-100 text-purple-800'; break;
                                            case 'student': echo 'bg-blue-100 text-blue-800'; break;
                                        }
                                        ?>">
                                    <option value="student" <?= $user->role == 'student' ? 'selected' : '' ?>>Student</option>
                                    <option value="instructor" <?= $user->role == 'instructor' ? 'selected' : '' ?>>Instructor</option>
                                    <option value="admin" <?= $user->role == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="change_status">
                                <input type="hidden" name="user_id" value="<?= $user->getId() ?>">
                                <select name="status" onchange="this.form.submit()" 
                                        class="text-xs rounded-full px-3 py-1 font-semibold border-0
                                        <?php
                                        switch($user->status) {
                                            case 'active': echo 'bg-green-100 text-green-800'; break;
                                            case 'inactive': echo 'bg-gray-100 text-gray-800'; break;
                                            case 'suspended': echo 'bg-red-100 text-red-800'; break;
                                        }
                                        ?>">
                                    <option value="active" <?= $user->status == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $user->status == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="suspended" <?= $user->status == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= timeAgo($user->created_at) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= $user->last_login ? timeAgo($user->last_login) : 'Never' ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="<?= url('admin/users/edit.php?id=' . $user->getId()) ?>" class="text-blue-600 hover:text-blue-800" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($user->getId() != currentUserId()): ?>
                                <form method="POST" class="inline" onsubmit="return confirmDelete('Delete this user and all their data?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= $user->getId() ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
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
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Showing <?= number_format($offset + 1) ?> to <?= number_format(min($offset + $perPage, $totalUsers)) ?> of <?= number_format($totalUsers) ?> users
                </p>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&role=<?= $role ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>" 
                           class="px-3 py-2 border rounded hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&role=<?= $role ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>" 
                           class="px-3 py-2 border rounded hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>