<?php
/**
 * Lenco Payment Gateway Integration
 *
 * Lenco provides virtual bank accounts and bank transfer payment processing
 * for businesses in Africa (Nigeria, Zambia, etc.)
 *
 * API Documentation: https://docs.lenco.co
 */

class Lenco {
    private $apiKey;
    private $secretKey;
    private $baseUrl;
    private $webhookSecret;
    private $isLive;
    private $db;

    // API Endpoints
    const ENDPOINT_VIRTUAL_ACCOUNTS = '/virtual-accounts';
    const ENDPOINT_TRANSACTIONS = '/transactions';
    const ENDPOINT_BANKS = '/banks';
    const ENDPOINT_TRANSFERS = '/transfers';
    const ENDPOINT_BALANCE = '/balance';

    // Transaction statuses
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED = 'failed';
    const STATUS_REVERSED = 'reversed';

    /**
     * Initialize Lenco gateway
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadConfig();
    }

    /**
     * Load configuration from environment
     */
    private function loadConfig() {
        $this->isLive = env('LENCO_LIVE_MODE', false);

        if ($this->isLive) {
            $this->baseUrl = 'https://api.lenco.co';
            $this->apiKey = env('LENCO_LIVE_API_KEY', '');
            $this->secretKey = env('LENCO_LIVE_SECRET_KEY', '');
        } else {
            $this->baseUrl = 'https://api.sandbox.lenco.co';
            $this->apiKey = env('LENCO_SANDBOX_API_KEY', '');
            $this->secretKey = env('LENCO_SANDBOX_SECRET_KEY', '');
        }

        $this->webhookSecret = env('LENCO_WEBHOOK_SECRET', '');
    }

    /**
     * Check if Lenco is properly configured
     */
    public function isConfigured() {
        return !empty($this->apiKey) && !empty($this->secretKey);
    }

    /**
     * Make API request to Lenco
     */
    private function request($method, $endpoint, $data = null) {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'GET' && $data) {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->logError('Curl error: ' . $error);
            return [
                'success' => false,
                'error' => 'Connection error: ' . $error
            ];
        }

        $result = json_decode($response, true);

