<?php
/**
 * API: Courses
 * GET /api/courses.php - List/search courses
 */

header('Content-Type: application/json');

require_once '../../src/includes/config.php';
require_once '../../src/includes/database.php';
require_once '../../src/includes/functions.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Category.php';

// GET: List courses
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 12)));
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $level = $_GET['level'] ?? '';
    $price = $_GET['price'] ?? '';
    $sort = $_GET['sort'] ?? 'newest';
    
    // Build options
    $options = [
        'status' => 'published',
        'page' => $page,
        'per_page' => $perPage
    ];
    
    if ($search) {
        $options['search'] = $search;
    }
    
    if ($category) {
        $options['category_id'] = $category;
    }
    
    if ($level) {
        $options['level'] = $level;
    }
    
    if ($price === 'free') {
        $options['free_only'] = true;
    } elseif ($price === 'paid') {
        $options['paid_only'] = true;
    }
    
    // Sort
    switch ($sort) {
        case 'popular':
            $options['order_by'] = 'total_students';
            $options['order_dir'] = 'DESC';
            break;
        case 'rating':
            $options['order_by'] = 'avg_rating';
            $options['order_dir'] = 'DESC';
            break;
        case 'price_low':
            $options['order_by'] = 'price';
            $options['order_dir'] = 'ASC';
            break;
        case 'price_high':
            $options['order_by'] = 'price';
            $options['order_dir'] = 'DESC';
            break;
        case 'newest':
        default:
            $options['order_by'] = 'created_at';
            $options['order_dir'] = 'DESC';
            break;
    }
    
    // Get courses
    $courses = Course::all($options);
    
    // Get total count for pagination
    $totalOptions = $options;
    unset($totalOptions['page']);
    unset($totalOptions['per_page']);
    unset($totalOptions['order_by']);
    unset($totalOptions['order_dir']);
    
    $total = Course::count($totalOptions);
    $totalPages = ceil($total / $perPage);
    
    // Format courses for API
    $coursesFormatted = array_map(function($course) {
        return [
            'id' => $course['id'],
            'title' => $course['title'],
            'slug' => $course['slug'],
            'short_description' => $course['short_description'],
            'thumbnail' => $course['thumbnail'] ? uploadUrl($course['thumbnail']) : null,
            'category' => [
                'id' => $course['category_id'],
                'name' => $course['category_name'] ?? ''
            ],
            'instructor' => [
                'id' => $course['instructor_id'],
                'name' => ($course['instructor_first_name'] ?? '') . ' ' . ($course['instructor_last_name'] ?? '')
            ],
            'level' => $course['level'],
            'language' => $course['language'],
            'price' => (float)$course['price'],
            'is_free' => $course['price'] == 0,
            'duration_hours' => $course['duration_hours'],
            'total_students' => $course['total_students'] ?? 0,
            'total_lessons' => $course['total_lessons'] ?? 0,
            'avg_rating' => round($course['avg_rating'] ?? 0, 1),
            'total_reviews' => $course['total_reviews'] ?? 0,
            'is_teveta' => (bool)$course['is_teveta'],
            'has_certificate' => (bool)$course['has_certificate'],
            'created_at' => $course['created_at']
        ];
    }, $courses);
    
    // Get categories for filter
    $categories = Category::getActiveWithCourses();
    
    echo json_encode([
        'success' => true,
        'courses' => $coursesFormatted,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages
        ],
        'filters' => [
            'categories' => array_map(function($cat) {
                return [
                    'id' => $cat['id'],
                    'name' => $cat['name'],
                    'slug' => $cat['slug'],
                    'course_count' => $cat['course_count']
                ];
            }, $categories),
            'levels' => ['beginner', 'intermediate', 'advanced'],
            'price_options' => ['all', 'free', 'paid']
        ]
    ]);
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);