<?php
/**
 * Admin Edit Course
 */

require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Category.php';
require_once '../../../src/classes/User.php';
require_once '../../../src/classes/FileUpload.php';

$courseId = $_GET['id'] ?? null;

if (!$courseId) {
    redirect(url('admin/courses/index.php'));
}

$course = Course::find($courseId);

if (!$course) {
    flash('message', 'Course not found', 'error');
    redirect(url('admin/courses/index.php'));
}

// Get categories and instructors
$categories = Category::all(['active_only' => true]);
$instructors = User::getByRole('instructor');

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();
    
    $title = trim($_POST['title'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $instructor_id = $_POST['instructor_id'] ?? null;
    $level = $_POST['level'] ?? 'beginner';
    $language = $_POST['language'] ?? 'English';
    $price = floatval($_POST['price'] ?? 0);
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
    $thumbnail = $course->getThumbnail();
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $upload = new FileUpload($_FILES['thumbnail'], 'courses/thumbnails', $allowedTypes);
        $result = $upload->upload();
        
        if ($result && isset($result['filepath'])) {
            // Delete old thumbnail
            if ($thumbnail) {
                FileUpload::delete($thumbnail);
            }
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
            'thumbnail' => $thumbnail,
            'category_id' => $category_id,
            'instructor_id' => $instructor_id,
            'level' => $level,
            'language' => $language,
            'price' => $price,
            'duration_hours' => $duration_hours,
            'status' => $status,
            'is_teveta' => $is_teveta,
            'teveta_code' => $teveta_code,
            'has_certificate' => $has_certificate,
            'video_url' => $video_url,
            'prerequisites' => $prerequisites,
            'learning_outcomes' => $learning_outcomes,
            'target_audience' => $target_audience,
            'requirements' => $requirements
        ];
        
        if ($course->update($courseData)) {
            flash('message', 'Course updated successfully!', 'success');
            redirect(url('admin/courses/index.php'));
        } else {
            flash('message', 'Failed to update course', 'error');
        }
    }
}

$page_title = 'Edit Course - Admin';
require_once '../../../src/templates/admin-header.php';
?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Course</h1>
                    <p class="text-gray-600 mt-1">Update course information and settings</p>
                </div>
                <a href="<?= url('admin/courses/index.php') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Courses
                </a>
            </div>
        </div>

        <?php if (hasFlash('message')): ?>
            <?= renderFlash() ?>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= csrfField() ?>
            
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Basic Information</h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Course Title *</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($course->getTitle()) ?>" 
                               class="w-full px-4 py-2 border rounded-lg <?= isset($errors['title']) ? 'border-red-500' : '' ?>" required>
                        <?php if (isset($errors['title'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['title'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                        <textarea name="short_description" rows="2" 
                                  class="w-full px-4 py-2 border rounded-lg"><?= htmlspecialchars($course->getShortDescription()) ?></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Description *</label>
                        <textarea name="description" rows="6" 
                                  class="w-full px-4 py-2 border rounded-lg <?= isset($errors['description']) ? 'border-red-500' : '' ?>" 
                                  required><?= htmlspecialchars($course->getDescription()) ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['description'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                        <select name="category_id" class="w-full px-4 py-2 border rounded-lg" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $category['id'] == $course->getCategoryId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Instructor *</label>
                        <select name="instructor_id" class="w-full px-4 py-2 border rounded-lg" required>
                            <option value="">Select Instructor</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?= $instructor['id'] ?>" <?= $instructor['id'] == $course->getInstructorId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                        <select name="level" class="w-full px-4 py-2 border rounded-lg">
                            <option value="beginner" <?= $course->getLevel() == 'beginner' ? 'selected' : '' ?>>Beginner</option>
                            <option value="intermediate" <?= $course->getLevel() == 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                            <option value="advanced" <?= $course->getLevel() == 'advanced' ? 'selected' : '' ?>>Advanced</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                        <input type="text" name="language" value="<?= htmlspecialchars($course->getLanguage()) ?>" 
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price (ZMW)</label>
                        <input type="number" name="price" step="0.01" value="<?= $course->getPrice() ?>" 
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Hours)</label>
                        <input type="number" name="duration_hours" value="<?= $course->getDurationHours() ?>" 
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border rounded-lg">
                            <option value="draft" <?= $course->getStatus() == 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= $course->getStatus() == 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="archived" <?= $course->getStatus() == 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Course Thumbnail</label>
                        <?php if ($course->getThumbnail()): ?>
                            <div class="mb-2">
                                <img src="<?= uploadUrl($course->getThumbnail()) ?>" 
                                     alt="Current thumbnail" class="h-32 rounded-lg">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="thumbnail" accept="image/*" class="w-full px-4 py-2 border rounded-lg">
                        <p class="text-sm text-gray-500 mt-1">Recommended: 1280x720px, Max 2MB</p>
                    </div>
                </div>
            </div>
            
            <!-- TEVETA & Certificate Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">TEVETA & Certificate</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_teveta" id="is_teveta" 
                               class="h-4 w-4 text-primary-600" <?= $course->isTeveta() ? 'checked' : '' ?>>
                        <label for="is_teveta" class="ml-2 text-sm text-gray-700">
                            <span class="font-medium">TEVETA Registered Course</span>
                        </label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">TEVETA Code</label>
                        <input type="text" name="teveta_code" value="<?= htmlspecialchars($course->getTevetaCode()) ?>" 
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="has_certificate" id="has_certificate" 
                               class="h-4 w-4 text-primary-600" <?= $course->hasCertificate() ? 'checked' : '' ?>>
                        <label for="has_certificate" class="ml-2 text-sm text-gray-700">
                            <span class="font-medium">Issue Certificate on Completion</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Additional Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preview Video URL</label>
                        <input type="url" name="video_url" value="<?= htmlspecialchars($course->getVideoUrl()) ?>" 
                               class="w-full px-4 py-2 border rounded-lg" 
                               placeholder="https://youtube.com/watch?v=...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prerequisites</label>
                        <textarea name="prerequisites" rows="3" 
                                  class="w-full px-4 py-2 border rounded-lg"><?= htmlspecialchars($course->getPrerequisites()) ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Learning Outcomes</label>
                        <textarea name="learning_outcomes" rows="4" 
                                  class="w-full px-4 py-2 border rounded-lg"><?= htmlspecialchars($course->getLearningOutcomes()) ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">One outcome per line</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                        <textarea name="target_audience" rows="3" 
                                  class="w-full px-4 py-2 border rounded-lg"><?= htmlspecialchars($course->getTargetAudience()) ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                        <textarea name="requirements" rows="3" 
                                  class="w-full px-4 py-2 border rounded-lg"><?= htmlspecialchars($course->getRequirements()) ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="<?= url('admin/courses/index.php') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> Update Course
                </button>
            </div>
        </form>
        
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>