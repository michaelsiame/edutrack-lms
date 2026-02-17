<?php
/**
 * Announcements Management Page - Full CRUD with Edit Support
 */

// Pagination
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

// Fetch announcements
$totalAnnouncements = $db->fetchColumn("SELECT COUNT(*) FROM announcements");
$totalPages = ceil($totalAnnouncements / $per_page);

$announcements = $db->fetchAll("
    SELECT a.*,
           CONCAT(u.first_name, ' ', u.last_name) as author_name,
           c.title as course_title
    FROM announcements a
    LEFT JOIN users u ON a.posted_by = u.id
    LEFT JOIN courses c ON a.course_id = c.id
    ORDER BY a.created_at DESC
    LIMIT $per_page OFFSET $offset
");

// Stats
$publishedCount = $db->fetchColumn("SELECT COUNT(*) FROM announcements WHERE is_published = 1");
$highPriorityCount = $db->fetchColumn("SELECT COUNT(*) FROM announcements WHERE priority IN ('High', 'Urgent')");
$thisMonthCount = $db->fetchColumn("SELECT COUNT(*) FROM announcements WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");

// Get published courses for the dropdown
$courses = $db->fetchAll("SELECT id, title FROM courses WHERE status = 'published' ORDER BY title");

$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Announcements</h2>
            <p class="text-gray-500 text-sm mt-1">Manage system and course announcements</p>
        </div>
        <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 shadow-sm">
            <i class="fas fa-plus"></i> New Announcement
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if ($msg): ?>
        <div class="<?= $msg === 'csrf_error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700' ?> border px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas <?= $msg === 'csrf_error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?>"></i>
            <?= match($msg) {
                'added' => 'Announcement created successfully!',
                'updated' => 'Announcement updated successfully!',
                'deleted' => 'Announcement deleted!',
                'toggle_publish' => 'Announcement status updated!',
                'csrf_error' => 'Security token expired. Please try again.',
                default => 'Action completed!'
            } ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg"><i class="fas fa-bullhorn"></i></div>
                <div><p class="text-2xl font-bold"><?= $totalAnnouncements ?></p><p class="text-xs text-gray-500">Total</p></div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 text-green-600 rounded-lg"><i class="fas fa-eye"></i></div>
                <div><p class="text-2xl font-bold"><?= $publishedCount ?></p><p class="text-xs text-gray-500">Published</p></div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 text-red-600 rounded-lg"><i class="fas fa-exclamation-triangle"></i></div>
                <div><p class="text-2xl font-bold"><?= $highPriorityCount ?></p><p class="text-xs text-gray-500">High Priority</p></div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 text-yellow-600 rounded-lg"><i class="fas fa-calendar"></i></div>
                <div><p class="text-2xl font-bold"><?= $thisMonthCount ?></p><p class="text-xs text-gray-500">This Month</p></div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($announcements as $ann): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <?php
                                $pClass = match($ann['priority'] ?? 'Normal') {
                                    'Urgent' => 'bg-red-100 text-red-700 border-red-200',
                                    'High' => 'bg-orange-100 text-orange-700 border-orange-200',
                                    'Normal' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'Low' => 'bg-gray-100 text-gray-600 border-gray-200',
                                    default => 'bg-gray-100 text-gray-600 border-gray-200'
                                };
                                ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full border <?= $pClass ?>"><?= htmlspecialchars($ann['priority'] ?? 'Normal') ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($ann['title']) ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars(mb_substr(strip_tags($ann['content']), 0, 80)) ?>...</p>
                                <?php if (!empty($ann['course_title'])): ?>
                                    <p class="text-xs text-blue-600 mt-1"><i class="fas fa-book mr-1"></i><?= htmlspecialchars($ann['course_title']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4"><span class="text-sm text-gray-600"><?= htmlspecialchars($ann['announcement_type'] ?? 'General') ?></span></td>
                            <td class="px-6 py-4"><span class="text-sm text-gray-600"><?= htmlspecialchars($ann['author_name'] ?? 'System') ?></span></td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full <?= ($ann['is_published'] ?? 0) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' ?>">
                                    <?= ($ann['is_published'] ?? 0) ? 'Published' : 'Draft' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= date('M j, Y', strtotime($ann['created_at'])) ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="openEditModal(<?= $ann['announcement_id'] ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit"><i class="fas fa-edit"></i></button>
                                    <form method="POST" class="inline"><?= csrfField() ?><input type="hidden" name="action" value="toggle_publish"><input type="hidden" name="announcement_id" value="<?= $ann['announcement_id'] ?>"><button type="submit" class="p-2 <?= ($ann['is_published'] ?? 0) ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' ?> rounded-lg" title="<?= ($ann['is_published'] ?? 0) ? 'Unpublish' : 'Publish' ?>"><i class="fas <?= ($ann['is_published'] ?? 0) ? 'fa-eye-slash' : 'fa-eye' ?>"></i></button></form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this announcement?')"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="announcement_id" value="<?= $ann['announcement_id'] ?>"><button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete"><i class="fas fa-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($announcements)): ?>
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400"><i class="fas fa-bullhorn text-4xl mb-3"></i><p class="text-lg font-medium">No announcements</p></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 border-t bg-gray-50 flex items-center justify-between">
                <p class="text-sm text-gray-600">Page <?= $page_num ?> of <?= $totalPages ?></p>
                <div class="flex gap-1">
                    <?php if ($page_num > 1): ?><a href="?page=announcements&p=<?= $page_num - 1 ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-100"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
                    <?php for ($i = max(1, $page_num - 2); $i <= min($totalPages, $page_num + 2); $i++): ?>
                        <a href="?page=announcements&p=<?= $i ?>" class="px-3 py-1 border rounded-lg <?= $i === $page_num ? 'bg-blue-600 text-white border-blue-600' : 'hover:bg-gray-100' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($page_num < $totalPages): ?><a href="?page=announcements&p=<?= $page_num + 1 ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-100"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="annModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b sticky top-0 bg-white">
            <div class="flex justify-between items-center">
                <h3 id="annModalTitle" class="text-xl font-semibold">New Announcement</h3>
                <button onclick="closeAnnModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
        </div>
        <form id="annForm" method="POST" class="p-6">
            <?= csrfField() ?>
            <input type="hidden" name="action" id="annAction" value="add">
            <input type="hidden" name="announcement_id" id="annId" value="">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="annTitle" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
                    <textarea name="content" id="annContent" rows="5" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" id="annPriority" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="Low">Low</option>
                            <option value="Normal" selected>Normal</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="announcement_type" id="annType" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" onchange="toggleCourseField()">
                            <option value="General">General</option>
                            <option value="System">System</option>
                            <option value="Course">Course</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div id="courseField" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                    <select name="course_id" id="annCourseId" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeAnnModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" id="annSubmitBtn">Create Announcement</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('annModalTitle').textContent = 'New Announcement';
    document.getElementById('annAction').value = 'add';
    document.getElementById('annId').value = '';
    document.getElementById('annSubmitBtn').textContent = 'Create Announcement';
    document.getElementById('annForm').reset();
    document.getElementById('courseField').classList.add('hidden');
    document.getElementById('annModal').classList.remove('hidden');
}

function openEditModal(id) {
    document.getElementById('annModalTitle').textContent = 'Edit Announcement';
    document.getElementById('annAction').value = 'edit';
    document.getElementById('annId').value = id;
    document.getElementById('annSubmitBtn').textContent = 'Save Changes';

    fetch('?page=announcements&ajax=get_announcement&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            document.getElementById('annTitle').value = data.title || '';
            document.getElementById('annContent').value = data.content || '';
            document.getElementById('annPriority').value = data.priority || 'Normal';
            document.getElementById('annType').value = data.announcement_type || 'General';
            document.getElementById('annCourseId').value = data.course_id || '';
            toggleCourseField();
            document.getElementById('annModal').classList.remove('hidden');
        })
        .catch(e => { console.error(e); alert('Failed to load announcement'); });
}

function closeAnnModal() { document.getElementById('annModal').classList.add('hidden'); }
function toggleCourseField() {
    var type = document.getElementById('annType').value;
    document.getElementById('courseField').classList.toggle('hidden', type !== 'Course');
}
document.getElementById('annModal').addEventListener('click', function(e) { if (e.target === this) closeAnnModal(); });
</script>
