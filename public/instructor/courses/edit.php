<?php
/**
 * Course Edit Page
 * Allows instructors to edit their courses
 */

require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Category.php';
require_once '../../../src/classes/FileUpload.php';

$user = User::current();
$instructorId = $user->getId();

// Get course ID
if (!isset($_GET['id'])) {
    setFlash('Course not found.', 'error');
    redirect('instructor/courses.php');
}

$courseId = (int)$_GET['id'];
$course = Course::find($courseId);

if (!$course) {
    setFlash('Course not found.', 'error');
    redirect('instructor/courses.php');
}

// Verify ownership
if ($course->getInstructorId() != $instructorId && !hasRole('admin')) {
    setFlash('You do not have permission to edit this course.', 'error');
    redirect('instructor/courses.php');
}

// Get all categories
$categories = Category::active();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        setFlash('Invalid security token.', 'error');
        redirect('instructor/courses/edit.php?id=' . $courseId);
    }

    // Validate required fields
    $errors = [];
    if (empty($_POST['title'])) {
        $errors[] = 'Course title is required';
    }
    if (empty($_POST['category_id'])) {
        $errors[] = 'Please select a category';
    }
    if (empty($_POST['level'])) {
        $errors[] = 'Please select a difficulty level';
    }

    if (!empty($errors)) {
        setFlash(implode(', ', $errors), 'error');
    } else {
        // Handle thumbnail upload
        $thumbnailPath = $course->getThumbnail();
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $upload = new FileUpload($_FILES['thumbnail']);
            $upload->setAllowedTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);
            $upload->setMaxSize(5 * 1024 * 1024); // 5MB
            $upload->setUploadDir(__DIR__ . '/../../uploads/courses/thumbnails/');

            if ($upload->upload()) {
                // Delete old thumbnail if exists
                if ($thumbnailPath && file_exists(__DIR__ . '/../../uploads/courses/thumbnails/' . $thumbnailPath)) {
                    unlink(__DIR__ . '/../../uploads/courses/thumbnails/' . $thumbnailPath);
                }
                $thumbnailPath = $upload->getFileName();
            } else {
                $errors[] = 'Thumbnail upload failed: ' . $upload->getError();
            }
        }

        if (empty($errors)) {
            // Prepare course data
            $courseData = [
                'title' => sanitize($_POST['title']),
                'slug' => slugify($_POST['slug']),
                'short_description' => sanitize($_POST['short_description'] ?? ''),
                'description' => $_POST['description'] ?? '',
                'what_you_will_learn' => $_POST['what_you_will_learn'] ?? '',
                'requirements' => $_POST['requirements'] ?? '',
                'category_id' => (int)$_POST['category_id'],
                'level' => sanitize($_POST['level']),
                'language' => sanitize($_POST['language'] ?? 'English'),
                'price' => (float)($_POST['price'] ?? 0),
                'duration' => (int)($_POST['duration'] ?? 0),
                'status' => sanitize($_POST['status'] ?? 'draft'),
                'teveta_accredited' => isset($_POST['teveta_accredited']) ? 1 : 0,
                'teveta_course_code' => sanitize($_POST['teveta_course_code'] ?? ''),
                'certificate_available' => isset($_POST['certificate_available']) ? 1 : 0,
                'featured' => $course->isFeatured() ? 1 : 0 // Preserve featured status
            ];

            // Only update thumbnail if new one was uploaded
            if ($thumbnailPath !== $course->getThumbnail()) {
                $db->query("UPDATE courses SET thumbnail = ? WHERE id = ?", [$thumbnailPath, $courseId]);
            }

            // Update course
            if ($course->update($courseData)) {
                setFlash('Course updated successfully!', 'success');
                redirect('instructor/courses/edit.php?id=' . $courseId);
            } else {
                setFlash('Failed to update course. Please try again.', 'error');
            }
        } else {
            setFlash(implode(', ', $errors), 'error');
        }
    }
}

