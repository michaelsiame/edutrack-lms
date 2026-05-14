<?php
/**
 * Enrollments Management Page
 */

require_once __DIR__ . '/../../../src/includes/security.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        validateCsrf();
    } catch (Exception $e) {
        header('Location: ?page=enrollments&msg=csrf_error');
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_status' && isset($_POST['enrollment_id'], $_POST['status'])) {
        $enrollmentId = (int)$_POST['enrollment_id'];
        $statusMap = [
            'active' => 'In Progress',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Dropped',
            'dropped' => 'Dropped'
        ];
        $statusKey = strtolower(trim($_POST['status'] ?? ''));
        $status = $statusMap[$statusKey] ?? 'In Progress';

        $db->update('enrollments', ['enrollment_status' => $status], 'id = ?', [$enrollmentId]);
        header('Location: ?page=enrollments&msg=status_updated');
        exit;
    }

    if ($action === 'add' && isset($_POST['user_id'], $_POST['course_id'])) {
        $userId = (int)$_POST['user_id'];
        $courseId = (int)$_POST['course_id'];

        $exists = $db->exists('enrollments', 'user_id = ? AND course_id = ?', [$userId, $courseId]);
        if (!$exists) {
            $enrollmentId = Enrollment::create([
                'user_id' => $userId,
                'course_id' => $courseId
            ]);
            header('Location: ?page=enrollments&msg=' . ($enrollmentId ? 'added' : 'error'));
        } else {
            header('Location: ?page=enrollments&msg=exists');
        }
        exit;
    }
}

// Pagination
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$statusFilter = $_GET['status'] ?? '';
$courseFilter = $_GET['course'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as full_name, u.email, c.title as course_title, e.enrollment_status as status
    FROM enrollments e JOIN users u ON e.user_id = u.id JOIN courses c ON e.course_id = c.id WHERE 1=1";
$countSql = "SELECT COUNT(*) FROM enrollments e JOIN users u ON e.user_id = u.id JOIN courses c ON e.course_id = c.id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $countSql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($statusFilter) {
    $sql .= " AND e.enrollment_status = ?";
    $countSql .= " AND e.enrollment_status = ?";
    $params[] = $statusFilter;
}
if ($courseFilter) {
    $sql .= " AND e.course_id = ?";
    $countSql .= " AND e.course_id = ?";
    $params[] = (int)$courseFilter;
}

$total = $db->fetchColumn($countSql, $params) ?: 0;
$totalPages = ceil($total / $per_page);
$sql .= " ORDER BY e.enrolled_at DESC LIMIT $per_page OFFSET $offset";
$enrollments = $db->fetchAll($sql, $params);

$students = $db->fetchAll("SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as full_name, u.email FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE r.role_name = 'Student' ORDER BY u.first_name, u.last_name");
$courses = $db->fetchAll("SELECT id, title FROM courses ORDER BY title");

$msg = $_GET['msg'] ?? '';
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Enrollments</h1>
            <p class="text-gray-500 mt-1">Manage student course enrollments</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium shadow-sm">
            <i class="fas fa-plus mr-2"></i>Enroll Student
        </button>
    </div>

    <?php if ($msg): ?>
        <div class="mb-6 <?= $msg === 'exists' ? 'bg-yellow-50 border-yellow-200 text-yellow-700' : ($msg === 'csrf_error' || $msg === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700') ?> border px-4 py-3 rounded-xl">
            <i class="fas <?= $msg === 'csrf_error' || $msg === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?> mr-2"></i>
            <?= match($msg) {
                'added' => 'Student enrolled successfully!',
                'exists' => 'Student is already enrolled in this course.',
                'error' => 'Failed to enroll student. Please try again.',
                'csrf_error' => 'Security check failed. Please refresh and try again.',
                default => 'Status updated!'
            } ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <input type="hidden" name="page" value="enrollments">
            <div class="flex-1 min-w-[200px] relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search student..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>
            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Status</option>
                <option value="In Progress" <?= $statusFilter === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="Completed" <?= $statusFilter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option value="Dropped" <?= $statusFilter === 'Dropped' ? 'selected' : '' ?>>Dropped</option>
            </select>
            <select name="course" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Courses</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $courseFilter == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Filter</button>
            <?php if ($search || $statusFilter || $courseFilter): ?>
                <a href="?page=enrollments" class="text-gray-500 hover:text-gray-700 text-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrolled</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($enrollments as $e): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold">
                                <?= strtoupper(substr($e['first_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($e['full_name'] ?? 'Unknown') ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($e['email']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($e['course_title']) ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-primary-500 h-2 rounded-full" style="width: <?= min(100, $e['progress'] ?? 0) ?>"></div>
                            </div>
                            <span class="text-xs text-gray-600"><?= $e['progress'] ?? 0 ?>%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= 
                            ($e['status'] ?? '') === 'Completed' ? 'bg-green-100 text-green-800' : 
                            (($e['status'] ?? '') === 'Dropped' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') ?>">
                            <?= htmlspecialchars($e['status'] ?? 'In Progress') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= date('M j, Y', strtotime($e['enrolled_at'])) ?></td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <form method="POST" class="inline">
                                <?= csrfField(); ?>
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="enrollment_id" value="<?= $e['id'] ?>">
                                <select name="status" onchange="this.form.submit()" class="text-xs px-2 py-1 border rounded-lg focus:ring-2 focus:ring-primary-500">
                                    <option value="" disabled <?= !in_array($e['status'], ['In Progress', 'Completed', 'Dropped']) ? 'selected' : '' ?>>Change</option>
                                    <option value="in_progress" <?= ($e['status'] ?? '') === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= ($e['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="dropped" <?= ($e['status'] ?? '') === 'Dropped' ? 'selected' : '' ?>>Dropped</option>
                                </select>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($enrollments)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-user-check text-4xl mb-3 text-gray-300"></i><p>No enrollments found</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=enrollments&p=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?><?= $courseFilter ? '&course=' . urlencode($courseFilter) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Add Enrollment Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-md shadow-2xl">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Enroll Student</h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
        </div>
        <form method="POST" class="p-6">
            <?= csrfField(); ?>
            <input type="hidden" name="action" value="add">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student <span class="text-red-500">*</span></label>
                    <select name="user_id" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">Select Student</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?> (<?= htmlspecialchars($s['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course <span class="text-red-500">*</span></label>
                    <select name="course_id" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg hover:bg-gray-50 font-medium">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">Enroll</button>
            </div>
        </form>
    </div>
</div>
