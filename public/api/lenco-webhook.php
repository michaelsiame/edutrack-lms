<?php
/**
 * Lenco Payment Gateway Webhook Handler
 *
 * Receives and processes webhook notifications from Lenco
 * for payment confirmations, failures, and reversals.
 *
 * Endpoint: /api/lenco-webhook.php
 */

// Prevent any output buffering issues
ob_clean();

require_once '../../src/bootstrap.php';
require_once '../../src/classes/Lenco.php';

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get raw payload
$payload = file_get_contents('php://input');

if (empty($payload)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Empty payload']);
    exit;
}

// Get signature from headers
$signature = $_SERVER['HTTP_X_LENCO_SIGNATURE'] ?? $_SERVER['HTTP_LENCO_SIGNATURE'] ?? null;

// Initialize Lenco
$lenco = new Lenco();

// Log incoming webhook
$db = Database::getInstance();
$logId = null;

try {
    $db->query(
        "INSERT INTO lenco_webhook_logs (event_type, payload, signature, ip_address, created_at)
         VALUES (:event_type, :payload, :signature, :ip_address, NOW())",
        [
            'event_type' => 'incoming',
            'payload' => $payload,
            'signature' => $signature,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]
    );
    $logId = $db->lastInsertId();
} catch (Exception $e) {
    error_log('Failed to log webhook: ' . $e->getMessage());
}

// Verify webhook signature (REQUIRED in production)
$webhookSecret = env('LENCO_WEBHOOK_SECRET', '');
$signatureValid = false;

if (empty($webhookSecret)) {
    // Webhook secret not configured - reject in production
    if (getenv('APP_ENV') !== 'development') {
        error_log('CRITICAL: LENCO_WEBHOOK_SECRET not configured. Rejecting webhook.');
        if ($logId) {
            $db->query(
                "UPDATE lenco_webhook_logs SET signature_valid = 0, error_message = 'Webhook secret not configured' WHERE id = :id",
                ['id' => $logId]
            );
        }
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Webhook verification not configured']);
        exit;
    }
    // In development, allow unsigned webhooks
    $signatureValid = true;
} elseif (empty($signature)) {
    // Signature header missing - reject
    if ($logId) {
        $db->query(
            "UPDATE lenco_webhook_logs SET signature_valid = 0, error_message = 'Missing signature header' WHERE id = :id",
            ['id' => $logId]
        );
    }
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Missing signature']);
    exit;
} else {
    $signatureValid = $lenco->verifyWebhookSignature($payload, $signature);

    if (!$signatureValid) {
        if ($logId) {
            $db->query(
                "UPDATE lenco_webhook_logs SET signature_valid = 0, error_message = 'Invalid signature' WHERE id = :id",
                ['id' => $logId]
            );
        }

        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
        exit;
    }
}

// Parse payload
$data = json_decode($payload, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    if ($logId) {
        $db->query(
            "UPDATE lenco_webhook_logs SET error_message = 'Invalid JSON payload' WHERE id = :id",
            ['id' => $logId]
        );
    }

    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
    exit;
}

// Update log with event type
$eventType = $data['event'] ?? 'unknown';
if ($logId) {
    $db->query(
        "UPDATE lenco_webhook_logs SET event_type = :event_type, signature_valid = :valid WHERE id = :id",
        ['id' => $logId, 'event_type' => $eventType, 'valid' => $signatureValid ? 1 : 0]
    );
}

// Process the webhook
try {
    $result = $lenco->processWebhook($data);

    // Update log as processed
    if ($logId) {
        $db->query(
            "UPDATE lenco_webhook_logs SET processed = 1 WHERE id = :id",
            ['id' => $logId]
        );
    }

    // Return success response to Lenco
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Webhook processed successfully',
        'data' => $result
    ]);

} catch (Exception $e) {
    // Log error
    error_log('Lenco webhook processing error: ' . $e->getMessage());

    if ($logId) {
        $db->query(
            "UPDATE lenco_webhook_logs SET processed = 0, error_message = :error WHERE id = :id",
            ['id' => $logId, 'error' => $e->getMessage()]
        );
    }

    // Return 500 so Lenco will retry
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal processing error'
    ]);
}
