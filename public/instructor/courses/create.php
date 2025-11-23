<?php
/**
 * Instructor - Create New Course
 */

require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Category.php';
require_once '../../../src/classes/FileUpload.php';
require_once '../../../src/classes/Instructor.php';

$db = Database::getInstance();
$userId = currentUserId();

// Get or create instructor record
$instructor = Instructor::getOrCreate($userId);

if (!$instructor) {
    flash('message', 'Unable to verify instructor account. Please contact support.', 'error');
    redirect(url('instructor/index.php'));
}

$instructorId = $instructor->getId();

// Get categories for dropdown
$categories = Category::all(['active_only' => true]);

$errors = [];
$formData = [
    'title' => '',
    'short_description' => '',
    'description' => '',
    'category_id' => '',
    'level' => 'beginner',
    'language' => 'English',
    'price' => 0,
    'duration_hours' => '',
    'prerequisites' => '',
    'learning_outcomes' => '',
    'target_audience' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();

    // Get form data
    $formData = [
        'title' => trim($_POST['title'] ?? ''),
        'short_description' => trim($_POST['short_description'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'category_id' => $_POST['category_id'] ?? null,
        'level' => $_POST['level'] ?? 'beginner',
        'language' => trim($_POST['language'] ?? 'English'),
        'price' => floatval($_POST['price'] ?? 0),
        'duration_hours' => $_POST['duration_hours'] ? (int)$_POST['duration_hours'] : null,
        'prerequisites' => trim($_POST['prerequisites'] ?? ''),
        'learning_outcomes' => trim($_POST['learning_outcomes'] ?? ''),
        'target_audience' => trim($_POST['target_audience'] ?? '')
    ];

    // Validation
    if (empty($formData['title'])) {
        $errors['title'] = 'Course title is required';
    } elseif (strlen($formData['title']) < 5) {
        $errors['title'] = 'Course title must be at least 5 characters';
    }

    if (empty($formData['description'])) {
        $errors['description'] = 'Course description is required';
    }

    if (empty($formData['category_id'])) {
        $errors['category_id'] = 'Please select a category';
    }

    if ($formData['price'] < 0) {
        $errors['price'] = 'Price cannot be negative';
    }

    // Handle thumbnail upload
    $thumbnail = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $upload = new FileUpload($_FILES['thumbnail'], 'courses/thumbnails');
        $upload->setAllowedTypes($allowedTypes);
        $result = $upload->upload();

        if ($result && isset($result['filepath'])) {
            $thumbnail = $result['filepath'];
        } else {
            $errors['thumbnail'] = $upload->getError();
        }
    }

    // Create course if no errors
    if (empty($errors)) {
        $courseData = [
            'instructor_id' => $instructorId,
            'title' => $formData['title'],
            'short_description' => $formData['short_description'],
            'description' => $formData['description'],
            'category_id' => $formData['category_id'],
            'course_level' => $formData['level'],
            'language' => $formData['language'],
            'price' => $formData['price'],
            'duration_hours' => $formData['duration_hours'],
            'thumbnail' => $thumbnail,
            'prerequisites' => $formData['prerequisites'],
            'learning_outcomes' => $formData['learning_outcomes'],
            'target_audience' => $formData['target_audience'],
            'status' => 'draft'
        ];

        $courseId = Course::create($courseData);

        if ($courseId) {
            flash('message', 'Course created successfully! You can now add modules and lessons.', 'success');
            redirect(url('instructor/course-edit.php?id=' . $courseId));
        } else {
            flash('message', 'Failed to create course. Please try again.', 'error');
        }
    }
}

$page_title = 'Create New Course - Instructor';
require_once '../../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Page Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-plus-circle text-primary-600 mr-3"></i>Create New Course
                </h1>
                <p class="text-gray-600 mt-1">Fill in the details to create your course</p>
            </div>
            <a href="<?= url('instructor/courses.php') ?>" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                <i class="fas fa-arrow-left mr-2"></i>Back to Courses
            </a>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <h3 class="text-red-800 font-semibold mb-2">
                <i class="fas fa-exclamation-triangle mr-2"></i>Please fix the following errors:
            </h3>
            <ul class="list-disc list-inside text-red-700">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= csrfField() ?>

            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>Basic Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Course Title -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Course Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" value="<?= htmlspecialchars($formData['title']) ?>" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['title']) ? 'border-red-500' : 'border-gray-300' ?>"
                               placeholder="e.g., Introduction to Web Development">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['category_id']) ? 'border-red-500' : 'border-gray-300' ?>">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->getId() ?>" <?= $formData['category_id'] == $category->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Level -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Course Level</label>
                        <select name="level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="beginner" <?= $formData['level'] == 'beginner' ? 'selected' : '' ?>>Beginner</option>
                            <option value="intermediate" <?= $formData['level'] == 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                            <option value="advanced" <?= $formData['level'] == 'advanced' ? 'selected' : '' ?>>Advanced</option>
                            <option value="all levels" <?= $formData['level'] == 'all levels' ? 'selected' : '' ?>>All Levels</option>
                        </select>
                    </div>

                    <!-- Language -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                        <input type="text" name="language" value="<?= htmlspecialchars($formData['language']) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                               placeholder="English">
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price (ZMW)</label>
                        <input type="number" name="price" value="<?= $formData['price'] ?>" step="0.01" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['price']) ? 'border-red-500' : '' ?>"
                               placeholder="0.00 for free course">
                        <p class="text-xs text-gray-500 mt-1">Enter 0 for a free course</p>
                    </div>

                    <!-- Duration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (hours)</label>
                        <input type="number" name="duration_hours" value="<?= $formData['duration_hours'] ?>" min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                               placeholder="Estimated course duration">
                    </div>

                    <!-- Thumbnail -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Course Thumbnail</label>
                        <input type="file" name="thumbnail" accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['thumbnail']) ? 'border-red-500' : '' ?>">
                        <p class="text-xs text-gray-500 mt-1">Recommended: 1280x720px (JPG, PNG, or WebP)</p>
                    </div>

                    <!-- Short Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                        <textarea name="short_description" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                  placeholder="Brief summary shown in course listings..."><?= htmlspecialchars($formData['short_description']) ?></textarea>
                    </div>

                    <!-- Full Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Course Description <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" rows="6" required
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['description']) ? 'border-red-500' : 'border-gray-300' ?>"
                                  placeholder="Detailed description of what students will learn..."><?= htmlspecialchars($formData['description']) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-list-alt text-green-500 mr-2"></i>Additional Details
                </h2>

                <div class="space-y-4">
                    <!-- Prerequisites -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prerequisites</label>
                        <textarea name="prerequisites" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                  placeholder="What should students know before taking this course?"><?= htmlspecialchars($formData['prerequisites']) ?></textarea>
                    </div>

                    <!-- Learning Outcomes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Learning Outcomes</label>
                        <textarea name="learning_outcomes" rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                  placeholder="What will students be able to do after completing this course?"><?= htmlspecialchars($formData['learning_outcomes']) ?></textarea>
                    </div>

                    <!-- Target Audience -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                        <textarea name="target_audience" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                  placeholder="Who is this course for?"><?= htmlspecialchars($formData['target_audience']) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-4">
                <a href="<?= url('instructor/courses.php') ?>"
                   class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-plus mr-2"></i>Create Course
                </button>
            </div>
        </form>

    </div>
</div>

<?php require_once '../../../src/templates/instructor-footer.php'; ?>
