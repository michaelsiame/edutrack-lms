<?php
/**
 * Certificates Management Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$search = $_GET['search'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($search) {
    $where .= " AND (c.certificate_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM certificates c JOIN users u ON c.user_id = u.id $where", $params);
$totalPages = ceil($total / $per_page);

$certificates = $db->fetchAll("SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as student_name, u.email, co.title as course_title
    FROM certificates c
    JOIN users u ON c.user_id = u.id
    JOIN courses co ON c.course_id = co.id
    $where
    ORDER BY c.issued_at DESC LIMIT $per_page OFFSET $offset", $params);
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Certificates</h1>
            <p class="text-gray-500 mt-1">Issue and manage student certificates</p>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="certificates">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search certificates..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Search</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Certificate #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verified</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($certificates as $cert): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-mono text-primary-600"><?= htmlspecialchars($cert['certificate_number']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($cert['student_name']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($cert['course_title']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= $cert['issued_at'] ? date('M j, Y', strtotime($cert['issued_at'])) : '-' ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= !empty($cert['is_verified']) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= !empty($cert['is_verified']) ? 'Verified' : 'Unverified' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($certificates)): ?>
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-certificate text-4xl mb-3 text-gray-300"></i><p>No certificates issued yet</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=certificates&p=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
