<?php
/**
 * Admin Enrollments Report
 * Detailed enrollment analytics and filtering
 */

require_once '../../../src/middleware/admin-only.php';

// Date range filters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$courseId = $_GET['course_id'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Build query
$sql = "SELECT e.*, 
        c.title as course_title, c.price,
        u.first_name, u.last_name, u.email,
        p.status as payment_status, p.amount as payment_amount
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        JOIN users u ON e.user_id = u.id
        LEFT JOIN payments p ON e.id = p.enrollment_id
        WHERE e.enrolled_at BETWEEN ? AND ?";

$params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];

if ($courseId) {
    $sql .= " AND e.course_id = ?";
    $params[] = $courseId;
}

if ($status) {
    $sql .= " AND e.status = ?";
    $params[] = $status;
}

// Get total count
$countSql = "SELECT COUNT(*) FROM ($sql) as count_table";
$totalEnrollments = (int) $db->fetchColumn($countSql, $params);
$totalPages = ceil($totalEnrollments / $perPage);

// Get enrollments
$sql .= " ORDER BY e.enrolled_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$enrollments = $db->fetchAll($sql, $params);

// Get summary statistics
$summaryParams = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
$summary = [
    'total' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrolled_at BETWEEN ? AND ?", $summaryParams),
    'active' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrolled_at BETWEEN ? AND ? AND status = 'active'", $summaryParams),
    'completed' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrolled_at BETWEEN ? AND ? AND status = 'completed'", $summaryParams),
    'dropped' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE enrolled_at BETWEEN ? AND ? AND status = 'dropped'", $summaryParams),
];

// Get courses for filter
$courses = $db->fetchAll("SELECT id, title FROM courses WHERE status = 'published' ORDER BY title");

$page_title = 'Enrollments Report';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container-fluid px-4 py-6">

    <!-- Back Button -->
    <div class="mb-6">
        <a href="<?= url('admin/reports/index.php') ?>" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Reports
        </a>
    </div>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Enrollments Report</h1>
        <p class="text-gray-600 mt-1">Detailed enrollment data and analytics</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Total Enrollments</p>
            <p class="text-3xl font-bold text-gray-900 mt-2"><?= number_format($summary['total']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Active</p>
            <p class="text-3xl font-bold text-blue-600 mt-2"><?= number_format($summary['active']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Completed</p>
            <p class="text-3xl font-bold text-green-600 mt-2"><?= number_format($summary['completed']) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Dropped</p>
            <p class="text-3xl font-bold text-red-600 mt-2"><?= number_format($summary['dropped']) ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>"
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>"
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
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
                        <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="dropped" <?= $status == 'dropped' ? 'selected' : '' ?>>Dropped</option>
                        <option value="suspended" <?= $status == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search mr-2"></i>Apply Filters
                    </button>
                    <a href="<?= url('admin/reports/enrollments.php') ?>" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                    <button type="button" onclick="exportToCSV()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enrollments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200" id="enrollmentsTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrolled Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($enrollments)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        No enrollments found for the selected criteria
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
                            <?php if ($enrollment['status'] == 'active'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Active</span>
                            <?php elseif ($enrollment['status'] == 'completed'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                            <?php elseif ($enrollment['status'] == 'dropped'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Dropped</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800"><?= ucfirst($enrollment['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $enrollment['progress_percentage'] ?>%"></div>
                                </div>
                                <span class="text-sm text-gray-600"><?= round($enrollment['progress_percentage']) ?>%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($enrollment['payment_status'] == 'completed'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                            <?php elseif ($enrollment['payment_status'] == 'pending'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            <?php elseif ($enrollment['payment_status'] == 'failed'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php if ($enrollment['payment_amount']): ?>
                                ZMW <?= number_format($enrollment['payment_amount'], 2) ?>
                            <?php elseif ($enrollment['price'] > 0): ?>
                                ZMW <?= number_format($enrollment['price'], 2) ?>
                            <?php else: ?>
                                Free
                            <?php endif; ?>
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
            <a href="?page=<?= $page - 1 ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&course_id=<?= urlencode($courseId) ?>&status=<?= urlencode($status) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                Previous
            </a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&course_id=<?= urlencode($courseId) ?>&status=<?= urlencode($status) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm <?= $i == $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-50' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&course_id=<?= urlencode($courseId) ?>&status=<?= urlencode($status) ?>"
               class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                Next
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function exportToCSV() {
    const table = document.getElementById('enrollmentsTable');
    let csv = [];
    
    // Get headers
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
    csv.push(headers.join(','));
    
    // Get rows
    table.querySelectorAll('tbody tr').forEach(row => {
        if (row.cells.length > 1) {
            const rowData = Array.from(row.cells).map(cell => {
                let text = cell.textContent.trim().replace(/\n/g, ' ').replace(/,/g, ';');
                return `"${text}"`;
            });
            csv.push(rowData.join(','));
        }
    });
    
    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'enrollments_report_<?= date('Y-m-d') ?>.csv';
    a.click();
}
</script>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
