<?php
/**
 * Users API Endpoint
 * Handles CRUD operations for user management
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            // Get all users with their roles
            $sql = "SELECT
                        u.id,
                        u.username,
                        u.email,
                        u.first_name,
                        u.last_name,
                        u.status,
                        u.created_at,
                        GROUP_CONCAT(r.name SEPARATOR ', ') as role_name
                    FROM users u
                    LEFT JOIN user_roles ur ON u.id = ur.user_id
                    LEFT JOIN roles r ON ur.role_id = r.id
                    GROUP BY u.id
                    ORDER BY u.created_at DESC";

            $users = $db->fetchAll($sql);

            echo json_encode([
                'success' => true,
                'data' => $users
            ]);
            break;

        case 'POST':
            // Create new user
            if (empty($input['email']) || empty($input['password'])) {
                throw new Exception('Email and password are required');
            }

            // Check if email already exists
            if ($db->exists('users', 'email = ?', [$input['email']])) {
                throw new Exception('Email already exists');
            }

            // Parse name from input
            $nameParts = explode(' ', $input['name'] ?? '');
            $firstName = $nameParts[0] ?? '';
            $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';

            $db->beginTransaction();

            // Insert user
            $userId = $db->insert('users', [
                'username' => $input['email'],
                'email' => $input['email'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
                'status' => $input['status'] ?? 'Active',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Assign role
            $roleMap = [
                'Admin' => 1,
                'Instructor' => 2,
                'Student' => 3
            ];
            $roleId = $roleMap[$input['role'] ?? 'Student'] ?? 3;

            $db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'assigned_at' => date('Y-m-d H:i:s')
            ]);

            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'data' => ['id' => $userId]
            ]);
            break;

        case 'PUT':
            // Update user
            if (empty($input['id'])) {
                throw new Exception('User ID is required');
            }

            $userId = $input['id'];
            $updateData = [];

            // Parse name if provided
            if (isset($input['name'])) {
                $nameParts = explode(' ', $input['name']);
                $updateData['first_name'] = $nameParts[0] ?? '';
                $updateData['last_name'] = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
            }

            if (isset($input['email'])) {
                $updateData['email'] = $input['email'];
                $updateData['username'] = $input['email'];
            }

            if (isset($input['status'])) {
                $updateData['status'] = $input['status'];
            }

            if (!empty($updateData)) {
                $db->update('users', $updateData, 'id = ?', [$userId]);
            }

            // Update role if provided
            if (isset($input['role'])) {
                $roleMap = [
                    'Admin' => 1,
                    'Instructor' => 2,
                    'Student' => 3
                ];
                $roleId = $roleMap[$input['role']] ?? 3;

                // Delete existing roles
                $db->delete('user_roles', 'user_id = ?', [$userId]);

                // Assign new role
                $db->insert('user_roles', [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'assigned_at' => date('Y-m-d H:i:s')
                ]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
            break;

        case 'DELETE':
            // Delete user
            parse_str(file_get_contents('php://input'), $params);
            $userId = $params['id'] ?? $_GET['id'] ?? null;

            if (empty($userId)) {
                throw new Exception('User ID is required');
            }

            // Don't allow deleting yourself
            if ($userId == $_SESSION['user_id']) {
                throw new Exception('Cannot delete your own account');
            }

            $db->beginTransaction();

            // Delete user roles
            $db->delete('user_roles', 'user_id = ?', [$userId]);

            // Delete user
            $db->delete('users', 'id = ?', [$userId]);

            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    if ($db->getConnection()->inTransaction()) {
        $db->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
