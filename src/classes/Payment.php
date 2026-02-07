<?php
/**
 * Payment Class
 * Handles payment processing
 */

class Payment {
    private $db;
    private $id;
    private $data = [];
    
    public function __construct($id = null) {
        $this->db = Database::getInstance();
        if ($id) {
            $this->id = $id;
            $this->load();
        }
    }
    
    /**
     * Load payment data
     */
    private function load() {
        $sql = "SELECT p.*, u.id as user_id, u.first_name, u.last_name, u.email,
                c.title as course_title, c.slug as course_slug
                FROM payments p
                JOIN students s ON p.student_id = s.id
                JOIN users u ON s.user_id = u.id
                LEFT JOIN courses c ON p.course_id = c.id
                WHERE p.payment_id = :id";

        $result = $this->db->query($sql, ['id' => $this->id])->fetch();
        // Ensure $this->data is always an array, never false
        // This prevents TypeError in PHP 8+ when accessing array keys on false
        $this->data = $result ?: [];
    }
    
    /**
     * Check if payment exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Find payment by ID
     */
    public static function find($id) {
        $payment = new self($id);
        return $payment->exists() ? $payment : null;
    }
    
    /**
     * Find by transaction reference
     */
    public static function findByReference($reference) {
        $db = Database::getInstance();
        $sql = "SELECT payment_id FROM payments WHERE transaction_id = :reference";
        $result = $db->query($sql, ['reference' => $reference])->fetch();

        return $result ? new self($result['payment_id']) : null;
    }
    
