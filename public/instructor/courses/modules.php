<?php
/**
 * Module Management Page
 * Allows instructors to manage course modules and lessons
 */

require_once '../../../src/middleware/instructor-only.php';
require_once '../../../src/classes/Course.php';
require_once '../../../src/classes/Module.php';
require_once '../../../src/classes/Lesson.php';

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
    setFlash('You do not have permission to manage this course.', 'error');
    redirect('instructor/courses.php');
}

// Get all modules for this course
$modules = Module::getByCourse($courseId);

// Handle module operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        setFlash('Invalid security token.', 'error');
        redirect('instructor/courses/modules.php?id=' . $courseId);
    }

    $action = $_POST['action'];

    // Create new module
    if ($action === 'create_module') {
        if (empty($_POST['title'])) {
            setFlash('Module title is required.', 'error');
        } else {
            // Get max display_order
            $maxOrder = $db->fetchOne("SELECT MAX(display_order) as max_order FROM course_modules WHERE course_id = ?", [$courseId]);
            $displayOrder = ($maxOrder['max_order'] ?? 0) + 1;

            $moduleData = [
                'course_id' => $courseId,
                'title' => sanitize($_POST['title']),
                'description' => sanitize($_POST['description'] ?? ''),
                'display_order' => $displayOrder,
                'duration_minutes' => (int)($_POST['duration_minutes'] ?? 0),
                'is_preview' => isset($_POST['is_preview']) ? 1 : 0
            ];

            $sql = "INSERT INTO course_modules (course_id, title, description, display_order, duration_minutes, is_preview, created_at, updated_at)
                    VALUES (:course_id, :title, :description, :display_order, :duration_minutes, :is_preview, NOW(), NOW())";

            if ($db->query($sql, $moduleData)) {
                setFlash('Module created successfully!', 'success');
            } else {
                setFlash('Failed to create module.', 'error');
            }
        }
        redirect('instructor/courses/modules.php?id=' . $courseId);
    }

    // Update module
    if ($action === 'update_module' && isset($_POST['module_id'])) {
        $moduleId = (int)$_POST['module_id'];
        $module = Module::find($moduleId);

        if ($module && !empty($_POST['title'])) {
            $updateData = [
                'title' => sanitize($_POST['title']),
                'description' => sanitize($_POST['description'] ?? ''),
                'duration_minutes' => (int)($_POST['duration_minutes'] ?? 0),
                'is_preview' => isset($_POST['is_preview']) ? 1 : 0
            ];

            $sql = "UPDATE course_modules SET title = :title, description = :description,
                    duration_minutes = :duration_minutes, is_preview = :is_preview, updated_at = NOW()
                    WHERE id = :id";
            $updateData['id'] = $moduleId;

            if ($db->query($sql, $updateData)) {
                setFlash('Module updated successfully!', 'success');
            } else {
                setFlash('Failed to update module.', 'error');
            }
        }
        redirect('instructor/courses/modules.php?id=' . $courseId);
    }

    // Delete module
    if ($action === 'delete_module' && isset($_POST['module_id'])) {
        $moduleId = (int)$_POST['module_id'];
        $module = Module::find($moduleId);

        if ($module) {
            if ($module->delete()) {
                setFlash('Module deleted successfully!', 'success');
            } else {
                setFlash('Failed to delete module.', 'error');
            }
        }
        redirect('instructor/courses/modules.php?id=' . $courseId);
    }

    // Reorder modules
    if ($action === 'reorder_modules' && isset($_POST['module_orders'])) {
        parse_str($_POST['module_orders'], $orders);
        if (isset($orders['module'])) {
            foreach ($orders['module'] as $position => $moduleId) {
                $db->query("UPDATE course_modules SET display_order = ? WHERE id = ?", [$position + 1, $moduleId]);
            }
            setFlash('Modules reordered successfully!', 'success');
        }
        redirect('instructor/courses/modules.php?id=' . $courseId);
    }
}

