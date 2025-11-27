<?php
/**
 * Live Sessions API
 * Manage live session scheduling, joining, and attendance
 */

require_once '../../src/bootstrap.php';

header('Content-Type: application/json');

// Ensure user is authenticated
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = User::current();
$userId = $user->getId();
$userRole = $_SESSION['user_role'] ?? 'student';

// Get instructor ID if user is an instructor
$instructorId = null;
if ($userRole === 'instructor') {
    $instructorRecord = $db->fetchOne("SELECT id FROM instructors WHERE user_id = ?", [$userId]);
    $instructorId = $instructorRecord ? $instructorRecord['id'] : null;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'get') {
        $sessionId = $_GET['session_id'] ?? null;

        if (!$sessionId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Session ID is required']);
            exit;
        }

        $session = LiveSession::find($sessionId);

        if (!$session) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Session not found']);
            exit;
        }

        // Check if user can view this session
        if (!$session->canJoin($userId) && $session->instructor_id != $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        $sessionData = $session->toArray();
        $sessionData['meeting_url'] = $session->getMeetingUrl();
        $sessionData['is_live'] = $session->isLive();
        $sessionData['can_join'] = $session->canJoin($userId);

        // Get attendance count
        $attendanceCount = $session->getAttendanceCount();

        echo json_encode([
            'success' => true,
            'session' => $sessionData,
            'attendance_count' => $attendanceCount
        ]);
        exit;
    }

    if ($action === 'list') {
        $courseId = $_GET['course_id'] ?? null;

        if (!$courseId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Course ID is required']);
            exit;
        }

        // Check if user is enrolled or is instructor
        $isEnrolled = $db->fetchOne("
            SELECT COUNT(*) as count FROM enrollments
            WHERE course_id = ? AND user_id = ? AND status = 'enrolled'
        ", [$courseId, $userId]);

        $isInstructor = $db->fetchOne("
            SELECT COUNT(*) as count FROM courses
            WHERE id = ? AND (instructor_id = ? OR instructor_id = ?)
        ", [$courseId, $instructorId ?? 0, $userId]);

        if (!$isEnrolled['count'] && !$isInstructor['count']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        // Get upcoming sessions for this course
        $sessions = LiveSession::getUpcomingByCourse($courseId);

        // Enhance session data
        foreach ($sessions as &$session) {
            $sessionObj = new LiveSession($session['id']);
            $session['meeting_url'] = $sessionObj->getMeetingUrl();
            $session['is_live'] = $sessionObj->isLive();
            $session['can_join'] = $sessionObj->canJoin($userId);
        }

        echo json_encode([
            'success' => true,
            'sessions' => $sessions
        ]);
        exit;
    }

    if ($action === 'attendance') {
        $sessionId = $_GET['session_id'] ?? null;

        if (!$sessionId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Session ID is required']);
            exit;
        }

        $session = LiveSession::find($sessionId);

        if (!$session) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Session not found']);
            exit;
        }

        // Only instructor can view attendance
        if ($session->instructor_id != $userId && $userRole !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        $attendance = $session->getAttendance();

        echo json_encode([
            'success' => true,
            'attendance' => $attendance
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null;

    if (!$action) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action is required']);
        exit;
    }

    try {
        if ($action === 'create') {
            // Only instructors can create sessions
            if ($userRole !== 'instructor' || !$instructorId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Only instructors can create live sessions']);
                exit;
            }

            $lessonId = $input['lesson_id'] ?? null;
            $scheduledStartTime = $input['scheduled_start_time'] ?? null;
            $durationMinutes = $input['duration_minutes'] ?? 60;

            if (!$lessonId || !$scheduledStartTime) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }

            // Verify lesson exists and instructor owns it
            $lesson = $db->fetchOne("
                SELECT l.*, m.course_id
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                JOIN courses c ON m.course_id = c.id
                WHERE l.id = ? AND (c.instructor_id = ? OR c.instructor_id = ?)
            ", [$lessonId, $instructorId, $userId]);

            if (!$lesson) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Lesson not found or access denied']);
                exit;
            }

            // Update lesson type to 'Live Session' if it isn't already
            if ($lesson['lesson_type'] !== 'Live Session') {
                $db->query("UPDATE lessons SET lesson_type = 'Live Session' WHERE id = ?", [$lessonId]);
            }

            // Create session
            $sessionData = [
                'lesson_id' => $lessonId,
                'instructor_id' => $instructorId,
                'scheduled_start_time' => $scheduledStartTime,
                'duration_minutes' => $durationMinutes,
                'description' => $input['description'] ?? '',
                'max_participants' => $input['max_participants'] ?? null,
                'allow_recording' => $input['allow_recording'] ?? 1,
                'auto_start_recording' => $input['auto_start_recording'] ?? 0,
                'enable_chat' => $input['enable_chat'] ?? 1,
                'enable_screen_share' => $input['enable_screen_share'] ?? 1,
                'buffer_minutes_before' => $input['buffer_minutes_before'] ?? 15,
                'buffer_minutes_after' => $input['buffer_minutes_after'] ?? 30,
            ];

            $newSessionId = LiveSession::create($sessionData);

            // Create notification for enrolled students
            $enrolledStudents = $db->fetchAll("
                SELECT user_id FROM enrollments
                WHERE course_id = ? AND status = 'enrolled'
            ", [$lesson['course_id']]);

            foreach ($enrolledStudents as $student) {
                $notificationData = [
                    'user_id' => $student['user_id'],
                    'type' => 'live_session_scheduled',
                    'title' => 'New Live Session Scheduled',
                    'message' => "A live session has been scheduled for '{$lesson['title']}' on " . date('M d, Y g:i A', strtotime($scheduledStartTime)),
                    'link' => url("live-session.php?session_id={$newSessionId}"),
                    'is_read' => 0
                ];

                $db->query("
                    INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
                    VALUES (:user_id, :type, :title, :message, :link, :is_read, NOW())
                ", $notificationData);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Live session scheduled successfully',
                'session_id' => $newSessionId
            ]);
            exit;
        }

        if ($action === 'update') {
            // Only instructors can update sessions
            if ($userRole !== 'instructor' || !$instructorId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Only instructors can update live sessions']);
                exit;
            }

            $sessionId = $input['session_id'] ?? null;

            if (!$sessionId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Session ID is required']);
                exit;
            }

            $session = LiveSession::find($sessionId);

            if (!$session) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Session not found']);
                exit;
            }

            // Verify instructor owns this session
            if ($session->instructor_id != $instructorId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            // Only allow updating scheduled sessions
            if ($session->status !== 'scheduled') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot update session that has already started or ended']);
                exit;
            }

            $updateData = [];
            $allowedFields = [
                'scheduled_start_time', 'duration_minutes', 'description',
                'max_participants', 'allow_recording', 'enable_chat', 'enable_screen_share'
            ];

            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updateData[$field] = $input[$field];
                }
            }

            $session->update($updateData);

            echo json_encode([
                'success' => true,
                'message' => 'Session updated successfully'
            ]);
            exit;
        }

        if ($action === 'cancel') {
            // Only instructors can cancel sessions
            if ($userRole !== 'instructor' || !$instructorId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Only instructors can cancel live sessions']);
                exit;
            }

            $sessionId = $input['session_id'] ?? null;

            if (!$sessionId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Session ID is required']);
                exit;
            }

            $session = LiveSession::find($sessionId);

            if (!$session) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Session not found']);
                exit;
            }

            // Verify instructor owns this session
            if ($session->instructor_id != $instructorId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            $session->update(['status' => 'cancelled']);

            // Notify enrolled students
            $enrolledStudents = $db->fetchAll("
                SELECT e.user_id FROM enrollments e
                JOIN modules m ON m.course_id = e.course_id
                JOIN lessons l ON l.module_id = m.id
                WHERE l.id = ? AND e.status = 'enrolled'
            ", [$session->lesson_id]);

            foreach ($enrolledStudents as $student) {
                $notificationData = [
                    'user_id' => $student['user_id'],
                    'type' => 'live_session_cancelled',
                    'title' => 'Live Session Cancelled',
                    'message' => "The live session for '{$session->lesson_title}' scheduled on " . date('M d, Y g:i A', strtotime($session->scheduled_start_time)) . " has been cancelled.",
                    'link' => url("learn.php?course_id={$session->course_id}"),
                    'is_read' => 0
                ];

                $db->query("
                    INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
                    VALUES (:user_id, :type, :title, :message, :link, :is_read, NOW())
                ", $notificationData);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Session cancelled successfully'
            ]);
            exit;
        }

        if ($action === 'exit') {
            $attendanceId = $input['attendance_id'] ?? null;

            if (!$attendanceId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Attendance ID is required']);
                exit;
            }

            // Verify attendance record belongs to current user
            $attendance = $db->fetchOne("
                SELECT * FROM live_session_attendance
                WHERE id = ? AND user_id = ?
            ", [$attendanceId, $userId]);

            if (!$attendance) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Attendance record not found']);
                exit;
            }

            // Update exit time
            $session = new LiveSession($attendance['live_session_id']);
            $session->updateAttendanceExit($attendanceId);

            echo json_encode([
                'success' => true,
                'message' => 'Attendance updated'
            ]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;

    } catch (Exception $e) {
        error_log("Live Sessions API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
        exit;
    }
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
