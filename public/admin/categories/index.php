<?php
/**
 * Admin Course Categories
 * Manage course categories
 */

require_once '../../../src/middleware/admin-only.php';

$errors = [];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    $action = $_POST['action'] ?? null;
    
    if ($action == 'create') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $errors['name'] = 'Category name is required';
        }
        
        if (empty($errors)) {
            $slug = slugify($name);
            if ($db->insert('categories', ['name' => $name, 'slug' => $slug, 'description' => $description])) {
                flash('message', 'Category created successfully', 'success');
            }
        }
    } elseif ($action == 'delete') {
        $catId = $_POST['category_id'] ?? null;
        if ($catId && $db->delete('categories', 'id = ?', [$catId])) {
            flash('message', 'Category deleted successfully', 'success');
        }
    }
    
    if (empty($errors)) {
        redirect(url('admin/courses/categories.php'));
    }
}

// Get all categories
$categories = $db->fetchAll("
    SELECT c.*, COUNT(co.id) as course_count
    FROM categories c
    LEFT JOIN courses co ON c.id = co.category_id
    GROUP BY c.id
    ORDER BY c.name
");

$page_title = 'Course Categories';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-folder text-primary-600 mr-2"></i>
            Course Categories
        </h1>
        <p class="text-gray-600 mt-1">Organize courses into categories</p>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Create Category Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Add New Category</h2>
            
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Category Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['name']) ? 'border-red-500' : '' ?>">
                        <?php if (isset($errors['name'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['name'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full btn-primary px-6 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>Add Category
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Categories List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">All Categories (<?= count($categories) ?>)</h2>
                </div>
                
                <?php if (empty($categories)): ?>
                    <p class="text-center text-gray-500 py-12">No categories yet</p>
                <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($categories as $category): ?>
                    <div class="p-6 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg"><?= sanitize($category['name']) ?></h3>
                            <?php if ($category['description']): ?>
                                <p class="text-sm text-gray-600 mt-1"><?= sanitize($category['description']) ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="fas fa-book mr-1"></i>
                                <?= $category['course_count'] ?> course<?= $category['course_count'] != 1 ? 's' : '' ?>
                            </p>
                        </div>
                        <div class="ml-4">
                            <form method="POST" class="inline" onsubmit="return confirm('Delete this category? Courses will remain but be uncategorized.')">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>