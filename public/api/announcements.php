<?php
/**
 * Announcements API Endpoint
 * Handles system and course announcements
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
            // Get all announcements
            $sql = "SELECT
                        a.announcement_id as id,
                        a.course_id,
                        a.posted_by,
                        a.title,
                        a.content,
                        a.announcement_type as type,
                        a.priority,
                        a.is_published,
                        a.published_at,
                        a.expires_at,
                        a.created_at as date,
                        a.updated_at,
                        CONCAT(u.first_name, ' ', u.last_name) as author_name,
                        c.title as course_title
                    FROM announcements a
                    INNER JOIN users u ON a.posted_by = u.id
                    LEFT JOIN courses c ON a.course_id = c.id
                    ORDER BY a.created_at DESC";

            $announcements = $db->fetchAll($sql);

            echo json_encode([
                'success' => true,
                'data' => $announcements
            ]);
            break;

        case 'POST':
            // Create new announcement
            if (empty($input['title']) || empty($input['content'])) {
                throw new Exception('Title and content are required');
            }

            $announcementData = [
                'posted_by' => $_SESSION['user_id'],
                'title' => $input['title'],
                'content' => $input['content'],
                'announcement_type' => $input['type'] ?? 'General',
                'priority' => $input['priority'] ?? 'Normal',
                'is_published' => isset($input['is_published']) ? ($input['is_published'] ? 1 : 0) : 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (isset($input['course_id']) && $input['course_id'] > 0) {
                $announcementData['course_id'] = $input['course_id'];
            }

            if (isset($input['published_at'])) {
                $announcementData['published_at'] = $input['published_at'];
            } elseif ($announcementData['is_published']) {
                $announcementData['published_at'] = date('Y-m-d H:i:s');
            }

            if (isset($input['expires_at'])) {
                $announcementData['expires_at'] = $input['expires_at'];
            }

            $announcementId = $db->insert('announcements', $announcementData);

            echo json_encode([
                'success' => true,
                'message' => 'Announcement created successfully',
                'data' => ['id' => $announcementId]
            ]);
            break;

        case 'PUT':
            // Update announcement
            if (empty($input['id'])) {
                throw new Exception('Announcement ID is required');
            }

            $updateData = [];

            if (isset($input['title'])) {
                $updateData['title'] = $input['title'];
            }
            if (isset($input['content'])) {
                $updateData['content'] = $input['content'];
            }
            if (isset($input['type'])) {
                $updateData['announcement_type'] = $input['type'];
            }
            if (isset($input['priority'])) {
                $updateData['priority'] = $input['priority'];
            }
            if (isset($input['is_published'])) {
                $updateData['is_published'] = $input['is_published'] ? 1 : 0;
                if ($updateData['is_published'] && !isset($input['published_at'])) {
                    $updateData['published_at'] = date('Y-m-d H:i:s');
                }
            }
            if (isset($input['expires_at'])) {
                $updateData['expires_at'] = $input['expires_at'];
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $db->update('announcements', $updateData, 'announcement_id = ?', [$input['id']]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Announcement updated successfully'
            ]);
            break;

        case 'DELETE':
            // Delete announcement
            parse_str(file_get_contents('php://input'), $params);
            $announcementId = $params['id'] ?? $_GET['id'] ?? null;

            if (empty($announcementId)) {
                throw new Exception('Announcement ID is required');
            }

            $db->delete('announcements', 'announcement_id = ?', [$announcementId]);

            echo json_encode([
                'success' => true,
                'message' => 'Announcement deleted successfully'
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
