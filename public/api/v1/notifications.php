<?php
/**
 * Notifications API
 * Handles notification operations
 */

require_once '../../src/bootstrap.php';
require_once '../../src/classes/Notification.php';

header('Content-Type: application/json');

// Require authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = currentUserId();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($userId);
            break;

        case 'POST':
            handlePost($userId);
            break;

        case 'PUT':
        case 'PATCH':
            handlePut($userId);
            break;

        case 'DELETE':
            handleDelete($userId);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Notifications API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}

/**
 * GET - Retrieve notifications
 */
function handleGet($userId) {
    $action = $_GET['action'] ?? 'list';

    switch ($action) {
        case 'list':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] == '1';

            $notifications = Notification::getByUser($userId, [
                'limit' => $limit,
                'offset' => $offset,
                'unread_only' => $unreadOnly
            ]);

            $unreadCount = Notification::getUnreadCount($userId);

            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
                'total_returned' => count($notifications)
            ]);
            break;

        case 'unread_count':
            $count = Notification::getUnreadCount($userId);

            echo json_encode([
                'success' => true,
                'unread_count' => $count
            ]);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Notification ID required']);
                exit;
            }

            $notification = Notification::find($id);

            if (!$notification) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Notification not found']);
                exit;
            }

            // Check ownership
            if ($notification->getUserId() != $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'notification' => [
                    'id' => $notification->getId(),
                    'type' => $notification->getType(),
                    'title' => $notification->getTitle(),
                    'message' => $notification->getMessage(),
                    'link' => $notification->getLink(),
                    'icon' => $notification->getIcon(),
                    'color' => $notification->getColor(),
                    'is_read' => $notification->isRead(),
                    'created_at' => $notification->getCreatedAt(),
                    'time_ago' => $notification->getTimeAgo()
                ]
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
}

/**
 * POST - Create notification or perform action
 */
function handlePost($userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? null;

    if (!$action) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Action required']);
        exit;
    }

    switch ($action) {
        case 'mark_as_read':
            $id = $data['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Notification ID required']);
                exit;
            }

            $notification = Notification::find($id);

            if (!$notification) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Notification not found']);
                exit;
            }

            // Check ownership
            if ($notification->getUserId() != $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied']);
                exit;
            }

            if ($notification->markAsRead()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to mark as read']);
            }
            break;

        case 'mark_all_as_read':
            if (Notification::markAllAsRead($userId)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'All notifications marked as read'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to mark all as read']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
}

/**
 * PUT/PATCH - Update notification
 */
function handlePut($userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Notification ID required']);
        exit;
    }

    $notification = Notification::find($id);

    if (!$notification) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Notification not found']);
        exit;
    }

    // Check ownership
    if ($notification->getUserId() != $userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }

    // Mark as read (primary update operation)
    if ($notification->markAsRead()) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification updated'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update notification']);
    }
}

/**
 * DELETE - Delete notification
 */
function handleDelete($userId) {
    // Parse DELETE request data
    parse_str(file_get_contents('php://input'), $data);

    if (empty($data)) {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    $id = $data['id'] ?? $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Notification ID required']);
        exit;
    }

    $notification = Notification::find($id);

    if (!$notification) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Notification not found']);
        exit;
    }

    // Check ownership
    if ($notification->getUserId() != $userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }

    if ($notification->delete()) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete notification']);
    }
}
