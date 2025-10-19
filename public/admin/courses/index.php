<?php
/**
 * Admin Courses Management
 */

require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Category.php';

// Handle course deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    validateCSRF();
    $courseId = $_POST['course_id'] ?? null;
    
    if ($courseId) {
        $course = Course::find($courseId);
        if ($course && $course->delete()) {
            flash('message', 'Course deleted successfully', 'success');
        } else {
            flash('message', 'Failed to delete course', 'error');
        }
    }
    redirect(url('admin/courses/index.php'));
}

// Handle status change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'change_status') {
    validateCSRF();
    $courseId = $_POST['course_id'] ?? null;
    $status = $_POST['status'] ?? null;
    
    if ($courseId && in_array($status, ['draft', 'published', 'archived'])) {
        $course = Course::find($courseId);
        if ($course && $course->update(['status' => $status])) {
            flash('message', 'Course status updated', 'success');
        }
    }
    redirect(url('admin/courses/index.php'));
}

// Filters
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// Get courses with filters
$filters = ['include_stats' => true];
if ($status) $filters['status'] = $status;
if ($category) $filters['category_id'] = $category;
if ($search) $filters['search'] = $search;

$courses = Course::all($filters);
$categories = Category::all();

$page_title = 'Manage Courses';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-book text-primary-600 mr-2"></i>
                Manage Courses
            </h1>
            <p class="text-gray-600 mt-1">Create and manage all courses</p>
        </div>
        <a href="<?= url('admin/courses/create.php') ?>" class="btn-primary px-6 py-3 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Create Course
        </a>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <!-- Search -->
            <div>
                <input type="text" 
                       name="search" 
                       value="<?= sanitize($search) ?>"
                       placeholder="Search courses..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            
            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="draft" <?= $status == 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $status == 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= $status == 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            
            <!-- Category Filter -->
            <div>
                <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                            <?= sanitize($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Submit -->
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="<?= url('admin/courses/index.php') ?>" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>
    
    <!-- Courses Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>No courses found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $courseData): ?>
                            <?php $course = new Course($courseData['id']); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <img src="<?= courseThumbnail($course->getThumbnail()) ?>" 
                                             alt="<?= sanitize($course->getTitle()) ?>"
                                             class="h-12 w-16 object-cover rounded">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= sanitize($course->getTitle()) ?></div>
                                            <div class="text-sm text-gray-500"><?= sanitize($course->getCategoryName()) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= sanitize($course->getInstructorName()) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= sanitize($course->getCategoryName()) ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    <?= $course->isFree() ? '<span class="text-green-600">Free</span>' : formatCurrency($course->getPrice()) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= number_format($course->getStudentCount()) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="change_status">
                                        <input type="hidden" name="course_id" value="<?= $course->getId() ?>">
                                        <select name="status" onchange="this.form.submit()" 
                                                class="text-xs rounded-full px-3 py-1 font-semibold
                                                <?php
                                                switch($course->getStatus()) {
                                                    case 'published': echo 'bg-green-100 text-green-800'; break;
                                                    case 'draft': echo 'bg-yellow-100 text-yellow-800'; break;
                                                    case 'archived': echo 'bg-gray-100 text-gray-800'; break;
                                                }
                                                ?>">
                                            <option value="draft" <?= $course->getStatus() == 'draft' ? 'selected' : '' ?>>Draft</option>
                                            <option value="published" <?= $course->getStatus() == 'published' ? 'selected' : '' ?>>Published</option>
                                            <option value="archived" <?= $course->getStatus() == 'archived' ? 'selected' : '' ?>>Archived</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= url('course.php?slug=' . $course->getSlug()) ?>" 
                                           target="_blank"
                                           class="text-blue-600 hover:text-blue-800"
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= url('admin/courses/edit.php?id=' . $course->getId()) ?>" 
                                           class="text-green-600 hover:text-green-800"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= url('admin/courses/modules.php?id=' . $course->getId()) ?>" 
                                           class="text-purple-600 hover:text-purple-800"
                                           title="Modules">
                                            <i class="fas fa-list"></i>
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirmDelete('Delete this course and all its content?')">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="course_id" value="<?= $course->getId() ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>