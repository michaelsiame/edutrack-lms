<?php
/**
 * Admin Edit Category
 */

require_once '../../../src/includes/admin-debug.php';
require_once '../../../src/middleware/admin-only.php';

$categoryId = $_GET['id'] ?? null;

if (!$categoryId) {
    flash('message', 'Category not found', 'error');
    redirect(url('admin/categories/index.php'));
}

// Get category
$category = $db->fetchOne("SELECT *, category_description as description, icon_url as icon FROM course_categories WHERE id = ?", [$categoryId]);

if (!$category) {
    flash('message', 'Category not found', 'error');
    redirect(url('admin/categories/index.php'));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('message', 'Invalid request', 'error');
        redirect(url('admin/categories/edit.php?id=' . $categoryId));
    }

    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? '');

    $errors = [];

    if (empty($name)) {
        $errors[] = 'Category name is required';
    }

    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $slug = trim($slug, '-');
    }

    // Check if name already exists (excluding current category)
    if (empty($errors)) {
        $existingCategory = $db->fetchOne("SELECT id FROM course_categories WHERE name = ? AND id != ?", [$name, $categoryId]);
        if ($existingCategory) {
            $errors[] = 'Category name already exists';
        }
    }

    if (empty($errors)) {
        $result = $db->update('course_categories', [
            'name' => $name,
            'category_description' => $description,
            'icon_url' => $icon
        ], 'id = ?', [$categoryId]);

        if ($result !== false) {
            flash('message', 'Category updated successfully', 'success');
            redirect(url('admin/categories/index.php'));
        } else {
            flash('message', 'Failed to update category', 'error');
        }
    } else {
        foreach ($errors as $error) {
            flash('message', $error, 'error');
        }
    }
}

$page_title = 'Edit Category';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container-fluid px-4 py-6">

    <div class="mb-6">
        <a href="<?= url('admin/categories/index.php') ?>" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Categories
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit Category</h1>
        <p class="text-gray-600 mt-1">Update category information</p>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-lg shadow">
            <form method="POST" class="p-6 space-y-6">
                <?= csrfField() ?>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" required
                           value="<?= htmlspecialchars($_POST['name'] ?? $category['name']) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($_POST['description'] ?? $category['description']) ?></textarea>
                </div>

                <div>
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-1">
                        Icon (Font Awesome class)
                    </label>
                    <input type="text" id="icon" name="icon"
                           value="<?= htmlspecialchars($_POST['icon'] ?? $category['icon']) ?>"
                           placeholder="e.g., fa-laptop-code"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Font Awesome icon class (optional)</p>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                    <a href="<?= url('admin/categories/index.php') ?>"
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>
