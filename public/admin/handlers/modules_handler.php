<?php
/**
 * Modules Handler - Processes form submissions before HTML output
 */

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
