<?php
/**
 * Announcements API Endpoint
 * Uses Announcement class for database operations
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';
require_once '../../src/classes/Announcement.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            $announcements = Announcement::all();
            
            $formattedAnnouncements = array_map(function($a) {
                return [
                    'id' => $a['announcement_id'],
                    'course_id' => $a['course_id'],
                    'title' => $a['title'],
                    'content' => $a['content'],
                    'type' => $a['announcement_type'],
                    'priority' => $a['priority'],
                    'is_published' => (bool)$a['is_published'],
                    'date' => $a['created_at'],
                    'author_name' => $a['author_name'] ?? 'Unknown'
                ];
            }, $announcements);

            echo json_encode(['success' => true, 'data' => $formattedAnnouncements]);
            break;

        case 'POST':
            if (empty($input['title']) || empty($input['content'])) {
                throw new Exception('Title and content required');
            }

            $announcementId = Announcement::create([
                'posted_by' => $_SESSION['user_id'],
                'title' => $input['title'],
                'content' => $input['content'],
                'announcement_type' => $input['type'] ?? 'General',
                'priority' => $input['priority'] ?? 'Normal',
                'course_id' => $input['course_id'] ?? null,
                'is_published' => isset($input['is_published']) ? (int)$input['is_published'] : 1
            ]);

            echo json_encode(['success' => true, 'message' => 'Announcement created', 'data' => ['id' => $announcementId]]);
            break;

        case 'PUT':
            if (empty($input['id'])) throw new Exception('Announcement ID required');

            $announcement = Announcement::find($input['id']);
            if (!$announcement) throw new Exception('Announcement not found');

            $updateData = array_intersect_key($input, array_flip([
                'title', 'content', 'type', 'priority', 'is_published'
            ]));
            
            if (isset($updateData['type'])) {
                $updateData['announcement_type'] = $updateData['type'];
                unset($updateData['type']);
            }

            $announcement->update($updateData);
            echo json_encode(['success' => true, 'message' => 'Announcement updated']);
            break;

        case 'DELETE':
            parse_str(file_get_contents('php://input'), $params);
            $announcementId = $params['id'] ?? $_GET['id'] ?? null;
            if (!$announcementId) throw new Exception('Announcement ID required');

            Announcement::delete($announcementId);
            echo json_encode(['success' => true, 'message' => 'Announcement deleted']);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
