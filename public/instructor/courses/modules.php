<?php
/**
 * Instructor - Course Modules & Lessons Management
 * Allows instructors to create and manage course structure
 */

require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Module.php';
require_once '../../../src/classes/Lesson.php';
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
$instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
$instructorId = $instructorRecord ? $instructorRecord['id'] : null;

$canEdit = hasRole('admin') ||
           ($instructorId && $course->getInstructorId() == $instructorId) ||
           ($course->getInstructorId() == $userId);

if (!$canEdit) {
    flash('message', 'You do not have permission to edit this course', 'error');
    redirect(url('instructor/courses.php'));
}

// Get modules and lessons
$modules = Module::getByCourse($courseId);

$page_title = 'Manage Course Content - ' . $course->getTitle();
require_once '../../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Page Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-list text-primary-600 mr-3"></i>Course Content
                </h1>
                <p class="text-gray-600 mt-1"><?= htmlspecialchars($course->getTitle()) ?></p>
            </div>
            <div class="flex space-x-3">
                <a href="<?= url('instructor/course-edit.php?id=' . $courseId) ?>"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Course
                </a>
                <button onclick="showAddModuleModal()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-plus mr-2"></i>Add Module
                </button>
            </div>
        </div>

        <!-- Course Structure -->
        <?php if (empty($modules)): ?>
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-folder-open text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Modules Yet</h3>
            <p class="text-gray-600 mb-6">Start building your course by adding your first module</p>
            <button onclick="showAddModuleModal()" class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                <i class="fas fa-plus mr-2"></i>Add Your First Module
            </button>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($modules as $index => $module):
                $lessons = Lesson::getByModule($module['id']);
            ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Module Header -->
                <div class="bg-gray-50 px-6 py-4 border-b flex items-center justify-between">
                    <div class="flex items-center space-x-4 flex-1">
                        <div class="flex items-center text-gray-400">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Module <?= $index + 1 ?>: <?= htmlspecialchars($module['title']) ?>
                            </h3>
                            <?php if ($module['description']): ?>
                            <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($module['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="showEditModuleModal(<?= $module['id'] ?>, '<?= htmlspecialchars($module['title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($module['description'] ?? '', ENT_QUOTES) ?>')"
                                class="px-3 py-1 text-sm bg-blue-50 text-blue-600 rounded hover:bg-blue-100">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <button onclick="showAddLessonModal(<?= $module['id'] ?>)"
                                class="px-3 py-1 text-sm bg-green-50 text-green-600 rounded hover:bg-green-100">
                            <i class="fas fa-plus mr-1"></i>Add Lesson
                        </button>
                        <button onclick="deleteModule(<?= $module['id'] ?>)"
                                class="px-3 py-1 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </div>
                </div>

                <!-- Lessons List -->
                <?php if (empty($lessons)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-file-alt text-3xl mb-2"></i>
                    <p class="text-sm">No lessons in this module yet</p>
                    <button onclick="showAddLessonModal(<?= $module['id'] ?>)"
                            class="mt-3 text-sm text-primary-600 hover:text-primary-700 font-medium">
                        <i class="fas fa-plus mr-1"></i>Add First Lesson
                    </button>
                </div>
                <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($lessons as $lessonIndex => $lesson): ?>
                    <div class="px-6 py-4 hover:bg-gray-50 flex items-center justify-between">
                        <div class="flex items-center space-x-4 flex-1">
                            <div class="text-gray-400">
                                <i class="fas fa-grip-vertical"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <h4 class="font-medium text-gray-900">
                                        <?= $lessonIndex + 1 ?>. <?= htmlspecialchars($lesson['title']) ?>
                                    </h4>
                                    <?php if ($lesson['is_preview']): ?>
                                    <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">Free Preview</span>
                                    <?php endif; ?>
                                    <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded-full">
                                        <i class="fas fa-<?= $lesson['lesson_type'] == 'video' ? 'video' : 'file-alt' ?> mr-1"></i>
                                        <?= ucfirst($lesson['lesson_type']) ?>
                                    </span>
                                </div>
                                <?php if ($lesson['duration']): ?>
                                <p class="text-sm text-gray-500 mt-1">
                                    <i class="far fa-clock mr-1"></i><?= $lesson['duration'] ?> min
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="showEditLessonModal(<?= htmlspecialchars(json_encode($lesson)) ?>)"
                                    class="px-3 py-1 text-sm bg-blue-50 text-blue-600 rounded hover:bg-blue-100">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteLesson(<?= $lesson['id'] ?>)"
                                    class="px-3 py-1 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100">
                                <i class="fas fa-trash"></i>
                            </button>
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
<div id="addModuleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Add New Module</h3>
            <button onclick="closeModal('addModuleModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="<?= url('actions/instructor/module-create.php') ?>" method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="course_id" value="<?= $courseId ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Module Title *</label>
                    <input type="text" name="title" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                           placeholder="e.g., Introduction to Programming">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                              placeholder="Brief description of what this module covers"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('addModuleModal')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-plus mr-2"></i>Add Module
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Module Modal -->
<div id="editModuleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Edit Module</h3>
            <button onclick="closeModal('editModuleModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="<?= url('actions/instructor/module-update.php') ?>" method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="module_id" id="edit_module_id">
            <input type="hidden" name="course_id" value="<?= $courseId ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Module Title *</label>
                    <input type="text" name="title" id="edit_module_title" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="edit_module_description" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editModuleModal')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Lesson Modal -->
<div id="addLessonModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
    <div class="bg-white rounded-lg max-w-3xl w-full p-6 my-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Add New Lesson</h3>
            <button onclick="closeModal('addLessonModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="<?= url('actions/instructor/lesson-create.php') ?>" method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="module_id" id="add_lesson_module_id">
            <input type="hidden" name="course_id" value="<?= $courseId ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Title *</label>
                    <input type="text" name="title" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                           placeholder="e.g., Introduction to Variables">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Type *</label>
                        <select name="lesson_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="video">Video</option>
                            <option value="text">Text/Article</option>
                            <option value="mixed">Mixed (Video + Text)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                        <input type="number" name="duration" min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Video URL</label>
                    <input type="url" name="video_url"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                           placeholder="https://youtube.com/watch?v=...">
                    <p class="text-xs text-gray-500 mt-1">YouTube, Vimeo, or direct video URL</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                              placeholder="Brief description of this lesson"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Content</label>
                    <textarea name="content" rows="6"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                              placeholder="Full lesson content, notes, code examples, etc."></textarea>
                </div>

                <div class="flex items-center space-x-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_preview" value="1" class="rounded text-primary-600 mr-2">
                        <span class="text-sm text-gray-700">Free Preview (visible to non-enrolled users)</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('addLessonModal')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-plus mr-2"></i>Add Lesson
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Lesson Modal -->
<div id="editLessonModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
    <div class="bg-white rounded-lg max-w-3xl w-full p-6 my-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Edit Lesson</h3>
            <button onclick="closeModal('editLessonModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="<?= url('actions/instructor/lesson-update.php') ?>" method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="lesson_id" id="edit_lesson_id">
            <input type="hidden" name="course_id" value="<?= $courseId ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Title *</label>
                    <input type="text" name="title" id="edit_lesson_title" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Type *</label>
                        <select name="lesson_type" id="edit_lesson_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="video">Video</option>
                            <option value="text">Text/Article</option>
                            <option value="mixed">Mixed (Video + Text)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                        <input type="number" name="duration" id="edit_lesson_duration" min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Video URL</label>
                    <input type="url" name="video_url" id="edit_lesson_video_url"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="edit_lesson_description" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Content</label>
                    <textarea name="content" id="edit_lesson_content" rows="6"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
                </div>

                <div class="flex items-center space-x-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_preview" id="edit_lesson_is_preview" value="1" class="rounded text-primary-600 mr-2">
                        <span class="text-sm text-gray-700">Free Preview</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editLessonModal')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModuleModal() {
    document.getElementById('addModuleModal').classList.remove('hidden');
}

function showEditModuleModal(id, title, description) {
    document.getElementById('edit_module_id').value = id;
    document.getElementById('edit_module_title').value = title;
    document.getElementById('edit_module_description').value = description;
    document.getElementById('editModuleModal').classList.remove('hidden');
}

function showAddLessonModal(moduleId) {
    document.getElementById('add_lesson_module_id').value = moduleId;
    document.getElementById('addLessonModal').classList.remove('hidden');
}

function showEditLessonModal(lesson) {
    document.getElementById('edit_lesson_id').value = lesson.id;
    document.getElementById('edit_lesson_title').value = lesson.title;
    document.getElementById('edit_lesson_type').value = lesson.lesson_type || 'video';
    document.getElementById('edit_lesson_duration').value = lesson.duration || '';
    document.getElementById('edit_lesson_video_url').value = lesson.video_url || '';
    document.getElementById('edit_lesson_description').value = lesson.description || '';
    document.getElementById('edit_lesson_content').value = lesson.content || '';
    document.getElementById('edit_lesson_is_preview').checked = lesson.is_preview == 1;
    document.getElementById('editLessonModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function deleteModule(moduleId) {
    if (!confirm('Are you sure you want to delete this module? All lessons in this module will also be deleted.')) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= url('actions/instructor/module-delete.php') ?>';

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    form.appendChild(csrfInput);

    const moduleInput = document.createElement('input');
    moduleInput.type = 'hidden';
    moduleInput.name = 'module_id';
    moduleInput.value = moduleId;
    form.appendChild(moduleInput);

    const courseInput = document.createElement('input');
    courseInput.type = 'hidden';
    courseInput.name = 'course_id';
    courseInput.value = '<?= $courseId ?>';
    form.appendChild(courseInput);

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

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    form.appendChild(csrfInput);

    const lessonInput = document.createElement('input');
    lessonInput.type = 'hidden';
    lessonInput.name = 'lesson_id';
    lessonInput.value = lessonId;
    form.appendChild(lessonInput);

    const courseInput = document.createElement('input');
    courseInput.type = 'hidden';
    courseInput.name = 'course_id';
    courseInput.value = '<?= $courseId ?>';
    form.appendChild(courseInput);

    document.body.appendChild(form);
    form.submit();
}

// Close modal on outside click
window.onclick = function(event) {
    const modals = ['addModuleModal', 'editModuleModal', 'addLessonModal', 'editLessonModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
}
</script>

<?php require_once '../../../src/templates/instructor-footer.php'; ?>