$page_title = 'Edit Course - ' . $course->getTitle();
require_once '../../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Course</h1>
                    <p class="text-gray-600 mt-2"><?= htmlspecialchars($course->getTitle()) ?></p>
                </div>
                <div class="flex gap-2">
                    <a href="<?= url('instructor/courses/modules.php?id=' . $courseId) ?>" class="btn btn-secondary">
                        <i class="fas fa-list mr-2"></i> Manage Content
                    </a>
                    <a href="<?= url('instructor/courses.php') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Course Edit Form -->
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="p-6 space-y-6">

                <!-- Basic Information -->
                <div class="border-b pb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Basic Information</h2>

                    <div class="space-y-4">
                        <!-- Course Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Course Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   value="<?= htmlspecialchars($course->getTitle()) ?>">
                        </div>

                        <!-- Course Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                URL Slug
                            </label>
                            <input type="text"
                                   id="slug"
                                   name="slug"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   value="<?= htmlspecialchars($course->getSlug()) ?>">
                            <p class="text-sm text-gray-500 mt-1">Current URL: <?= url('course.php?slug=' . $course->getSlug()) ?></p>
                        </div>

                        <!-- Short Description -->
                        <div>
                            <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Short Description
                            </label>
                            <input type="text"
                                   id="short_description"
                                   name="short_description"
                                   maxlength="200"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   value="<?= htmlspecialchars($course->getShortDescription()) ?>">
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Description
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="6"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"><?= htmlspecialchars($course->getDescription()) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Learning Outcomes & Requirements -->
                <div class="border-b pb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Learning Outcomes & Requirements</h2>

                    <div class="space-y-4">
                        <!-- What You Will Learn -->
                        <div>
                            <label for="what_you_will_learn" class="block text-sm font-medium text-gray-700 mb-2">
                                What Students Will Learn
                            </label>
                            <textarea id="what_you_will_learn"
                                      name="what_you_will_learn"
                                      rows="5"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"><?= htmlspecialchars($course->getWhatYouWillLearn()) ?></textarea>
                        </div>

                        <!-- Requirements -->
                        <div>
                            <label for="requirements" class="block text-sm font-medium text-gray-700 mb-2">
                                Requirements & Prerequisites
                            </label>
                            <textarea id="requirements"
                                      name="requirements"
                                      rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"><?= htmlspecialchars($course->getRequirements()) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Course Details -->
                <div class="border-b pb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Course Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Category -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select id="category_id"
                                    name="category_id"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $course->getCategoryId() == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Level -->
                        <div>
                            <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                                Difficulty Level <span class="text-red-500">*</span>
                            </label>
                            <select id="level"
                                    name="level"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="beginner" <?= $course->getLevel() == 'beginner' ? 'selected' : '' ?>>Beginner</option>
                                <option value="intermediate" <?= $course->getLevel() == 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                <option value="advanced" <?= $course->getLevel() == 'advanced' ? 'selected' : '' ?>>Advanced</option>
                                <option value="all_levels" <?= $course->getLevel() == 'all_levels' ? 'selected' : '' ?>>All Levels</option>
                            </select>
                        </div>

                        <!-- Language -->
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                                Language
                            </label>
                            <input type="text"
                                   id="language"
                                   name="language"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   value="<?= htmlspecialchars($course->getLanguage()) ?>">
                        </div>

                        <!-- Duration -->
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
                                Total Duration (hours)
                            </label>
                            <input type="number"
                                   id="duration"
                                   name="duration"
                                   min="0"
                                   step="0.5"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   value="<?= $course->getDuration() ?>">
                        </div>

                        <!-- Price -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                Price (K)
                            </label>
                            <input type="number"
                                   id="price"
                                   name="price"
                                   min="0"
                                   step="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   value="<?= $course->getPrice() ?>">
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <select id="status"
                                    name="status"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="draft" <?= $course->getStatus() == 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= $course->getStatus() == 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="archived" <?= $course->getStatus() == 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Thumbnail -->
                <div class="border-b pb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Course Thumbnail</h2>

                    <?php if ($course->getThumbnail()): ?>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Current Thumbnail:</p>
                        <img src="<?= courseThumbnail($course->getThumbnail()) ?>"
                             alt="Current thumbnail"
                             class="max-w-md h-48 object-cover rounded-lg border border-gray-300">
                    </div>
                    <?php endif; ?>

                    <div>
                        <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-2">
                            Upload New Thumbnail
                        </label>
                        <input type="file"
                               id="thumbnail"
                               name="thumbnail"
                               accept="image/jpeg,image/png,image/jpg,image/webp"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Leave empty to keep current thumbnail. Max 5MB.</p>

                        <!-- Image Preview -->
                        <div id="image-preview" class="mt-4 hidden">
                            <p class="text-sm text-gray-600 mb-2">New Thumbnail Preview:</p>
                            <img src="" alt="Preview" class="max-w-md h-48 object-cover rounded-lg border border-gray-300">
                        </div>
                    </div>
                </div>

                <!-- Certification Options -->
                <div class="border-b pb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Certification Options</h2>

                    <div class="space-y-4">
                        <!-- Certificate Available -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="certificate_available"
                                       name="certificate_available"
                                       type="checkbox"
                                       <?= $course->hasCertificate() ? 'checked' : '' ?>
                                       class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            </div>
                            <div class="ml-3">
                                <label for="certificate_available" class="font-medium text-gray-700">
                                    Certificate Available
                                </label>
                                <p class="text-sm text-gray-500">Students will receive a certificate upon completion</p>
                            </div>
                        </div>

                        <!-- TEVETA Accredited -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="teveta_accredited"
                                       name="teveta_accredited"
                                       type="checkbox"
                                       <?= $course->isTeveta() ? 'checked' : '' ?>
                                       class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            </div>
                            <div class="ml-3">
                                <label for="teveta_accredited" class="font-medium text-gray-700">
                                    TEVETA Accredited
                                </label>
                                <p class="text-sm text-gray-500">This course is accredited by TEVETA</p>
                            </div>
                        </div>

                        <!-- TEVETA Course Code -->
                        <div id="teveta_code_field" class="ml-7 <?= $course->isTeveta() ? '' : 'hidden' ?>">
                            <label for="teveta_course_code" class="block text-sm font-medium text-gray-700 mb-2">
                                TEVETA Course Code
                            </label>
                            <input type="text"
                                   id="teveta_course_code"
                                   name="teveta_course_code"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   value="<?= htmlspecialchars($course->getTevetaCourseCode()) ?>">
                        </div>
                    </div>
                </div>

                <!-- Course Statistics -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Course Statistics</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Students</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $course->getTotalStudents() ?></p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Lessons</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $course->getTotalLessons() ?></p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Avg Rating</p>
                            <p class="text-2xl font-bold text-gray-900"><?= number_format($course->getAvgRating(), 1) ?> ⭐</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Reviews</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $course->getTotalReviews() ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-lg">
                <a href="<?= url('instructor/courses.php') ?>" class="text-gray-600 hover:text-gray-900">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
            </div>
        </form>

    </div>
</div>

<script>
// Image preview
document.getElementById('thumbnail').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('image-preview');
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
});

// Show/hide TEVETA code field
document.getElementById('teveta_accredited').addEventListener('change', function() {
    const codeField = document.getElementById('teveta_code_field');
    if (this.checked) {
        codeField.classList.remove('hidden');
    } else {
        codeField.classList.add('hidden');
    }
});
</script>

<?php require_once '../../../src/templates/instructor-footer.php'; ?>
