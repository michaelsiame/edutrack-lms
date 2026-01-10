<?php
/**
 * Modules & Lessons Management Page - Full CRUD
 * Manage course modules and their lessons with drag-and-drop reordering
 */

$courseId = (int)($_GET['course_id'] ?? 0);

if (!$courseId) {
    echo '<div class="text-center py-12"><p class="text-gray-500">Please select a course from the Courses page to manage its modules.</p><a href="?page=courses" class="text-blue-600 hover:underline mt-2 inline-block">Go to Courses</a></div>';
    return;
}

// Get course info
$course = $db->fetchOne("SELECT * FROM courses WHERE id = ?", [$courseId]);
if (!$course) {
    echo '<div class="text-center py-12"><p class="text-red-500">Course not found.</p><a href="?page=courses" class="text-blue-600 hover:underline mt-2 inline-block">Go to Courses</a></div>';
    return;
}

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    if ($_GET['ajax'] === 'get_module' && isset($_GET['id'])) {
        $module = $db->fetchOne("SELECT * FROM modules WHERE id = ?", [(int)$_GET['id']]);
        echo json_encode($module ?: ['error' => 'Module not found']);
        exit;
    }

    if ($_GET['ajax'] === 'get_lesson' && isset($_GET['id'])) {
        $lesson = $db->fetchOne("SELECT * FROM lessons WHERE id = ?", [(int)$_GET['id']]);
        echo json_encode($lesson ?: ['error' => 'Lesson not found']);
        exit;
    }

    if ($_GET['ajax'] === 'reorder_modules' && isset($_POST['order'])) {
        $order = json_decode($_POST['order'], true);
        foreach ($order as $index => $moduleId) {
            $db->update('modules', ['display_order' => $index + 1], 'id = ?', [(int)$moduleId]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_GET['ajax'] === 'reorder_lessons' && isset($_POST['order'], $_POST['module_id'])) {
        $order = json_decode($_POST['order'], true);
        $moduleId = (int)$_POST['module_id'];
        foreach ($order as $index => $lessonId) {
            $db->update('lessons', ['display_order' => $index + 1, 'module_id' => $moduleId], 'id = ?', [(int)$lessonId]);
        }
        echo json_encode(['success' => true]);
        exit;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Add module
    if ($action === 'add_module') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $durationMinutes = (int)($_POST['duration_minutes'] ?? 0);
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($title) {
            $maxOrder = $db->fetchColumn("SELECT MAX(display_order) FROM modules WHERE course_id = ?", [$courseId]);
            $db->insert('modules', [
                'course_id' => $courseId,
                'title' => $title,
                'description' => $description,
                'duration_minutes' => $durationMinutes ?: null,
                'display_order' => ($maxOrder ?? 0) + 1,
                'is_published' => $isPublished,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            header("Location: ?page=modules&course_id=$courseId&msg=module_added");
            exit;
        }
    }

    // Edit module
    if ($action === 'edit_module' && isset($_POST['module_id'])) {
        $moduleId = (int)$_POST['module_id'];
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $durationMinutes = (int)($_POST['duration_minutes'] ?? 0);
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($title) {
            $db->update('modules', [
                'title' => $title,
                'description' => $description,
                'duration_minutes' => $durationMinutes ?: null,
                'is_published' => $isPublished,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$moduleId]);
            header("Location: ?page=modules&course_id=$courseId&msg=module_updated");
            exit;
        }
    }

    // Delete module
    if ($action === 'delete_module' && isset($_POST['module_id'])) {
        $moduleId = (int)$_POST['module_id'];
        $db->delete('lessons', 'module_id = ?', [$moduleId]);
        $db->delete('modules', 'id = ?', [$moduleId]);
        header("Location: ?page=modules&course_id=$courseId&msg=module_deleted");
        exit;
    }

    // Toggle module publish
    if ($action === 'toggle_module' && isset($_POST['module_id'])) {
        $moduleId = (int)$_POST['module_id'];
        $module = $db->fetchOne("SELECT is_published FROM modules WHERE id = ?", [$moduleId]);
        $db->update('modules', ['is_published' => $module['is_published'] ? 0 : 1], 'id = ?', [$moduleId]);
        header("Location: ?page=modules&course_id=$courseId&msg=module_toggled");
        exit;
    }

    // Add lesson
    if ($action === 'add_lesson' && isset($_POST['module_id'])) {
        $moduleId = (int)$_POST['module_id'];
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $lessonType = $_POST['lesson_type'] ?? 'Reading';
        $durationMinutes = (int)($_POST['duration_minutes'] ?? 0);
        $videoUrl = trim($_POST['video_url'] ?? '');
        $isPreview = isset($_POST['is_preview']) ? 1 : 0;
        $isMandatory = isset($_POST['is_mandatory']) ? 1 : 0;
        $points = (int)($_POST['points'] ?? 0);

        if ($title) {
            $maxOrder = $db->fetchColumn("SELECT MAX(display_order) FROM lessons WHERE module_id = ?", [$moduleId]);
            $db->insert('lessons', [
                'module_id' => $moduleId,
                'title' => $title,
                'content' => $content,
                'lesson_type' => $lessonType,
                'duration_minutes' => $durationMinutes ?: null,
                'video_url' => $videoUrl ?: null,
                'is_preview' => $isPreview,
                'is_mandatory' => $isMandatory,
                'points' => $points,
                'display_order' => ($maxOrder ?? 0) + 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            header("Location: ?page=modules&course_id=$courseId&msg=lesson_added");
            exit;
        }
    }

    // Edit lesson
    if ($action === 'edit_lesson' && isset($_POST['lesson_id'])) {
        $lessonId = (int)$_POST['lesson_id'];
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $lessonType = $_POST['lesson_type'] ?? 'Reading';
        $durationMinutes = (int)($_POST['duration_minutes'] ?? 0);
        $videoUrl = trim($_POST['video_url'] ?? '');
        $isPreview = isset($_POST['is_preview']) ? 1 : 0;
        $isMandatory = isset($_POST['is_mandatory']) ? 1 : 0;
        $points = (int)($_POST['points'] ?? 0);

        if ($title) {
            $db->update('lessons', [
                'title' => $title,
                'content' => $content,
                'lesson_type' => $lessonType,
                'duration_minutes' => $durationMinutes ?: null,
                'video_url' => $videoUrl ?: null,
                'is_preview' => $isPreview,
                'is_mandatory' => $isMandatory,
                'points' => $points,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$lessonId]);
            header("Location: ?page=modules&course_id=$courseId&msg=lesson_updated");
            exit;
        }
    }

    // Delete lesson
    if ($action === 'delete_lesson' && isset($_POST['lesson_id'])) {
        $lessonId = (int)$_POST['lesson_id'];
        $db->delete('lesson_resources', 'lesson_id = ?', [$lessonId]);
        $db->delete('lessons', 'id = ?', [$lessonId]);
        header("Location: ?page=modules&course_id=$courseId&msg=lesson_deleted");
        exit;
    }
}

// Get modules with lessons
$modules = $db->fetchAll("
    SELECT m.*,
           (SELECT COUNT(*) FROM lessons WHERE module_id = m.id) as lesson_count,
           (SELECT SUM(duration_minutes) FROM lessons WHERE module_id = m.id) as total_duration
    FROM modules m
    WHERE m.course_id = ?
    ORDER BY m.display_order ASC
", [$courseId]);

// Get all lessons grouped by module
$allLessons = [];
foreach ($modules as $module) {
    $allLessons[$module['id']] = $db->fetchAll("
        SELECT * FROM lessons WHERE module_id = ? ORDER BY display_order ASC
    ", [$module['id']]);
}

$msg = $_GET['msg'] ?? '';
?>

<div class="space-y-6">
    <!-- Header with breadcrumb -->
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="?page=courses" class="hover:text-blue-600">Courses</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-800 font-medium"><?= htmlspecialchars($course['title']) ?></span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Course Modules & Lessons</h2>
            <p class="text-gray-500 text-sm mt-1">Organize your course content with modules and lessons</p>
        </div>
        <div class="flex gap-2">
            <a href="?page=courses" class="px-4 py-2 border rounded-lg hover:bg-gray-50 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Courses
            </a>
            <button onclick="openModuleModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 shadow-sm">
                <i class="fas fa-plus"></i> Add Module
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($msg): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <?php
            echo match($msg) {
                'module_added' => 'Module added successfully!',
                'module_updated' => 'Module updated successfully!',
                'module_deleted' => 'Module deleted successfully!',
                'module_toggled' => 'Module visibility updated!',
                'lesson_added' => 'Lesson added successfully!',
                'lesson_updated' => 'Lesson updated successfully!',
                'lesson_deleted' => 'Lesson deleted successfully!',
                default => 'Action completed!'
            };
            ?>
        </div>
    <?php endif; ?>

    <!-- Course Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= count($modules) ?></p>
                    <p class="text-xs text-gray-500">Modules</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                    <i class="fas fa-book-open"></i>
                </div>
                <div>
                    <?php $totalLessons = array_sum(array_column($modules, 'lesson_count')); ?>
                    <p class="text-2xl font-bold text-gray-800"><?= $totalLessons ?></p>
                    <p class="text-xs text-gray-500">Lessons</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <?php $totalMinutes = array_sum(array_column($modules, 'total_duration')); ?>
                    <p class="text-2xl font-bold text-gray-800"><?= floor($totalMinutes / 60) ?>h <?= $totalMinutes % 60 ?>m</p>
                    <p class="text-xs text-gray-500">Total Duration</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 text-yellow-600 rounded-lg">
                    <i class="fas fa-eye"></i>
                </div>
                <div>
                    <?php $publishedModules = count(array_filter($modules, fn($m) => $m['is_published'])); ?>
                    <p class="text-2xl font-bold text-gray-800"><?= $publishedModules ?>/<?= count($modules) ?></p>
                    <p class="text-xs text-gray-500">Published</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modules List -->
    <div id="modulesList" class="space-y-4">
        <?php if (empty($modules)): ?>
            <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
                <i class="fas fa-layer-group text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-600 mb-2">No modules yet</h3>
                <p class="text-gray-400 mb-4">Start building your course by adding the first module.</p>
                <button onclick="openModuleModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Add First Module
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($modules as $index => $module): ?>
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden" data-module-id="<?= $module['id'] ?>">
                    <!-- Module Header -->
                    <div class="p-4 bg-gray-50 border-b flex items-center gap-4">
                        <div class="cursor-move text-gray-400 hover:text-gray-600 module-handle">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-400">Module <?= $index + 1 ?></span>
                                <?php if (!$module['is_published']): ?>
                                    <span class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded-full">Draft</span>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($module['title']) ?></h3>
                            <?php if ($module['description']): ?>
                                <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars(substr($module['description'], 0, 150)) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <span><i class="fas fa-book-open mr-1"></i><?= $module['lesson_count'] ?> lessons</span>
                            <?php if ($module['total_duration']): ?>
                                <span><i class="fas fa-clock mr-1"></i><?= $module['total_duration'] ?> min</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-1">
                            <button onclick="openLessonModal(<?= $module['id'] ?>)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Add Lesson">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button onclick="openModuleModal(<?= $module['id'] ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit Module">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="toggle_module">
                                <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                                <button type="submit" class="p-2 <?= $module['is_published'] ? 'text-green-600' : 'text-gray-400' ?> hover:bg-gray-100 rounded-lg" title="<?= $module['is_published'] ? 'Published' : 'Unpublished' ?>">
                                    <i class="fas <?= $module['is_published'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                </button>
                            </form>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete this module and all its lessons?')">
                                <input type="hidden" name="action" value="delete_module">
                                <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete Module">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Lessons List -->
                    <div class="lessons-list divide-y" data-module-id="<?= $module['id'] ?>">
                        <?php if (empty($allLessons[$module['id']])): ?>
                            <div class="p-6 text-center text-gray-400">
                                <p>No lessons in this module yet.</p>
                                <button onclick="openLessonModal(<?= $module['id'] ?>)" class="text-blue-600 hover:underline mt-2">Add a lesson</button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($allLessons[$module['id']] as $lesson): ?>
                                <div class="p-4 hover:bg-gray-50 flex items-center gap-4 lesson-item" data-lesson-id="<?= $lesson['id'] ?>">
                                    <div class="cursor-move text-gray-300 hover:text-gray-500 lesson-handle">
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center <?= match($lesson['lesson_type']) {
                                        'Video' => 'bg-red-100 text-red-600',
                                        'Quiz' => 'bg-purple-100 text-purple-600',
                                        'Assignment' => 'bg-orange-100 text-orange-600',
                                        'Live Session' => 'bg-green-100 text-green-600',
                                        'Download' => 'bg-blue-100 text-blue-600',
                                        default => 'bg-gray-100 text-gray-600'
                                    } ?>">
                                        <i class="fas <?= match($lesson['lesson_type']) {
                                            'Video' => 'fa-play-circle',
                                            'Quiz' => 'fa-question-circle',
                                            'Assignment' => 'fa-tasks',
                                            'Live Session' => 'fa-video',
                                            'Download' => 'fa-download',
                                            default => 'fa-file-alt'
                                        } ?>"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium text-gray-800"><?= htmlspecialchars($lesson['title']) ?></p>
                                            <?php if ($lesson['is_preview']): ?>
                                                <span class="px-1.5 py-0.5 text-[10px] bg-blue-100 text-blue-700 rounded">Preview</span>
                                            <?php endif; ?>
                                            <?php if ($lesson['is_mandatory']): ?>
                                                <span class="px-1.5 py-0.5 text-[10px] bg-red-100 text-red-700 rounded">Required</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
                                            <span><?= $lesson['lesson_type'] ?></span>
                                            <?php if ($lesson['duration_minutes']): ?>
                                                <span><i class="fas fa-clock mr-1"></i><?= $lesson['duration_minutes'] ?> min</span>
                                            <?php endif; ?>
                                            <?php if ($lesson['points']): ?>
                                                <span><i class="fas fa-star mr-1"></i><?= $lesson['points'] ?> pts</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button onclick="openLessonModal(<?= $module['id'] ?>, <?= $lesson['id'] ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit Lesson">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this lesson?')">
                                            <input type="hidden" name="action" value="delete_lesson">
                                            <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete Lesson">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Add Lesson Footer -->
                    <div class="p-3 bg-gray-50 border-t">
                        <button onclick="openLessonModal(<?= $module['id'] ?>)" class="w-full py-2 text-sm text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-plus"></i> Add Lesson to this Module
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Module Modal -->
<div id="moduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-lg shadow-2xl">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <h3 id="moduleModalTitle" class="text-xl font-semibold text-gray-800">Add Module</h3>
                <button onclick="closeModuleModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
        </div>
        <form id="moduleForm" method="POST" class="p-6">
            <input type="hidden" name="action" id="moduleFormAction" value="add_module">
            <input type="hidden" name="module_id" id="moduleId" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Module Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="moduleTitle" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="moduleDescription" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" id="moduleDuration" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_published" id="modulePublished" checked class="w-4 h-4 text-blue-600 rounded">
                    <label for="modulePublished" class="text-sm text-gray-700">Published (visible to students)</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModuleModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" id="moduleSubmitBtn">Add Module</button>
            </div>
        </form>
    </div>
</div>

<!-- Lesson Modal -->
<div id="lessonModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b sticky top-0 bg-white">
            <div class="flex justify-between items-center">
                <h3 id="lessonModalTitle" class="text-xl font-semibold text-gray-800">Add Lesson</h3>
                <button onclick="closeLessonModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
        </div>
        <form id="lessonForm" method="POST" class="p-6">
            <input type="hidden" name="action" id="lessonFormAction" value="add_lesson">
            <input type="hidden" name="module_id" id="lessonModuleId" value="">
            <input type="hidden" name="lesson_id" id="lessonId" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lesson Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="lessonTitle" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lesson Type</label>
                        <select name="lesson_type" id="lessonType" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="Reading">Reading</option>
                            <option value="Video">Video</option>
                            <option value="Quiz">Quiz</option>
                            <option value="Assignment">Assignment</option>
                            <option value="Live Session">Live Session</option>
                            <option value="Download">Download</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" id="lessonDuration" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" id="lessonContent" rows="5" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Lesson content, instructions, or description..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
                    <input type="url" name="video_url" id="lessonVideoUrl" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="https://youtube.com/watch?v=...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Points</label>
                    <input type="number" name="points" id="lessonPoints" min="0" value="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_preview" id="lessonPreview" class="w-4 h-4 text-blue-600 rounded">
                        <label for="lessonPreview" class="text-sm text-gray-700">Free Preview</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_mandatory" id="lessonMandatory" checked class="w-4 h-4 text-blue-600 rounded">
                        <label for="lessonMandatory" class="text-sm text-gray-700">Mandatory</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeLessonModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" id="lessonSubmitBtn">Add Lesson</button>
            </div>
        </form>
    </div>
</div>

<script>
// Module Modal Functions
function openModuleModal(moduleId = null) {
    if (moduleId) {
        document.getElementById('moduleModalTitle').textContent = 'Edit Module';
        document.getElementById('moduleFormAction').value = 'edit_module';
        document.getElementById('moduleId').value = moduleId;
        document.getElementById('moduleSubmitBtn').textContent = 'Save Changes';

        fetch('?page=modules&course_id=<?= $courseId ?>&ajax=get_module&id=' + moduleId)
            .then(r => r.json())
            .then(data => {
                document.getElementById('moduleTitle').value = data.title || '';
                document.getElementById('moduleDescription').value = data.description || '';
                document.getElementById('moduleDuration').value = data.duration_minutes || '';
                document.getElementById('modulePublished').checked = data.is_published == 1;
            });
    } else {
        document.getElementById('moduleModalTitle').textContent = 'Add Module';
        document.getElementById('moduleFormAction').value = 'add_module';
        document.getElementById('moduleId').value = '';
        document.getElementById('moduleSubmitBtn').textContent = 'Add Module';
        document.getElementById('moduleForm').reset();
        document.getElementById('modulePublished').checked = true;
    }
    document.getElementById('moduleModal').classList.remove('hidden');
}

function closeModuleModal() {
    document.getElementById('moduleModal').classList.add('hidden');
}

// Lesson Modal Functions
function openLessonModal(moduleId, lessonId = null) {
    document.getElementById('lessonModuleId').value = moduleId;

    if (lessonId) {
        document.getElementById('lessonModalTitle').textContent = 'Edit Lesson';
        document.getElementById('lessonFormAction').value = 'edit_lesson';
        document.getElementById('lessonId').value = lessonId;
        document.getElementById('lessonSubmitBtn').textContent = 'Save Changes';

        fetch('?page=modules&course_id=<?= $courseId ?>&ajax=get_lesson&id=' + lessonId)
            .then(r => r.json())
            .then(data => {
                document.getElementById('lessonTitle').value = data.title || '';
                document.getElementById('lessonType').value = data.lesson_type || 'Reading';
                document.getElementById('lessonDuration').value = data.duration_minutes || '';
                document.getElementById('lessonContent').value = data.content || '';
                document.getElementById('lessonVideoUrl').value = data.video_url || '';
                document.getElementById('lessonPoints').value = data.points || 0;
                document.getElementById('lessonPreview').checked = data.is_preview == 1;
                document.getElementById('lessonMandatory').checked = data.is_mandatory == 1;
            });
    } else {
        document.getElementById('lessonModalTitle').textContent = 'Add Lesson';
        document.getElementById('lessonFormAction').value = 'add_lesson';
        document.getElementById('lessonId').value = '';
        document.getElementById('lessonSubmitBtn').textContent = 'Add Lesson';
        document.getElementById('lessonForm').reset();
        document.getElementById('lessonModuleId').value = moduleId;
        document.getElementById('lessonMandatory').checked = true;
    }
    document.getElementById('lessonModal').classList.remove('hidden');
}

function closeLessonModal() {
    document.getElementById('lessonModal').classList.add('hidden');
}

// Close modals on outside click
document.getElementById('moduleModal').addEventListener('click', e => { if (e.target === document.getElementById('moduleModal')) closeModuleModal(); });
document.getElementById('lessonModal').addEventListener('click', e => { if (e.target === document.getElementById('lessonModal')) closeLessonModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModuleModal(); closeLessonModal(); }});
</script>
