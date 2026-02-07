<?php
/**
 * Courses API Endpoint
 * Uses Course class for database operations
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Module.php';
require_once '../../src/classes/Lesson.php';

header('Content-Type: application/json');
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$pathInfo = $_SERVER['PATH_INFO'] ?? '';
$segments = explode('/', trim($pathInfo, '/'));

try {
    // Handle nested resources
    if (count($segments) >= 2 && is_numeric($segments[0])) {
        $courseId = (int)$segments[0];
        if ($segments[1] === 'modules') {
            $modules = Module::getByCourse($courseId);
            echo json_encode(['success' => true, 'data' => $modules]);
            exit;
        }
    }

    if (count($segments) >= 2 && $segments[0] === 'modules' && is_numeric($segments[1])) {
        $moduleId = (int)$segments[1];
        if (isset($segments[2]) && $segments[2] === 'lessons') {
            $lessons = Lesson::getByModule($moduleId);
            echo json_encode(['success' => true, 'data' => $lessons]);
            exit;
        }
    }

    switch ($method) {
        case 'GET':
            $filters = ['include_draft' => true];
            if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
            if (isset($_GET['category_id'])) $filters['category_id'] = (int)$_GET['category_id'];

            $courses = Course::all($filters);
            $formattedCourses = array_map(function($course) {
                return [
                    'id' => $course['id'],
                    'title' => $course['title'],
                    'slug' => $course['slug'],
                    'description' => $course['description'],
                    'short_description' => $course['short_description'],
                    'category_id' => $course['category_id'],
                    'instructor_id' => $course['instructor_id'],
                    'level' => $course['level'],
                    'price' => (float)$course['price'],
                    'status' => $course['status'],
                    'enrollment_count' => $course['total_students'] ?? 0,
                    'start_date' => $course['start_date'],
                    'end_date' => $course['end_date'],
                    'instructor_name' => trim(($course['instructor_first_name'] ?? '') . ' ' . ($course['instructor_last_name'] ?? '')),
                    'category_name' => $course['category_name'] ?? 'Uncategorized'
                ];
            }, $courses);

            echo json_encode(['success' => true, 'data' => $formattedCourses]);
            break;

        case 'POST':
            if (empty($input['title'])) throw new Exception('Course title is required');
            
            $courseId = Course::create([
                'title' => $input['title'],
                'description' => $input['description'] ?? '',
                'category_id' => $input['category_id'] ?? 1,
                'instructor_id' => $input['instructor_id'] ?? $_SESSION['user_id'],
                'level' => $input['level'] ?? 'Beginner',
                'price' => $input['price'] ?? 0,
                'status' => $input['status'] ?? 'draft'
            ]);

            echo json_encode(['success' => true, 'message' => 'Course created', 'data' => ['id' => $courseId]]);
            break;

        case 'PUT':
            if (empty($input['id'])) throw new Exception('Course ID required');
            
            $course = Course::find($input['id']);
            if (!$course) throw new Exception('Course not found');

            $updateData = array_intersect_key($input, array_flip([
                'title', 'description', 'category_id', 'instructor_id', 'level', 
                'price', 'status', 'start_date', 'end_date'
            ]));

            Course::update($input['id'], $updateData);
            echo json_encode(['success' => true, 'message' => 'Course updated']);
            break;

        case 'DELETE':
            parse_str(file_get_contents('php://input'), $params);
            $courseId = $params['id'] ?? $_GET['id'] ?? null;
            if (!$courseId) throw new Exception('Course ID required');

            Course::delete($courseId);
            echo json_encode(['success' => true, 'message' => 'Course deleted']);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
