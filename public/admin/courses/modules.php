<?php
/**
 * Admin Course Modules & Lessons Management
 */

require_once '../../../src/middleware/admin-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Module.php';
require_once '../../../src/classes/Lesson.php';

$courseId = $_GET['id'] ?? null;

if (!$courseId) {
    redirect(url('admin/courses/index.php'));
}

$course = Course::find($courseId);

if (!$course) {
    flash('message', 'Course not found', 'error');
    redirect(url('admin/courses/index.php'));
}

// Get modules with lessons
$modules = Module::getByCourse($courseId);
foreach ($modules as &$module) {
    $module['lessons'] = Lesson::getByModule($module['id']);
}

// Handle module creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_module') {
    validateCSRF();
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (!empty($title)) {
        $moduleData = [
            'course_id' => $courseId,
            'title' => $title,
            'description' => $description,
            'order_index' => count($modules)
        ];
        
        if (Module::create($moduleData)) {
            flash('message', 'Module created successfully!', 'success');
        } else {
            flash('message', 'Failed to create module', 'error');
        }
    }
    
    redirect($_SERVER['REQUEST_URI']);
}

// Handle module update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_module') {
    validateCSRF();
    
    $moduleId = $_POST['module_id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if ($moduleId && !empty($title)) {
        $module = Module::find($moduleId);
        if ($module && $module->update(['title' => $title, 'description' => $description])) {
            flash('message', 'Module updated successfully!', 'success');
        } else {
            flash('message', 'Failed to update module', 'error');
        }
    }
    
    redirect($_SERVER['REQUEST_URI']);
}

// Handle module delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_module') {
    validateCSRF();
    
    $moduleId = $_POST['module_id'] ?? null;
    
    if ($moduleId) {
        $module = Module::find($moduleId);
        if ($module && $module->delete()) {
            flash('message', 'Module deleted successfully!', 'success');
        } else {
            flash('message', 'Failed to delete module', 'error');
        }
    }
    
    redirect($_SERVER['REQUEST_URI']);
}

// Handle lesson creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_lesson') {
    validateCSRF();
    
    $moduleId = $_POST['module_id'] ?? null;
    $title = trim($_POST['lesson_title'] ?? '');
    $lessonType = $_POST['lesson_type'] ?? 'video';
    $videoUrl = trim($_POST['video_url'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $duration = $_POST['duration'] ?? null;
    $isPreview = isset($_POST['is_preview']) ? 1 : 0;
    
    if ($moduleId && !empty($title)) {
        $module = Module::find($moduleId);
        $lessonCount = count($module ? Lesson::getByModule($moduleId) : []);
        
        $lessonData = [
            'module_id' => $moduleId,
            'title' => $title,
            'lesson_type' => $lessonType,
            'video_url' => $videoUrl,
            'content' => $content,
            'duration' => $duration,
            'is_preview' => $isPreview,
            'order_index' => $lessonCount
        ];
        
        if (Lesson::create($lessonData)) {
            flash('message', 'Lesson created successfully!', 'success');
        } else {
            flash('message', 'Failed to create lesson', 'error');
        }
    }
    
    redirect($_SERVER['REQUEST_URI']);
}

// Handle lesson delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_lesson') {
    validateCSRF();
    
    $lessonId = $_POST['lesson_id'] ?? null;
    
    if ($lessonId) {
        $lesson = Lesson::find($lessonId);
        if ($lesson && $lesson->delete()) {
            flash('message', 'Lesson deleted successfully!', 'success');
        } else {
            flash('message', 'Failed to delete lesson', 'error');
        }
    }
    
    redirect($_SERVER['REQUEST_URI']);
}

$page_title = 'Manage Course Content - Admin';
require_once '../../../src/templates/admin-header.php';
?>

