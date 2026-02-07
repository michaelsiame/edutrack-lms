<?php
/**
 * Payment Gateway Callback Handler
 * Processes payment notifications from MTN MoMo, Airtel Money, Zamtel Kwacha
 */

require_once __DIR__ . '/../../src/bootstrap.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$db = Database::getInstance();

try {
    // Get raw payload
    $payload = file_get_contents('php://input');
    $data = json_decode($payload, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
        exit;
    }

    // Log the callback for debugging
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $gateway = $data['gateway'] ?? $data['provider'] ?? 'unknown';

    $db->query(
        "INSERT INTO activity_logs (user_id, activity_type, description, ip_address, created_at) VALUES (NULL, ?, ?, ?, NOW())",
        ['payment_callback', "Payment callback from {$gateway}: " . substr($payload, 0, 500), $ip]
    );

    // Determine which gateway sent the callback
    $gateway = strtolower($gateway);

    switch ($gateway) {
        case 'mtn':
        case 'mtn_momo':
            $result = processMTNCallback($data, $db);
            break;

        case 'airtel':
        case 'airtel_money':
            $result = processAirtelCallback($data, $db);
            break;

        case 'zamtel':
        case 'zamtel_kwacha':
            $result = processZamtelCallback($data, $db);
            break;

        default:
            // Try to identify by payload structure
            if (isset($data['transactionId']) && isset($data['msisdn'])) {
                $result = processMTNCallback($data, $db);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Unknown payment gateway']);
                exit;
            }
    }

    if ($result['success']) {
        echo json_encode(['status' => 'success', 'message' => 'Payment processed']);
    } else {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => $result['message'] ?? 'Processing failed']);
    }

} catch (Exception $e) {
    error_log('Payment callback error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
}

/**
 * Process MTN Mobile Money callback
 */
function processMTNCallback($data, $db) {
    $transactionId = $data['transactionId'] ?? $data['transaction_id'] ?? null;
    $status = $data['status'] ?? $data['transactionStatus'] ?? null;
    $amount = $data['amount'] ?? null;
    $reference = $data['externalId'] ?? $data['reference'] ?? null;
    $phone = $data['msisdn'] ?? $data['phone'] ?? null;

    if (!$transactionId || !$status || !$reference) {
        return ['success' => false, 'message' => 'Missing required fields'];
    }

    return processPaymentUpdate($db, $reference, $transactionId, $status, $amount, 'MTN MoMo', $phone);
}

/**
 * Process Airtel Money callback
 */
function processAirtelCallback($data, $db) {
    $transactionId = $data['transaction']['id'] ?? $data['transactionId'] ?? null;
    $status = $data['transaction']['status'] ?? $data['status'] ?? null;
    $amount = $data['transaction']['amount'] ?? $data['amount'] ?? null;
    $reference = $data['transaction']['reference'] ?? $data['reference'] ?? null;
    $phone = $data['transaction']['msisdn'] ?? $data['phone'] ?? null;

    if (!$transactionId || !$status || !$reference) {
        return ['success' => false, 'message' => 'Missing required fields'];
    }

    return processPaymentUpdate($db, $reference, $transactionId, $status, $amount, 'Airtel Money', $phone);
}

/**
 * Process Zamtel Kwacha callback
 */
function processZamtelCallback($data, $db) {
    $transactionId = $data['transactionId'] ?? $data['transaction_id'] ?? null;
    $status = $data['status'] ?? null;
    $amount = $data['amount'] ?? null;
    $reference = $data['reference'] ?? $data['externalReference'] ?? null;
    $phone = $data['phoneNumber'] ?? $data['phone'] ?? null;

    if (!$transactionId || !$status || !$reference) {
        return ['success' => false, 'message' => 'Missing required fields'];
    }

    return processPaymentUpdate($db, $reference, $transactionId, $status, $amount, 'Zamtel Kwacha', $phone);
}

/**
 * Common payment processing logic
 */
function processPaymentUpdate($db, $reference, $transactionId, $gatewayStatus, $amount, $provider, $phone) {
    // Map gateway status to our status
    $statusMap = [
        'successful' => 'Completed',
        'success' => 'Completed',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'failure' => 'Failed',
        'cancelled' => 'Cancelled',
        'pending' => 'Pending',
    ];

    $paymentStatus = $statusMap[strtolower($gatewayStatus)] ?? 'Pending';

    // Find the payment by reference
    $payment = $db->fetchOne(
        "SELECT * FROM payments WHERE transaction_id = ? OR (phone_number = ? AND amount = ? AND payment_status = 'Pending')",
        [$reference, $phone, $amount]
    );

    if (!$payment) {
        error_log("Payment callback: No matching payment found for reference={$reference}, phone={$phone}, amount={$amount}");
        return ['success' => false, 'message' => 'Payment not found'];
    }

    // Update payment status
    $db->query(
        "UPDATE payments SET payment_status = ?, transaction_id = ?, payment_date = NOW(), notes = CONCAT(COALESCE(notes, ''), ?) WHERE payment_id = ?",
        [$paymentStatus, $transactionId, "\n[{$provider}] Callback received: {$gatewayStatus}", $payment['payment_id']]
    );

    // If completed, update enrollment
    if ($paymentStatus === 'Completed' && $payment['enrollment_id']) {
        $db->query(
            "UPDATE enrollments SET payment_status = 'completed', amount_paid = amount_paid + ? WHERE id = ? AND payment_status != 'completed'",
            [$amount ?? $payment['amount'], $payment['enrollment_id']]
        );

        // Update payment plan if exists
        if ($payment['payment_plan_id']) {
            $db->query(
                "UPDATE enrollment_payment_plans SET total_paid = total_paid + ?, updated_at = NOW() WHERE id = ?",
                [$amount ?? $payment['amount'], $payment['payment_plan_id']]
            );
        }

        // Send confirmation email
        try {
            $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$payment['student_id']]);
            if ($user && function_exists('sendPaymentEmail')) {
                sendPaymentEmail($user, $payment);
            }
        } catch (Exception $e) {
            error_log("Payment callback: Failed to send confirmation email: " . $e->getMessage());
        }
    }

    // Record transaction
    $db->query(
        "INSERT INTO transactions (payment_id, transaction_type, amount, currency, gateway_response, processed_at) VALUES (?, 'Payment', ?, ?, ?, NOW())",
        [$payment['payment_id'], $amount ?? $payment['amount'], $payment['currency'] ?? 'ZMW', json_encode(['provider' => $provider, 'transaction_id' => $transactionId, 'status' => $gatewayStatus])]
    );

    return ['success' => true, 'message' => 'Payment updated'];
}
