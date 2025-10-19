<?php
/**
 * API: Course Enrollment
 * POST /api/enroll.php
 */

header('Content-Type: application/json');

require_once '../../src/includes/config.php';
require_once '../../src/includes/database.php';
require_once '../../src/includes/functions.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Enrollment.php';
require_once '../../src/classes/User.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

$courseId = $input['course_id'] ?? null;
$paymentMethod = $input['payment_method'] ?? 'free';

// Validate input
if (!$courseId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Course ID is required']);
    exit;
}

// Get course
$course = Course::find($courseId);

if (!$course || !$course->exists()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Course not found']);
    exit;
}

// Check if course is published
if ($course->getStatus() !== 'published') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Course is not available for enrollment']);
    exit;
}

$userId = $_SESSION['user_id'];

// Check if already enrolled
if (Enrollment::isEnrolled($userId, $courseId)) {
    echo json_encode([
        'success' => false, 
        'error' => 'Already enrolled in this course',
        'redirect' => url('learn.php?course=' . $course->getSlug())
    ]);
    exit;
}

// Check if course is free
if ($course->isFree()) {
    // Free enrollment
    $enrollmentId = Enrollment::create([
        'user_id' => $userId,
        'course_id' => $courseId,
        'enrollment_status' => 'active',
        'payment_status' => 'free'
    ]);
    
    if ($enrollmentId) {
        echo json_encode([
            'success' => true,
            'message' => 'Successfully enrolled in course!',
            'enrollment_id' => $enrollmentId,
            'redirect' => url('learn.php?course=' . $course->getSlug())
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to enroll']);
    }
    exit;
}

// Paid course - create pending enrollment
$enrollmentId = Enrollment::create([
    'user_id' => $userId,
    'course_id' => $courseId,
    'enrollment_status' => 'pending',
    'payment_status' => 'pending'
]);

if (!$enrollmentId) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to create enrollment']);
    exit;
}

// Create payment record
require_once '../../src/classes/Payment.php';

$paymentId = Payment::create([
    'user_id' => $userId,
    'course_id' => $courseId,
    'enrollment_id' => $enrollmentId,
    'amount' => $course->getPrice(),
    'currency' => CURRENCY,
    'payment_method' => $paymentMethod,
    'status' => 'pending'
]);

if (!$paymentId) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to create payment']);
    exit;
}

// Return success with payment info
echo json_encode([
    'success' => true,
    'message' => 'Enrollment created. Please complete payment.',
    'enrollment_id' => $enrollmentId,
    'payment_id' => $paymentId,
    'amount' => $course->getPrice(),
    'currency' => CURRENCY,
    'redirect' => url('checkout.php?payment=' . $paymentId)
]);