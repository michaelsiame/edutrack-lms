<?php
/**
 * Transactions API Endpoint
 * Handles financial transaction management
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';
require_once '../../src/includes/email-hooks.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$db = Database::getInstance();

try {
    switch ($method) {
        case 'GET':
            // Get all transactions with student and payment method info
            $sql = "SELECT
                        t.transaction_id as id,
                        t.user_id,
                        t.amount,
                        t.transaction_type as type,
                        t.payment_status as status,
                        t.payment_method_id,
                        t.reference_number,
                        t.description,
                        t.processed_at as date,
                        t.created_at,
                        CONCAT(u.first_name, ' ', u.last_name) as student_name,
                        pm.method_name as method
                    FROM transactions t
                    INNER JOIN users u ON t.user_id = u.id
                    LEFT JOIN payment_methods pm ON t.payment_method_id = pm.id
                    ORDER BY t.processed_at DESC";

            $transactions = $db->fetchAll($sql);

            echo json_encode([
                'success' => true,
                'data' => $transactions
            ]);
            break;

        case 'POST':
            // Create new transaction
            if (empty($input['user_id']) || empty($input['amount'])) {
                throw new Exception('User ID and amount are required');
            }

            $transactionData = [
                'user_id' => $input['user_id'],
                'amount' => $input['amount'],
                'transaction_type' => $input['type'] ?? 'Payment',
                'payment_status' => $input['status'] ?? 'Pending',
                'description' => $input['description'] ?? '',
                'reference_number' => 'TXN-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'processed_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (isset($input['payment_method_id'])) {
                $transactionData['payment_method_id'] = $input['payment_method_id'];
            }

            $transactionId = $db->insert('transactions', $transactionData);

            // Send payment receipt email if transaction is completed
            if ($transactionData['payment_status'] === 'Completed') {
                sendPaymentReceipt($transactionId);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => ['id' => $transactionId]
            ]);
            break;

        case 'PUT':
            // Update transaction (typically to verify/update status)
            if (empty($input['id'])) {
                throw new Exception('Transaction ID is required');
            }

            $updateData = [];

            if (isset($input['status'])) {
                $updateData['payment_status'] = $input['status'];

                // If completing the transaction, update processed time
                if ($input['status'] === 'Completed') {
                    $updateData['processed_at'] = date('Y-m-d H:i:s');
                }
            }

            if (isset($input['description'])) {
                $updateData['description'] = $input['description'];
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $db->update('transactions', $updateData, 'transaction_id = ?', [$input['id']]);

                // Send payment receipt email if status changed to Completed
                if (isset($input['status']) && $input['status'] === 'Completed') {
                    sendPaymentReceipt($input['id']);
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Transaction updated successfully'
            ]);
            break;

        case 'DELETE':
            // Delete transaction (typically only for cancelled/failed transactions)
            parse_str(file_get_contents('php://input'), $params);
            $transactionId = $params['id'] ?? $_GET['id'] ?? null;

            if (empty($transactionId)) {
                throw new Exception('Transaction ID is required');
            }

            // Check if transaction can be deleted (not completed)
            $transaction = $db->fetchOne(
                "SELECT payment_status FROM transactions WHERE transaction_id = ?",
                [$transactionId]
            );

            if ($transaction && $transaction['payment_status'] === 'Completed') {
                throw new Exception('Cannot delete completed transactions');
            }

            $db->delete('transactions', 'transaction_id = ?', [$transactionId]);

            echo json_encode([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    if ($db->getConnection()->inTransaction()) {
        $db->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
