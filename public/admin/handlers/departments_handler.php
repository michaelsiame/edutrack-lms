<?php
/**
 * Departments Handler - Processes form submissions before HTML output
 * Includes: CSRF protection, Add, Edit, Delete (with course check), Toggle Status
 */

$action = $_POST['action'] ?? '';

// Verify CSRF token for all POST actions
if (!verifyCsrfToken()) {
    header('Location: ?page=departments&msg=csrf_error');
    exit;
}

// Add new department
if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $headOfDepartment = !empty($_POST['head_of_department']) ? (int)$_POST['head_of_department'] : null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($name && $code) {
        // Check for duplicate code
        $existing = $db->fetchOne("SELECT id FROM departments WHERE code = ?", [$code]);
        if ($existing) {
            header('Location: ?page=departments&msg=duplicate_code');
            exit;
        }

        $db->insert('departments', [
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'head_of_department' => $headOfDepartment,
            'is_active' => $isActive,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        header('Location: ?page=departments&msg=added');
        exit;
    }

    header('Location: ?page=departments&msg=validation_error');
    exit;
}

// Edit department
if ($action === 'edit' && isset($_POST['department_id'])) {
    $departmentId = (int)$_POST['department_id'];
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $headOfDepartment = !empty($_POST['head_of_department']) ? (int)$_POST['head_of_department'] : null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($name && $code) {
        // Check for duplicate code (excluding current department)
        $existing = $db->fetchOne("SELECT id FROM departments WHERE code = ? AND id != ?", [$code, $departmentId]);
        if ($existing) {
            header('Location: ?page=departments&msg=duplicate_code');
            exit;
        }

        $db->update('departments', [
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'head_of_department' => $headOfDepartment,
            'is_active' => $isActive,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$departmentId]);

        header('Location: ?page=departments&msg=updated');
        exit;
    }

    header('Location: ?page=departments&msg=validation_error');
    exit;
}

// Delete department
if ($action === 'delete' && isset($_POST['department_id'])) {
    $departmentId = (int)$_POST['department_id'];

    // Check if any courses reference this department
    $courseCount = $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE department_id = ?", [$departmentId]);
    if ($courseCount > 0) {
        header('Location: ?page=departments&msg=cannot_delete');
        exit;
    }

    $db->delete('departments', 'id = ?', [$departmentId]);
    header('Location: ?page=departments&msg=deleted');
    exit;
}

// Toggle department active status
if ($action === 'toggle_status' && isset($_POST['department_id'])) {
    $departmentId = (int)$_POST['department_id'];
    $department = $db->fetchOne("SELECT is_active FROM departments WHERE id = ?", [$departmentId]);

    if ($department) {
        $newStatus = ($department['is_active'] ?? 0) ? 0 : 1;
        $db->update('departments', [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$departmentId]);
    }

    header('Location: ?page=departments&msg=status_updated');
    exit;
}
