<?php
/**
 * Fix User Roles Script
 * Ensures all users have roles assigned
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/includes/config.php';
require_once __DIR__ . '/../src/includes/database.php';

try {
    echo "=== User Roles Fix Script ===\n\n";
    echo "Attempting to connect to database...\n";

    $db = Database::getInstance();
    echo "✓ Database connected successfully!\n\n";

    // Check if roles exist
    echo "1. Checking roles table...\n";
    $roles = $db->fetchAll("SELECT role_id, role_name FROM Roles ORDER BY role_id");

    if (empty($roles)) {
        echo "   ERROR: No roles found! Running seed data migration...\n";
        $seedSQL = file_get_contents(__DIR__ . '/migrations/002_seed_data.sql');
        $db->query($seedSQL);
        echo "   ✓ Roles seeded\n";
        $roles = $db->fetchAll("SELECT role_id, role_name FROM Roles ORDER BY role_id");
    }

    echo "   Found " . count($roles) . " roles:\n";
    foreach ($roles as $role) {
        echo "   - {$role['role_name']} (ID: {$role['role_id']})\n";
    }
    echo "\n";

    // Check users without roles
    echo "2. Checking users without roles...\n";
    $usersWithoutRoles = $db->fetchAll("
        SELECT u.user_id, u.email, u.first_name, u.last_name
        FROM Users u
        LEFT JOIN User_Roles ur ON u.user_id = ur.user_id
        WHERE ur.user_role_id IS NULL
    ");

    if (empty($usersWithoutRoles)) {
        echo "   ✓ All users have roles assigned\n\n";
    } else {
        echo "   Found " . count($usersWithoutRoles) . " users without roles:\n";

        foreach ($usersWithoutRoles as $user) {
            echo "   - {$user['email']} ({$user['first_name']} {$user['last_name']})\n";

            // Determine role based on email
            $roleName = 'Student'; // Default
            if (stripos($user['email'], 'admin') !== false) {
                $roleName = 'Super Admin';
            } elseif (stripos($user['email'], 'instructor') !== false || stripos($user['email'], 'teacher') !== false) {
                $roleName = 'Instructor';
            }

            // Get role ID
            $role = $db->fetchOne("SELECT role_id FROM Roles WHERE role_name = ?", [$roleName]);

            if ($role) {
                // Assign role
                $db->insert('User_Roles', [
                    'user_id' => $user['user_id'],
                    'role_id' => $role['role_id']
                ]);
                echo "     ✓ Assigned role: {$roleName}\n";
            } else {
                echo "     ERROR: Role '{$roleName}' not found!\n";
            }
        }
        echo "\n";
    }

    // Show final summary
    echo "3. Final Summary:\n";
    $summary = $db->fetchAll("
        SELECT r.role_name, COUNT(ur.user_id) as user_count
        FROM Roles r
        LEFT JOIN User_Roles ur ON r.role_id = ur.role_id
        GROUP BY r.role_id, r.role_name
        ORDER BY r.role_id
    ");

    foreach ($summary as $row) {
        echo "   - {$row['role_name']}: {$row['user_count']} users\n";
    }

    echo "\n✓ User roles fix completed!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
