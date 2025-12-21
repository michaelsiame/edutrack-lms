<?php
/**
 * Courses API Endpoint
 * Handles CRUD operations for course management
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

// Check if this is a request for course modules or lessons
$pathInfo = $_SERVER['PATH_INFO'] ?? '';
$segments = explode('/', trim($pathInfo, '/'));

try {
    // Handle nested resources (modules, lessons)
    if (count($segments) >= 2 && is_numeric($segments[0])) {
        $courseId = (int)$segments[0];

        if ($segments[1] === 'modules') {
            // Get modules for a course
            $modules = $db->fetchAll(
                "SELECT * FROM modules WHERE course_id = ? ORDER BY display_order ASC",
                [$courseId]
            );

            echo json_encode([
                'success' => true,
                'data' => $modules
            ]);
            exit;
        }
    }

    if (count($segments) >= 2 && $segments[0] === 'modules' && is_numeric($segments[1])) {
        $moduleId = (int)$segments[1];

        if (isset($segments[2]) && $segments[2] === 'lessons') {
            // Get lessons for a module
            $lessons = $db->fetchAll(
                "SELECT * FROM lessons WHERE module_id = ? ORDER BY lesson_order ASC",
                [$moduleId]
            );

            echo json_encode([
                'success' => true,
                'data' => $lessons
            ]);
            exit;
        }
    }

    // Standard CRUD operations
    switch ($method) {
        case 'GET':
            // Get all courses with instructor and category info
            $sql = "SELECT
                        c.id,
                        c.title,
                        c.slug,
                        c.description,
                        c.short_description,
                        c.category_id,
                        c.instructor_id,
                        c.level,
                        c.language,
                        c.thumbnail_url,
                        c.video_intro_url,
                        c.start_date,
                        c.end_date,
                        c.price,
                        c.discount_price,
                        c.duration_weeks,
                        c.total_hours,
                        c.max_students,
                        c.enrollment_count,
                        c.status,
                        c.is_featured,
                        c.rating,
                        c.total_reviews,
                        c.created_at,
                        c.updated_at,
                        CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
                        cat.name as category_name
                    FROM courses c
                    LEFT JOIN users u ON c.instructor_id = u.id
                    LEFT JOIN course_categories cat ON c.category_id = cat.id
                    ORDER BY c.created_at DESC";

            $courses = $db->fetchAll($sql);

            echo json_encode([
                'success' => true,
                'data' => $courses
            ]);
            break;

        case 'POST':
            // Create new course
            if (empty($input['title'])) {
                throw new Exception('Course title is required');
            }

            // Generate slug from title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['title'])));

            $courseData = [
                'title' => $input['title'],
                'slug' => $slug,
                'description' => $input['description'] ?? '',
                'short_description' => $input['short_description'] ?? substr($input['description'] ?? '', 0, 200),
                'category_id' => $input['category_id'] ?? 1,
                'instructor_id' => $input['instructor_id'] ?? $_SESSION['user_id'],
                'level' => $input['level'] ?? 'Beginner',
                'language' => $input['language'] ?? 'English',
                'price' => $input['price'] ?? 0,
                'status' => $input['status'] ?? 'draft',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (isset($input['start_date'])) {
                $courseData['start_date'] = $input['start_date'];
            }
            if (isset($input['end_date'])) {
                $courseData['end_date'] = $input['end_date'];
            }

            $courseId = $db->insert('courses', $courseData);

            echo json_encode([
                'success' => true,
                'message' => 'Course created successfully',
                'data' => ['id' => $courseId]
            ]);
            break;

        case 'PUT':
            // Update course
            if (empty($input['id'])) {
                throw new Exception('Course ID is required');
            }

            $courseId = $input['id'];
            $updateData = [];

            $allowedFields = [
                'title', 'description', 'short_description', 'category_id',
                'instructor_id', 'level', 'language', 'price', 'discount_price',
                'start_date', 'end_date', 'status', 'is_featured', 'max_students'
            ];

            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updateData[$field] = $input[$field];
                }
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $db->update('courses', $updateData, 'id = ?', [$courseId]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Course updated successfully'
            ]);
            break;

        case 'DELETE':
            // Delete course
            parse_str(file_get_contents('php://input'), $params);
            $courseId = $params['id'] ?? $_GET['id'] ?? null;

            if (empty($courseId)) {
                throw new Exception('Course ID is required');
            }

            $db->beginTransaction();

            // Delete related data
            $db->delete('modules', 'course_id = ?', [$courseId]);
            $db->delete('enrollments', 'course_id = ?', [$courseId]);
            $db->delete('course_reviews', 'course_id = ?', [$courseId]);

            // Delete course
            $db->delete('courses', 'id = ?', [$courseId]);

            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Course deleted successfully'
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
