<?php
/**
 * Users Handler - Processes form submissions before HTML output
 * Includes: CSRF protection, student ID generation, secure password handling
 */

$action = $_POST['action'] ?? '';

// Verify CSRF token for all POST actions
if (!verifyCsrfToken()) {
    header('Location: ?page=users&msg=csrf_error');
    exit;
}

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
    $db->delete('students', 'user_id = ?', [$userId]);
    $db->delete('user_profiles', 'user_id = ?', [$userId]);
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
    $address = trim($_POST['address'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Generate a random password if none provided, or use the provided one
    $rawPassword = trim($_POST['password'] ?? '');
    if (empty($rawPassword)) {
        $rawPassword = generateRandomPassword(12);
    }
    $password = password_hash($rawPassword, PASSWORD_DEFAULT);

    $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingUser) {
        header('Location: ?page=users&msg=email_exists');
        exit;
    }

    // Ensure username is unique
    $baseUsername = $username;
    $counter = 1;
    while ($db->fetchColumn("SELECT COUNT(*) FROM users WHERE username = ?", [$username]) > 0) {
        $username = $baseUsername . $counter;
        $counter++;
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

            // Create student record with student number if role is Student (4)
            if ($roleId == 4) {
                $studentNumber = generateStudentNumber($db);
                $db->insert('students', [
                    'user_id' => $newUserId,
                    'student_number' => $studentNumber,
                    'enrollment_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Create instructor record if role is Instructor (3)
            if ($roleId == 3) {
                $db->insert('instructors', [
                    'user_id' => $newUserId,
                    'is_verified' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Create user profile
            $db->insert('user_profiles', [
                'user_id' => $newUserId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Store the generated password in session so admin can see it once
        $_SESSION['last_generated_password'] = $rawPassword;
        $_SESSION['last_created_user'] = $firstName . ' ' . $lastName;

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
        $oldRoleId = $currentRole ? (int)$currentRole['role_id'] : 0;

        if ($currentRole) {
            $db->update('user_roles', ['role_id' => $roleId], 'user_id = ?', [$userId]);
        } else {
            $db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'assigned_at' => date('Y-m-d H:i:s')
            ]);
        }

        // If changed to Student role and no student record exists, create one
        if ($roleId == 4) {
            $existingStudent = $db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$userId]);
            if (!$existingStudent) {
                $studentNumber = generateStudentNumber($db);
                $db->insert('students', [
                    'user_id' => $userId,
                    'student_number' => $studentNumber,
                    'enrollment_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
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

// Reset password - generate random instead of hardcoded
if ($action === 'reset_password' && isset($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];
    $newPassword = generateRandomPassword(12);
    $db->update('users', ['password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)], 'id = ?', [$userId]);

    // Store in session so admin can share with user
    $_SESSION['last_reset_password'] = $newPassword;
    $user = $db->fetchOne("SELECT first_name, last_name FROM users WHERE id = ?", [$userId]);
    $_SESSION['last_reset_user'] = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');

    header('Location: ?page=users&msg=password_reset');
    exit;
}

// Bulk import users
if ($action === 'bulk_import' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    if ($file['error'] === UPLOAD_ERR_OK && pathinfo($file['name'], PATHINFO_EXTENSION) === 'csv') {
        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle); // Skip header row
        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) continue; // Need at least first_name, last_name, email

            $firstName = trim($row[0]);
            $lastName = trim($row[1]);
            $email = trim($row[2]);
            $phone = trim($row[3] ?? '');
            $roleName = trim($row[4] ?? 'Student');

            if (empty($firstName) || empty($lastName) || empty($email)) continue;
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email: {$email}";
                continue;
            }

            // Check duplicate
            if ($db->fetchColumn("SELECT COUNT(*) FROM users WHERE email = ?", [$email]) > 0) {
                $errors[] = "Duplicate email: {$email}";
                continue;
            }

            // Map role name to ID
            $roleMap = ['student' => 4, 'instructor' => 3, 'admin' => 2, 'super admin' => 1];
            $roleId = $roleMap[strtolower($roleName)] ?? 4;

            $username = strtolower($firstName . '.' . $lastName);
            $username = preg_replace('/[^a-z0-9._-]/', '', $username);
            $baseUsername = $username;
            $counter = 1;
            while ($db->fetchColumn("SELECT COUNT(*) FROM users WHERE username = ?", [$username]) > 0) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $rawPassword = generateRandomPassword(12);

            $db->insert('users', [
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => password_hash($rawPassword, PASSWORD_DEFAULT),
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

                if ($roleId == 4) {
                    $studentNumber = generateStudentNumber($db);
                    $db->insert('students', [
                        'user_id' => $newUserId,
                        'student_number' => $studentNumber,
                        'enrollment_date' => date('Y-m-d'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                $db->insert('user_profiles', [
                    'user_id' => $newUserId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $imported++;
            }
        }
        fclose($handle);

        $_SESSION['bulk_import_errors'] = $errors;
        header('Location: ?page=users&msg=bulk_imported&count=' . $imported);
        exit;
    } else {
        header('Location: ?page=users&msg=invalid_file');
        exit;
    }
}

// Export users to CSV
if ($action === 'export') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['First Name', 'Last Name', 'Email', 'Phone', 'Role', 'Student ID', 'Status', 'Joined']);

    $exportUsers = $db->fetchAll("
        SELECT u.first_name, u.last_name, u.email, u.phone, r.role_name, s.student_number, u.status, u.created_at
        FROM users u
        LEFT JOIN user_roles ur ON u.id = ur.user_id
        LEFT JOIN roles r ON ur.role_id = r.id
        LEFT JOIN students s ON u.id = s.user_id
        ORDER BY u.created_at DESC
    ");

    foreach ($exportUsers as $u) {
        fputcsv($output, [
            $u['first_name'],
            $u['last_name'],
            $u['email'],
            $u['phone'] ?? '',
            $u['role_name'] ?? 'No Role',
            $u['student_number'] ?? 'N/A',
            $u['status'],
            $u['created_at']
        ]);
    }
    fclose($output);
    exit;
}
