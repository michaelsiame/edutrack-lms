<?php
/**
 * Lenco Payment API
 *
 * Handles payment initialization and status checking for Lenco payments.
 *
 * Endpoints:
 * - POST /api/lenco-payment.php (Initialize payment)
 * - GET /api/lenco-payment.php?action=status&reference=XXX (Check status)
 * - GET /api/lenco-payment.php?action=details&reference=XXX (Get payment details)
 */

require_once '../../src/bootstrap.php';
require_once '../../src/classes/Lenco.php';
require_once '../../src/classes/Course.php';
require_once '../../src/classes/Enrollment.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Please login to continue'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Initialize Lenco
$lenco = new Lenco();

// Check if Lenco is configured
if (!$lenco->isConfigured()) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'error' => 'Payment gateway not configured. Please contact support.'
    ]);
    exit;
}

// Handle GET requests (status checks)
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'status';
    $reference = $_GET['reference'] ?? null;

    if (!$reference) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Payment reference is required'
        ]);
        exit;
    }

    switch ($action) {
        case 'status':
            // Check payment status
            $result = $lenco->checkPaymentStatus($reference);

            // Get local transaction data
            $localTx = $lenco->getPendingTransaction($reference);

            if (!$localTx) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Transaction not found'
                ]);
                exit;
            }

            // Verify user owns this transaction
            if ($localTx['user_id'] != $userId && $_SESSION['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Access denied'
                ]);
                exit;
            }

            echo json_encode([
                'success' => true,
                'status' => $localTx['status'],
                'transaction' => [
                    'reference' => $localTx['reference'],
                    'amount' => floatval($localTx['amount']),
                    'currency' => $localTx['currency'],
                    'status' => $localTx['status'],
                    'account_number' => $localTx['virtual_account_number'],
                    'account_bank' => $localTx['virtual_account_bank'],
                    'account_name' => $localTx['virtual_account_name'],
                    'created_at' => $localTx['created_at'],
                    'expires_at' => $localTx['expires_at'],
                    'paid_at' => $localTx['paid_at']
                ]
            ]);
            break;

        case 'details':
            // Get full transaction details
            $localTx = $lenco->getPendingTransaction($reference);

            if (!$localTx) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Transaction not found'
                ]);
                exit;
            }

            // Verify user owns this transaction
            if ($localTx['user_id'] != $userId && $_SESSION['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Access denied'
                ]);
                exit;
            }

            // Get course details if available
            $course = null;
            if ($localTx['course_id']) {
                $courseObj = Course::find($localTx['course_id']);
                if ($courseObj) {
                    $course = [
                        'id' => $courseObj->getId(),
                        'title' => $courseObj->getTitle(),
                        'slug' => $courseObj->getSlug()
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'transaction' => [
                    'reference' => $localTx['reference'],
                    'amount' => floatval($localTx['amount']),
                    'formatted_amount' => 'K' . number_format($localTx['amount'], 2),
                    'currency' => $localTx['currency'],
                    'status' => $localTx['status'],
                    'payment_info' => [
                        'account_number' => $localTx['virtual_account_number'],
                        'account_bank' => $localTx['virtual_account_bank'],
                        'account_name' => $localTx['virtual_account_name']
                    ],
                    'course' => $course,
                    'created_at' => $localTx['created_at'],
                    'expires_at' => $localTx['expires_at'],
                    'paid_at' => $localTx['paid_at'],
                    'is_expired' => strtotime($localTx['expires_at']) < time()
                ]
            ]);
            break;

        case 'history':
            // Get user's Lenco transaction history
            $status = $_GET['status'] ?? null;
            $transactions = $lenco->getUserTransactions($userId, $status);

            echo json_encode([
                'success' => true,
                'transactions' => array_map(function ($tx) {
                    return [
                        'reference' => $tx['reference'],
                        'amount' => floatval($tx['amount']),
                        'formatted_amount' => 'K' . number_format($tx['amount'], 2),
                        'currency' => $tx['currency'],
                        'status' => $tx['status'],
                        'course_title' => $tx['course_title'] ?? null,
                        'created_at' => $tx['created_at'],
                        'paid_at' => $tx['paid_at']
                    ];
                }, $transactions)
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action'
            ]);
    }

    exit;
}

