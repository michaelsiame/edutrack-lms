<?php
/**
 * Admin Courses List
 * View and manage all courses
 */

require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Course.php';

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    validateCSRF();
    $courseId = $_POST['course_id'] ?? null;
    
    if ($courseId && $db->delete('courses', 'id = ?', [$courseId])) {
        flash('message', 'Course deleted successfully', 'success');
    }
    redirect(url('admin/courses/index.php'));
}

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$sql = "SELECT c.*, 
        cat.name as category_name,
        u.first_name, u.last_name,
        COUNT(DISTINCT e.id) as enrollments
        FROM courses c
        LEFT JOIN categories cat ON c.category_id = cat.id
        LEFT JOIN users u ON c.instructor_id = u.id
        LEFT JOIN enrollments e ON c.id = e.course_id
        WHERE 1=1";

$params = [];

if ($search) {
    $sql .= " AND c.title LIKE ?";
    $params[] = "%$search%";
}

if ($status) {
    $sql .= " AND c.status = ?";
    $params[] = $status;
}

if ($category) {
    $sql .= " AND c.category_id = ?";
    $params[] = $category;
}

$sql .= " GROUP BY c.id";

// Get total count
$countSql = "SELECT COUNT(*) FROM ($sql) as count_table";
$totalCourses = $db->fetchColumn($countSql, $params);
$totalPages = ceil($totalCourses / $perPage);

// Get courses
$sql .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$courses = $db->fetchAll($sql, $params);

// Get categories for filter
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

$page_title = 'Manage Courses';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-book text-primary-600 mr-2"></i>
                All Courses
            </h1>
            <p class="text-gray-600 mt-1"><?= number_format($totalCourses) ?> total courses</p>
        </div>
        <a href="<?= url('admin/courses/create.php') ?>" class="btn-primary px-6 py-2 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Create Course
        </a>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <form method="GET" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" name="search" value="<?= sanitize($search) ?>"
                           placeholder="Search courses..."
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <select name="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Status</option>
                        <option value="draft" <?= $status == 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $status == 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="archived" <?= $status == 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                <div>
                    <select name="category" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                <?= sanitize($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 btn-primary px-6 py-2 rounded-lg">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <a href="<?= url('admin/courses/index.php') ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Courses Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($courses)): ?>
            <p class="text-center text-gray-500 py-12">No courses found</p>
        <?php else: ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instructor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($courses as $course): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <?php if ($course['thumbnail']): ?>
                                <img src="<?= uploadUrl($course['thumbnail']) ?>" alt="" class="w-16 h-16 object-cover rounded mr-3">
                            <?php endif; ?>
                            <div>
                                <p class="font-medium text-gray-900"><?= sanitize($course['title']) ?></p>
                                <p class="text-sm text-gray-500"><?= date('M d, Y', strtotime($course['created_at'])) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900"><?= sanitize($course['first_name'] . ' ' . $course['last_name']) ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                            <?= sanitize($course['category_name']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 font-semibold">
                        <?= $course['price'] > 0 ? formatCurrency($course['price']) : 'Free' ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">
                            <?= $course['enrollments'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <?php
                        $statusColors = [
                            'published' => 'bg-green-100 text-green-800',
                            'draft' => 'bg-yellow-100 text-yellow-800',
                            'archived' => 'bg-gray-100 text-gray-800'
                        ];
                        $color = $statusColors[$course['status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $color ?>">
                            <?= ucfirst($course['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="<?= url('course.php?slug=' . $course['slug']) ?>" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= url('admin/courses/edit.php?id=' . $course['id']) ?>" 
                               class="text-green-600 hover:text-green-800" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= url('admin/courses/modules.php?id=' . $course['id']) ?>" 
                               class="text-purple-600 hover:text-purple-800" title="Modules">
                                <i class="fas fa-list"></i>
                            </a>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete this course and all its content?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Showing <?= number_format($offset + 1) ?> to <?= number_format(min($offset + $perPage, $totalCourses)) ?> 
                    of <?= number_format($totalCourses) ?> courses
                </p>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&category=<?= $category ?>" 
                           class="px-4 py-2 border rounded hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&category=<?= $category ?>" 
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