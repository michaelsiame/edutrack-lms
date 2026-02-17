<?php
/**
 * Announcements Handler - Processes form submissions
 * Supports: Create, Edit, Delete, Toggle Publish
 */

$action = $_POST['action'] ?? '';

// CSRF protection
if (!verifyCsrfToken()) {
    header('Location: ?page=announcements&msg=csrf_error');
    exit;
}

// Add announcement
if ($action === 'add') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $priority = in_array($_POST['priority'] ?? '', ['Low', 'Normal', 'High', 'Urgent']) ? $_POST['priority'] : 'Normal';
    $type = in_array($_POST['announcement_type'] ?? '', ['General', 'System', 'Course', 'Urgent']) ? $_POST['announcement_type'] : 'General';
    $courseId = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;

    if ($title && $content) {
        $db->query(
            "INSERT INTO announcements (title, content, priority, announcement_type, course_id, posted_by, is_published, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())",
            [$title, $content, $priority, $type, $courseId, currentUserId()]
        );
        header('Location: ?page=announcements&msg=added');
        exit;
    }
}

// Edit announcement
if ($action === 'edit' && isset($_POST['announcement_id'])) {
    $id = (int)$_POST['announcement_id'];
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $priority = in_array($_POST['priority'] ?? '', ['Low', 'Normal', 'High', 'Urgent']) ? $_POST['priority'] : 'Normal';
    $type = in_array($_POST['announcement_type'] ?? '', ['General', 'System', 'Course', 'Urgent']) ? $_POST['announcement_type'] : 'General';
    $courseId = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;

    if ($title && $content) {
        $db->query(
            "UPDATE announcements SET title = ?, content = ?, priority = ?, announcement_type = ?, course_id = ?, updated_at = NOW() WHERE announcement_id = ?",
            [$title, $content, $priority, $type, $courseId, $id]
        );
        header('Location: ?page=announcements&msg=updated');
        exit;
    }
}

// Delete announcement
if ($action === 'delete' && isset($_POST['announcement_id'])) {
    $id = (int)$_POST['announcement_id'];
    $db->query("DELETE FROM announcements WHERE announcement_id = ?", [$id]);
    header('Location: ?page=announcements&msg=deleted');
    exit;
}

// Toggle publish
if ($action === 'toggle_publish' && isset($_POST['announcement_id'])) {
    $id = (int)$_POST['announcement_id'];
    $current = $db->fetchColumn("SELECT is_published FROM announcements WHERE announcement_id = ?", [$id]);
    $newStatus = $current ? 0 : 1;
    $db->query("UPDATE announcements SET is_published = ?, updated_at = NOW() WHERE announcement_id = ?", [$newStatus, $id]);
    header('Location: ?page=announcements&msg=toggle_publish');
    exit;
}
