<?php
/**
 * Enrollments Management Page
 */

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status' && isset($_POST['enrollment_id'], $_POST['status'])) {
        $enrollmentId = (int)$_POST['enrollment_id'];
        $status = in_array($_POST['status'], ['active', 'completed', 'cancelled']) ? $_POST['status'] : 'active';
        $db->update('enrollments', ['status' => $status], 'id = ?', [$enrollmentId]);
        header('Location: ?page=enrollments&msg=status_updated');
        exit;
    }

    if ($action === 'add' && isset($_POST['user_id'], $_POST['course_id'])) {
        $userId = (int)$_POST['user_id'];
        $courseId = (int)$_POST['course_id'];

        // Check if already enrolled
        $exists = $db->exists('enrollments', 'user_id = ? AND course_id = ?', [$userId, $courseId]);
        if (!$exists) {
            $db->insert('enrollments', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => 'active',
                'enrolled_at' => date('Y-m-d H:i:s')
            ]);
            header('Location: ?page=enrollments&msg=added');
        } else {
            header('Location: ?page=enrollments&msg=exists');
        }
        exit;
    }
}

// Fetch enrollments
$enrollments = $db->fetchAll("
    SELECT e.*, u.full_name, u.email, c.title as course_title
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
");

$students = $db->fetchAll("SELECT id, full_name, email FROM users WHERE role = 'Student' ORDER BY full_name");
$courses = $db->fetchAll("SELECT id, title FROM courses WHERE status = 'published' ORDER BY title");

$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Enrollments</h2>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Enroll Student
        </button>
    </div>

    <?php if ($msg): ?>
        <div class="<?= $msg === 'exists' ? 'bg-yellow-100 border-yellow-400 text-yellow-700' : 'bg-green-100 border-green-400 text-green-700' ?> border px-4 py-3 rounded">
            <?= $msg === 'added' ? 'Student enrolled successfully!' : ($msg === 'exists' ? 'Student is already enrolled in this course.' : 'Status updated!') ?>
        </div>
    <?php endif; ?>

    <!-- Enrollments Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrolled</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($enrollments as $enrollment): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($enrollment['full_name']) ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($enrollment['email']) ?></p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($enrollment['course_title']) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full <?= $enrollment['status'] === 'active' ? 'bg-green-100 text-green-700' : ($enrollment['status'] === 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700') ?>">
                                <?= ucfirst($enrollment['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-sm"><?= date('M j, Y', strtotime($enrollment['enrolled_at'])) ?></td>
                        <td class="px-6 py-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $enrollment['progress'] ?? 0 ?>%"></div>
                            </div>
                            <span class="text-xs text-gray-500"><?= $enrollment['progress'] ?? 0 ?>%</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <?php if ($enrollment['status'] === 'active'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200">Mark Complete</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $enrollment['status'] === 'cancelled' ? 'active' : 'cancelled' ?>">
                                    <button type="submit" class="text-xs px-2 py-1 rounded <?= $enrollment['status'] === 'cancelled' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                        <?= $enrollment['status'] === 'cancelled' ? 'Reactivate' : 'Cancel' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($enrollments)): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No enrollments found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Enroll Student Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Enroll Student</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student</label>
                    <select name="user_id" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select Student</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['full_name']) ?> (<?= htmlspecialchars($student['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                    <select name="course_id" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Enroll</button>
            </div>
        </form>
    </div>
</div>