// Handle POST requests (payment initialization)
if ($method === 'POST') {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        // Try form data
        $input = $_POST;
    }

    if (empty($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid request data'
        ]);
        exit;
    }

    // Validate CSRF token
    $csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!verifyCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid security token. Please refresh the page.'
        ]);
        exit;
    }

    // Rate limiting
    if (!checkRateLimit('lenco_payment_' . $userId, 5, 300)) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'error' => 'Too many payment attempts. Please wait a few minutes.'
        ]);
        exit;
    }

    $action = $input['action'] ?? 'initialize';

    switch ($action) {
        case 'initialize':
            // Validate required fields
            $enrollmentId = $input['enrollment_id'] ?? null;
            $courseId = $input['course_id'] ?? null;
            $amount = floatval($input['amount'] ?? 0);

            if (!$enrollmentId && !$courseId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Enrollment or course ID is required'
                ]);
                exit;
            }

            if ($amount <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid payment amount'
                ]);
                exit;
            }

            // Validate minimum amount
            $minAmount = config('payment.lenco.min_amount', 10);
            if ($amount < $minAmount) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => "Minimum payment amount is K{$minAmount}"
                ]);
                exit;
            }

            // If enrollment_id provided, verify ownership
            if ($enrollmentId) {
                $enrollment = Enrollment::find($enrollmentId);
                if (!$enrollment || $enrollment->getUserId() != $userId) {
                    http_response_code(403);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Invalid enrollment'
                    ]);
                    exit;
                }
                $courseId = $enrollment->getCourseId();
            }

            // Initialize Lenco payment
            $result = $lenco->initializePayment([
                'user_id' => $userId,
                'enrollment_id' => $enrollmentId,
                'course_id' => $courseId,
                'amount' => $amount,
                'currency' => 'ZMW'
            ]);

            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment initialized successfully',
                    'reference' => $result['reference'],
                    'payment_info' => $result['payment_info'],
                    'redirect_url' => url('lenco-checkout.php?reference=' . $result['reference'])
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to initialize payment'
                ]);
            }
            break;

        case 'verify':
            // Manual payment verification (for polling)
            $reference = $input['reference'] ?? null;

            if (!$reference) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Reference is required'
                ]);
                exit;
            }

            // Check local transaction first
            $localTx = $lenco->getPendingTransaction($reference);

            if (!$localTx) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Transaction not found'
                ]);
                exit;
            }

            // Verify ownership
            if ($localTx['user_id'] != $userId && $_SESSION['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Access denied'
                ]);
                exit;
            }

            // Check status with Lenco API
            $result = $lenco->checkPaymentStatus($reference);

            echo json_encode([
                'success' => true,
                'status' => $result['status'] ?? 'unknown',
                'is_paid' => ($result['status'] ?? '') === 'successful'
            ]);
            break;

        case 'cancel':
            // Cancel pending payment
            $reference = $input['reference'] ?? null;

            if (!$reference) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Reference is required'
                ]);
                exit;
            }

            $localTx = $lenco->getPendingTransaction($reference);

            if (!$localTx) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Transaction not found'
                ]);
                exit;
            }

            // Verify ownership
            if ($localTx['user_id'] != $userId) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Access denied'
                ]);
                exit;
            }

            // Can only cancel pending transactions
            if ($localTx['status'] !== 'pending') {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Can only cancel pending payments'
                ]);
                exit;
            }

            // Update status to cancelled (using failed as cancelled isn't in enum)
            $lenco->updateTransactionStatus($reference, 'failed');

            echo json_encode([
                'success' => true,
                'message' => 'Payment cancelled'
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action'
            ]);
    }

    exit;
}

// Invalid method
http_response_code(405);
echo json_encode([
    'success' => false,
    'error' => 'Method not allowed'
]);
