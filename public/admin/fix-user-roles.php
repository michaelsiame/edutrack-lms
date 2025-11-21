<?php
/**
 * Fix User Roles - Web Accessible
 * Navigate to /admin/fix-user-roles.php to run this
 */

require_once '../../src/includes/config.php';
require_once '../../src/includes/database.php';

// Simple authentication - only allow if you know the secret key
$secret = $_GET['key'] ?? '';
if ($secret !== 'fix2024') {
    die('Access denied. Add ?key=fix2024 to URL');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix User Roles</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 User Roles Fix Script</h1>

    <?php
    try {
        $db = Database::getInstance();
        echo "<p class='success'>✓ Database connected successfully!</p>";

        // Check if roles exist
        echo "<h2>1. Checking Roles Table</h2>";
        $roles = $db->fetchAll("SELECT id, role_name FROM roles ORDER BY id");

        if (empty($roles)) {
            echo "<p class='error'>ERROR: No roles found! Seeding roles...</p>";

            // Insert roles manually
            $rolesToAdd = [
                ['Super Admin', 'Full system access and control', '{"all": true}'],
                ['Admin', 'Administrative access to manage system', '{"users": ["create", "read", "update", "delete"], "courses": ["create", "read", "update", "delete"], "reports": ["read"]}'],
                ['Instructor', 'Can create and manage courses', '{"courses": ["create", "read", "update"], "students": ["read"], "grades": ["create", "update"]}'],
                ['Student', 'Can enroll and access courses', '{"courses": ["read", "enroll"], "assignments": ["submit"], "quizzes": ["take"]}'],
                ['Content Creator', 'Can create course content', '{"courses": ["create", "read", "update"], "content": ["create", "update"]}']
            ];

            foreach ($rolesToAdd as $index => $roleData) {
                $db->query("INSERT INTO roles (id, role_name, description, permissions) VALUES (?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE role_name=role_name",
                           [$index + 1, $roleData[0], $roleData[1], $roleData[2]]);
            }

            echo "<p class='success'>✓ Roles seeded</p>";
            $roles = $db->fetchAll("SELECT id, role_name FROM roles ORDER BY id");
        }

        echo "<p>Found " . count($roles) . " roles:</p><ul>";
        foreach ($roles as $role) {
            echo "<li>{$role['role_name']} (ID: {$role['id']})</li>";
        }
        echo "</ul>";

        // Check users without roles
        echo "<h2>2. Checking Users Without Roles</h2>";
        $usersWithoutRoles = $db->fetchAll("
            SELECT u.id, u.email, u.first_name, u.last_name
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            WHERE ur.id IS NULL
        ");

        if (empty($usersWithoutRoles)) {
            echo "<p class='success'>✓ All users have roles assigned</p>";
        } else {
            echo "<p>Found " . count($usersWithoutRoles) . " users without roles:</p><ul>";

            foreach ($usersWithoutRoles as $user) {
                echo "<li><strong>{$user['email']}</strong> ({$user['first_name']} {$user['last_name']})";

                // Determine role based on email
                $roleName = 'Student'; // Default
                if (stripos($user['email'], 'admin') !== false) {
                    $roleName = 'Super Admin';
                } elseif (stripos($user['email'], 'instructor') !== false || stripos($user['email'], 'teacher') !== false) {
                    $roleName = 'Instructor';
                }

                // Get role ID
                $role = $db->fetchOne("SELECT id FROM roles WHERE role_name = ?", [$roleName]);

                if ($role) {
                    // Assign role
                    $db->insert('user_roles', [
                        'user_id' => $user['id'],
                        'role_id' => $role['id']
                    ]);
                    echo " <span class='success'>→ Assigned: {$roleName}</span>";
                } else {
                    echo " <span class='error'>→ ERROR: Role '{$roleName}' not found!</span>";
                }
                echo "</li>";
            }
            echo "</ul>";
        }

        // Show final summary
        echo "<h2>3. Final Summary</h2>";
        $summary = $db->fetchAll("
            SELECT r.role_name, COUNT(ur.user_id) as user_count
            FROM roles r
            LEFT JOIN user_roles ur ON r.id = ur.role_id
            GROUP BY r.id, r.role_name
            ORDER BY r.id
        ");

        echo "<ul>";
        foreach ($summary as $row) {
            echo "<li><strong>{$row['role_name']}:</strong> {$row['user_count']} users</li>";
        }
        echo "</ul>";

        echo "<h2 class='success'>✓ User Roles Fix Completed!</h2>";
        echo "<p><a href='" . url('admin/index.php') . "'>Go to Admin Dashboard</a> | <a href='" . url('login.php') . "'>Go to Login</a></p>";

    } catch (Exception $e) {
        echo "<p class='error'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    ?>
</div>
</body>
</html>