        // Log API calls for debugging
        $this->logApiCall($method, $endpoint, $data, $result, $httpCode);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $result
            ];
        }

        return [
            'success' => false,
            'error' => $result['message'] ?? 'Unknown error',
            'http_code' => $httpCode,
            'response' => $result
        ];
    }

    /**
     * Create a virtual bank account for a user/transaction
     * This generates a unique account number for the payment
     */
    public function createVirtualAccount($data) {
        $payload = [
            'account_name' => $data['account_name'] ?? 'EduTrack Payment',
            'bank_code' => $data['bank_code'] ?? null, // Optional: specific bank
            'bvn' => $data['bvn'] ?? null, // Optional: BVN verification
            'tx_ref' => $data['reference'],
            'is_permanent' => false, // Temporary account for this transaction
            'meta' => [
                'user_id' => $data['user_id'] ?? null,
                'enrollment_id' => $data['enrollment_id'] ?? null,
                'course_id' => $data['course_id'] ?? null,
                'amount' => $data['amount'] ?? null
            ]
        ];

        return $this->request('POST', self::ENDPOINT_VIRTUAL_ACCOUNTS, $payload);
    }

    /**
     * Get virtual account details
     */
    public function getVirtualAccount($accountId) {
        return $this->request('GET', self::ENDPOINT_VIRTUAL_ACCOUNTS . '/' . $accountId);
    }

    /**
     * Get list of transactions
     */
    public function getTransactions($filters = []) {
        return $this->request('GET', self::ENDPOINT_TRANSACTIONS, $filters);
    }

    /**
     * Get single transaction details
     */
    public function getTransaction($transactionId) {
        return $this->request('GET', self::ENDPOINT_TRANSACTIONS . '/' . $transactionId);
    }

    /**
     * Verify a transaction by reference
     */
    public function verifyTransaction($reference) {
        return $this->request('GET', self::ENDPOINT_TRANSACTIONS . '/verify/' . $reference);
    }

    /**
     * Get available banks
     */
    public function getBanks() {
        return $this->request('GET', self::ENDPOINT_BANKS);
    }

    /**
     * Get account balance
     */
    public function getBalance() {
        return $this->request('GET', self::ENDPOINT_BALANCE);
    }

    /**
     * Initialize a payment transaction
     * Creates virtual account and stores transaction details
     */
    public function initializePayment($paymentData) {
        // Generate unique reference
        $reference = $this->generateReference();

        // Create virtual account for this payment
        $accountResult = $this->createVirtualAccount([
            'reference' => $reference,
            'account_name' => 'EDUTRACK-' . ($paymentData['user_id'] ?? 'GUEST'),
            'user_id' => $paymentData['user_id'] ?? null,
            'enrollment_id' => $paymentData['enrollment_id'] ?? null,
            'course_id' => $paymentData['course_id'] ?? null,
            'amount' => $paymentData['amount'] ?? null
        ]);

        if (!$accountResult['success']) {
            return $accountResult;
        }

        $accountData = $accountResult['data']['data'] ?? $accountResult['data'];

        // Store pending transaction in database
        $this->storePendingTransaction([
            'reference' => $reference,
            'user_id' => $paymentData['user_id'],
            'enrollment_id' => $paymentData['enrollment_id'] ?? null,
            'course_id' => $paymentData['course_id'] ?? null,
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'ZMW',
            'virtual_account_number' => $accountData['account_number'] ?? null,
            'virtual_account_bank' => $accountData['bank_name'] ?? null,
            'virtual_account_name' => $accountData['account_name'] ?? null,
            'lenco_account_id' => $accountData['id'] ?? null,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ]);

        return [
            'success' => true,
            'reference' => $reference,
            'payment_info' => [
                'account_number' => $accountData['account_number'] ?? null,
                'account_name' => $accountData['account_name'] ?? 'EduTrack Payment',
                'bank_name' => $accountData['bank_name'] ?? 'Lenco',
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'ZMW',
                'reference' => $reference,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
            ]
        ];
    }

    /**
     * Store pending Lenco transaction
     */
    private function storePendingTransaction($data) {
        $sql = "INSERT INTO lenco_transactions (
            reference, user_id, enrollment_id, course_id, amount, currency,
            virtual_account_number, virtual_account_bank, virtual_account_name,
            lenco_account_id, status, expires_at, created_at
        ) VALUES (
            :reference, :user_id, :enrollment_id, :course_id, :amount, :currency,
            :virtual_account_number, :virtual_account_bank, :virtual_account_name,
            :lenco_account_id, 'pending', :expires_at, NOW()
        )";

        return $this->db->query($sql, $data);
    }

    /**
     * Get pending transaction by reference
     */
    public function getPendingTransaction($reference) {
        $sql = "SELECT * FROM lenco_transactions WHERE reference = :reference";
        return $this->db->query($sql, ['reference' => $reference])->fetch();
    }

    /**
     * Get pending transaction by virtual account
     */
    public function getPendingTransactionByAccount($accountNumber) {
        $sql = "SELECT * FROM lenco_transactions
                WHERE virtual_account_number = :account_number
                AND status = 'pending'
                ORDER BY created_at DESC LIMIT 1";
        return $this->db->query($sql, ['account_number' => $accountNumber])->fetch();
    }

    /**
     * Update Lenco transaction status
     */
    public function updateTransactionStatus($reference, $status, $lencoTransactionId = null, $paidAt = null) {
        $sql = "UPDATE lenco_transactions SET
                status = :status,
                lenco_transaction_id = :lenco_transaction_id,
                paid_at = :paid_at,
                updated_at = NOW()
                WHERE reference = :reference";

        return $this->db->query($sql, [
            'reference' => $reference,
            'status' => $status,
            'lenco_transaction_id' => $lencoTransactionId,
            'paid_at' => $paidAt
        ]);
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature) {
        if (empty($this->webhookSecret)) {
            $this->logError('Webhook secret not configured');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process webhook notification
     */
    public function processWebhook($payload) {
        $data = is_array($payload) ? $payload : json_decode($payload, true);

        if (!$data) {
            $this->logError('Invalid webhook payload');
            return ['success' => false, 'error' => 'Invalid payload'];
        }

        $event = $data['event'] ?? '';
        $eventData = $data['data'] ?? [];

        $this->logWebhook($event, $data);

        switch ($event) {
            case 'transaction.successful':
            case 'virtualaccount.credit':
                return $this->handleSuccessfulPayment($eventData);

            case 'transaction.failed':
                return $this->handleFailedPayment($eventData);

            case 'transaction.reversed':
                return $this->handleReversedPayment($eventData);

            default:
                $this->logError('Unknown webhook event: ' . $event);
                return ['success' => true, 'message' => 'Event ignored'];
        }
    }

    /**
     * Handle successful payment from webhook
     */
    private function handleSuccessfulPayment($data) {
        // Get transaction reference from metadata or virtual account
        $reference = $data['tx_ref'] ?? $data['reference'] ?? null;
        $accountNumber = $data['account_number'] ?? null;

        // Try to find pending transaction
        $pendingTx = null;
        if ($reference) {
            $pendingTx = $this->getPendingTransaction($reference);
        }
        if (!$pendingTx && $accountNumber) {
            $pendingTx = $this->getPendingTransactionByAccount($accountNumber);
            $reference = $pendingTx['reference'] ?? null;
        }

        if (!$pendingTx) {
            $this->logError('Pending transaction not found for webhook');
            return ['success' => false, 'error' => 'Transaction not found'];
        }

        // Verify amount matches (with small tolerance for fees)
        $expectedAmount = floatval($pendingTx['amount']);
        $receivedAmount = floatval($data['amount'] ?? 0);

        if ($receivedAmount < ($expectedAmount * 0.99)) { // Allow 1% tolerance
            $this->logError("Amount mismatch: expected {$expectedAmount}, received {$receivedAmount}");
            // Still process but flag for review
        }

        // Update Lenco transaction status
        $this->updateTransactionStatus(
            $reference,
            self::STATUS_SUCCESSFUL,
            $data['id'] ?? null,
            date('Y-m-d H:i:s')
        );

        // Create/update payment record
        $paymentResult = $this->createPaymentRecord($pendingTx, $data);

        if ($paymentResult['success']) {
            // Send confirmation email
            $this->sendPaymentConfirmation($pendingTx, $data);
        }

        return $paymentResult;
    }

    /**
     * Handle failed payment from webhook
     */
    private function handleFailedPayment($data) {
        $reference = $data['tx_ref'] ?? $data['reference'] ?? null;

        if ($reference) {
            $this->updateTransactionStatus($reference, self::STATUS_FAILED);
        }

        return ['success' => true, 'message' => 'Failed payment recorded'];
    }

    /**
     * Handle reversed payment from webhook
     */
    private function handleReversedPayment($data) {
        $reference = $data['tx_ref'] ?? $data['reference'] ?? null;

        if ($reference) {
            $this->updateTransactionStatus($reference, self::STATUS_REVERSED);

            // Mark payment as refunded
            $payment = Payment::findByReference($reference);
            if ($payment) {
                $payment->update(['payment_status' => 'Refunded']);
            }
        }

        return ['success' => true, 'message' => 'Reversed payment recorded'];
    }

    /**
     * Create payment record from Lenco transaction
     */
    private function createPaymentRecord($pendingTx, $webhookData) {
        require_once __DIR__ . '/Payment.php';
        require_once __DIR__ . '/PaymentPlan.php';

        // Get student ID
        $student = $this->db->query(
            "SELECT id FROM students WHERE user_id = :user_id",
            ['user_id' => $pendingTx['user_id']]
        )->fetch();

        if (!$student) {
            $this->logError('Student not found for user: ' . $pendingTx['user_id']);
            return ['success' => false, 'error' => 'Student not found'];
        }

        // Get payment plan if exists
        $paymentPlan = null;
        if ($pendingTx['enrollment_id']) {
            $paymentPlan = $this->db->query(
                "SELECT id FROM enrollment_payment_plans WHERE enrollment_id = :enrollment_id",
                ['enrollment_id' => $pendingTx['enrollment_id']]
            )->fetch();
        }

        // Get Lenco payment method ID
        $paymentMethod = $this->db->query(
            "SELECT payment_method_id FROM payment_methods WHERE method_name LIKE '%Lenco%' OR method_name LIKE '%Bank Transfer%' LIMIT 1"
        )->fetch();
        $methodId = $paymentMethod['payment_method_id'] ?? 3; // Default to Bank Transfer

        // Insert payment record
        $sql = "INSERT INTO payments (
            student_id, course_id, enrollment_id, payment_plan_id,
            amount, currency, payment_method_id, transaction_id,
            payment_status, payment_date, phone_number, notes, created_at
        ) VALUES (
            :student_id, :course_id, :enrollment_id, :payment_plan_id,
            :amount, :currency, :payment_method_id, :transaction_id,
            'Completed', NOW(), :phone_number, :notes, NOW()
        )";

        $amount = floatval($webhookData['amount'] ?? $pendingTx['amount']);

        $result = $this->db->query($sql, [
            'student_id' => $student['id'],
            'course_id' => $pendingTx['course_id'],
            'enrollment_id' => $pendingTx['enrollment_id'],
            'payment_plan_id' => $paymentPlan['id'] ?? null,
            'amount' => $amount,
            'currency' => $pendingTx['currency'] ?? 'ZMW',
            'payment_method_id' => $methodId,
            'transaction_id' => $pendingTx['reference'],
            'phone_number' => $webhookData['sender_phone'] ?? null,
            'notes' => 'Lenco Payment - ' . ($webhookData['narration'] ?? 'Bank Transfer')
        ]);

        if (!$result) {
            return ['success' => false, 'error' => 'Failed to create payment record'];
        }

        $paymentId = $this->db->lastInsertId();

        // Update payment plan if exists (trigger should handle this, but manual update as backup)
        if ($paymentPlan) {
            $plan = PaymentPlan::find($paymentPlan['id']);
            if ($plan) {
                $plan->recordPayment($amount, $paymentId);
            }
        }

        // Update enrollment status if 30% or more paid
        if ($pendingTx['enrollment_id']) {
            $this->updateEnrollmentAccess($pendingTx['enrollment_id']);
        }

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'message' => 'Payment recorded successfully'
        ];
    }

    /**
     * Update enrollment access based on payment
     */
    private function updateEnrollmentAccess($enrollmentId) {
        // Get enrollment and payment plan
        $sql = "SELECT e.*, epp.total_fee, epp.total_paid, epp.balance
                FROM enrollments e
                LEFT JOIN enrollment_payment_plans epp ON e.id = epp.enrollment_id
                WHERE e.id = :enrollment_id";

        $enrollment = $this->db->query($sql, ['enrollment_id' => $enrollmentId])->fetch();

        if (!$enrollment) return;

        $totalFee = floatval($enrollment['total_fee'] ?? 0);
        $totalPaid = floatval($enrollment['total_paid'] ?? 0);

        // Check if 30% or more paid
        $percentPaid = $totalFee > 0 ? ($totalPaid / $totalFee) * 100 : 0;

        if ($percentPaid >= 30) {
            // Unlock course access
            $this->db->query(
                "UPDATE enrollments SET enrollment_status = 'In Progress' WHERE id = :id AND enrollment_status = 'Enrolled'",
                ['id' => $enrollmentId]
            );
        }

        // Check if fully paid
        if ($percentPaid >= 100) {
            $this->db->query(
                "UPDATE enrollments SET payment_status = 'completed', certificate_blocked = 0 WHERE id = :id",
                ['id' => $enrollmentId]
            );
        }
    }

    /**
     * Send payment confirmation email
     */
    private function sendPaymentConfirmation($pendingTx, $webhookData) {
        // Get user details
        $user = $this->db->query(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $pendingTx['user_id']]
        )->fetch();

        if (!$user || empty($user['email'])) return;

        // Get course details
        $course = null;
        if ($pendingTx['course_id']) {
            $course = $this->db->query(
                "SELECT * FROM courses WHERE id = :id",
                ['id' => $pendingTx['course_id']]
            )->fetch();
        }

        $amount = floatval($webhookData['amount'] ?? $pendingTx['amount']);

        // Build email content
        $subject = "Payment Received - " . ($course['title'] ?? 'EduTrack');

        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2 style='color: #2563eb;'>Payment Confirmation</h2>
            <p>Dear {$user['first_name']},</p>
            <p>We have received your payment. Thank you!</p>

            <div style='background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Payment Details</h3>
                <p><strong>Amount:</strong> K" . number_format($amount, 2) . "</p>
                <p><strong>Reference:</strong> {$pendingTx['reference']}</p>
                <p><strong>Course:</strong> " . ($course['title'] ?? 'N/A') . "</p>
                <p><strong>Date:</strong> " . date('F j, Y g:i A') . "</p>
            </div>

            <p>Your course access has been updated. You can now continue learning!</p>

            <p style='margin-top: 30px;'>
                <a href='" . env('APP_URL') . "/my-courses.php'
                   style='background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;'>
                    Go to My Courses
                </a>
            </p>

            <p style='margin-top: 30px; color: #666;'>
                Best regards,<br>
                Edutrack Computer Training College
            </p>
        </body>
        </html>
        ";

        // Use the mailer if available
        try {
            if (function_exists('sendEmail')) {
                sendEmail($user['email'], $subject, $message);
            } else {
                // Fallback to PHP mail
                $headers = [
                    'MIME-Version: 1.0',
                    'Content-type: text/html; charset=UTF-8',
                    'From: ' . env('MAIL_FROM_NAME', 'EduTrack') . ' <' . env('MAIL_FROM_ADDRESS', 'noreply@edutrack.com') . '>'
                ];
                mail($user['email'], $subject, $message, implode("\r\n", $headers));
            }
        } catch (Exception $e) {
            $this->logError('Failed to send confirmation email: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique payment reference
     */
    public function generateReference() {
        return 'LENCO-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
    }

    /**
     * Check payment status by reference
     */
    public function checkPaymentStatus($reference) {
        // First check local database
        $localTx = $this->getPendingTransaction($reference);

        if ($localTx && $localTx['status'] === self::STATUS_SUCCESSFUL) {
            return [
                'success' => true,
                'status' => 'completed',
                'transaction' => $localTx
            ];
        }

        // Verify with Lenco API
        $result = $this->verifyTransaction($reference);

        if ($result['success']) {
            $txData = $result['data']['data'] ?? $result['data'];
            $status = $txData['status'] ?? 'pending';

            // Update local record if status changed
            if ($localTx && $localTx['status'] !== $status) {
                $this->updateTransactionStatus($reference, $status);

                // If successful and not yet processed
                if ($status === 'successful' && $localTx['status'] === 'pending') {
                    $this->handleSuccessfulPayment($txData);
                }
            }

            return [
                'success' => true,
                'status' => $status,
                'transaction' => $txData
            ];
        }

        return [
            'success' => false,
            'status' => 'unknown',
            'error' => $result['error'] ?? 'Unable to verify payment'
        ];
    }

    /**
     * Get user's Lenco transactions
     */
    public function getUserTransactions($userId, $status = null) {
        $sql = "SELECT lt.*, c.title as course_title
                FROM lenco_transactions lt
                LEFT JOIN courses c ON lt.course_id = c.id
                WHERE lt.user_id = :user_id";

        $params = ['user_id' => $userId];

        if ($status) {
            $sql .= " AND lt.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY lt.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Cancel expired pending transactions
     */
    public function cancelExpiredTransactions() {
        $sql = "UPDATE lenco_transactions
                SET status = 'expired', updated_at = NOW()
                WHERE status = 'pending' AND expires_at < NOW()";

        return $this->db->query($sql);
    }

    /**
     * Get payment statistics
     */
    public function getStats($startDate = null, $endDate = null) {
        $sql = "SELECT
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status = 'successful' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'successful' THEN amount ELSE 0 END) as total_amount
                FROM lenco_transactions WHERE 1=1";

        $params = [];

        if ($startDate) {
            $sql .= " AND created_at >= :start_date";
            $params['start_date'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND created_at <= :end_date";
            $params['end_date'] = $endDate;
        }

        return $this->db->query($sql, $params)->fetch();
    }

    /**
     * Log API call for debugging
     */
    private function logApiCall($method, $endpoint, $request, $response, $httpCode) {
        $logFile = STORAGE_PATH . '/logs/lenco_api.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $log = sprintf(
            "[%s] %s %s\nRequest: %s\nResponse (%d): %s\n\n",
            date('Y-m-d H:i:s'),
            $method,
            $endpoint,
            json_encode($request),
            $httpCode,
            json_encode($response)
        );

        file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log webhook event
     */
    private function logWebhook($event, $data) {
        $logFile = STORAGE_PATH . '/logs/lenco_webhooks.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $log = sprintf(
            "[%s] Event: %s\nData: %s\n\n",
            date('Y-m-d H:i:s'),
            $event,
            json_encode($data)
        );

        file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log error
     */
    private function logError($message) {
        $logFile = STORAGE_PATH . '/logs/lenco_errors.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $log = sprintf("[%s] ERROR: %s\n", date('Y-m-d H:i:s'), $message);
        file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX);
        error_log('Lenco: ' . $message);
    }
}
