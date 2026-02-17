<?php
/**
 * Users Management Page - Full CRUD
 * Features: Add, Edit, Delete, Status Toggle, Search, Filter, Pagination,
 *           Student ID display, CSRF protection, Bulk Import/Export
 */

// Pagination
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

// Fetch users with their roles and student numbers
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

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
        u.last_login,
        r.role_name,
        r.id as role_id,
        s.student_number,
        (SELECT COUNT(*) FROM enrollments WHERE user_id = u.id) as enrollment_count
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    LEFT JOIN students s ON u.id = s.user_id
    WHERE 1=1
";
$countSql = "SELECT COUNT(DISTINCT u.id) FROM users u LEFT JOIN user_roles ur ON u.id = ur.user_id LEFT JOIN roles r ON ur.role_id = r.id WHERE 1=1";
$params = [];

if ($search) {
    $searchCondition = " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $sql .= $searchCondition;
    $countSql .= $searchCondition;
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}

if ($roleFilter) {
    $sql .= " AND r.role_name = ?";
    $countSql .= " AND r.role_name = ?";
    $params[] = $roleFilter;
}

if ($statusFilter) {
    $sql .= " AND u.status = ?";
    $countSql .= " AND u.status = ?";
    $params[] = $statusFilter;
}

$totalUsers = $db->fetchColumn($countSql, $params);
$totalPages = ceil($totalUsers / $per_page);

$sql .= " ORDER BY u.created_at DESC LIMIT $per_page OFFSET $offset";
$users = $db->fetchAll($sql, $params);

// Get available roles for the dropdown
$roles = $db->fetchAll("SELECT id, role_name FROM roles ORDER BY id");

// Stats
$totalStudentsCount = $db->fetchColumn("SELECT COUNT(DISTINCT u.id) FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE r.role_name = 'Student'");
$totalInstructorsCount = $db->fetchColumn("SELECT COUNT(DISTINCT u.id) FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE r.role_name = 'Instructor'");
$activeUsersCount = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'active'");
$newThisMonth = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");

$msg = $_GET['msg'] ?? '';

// Get generated password from session (shown once)
$generatedPassword = $_SESSION['last_generated_password'] ?? null;
$createdUser = $_SESSION['last_created_user'] ?? null;
unset($_SESSION['last_generated_password'], $_SESSION['last_created_user']);

$resetPassword = $_SESSION['last_reset_password'] ?? null;
$resetUser = $_SESSION['last_reset_user'] ?? null;
unset($_SESSION['last_reset_password'], $_SESSION['last_reset_user']);

