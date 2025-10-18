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
        $sql = "SELECT p.*, u.first_name, u.last_name, u.email,
                c.title as course_title, c.slug as course_slug
                FROM payments p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN courses c ON p.course_id = c.id
                WHERE p.id = :id";
        
        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
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
        $sql = "SELECT id FROM payments WHERE transaction_reference = :reference";
        $result = $db->query($sql, ['reference' => $reference])->fetch();
        
        return $result ? new self($result['id']) : null;
    }
    
    /**
     * Get user payments
     */
    public static function getByUser($userId) {
        $db = Database::getInstance();
        $sql = "SELECT p.*, c.title as course_title 
                FROM payments p
                LEFT JOIN courses c ON p.course_id = c.id
                WHERE p.user_id = :user_id
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
                JOIN users u ON p.user_id = u.id
                WHERE p.course_id = :course_id
                ORDER BY p.created_at DESC";
        
        return $db->query($sql, ['course_id' => $courseId])->fetchAll();
    }
    
    /**
     * Create new payment
     */
    public static function create($data) {
        $db = Database::getInstance();
        
        // Generate unique reference
        $reference = self::generateReference();
        
        $sql = "INSERT INTO payments (
            user_id, course_id, amount, currency, payment_method,
            transaction_reference, phone_number, status
        ) VALUES (
            :user_id, :course_id, :amount, :currency, :payment_method,
            :transaction_reference, :phone_number, :status
        )";
        
        $params = [
            'user_id' => $data['user_id'],
            'course_id' => $data['course_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'ZMW',
            'payment_method' => $data['payment_method'],
            'transaction_reference' => $reference,
            'phone_number' => $data['phone_number'] ?? null,
            'status' => 'pending'
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
        $allowed = ['status', 'transaction_id', 'provider_reference', 
                   'payment_date', 'notes'];
        
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
        
        $sql = "UPDATE payments SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :id";
        
        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }
    
    /**
     * Mark as successful
     */
    public function markSuccessful($transactionId = null, $providerReference = null) {
        $result = $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'provider_reference' => $providerReference,
            'payment_date' => date('Y-m-d H:i:s')
        ]);
        
        if ($result && $this->getCourseId()) {
            // Auto-enroll in course
            require_once __DIR__ . '/Enrollment.php';
            
            $enrollmentData = [
                'user_id' => $this->getUserId(),
                'course_id' => $this->getCourseId(),
                'enrollment_status' => 'active',
                'payment_status' => 'paid',
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
            'status' => 'failed',
            'notes' => $notes
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
        $sql = "SELECT COUNT(*) as count FROM payments WHERE status = 'pending'";
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
                    WHERE status = 'completed' AND course_id = :course_id";
            $result = $db->query($sql, ['course_id' => $courseId])->fetch();
        } else {
            $sql = "SELECT SUM(amount) as total 
                    FROM payments 
                    WHERE status = 'completed'";
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
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as avg_payment
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
    public function getId() { return $this->data['id'] ?? null; }
    public function getUserId() { return $this->data['user_id'] ?? null; }
    public function getUserName() { 
        return trim(($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''));
    }
    public function getUserEmail() { return $this->data['email'] ?? ''; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getCourseSlug() { return $this->data['course_slug'] ?? ''; }
    public function getAmount() { return $this->data['amount'] ?? 0; }
    public function getCurrency() { return $this->data['currency'] ?? 'ZMW'; }
    public function getPaymentMethod() { return $this->data['payment_method'] ?? ''; }
    public function getTransactionReference() { return $this->data['transaction_reference'] ?? ''; }
    public function getTransactionId() { return $this->data['transaction_id'] ?? null; }
    public function getProviderReference() { return $this->data['provider_reference'] ?? null; }
    public function getPhoneNumber() { return $this->data['phone_number'] ?? null; }
    public function getStatus() { return $this->data['status'] ?? 'pending'; }
    public function getPaymentDate() { return $this->data['payment_date'] ?? null; }
    public function getNotes() { return $this->data['notes'] ?? ''; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getUpdatedAt() { return $this->data['updated_at'] ?? null; }
    
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
        return $this->getStatus() == 'completed';
    }
    
    /**
     * Check if pending
     */
    public function isPending() {
        return $this->getStatus() == 'pending';
    }
    
    /**
     * Check if failed
     */
    public function isFailed() {
        return $this->getStatus() == 'failed';
    }
    
    /**
     * Get status badge HTML
     */
    public function getStatusBadge() {
        $status = $this->getStatus();
        $badges = [
            'completed' => '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Completed</span>',
            'pending' => '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Pending</span>',
            'failed' => '<span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Failed</span>'
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