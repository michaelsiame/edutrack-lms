<?php
/**
 * Admin Create Course
 */

require_once '../../../src/includes/admin-debug.php';
require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Category.php';
require_once '../../../src/classes/User.php';

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    
    // Validate input
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $instructor_id = $_POST['instructor_id'] ?? '';
    $level = $_POST['level'] ?? 'beginner';
    $language = $_POST['language'] ?? 'English';
    $price = $_POST['price'] ?? 0;
    $duration_hours = $_POST['duration_hours'] ?? null;
    $status = $_POST['status'] ?? 'draft';
    $is_teveta = isset($_POST['is_teveta']) ? 1 : 0;
    $teveta_code = trim($_POST['teveta_code'] ?? '');
    $has_certificate = isset($_POST['has_certificate']) ? 1 : 0;
    $video_url = trim($_POST['video_url'] ?? '');
    $prerequisites = trim($_POST['prerequisites'] ?? '');
    $learning_outcomes = trim($_POST['learning_outcomes'] ?? '');
    $target_audience = trim($_POST['target_audience'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    
    // Validation
    if (empty($title)) {
        $errors['title'] = 'Course title is required';
    }
    if (empty($description)) {
        $errors['description'] = 'Course description is required';
    }
    if (empty($category_id)) {
        $errors['category_id'] = 'Category is required';
    }
    if (empty($instructor_id)) {
        $errors['instructor_id'] = 'Instructor is required';
    }
    if ($price < 0) {
        $errors['price'] = 'Price cannot be negative';
    }
    
    // Handle thumbnail upload
    $thumbnail = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $upload = new FileUpload($_FILES['thumbnail'], 'courses/thumbnails', $allowedTypes);
        $result = $upload->upload();
        
        if ($result && isset($result['filepath'])) {
            $thumbnail = $result['filepath'];
        } else {
            $errors['thumbnail'] = $upload->getError();
        }
    }
    
    if (empty($errors)) {
        $courseData = [
            'title' => $title,
            'slug' => slugify($title),
            'description' => $description,
            'short_description' => $short_description,
            'thumbnail_url' => $thumbnail,
            'category_id' => $category_id,
            'level' => $level,
            'language' => $language,
            'price' => $price,
            'total_hours' => $duration_hours,
            'status' => $status,
            'video_intro_url' => $video_url,
            'prerequisites' => $prerequisites,
            'learning_outcomes' => $learning_outcomes
        ];
        
        $courseId = Course::create($courseData);
        
        if ($courseId) {
            flash('message', 'Course created successfully!', 'success');
            redirect(url('admin/courses/modules.php?id=' . $courseId));
        } else {
            $errors['general'] = 'Failed to create course';
        }
    }
}

// Get categories and instructors
$categories = Category::all();
$instructors = $db->fetchAll("
    SELECT DISTINCT u.id, u.first_name, u.last_name, u.email
    FROM users u
    INNER JOIN user_roles ur ON u.id = ur.user_id
    INNER JOIN roles r ON ur.role_id = r.id
    WHERE r.role_name IN ('Instructor', 'Admin', 'Super Admin')
    ORDER BY u.first_name
");

$page_title = 'Create Course';
require_once '../../../src/templates/admin-header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-plus-circle text-primary-600 mr-2"></i>
                Create New Course
            </h1>
            <a href="<?= url('admin/courses/index.php') ?>" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>Back to Courses
            </a>
        </div>
    </div>
    
    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <h3 class="text-red-800 font-semibold mb-2">Please fix the following errors:</h3>
        <ul class="list-disc list-inside text-red-700 text-sm">
            <?php foreach ($errors as $error): ?>
                <li><?= sanitize($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow">
        <?= csrfField() ?>
        
        <!-- Basic Information -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Basic Information</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Course Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="<?= sanitize($_POST['title'] ?? '') ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="e.g., Complete Web Development Bootcamp">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Short Description
                    </label>
                    <input type="text" name="short_description" value="<?= sanitize($_POST['short_description'] ?? '') ?>" maxlength="500"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="Brief one-line description">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Full Description <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" rows="6" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="Detailed course description..."><?= sanitize($_POST['description'] ?? '') ?></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Instructor <span class="text-red-500">*</span>
                        </label>
                        <select name="instructor_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Instructor</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?= $instructor['id'] ?>" <?= ($_POST['instructor_id'] ?? '') == $instructor['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($instructor['first_name'] . ' ' . $instructor['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                        <select name="level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="all_levels">All Levels</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                        <input type="text" name="language" value="<?= sanitize($_POST['language'] ?? 'English') ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (hours)</label>
                        <input type="number" name="duration_hours" value="<?= sanitize($_POST['duration_hours'] ?? '') ?>" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="e.g., 40">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course Thumbnail</label>
                    <input type="file" name="thumbnail" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Recommended: 1280x720px, Max 2MB</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preview Video URL (Optional)</label>
                    <input type="url" name="video_url" value="<?= sanitize($_POST['video_url'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="https://youtube.com/watch?v=...">
                </div>
            </div>
        </div>
        
        <!-- Pricing & Status -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Pricing & Status</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Price (ZMW)
                    </label>
                    <input type="number" name="price" value="<?= sanitize($_POST['price'] ?? '0') ?>" min="0" step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Set to 0 for free course</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- TEVETA & Certification -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">TEVETA & Certification</h2>
            
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="is_teveta" id="is_teveta" value="1" checked
                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label for="is_teveta" class="ml-2 text-sm text-gray-700">
                        This is a TEVETA-registered course
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">TEVETA Code</label>
                    <input type="text" name="teveta_code" value="<?= sanitize($_POST['teveta_code'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="e.g., TEVETA/IT/2024/001">
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="has_certificate" id="has_certificate" value="1" checked
                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label for="has_certificate" class="ml-2 text-sm text-gray-700">
                        Issue certificate upon completion
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Course Details -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Course Details</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prerequisites</label>
                    <textarea name="prerequisites" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="List any prerequisites or requirements..."><?= sanitize($_POST['prerequisites'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Learning Outcomes</label>
                    <textarea name="learning_outcomes" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="What will students learn? (one per line)"><?= sanitize($_POST['learning_outcomes'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                    <textarea name="target_audience" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="Who is this course for?"><?= sanitize($_POST['target_audience'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                    <textarea name="requirements" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="What do students need? (software, hardware, etc.)"><?= sanitize($_POST['requirements'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="p-6 bg-gray-50">
            <div class="flex items-center justify-end space-x-3">
                <a href="<?= url('admin/courses/index.php') ?>" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-save mr-2"></i>Create Course
                </button>
            </div>
        </div>
        
    </form>
    
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>