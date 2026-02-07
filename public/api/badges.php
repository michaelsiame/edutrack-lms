<?php
/**
 * Badges API
 * List available badges and student achievements
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
// GET - List badges and/or student achievements
// =====================================================
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'my_achievements';

    switch ($action) {
        case 'all':
            // List all available badges
            $badges = Badge::getAll();
            echo json_encode([
                'success' => true,
                'badges' => $badges
            ]);
            break;

        case 'my_achievements':
            // Get achievements for the current student
            // Allow admin to query another student's achievements
            $studentId = $userId;

            $userRoles = $_SESSION['user_roles'] ?? [];
            $isAdmin = in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles);

            if ($isAdmin && isset($_GET['student_id'])) {
                $studentId = (int)$_GET['student_id'];
            }

            $achievements = Badge::getStudentAchievements($studentId);
            $totalCount = Badge::getStudentCount($studentId);

            echo json_encode([
                'success' => true,
                'achievements' => $achievements,
                'total_badges' => $totalCount
            ]);
            break;

        case 'summary':
            // Get both all badges and student achievements for a combined view
            $studentId = $userId;

            $userRoles = $_SESSION['user_roles'] ?? [];
            $isAdmin = in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles);

            if ($isAdmin && isset($_GET['student_id'])) {
                $studentId = (int)$_GET['student_id'];
            }

            $allBadges = Badge::getAll();
            $achievements = Badge::getStudentAchievements($studentId);
            $earnedBadgeIds = array_column($achievements, 'badge_id');

            echo json_encode([
                'success' => true,
                'all_badges' => $allBadges,
                'earned' => $achievements,
                'earned_badge_ids' => $earnedBadgeIds,
                'total_available' => count($allBadges),
                'total_earned' => count($achievements)
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action. Use: all, my_achievements, or summary']);
            break;
    }
    exit;
}

// Invalid method
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
