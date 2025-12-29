<?php
/**
 * Role Switching API
 * Allows users with multiple roles to switch between them
 */

require_once '../../src/bootstrap.php';
require_once '../../src/classes/User.php';

header('Content-Type: application/json');

// Require authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = User::current();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($user);
            break;

        case 'POST':
            handlePost($user);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Role Switch API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}

/**
 * GET - Get user's available roles and current active role
 */
function handleGet($user) {
    $action = $_GET['action'] ?? 'roles';

    switch ($action) {
        case 'roles':
            $roles = $user->getAllRoles();
            $activeRole = $user->getRole();
            $hasMultiple = $user->hasMultipleRoles();

            echo json_encode([
                'success' => true,
                'roles' => $roles,
                'active_role' => $activeRole,
                'has_multiple_roles' => $hasMultiple,
                'active_role_details' => $user->getActiveRoleDetails()
            ]);
            break;

        case 'check':
            // Quick check if user has multiple roles
            echo json_encode([
                'success' => true,
                'has_multiple_roles' => $user->hasMultipleRoles(),
                'active_role' => $user->getRole()
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
}

/**
 * POST - Switch to a different role
 */
function handlePost($user) {
    $data = json_decode(file_get_contents('php://input'), true);
    $targetRole = $data['role'] ?? null;

    if (!$targetRole) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Target role is required']);
        exit;
    }

    // Attempt to switch role
    if ($user->switchRole($targetRole)) {
        // Log the role switch
        logActivity("User switched role to: {$targetRole}");

        // Get redirect URL based on new role
        $redirectUrl = getRedirectUrlForRole($targetRole);

        echo json_encode([
            'success' => true,
            'message' => 'Role switched successfully',
            'new_role' => $targetRole,
            'active_role_details' => $user->getActiveRoleDetails(),
            'redirect_url' => $redirectUrl
        ]);
    } else {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'You do not have permission to switch to this role'
        ]);
    }
}

/**
 * Get the appropriate dashboard URL for a role
 */
function getRedirectUrlForRole($role) {
    $role = strtolower($role);

    switch ($role) {
        case 'admin':
            return url('admin/');
        case 'instructor':
            return url('instructor/dashboard.php');
        case 'student':
        default:
            return url('student/dashboard.php');
    }
}
