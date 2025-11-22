<?php
/**
 * Admin Certificates Management
 * View and manage all issued certificates
 */

require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Certificate.php';
require_once '../../../src/classes/User.php';
require_once '../../../src/classes/Course.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    $action = $_POST['action'] ?? null;
    $certId = $_POST['cert_id'] ?? null;
    
    if ($certId) {
        $cert = Certificate::find($certId);
        
        if ($cert) {
            switch ($action) {
                case 'revoke':
                    $reason = $_POST['reason'] ?? 'Revoked by admin';
                    if ($cert->revoke($reason)) {
                        flash('message', 'Certificate revoked successfully', 'success');
                    }
                    break;
                    
                case 'regenerate':
                    if ($cert->generatePDF()) {
                        flash('message', 'Certificate regenerated successfully', 'success');
                    }
                    break;
            }
        }
    }
    
    redirect(url('admin/certificates/index.php'));
}

// Filters
$search = $_GET['search'] ?? '';
$courseId = $_GET['course'] ?? '';
$revoked = $_GET['revoked'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$sql = "SELECT c.*,
        u.first_name, u.last_name, u.email,
        co.title as course_title
        FROM certificates c
        JOIN enrollments e ON c.enrollment_id = e.id
        JOIN users u ON e.user_id = u.id
        JOIN courses co ON e.course_id = co.id
        WHERE 1=1";

$params = [];

if ($search) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR c.certificate_number LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($courseId) {
    $sql .= " AND e.course_id = ?";
    $params[] = $courseId;
}

if ($revoked !== '') {
    $sql .= " AND c.is_verified = ?";
    $params[] = $revoked ? 0 : 1;  // revoked=1 means is_verified=0
}

// Get total count
$countSql = "SELECT COUNT(*) FROM ($sql) as count_table";
$totalCerts = $db->fetchColumn($countSql, $params);
$totalPages = ceil($totalCerts / $perPage);

// Get certificates
$sql .= " ORDER BY c.issued_date DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$certificates = $db->fetchAll($sql, $params);

// Get courses for filter
$courses = Course::all(['order' => 'title ASC']);

// Stats
$stats = [
    'total' => $db->fetchColumn("SELECT COUNT(*) FROM certificates"),
    'issued_today' => $db->fetchColumn("SELECT COUNT(*) FROM certificates WHERE issued_date = CURDATE()"),
    'issued_month' => $db->fetchColumn("SELECT COUNT(*) FROM certificates WHERE MONTH(issued_date) = MONTH(CURDATE()) AND YEAR(issued_date) = YEAR(CURDATE())"),
    'revoked' => $db->fetchColumn("SELECT COUNT(*) FROM certificates WHERE is_verified = 0")
];

$page_title = 'Manage Certificates';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-certificate text-primary-600 mr-2"></i>
                Certificates
            </h1>
            <p class="text-gray-600 mt-1"><?= number_format($totalCerts) ?> total certificates</p>
        </div>
        <a href="<?= url('admin/certificates/issue.php') ?>" class="btn-primary px-6 py-2 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Issue Certificate
        </a>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Issued</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total']) ?></p>
                </div>
                <i class="fas fa-certificate text-3xl text-gray-400"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Today</p>
                    <p class="text-2xl font-bold text-blue-600"><?= number_format($stats['issued_today']) ?></p>
                </div>
                <i class="fas fa-calendar-day text-3xl text-blue-400"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">This Month</p>
                    <p class="text-2xl font-bold text-green-600"><?= number_format($stats['issued_month']) ?></p>
                </div>
                <i class="fas fa-calendar text-3xl text-green-400"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Revoked</p>
                    <p class="text-2xl font-bold text-red-600"><?= number_format($stats['revoked']) ?></p>
                </div>
                <i class="fas fa-ban text-3xl text-red-400"></i>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <form method="GET" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                
                <div>
                    <input type="text" name="search" value="<?= sanitize($search) ?>"
                           placeholder="Search certificates..."
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                
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
                
                <div>
                    <select name="revoked" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Status</option>
                        <option value="0" <?= $revoked === '0' ? 'selected' : '' ?>>Active</option>
                        <option value="1" <?= $revoked === '1' ? 'selected' : '' ?>>Revoked</option>
                    </select>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <a href="<?= url('admin/certificates/index.php') ?>" class="px-6 py-2 border rounded-lg hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Certificates Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($certificates)): ?>
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-certificate text-5xl mb-4"></i>
                <p>No certificates found</p>
            </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cert Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issued</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($certificates as $cert): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900">
                                    <?= sanitize($cert['first_name'] . ' ' . $cert['last_name']) ?>
                                </p>
                                <p class="text-sm text-gray-600"><?= sanitize($cert['email']) ?></p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900"><?= sanitize($cert['course_title']) ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-mono text-sm text-gray-900"><?= sanitize($cert['certificate_number']) ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                <?= number_format($cert['final_grade'], 1) ?>%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?= timeAgo($cert['issued_date']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if (!$cert['is_verified']): ?>
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                    Revoked
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                    Active
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                
                                <a href="<?= url('verify-certificate.php?code=' . $cert['verification_code']) ?>" 
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($cert['is_verified']): ?>
                                <form method="POST" class="inline" onsubmit="return confirm('Revoke this certificate?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="revoke">
                                    <input type="hidden" name="cert_id" value="<?= $cert['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Revoke">
                                        <i class="fas fa-ban"></i>
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
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Showing <?= number_format($offset + 1) ?> to <?= number_format(min($offset + $perPage, $totalCerts)) ?> 
                    of <?= number_format($totalCerts) ?> certificates
                </p>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&course=<?= $courseId ?>&revoked=<?= $revoked ?>" 
                           class="px-4 py-2 border rounded hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&course=<?= $courseId ?>&revoked=<?= $revoked ?>"
                           class="px-4 py-2 border rounded <?= $i == $page ? 'bg-primary-600 text-white' : 'hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&course=<?= $courseId ?>&revoked=<?= $revoked ?>" 
                           class="px-4 py-2 border rounded hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>