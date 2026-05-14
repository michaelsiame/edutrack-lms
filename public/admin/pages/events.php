<?php
/**
 * Events Management Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$statusFilter = $_GET['status'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($statusFilter) {
    if ($statusFilter === 'upcoming') {
        $where .= " AND event_date >= CURDATE()";
    } elseif ($statusFilter === 'past') {
        $where .= " AND event_date < CURDATE()";
    } else {
        $where .= " AND status = ?";
        $params[] = $statusFilter;
    }
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM events $where", $params);
$totalPages = ceil($total / $per_page);

$events = $db->fetchAll("SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as author_name
    FROM events e
    LEFT JOIN users u ON e.created_by = u.id
    $where
    ORDER BY e.event_date DESC LIMIT $per_page OFFSET $offset", $params);
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Events</h1>
            <p class="text-gray-500 mt-1">Manage workshops, graduations, and community events</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Events</p>
            <p class="text-2xl font-bold text-gray-900"><?= $total ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Upcoming</p>
            <p class="text-2xl font-bold text-green-600"><?= $db->fetchColumn("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()") ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Featured</p>
            <p class="text-2xl font-bold text-primary-600"><?= $db->fetchColumn("SELECT COUNT(*) FROM events WHERE is_featured = 1") ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="events">
            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Events</option>
                <option value="upcoming" <?= $statusFilter === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                <option value="past" <?= $statusFilter === 'past' ? 'selected' : '' ?>>Past</option>
                <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Featured</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($events as $e): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($e['title']) ?></p>
                        <p class="text-xs text-gray-500 line-clamp-1"><?= htmlspecialchars(substr($e['summary'] ?? '', 0, 60)) ?>...</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $e['event_date'] ? date('M j, Y', strtotime($e['event_date'])) : 'TBA' ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($e['location'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= 
                            $e['status'] === 'published' ? 'bg-green-100 text-green-800' : 
                            ($e['status'] === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') ?>">
                            <?= ucfirst($e['status'] ?? 'Draft') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= ($e['is_featured'] ?? 0) ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= ($e['is_featured'] ?? 0) ? 'Featured' : 'No' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($events)): ?>
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-calendar-alt text-4xl mb-3 text-gray-300"></i><p>No events found</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=events&p=<?= $i ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
