<?php
/**
 * Payment Gateway Callback Handler
 * Processes payment notifications from MTN, Airtel, etc.
 */

require_once '../../src/includes/config.php';
require_once '../../src/includes/database.php';
require_once '../../src/classes/Payment.php';
require_once '../../src/classes/Enrollment.php';
require_once '../../src/classes/Email.php';

header('Content-Type: application/json');

// Log callback for debugging
$logFile = STORAGE_PATH . '/logs/payment-callbacks.log';
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'provider' => $_GET['provider'] ?? 'unknown',
    'method' => $_SERVER['REQUEST_METHOD'],
    'get' => $_GET,
    'post' => $_POST,
    'body' => file_get_contents('php://input'),
    'headers' => getallheaders()
];
file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND);

$provider = $_GET['provider'] ?? null;

if (!$provider) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Provider not specified']);
    exit;
}

switch ($provider) {
    case 'mtn':
        handleMTNCallback();
        break;
    case 'airtel':
        handleAirtelCallback();
        break;
    case 'zamtel':
        handleZamtelCallback();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid provider']);
}

/**
 * Handle MTN Mobile Money Callback
 */
function handleMTNCallback() {
    global $db;
    
    // Get callback data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    // MTN callback structure (adjust based on actual MTN API)
    $transactionId = $input['transaction_id'] ?? $input['financialTransactionId'] ?? null;
    $status = $input['status'] ?? $input['transactionStatus'] ?? null;
    $amount = $input['amount'] ?? null;
    $phoneNumber = $input['phone_number'] ?? $input['payer'] ?? null;
    $reference = $input['reference'] ?? $input['externalId'] ?? null;
    
    if (!$reference) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing reference']);
        return;
    }
    
    // Find payment by reference
    $payment = Payment::findByReference($reference);
    
    if (!$payment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
        return;
    }
    
    // Update payment based on status
    if (strtolower($status) == 'successful' || strtolower($status) == 'completed') {
        // Payment successful
        $updateData = [
            'status' => 'completed',
            'provider_reference' => $transactionId,
            'payment_date' => date('Y-m-d H:i:s')
        ];
        
        if ($payment->update($updateData)) {
            // Create enrollment
            Enrollment::create([
                'user_id' => $payment->getUserId(),
                'course_id' => $payment->getCourseId(),
                'payment_id' => $payment->getId(),
                'enrollment_status' => 'active'
            ]);
            
            // Send confirmation email
            Email::send($payment->getUserEmail(), 'payment-success', [
                'name' => $payment->getUserName(),
                'course_title' => $payment->getCourseTitle(),
                'amount' => formatCurrency($payment->getAmount()),
                'reference' => $payment->getTransactionReference(),
                'course_url' => url('learn.php?course=' . $payment->getCourseSlug())
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Payment processed']);
        }
    } elseif (strtolower($status) == 'failed' || strtolower($status) == 'cancelled') {
        // Payment failed
        $payment->update([
            'status' => 'failed',
            'provider_reference' => $transactionId,
            'notes' => 'Payment failed or cancelled'
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Payment marked as failed']);
    } else {
        // Still pending
        echo json_encode(['success' => true, 'message' => 'Payment still pending']);
    }
}

/**
 * Handle Airtel Money Callback
 */
function handleAirtelCallback() {
    global $db;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    // Airtel callback structure (adjust based on actual Airtel API)
    $transactionId = $input['transaction_id'] ?? $input['id'] ?? null;
    $status = $input['status'] ?? $input['transaction']['status'] ?? null;
    $reference = $input['reference'] ?? $input['transaction']['reference'] ?? null;
    
    if (!$reference) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing reference']);
        return;
    }
    
    $payment = Payment::findByReference($reference);
    
    if (!$payment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
        return;
    }
    
    if (strtolower($status) == 'ts' || strtolower($status) == 'successful') {
        $updateData = [
            'status' => 'completed',
            'provider_reference' => $transactionId,
            'payment_date' => date('Y-m-d H:i:s')
        ];
        
        if ($payment->update($updateData)) {
            Enrollment::create([
                'user_id' => $payment->getUserId(),
                'course_id' => $payment->getCourseId(),
                'payment_id' => $payment->getId(),
                'enrollment_status' => 'active'
            ]);
            
            Email::send($payment->getUserEmail(), 'payment-success', [
                'name' => $payment->getUserName(),
                'course_title' => $payment->getCourseTitle(),
                'amount' => formatCurrency($payment->getAmount()),
                'reference' => $payment->getTransactionReference(),
                'course_url' => url('learn.php?course=' . $payment->getCourseSlug())
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Payment processed']);
        }
    } elseif (strtolower($status) == 'tf' || strtolower($status) == 'failed') {
        $payment->update([
            'status' => 'failed',
            'provider_reference' => $transactionId,
            'notes' => 'Payment failed'
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Payment marked as failed']);
    }
}

/**
 * Handle Zamtel Kwacha Callback
 */
function handleZamtelCallback() {
    global $db;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    // Similar implementation to MTN/Airtel
    // Adjust based on Zamtel API specifications
    
    $transactionId = $input['transaction_id'] ?? null;
    $status = $input['status'] ?? null;
    $reference = $input['reference'] ?? null;
    
    if (!$reference) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing reference']);
        return;
    }
    
    $payment = Payment::findByReference($reference);
    
    if (!$payment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
        return;
    }
    
    if (strtolower($status) == 'success' || strtolower($status) == 'completed') {
        $updateData = [
            'status' => 'completed',
            'provider_reference' => $transactionId,
            'payment_date' => date('Y-m-d H:i:s')
        ];
        
        if ($payment->update($updateData)) {
            Enrollment::create([
                'user_id' => $payment->getUserId(),
                'course_id' => $payment->getCourseId(),
                'payment_id' => $payment->getId(),
                'enrollment_status' => 'active'
            ]);
            
            Email::send($payment->getUserEmail(), 'payment-success', [
                'name' => $payment->getUserName(),
                'course_title' => $payment->getCourseTitle(),
                'amount' => formatCurrency($payment->getAmount()),
                'reference' => $payment->getTransactionReference(),
                'course_url' => url('learn.php?course=' . $payment->getCourseSlug())
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Payment processed']);
        }
    } else {
        $payment->update([
            'status' => 'failed',
            'provider_reference' => $transactionId,
            'notes' => 'Payment failed'
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Payment marked as failed']);
    }
}