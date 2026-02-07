<?php
/**
 * RegistrationFee Class
 * Handles K150 registration fee (one-time, paid to bank)
 */

class RegistrationFee {
    private $db;
    private $id;
    private $data = [];

    // Default registration fee amount
    const DEFAULT_FEE = 150.00;
    const CURRENCY = 'ZMW';

    public function __construct($id = null) {
        $this->db = Database::getInstance();
        if ($id) {
            $this->id = $id;
            $this->load();
        }
    }

    /**
     * Load registration fee data
     */
    private function load() {
        $sql = "SELECT rf.*, u.username, u.first_name, u.last_name, u.email, u.phone
                FROM registration_fees rf
                JOIN users u ON rf.user_id = u.id
                WHERE rf.id = :id";

        $result = $this->db->query($sql, ['id' => $this->id])->fetch();
        $this->data = $result ?: [];
    }

    /**
     * Check if exists
     */
    public function exists() {
        return !empty($this->data);
    }

    /**
     * Find by ID
     */
    public static function find($id) {
        $fee = new self($id);
        return $fee->exists() ? $fee : null;
    }

    /**
     * Find by user ID
     */
    public static function findByUser($userId) {
        $db = Database::getInstance();
        $sql = "SELECT id FROM registration_fees WHERE user_id = :user_id";
        $result = $db->query($sql, ['user_id' => $userId])->fetch();

        return $result ? new self($result['id']) : null;
    }

    /**
     * Check if user has paid registration fee
     */
    public static function hasPaid($userId) {
        $db = Database::getInstance();
        $sql = "SELECT payment_status FROM registration_fees
                WHERE user_id = :user_id AND payment_status = 'completed'";
        $result = $db->query($sql, ['user_id' => $userId])->fetch();

        return !empty($result);
    }

    /**
     * Check if user has pending registration fee
     */
    public static function hasPending($userId) {
        $db = Database::getInstance();
        $sql = "SELECT id FROM registration_fees
                WHERE user_id = :user_id AND payment_status = 'pending'";
        $result = $db->query($sql, ['user_id' => $userId])->fetch();

        return !empty($result);
    }

    /**
     * Create registration fee record
     */
    public static function create($data) {
        $db = Database::getInstance();

        // Check if user already has registration fee
        $existing = self::findByUser($data['user_id']);
        if ($existing) {
            return $existing->getId();
        }

        // Get student ID if exists
        $student = $db->query("SELECT id FROM students WHERE user_id = :user_id",
                             ['user_id' => $data['user_id']])->fetch();

        $sql = "INSERT INTO registration_fees (
            user_id, student_id, amount, currency, payment_method,
            bank_reference, bank_name, deposit_date, notes
        ) VALUES (
            :user_id, :student_id, :amount, :currency, :payment_method,
            :bank_reference, :bank_name, :deposit_date, :notes
        )";

        $params = [
            'user_id' => $data['user_id'],
            'student_id' => $student['id'] ?? null,
            'amount' => $data['amount'] ?? self::DEFAULT_FEE,
            'currency' => $data['currency'] ?? self::CURRENCY,
            'payment_method' => $data['payment_method'] ?? 'bank_deposit',
            'bank_reference' => $data['bank_reference'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'deposit_date' => $data['deposit_date'] ?? null,
            'notes' => $data['notes'] ?? null
        ];

        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }

    /**
     * Update registration fee
     */
    public function update($data) {
        $allowed = ['payment_status', 'bank_reference', 'bank_name',
                   'deposit_date', 'verified_by', 'verified_at', 'notes'];

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

        $sql = "UPDATE registration_fees SET " . implode(', ', $updates) .
               ", updated_at = NOW() WHERE id = :id";

        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }

