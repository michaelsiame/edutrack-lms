<?php
/**
 * Edutrack Computer Training College
 * Enrollment Handler
 * Logic: Checks prerequisites -> Creates Pending Enrollment -> Redirects to Payment
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';
require_once '../src/classes/RegistrationFee.php';

// 1. Authentication Check
if (!isLoggedIn()) {
    setFlashMessage('Please login to enroll in courses', 'error');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$userId = $_SESSION['user_id'];
$courseId = $_GET['course_id'] ?? null;

// 2. Validate Course
if (!$courseId) {
    setFlashMessage('Invalid course selected.', 'error');
    redirect('courses.php');
}

$course = Course::find($courseId);

if (!$course || !$course->isPublished()) {
    setFlashMessage('Course not found or unavailable.', 'error');
    redirect('courses.php');
}

// 3. Check Pre-requisites (Already Enrolled?)
if (Enrollment::isEnrolled($userId, $courseId)) {
    // If they are already enrolled, check status
    $enrollment = Enrollment::findByUserAndCourse($userId, $courseId);
    
    // If they are pending payment (Enrolled status), send them to checkout to finish paying
    if ($enrollment->getStatus() === 'Enrolled' && !$course->isFree()) {
        setFlashMessage('You have a pending enrollment. Please complete your payment.', 'info');
        redirect('checkout.php?enrollment_id=' . $enrollment->getId());
    }
    
    // Otherwise, go to learning
    setFlashMessage('You are already enrolled in this course.', 'info');
    redirect('learn.php?course=' . $course->getSlug());
}

// 4. Check Registration Fee (Mandatory K150)
if (RegistrationFee::isRequired() && !RegistrationFee::hasPaid($userId)) {
    $_SESSION['intended_course_id'] = $courseId;
    
    // Get fee amount from settings if available
    $db = Database::getInstance();
    $feeAmount = $db->fetchColumn("SELECT setting_value FROM system_settings WHERE setting_key = 'registration_fee_amount'") ?: 150;
    
    setFlashMessage("Please pay the registration fee (K{$feeAmount}) before enrolling.", 'warning');
    redirect('registration-fee.php');
}

// 5. Create the Enrollment Record (Pending State)
// This automatically creates the 'enrollment_payment_plans' record via the Enrollment class logic
$enrollmentId = Enrollment::create([
    'user_id' => $userId,
    'course_id' => $courseId,
    // Status 'Enrolled' means "Registered but waiting for deposit/payment"
    'enrollment_status' => 'Enrolled', 
    'payment_status' => 'pending' 
]);

if (!$enrollmentId) {
    setFlashMessage('Unable to create enrollment. Please contact support.', 'error');
    redirect('course.php?slug=' . $course->getSlug());
}

// 6. Handle Free vs Paid
if ($course->isFree()) {
    // Auto-complete the financial logic for free courses
    $enrollment = new Enrollment($enrollmentId);
    
    // Update Enrollment
    $enrollment->update([
        'enrollment_status' => 'In Progress', // Grant Access
        'payment_status' => 'completed',
        'amount_paid' => 0,
        'certificate_blocked' => 0 // Unblock certificate
    ]);

    // Manually mark the Payment Plan as completed (since no payment trigger will fire)
    $db = Database::getInstance();
    $db->update('enrollment_payment_plans', [
        'payment_status' => 'completed',
        'total_paid' => 0,
        'balance' => 0
    ], 'enrollment_id = ?', [$enrollmentId]);

    setFlashMessage('Successfully enrolled in ' . $course->getTitle(), 'success');
    redirect('learn.php?course=' . $course->getSlug());

} else {
    // Paid Course: Redirect to Checkout with the NEW Enrollment ID
    // checkout.php should use this ID to link the payment to the plan
    redirect('checkout.php?enrollment_id=' . $enrollmentId);
}
?>