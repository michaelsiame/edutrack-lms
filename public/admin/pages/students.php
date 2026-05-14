<?php
/**
 * Students Management Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$params = [];
$where = "WHERE r.role_name = 'Student'";

if ($search) {
    $where .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($statusFilter) {
    $where .= " AND u.status = ?";
    $params[] = $statusFilter;
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id $where", $params);
$totalPages = ceil($total / $per_page);

$students = $db->fetchAll("SELECT u.*, s.enrollment_date,
    (SELECT COUNT(*) FROM enrollments WHERE user_id = u.id) as course_count
    FROM users u 
    JOIN user_roles ur ON u.id = ur.user_id 
    JOIN roles r ON ur.role_id = r.id 
    LEFT JOIN students s ON u.id = s.user_id
    $where 
    ORDER BY u.created_at DESC LIMIT $per_page OFFSET $offset", $params);
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Students</h1>
            <p class="text-gray-500 mt-1">Manage enrolled students</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="page" value="students">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search students..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>
            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Status</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Courses</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Courses</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($students as $s): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold">
                                <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $s['course_count'] ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($s['email']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $s['course_count'] ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $s['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= ucfirst($s['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= date('M j, Y', strtotime($s['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($students)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-user-graduate text-4xl mb-3 text-gray-300"></i><p>No students found</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=students&p=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
