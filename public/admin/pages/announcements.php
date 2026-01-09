<?php
/**
 * Announcements Management Page
 */

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $priority = $_POST['priority'] ?? 'normal';

        if ($title && $content) {
            $db->insert('announcements', [
                'title' => $title,
                'content' => $content,
                'priority' => $priority,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            header('Location: ?page=announcements&msg=added');
            exit;
        }
    }

    if ($action === 'toggle' && isset($_POST['announcement_id'])) {
        $id = (int)$_POST['announcement_id'];
        $announcement = $db->fetchOne("SELECT is_active FROM announcements WHERE id = ?", [$id]);
        $newStatus = $announcement['is_active'] ? 0 : 1;
        $db->update('announcements', ['is_active' => $newStatus], 'id = ?', [$id]);
        header('Location: ?page=announcements&msg=updated');
        exit;
    }

    if ($action === 'delete' && isset($_POST['announcement_id'])) {
        $id = (int)$_POST['announcement_id'];
        $db->delete('announcements', 'id = ?', [$id]);
        header('Location: ?page=announcements&msg=deleted');
        exit;
    }
}

// Fetch announcements
$announcements = $db->fetchAll("SELECT * FROM announcements ORDER BY created_at DESC");
$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Announcements</h2>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>New Announcement
        </button>
    </div>

    <?php if ($msg): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?= $msg === 'added' ? 'Announcement created!' : ($msg === 'deleted' ? 'Announcement deleted!' : 'Announcement updated!') ?>
        </div>
    <?php endif; ?>

    <!-- Announcements List -->
    <div class="space-y-4">
        <?php foreach ($announcements as $ann): ?>
            <div class="bg-white rounded-lg shadow-sm border p-6 <?= !$ann['is_active'] ? 'opacity-60' : '' ?>">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <h3 class="font-semibold text-gray-800 text-lg"><?= htmlspecialchars($ann['title']) ?></h3>
                        <span class="px-2 py-1 text-xs rounded-full <?= $ann['priority'] === 'high' ? 'bg-red-100 text-red-700' : ($ann['priority'] === 'low' ? 'bg-gray-100 text-gray-700' : 'bg-blue-100 text-blue-700') ?>">
                            <?= ucfirst($ann['priority']) ?>
                        </span>
                        <?php if (!$ann['is_active']): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-200 text-gray-600">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <span class="text-sm text-gray-500"><?= date('M j, Y', strtotime($ann['created_at'])) ?></span>
                </div>
                <p class="text-gray-600 mb-4"><?= nl2br(htmlspecialchars($ann['content'])) ?></p>
                <div class="flex gap-2">
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="announcement_id" value="<?= $ann['id'] ?>">
                        <button type="submit" class="text-sm px-3 py-1 rounded <?= $ann['is_active'] ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' ?>">
                            <?= $ann['is_active'] ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>
                    <form method="POST" class="inline" onsubmit="return confirm('Delete this announcement?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="announcement_id" value="<?= $ann['id'] ?>">
                        <button type="submit" class="text-sm px-3 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($announcements)): ?>
            <div class="bg-white rounded-lg shadow-sm border p-12 text-center text-gray-500">
                <i class="fas fa-bullhorn text-4xl mb-4 text-gray-300"></i>
                <p>No announcements yet. Create your first one!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Announcement Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4">New Announcement</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" rows="4" required class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" class="w-full px-3 py-2 border rounded-lg">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create</button>
            </div>
        </form>
    </div>
</div>