$page_title = 'Manage Course Content - ' . $course->getTitle();
require_once '../../../src/templates/instructor-header.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Manage Course Content</h1>
                    <p class="text-gray-600 mt-2"><?= htmlspecialchars($course->getTitle()) ?></p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="<?= url('course.php?slug=' . $course->getSlug()) ?>"
                       target="_blank"
                       class="btn btn-secondary">
                        <i class="fas fa-eye mr-2"></i> Preview Course
                    </a>
                    <a href="<?= url('instructor/courses/edit.php?id=' . $courseId) ?>"
                       class="btn btn-secondary">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                    <a href="<?= url('instructor/courses.php') ?>"
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex flex-wrap items-center gap-4">
                <button onclick="openCreateModuleModal()"
                        class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i> Add Module
                </button>
                <button onclick="toggleReorderMode()"
                        id="reorder-btn"
                        class="btn btn-secondary">
                    <i class="fas fa-sort mr-2"></i> Reorder Modules
                </button>
            </div>
        </div>

        <!-- Modules List -->
        <?php if (empty($modules)): ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Modules Yet</h3>
            <p class="text-gray-500 mb-6">Start building your course by creating modules</p>
            <button onclick="openCreateModuleModal()" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Create First Module
            </button>
        </div>
        <?php else: ?>
        <div id="modules-container" class="space-y-4">
            <?php foreach ($modules as $module):
                $lessons = Lesson::getByModule($module['id']);
            ?>
            <div class="module-item bg-white rounded-lg shadow-md" data-module-id="<?= $module['id'] ?>">
                <!-- Module Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <div class="drag-handle hidden cursor-move text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-grip-vertical text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                                        <?= htmlspecialchars($module['title']) ?>
                                        <?php if ($module['is_preview']): ?>
                                        <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Preview</span>
                                        <?php endif; ?>
                                    </h3>
                                    <?php if ($module['description']): ?>
                                    <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($module['description']) ?></p>
                                    <?php endif; ?>
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                        <span><i class="fas fa-book mr-1"></i> <?= $module['lesson_count'] ?> lessons</span>
                                        <?php if ($module['duration_minutes'] > 0): ?>
                                        <span><i class="fas fa-clock mr-1"></i> <?= $module['duration_minutes'] ?> min</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2 ml-4">
                            <button onclick="openEditModuleModal(<?= htmlspecialchars(json_encode($module)) ?>)"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                    title="Edit Module">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirmDeleteModule(<?= $module['id'] ?>, '<?= htmlspecialchars($module['title']) ?>')"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                    title="Delete Module">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lessons List -->
                <div class="p-6 bg-gray-50">
                    <?php if (empty($lessons)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <p class="mb-4">No lessons in this module yet</p>
                        <a href="<?= url('instructor/courses/lessons.php?module_id=' . $module['id']) ?>"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-plus mr-2"></i> Add Lesson
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="space-y-2 mb-4">
                        <?php foreach ($lessons as $lesson): ?>
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg hover:shadow transition">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-600">
                                    <?php
                                    $icon = 'fa-file-alt';
                                    if ($lesson['lesson_type'] === 'video') $icon = 'fa-video';
                                    elseif ($lesson['lesson_type'] === 'quiz') $icon = 'fa-question-circle';
                                    elseif ($lesson['lesson_type'] === 'assignment') $icon = 'fa-tasks';
                                    ?>
                                    <i class="fas <?= $icon ?>"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900"><?= htmlspecialchars($lesson['title']) ?></h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?= ucfirst($lesson['lesson_type']) ?>
                                        <?php if ($lesson['duration_minutes']): ?>
                                        • <?= $lesson['duration_minutes'] ?> min
                                        <?php endif; ?>
                                        <?php if ($lesson['is_preview']): ?>
                                        <span class="ml-2 text-blue-600">• Free Preview</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <a href="<?= url('instructor/courses/lessons.php?module_id=' . $module['id'] . '&lesson_id=' . $lesson['id']) ?>"
                               class="text-primary-600 hover:text-primary-700">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= url('instructor/courses/lessons.php?module_id=' . $module['id']) ?>"
                       class="btn btn-sm btn-secondary w-full">
                        <i class="fas fa-plus mr-2"></i> Add Lesson to This Module
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Reorder Form (Hidden) -->
        <form id="reorder-form" method="POST" class="hidden">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="reorder_modules">
            <input type="hidden" name="module_orders" id="module-orders">
        </form>
        <?php endif; ?>

    </div>
</div>

<!-- Create/Edit Module Modal -->
<div id="module-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" id="modal-action" value="create_module">
            <input type="hidden" name="module_id" id="modal-module-id">

            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900" id="modal-title">Create New Module</h2>
            </div>

            <div class="p-6 space-y-4">
                <!-- Module Title -->
                <div>
                    <label for="modal-module-title" class="block text-sm font-medium text-gray-700 mb-2">
                        Module Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="modal-module-title"
                           name="title"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="e.g., Introduction to JavaScript">
                </div>

                <!-- Module Description -->
                <div>
                    <label for="modal-module-description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="modal-module-description"
                              name="description"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="Brief description of what this module covers..."></textarea>
                </div>

                <!-- Duration -->
                <div>
                    <label for="modal-duration" class="block text-sm font-medium text-gray-700 mb-2">
                        Estimated Duration (minutes)
                    </label>
                    <input type="number"
                           id="modal-duration"
                           name="duration_minutes"
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="60">
                </div>

                <!-- Preview Checkbox -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="modal-is-preview"
                               name="is_preview"
                               type="checkbox"
                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    </div>
                    <div class="ml-3">
                        <label for="modal-is-preview" class="font-medium text-gray-700">
                            Free Preview
                        </label>
                        <p class="text-sm text-gray-500">Allow non-enrolled students to preview this module</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 rounded-b-lg">
                <button type="button"
                        onclick="closeModuleModal()"
                        class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                    <i class="fas fa-save mr-2"></i> Save Module
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="delete_module">
            <input type="hidden" name="module_id" id="delete-module-id">

            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Module</h3>
                <p class="text-gray-600 mb-4">
                    Are you sure you want to delete "<span id="delete-module-name" class="font-semibold"></span>"?
                </p>
                <p class="text-sm text-red-600">
                    <i class="fas fa-warning mr-1"></i> This will also delete all lessons in this module. This action cannot be undone.
                </p>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 rounded-b-lg">
                <button type="button"
                        onclick="closeDeleteModal()"
                        class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-trash mr-2"></i> Delete Module
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Module Modal Functions
function openCreateModuleModal() {
    document.getElementById('modal-title').textContent = 'Create New Module';
    document.getElementById('modal-action').value = 'create_module';
    document.getElementById('modal-module-id').value = '';
    document.getElementById('modal-module-title').value = '';
    document.getElementById('modal-module-description').value = '';
    document.getElementById('modal-duration').value = '';
    document.getElementById('modal-is-preview').checked = false;
    document.getElementById('module-modal').classList.remove('hidden');
}

function openEditModuleModal(module) {
    document.getElementById('modal-title').textContent = 'Edit Module';
    document.getElementById('modal-action').value = 'update_module';
    document.getElementById('modal-module-id').value = module.id;
    document.getElementById('modal-module-title').value = module.title;
    document.getElementById('modal-module-description').value = module.description || '';
    document.getElementById('modal-duration').value = module.duration_minutes || '';
    document.getElementById('modal-is-preview').checked = module.is_preview == 1;
    document.getElementById('module-modal').classList.remove('hidden');
}

function closeModuleModal() {
    document.getElementById('module-modal').classList.add('hidden');
}

// Delete Modal Functions
function confirmDeleteModule(moduleId, moduleName) {
    document.getElementById('delete-module-id').value = moduleId;
    document.getElementById('delete-module-name').textContent = moduleName;
    document.getElementById('delete-modal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('delete-modal').classList.add('hidden');
}

// Reorder Functionality
let sortable = null;
let isReorderMode = false;

function toggleReorderMode() {
    isReorderMode = !isReorderMode;
    const btn = document.getElementById('reorder-btn');
    const dragHandles = document.querySelectorAll('.drag-handle');
    const container = document.getElementById('modules-container');

    if (isReorderMode) {
        btn.innerHTML = '<i class="fas fa-save mr-2"></i> Save Order';
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-primary');
        dragHandles.forEach(handle => handle.classList.remove('hidden'));

        // Initialize Sortable
        sortable = Sortable.create(container, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function() {
                // Update order
            }
        });
    } else {
        // Save order
        const order = sortable.toArray();
        document.getElementById('module-orders').value = order.map((id, index) => `module[${index}]=${id}`).join('&');
        document.getElementById('reorder-form').submit();
    }
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModuleModal();
        closeDeleteModal();
    }
});

// Close modals on outside click
document.getElementById('module-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModuleModal();
});
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<?php require_once '../../../src/templates/instructor-footer.php'; ?>
