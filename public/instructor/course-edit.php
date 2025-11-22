<?php
/**
 * Instructor Course Edit
 * Allow instructors to edit their own courses
 */

// Debug initialization
$DEBUG_MODE = defined('DEBUG_MODE') ? $DEBUG_MODE : ($_ENV['DEBUG_MODE'] ?? false);
$page_start_time = microtime(true);
$page_start_memory = memory_get_usage();
$debug_data = [
    'page' => 'instructor/course-edit.php',
    'timestamp' => date('Y-m-d H:i:s'),
    'queries' => [],
    'errors' => []
];

// Error handler for debugging
if ($DEBUG_MODE) {
    set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$debug_data) {
        $debug_data['errors'][] = [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ];
        return false;
    });
}

require_once '../../src/middleware/instructor-only.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Category.php';
require_once '../../src/classes/FileUpload.php';

// Debug: Log user info
if ($DEBUG_MODE) {
    $debug_data['user'] = [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role' => $_SESSION['user_role'] ?? null
    ];
}

$courseId = $_GET['id'] ?? null;

// Debug: Log course ID being edited
if ($DEBUG_MODE) {
    $debug_data['course_id'] = $courseId;
}

if (!$courseId) {
    redirect(url('instructor/courses.php'));
}

$course = Course::find($courseId);

if (!$course) {
    flash('message', 'Course not found', 'error');
    redirect(url('instructor/courses.php'));
}

// Security: Ensure instructor can only edit their own courses
if ($course->getInstructorId() != currentUserId()) {
    flash('message', 'You do not have permission to edit this course', 'error');
    redirect(url('instructor/courses.php'));
}

// Get categories
$categories = Category::all(['active_only' => true]);

// Debug: Log course and categories data
if ($DEBUG_MODE) {
    $debug_data['data'] = [
        'course_title' => $course->getTitle(),
        'course_status' => $course->getStatus(),
        'categories_count' => count($categories),
        'instructor_id' => $course->getInstructorId()
    ];
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validateCSRF();

    $title = trim($_POST['title'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $level = $_POST['level'] ?? 'beginner';
    $language = $_POST['language'] ?? 'English';
    $price = floatval($_POST['price'] ?? 0);
    $duration_hours = $_POST['duration_hours'] ?? null;
    $status = $_POST['status'] ?? 'draft';
    $video_url = trim($_POST['video_url'] ?? '');
    $prerequisites = trim($_POST['prerequisites'] ?? '');
    $learning_outcomes = trim($_POST['learning_outcomes'] ?? '');
    $target_audience = trim($_POST['target_audience'] ?? '');

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
            'course_level' => $level,
            'language' => $language,
            'price' => $price,
            'duration_hours' => $duration_hours,
            'status' => $status,
            'promo_video_url' => $video_url,
            'prerequisites' => $prerequisites,
            'learning_outcomes' => $learning_outcomes,
            'target_audience' => $target_audience
        ];

        if ($course->update($courseData)) {
            flash('message', 'Course updated successfully', 'success');
            redirect('course-edit.php?id=' . $courseId);
        } else {
            flash('message', 'Failed to update course', 'error');
        }
    }
}

