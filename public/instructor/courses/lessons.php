<?php
/**
 * Lesson Management Page
 * Allows instructors to create and manage lessons within modules
 */

require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Module.php';
require_once '../../../src/classes/Lesson.php';

$user = User::current();
$instructorId = $user->getId();

// Get module ID
if (!isset($_GET['module_id'])) {
    setFlash('Module not found.', 'error');
    redirect('instructor/courses.php');
}

$moduleId = (int)$_GET['module_id'];
$module = Module::find($moduleId);

if (!$module) {
    setFlash('Module not found.', 'error');
    redirect('instructor/courses.php');
}

// Get course and verify ownership
$courseId = $module->getCourseId();
$course = Course::find($courseId);

if (!$course || ($course->getInstructorId() != $instructorId && !hasRole('admin'))) {
    setFlash('You do not have permission to manage this course.', 'error');
    redirect('instructor/courses.php');
}

// Check if editing existing lesson
$lesson = null;
$isEdit = false;
if (isset($_GET['lesson_id'])) {
    $lessonId = (int)$_GET['lesson_id'];
    $lesson = Lesson::find($lessonId);
    if ($lesson && $lesson->getModuleId() == $moduleId) {
        $isEdit = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        setFlash('Invalid security token.', 'error');
        redirect('instructor/courses/lessons.php?module_id=' . $moduleId);
    }

    // Validate required fields
    $errors = [];
    if (empty($_POST['title'])) {
        $errors[] = 'Lesson title is required';
    }
    if (empty($_POST['lesson_type'])) {
        $errors[] = 'Please select a lesson type';
    }

    if (!empty($errors)) {
        setFlash(implode(', ', $errors), 'error');
    } else {
        // Get max display_order
        $maxOrder = $db->fetchOne("SELECT MAX(display_order) as max_order FROM lessons WHERE module_id = ?", [$moduleId]);
        $displayOrder = $isEdit && $lesson ? $lesson->getDisplayOrder() : (($maxOrder['max_order'] ?? 0) + 1);

        // Prepare lesson data
        $lessonData = [
            'module_id' => $moduleId,
            'course_id' => $courseId,
            'title' => sanitize($_POST['title']),
            'description' => sanitize($_POST['description'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'lesson_type' => sanitize($_POST['lesson_type']),
            'video_url' => sanitize($_POST['video_url'] ?? ''),
            'video_duration_seconds' => (int)($_POST['video_duration_seconds'] ?? 0),
            'video_platform' => sanitize($_POST['video_platform'] ?? 'youtube'),
            'display_order' => $displayOrder,
            'duration_minutes' => (int)($_POST['duration_minutes'] ?? 0),
            'is_preview' => isset($_POST['is_preview']) ? 1 : 0,
            'is_mandatory' => isset($_POST['is_mandatory']) ? 1 : 0
        ];

        if ($isEdit && $lesson) {
            // Update existing lesson
            $sql = "UPDATE lessons SET 
                    title = :title, description = :description, content = :content,
                    lesson_type = :lesson_type, video_url = :video_url,
                    video_duration_seconds = :video_duration_seconds, video_platform = :video_platform,
                    duration_minutes = :duration_minutes, is_preview = :is_preview,
                    is_mandatory = :is_mandatory, updated_at = NOW()
                    WHERE id = :id";
            $lessonData['id'] = $lesson->getId();

            if ($db->query($sql, $lessonData)) {
                setFlash('Lesson updated successfully!', 'success');
                redirect('instructor/courses/modules.php?id=' . $courseId);
            } else {
                setFlash('Failed to update lesson.', 'error');
            }
        } else {
            // Create new lesson
            $sql = "INSERT INTO lessons (
                    module_id, course_id, title, description, content, lesson_type,
                    video_url, video_duration_seconds, video_platform, display_order,
                    duration_minutes, is_preview, is_mandatory, created_at, updated_at
                ) VALUES (
                    :module_id, :course_id, :title, :description, :content, :lesson_type,
                    :video_url, :video_duration_seconds, :video_platform, :display_order,
                    :duration_minutes, :is_preview, :is_mandatory, NOW(), NOW()
                )";

            if ($db->query($sql, $lessonData)) {
                setFlash('Lesson created successfully!', 'success');
                redirect('instructor/courses/modules.php?id=' . $courseId);
            } else {
                setFlash('Failed to create lesson.', 'error');
            }
        }
    }
}

$page_title = ($isEdit ? 'Edit' : 'Create') . ' Lesson - ' . $course->getTitle();
require_once '../../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?= $isEdit ? 'Edit' : 'Create New' ?> Lesson</h1>
                    <p class="text-gray-600 mt-2"><?= htmlspecialchars($module->getTitle()) ?></p>
                </div>
                <a href="<?= url('instructor/courses/modules.php?id=' . $courseId) ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Modules
                </a>
            </div>
        </div>

        <!-- Lesson Form -->
        <form method="POST" class="bg-white rounded-lg shadow-md">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="p-6 space-y-6">

                <!-- Basic Information -->
                <div class="border-b pb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Lesson Information</h2>

                    <div class="space-y-4">
                        <!-- Lesson Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Lesson Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="e.g., Introduction to Variables"
                                   value="<?= $isEdit ? htmlspecialchars($lesson->getTitle()) : '' ?>">
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                      placeholder="Brief description of this lesson..."><?= $isEdit ? htmlspecialchars($lesson->getDescription()) : '' ?></textarea>
                        </div>

                        <!-- Lesson Type -->
                        <div>
                            <label for="lesson_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Lesson Type <span class="text-red-500">*</span>
                            </label>
                            <select id="lesson_type"
                                    name="lesson_type"
                                    required
                                    onchange="toggleLessonTypeFields()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">Select Type</option>
                                <option value="video" <?= $isEdit && $lesson->getLessonType() == 'video' ? 'selected' : '' ?>>Video Lesson</option>
                                <option value="text" <?= $isEdit && $lesson->getLessonType() == 'text' ? 'selected' : '' ?>>Text/Article</option>
                                <option value="quiz" <?= $isEdit && $lesson->getLessonType() == 'quiz' ? 'selected' : '' ?>>Quiz (Link to Quiz Builder)</option>
                                <option value="assignment" <?= $isEdit && $lesson->getLessonType() == 'assignment' ? 'selected' : '' ?>>Assignment (Link to Assignment Builder)</option>
                            </select>
                        </div>

                        <!-- Duration -->
                        <div>
                            <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                                Duration (minutes)
                            </label>
                            <input type="number"
                                   id="duration_minutes"
                                   name="duration_minutes"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="15"
                                   value="<?= $isEdit ? $lesson->getDurationMinutes() : '' ?>">
                        </div>
                    </div>
                </div>

                <!-- Video Fields (shown when type is video) -->
                <div id="video-fields" class="border-b pb-6 <?= (!$isEdit || $lesson->getLessonType() != 'video') ? 'hidden' : '' ?>">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Video Details</h2>

                    <div class="space-y-4">
                        <!-- Video Platform -->
                        <div>
                            <label for="video_platform" class="block text-sm font-medium text-gray-700 mb-2">
                                Video Platform
                            </label>
                            <select id="video_platform"
                                    name="video_platform"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="youtube" <?= $isEdit && $lesson->getVideoPlatform() == 'youtube' ? 'selected' : '' ?>>YouTube</option>
                                <option value="vimeo" <?= $isEdit && $lesson->getVideoPlatform() == 'vimeo' ? 'selected' : '' ?>>Vimeo</option>
                            </select>
                        </div>

                        <!-- Video URL -->
                        <div>
                            <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">
                                Video URL / ID
                            </label>
                            <input type="text"
                                   id="video_url"
                                   name="video_url"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="dQw4w9WgXcQ (YouTube ID) or full URL"
                                   value="<?= $isEdit ? htmlspecialchars($lesson->getVideoUrl()) : '' ?>">
                            <p class="text-sm text-gray-500 mt-1">Enter the video ID or full URL</p>
                        </div>

                        <!-- Video Duration -->
                        <div>
                            <label for="video_duration_seconds" class="block text-sm font-medium text-gray-700 mb-2">
                                Video Duration (seconds)
                            </label>
                            <input type="number"
                                   id="video_duration_seconds"
                                   name="video_duration_seconds"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="900"
                                   value="<?= $isEdit ? $lesson->getVideoDurationSeconds() : '' ?>">
                        </div>
                    </div>
                </div>

                <!-- Text Content (shown when type is text) -->
                <div id="text-fields" class="border-b pb-6 <?= (!$isEdit || $lesson->getLessonType() != 'text') ? 'hidden' : '' ?>">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Lesson Content</h2>

                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                            Content (HTML supported)
                        </label>
                        <textarea id="content"
                                  name="content"
                                  rows="15"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent font-mono text-sm"
                                  placeholder="<h3>Lesson Title</h3>&#10;<p>Your lesson content here...</p>"><?= $isEdit ? htmlspecialchars($lesson->getContent()) : '' ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">You can use HTML for formatting</p>
                    </div>
                </div>

                <!-- Quiz/Assignment Info -->
                <div id="quiz-assignment-info" class="border-b pb-6 hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            After saving this lesson, you'll be redirected to create the quiz/assignment content.
                        </p>
                    </div>
                </div>

                <!-- Options -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Lesson Options</h2>

                    <div class="space-y-4">
                        <!-- Free Preview -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_preview"
                                       name="is_preview"
                                       type="checkbox"
                                       <?= $isEdit && $lesson->isPreview() ? 'checked' : '' ?>
                                       class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            </div>
                            <div class="ml-3">
                                <label for="is_preview" class="font-medium text-gray-700">
                                    Free Preview
                                </label>
                                <p class="text-sm text-gray-500">Allow non-enrolled students to preview this lesson</p>
                            </div>
                        </div>

                        <!-- Mandatory -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_mandatory"
                                       name="is_mandatory"
                                       type="checkbox"
                                       <?= $isEdit && $lesson->isMandatory() ? 'checked' : '' ?>
                                       class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            </div>
                            <div class="ml-3">
                                <label for="is_mandatory" class="font-medium text-gray-700">
                                    Mandatory Lesson
                                </label>
                                <p class="text-sm text-gray-500">Students must complete this lesson to progress</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-lg">
                <a href="<?= url('instructor/courses/modules.php?id=' . $courseId) ?>" class="text-gray-600 hover:text-gray-900">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                    <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Update' : 'Create' ?> Lesson
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function toggleLessonTypeFields() {
    const lessonType = document.getElementById('lesson_type').value;
    const videoFields = document.getElementById('video-fields');
    const textFields = document.getElementById('text-fields');
    const quizAssignmentInfo = document.getElementById('quiz-assignment-info');

    // Hide all
    videoFields.classList.add('hidden');
    textFields.classList.add('hidden');
    quizAssignmentInfo.classList.add('hidden');

    // Show relevant fields
    if (lessonType === 'video') {
        videoFields.classList.remove('hidden');
    } else if (lessonType === 'text') {
        textFields.classList.remove('hidden');
    } else if (lessonType === 'quiz' || lessonType === 'assignment') {
        quizAssignmentInfo.classList.remove('hidden');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleLessonTypeFields();
});
</script>

<?php require_once '../../../src/templates/instructor-footer.php'; ?>
