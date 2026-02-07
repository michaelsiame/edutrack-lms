<?php
/**
 * Discussions API
 * List discussion threads for a course and create new threads or replies
 */

require_once '../../src/bootstrap.php';

header('Content-Type: application/json');
setCorsHeaders();

// Ensure user is authenticated
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// =====================================================
// GET - List discussion threads for a course
// =====================================================
if ($method === 'GET') {
    $courseId = $_GET['course_id'] ?? null;
    $discussionId = $_GET['discussion_id'] ?? null;

    // If fetching replies for a specific discussion
    if ($discussionId) {
        $discussion = new Discussion((int)$discussionId);
        if (!$discussion->getId()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Discussion not found']);
            exit;
        }

        // Increment view count
        $discussion->incrementViewCount();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 50)));
        $replies = $discussion->getReplies($page, $perPage);

        echo json_encode([
            'success' => true,
            'discussion' => $discussion->getData(),
            'replies' => $replies,
            'page' => $page,
            'per_page' => $perPage
        ]);
        exit;
    }

    // List threads for a course
    if (!$courseId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'course_id is required']);
        exit;
    }

    $courseId = (int)$courseId;

    // Verify enrollment (admins/instructors can bypass)
    $userRoles = $_SESSION['user_roles'] ?? [];
    $isAdmin = in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles);
    $isInstructor = in_array('Instructor', $userRoles);

    if (!$isAdmin && !$isInstructor) {
        $db = Database::getInstance();
        $enrolled = $db->fetchOne(
            "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? AND enrollment_status IN ('Enrolled', 'In Progress', 'Completed')",
            [$userId, $courseId]
        );
        if (!$enrolled) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You must be enrolled in this course to view discussions']);
            exit;
        }
    }

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 20)));

    $threads = Discussion::getByCourse($courseId, $page, $perPage);
    $totalCount = Discussion::getCount($courseId);
    $totalPages = ceil($totalCount / $perPage);

    echo json_encode([
        'success' => true,
        'threads' => $threads,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $totalCount,
            'total_pages' => $totalPages
        ]
    ]);
    exit;
}

// =====================================================
// POST - Create a new thread or reply
// =====================================================
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }

    $action = $input['action'] ?? 'create_thread';

    if ($action === 'reply') {
        // Add a reply to an existing discussion
        $discussionId = $input['discussion_id'] ?? null;
        $content = trim($input['content'] ?? '');

        if (!$discussionId || empty($content)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'discussion_id and content are required']);
            exit;
        }

        $discussion = new Discussion((int)$discussionId);
        if (!$discussion->getId()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Discussion not found']);
            exit;
        }

        if ($discussion->isLocked()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'This discussion is locked']);
            exit;
        }

        // Check if user is an instructor for this course
        $userRoles = $_SESSION['user_roles'] ?? [];
        $isInstructor = in_array('Instructor', $userRoles);

        try {
            $replyId = $discussion->addReply([
                'user_id' => $userId,
                'content' => $content,
                'parent_reply_id' => $input['parent_reply_id'] ?? null,
                'is_instructor_reply' => $isInstructor ? 1 : 0
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Reply posted successfully',
                'reply_id' => $replyId
            ]);
        } catch (Exception $e) {
            error_log("Discussion reply error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to post reply']);
        }
        exit;
    }

    // Default: create a new thread
    $courseId = $input['course_id'] ?? null;
    $title = trim($input['title'] ?? '');
    $content = trim($input['content'] ?? '');

    if (!$courseId || empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'course_id, title, and content are required']);
        exit;
    }

    $courseId = (int)$courseId;

    // Verify enrollment (admins/instructors can bypass)
    $userRoles = $_SESSION['user_roles'] ?? [];
    $isAdmin = in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles);
    $isInstructor = in_array('Instructor', $userRoles);

    if (!$isAdmin && !$isInstructor) {
        $db = Database::getInstance();
        $enrolled = $db->fetchOne(
            "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? AND enrollment_status IN ('Enrolled', 'In Progress', 'Completed')",
            [$userId, $courseId]
        );
        if (!$enrolled) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You must be enrolled in this course to create discussions']);
            exit;
        }
    }

    try {
        $discussionId = Discussion::create([
            'course_id' => $courseId,
            'created_by' => $userId,
            'title' => $title,
            'content' => $content,
            'is_pinned' => ($isAdmin || $isInstructor) ? ($input['is_pinned'] ?? 0) : 0,
            'is_locked' => ($isAdmin || $isInstructor) ? ($input['is_locked'] ?? 0) : 0
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Discussion thread created successfully',
            'discussion_id' => $discussionId
        ]);
    } catch (InvalidArgumentException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } catch (Exception $e) {
        error_log("Discussion creation error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create discussion']);
    }
    exit;
}

// Invalid method
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