$page_title = 'Edit Course - ' . $course->getTitle();
require_once '../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Course</h1>
                <p class="text-gray-600 mt-1">Update your course information</p>
            </div>
            <a href="<?= url('instructor/courses.php') ?>" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                <i class="fas fa-arrow-left mr-2"></i>Back to Courses
            </a>
        </div>

        <!-- Main Content -->

            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h3 class="text-red-800 font-semibold mb-2">Please fix the following errors:</h3>
                <ul class="list-disc list-inside text-red-700">
                    <?php foreach ($errors as $error): ?>
                        <li><?= sanitize($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <?= csrfField() ?>

                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Basic Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Course Title -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Course Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" value="<?= sanitize($course->getTitle()) ?>" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 <?= isset($errors['title']) ? 'border-red-500' : 'border-gray-300' ?>">
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
                                    <option value="<?= $category->getId() ?>" <?= $category->getId() == $course->getCategoryId() ? 'selected' : '' ?>>
                                        <?= sanitize($category->getName()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Level -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Course Level</label>
                            <select name="level" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="beginner" <?= $course->getLevel() == 'beginner' ? 'selected' : '' ?>>Beginner</option>
                                <option value="intermediate" <?= $course->getLevel() == 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                <option value="advanced" <?= $course->getLevel() == 'advanced' ? 'selected' : '' ?>>Advanced</option>
                                <option value="all levels" <?= $course->getLevel() == 'all levels' ? 'selected' : '' ?>>All Levels</option>
                            </select>
                        </div>

                        <!-- Language -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                            <input type="text" name="language" value="<?= sanitize($course->getLanguage()) ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>

                        <!-- Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price (ZMW)</label>
                            <input type="number" name="price" value="<?= $course->getPrice() ?>" step="0.01" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg <?= isset($errors['price']) ? 'border-red-500' : '' ?>">
                        </div>

                        <!-- Duration -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duration (hours)</label>
                            <input type="number" name="duration_hours" value="<?= $course->getDurationHours() ?>" min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="draft" <?= $course->getStatus() == 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= $course->getStatus() == 'published' ? 'selected' : '' ?>>Published</option>
                            </select>
                        </div>

                        <!-- Short Description -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                            <textarea name="short_description" rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg"><?= sanitize($course->getShortDescription()) ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">Brief summary displayed in course listings</p>
                        </div>

                        <!-- Full Description -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Course Description <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description" rows="6" required
                                      class="w-full px-4 py-2 border rounded-lg <?= isset($errors['description']) ? 'border-red-500' : 'border-gray-300' ?>"><?= sanitize($course->getDescription()) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Course Media</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Thumbnail -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Course Thumbnail</label>
                            <?php if ($course->getThumbnail()): ?>
                                <div class="mb-3">
                                    <img src="<?= url('uploads/' . $course->getThumbnail()) ?>" alt="Current thumbnail"
                                         class="h-32 w-48 object-cover rounded-lg">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="thumbnail" accept="image/*"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Recommended size: 1280x720px (JPG, PNG, or WebP)</p>
                        </div>

                        <!-- Promo Video -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Promo Video URL</label>
                            <input type="url" name="video_url" value="<?= sanitize($course->getPromoVideoUrl()) ?>"
                                   placeholder="https://youtube.com/watch?v=..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">YouTube or Vimeo URL</p>
                        </div>
                    </div>
                </div>

                <!-- Additional Details -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Additional Details</h2>

                    <div class="space-y-4">
                        <!-- Prerequisites -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prerequisites</label>
                            <textarea name="prerequisites" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                      placeholder="What students should know before taking this course..."><?= sanitize($course->getPrerequisites()) ?></textarea>
                        </div>

                        <!-- Learning Outcomes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Learning Outcomes</label>
                            <textarea name="learning_outcomes" rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                      placeholder="What will students learn in this course..."><?= sanitize($course->getLearningOutcomes()) ?></textarea>
                        </div>

                        <!-- Target Audience -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                            <textarea name="target_audience" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                      placeholder="Who is this course for..."><?= sanitize($course->getTargetAudience()) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="<?= url('instructor/courses.php') ?>"
                       class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>

            <!-- Course Modules & Lessons Management -->
            <div class="mt-8 bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Course Content</h2>
                    <a href="<?= url('instructor/courses/modules.php?id=' . $courseId) ?>"
                       class="px-4 py-2 bg-secondary-600 text-white rounded-lg hover:bg-secondary-700">
                        <i class="fas fa-book mr-2"></i>Manage Modules & Lessons
                    </a>
                </div>
                <p class="text-gray-600">
                    Use the course content manager to add and organize modules, lessons, quizzes, and assignments.
                </p>
            </div>

    </div>
</div>

<?php
// Debug panel output
if ($DEBUG_MODE) {
    $debug_data['performance'] = [
        'execution_time' => round((microtime(true) - $page_start_time) * 1000, 2) . 'ms',
        'memory_used' => round((memory_get_usage() - $page_start_memory) / 1024, 2) . 'KB',
        'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB'
    ];
    $debug_data['form_errors'] = $errors;
?>
<!-- Debug Panel -->
<div id="debug-panel" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white text-xs z-50 max-h-96 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-between p-2 bg-gray-800 sticky top-0">
        <span class="font-bold"><i class="fas fa-bug mr-2"></i>Debug Panel - <?= $debug_data['page'] ?></span>
        <div class="flex items-center space-x-4">
            <span class="text-green-400">Time: <?= $debug_data['performance']['execution_time'] ?></span>
            <span class="text-blue-400">Memory: <?= $debug_data['performance']['memory_used'] ?></span>
            <button onclick="document.getElementById('debug-panel').style.display='none'" class="text-red-400 hover:text-red-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <h4 class="font-bold text-yellow-400 mb-2">User Info</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto"><?= json_encode($debug_data['user'] ?? [], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <div>
            <h4 class="font-bold text-yellow-400 mb-2">Course Data</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto"><?= json_encode($debug_data['data'] ?? [], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <?php if (!empty($debug_data['form_errors'])): ?>
        <div>
            <h4 class="font-bold text-orange-400 mb-2">Form Errors (<?= count($debug_data['form_errors']) ?>)</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto text-orange-300"><?= json_encode($debug_data['form_errors'], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <?php endif; ?>
        <?php if (!empty($debug_data['errors'])): ?>
        <div>
            <h4 class="font-bold text-red-400 mb-2">PHP Errors (<?= count($debug_data['errors']) ?>)</h4>
            <pre class="bg-gray-800 p-2 rounded overflow-x-auto text-red-300"><?= json_encode($debug_data['errors'], JSON_PRETTY_PRINT) ?></pre>
        </div>
        <?php endif; ?>
    </div>
</div>
<button onclick="document.getElementById('debug-panel').style.display = document.getElementById('debug-panel').style.display === 'none' ? 'block' : 'none'"
        class="fixed bottom-4 right-4 bg-gray-900 text-white p-3 rounded-full shadow-lg hover:bg-gray-700 z-50" title="Toggle Debug Panel">
    <i class="fas fa-bug"></i>
</button>
<?php } ?>

<?php require_once '../../src/templates/instructor-footer.php'; ?>
