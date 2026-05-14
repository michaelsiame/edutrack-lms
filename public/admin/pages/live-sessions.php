<?php
/**
 * Live Sessions / Virtual Classes Page
 */
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$statusFilter = $_GET['status'] ?? '';
$params = [];
$where = "WHERE 1=1";

if ($statusFilter) {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}

$total = $db->fetchColumn("SELECT COUNT(*) FROM live_sessions $where", $params);
$totalPages = ceil($total / $per_page);

$sessions = $db->fetchAll("SELECT ls.*, l.title as lesson_title, c.title as course_title,
    CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
    (SELECT COUNT(*) FROM live_session_attendance WHERE live_session_id = ls.id) as attendee_count
    FROM live_sessions ls
    JOIN lessons l ON ls.lesson_id = l.id
    JOIN courses c ON l.course_id = c.id
    JOIN users u ON ls.instructor_id = u.id
    $where
    ORDER BY ls.scheduled_start_time DESC LIMIT $per_page OFFSET $offset", $params);
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Live Sessions</h1>
            <p class="text-gray-500 mt-1">Manage virtual classes and webinars</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Sessions</p>
            <p class="text-2xl font-bold text-gray-900"><?= $total ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Scheduled</p>
            <p class="text-2xl font-bold text-green-600"><?= $db->fetchColumn("SELECT COUNT(*) FROM live_sessions WHERE status = 'scheduled'") ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Ended</p>
            <p class="text-2xl font-bold text-blue-600"><?= $db->fetchColumn("SELECT COUNT(*) FROM live_sessions WHERE status = 'ended'") ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <input type="hidden" name="page" value="live-sessions">
            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">All Status</option>
                <option value="scheduled" <?= $statusFilter === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                <option value="live" <?= $statusFilter === 'live' ? 'selected' : '' ?>>Live Now</option>
                <option value="ended" <?= $statusFilter === 'ended' ? 'selected' : '' ?>>Ended</option>
                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lesson</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instructor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attendees</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($sessions as $s): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($s['lesson_title']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($s['course_title']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($s['instructor_name']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $s['scheduled_start_time'] ? date('M j, Y H:i', strtotime($s['scheduled_start_time'])) : '-' ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= $s['attendee_count'] ?> / <?= $s['max_participants'] ?? '∞' ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= 
                            $s['status'] === 'live' ? 'bg-red-100 text-red-800' : 
                            ($s['status'] === 'ended' ? 'bg-blue-100 text-blue-800' : 
                            ($s['status'] === 'scheduled' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) ?>">
                            <?= ucfirst($s['status'] ?? 'Scheduled') ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($sessions)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-video text-4xl mb-3 text-gray-300"></i><p>No live sessions found</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center mt-6 gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=live-sessions&p=<?= $i ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page_num ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
