<?php
/**
 * Courses Management Page
 */

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status' && isset($_POST['course_id'], $_POST['status'])) {
        $courseId = (int)$_POST['course_id'];
        $status = in_array($_POST['status'], ['draft', 'published', 'archived']) ? $_POST['status'] : 'draft';
        $db->update('courses', ['status' => $status], 'id = ?', [$courseId]);
        header('Location: ?page=courses&msg=status_updated');
        exit;
    }

    if ($action === 'delete' && isset($_POST['course_id'])) {
        $courseId = (int)$_POST['course_id'];
        $db->delete('courses', 'id = ?', [$courseId]);
        header('Location: ?page=courses&msg=deleted');
        exit;
    }

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $instructorId = (int)($_POST['instructor_id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);

        if ($title) {
            $db->insert('courses', [
                'title' => $title,
                'description' => $description,
                'price' => $price,
                'instructor_id' => $instructorId ?: null,
                'category_id' => $categoryId ?: null,
                'status' => 'draft',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            header('Location: ?page=courses&msg=added');
            exit;
        }
    }
}

// Fetch data
$courses = $db->fetchAll("
    SELECT c.*, u.full_name as instructor_name, cat.name as category_name,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    ORDER BY c.created_at DESC
");

$instructors = $db->fetchAll("SELECT id, full_name FROM users WHERE role = 'Instructor' ORDER BY full_name");
$categories = $db->fetchAll("SELECT id, name FROM categories ORDER BY name");

$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Courses</h2>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Course
        </button>
    </div>

    <?php if ($msg): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?= $msg === 'added' ? 'Course added successfully!' : ($msg === 'deleted' ? 'Course deleted!' : 'Status updated!') ?>
        </div>
    <?php endif; ?>

    <!-- Courses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($courses as $course): ?>
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <div class="h-32 bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                    <i class="fas fa-book-open text-4xl text-white opacity-50"></i>
                </div>
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($course['title']) ?></h3>
                        <span class="px-2 py-1 text-xs rounded-full <?= $course['status'] === 'published' ? 'bg-green-100 text-green-700' : ($course['status'] === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') ?>">
                            <?= ucfirst($course['status']) ?>
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mb-3 line-clamp-2"><?= htmlspecialchars(substr($course['description'] ?? '', 0, 100)) ?>...</p>
                    <div class="flex justify-between items-center text-sm text-gray-600 mb-3">
                        <span><i class="fas fa-user mr-1"></i><?= htmlspecialchars($course['instructor_name'] ?? 'No instructor') ?></span>
                        <span><i class="fas fa-users mr-1"></i><?= $course['enrollment_count'] ?> enrolled</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-lg"><?= $currency ?> <?= number_format($course['price'], 2) ?></span>
                        <div class="flex gap-1">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                <input type="hidden" name="status" value="<?= $course['status'] === 'published' ? 'draft' : 'published' ?>">
                                <button type="submit" class="text-xs px-2 py-1 rounded <?= $course['status'] === 'published' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' ?>">
                                    <?= $course['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
                                </button>
                            </form>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete this course?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                <button type="submit" class="text-xs px-2 py-1 rounded bg-red-100 text-red-700">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($courses)): ?>
            <div class="col-span-full text-center py-12 text-gray-500">No courses found</div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Course Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Add New Course</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price (<?= $currency ?>)</label>
                    <input type="number" name="price" step="0.01" value="0" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Instructor</label>
                    <select name="instructor_id" class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select Instructor</option>
                        <?php foreach ($instructors as $instructor): ?>
                            <option value="<?= $instructor['id'] ?>"><?= htmlspecialchars($instructor['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Course</button>
            </div>
        </form>
    </div>
</div>