    /**
     * Verify/approve registration fee
     */
    public function verify($verifiedBy) {
        return $this->update([
            'payment_status' => 'completed',
            'verified_by' => $verifiedBy,
            'verified_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Reject registration fee
     */
    public function reject($notes = null) {
        return $this->update([
            'payment_status' => 'failed',
            'notes' => $notes
        ]);
    }

    /**
     * Get all pending registration fees
     */
    public static function getPending() {
        $db = Database::getInstance();
        $sql = "SELECT rf.*, u.username, u.first_name, u.last_name, u.email, u.phone
                FROM registration_fees rf
                JOIN users u ON rf.user_id = u.id
                WHERE rf.payment_status = 'pending'
                ORDER BY rf.created_at DESC";

        return $db->query($sql)->fetchAll();
    }

    /**
     * Get all registration fees with optional filters
     */
    public static function all($options = []) {
        $db = Database::getInstance();

        $sql = "SELECT rf.*, u.username, u.first_name, u.last_name, u.email, u.phone,
                CONCAT(v.first_name, ' ', v.last_name) as verified_by_name
                FROM registration_fees rf
                JOIN users u ON rf.user_id = u.id
                LEFT JOIN users v ON rf.verified_by = v.id
                WHERE 1=1";

        $params = [];

        if (isset($options['status'])) {
            $sql .= " AND rf.payment_status = :status";
            $params['status'] = $options['status'];
        }

        $sql .= " ORDER BY rf.created_at DESC";

        if (isset($options['limit'])) {
            $sql .= " LIMIT " . intval($options['limit']);
        }

        return $db->query($sql, $params)->fetchAll();
    }

    /**
     * Get registration fee amount from settings
     */
    public static function getFeeAmount() {
        $db = Database::getInstance();
        $sql = "SELECT setting_value FROM system_settings
                WHERE setting_key = 'registration_fee_amount'";
        $result = $db->query($sql)->fetch();

        return floatval($result['setting_value'] ?? self::DEFAULT_FEE);
    }

    /**
     * Check if registration is required
     */
    public static function isRequired() {
        $db = Database::getInstance();
        $sql = "SELECT setting_value FROM system_settings
                WHERE setting_key = 'registration_fee_required'";
        $result = $db->query($sql)->fetch();

        return ($result['setting_value'] ?? 'true') === 'true';
    }

    /**
     * Get bank details for payment
     */
    public static function getBankDetails() {
        $db = Database::getInstance();
        $sql = "SELECT setting_key, setting_value FROM system_settings
                WHERE setting_key IN ('bank_account_name', 'bank_account_number',
                                     'bank_name', 'bank_branch')";
        $results = $db->query($sql)->fetchAll();

        $details = [];
        foreach ($results as $row) {
            $key = str_replace('bank_', '', $row['setting_key']);
            $details[$key] = $row['setting_value'];
        }

        return $details;
    }

    /**
     * Get statistics
     */
    public static function getStats() {
        $db = Database::getInstance();
        $sql = "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN payment_status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_collected
                FROM registration_fees";

        return $db->query($sql)->fetch();
    }

    /**
     * Get pending count
     */
    public static function getPendingCount() {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM registration_fees WHERE payment_status = 'pending'";
        $result = $db->query($sql)->fetch();
        return $result['count'] ?? 0;
    }

    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getUserId() { return $this->data['user_id'] ?? null; }
    public function getStudentId() { return $this->data['student_id'] ?? null; }
    public function getAmount() { return floatval($this->data['amount'] ?? self::DEFAULT_FEE); }
    public function getCurrency() { return $this->data['currency'] ?? self::CURRENCY; }
    public function getPaymentStatus() { return $this->data['payment_status'] ?? 'pending'; }
    public function getPaymentMethod() { return $this->data['payment_method'] ?? ''; }
    public function getBankReference() { return $this->data['bank_reference'] ?? ''; }
    public function getBankName() { return $this->data['bank_name'] ?? ''; }
    public function getDepositDate() { return $this->data['deposit_date'] ?? null; }
    public function getVerifiedBy() { return $this->data['verified_by'] ?? null; }
    public function getVerifiedAt() { return $this->data['verified_at'] ?? null; }
    public function getNotes() { return $this->data['notes'] ?? ''; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }

    // User getters
    public function getUserName() {
        return trim(($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''));
    }
    public function getUserEmail() { return $this->data['email'] ?? ''; }
    public function getUserPhone() { return $this->data['phone'] ?? ''; }

    /**
     * Check statuses
     */
    public function isPaid() { return $this->getPaymentStatus() === 'completed'; }
    public function isPending() { return $this->getPaymentStatus() === 'pending'; }
    public function isFailed() { return $this->getPaymentStatus() === 'failed'; }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge() {
        $status = $this->getPaymentStatus();
        $badges = [
            'completed' => '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Paid</span>',
            'pending' => '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Pending</span>',
            'failed' => '<span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Rejected</span>',
            'refunded' => '<span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">Refunded</span>'
        ];

        return $badges[$status] ?? '<span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">Unknown</span>';
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmount() {
        return 'K' . number_format($this->getAmount(), 2);
    }
}
