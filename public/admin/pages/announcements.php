<?php
/**
 * Announcements Management Page
 */

require_once __DIR__ . '/../../../src/includes/security.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        validateCsrf();
    } catch (Exception $e) {
        header('Location: ?page=announcements&msg=csrf_error');
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $priorityInput = strtolower(trim($_POST['priority'] ?? 'normal'));
        $priorityMap = ['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'];
        $priority = $priorityMap[$priorityInput] ?? 'Normal';
        $courseId = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;

        if ($title && $content) {
            $db->insert('announcements', [
                'title' => $title,
                'content' => $content,
                'priority' => $priority,
                'course_id' => $courseId,
                'is_published' => 1,
                'posted_by' => (int)($_SESSION['user_id'] ?? 1),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            header('Location: ?page=announcements&msg=added');
            exit;
        }
    }

    if ($action === 'toggle' && isset($_POST['announcement_id'])) {
        $id = (int)$_POST['announcement_id'];
        $announcement = $db->fetchOne("SELECT is_published FROM announcements WHERE announcement_id = ?", [$id]);
        $newStatus = $announcement['is_published'] ? 0 : 1;
        $db->update('announcements', ['is_published' => $newStatus], 'announcement_id = ?', [$id]);
        header('Location: ?page=announcements&msg=updated');
        exit;
    }

    if ($action === 'delete' && isset($_POST['announcement_id'])) {
        $id = (int)$_POST['announcement_id'];
        $db->delete('announcements', 'announcement_id = ?', [$id]);
        header('Location: ?page=announcements&msg=deleted');
        exit;
    }
}

// Fetch data
$announcements = $db->fetchAll("SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as author_name, c.title as target_course
    FROM announcements a
    LEFT JOIN users u ON a.posted_by = u.id
    LEFT JOIN courses c ON a.course_id = c.id
    ORDER BY a.created_at DESC");
$courses = $db->fetchAll("SELECT id, title FROM courses ORDER BY title");
$msg = $_GET['msg'] ?? '';
?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Announcements</h1>
            <p class="text-gray-500 mt-1">Manage system-wide and course-targeted announcements</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium shadow-sm">
            <i class="fas fa-plus mr-2"></i>New Announcement
        </button>
    </div>

    <?php if ($msg): ?>
        <div class="mb-6 <?= $msg === 'csrf_error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' ?> border px-4 py-3 rounded-xl">
            <i class="fas <?= $msg === 'csrf_error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?> mr-2"></i>
            <?= match($msg) {
                'added' => 'Announcement created successfully!',
                'deleted' => 'Announcement deleted.',
                'updated' => 'Announcement updated.',
                'csrf_error' => 'Security check failed. Please refresh and try again.',
                default => 'Action completed!'
            } ?>
        </div>
    <?php endif; ?>

    <!-- Announcements List -->
    <div class="space-y-4">
        <?php foreach ($announcements as $ann): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 <?= !$ann['is_published'] ? 'opacity-60' : '' ?>">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h3 class="font-semibold text-gray-900 text-lg"><?= htmlspecialchars($ann['title']) ?></h3>
                        <?php $priorityNormalized = strtolower((string)$ann['priority']); ?>
                        <span class="px-2 py-1 text-xs rounded-full font-medium <?= $priorityNormalized === 'urgent' || $priorityNormalized === 'high' ? 'bg-red-100 text-red-700' : ($priorityNormalized === 'low' ? 'bg-gray-100 text-gray-700' : 'bg-blue-100 text-blue-700') ?>">
                            <?= htmlspecialchars($ann['priority']) ?>
                        </span>
                        <?php if (!$ann['is_published']): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-200 text-gray-600 font-medium">Inactive</span>
                        <?php endif; ?>
                        <?php if ($ann['target_course']): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700 font-medium" title="Course-targeted">
                                <i class="fas fa-book mr-1"></i><?= htmlspecialchars($ann['target_course']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <span class="text-sm text-gray-500"><?= date('M j, Y', strtotime($ann['created_at'])) ?></span>
                </div>
                <p class="text-gray-600 mb-4 whitespace-pre-wrap"><?= nl2br(htmlspecialchars($ann['content'])) ?></p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">By <?= htmlspecialchars($ann['author_name'] ?? 'System') ?></span>
                    <div class="flex gap-2">
                        <form method="POST" class="inline">
                            <?= csrfField(); ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="announcement_id" value="<?= $ann['announcement_id'] ?>">
                            <button type="submit" class="text-sm px-3 py-1.5 rounded-lg <?= $ann['is_published'] ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' ?> font-medium">
                                <?= $ann['is_published'] ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                        <form method="POST" class="inline" onsubmit="return confirm('Delete this announcement?')">
                            <?= csrfField(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="announcement_id" value="<?= $ann['announcement_id'] ?>">
                            <button type="submit" class="text-sm px-3 py-1.5 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 font-medium">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($announcements)): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
                <i class="fas fa-bullhorn text-4xl mb-4 text-gray-300"></i>
                <p class="text-lg font-medium">No announcements yet</p>
                <p class="text-sm mt-1">Create your first announcement to notify users</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Announcement Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b sticky top-0 bg-white rounded-t-xl">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">New Announcement</h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
        </div>
        <form method="POST" class="p-6">
            <?= csrfField(); ?>
            <input type="hidden" name="action" value="add">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="5" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Course</label>
                        <select name="course_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg hover:bg-gray-50 font-medium">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">Create Announcement</button>
            </div>
        </form>
    </div>
</div>
