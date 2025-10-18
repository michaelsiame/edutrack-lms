<?php
/**
 * Checkout Page
 * Process course payment
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/functions.php';
require_once '../src/middleware/authenticate.php';
require_once '../src/includes/security.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Payment.php';
require_once '../src/classes/Enrollment.php';

$userId = $_SESSION['user_id'];

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid request', 'error');
        redirect('courses.php');
    }
    
    $courseId = $_POST['course_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $paymentMethod = $_POST['payment_method'] ?? null;
    $phoneNumber = $_POST['phone_number'] ?? null;
    
    // Validate inputs
    if (!$courseId || !$amount || !$paymentMethod) {
        setFlashMessage('Missing required fields', 'error');
        redirect('enroll.php?course_id=' . $courseId);
    }
    
    // Get course
    $course = Course::find($courseId);
    if (!$course) {
        setFlashMessage('Course not found', 'error');
        redirect('courses.php');
    }
    
    // Check if already enrolled
    if (Enrollment::isEnrolled($userId, $courseId)) {
        setFlashMessage('You are already enrolled in this course', 'info');
        redirect('learn.php?course=' . $course->getSlug());
    }
    
    // Verify amount matches course price
    if ($amount != $course->getPrice()) {
        setFlashMessage('Invalid payment amount', 'error');
        redirect('enroll.php?course_id=' . $courseId);
    }
    
    // Create payment record
    $paymentData = [
        'user_id' => $userId,
        'course_id' => $courseId,
        'amount' => $amount,
        'currency' => 'ZMW',
        'payment_method' => $paymentMethod,
        'phone_number' => $phoneNumber
    ];
    
    $paymentId = Payment::create($paymentData);
    
    if (!$paymentId) {
        setFlashMessage('Failed to create payment', 'error');
        redirect('enroll.php?course_id=' . $courseId);
    }
    
    $payment = Payment::find($paymentId);
    
    // Process based on payment method
    if (in_array($paymentMethod, ['mtn', 'airtel', 'zamtel'])) {
        // Mobile money payment
        if (!$phoneNumber) {
            setFlashMessage('Phone number is required for mobile money', 'error');
            redirect('enroll.php?course_id=' . $courseId);
        }
        
        $result = $payment->processMobileMoney($phoneNumber);
        
        if ($result['success']) {
            // Update payment with transaction ID
            $payment->update([
                'transaction_id' => $result['transaction_id'] ?? null,
                'status' => 'pending'
            ]);
            
            setFlashMessage($result['message'], 'info');
            redirect('payment-pending.php?reference=' . $payment->getTransactionReference());
        } else {
            $payment->markFailed($result['message']);
            setFlashMessage('Payment failed: ' . $result['message'], 'error');
            redirect('payment-failed.php?reference=' . $payment->getTransactionReference());
        }
        
    } elseif ($paymentMethod == 'bank_transfer') {
        // Manual bank transfer - show bank details
        redirect('payment-bank-transfer.php?reference=' . $payment->getTransactionReference());
        
    } else {
        setFlashMessage('Invalid payment method', 'error');
        redirect('enroll.php?course_id=' . $courseId);
    }
}

// If GET request, redirect to enroll page
redirect('courses.php');