<div class="min-h-screen bg-gray-50" x-data="{ 
    showModuleModal: false, 
    showLessonModal: false,
    editingModule: null,
    selectedModuleId: null 
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Manage Course Content</h1>
                    <p class="text-gray-600 mt-1"><?= htmlspecialchars($course->getTitle()) ?></p>
                </div>
                <div class="flex space-x-3">
                    <a href="<?= url('admin/courses/edit.php?id=' . $courseId) ?>" class="btn btn-secondary">
                        <i class="fas fa-edit mr-2"></i> Edit Course
                    </a>
                    <a href="<?= url('admin/courses/index.php') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <?php if (hasFlash('message')): ?>
            <?= renderFlash() ?>
        <?php endif; ?>

        <!-- Add Module Button -->
        <div class="mb-6">
            <button @click="showModuleModal = true; editingModule = null" 
                    class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Add Module
            </button>
        </div>

        <!-- Modules List -->
        <div class="space-y-4">
            <?php if (empty($modules)): ?>
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Modules Yet</h3>
                    <p class="text-gray-500 mb-6">Start building your course by adding modules</p>
                    <button @click="showModuleModal = true" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i> Add First Module
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($modules as $index => $module): ?>
                    <div class="bg-white rounded-lg shadow">
                        <!-- Module Header -->
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-700 font-bold text-sm">
                                            <?= $index + 1 ?>
                                        </span>
                                        <h3 class="text-xl font-bold text-gray-900">
                                            <?= htmlspecialchars($module['title']) ?>
                                        </h3>
                                        <span class="text-sm text-gray-500">
                                            (<?= count($module['lessons']) ?> lessons)
                                        </span>
                                    </div>
                                    <?php if ($module['description']): ?>
                                        <p class="text-gray-600 ml-11"><?= htmlspecialchars($module['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button @click="editingModule = <?= htmlspecialchars(json_encode($module)) ?>; showModuleModal = true" 
                                            class="text-blue-600 hover:text-blue-800 p-2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button @click="selectedModuleId = <?= $module['id'] ?>; showLessonModal = true" 
                                            class="text-green-600 hover:text-green-800 p-2">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <form method="POST" class="inline" 
                                          onsubmit="return confirm('Delete this module and all its lessons?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete_module">
                                        <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800 p-2">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Lessons List -->
                        <div class="p-6">
                            <?php if (empty($module['lessons'])): ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-book-open text-3xl mb-2"></i>
                                    <p>No lessons yet</p>
                                    <button @click="selectedModuleId = <?= $module['id'] ?>; showLessonModal = true" 
                                            class="text-primary-600 hover:text-primary-700 mt-2">
                                        + Add Lesson
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="space-y-2">
                                    <?php foreach ($module['lessons'] as $lessonIndex => $lesson): ?>
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100">
                                            <div class="flex items-center space-x-3 flex-1">
                                                <span class="text-gray-400 font-medium"><?= $lessonIndex + 1 ?>.</span>
                                                <i class="fas fa-<?= $lesson['lesson_type'] == 'video' ? 'play-circle' : 'file-alt' ?> text-primary-600"></i>
                                                <div>
                                                    <h4 class="font-medium text-gray-900">
                                                        <?= htmlspecialchars($lesson['title']) ?>
                                                        <?php if ($lesson['is_preview']): ?>
                                                            <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Preview</span>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <p class="text-sm text-gray-500">
                                                        <?= ucfirst($lesson['lesson_type']) ?>
                                                        <?php if ($lesson['duration']): ?>
                                                            • <?= $lesson['duration'] ?> min
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <a href="<?= url('admin/courses/lesson-edit.php?id=' . $lesson['id']) ?>" 
                                                   class="text-blue-600 hover:text-blue-800 p-2">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" class="inline" 
                                                      onsubmit="return confirm('Delete this lesson?')">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="action" value="delete_lesson">
                                                    <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                                    <button type="submit" class="text-red-600 hover:text-red-800 p-2">
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
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Module Modal -->
    <div x-show="showModuleModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click.away="showModuleModal = false">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold" x-text="editingModule ? 'Edit Module' : 'Add Module'"></h3>
                <button @click="showModuleModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" x-bind:value="editingModule ? 'update_module' : 'create_module'">
                <input type="hidden" name="module_id" x-bind:value="editingModule ? editingModule.id : ''">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Module Title *</label>
                        <input type="text" name="title" 
                               x-bind:value="editingModule ? editingModule.title : ''"
                               class="w-full px-4 py-2 border rounded-lg" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" 
                                  class="w-full px-4 py-2 border rounded-lg"
                                  x-text="editingModule ? editingModule.description : ''"></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="showModuleModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span x-text="editingModule ? 'Update' : 'Create'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lesson Modal -->
    <div x-show="showLessonModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click.away="showLessonModal = false">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold">Add Lesson</h3>
                <button @click="showLessonModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create_lesson">
                <input type="hidden" name="module_id" x-bind:value="selectedModuleId">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Title *</label>
                        <input type="text" name="lesson_title" 
                               class="w-full px-4 py-2 border rounded-lg" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="lesson_type" class="w-full px-4 py-2 border rounded-lg">
                            <option value="video">Video</option>
                            <option value="article">Article</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Video URL</label>
                        <input type="url" name="video_url" 
                               class="w-full px-4 py-2 border rounded-lg"
                               placeholder="https://youtube.com/watch?v=...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                        <textarea name="content" rows="6" 
                                  class="w-full px-4 py-2 border rounded-lg"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                        <input type="number" name="duration" 
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="is_preview" id="is_preview" class="h-4 w-4">
                        <label for="is_preview" class="ml-2 text-sm text-gray-700">
                            Allow as preview (free access)
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="showLessonModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Lesson</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../../src/templates/admin-footer.php'; ?>