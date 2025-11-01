<?php
/**
 * Payment API
 * Handle payment operations
 */

require_once '../../src/bootstrap.php';
require_once '../../src/classes/Payment.php';

header('Content-Type: application/json');

// Must be logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// GET - Retrieve payment data
if ($method === 'GET') {
    $action = $_GET['action'] ?? null;
    $reference = $_GET['reference'] ?? null;
    $paymentId = $_GET['payment_id'] ?? null;
    
    if ($action == 'status' && $reference) {
        // Check payment status
        $payment = Payment::findByReference($reference);
        
        if (!$payment || $payment->getUserId() != $userId) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Payment not found']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'status' => $payment->getStatus(),
            'payment' => [
                'id' => $payment->getId(),
                'reference' => $payment->getTransactionReference(),
                'amount' => $payment->getFormattedAmount(),
                'status' => $payment->getStatus(),
                'payment_method' => $payment->getPaymentMethodLabel(),
                'created_at' => $payment->getCreatedAt()
            ]
        ]);
        exit;
    }
    
    if ($paymentId) {
        // Get payment details
        $payment = Payment::find($paymentId);
        
        if (!$payment || $payment->getUserId() != $userId) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Payment not found']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'payment' => [
                'id' => $payment->getId(),
                'reference' => $payment->getTransactionReference(),
                'course_title' => $payment->getCourseTitle(),
                'amount' => $payment->getFormattedAmount(),
                'status' => $payment->getStatus(),
                'payment_method' => $payment->getPaymentMethodLabel(),
                'payment_date' => $payment->getPaymentDate(),
                'created_at' => $payment->getCreatedAt()
            ]
        ]);
        exit;
    }
    
    // Get user payments
    $payments = Payment::getByUser($userId);
    
    echo json_encode([
        'success' => true,
        'payments' => array_map(function($p) {
            return [
                'id' => $p['id'],
                'reference' => $p['transaction_reference'],
                'course_title' => $p['course_title'],
                'amount' => formatCurrency($p['amount']),
                'status' => $p['status'],
                'payment_method' => $p['payment_method'],
                'created_at' => $p['created_at']
            ];
        }, $payments)
    ]);
    exit;
}

// POST - Verify or update payment
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }
    
    $action = $input['action'] ?? null;
    $reference = $input['reference'] ?? null;
    
    if (!$action || !$reference) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $payment = Payment::findByReference($reference);
    
    if (!$payment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
        exit;
    }
    
    // Only admin or payment owner can update
    if ($payment->getUserId() != $userId && $_SESSION['role'] != 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    switch ($action) {
        case 'verify':
            // Verify payment with provider
            // This would integrate with actual payment gateway API
            
            // For demo, mark as successful
            $payment->markSuccessful($input['transaction_id'] ?? null);
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment verified successfully',
                'redirect' => url('payment-success.php?reference=' . $reference)
            ]);
            break;
            
        case 'cancel':
            // Cancel pending payment
            if ($payment->getStatus() != 'pending') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Can only cancel pending payments']);
                exit;
            }
            
            $payment->markFailed('Cancelled by user');
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment cancelled'
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);