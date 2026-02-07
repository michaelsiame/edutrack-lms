<?php
/**
 * Users API Endpoint
 * Uses User class for database operations
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';
require_once '../../src/classes/User.php';

header('Content-Type: application/json');
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$db = Database::getInstance();

try {
    switch ($method) {
        case 'GET':
            // Get all users with roles
            $db = Database::getInstance();
            $users = $db->fetchAll("
                SELECT
                    u.id, u.username, u.email, u.first_name, u.last_name,
                    u.status, u.created_at,
                    GROUP_CONCAT(r.role_name SEPARATOR ', ') as role_name
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                GROUP BY u.id
                ORDER BY u.created_at DESC
            ");

            echo json_encode(['success' => true, 'data' => $users]);
            break;

        case 'POST':
            // Create new user
            if (empty($input['email']) || empty($input['password'])) {
                throw new Exception('Email and password required');
            }

            // Check if email exists
            if (User::findByEmail($input['email'])) {
                throw new Exception('Email already exists');
            }

            // Parse name
            $nameParts = explode(' ', $input['name'] ?? '');
            $firstName = $nameParts[0] ?? '';
            $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';

            // Create user using registerUser function (sends welcome email automatically)
            $result = registerUser([
                'email' => $input['email'],
                'password' => $input['password'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'send_welcome_email' => true
            ]);

            if ($result['success']) {
                // Assign role
                $db = Database::getInstance();
                $roleMap = ['Admin' => 1, 'Instructor' => 2, 'Student' => 3];
                $roleId = $roleMap[$input['role'] ?? 'Student'] ?? 3;

                $db->insert('user_roles', [
                    'user_id' => $result['user_id'],
                    'role_id' => $roleId,
                    'assigned_at' => date('Y-m-d H:i:s')
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'User created successfully',
                    'data' => ['id' => $result['user_id']]
                ]);
            } else {
                throw new Exception($result['message'] ?? 'Failed to create user');
            }
            break;

        case 'PUT':
            // Update user
            if (empty($input['id'])) throw new Exception('User ID required');

            $user = User::find($input['id']);
            if (!$user->exists()) throw new Exception('User not found');

            $db = Database::getInstance();
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
                $db->update('users', $updateData, 'id = ?', [$input['id']]);
            }

            // Update role if provided
            if (isset($input['role'])) {
                $roleMap = ['Admin' => 1, 'Instructor' => 2, 'Student' => 3];
                $roleId = $roleMap[$input['role']] ?? 3;

                $db->delete('user_roles', 'user_id = ?', [$input['id']]);
                $db->insert('user_roles', [
                    'user_id' => $input['id'],
                    'role_id' => $roleId,
                    'assigned_at' => date('Y-m-d H:i:s')
                ]);
            }

            echo json_encode(['success' => true, 'message' => 'User updated']);
            break;

        case 'DELETE':
            parse_str(file_get_contents('php://input'), $params);
            $userId = $params['id'] ?? $_GET['id'] ?? null;
            if (!$userId) throw new Exception('User ID required');

            // Don't allow self-deletion
            if ($userId == $_SESSION['user_id']) {
                throw new Exception('Cannot delete your own account');
            }

            $db = Database::getInstance();
            $db->beginTransaction();
            $db->delete('user_roles', 'user_id = ?', [$userId]);
            $db->delete('users', 'id = ?', [$userId]);
            $db->commit();

            echo json_encode(['success' => true, 'message' => 'User deleted']);
            break;
    }
} catch (Exception $e) {
    if (isset($db) && $db->getConnection()->inTransaction()) {
        $db->rollback();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
