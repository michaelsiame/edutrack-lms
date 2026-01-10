<?php
/**
 * Users Handler - Processes form submissions before HTML output
 */

$action = $_POST['action'] ?? '';

// Update user status
if ($action === 'update_status' && isset($_POST['user_id'], $_POST['status'])) {
    $userId = (int)$_POST['user_id'];
    $status = $_POST['status'] === 'active' ? 'active' : 'suspended';
    $db->update('users', ['status' => $status], 'id = ?', [$userId]);
    header('Location: ?page=users&msg=status_updated');
    exit;
}

// Delete user
if ($action === 'delete' && isset($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];
    $enrollmentCount = $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE user_id = ?", [$userId]);
    if ($enrollmentCount > 0) {
        header('Location: ?page=users&msg=cannot_delete&reason=enrollments');
        exit;
    }
    $db->delete('user_roles', 'user_id = ?', [$userId]);
    $db->delete('users', 'id = ?', [$userId]);
    header('Location: ?page=users&msg=deleted');
    exit;
}

// Add new user
if ($action === 'add') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = strtolower(str_replace(' ', '.', $firstName . '.' . $lastName));
    $phone = trim($_POST['phone'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? 4);
    $password = password_hash($_POST['password'] ?? 'password123', PASSWORD_DEFAULT);
    $address = trim($_POST['address'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingUser) {
        header('Location: ?page=users&msg=email_exists');
        exit;
    }

    if ($firstName && $lastName && $email) {
        $db->insert('users', [
            'username' => $username,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => $password,
            'address' => $address,
            'bio' => $bio,
            'status' => 'active',
            'email_verified' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $newUserId = $db->lastInsertId();

        if ($newUserId) {
            $db->insert('user_roles', [
                'user_id' => $newUserId,
                'role_id' => $roleId,
                'assigned_at' => date('Y-m-d H:i:s')
            ]);

            if ($roleId == 3) {
                $db->insert('instructors', [
                    'user_id' => $newUserId,
                    'is_verified' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        header('Location: ?page=users&msg=added');
        exit;
    }
}

// Edit user
if ($action === 'edit' && isset($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? 4);
    $address = trim($_POST['address'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
    if ($existingUser) {
        header('Location: ?page=users&msg=email_exists');
        exit;
    }

    if ($firstName && $lastName && $email) {
        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'bio' => $bio,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($_POST['password'])) {
            $updateData['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $db->update('users', $updateData, 'id = ?', [$userId]);

        $currentRole = $db->fetchOne("SELECT role_id FROM user_roles WHERE user_id = ?", [$userId]);
        if ($currentRole) {
            $db->update('user_roles', ['role_id' => $roleId], 'user_id = ?', [$userId]);
        } else {
            $db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'assigned_at' => date('Y-m-d H:i:s')
            ]);
        }

        if ($roleId == 3) {
            $existingInstructor = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
            if (!$existingInstructor) {
                $db->insert('instructors', [
                    'user_id' => $userId,
                    'is_verified' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        header('Location: ?page=users&msg=updated');
        exit;
    }
}

// Reset password
if ($action === 'reset_password' && isset($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];
    $newPassword = 'password123';
    $db->update('users', ['password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)], 'id = ?', [$userId]);
    header('Location: ?page=users&msg=password_reset');
    exit;
}
