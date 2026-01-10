<?php
/**
 * Users Management Page
 * Uses the proper schema with user_roles table
 */

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status' && isset($_POST['user_id'], $_POST['status'])) {
        $userId = (int)$_POST['user_id'];
        $status = $_POST['status'] === 'active' ? 'active' : 'suspended';
        $db->update('users', ['status' => $status], 'id = ?', [$userId]);
        header('Location: ?page=users&msg=status_updated');
        exit;
    }

    if ($action === 'delete' && isset($_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];
        // Delete user roles first
        $db->delete('user_roles', 'user_id = ?', [$userId]);
        $db->delete('users', 'id = ?', [$userId]);
        header('Location: ?page=users&msg=deleted');
        exit;
    }

    if ($action === 'add') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = strtolower(str_replace(' ', '.', $firstName . '.' . $lastName));
        $phone = trim($_POST['phone'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 4); // Default to Student (4)
        $password = password_hash($_POST['password'] ?? 'password123', PASSWORD_DEFAULT);

        if ($firstName && $lastName && $email) {
            // Insert user
            $db->insert('users', [
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => $password,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $newUserId = $db->lastInsertId();

            // Assign role
            if ($newUserId) {
                $db->insert('user_roles', [
                    'user_id' => $newUserId,
                    'role_id' => $roleId,
                    'assigned_at' => date('Y-m-d H:i:s')
                ]);
            }

            header('Location: ?page=users&msg=added');
            exit;
        }
    }
}

// Fetch users with their roles
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

$sql = "
    SELECT
        u.id,
        u.username,
        u.first_name,
        u.last_name,
        CONCAT(u.first_name, ' ', u.last_name) as full_name,
        u.email,
        u.phone,
        u.status,
        u.created_at,
        r.role_name,
        r.id as role_id
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    WHERE 1=1
";
$params = [];

if ($search) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($roleFilter) {
    $sql .= " AND r.role_name = ?";
    $params[] = $roleFilter;
}

$sql .= " ORDER BY u.created_at DESC";
$users = $db->fetchAll($sql, $params);

// Get available roles for the dropdown
$roles = $db->fetchAll("SELECT id, role_name FROM roles ORDER BY id");

$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Users</h2>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add User
        </button>
    </div>

    <?php if ($msg): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?= $msg === 'added' ? 'User added successfully!' : ($msg === 'deleted' ? 'User deleted!' : 'Status updated!') ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" class="flex gap-4 items-center">
            <input type="hidden" name="page" value="users">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email..." class="px-4 py-2 border rounded-lg flex-1">
            <select name="role" class="px-4 py-2 border rounded-lg">
                <option value="">All Roles</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= htmlspecialchars($role['role_name']) ?>" <?= $roleFilter === $role['role_name'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($role['role_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">Filter</button>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm mr-3">
                                    <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <?= htmlspecialchars($user['full_name'] ?? 'Unknown') ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-6 py-4">
                            <?php
                            $roleName = $user['role_name'] ?? 'No Role';
                            $roleClass = match($roleName) {
                                'Super Admin', 'Admin' => 'bg-purple-100 text-purple-700',
                                'Instructor' => 'bg-blue-100 text-blue-700',
                                'Student' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-700'
                            };
                            ?>
                            <span class="px-2 py-1 text-xs rounded-full <?= $roleClass ?>">
                                <?= htmlspecialchars($roleName) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full <?= $user['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-sm"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $user['status'] === 'active' ? 'suspended' : 'active' ?>">
                                    <button type="submit" class="text-sm px-2 py-1 rounded <?= $user['status'] === 'active' ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' ?>">
                                        <?= $user['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                                    </button>
                                </form>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this user?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="text-sm px-2 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No users found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Add New User</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role_id" class="w-full px-3 py-2 border rounded-lg">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>" <?= $role['role_name'] === 'Student' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['role_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" value="password123" class="w-full px-3 py-2 border rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Default: password123</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add User</button>
            </div>
        </form>
    </div>
</div>
