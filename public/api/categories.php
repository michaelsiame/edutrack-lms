<?php
/**
 * Categories API Endpoint
 * Handles course category management
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
            // Get all categories with course count
            $sql = "SELECT
                        cc.id,
                        cc.name,
                        cc.category_description as description,
                        cc.color,
                        cc.icon_url,
                        cc.display_order,
                        cc.is_active,
                        cc.parent_category_id,
                        COUNT(c.id) as count
                    FROM course_categories cc
                    LEFT JOIN courses c ON cc.id = c.category_id
                    GROUP BY cc.id
                    ORDER BY cc.display_order ASC, cc.name ASC";

            $categories = $db->fetchAll($sql);

            echo json_encode([
                'success' => true,
                'data' => $categories
            ]);
            break;

        case 'POST':
            // Create new category
            if (empty($input['name'])) {
                throw new Exception('Category name is required');
            }

            $categoryData = [
                'name' => $input['name'],
                'category_description' => $input['description'] ?? '',
                'color' => $input['color'] ?? '#333333',
                'is_active' => $input['is_active'] ?? 1,
                'display_order' => $input['display_order'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $categoryId = $db->insert('course_categories', $categoryData);

            echo json_encode([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => ['id' => $categoryId]
            ]);
            break;

        case 'PUT':
            // Update category
            if (empty($input['id'])) {
                throw new Exception('Category ID is required');
            }

            $updateData = [];

            if (isset($input['name'])) {
                $updateData['name'] = $input['name'];
            }
            if (isset($input['description'])) {
                $updateData['category_description'] = $input['description'];
            }
            if (isset($input['color'])) {
                $updateData['color'] = $input['color'];
            }
            if (isset($input['is_active'])) {
                $updateData['is_active'] = $input['is_active'] ? 1 : 0;
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $db->update('course_categories', $updateData, 'id = ?', [$input['id']]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
            break;

        case 'DELETE':
            // Delete category
            parse_str(file_get_contents('php://input'), $params);
            $categoryId = $params['id'] ?? $_GET['id'] ?? null;

            if (empty($categoryId)) {
                throw new Exception('Category ID is required');
            }

            // Check if category has courses
            $courseCount = $db->count('courses', 'category_id = ?', [$categoryId]);

            if ($courseCount > 0) {
                throw new Exception('Cannot delete category with associated courses. Please reassign courses first.');
            }

            $db->delete('course_categories', 'id = ?', [$categoryId]);

            echo json_encode([
                'success' => true,
                'message' => 'Category deleted successfully'
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