    /**
     * Get user payments
     */
    public static function getByUser($userId) {
        $db = Database::getInstance();
        $sql = "SELECT p.*, c.title as course_title
                FROM payments p
                JOIN students s ON p.student_id = s.id
                LEFT JOIN courses c ON p.course_id = c.id
                WHERE s.user_id = :user_id
                ORDER BY p.created_at DESC";

        return $db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Get course payments
     */
    public static function getByCourse($courseId) {
        $db = Database::getInstance();
        $sql = "SELECT p.*, u.first_name, u.last_name, u.email
                FROM payments p
                JOIN students s ON p.student_id = s.id
                JOIN users u ON s.user_id = u.id
                WHERE p.course_id = :course_id
                ORDER BY p.created_at DESC";

        return $db->query($sql, ['course_id' => $courseId])->fetchAll();
    }
    
    /**
     * Create new payment
     */
    public static function create($data) {
        $db = Database::getInstance();

        // Get student_id from user_id
        $student = $db->query("SELECT id FROM students WHERE user_id = :user_id",
                             ['user_id' => $data['user_id']])->fetch();

        if (!$student) {
            return false; // User is not a student
        }

        // Generate unique reference
        $reference = self::generateReference();

        $sql = "INSERT INTO payments (
            student_id, course_id, amount, currency, payment_method_id,
            transaction_id, payment_status
        ) VALUES (
            :student_id, :course_id, :amount, :currency, :payment_method_id,
            :transaction_id, :payment_status
        )";

        $params = [
            'student_id' => $student['id'],
            'course_id' => $data['course_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'USD',
            'payment_method_id' => $data['payment_method_id'] ?? null,
            'transaction_id' => $reference,
            'payment_status' => 'Pending'
        ];

        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update payment
     */
    public function update($data) {
        $allowed = ['payment_status', 'transaction_id', 'payment_date'];

        $updates = [];
        $params = ['id' => $this->id];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE payments SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE payment_id = :id";

        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }
        /**
     * Get all payments with optional filters
     */
    public static function all($options = []) {
        $db = Database::getInstance();

        $sql = "SELECT p.*, u.first_name, u.last_name, u.email,
                c.title as course_title, c.slug as course_slug
                FROM payments p
                JOIN students s ON p.student_id = s.id
                JOIN users u ON s.user_id = u.id
                LEFT JOIN courses c ON p.course_id = c.id
                WHERE 1=1";

        $params = [];

        // Apply filters
        if (isset($options['status'])) {
            $sql .= " AND p.payment_status = :status";
            $params['status'] = $options['status'];
        }

        if (isset($options['user_id'])) {
            $sql .= " AND s.user_id = :user_id";
            $params['user_id'] = $options['user_id'];
        }

        if (isset($options['course_id'])) {
            $sql .= " AND p.course_id = :course_id";
            $params['course_id'] = $options['course_id'];
        }

        if (isset($options['payment_method_id'])) {
            $sql .= " AND p.payment_method_id = :payment_method_id";
            $params['payment_method_id'] = $options['payment_method_id'];
        }

        // Apply ordering (whitelist to prevent SQL injection)
        $allowedOrderColumns = [
            'created_at', 'amount', 'payment_status', 'payment_date',
            'payment_id', 'updated_at', 'currency'
        ];
        if (isset($options['order']) && in_array($options['order'], $allowedOrderColumns)) {
            $orderDir = (isset($options['order_dir']) && strtoupper($options['order_dir']) === 'ASC') ? 'ASC' : 'DESC';
            $sql .= " ORDER BY p." . $options['order'] . " " . $orderDir;
        } else {
            $sql .= " ORDER BY p.created_at DESC";
        }

        // Apply limit
        if (isset($options['limit'])) {
            $sql .= " LIMIT " . intval($options['limit']);

            if (isset($options['offset'])) {
                $sql .= " OFFSET " . intval($options['offset']);
            }
        }

        return $db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Mark as successful
     * Uses correct schema enum values for enrollment
     */
    public function markSuccessful($transactionId = null, $providerReference = null) {
        $result = $this->update([
            'payment_status' => 'Completed',
            'transaction_id' => $transactionId,
            'payment_date' => date('Y-m-d H:i:s')
        ]);

        if ($result && $this->getCourseId()) {
            // Auto-enroll in course using Enrollment::create()
            // Uses correct schema enum values:
            // enrollment_status: 'Enrolled', 'In Progress', 'Completed', 'Dropped', 'Expired'
            // payment_status: 'pending', 'completed', 'failed', 'refunded'
            require_once __DIR__ . '/Enrollment.php';

            $enrollmentData = [
                'user_id' => $this->getUserId(),
                'course_id' => $this->getCourseId(),
                'enrollment_status' => 'Enrolled',
                'payment_status' => 'completed',
                'amount_paid' => $this->getAmount()
            ];

            Enrollment::create($enrollmentData);

            // Generate invoice
            $this->generateInvoice();

            // Send confirmation email
            $this->sendConfirmationEmail();
        }

        return $result;
    }
    
    /**
     * Mark as failed
     */
    public function markFailed($notes = null) {
        return $this->update([
            'payment_status' => 'Failed'
        ]);
    }
    
    /**
     * Generate invoice
     */
    public function generateInvoice() {
        require_once __DIR__ . '/Invoice.php';
        
        $invoiceData = [
            'payment_id' => $this->id,
            'user_id' => $this->getUserId(),
            'course_id' => $this->getCourseId(),
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'status' => 'paid'
        ];
        
        return Invoice::create($invoiceData);
    }
    
    /**
     * Send confirmation email
     */
    private function sendConfirmationEmail() {
        // Implementation will be in mail templates
        // For now, just log
        error_log("Payment confirmation email sent for payment ID: " . $this->id);
    }
    
    /**
     * Generate unique payment reference
     */
    private static function generateReference() {
        return 'PAY-' . strtoupper(uniqid()) . '-' . time();
    }
    
    /**
     * Get pending payments count
     */
    public static function getPendingCount() {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM payments WHERE payment_status = 'Pending'";
        $result = $db->query($sql)->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get total revenue
     */
    public static function getTotalRevenue($courseId = null) {
        $db = Database::getInstance();

        if ($courseId) {
            $sql = "SELECT SUM(amount) as total
                    FROM payments
                    WHERE payment_status = 'Completed' AND course_id = :course_id";
            $result = $db->query($sql, ['course_id' => $courseId])->fetch();
        } else {
            $sql = "SELECT SUM(amount) as total
                    FROM payments
                    WHERE payment_status = 'Completed'";
            $result = $db->query($sql)->fetch();
        }

        return $result['total'] ?? 0;
    }
    
    /**
     * Get payment statistics
     */
    public static function getStats($startDate = null, $endDate = null) {
        $db = Database::getInstance();

        $sql = "SELECT
                COUNT(*) as total_payments,
                SUM(CASE WHEN payment_status = 'Completed' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN payment_status = 'Failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN payment_status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN payment_status = 'Completed' THEN amount ELSE 0 END) as total_revenue,
                AVG(CASE WHEN payment_status = 'Completed' THEN amount ELSE NULL END) as avg_payment
                FROM payments
                WHERE 1=1";

        $params = [];

        if ($startDate) {
            $sql .= " AND created_at >= :start_date";
            $params['start_date'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND created_at <= :end_date";
            $params['end_date'] = $endDate;
        }

        return $db->query($sql, $params)->fetch();
    }
    
    /**
     * Process mobile money payment
     */
    public function processMobileMoney($phoneNumber) {
        $method = $this->getPaymentMethod();
        
        switch ($method) {
            case 'mtn':
                return $this->processMTN($phoneNumber);
            case 'airtel':
                return $this->processAirtel($phoneNumber);
            case 'zamtel':
                return $this->processZamtel($phoneNumber);
            default:
                return ['success' => false, 'message' => 'Invalid payment method'];
        }
    }
    
    /**
     * Process MTN Mobile Money
     */
    private function processMTN($phoneNumber) {
        // MTN Mobile Money API integration
        // This is a placeholder - implement actual MTN API
        
        $apiUrl = config('payment.mtn.api_url');
        $apiKey = config('payment.mtn.api_key');
        
        // Simulate API call
        // In production, use actual MTN API
        
        return [
            'success' => true,
            'message' => 'Payment initiated. Please complete on your phone.',
            'transaction_id' => 'MTN-' . uniqid()
        ];
    }
    
    /**
     * Process Airtel Money
     */
    private function processAirtel($phoneNumber) {
        // Airtel Money API integration
        // This is a placeholder - implement actual Airtel API
        
        return [
            'success' => true,
            'message' => 'Payment initiated. Please complete on your phone.',
            'transaction_id' => 'AIRTEL-' . uniqid()
        ];
    }
    
    /**
     * Process Zamtel Kwacha
     */
    private function processZamtel($phoneNumber) {
        // Zamtel Kwacha API integration
        // This is a placeholder - implement actual Zamtel API
        
        return [
            'success' => true,
            'message' => 'Payment initiated. Please complete on your phone.',
            'transaction_id' => 'ZAMTEL-' . uniqid()
        ];
    }
    
    // Getters
    public function getId() { return $this->data['payment_id'] ?? null; }
    public function getUserId() {
        // Get user_id from joined students table
        return $this->data['user_id'] ?? null;
    }
    public function getUserName() {
        return trim(($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''));
    }
    public function getUserEmail() { return $this->data['email'] ?? ''; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getCourseSlug() { return $this->data['course_slug'] ?? ''; }
    public function getAmount() { return $this->data['amount'] ?? 0; }
    public function getCurrency() { return $this->data['currency'] ?? 'USD'; }
    public function getPaymentMethod() { return $this->data['payment_method_id'] ?? ''; }
    public function getTransactionReference() { return $this->data['transaction_id'] ?? ''; }
    public function getTransactionId() { return $this->data['transaction_id'] ?? null; }
    public function getStatus() { return $this->data['payment_status'] ?? 'Pending'; }
    public function getPaymentDate() { return $this->data['payment_date'] ?? null; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getUpdatedAt() { return $this->data['updated_at'] ?? null; }
    public function getPhoneNumber() { return $this->data['phone_number'] ?? ''; }
    public function getNotes() { return $this->data['notes'] ?? ''; }
    public function getRecordedBy() { return $this->data['recorded_by'] ?? null; }
    public function getPaymentType() { return $this->data['payment_type'] ?? 'course_fee'; }
    public function getProofOfPayment() { return $this->data['proof_of_payment'] ?? null; }

    /**
     * Get proof of payment URL
     * Returns full URL to the uploaded proof image/PDF
     */
    public function getProofOfPaymentUrl() {
        $proof = $this->getProofOfPayment();
        if ($proof && file_exists(PUBLIC_PATH . '/uploads/payments/' . $proof)) {
            return url('uploads/payments/' . $proof);
        }
        return null;
    }

    /**
     * Check if proof of payment exists
     */
    public function hasProofOfPayment() {
        return $this->getProofOfPaymentUrl() !== null;
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmount() {
        return formatCurrency($this->getAmount());
    }
    
    /**
     * Check if completed
     */
    public function isCompleted() {
        return $this->getStatus() == 'Completed';
    }

    /**
     * Check if pending
     */
    public function isPending() {
        return $this->getStatus() == 'Pending';
    }

    /**
     * Check if failed
     */
    public function isFailed() {
        return $this->getStatus() == 'Failed';
    }
    
    /**
     * Get status badge HTML
     */
    public function getStatusBadge() {
        $status = $this->getStatus();
        $badges = [
            'Completed' => '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Completed</span>',
            'Pending' => '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Pending</span>',
            'Failed' => '<span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Failed</span>',
            'Refunded' => '<span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">Refunded</span>',
            'Cancelled' => '<span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">Cancelled</span>'
        ];

        return $badges[$status] ?? '<span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">Unknown</span>';
    }
    
    /**
     * Get payment method label
     */
    public function getPaymentMethodLabel() {
        $methods = [
            'mtn' => 'MTN Mobile Money',
            'airtel' => 'Airtel Money',
            'zamtel' => 'Zamtel Kwacha',
            'bank_transfer' => 'Bank Transfer'
        ];
        
        return $methods[$this->getPaymentMethod()] ?? $this->getPaymentMethod();
    }
}