$bulkErrors = $_SESSION['bulk_import_errors'] ?? [];
unset($_SESSION['bulk_import_errors']);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
            <p class="text-gray-500 text-sm mt-1">Manage students, instructors, and administrators</p>
        </div>
        <div class="flex gap-2">
            <div class="relative" id="bulkDropdown">
                <button onclick="document.getElementById('bulkMenu').classList.toggle('hidden')" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center gap-2 shadow-sm">
                    <i class="fas fa-ellipsis-v"></i>
                    <span>Bulk</span>
                </button>
                <div id="bulkMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-10">
                    <button onclick="document.getElementById('importModal').classList.remove('hidden'); document.getElementById('bulkMenu').classList.add('hidden');" class="w-full text-left px-4 py-2 hover:bg-gray-50 rounded-t-lg">
                        <i class="fas fa-upload mr-2 text-blue-600"></i>Import CSV
                    </button>
                    <form method="POST" class="inline w-full">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="export">
                        <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-50 rounded-b-lg">
                            <i class="fas fa-download mr-2 text-green-600"></i>Export CSV
                        </button>
                    </form>
                </div>
            </div>
            <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 shadow-sm">
                <i class="fas fa-user-plus"></i>
                <span>Add User</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($msg): ?>
        <div class="<?= in_array($msg, ['cannot_delete', 'email_exists', 'csrf_error', 'invalid_file']) ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700' ?> border px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas <?= in_array($msg, ['cannot_delete', 'email_exists', 'csrf_error', 'invalid_file']) ? 'fa-exclamation-circle' : 'fa-check-circle' ?>"></i>
            <?php
            echo match($msg) {
                'added' => 'User added successfully!',
                'updated' => 'User updated successfully!',
                'deleted' => 'User deleted successfully!',
                'status_updated' => 'User status updated!',
                'password_reset' => 'Password has been reset!',
                'email_exists' => 'Email address already exists!',
                'cannot_delete' => 'Cannot delete user with active enrollments!',
                'csrf_error' => 'Security token expired. Please try again.',
                'invalid_file' => 'Invalid file. Please upload a valid CSV file.',
                'bulk_imported' => ($_GET['count'] ?? 0) . ' users imported successfully!',
                default => 'Action completed!'
            };
            ?>
        </div>
    <?php endif; ?>

    <!-- Show generated password after user creation -->
    <?php if ($generatedPassword && $msg === 'added'): ?>
        <div class="bg-blue-50 border border-blue-300 text-blue-800 px-4 py-3 rounded-lg">
            <div class="flex items-center gap-2 mb-1">
                <i class="fas fa-key"></i>
                <strong>Password for <?= htmlspecialchars($createdUser) ?>:</strong>
            </div>
            <div class="flex items-center gap-2">
                <code class="bg-white px-3 py-1 rounded border font-mono text-lg" id="genPassword"><?= htmlspecialchars($generatedPassword) ?></code>
                <button onclick="navigator.clipboard.writeText(document.getElementById('genPassword').textContent); this.innerHTML='<i class=\'fas fa-check\'></i> Copied'" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            <p class="text-xs text-blue-600 mt-1">Please share this password with the user. It will not be shown again.</p>
        </div>
    <?php endif; ?>

    <!-- Show reset password -->
    <?php if ($resetPassword && $msg === 'password_reset'): ?>
        <div class="bg-orange-50 border border-orange-300 text-orange-800 px-4 py-3 rounded-lg">
            <div class="flex items-center gap-2 mb-1">
                <i class="fas fa-key"></i>
                <strong>New password for <?= htmlspecialchars($resetUser) ?>:</strong>
            </div>
            <div class="flex items-center gap-2">
                <code class="bg-white px-3 py-1 rounded border font-mono text-lg" id="resetPass"><?= htmlspecialchars($resetPassword) ?></code>
                <button onclick="navigator.clipboard.writeText(document.getElementById('resetPass').textContent); this.innerHTML='<i class=\'fas fa-check\'></i> Copied'" class="px-3 py-1 bg-orange-600 text-white rounded text-sm hover:bg-orange-700">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            <p class="text-xs text-orange-600 mt-1">Please share this password with the user. It will not be shown again.</p>
        </div>
    <?php endif; ?>

    <!-- Bulk import errors -->
    <?php if (!empty($bulkErrors)): ?>
        <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg">
            <strong>Import warnings:</strong>
            <ul class="list-disc list-inside mt-1 text-sm">
                <?php foreach (array_slice($bulkErrors, 0, 10) as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
                <?php if (count($bulkErrors) > 10): ?>
                    <li>...and <?= count($bulkErrors) - 10 ?> more</li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg"><i class="fas fa-user-graduate"></i></div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $totalStudentsCount ?></p>
                    <p class="text-xs text-gray-500">Students</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 text-purple-600 rounded-lg"><i class="fas fa-chalkboard-teacher"></i></div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $totalInstructorsCount ?></p>
                    <p class="text-xs text-gray-500">Instructors</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 text-green-600 rounded-lg"><i class="fas fa-user-check"></i></div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $activeUsersCount ?></p>
                    <p class="text-xs text-gray-500">Active</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 text-yellow-600 rounded-lg"><i class="fas fa-user-clock"></i></div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $newThisMonth ?></p>
                    <p class="text-xs text-gray-500">New This Month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <input type="hidden" name="page" value="users">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, email, phone, or student ID..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <select name="role" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Roles</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= htmlspecialchars($role['role_name']) ?>" <?= $roleFilter === $role['role_name'] ? 'selected' : '' ?>><?= htmlspecialchars($role['role_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= $statusFilter === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
            <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-800 flex items-center gap-2">
                <i class="fas fa-filter"></i> Filter
            </button>
            <?php if ($search || $roleFilter || $statusFilter): ?>
                <a href="?page=users" class="text-gray-600 hover:text-gray-800 px-3 py-2"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Enrollments</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                        <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? '', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800"><?= htmlspecialchars($user['full_name'] ?? 'Unknown') ?></p>
                                        <p class="text-xs text-gray-500">@<?= htmlspecialchars($user['username'] ?? '') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($user['student_number'])): ?>
                                    <span class="px-2 py-1 text-xs font-mono bg-indigo-50 text-indigo-700 rounded border border-indigo-200"><?= htmlspecialchars($user['student_number']) ?></span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($user['email']) ?></p>
                                <?php if ($user['phone']): ?>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($user['phone']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $roleName = $user['role_name'] ?? 'No Role';
                                $roleClass = match($roleName) {
                                    'Super Admin' => 'bg-red-100 text-red-700 border-red-200',
                                    'Admin' => 'bg-purple-100 text-purple-700 border-purple-200',
                                    'Instructor' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'Student' => 'bg-green-100 text-green-700 border-green-200',
                                    default => 'bg-gray-100 text-gray-700 border-gray-200'
                                };
                                ?>
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full border <?= $roleClass ?>"><?= htmlspecialchars($roleName) ?></span>
                            </td>
                            <td class="px-6 py-4"><span class="text-sm text-gray-600"><?= $user['enrollment_count'] ?></span></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full <?= $user['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $user['status'] === 'active' ? 'bg-green-500' : 'bg-red-500' ?>"></span>
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="openEditModal(<?= $user['id'] ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit"><i class="fas fa-edit"></i></button>
                                    <form method="POST" class="inline"><?= csrfField() ?><input type="hidden" name="action" value="update_status"><input type="hidden" name="user_id" value="<?= $user['id'] ?>"><input type="hidden" name="status" value="<?= $user['status'] === 'active' ? 'suspended' : 'active' ?>"><button type="submit" class="p-2 <?= $user['status'] === 'active' ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' ?> rounded-lg transition-colors" title="<?= $user['status'] === 'active' ? 'Suspend' : 'Activate' ?>"><i class="fas <?= $user['status'] === 'active' ? 'fa-user-slash' : 'fa-user-check' ?>"></i></button></form>
                                    <form method="POST" class="inline"><?= csrfField() ?><input type="hidden" name="action" value="reset_password"><input type="hidden" name="user_id" value="<?= $user['id'] ?>"><button type="submit" onclick="return confirm('Reset password? A new random password will be generated.')" class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Reset Password"><i class="fas fa-key"></i></button></form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="<?= $user['id'] ?>"><button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete"><i class="fas fa-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="8" class="px-6 py-12 text-center"><div class="text-gray-400"><i class="fas fa-users text-4xl mb-3"></i><p class="text-lg font-medium">No users found</p><p class="text-sm">Try adjusting your search or filter criteria</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 border-t bg-gray-50 flex items-center justify-between">
                <p class="text-sm text-gray-600">Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $totalUsers) ?> of <?= $totalUsers ?> users</p>
                <div class="flex gap-1">
                    <?php if ($page_num > 1): ?><a href="?page=users&p=<?= $page_num - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-100"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
                    <?php for ($i = max(1, $page_num - 2); $i <= min($totalPages, $page_num + 2); $i++): ?>
                        <a href="?page=users&p=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="px-3 py-1 border rounded-lg <?= $i === $page_num ? 'bg-blue-600 text-white border-blue-600' : 'hover:bg-gray-100' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($page_num < $totalPages): ?><a href="?page=users&p=<?= $page_num + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-100"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b sticky top-0 bg-white"><div class="flex justify-between items-center"><h3 id="modalTitle" class="text-xl font-semibold text-gray-800">Add New User</h3><button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button></div></div>
        <form id="userForm" method="POST" class="p-6">
            <?= csrfField() ?>
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="user_id" id="userId" value="">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label><input type="text" name="first_name" id="firstName" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label><input type="text" name="last_name" id="lastName" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label><input type="email" name="email" id="email" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label><input type="text" name="phone" id="phone" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select name="role_id" id="roleId" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <?php foreach ($roles as $role): ?><option value="<?= $role['id'] ?>" <?= $role['role_name'] === 'Student' ? 'selected' : '' ?>><?= htmlspecialchars($role['role_name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Address</label><input type="text" name="address" id="address" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Bio</label><textarea name="bio" id="bio" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Password</label><input type="password" name="password" id="password" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"><p id="passwordHint" class="text-xs text-gray-500 mt-1">Leave blank to auto-generate a secure password</p></div>
            </div>
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50 font-medium">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"><span id="submitBtn">Add User</span></button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-lg shadow-2xl">
        <div class="p-6 border-b"><div class="flex justify-between items-center"><h3 class="text-xl font-semibold text-gray-800">Import Users from CSV</h3><button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button></div></div>
        <form method="POST" enctype="multipart/form-data" class="p-6">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="bulk_import">
            <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                    <p class="font-medium text-gray-800 mb-2">CSV Format Required:</p>
                    <code class="block bg-white p-2 rounded border text-xs">First Name, Last Name, Email, Phone, Role</code>
                    <p class="mt-2">Role can be: Student, Instructor, Admin</p>
                    <p>Student IDs will be auto-generated for Student roles.</p>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Select CSV File</label><input type="file" name="csv_file" accept=".csv" required class="w-full px-3 py-2 border rounded-lg"></div>
            </div>
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"><i class="fas fa-upload mr-2"></i>Import</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('formAction').value = 'add';
    document.getElementById('userId').value = '';
    document.getElementById('submitBtn').textContent = 'Add User';
    document.getElementById('passwordHint').textContent = 'Leave blank to auto-generate a secure password';
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('userForm').reset();
    document.getElementById('userModal').classList.remove('hidden');
}

function openEditModal(userId) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('userId').value = userId;
    document.getElementById('submitBtn').textContent = 'Save Changes';
    document.getElementById('passwordHint').textContent = 'Leave blank to keep current password';
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;

    fetch('?page=users&ajax=get_user&id=' + userId)
        .then(r => r.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            document.getElementById('firstName').value = data.first_name || '';
            document.getElementById('lastName').value = data.last_name || '';
            document.getElementById('email').value = data.email || '';
            document.getElementById('phone').value = data.phone || '';
            document.getElementById('roleId').value = data.role_id || 4;
            document.getElementById('address').value = data.address || '';
            document.getElementById('bio').value = data.bio || '';
            document.getElementById('userModal').classList.remove('hidden');
        })
        .catch(e => { console.error(e); alert('Failed to load user data'); });
}

function closeModal() { document.getElementById('userModal').classList.add('hidden'); }
document.getElementById('userModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });
document.addEventListener('click', function(e) {
    if (!document.getElementById('bulkDropdown').contains(e.target)) document.getElementById('bulkMenu').classList.add('hidden');
});
</script>
