<?php
/**
 * Logs API Endpoint
 * Handles activity logs retrieval
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';

header('Content-Type: application/json');
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();

try {
    switch ($method) {
        case 'GET':
            // Get activity logs
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $sql = "SELECT
                        al.log_id as id,
                        al.user_id,
                        al.activity_type,
                        al.entity_type,
                        al.entity_id,
                        al.description,
                        al.ip_address,
                        al.user_agent,
                        al.created_at as timestamp,
                        CONCAT(u.first_name, ' ', u.last_name) as user_name,
                        u.email as user_email
                    FROM activity_logs al
                    LEFT JOIN users u ON al.user_id = u.id
                    ORDER BY al.created_at DESC
                    LIMIT ? OFFSET ?";

            $logs = $db->fetchAll($sql, [$limit, $offset]);

            // Get total count
            $totalCount = $db->count('activity_logs');

            echo json_encode([
                'success' => true,
                'data' => $logs,
                'meta' => [
                    'total' => $totalCount,
                    'limit' => $limit,
                    'offset' => $offset
                ]
            ]);
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
