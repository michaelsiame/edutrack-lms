<?php
/**
 * Courses Management Page - Full CRUD
 * Features: Add, Edit, Delete, Status Toggle, Table/Grid View, Filters, Pagination
 * Note: AJAX and POST handlers are processed in index.php and handlers/courses_handler.php
 */

// Pagination
$page_num = max(1, (int)($_GET['p'] ?? 1));
$per_page = 12;
$offset = ($page_num - 1) * $per_page;

// Filters
$search = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$levelFilter = $_GET['level'] ?? '';
$view = $_GET['view'] ?? 'grid';

$sql = "
    SELECT c.*,
           CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
           cat.name as category_name,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
           (SELECT COUNT(*) FROM modules WHERE course_id = c.id) as module_count
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.id
    LEFT JOIN course_categories cat ON c.category_id = cat.id
    WHERE 1=1
";
$countSql = "SELECT COUNT(*) FROM courses c WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $countSql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryFilter) {
    $sql .= " AND c.category_id = ?";
    $countSql .= " AND c.category_id = ?";
    $params[] = (int)$categoryFilter;
}

if ($statusFilter) {
    $sql .= " AND c.status = ?";
    $countSql .= " AND c.status = ?";
    $params[] = $statusFilter;
}

if ($levelFilter) {
    $sql .= " AND c.level = ?";
    $countSql .= " AND c.level = ?";
    $params[] = $levelFilter;
}

$totalCourses = $db->fetchColumn($countSql, $params);
$totalPages = ceil($totalCourses / $per_page);

$sql .= " ORDER BY c.created_at DESC LIMIT $per_page OFFSET $offset";
$courses = $db->fetchAll($sql, $params);

