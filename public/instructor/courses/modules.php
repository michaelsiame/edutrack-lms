<?php
/**
 * Instructor - Course Modules & Lessons Management
 * Modern course content builder
 */

require_once '../../../src/bootstrap.php';
require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Module.php';
require_once '../../../src/classes/Lesson.php';
require_once '../../../src/classes/LessonResource.php';
require_once '../../../src/classes/Instructor.php';

$db = Database::getInstance();
$userId = currentUserId();

// Get course ID
$courseId = $_GET['id'] ?? null;

if (!$courseId) {
    flash('message', 'Course ID is required', 'error');
    redirect(url('instructor/courses.php'));
}

// Get course
$course = Course::find($courseId);

if (!$course) {
    flash('message', 'Course not found', 'error');
    redirect(url('instructor/courses.php'));
}

// Verify ownership
$instructor = Instructor::getOrCreate($userId);
$instructorId = $instructor->getId();

$courseInstructorId = $course->getInstructorId();
$isAssignedViaTable = $db->fetchOne("
    SELECT 1 FROM course_instructors
    WHERE course_id = ? AND instructor_id = ?
", [$courseId, $instructorId]);

$canEdit = hasRole('admin') ||
           ($courseInstructorId == $instructorId) ||
           ($courseInstructorId == $userId) ||
           ($isAssignedViaTable !== null);

if (!$canEdit) {
    flash('message', 'You do not have permission to edit this course', 'error');
    redirect(url('instructor/courses.php'));
}

// Get modules and lessons
$modules = Module::getByCourse($courseId);

// Get course stats
$courseStats = $db->fetchOne("
    SELECT 
        COUNT(DISTINCT m.id) as module_count,
        COUNT(DISTINCT l.id) as lesson_count,
        COUNT(DISTINCT e.id) as enrollment_count
    FROM courses c
    LEFT JOIN modules m ON c.id = m.course_id
    LEFT JOIN lessons l ON m.id = l.module_id
    LEFT JOIN enrollments e ON c.id = e.course_id
    WHERE c.id = ?
", [$courseId]);

$page_title = 'Course Content - ' . $course->getTitle();
require_once '../../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Breadcrumb & Header -->
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                <a href="<?= url('instructor/courses.php') ?>" class="hover:text-primary-600">Courses</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="<?= url('instructor/course-edit.php?id=' . $courseId) ?>" class="hover:text-primary-600"><?= htmlspecialchars($course->getTitle()) ?></a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900 font-medium">Content</span>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-primary-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-sitemap text-primary-600 text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Course Content</h1>
                        <p class="text-gray-500"><?= htmlspecialchars($course->getTitle()) ?></p>
                    </div>
                </div>
                <div class="mt-4 md:mt-0 flex items-center gap-3">
                    <a href="<?= url('course.php?slug=' . $course->getSlug()) ?>" 
                       target="_blank"
                       class="px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition">
                        <i class="fas fa-eye mr-2"></i>Preview
                    </a>
                    <button onclick="openModal('addModuleModal')" 
                            class="px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-500/30">
                        <i class="fas fa-plus mr-2"></i>Add Module
                    </button>
                </div>
            </div>
        </div>

        <!-- Course Stats -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl p-4 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Modules</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $courseStats['module_count'] ?? 0 ?></p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-folder text-blue-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Lessons</p>
                        <p class="text-2xl font-bold text-purple-600"><?= $courseStats['lesson_count'] ?? 0 ?></p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-play-circle text-purple-500"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-card border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Enrollments</p>
                        <p class="text-2xl font-bold text-green-600"><?= $courseStats['enrollment_count'] ?? 0 ?></p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-green-500"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Structure -->
        <?php if (empty($modules)): ?>
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-folder-open text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Modules Yet</h3>
            <p class="text-gray-500 mb-6">Start building your course by adding your first module</p>
            <button onclick="openModal('addModuleModal')" 
                    class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-500/30">
                <i class="fas fa-plus mr-2"></i>Add Your First Module
            </button>
        </div>
        <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($modules as $index => $module):
                $lessons = Lesson::getByModule($module['id']);
            ?>
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                <!-- Module Header -->
                <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                                <span class="font-bold text-primary-600"><?= $index + 1 ?></span>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900">
                                    <?= htmlspecialchars($module['title']) ?>
                                </h3>
                                <?php if ($module['description']): ?>
                                <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($module['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="showEditModuleModal(<?= $module['id'] ?>, '<?= htmlspecialchars($module['title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($module['description'] ?? '', ENT_QUOTES) ?>')"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Module">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="showAddLessonModal(<?= $module['id'] ?>)"
                                    class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="Add Lesson">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button onclick="deleteModule(<?= $module['id'] ?>)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete Module">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lessons List -->
                <?php if (empty($lessons)): ?>
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-file-alt text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-gray-500 text-sm">No lessons in this module yet</p>
                    <button onclick="showAddLessonModal(<?= $module['id'] ?>)"
                            class="mt-3 text-sm text-primary-600 hover:text-primary-700 font-medium">
                        <i class="fas fa-plus mr-1"></i>Add First Lesson
                    </button>
                </div>
                <?php else: ?>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($lessons as $lessonIndex => $lesson): 
                        $typeIcons = [
                            'Video' => 'fa-play-circle text-red-500',
                            'Reading' => 'fa-file-alt text-blue-500',
                            'Quiz' => 'fa-question-circle text-purple-500',
                            'Assignment' => 'fa-tasks text-orange-500',
                            'Live Session' => 'fa-video text-green-500',
                            'Download' => 'fa-download text-indigo-500'
                        ];
                        $typeIcon = $typeIcons[$lesson['lesson_type']] ?? $typeIcons['text'];
                        
                        $lessonResources = [];
                        try {
                            $lessonResources = LessonResource::getByLesson($lesson['id']);
                        } catch (Exception $e) {}
                    ?>
                    <div class="px-6 py-4 hover:bg-gray-50/50 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas <?= $typeIcon ?> text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-medium text-gray-900">
                                            <?= $lessonIndex + 1 ?>. <?= htmlspecialchars($lesson['title']) ?>
                                        </h4>
                                        <?php if ($lesson['is_preview']): ?>
                                        <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-lg font-medium">Free Preview</span>
                                        <?php endif; ?>
                                        <?php if (!empty($lessonResources)): ?>
                                        <span class="px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded-lg font-medium">
                                            <i class="fas fa-download mr-1"></i><?= count($lessonResources) ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($lesson['duration']): ?>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="far fa-clock mr-1"></i><?= $lesson['duration'] ?> min
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="<?= url('instructor/courses/lesson-resources.php?lesson_id=' . $lesson['id']) ?>"
                                   class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition" title="Resources">
                                    <i class="fas fa-paperclip"></i>
                                </a>
                                <button onclick="showEditLessonModal(<?= htmlspecialchars(json_encode($lesson)) ?>)"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteLesson(<?= $lesson['id'] ?>)"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Add Module Modal -->
<div id="addModuleModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Add New Module</h3>
            <button onclick="closeModal('addModuleModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="<?= url('actions/instructor/module-create.php') ?>" method="POST" class="p-6 space-y-5">
            <?= csrfField() ?>
            <input type="hidden" name="course_id" value="<?= $courseId ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Module Title *</label>
                <input type="text" name="title" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"
                       placeholder="e.g., Introduction to Programming">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"
                          placeholder="Brief description of what this module covers"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('addModuleModal')"
                        class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition">
                    <i class="fas fa-plus mr-2"></i>Add Module
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Module Modal -->
<div id="editModuleModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Edit Module</h3>
            <button onclick="closeModal('editModuleModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="<?= url('actions/instructor/module-update.php') ?>" method="POST" class="p-6 space-y-5">
            <?= csrfField() ?>
            <input type="hidden" name="module_id" id="edit_module_id">
            <input type="hidden" name="course_id" value="<?= $courseId ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Module Title *</label>
                <input type="text" name="title" id="edit_module_title" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="edit_module_description" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('editModuleModal')"
                        class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Lesson Modal -->
<div id="addLessonModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
            <h3 class="text-lg font-bold text-gray-900">Add New Lesson</h3>
            <button onclick="closeModal('addLessonModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="<?= url('actions/instructor/lesson-create.php') ?>" method="POST" class="p-6 space-y-5">
            <?= csrfField() ?>
            <input type="hidden" name="module_id" id="add_lesson_module_id">
            <input type="hidden" name="course_id" value="<?= $courseId ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Title *</label>
                <input type="text" name="title" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"
                       placeholder="e.g., Introduction to Variables">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Type *</label>
                    <select name="lesson_type" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                        <option value="Video">Video</option>
                        <option value="Reading">Text/Article</option>
                        <option value="Quiz">Quiz</option>
                        <option value="Assignment">Assignment</option>
                        <option value="Download">Downloadable Resource</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                    <input type="number" name="duration" min="1"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Video URL</label>
                <input type="url" name="video_url"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"
                       placeholder="https://youtube.com/watch?v=...">
                <p class="text-xs text-gray-500 mt-1">YouTube, Vimeo, or direct video URL</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"
                          placeholder="Brief description of this lesson"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Content</label>
                <textarea name="content" rows="6"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"
                          placeholder="Full lesson content, notes, code examples, etc."></textarea>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_preview" value="1" id="add_is_preview" 
                       class="w-4 h-4 text-primary-600 rounded border-gray-300">
                <label for="add_is_preview" class="ml-2 text-sm text-gray-700">
                    Free Preview (visible to non-enrolled users)
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('addLessonModal')"
                        class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium transition">
                    <i class="fas fa-plus mr-2"></i>Add Lesson
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Lesson Modal -->
<div id="editLessonModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 modal-container modal-overlay">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
            <h3 class="text-lg font-bold text-gray-900">Edit Lesson</h3>
            <button onclick="closeModal('editLessonModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="<?= url('actions/instructor/lesson-update.php') ?>" method="POST" class="p-6 space-y-5">
            <?= csrfField() ?>
            <input type="hidden" name="lesson_id" id="edit_lesson_id">
            <input type="hidden" name="course_id" value="<?= $courseId ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Title *</label>
                <input type="text" name="title" id="edit_lesson_title" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Type *</label>
                    <select name="lesson_type" id="edit_lesson_type" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                        <option value="Video">Video</option>
                        <option value="Reading">Text/Article</option>
                        <option value="Quiz">Quiz</option>
                        <option value="Assignment">Assignment</option>
                        <option value="Download">Downloadable Resource</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                    <input type="number" name="duration" id="edit_lesson_duration" min="1"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Video URL</label>
                <input type="url" name="video_url" id="edit_lesson_video_url"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="edit_lesson_description" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Content</label>
                <textarea name="content" id="edit_lesson_content" rows="6"
                          class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500"></textarea>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_preview" id="edit_lesson_is_preview" value="1" 
                       class="w-4 h-4 text-primary-600 rounded border-gray-300">
                <label for="edit_lesson_is_preview" class="ml-2 text-sm text-gray-700">Free Preview</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('editLessonModal')"
                        class="flex-1 px-4 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModuleModal() {
    openModal('addModuleModal');
}

function showEditModuleModal(id, title, description) {
    document.getElementById('edit_module_id').value = id;
    document.getElementById('edit_module_title').value = title;
    document.getElementById('edit_module_description').value = description;
    openModal('editModuleModal');
}

function showAddLessonModal(moduleId) {
    document.getElementById('add_lesson_module_id').value = moduleId;
    openModal('addLessonModal');
}

function showEditLessonModal(lesson) {
    document.getElementById('edit_lesson_id').value = lesson.id;
    document.getElementById('edit_lesson_title').value = lesson.title;
    document.getElementById('edit_lesson_type').value = lesson.lesson_type || 'Video';
    document.getElementById('edit_lesson_duration').value = lesson.duration || '';
    document.getElementById('edit_lesson_video_url').value = lesson.video_url || '';
    document.getElementById('edit_lesson_description').value = lesson.description || '';
    document.getElementById('edit_lesson_content').value = lesson.content || '';
    document.getElementById('edit_lesson_is_preview').checked = lesson.is_preview == 1;
    openModal('editLessonModal');
}

function deleteModule(moduleId) {
    if (!confirm('Are you sure you want to delete this module? All lessons in this module will also be deleted.')) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= url('actions/instructor/module-delete.php') ?>';

    form.innerHTML = `
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="module_id" value="${moduleId}">
        <input type="hidden" name="course_id" value="<?= $courseId ?>">
    `;

    document.body.appendChild(form);
    form.submit();
}

function deleteLesson(lessonId) {
    if (!confirm('Are you sure you want to delete this lesson?')) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= url('actions/instructor/lesson-delete.php') ?>';

    form.innerHTML = `
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="lesson_id" value="${lessonId}">
        <input type="hidden" name="course_id" value="<?= $courseId ?>">
    `;

    document.body.appendChild(form);
    form.submit();
}
</script>

<?php require_once '../../../src/templates/instructor-footer.php'; ?>
