<?php
/**
 * Enrollment Handler
 * Handles course enrollment process
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';

// Must be logged in to enroll
if (!isLoggedIn()) {
    setFlashMessage('Please login to enroll in courses', 'error');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Get course ID
$courseId = $_GET['course_id'] ?? null;

if (!$courseId) {
    setFlashMessage('Invalid course', 'error');
    redirect('courses.php');
}

// Get course
$course = Course::find($courseId);

if (!$course || !$course->isPublished()) {
    setFlashMessage('Course not found', 'error');
    redirect('courses.php');
}

// Check if already enrolled
$userId = $_SESSION['user_id'];
if (Enrollment::isEnrolled($userId, $courseId)) {
    setFlashMessage('You are already enrolled in this course', 'info');
    redirect('learn.php?course=' . $course->getSlug());
}

// If course is free, enroll immediately
if ($course->isFree()) {
    // Use correct schema enum values:
    // enrollment_status: 'Enrolled', 'In Progress', 'Completed', 'Dropped', 'Expired'
    // payment_status: 'pending', 'completed', 'failed', 'refunded'
    $enrollmentData = [
        'user_id' => $userId,
        'course_id' => $courseId,
        'enrollment_status' => 'Enrolled',
        'payment_status' => 'completed', // Free course = already paid
        'amount_paid' => 0
    ];

    $enrollmentId = Enrollment::create($enrollmentData);

    if ($enrollmentId) {
        setFlashMessage('Successfully enrolled in ' . $course->getTitle(), 'success');
        redirect('learn.php?course=' . $course->getSlug());
    } else {
        setFlashMessage('Failed to enroll. Please try again.', 'error');
        redirect('course.php?slug=' . $course->getSlug());
    }
}

// For paid courses, redirect directly to checkout (no duplicate page)
// All payment info, method selection, and form handling is in checkout.php
redirect('checkout.php?course_id=' . $courseId);