// Get instructors and categories
$instructors = $db->fetchAll("
    SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as full_name
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    JOIN roles r ON ur.role_id = r.id
    WHERE r.role_name = 'Instructor'
    ORDER BY u.first_name, u.last_name
");
$categories = $db->fetchAll("SELECT id, name FROM course_categories ORDER BY name");

// Stats
$publishedCourses = $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE status = 'published'");
$draftCourses = $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE status = 'draft'");
$totalEnrollmentsAll = $db->fetchColumn("SELECT COUNT(*) FROM enrollments");
$featuredCourses = $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE is_featured = 1");

$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Course Management</h2>
            <p class="text-gray-500 text-sm mt-1">Create and manage your course catalog</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 shadow-sm">
                <i class="fas fa-plus"></i>
                <span>Add Course</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($msg): ?>
        <div class="<?= $msg === 'cannot_delete' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700' ?> border px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas <?= $msg === 'cannot_delete' ? 'fa-exclamation-circle' : 'fa-check-circle' ?>"></i>
            <?php
            echo match($msg) {
                'added' => 'Course created successfully!',
                'updated' => 'Course updated successfully!',
                'deleted' => 'Course deleted successfully!',
                'status_updated' => 'Course status updated!',
                'featured_updated' => 'Featured status updated!',
                'cannot_delete' => 'Cannot delete course with active enrollments!',
                default => 'Action completed!'
            };
            ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $publishedCourses ?></p>
                    <p class="text-xs text-gray-500">Published</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 text-yellow-600 rounded-lg">
                    <i class="fas fa-edit"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $draftCourses ?></p>
                    <p class="text-xs text-gray-500">Drafts</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $totalEnrollmentsAll ?></p>
                    <p class="text-xs text-gray-500">Total Enrollments</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $featuredCourses ?></p>
                    <p class="text-xs text-gray-500">Featured</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <input type="hidden" name="page" value="courses">
            <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search courses..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <select name="category" class="px-4 py-2 border rounded-lg">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="px-4 py-2 border rounded-lg">
                <option value="">All Status</option>
                <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
            <select name="level" class="px-4 py-2 border rounded-lg">
                <option value="">All Levels</option>
                <option value="Beginner" <?= $levelFilter === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
                <option value="Intermediate" <?= $levelFilter === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                <option value="Advanced" <?= $levelFilter === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
            </select>
            <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-800">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <?php if ($search || $categoryFilter || $statusFilter || $levelFilter): ?>
                <a href="?page=courses&view=<?= $view ?>" class="text-gray-600 hover:text-gray-800 px-3 py-2">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
            <div class="ml-auto flex gap-1 border rounded-lg p-1">
                <a href="?page=courses&view=grid&search=<?= urlencode($search) ?>&category=<?= $categoryFilter ?>&status=<?= $statusFilter ?>&level=<?= $levelFilter ?>" class="p-2 rounded <?= $view === 'grid' ? 'bg-gray-200' : 'hover:bg-gray-100' ?>">
                    <i class="fas fa-th"></i>
                </a>
                <a href="?page=courses&view=table&search=<?= urlencode($search) ?>&category=<?= $categoryFilter ?>&status=<?= $statusFilter ?>&level=<?= $levelFilter ?>" class="p-2 rounded <?= $view === 'table' ? 'bg-gray-200' : 'hover:bg-gray-100' ?>">
                    <i class="fas fa-list"></i>
                </a>
            </div>
        </form>
    </div>

    <?php if ($view === 'table'): ?>
    <!-- Table View -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Instructor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Enrollments</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($courses as $course): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if ($course['thumbnail_url']): ?>
                                        <img src="<?= htmlspecialchars($course['thumbnail_url']) ?>" class="w-12 h-12 rounded-lg object-cover" alt="">
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-medium text-gray-800"><?= htmlspecialchars($course['title']) ?></p>
                                        <p class="text-xs text-gray-500"><?= $course['level'] ?> - <?= $course['module_count'] ?> modules</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($course['category_name'] ?? 'Uncategorized') ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($course['instructor_name'] ?? 'No instructor') ?></td>
                            <td class="px-6 py-4">
                                <?php if ($course['discount_price']): ?>
                                    <span class="text-sm text-gray-400 line-through"><?= $currency ?> <?= number_format($course['price'], 2) ?></span>
                                    <span class="font-semibold text-green-600"><?= $currency ?> <?= number_format($course['discount_price'], 2) ?></span>
                                <?php else: ?>
                                    <span class="font-semibold"><?= $currency ?> <?= number_format($course['price'], 2) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 text-sm">
                                    <i class="fas fa-users text-gray-400"></i>
                                    <?= $course['enrollment_count'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $statusClass = match($course['status']) {
                                    'published' => 'bg-green-100 text-green-700',
                                    'draft' => 'bg-yellow-100 text-yellow-700',
                                    'archived' => 'bg-gray-100 text-gray-700',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                                ?>
                                <span class="px-2 py-1 text-xs rounded-full <?= $statusClass ?>"><?= ucfirst($course['status']) ?></span>
                                <?php if ($course['is_featured']): ?>
                                    <span class="ml-1 text-yellow-500"><i class="fas fa-star"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="openEditModal(<?= $course['id'] ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?page=modules&course_id=<?= $course['id'] ?>" class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg" title="Manage Modules">
                                        <i class="fas fa-layer-group"></i>
                                    </a>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle_featured">
                                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                        <button type="submit" class="p-2 <?= $course['is_featured'] ? 'text-yellow-500' : 'text-gray-400' ?> hover:bg-yellow-50 rounded-lg" title="Toggle Featured">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $course['status'] === 'published' ? 'draft' : 'published' ?>">
                                        <button type="submit" class="p-2 <?= $course['status'] === 'published' ? 'text-yellow-600' : 'text-green-600' ?> hover:bg-gray-50 rounded-lg" title="<?= $course['status'] === 'published' ? 'Unpublish' : 'Publish' ?>">
                                            <i class="fas <?= $course['status'] === 'published' ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this course? This cannot be undone.')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($courses)): ?>
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No courses found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <!-- Grid View -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($courses as $course): ?>
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden group hover:shadow-md transition-shadow">
                <div class="relative h-36 bg-gradient-to-br from-blue-500 to-purple-600">
                    <?php if ($course['thumbnail_url']): ?>
                        <img src="<?= htmlspecialchars($course['thumbnail_url']) ?>" class="w-full h-full object-cover" alt="">
                    <?php else: ?>
                        <div class="flex items-center justify-center h-full">
                            <i class="fas fa-book-open text-4xl text-white opacity-50"></i>
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-2 right-2 flex gap-1">
                        <?php if ($course['is_featured']): ?>
                            <span class="bg-yellow-400 text-yellow-900 px-2 py-0.5 text-xs rounded-full font-medium">Featured</span>
                        <?php endif; ?>
                        <span class="<?= $course['status'] === 'published' ? 'bg-green-500' : ($course['status'] === 'draft' ? 'bg-yellow-500' : 'bg-gray-500') ?> text-white px-2 py-0.5 text-xs rounded-full font-medium">
                            <?= ucfirst($course['status']) ?>
                        </span>
                    </div>
                    <div class="absolute top-2 left-2">
                        <span class="bg-white/90 text-gray-700 px-2 py-0.5 text-xs rounded-full font-medium"><?= $course['level'] ?></span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-1 line-clamp-1"><?= htmlspecialchars($course['title']) ?></h3>
                    <p class="text-xs text-gray-500 mb-3"><?= htmlspecialchars($course['category_name'] ?? 'Uncategorized') ?></p>

                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                        <span><i class="fas fa-user mr-1"></i><?= htmlspecialchars($course['instructor_name'] ?? 'TBA') ?></span>
                        <span class="text-gray-300">|</span>
                        <span><i class="fas fa-users mr-1"></i><?= $course['enrollment_count'] ?></span>
                        <span class="text-gray-300">|</span>
                        <span><i class="fas fa-layer-group mr-1"></i><?= $course['module_count'] ?></span>
                    </div>

                    <div class="flex items-center justify-between mb-3">
                        <?php if ($course['discount_price']): ?>
                            <div>
                                <span class="text-xs text-gray-400 line-through"><?= $currency ?> <?= number_format($course['price'], 2) ?></span>
                                <span class="font-bold text-lg text-green-600"><?= $currency ?> <?= number_format($course['discount_price'], 2) ?></span>
                            </div>
                        <?php else: ?>
                            <span class="font-bold text-lg"><?= $currency ?> <?= number_format($course['price'], 2) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex gap-1 pt-3 border-t">
                        <button onclick="openEditModal(<?= $course['id'] ?>)" class="flex-1 text-center py-1.5 text-blue-600 hover:bg-blue-50 rounded text-sm font-medium">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <a href="?page=modules&course_id=<?= $course['id'] ?>" class="flex-1 text-center py-1.5 text-purple-600 hover:bg-purple-50 rounded text-sm font-medium">
                            <i class="fas fa-layer-group mr-1"></i>Modules
                        </a>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                            <input type="hidden" name="status" value="<?= $course['status'] === 'published' ? 'draft' : 'published' ?>">
                            <button type="submit" class="py-1.5 px-2 <?= $course['status'] === 'published' ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' ?> rounded text-sm">
                                <i class="fas <?= $course['status'] === 'published' ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($courses)): ?>
            <div class="col-span-full py-12 text-center text-gray-400">
                <i class="fas fa-book text-4xl mb-3"></i>
                <p class="text-lg font-medium">No courses found</p>
                <p class="text-sm">Try adjusting your filters or add a new course</p>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border">
            <p class="text-sm text-gray-600">
                Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $totalCourses) ?> of <?= $totalCourses ?> courses
            </p>
            <div class="flex gap-1">
                <?php if ($page_num > 1): ?>
                    <a href="?page=courses&p=<?= $page_num - 1 ?>&view=<?= $view ?>&search=<?= urlencode($search) ?>&category=<?= $categoryFilter ?>&status=<?= $statusFilter ?>&level=<?= $levelFilter ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-100">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                <?php for ($i = max(1, $page_num - 2); $i <= min($totalPages, $page_num + 2); $i++): ?>
                    <a href="?page=courses&p=<?= $i ?>&view=<?= $view ?>&search=<?= urlencode($search) ?>&category=<?= $categoryFilter ?>&status=<?= $statusFilter ?>&level=<?= $levelFilter ?>" class="px-3 py-1 border rounded-lg <?= $i === $page_num ? 'bg-blue-600 text-white border-blue-600' : 'hover:bg-gray-100' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page_num < $totalPages): ?>
                    <a href="?page=courses&p=<?= $page_num + 1 ?>&view=<?= $view ?>&search=<?= urlencode($search) ?>&category=<?= $categoryFilter ?>&status=<?= $statusFilter ?>&level=<?= $levelFilter ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-100">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Course Modal -->
<div id="courseModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b sticky top-0 bg-white z-10">
            <div class="flex justify-between items-center">
                <h3 id="modalTitle" class="text-xl font-semibold text-gray-800">Add New Course</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
        </div>
        <form id="courseForm" method="POST" class="p-6">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="course_id" id="courseId" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                    <input type="text" name="short_description" id="shortDescription" maxlength="500" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Brief summary (max 500 chars)">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                    <textarea name="description" id="description" rows="4" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category_id" id="categoryId" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instructor</label>
                        <select name="instructor_id" id="instructorId" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Instructor</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?= $instructor['id'] ?>"><?= htmlspecialchars($instructor['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (<?= $currency ?>)</label>
                        <input type="number" name="price" id="price" step="0.01" min="0" value="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discount Price</label>
                        <input type="number" name="discount_price" id="discountPrice" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                        <select name="level" id="level" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duration (weeks)</label>
                        <input type="number" name="duration_weeks" id="durationWeeks" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Hours</label>
                        <input type="number" name="total_hours" id="totalHours" step="0.5" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Students</label>
                        <input type="number" name="max_students" id="maxStudents" min="1" value="30" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" id="startDate" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" id="endDate" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thumbnail URL</label>
                    <input type="url" name="thumbnail_url" id="thumbnailUrl" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="https://example.com/image.jpg">
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50 font-medium">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    <span id="submitBtn">Add Course</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Course';
    document.getElementById('formAction').value = 'add';
    document.getElementById('courseId').value = '';
    document.getElementById('submitBtn').textContent = 'Add Course';
    document.getElementById('courseForm').reset();
    document.getElementById('courseModal').classList.remove('hidden');
}

function openEditModal(courseId) {
    document.getElementById('modalTitle').textContent = 'Edit Course';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('courseId').value = courseId;
    document.getElementById('submitBtn').textContent = 'Save Changes';

    fetch('?page=courses&ajax=get_course&id=' + courseId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            document.getElementById('title').value = data.title || '';
            document.getElementById('shortDescription').value = data.short_description || '';
            document.getElementById('description').value = data.description || '';
            document.getElementById('categoryId').value = data.category_id || '';
            document.getElementById('instructorId').value = data.instructor_id || '';
            document.getElementById('price').value = data.price || 0;
            document.getElementById('discountPrice').value = data.discount_price || '';
            document.getElementById('level').value = data.level || 'Beginner';
            document.getElementById('durationWeeks').value = data.duration_weeks || '';
            document.getElementById('totalHours').value = data.total_hours || '';
            document.getElementById('maxStudents').value = data.max_students || 30;
            document.getElementById('startDate').value = data.start_date || '';
            document.getElementById('endDate').value = data.end_date || '';
            document.getElementById('thumbnailUrl').value = data.thumbnail_url || '';
            document.getElementById('courseModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load course data');
        });
}

function closeModal() {
    document.getElementById('courseModal').classList.add('hidden');
}

document.getElementById('courseModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
