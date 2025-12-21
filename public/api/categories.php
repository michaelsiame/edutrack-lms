<?php
/**
 * Categories API Endpoint
 * Uses Category class for database operations
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';
require_once '../../src/classes/Category.php';

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
            $categories = Category::all();
            
            $formattedCategories = array_map(function($cat) {
                return [
                    'id' => $cat['id'],
                    'name' => $cat['name'],
                    'description' => $cat['category_description'] ?? '',
                    'color' => $cat['color'] ?? '#333333',
                    'count' => $cat['course_count'] ?? 0,
                    'is_active' => (bool)$cat['is_active']
                ];
            }, $categories);

            echo json_encode(['success' => true, 'data' => $formattedCategories]);
            break;

        case 'POST':
            if (empty($input['name'])) throw new Exception('Category name required');

            $categoryId = Category::create([
                'name' => $input['name'],
                'category_description' => $input['description'] ?? '',
                'color' => $input['color'] ?? '#333333'
            ]);

            echo json_encode(['success' => true, 'message' => 'Category created', 'data' => ['id' => $categoryId]]);
            break;

        case 'PUT':
            if (empty($input['id'])) throw new Exception('Category ID required');

            $category = Category::find($input['id']);
            if (!$category) throw new Exception('Category not found');

            $updateData = array_intersect_key($input, array_flip(['name', 'description', 'color']));
            if (isset($input['description'])) {
                $updateData['category_description'] = $input['description'];
                unset($updateData['description']);
            }

            $category->update($updateData);
            echo json_encode(['success' => true, 'message' => 'Category updated']);
            break;

        case 'DELETE':
            parse_str(file_get_contents('php://input'), $params);
            $categoryId = $params['id'] ?? $_GET['id'] ?? null;
            if (!$categoryId) throw new Exception('Category ID required');

            Category::delete($categoryId);
            echo json_encode(['success' => true, 'message' => 'Category deleted']);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
