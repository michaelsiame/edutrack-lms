<?php
/**
 * Global Search API
 * Provides live search results across courses, lessons, and users
 */

header('Content-Type: application/json');

require_once '../../src/bootstrap.php';

// Ensure user is authenticated
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $user = User::current();
    $userId = $user->getId();
    $userRole = $user->getRole();

    // Get search query
    $query = trim($_GET['q'] ?? '');

    if (strlen($query) < 2) {
        echo json_encode([
            'success' => true,
            'results' => [],
            'message' => 'Query too short'
        ]);
        exit;
    }

    // Sanitize query for LIKE search
    $searchTerm = '%' . $query . '%';
    $results = [];

    // Search courses (available to all users)
    $courseResults = $db->fetchAll("
        SELECT
            c.id,
            c.title,
            c.slug,
            c.description,
            CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
            'course' as type
        FROM courses c
        LEFT JOIN instructors i ON c.instructor_id = i.id
        LEFT JOIN users u ON i.user_id = u.id
        WHERE c.is_published = 1
        AND (
            c.title LIKE ?
            OR c.description LIKE ?
            OR c.category LIKE ?
        )
        LIMIT 10
    ", [$searchTerm, $searchTerm, $searchTerm]);

    foreach ($courseResults as $course) {
        $results[] = [
            'type' => 'course',
            'id' => $course['id'],
            'title' => $course['title'],
            'description' => substr(strip_tags($course['description'] ?? ''), 0, 100),
            'instructor' => $course['instructor_name'],
            'url' => url('course.php?slug=' . urlencode($course['slug'])),
            'icon' => 'fa-book'
        ];
    }

    // Search enrolled courses (student-specific)
    if ($userRole === 'student') {
        $myCourses = $db->fetchAll("
            SELECT
                c.id,
                c.title,
                c.slug,
                e.progress_percentage,
                'my-course' as type
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE e.user_id = ?
            AND (c.title LIKE ? OR c.description LIKE ?)
            LIMIT 5
        ", [$userId, $searchTerm, $searchTerm]);

        foreach ($myCourses as $course) {
            $results[] = [
                'type' => 'my-course',
                'id' => $course['id'],
                'title' => $course['title'],
                'description' => 'My Course - ' . round($course['progress_percentage']) . '% complete',
                'url' => url('learn.php?course=' . urlencode($course['slug'])),
                'icon' => 'fa-graduation-cap',
                'badge' => round($course['progress_percentage']) . '%'
            ];
        }

        // Search lessons in enrolled courses
        $lessonResults = $db->fetchAll("
            SELECT
                l.id,
                l.title,
                l.lesson_type,
                c.title as course_title,
                c.slug as course_slug,
                m.title as module_title,
                'lesson' as type
            FROM lessons l
            JOIN modules m ON l.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            WHERE l.title LIKE ?
            OR l.description LIKE ?
            LIMIT 10
        ", [$userId, $searchTerm, $searchTerm]);

        foreach ($lessonResults as $lesson) {
            $results[] = [
                'type' => 'lesson',
                'id' => $lesson['id'],
                'title' => $lesson['title'],
                'description' => $lesson['course_title'] . ' › ' . $lesson['module_title'],
                'url' => url('learn.php?course=' . urlencode($lesson['course_slug']) . '&lesson=' . $lesson['id']),
                'icon' => $lesson['lesson_type'] === 'video' ? 'fa-play-circle' : 'fa-file-alt'
            ];
        }
    }

    // Search for students (instructor/admin only)
    if (in_array($userRole, ['instructor', 'admin'])) {
        $studentResults = $db->fetchAll("
            SELECT
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) as name,
                u.email,
                'student' as type
            FROM users u
            WHERE u.role = 'student'
            AND (
                u.first_name LIKE ?
                OR u.last_name LIKE ?
                OR u.email LIKE ?
                OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?
            )
            LIMIT 5
        ", [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);

        foreach ($studentResults as $student) {
            $results[] = [
                'type' => 'student',
                'id' => $student['id'],
                'title' => $student['name'],
                'description' => $student['email'],
                'url' => url('admin/students/view.php?id=' . $student['id']),
                'icon' => 'fa-user'
            ];
        }
    }

    // Search for instructors (admin only)
    if ($userRole === 'admin') {
        $instructorResults = $db->fetchAll("
            SELECT
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) as name,
                u.email,
                COUNT(DISTINCT c.id) as course_count,
                'instructor' as type
            FROM users u
            JOIN instructors i ON u.id = i.user_id
            LEFT JOIN courses c ON i.id = c.instructor_id
            WHERE (
                u.first_name LIKE ?
                OR u.last_name LIKE ?
                OR u.email LIKE ?
                OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?
            )
            GROUP BY u.id, u.first_name, u.last_name, u.email
            LIMIT 5
        ", [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);

        foreach ($instructorResults as $instructor) {
            $results[] = [
                'type' => 'instructor',
                'id' => $instructor['id'],
                'title' => $instructor['name'],
                'description' => $instructor['course_count'] . ' courses',
                'url' => url('admin/instructors/view.php?id=' . $instructor['id']),
                'icon' => 'fa-chalkboard-teacher'
            ];
        }
    }

    // Limit total results
    $results = array_slice($results, 0, 15);

    echo json_encode([
        'success' => true,
        'results' => $results,
        'query' => $query,
        'count' => count($results)
    ]);

} catch (Exception $e) {
    error_log("Search API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Search failed',
        'error' => $e->getMessage()
    ]);
}
