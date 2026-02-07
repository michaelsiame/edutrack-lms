<?php
/**
 * PaymentPlan Class
 * Handles course fee payment tracking with partial payments
 */

class PaymentPlan {
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
     * Load payment plan data
     */
    private function load() {
        $sql = "SELECT epp.*,
                u.username, u.first_name, u.last_name, u.email,
                c.title as course_title, c.slug as course_slug
                FROM enrollment_payment_plans epp
                JOIN users u ON epp.user_id = u.id
                JOIN courses c ON epp.course_id = c.id
                WHERE epp.id = :id";

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
        $plan = new self($id);
        return $plan->exists() ? $plan : null;
    }

    /**
     * Find by enrollment ID
     */
    public static function findByEnrollment($enrollmentId) {
        $db = Database::getInstance();
        $sql = "SELECT id FROM enrollment_payment_plans WHERE enrollment_id = :enrollment_id";
        $result = $db->query($sql, ['enrollment_id' => $enrollmentId])->fetch();

        return $result ? new self($result['id']) : null;
    }

    /**
     * Get all payment plans for a user
     */
    public static function getByUser($userId) {
        $db = Database::getInstance();
        $sql = "SELECT epp.*,
                c.title as course_title, c.slug as course_slug
                FROM enrollment_payment_plans epp
                JOIN courses c ON epp.course_id = c.id
                WHERE epp.user_id = :user_id
                ORDER BY epp.created_at DESC";

        return $db->query($sql, ['user_id' => $userId])->fetchAll();
    }

    /**
     * Get plans with outstanding balance
     */
    public static function getWithBalance($userId = null) {
        $db = Database::getInstance();
        $sql = "SELECT epp.*,
                u.username, u.first_name, u.last_name, u.email,
                c.title as course_title, c.slug as course_slug
                FROM enrollment_payment_plans epp
                JOIN users u ON epp.user_id = u.id
                JOIN courses c ON epp.course_id = c.id
                WHERE epp.balance > 0";

        $params = [];

        if ($userId) {
            $sql .= " AND epp.user_id = :user_id";
            $params['user_id'] = $userId;
        }

        $sql .= " ORDER BY epp.balance DESC";

        return $db->query($sql, $params)->fetchAll();
    }

    /**
     * Create payment plan for enrollment
     */
    public static function create($data) {
        $db = Database::getInstance();

        // Check if plan already exists for this enrollment
        $existing = self::findByEnrollment($data['enrollment_id']);
        if ($existing) {
            return $existing->getId();
        }

        $sql = "INSERT INTO enrollment_payment_plans (
            enrollment_id, user_id, course_id, total_fee, total_paid,
            currency, payment_status, due_date, notes
        ) VALUES (
            :enrollment_id, :user_id, :course_id, :total_fee, :total_paid,
            :currency, :payment_status, :due_date, :notes
        )";

        $params = [
            'enrollment_id' => $data['enrollment_id'],
            'user_id' => $data['user_id'],
            'course_id' => $data['course_id'],
            'total_fee' => $data['total_fee'],
            'total_paid' => $data['total_paid'] ?? 0,
            'currency' => $data['currency'] ?? 'ZMW',
            'payment_status' => $data['payment_status'] ?? 'pending',
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null
        ];

        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }

    /**
     * Record a payment
     */
    public function recordPayment($amount, $paymentId = null) {
        $newTotal = $this->getTotalPaid() + $amount;
        $newStatus = $newTotal >= $this->getTotalFee() ? 'completed' : 'partial';

        $sql = "UPDATE enrollment_payment_plans
                SET total_paid = :total_paid,
                    payment_status = :status,
                    updated_at = NOW()
                WHERE id = :id";

        $params = [
            'id' => $this->id,
            'total_paid' => $newTotal,
            'status' => $newStatus
        ];

        if ($this->db->query($sql, $params)) {
            $this->load();

            // Update enrollment payment status and certificate_blocked
            $this->updateEnrollmentStatus();

            return true;
        }
        return false;
    }

    /**
     * Update enrollment status based on payment
     */
    private function updateEnrollmentStatus() {
        $enrollmentId = $this->getEnrollmentId();
        $isFullyPaid = $this->getBalance() <= 0;

        $sql = "UPDATE enrollments
                SET payment_status = :payment_status,
                    amount_paid = :amount_paid,
                    certificate_blocked = :blocked,
                    updated_at = NOW()
                WHERE id = :id";

        $params = [
            'id' => $enrollmentId,
            'payment_status' => $isFullyPaid ? 'completed' : 'pending',
            'amount_paid' => $this->getTotalPaid(),
            'blocked' => $isFullyPaid ? 0 : 1
        ];

        $this->db->query($sql, $params);
    }

    /**
     * Get all with filters
     */
    public static function all($options = []) {
        $db = Database::getInstance();

        $sql = "SELECT epp.*,
                u.username, u.first_name, u.last_name, u.email,
                c.title as course_title, c.slug as course_slug
                FROM enrollment_payment_plans epp
                JOIN users u ON epp.user_id = u.id
                JOIN courses c ON epp.course_id = c.id
                WHERE 1=1";

        $params = [];

        if (isset($options['status'])) {
            $sql .= " AND epp.payment_status = :status";
            $params['status'] = $options['status'];
        }

        if (isset($options['user_id'])) {
            $sql .= " AND epp.user_id = :user_id";
            $params['user_id'] = $options['user_id'];
        }

        if (isset($options['has_balance']) && $options['has_balance']) {
            $sql .= " AND epp.balance > 0";
        }

        $sql .= " ORDER BY epp.created_at DESC";

        if (isset($options['limit'])) {
            $sql .= " LIMIT " . intval($options['limit']);
        }

        return $db->query($sql, $params)->fetchAll();
    }

    /**
     * Get payment history for this plan
     */
    public function getPaymentHistory() {
        $sql = "SELECT p.*, pm.method_name as payment_method_name,
                CONCAT(u.first_name, ' ', u.last_name) as recorded_by_name
                FROM payments p
                LEFT JOIN payment_methods pm ON p.payment_method_id = pm.payment_method_id
                LEFT JOIN users u ON p.recorded_by = u.id
                WHERE p.payment_plan_id = :plan_id
                ORDER BY p.created_at DESC";

        return $this->db->query($sql, ['plan_id' => $this->id])->fetchAll();
    }

    /**
     * Get statistics
     */
    public static function getStats() {
        $db = Database::getInstance();
        $sql = "SELECT
                COUNT(*) as total_plans,
                SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as fully_paid,
                SUM(CASE WHEN payment_status = 'partial' THEN 1 ELSE 0 END) as partial,
                SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(total_fee) as total_fees,
                SUM(total_paid) as total_collected,
                SUM(balance) as total_outstanding
                FROM enrollment_payment_plans";

        return $db->query($sql)->fetch();
    }

    /**
     * Get total outstanding balance (all students)
     */
    public static function getTotalOutstanding() {
        $db = Database::getInstance();
        $sql = "SELECT SUM(balance) as total FROM enrollment_payment_plans WHERE balance > 0";
        $result = $db->query($sql)->fetch();
        return floatval($result['total'] ?? 0);
    }

    /**
     * Check if certificate is blocked for enrollment
     */
    public static function isCertificateBlocked($enrollmentId) {
        $plan = self::findByEnrollment($enrollmentId);
        if (!$plan) {
            return false;
        }
        return $plan->getBalance() > 0;
    }

    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getEnrollmentId() { return $this->data['enrollment_id'] ?? null; }
    public function getUserId() { return $this->data['user_id'] ?? null; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getTotalFee() { return floatval($this->data['total_fee'] ?? 0); }
    public function getTotalPaid() { return floatval($this->data['total_paid'] ?? 0); }
    public function getBalance() { return floatval($this->data['balance'] ?? 0); }
    public function getCurrency() { return $this->data['currency'] ?? 'ZMW'; }
    public function getPaymentStatus() { return $this->data['payment_status'] ?? 'pending'; }
    public function getDueDate() { return $this->data['due_date'] ?? null; }
    public function getNotes() { return $this->data['notes'] ?? ''; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }

    // Related data getters
    public function getUserName() {
        return trim(($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''));
    }
    public function getUserEmail() { return $this->data['email'] ?? ''; }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getCourseSlug() { return $this->data['course_slug'] ?? ''; }

    /**
     * Status checks
     */
    public function isFullyPaid() { return $this->getBalance() <= 0; }
    public function isPartiallyPaid() { return $this->getTotalPaid() > 0 && $this->getBalance() > 0; }
    public function isUnpaid() { return $this->getTotalPaid() <= 0; }

    /**
     * Get payment progress percentage
     */
    public function getProgressPercentage() {
        if ($this->getTotalFee() <= 0) return 100;
        return min(100, round(($this->getTotalPaid() / $this->getTotalFee()) * 100, 1));
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge() {
        $status = $this->getPaymentStatus();
        $badges = [
            'completed' => '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Fully Paid</span>',
            'partial' => '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Partial</span>',
            'pending' => '<span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Unpaid</span>',
            'overdue' => '<span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Overdue</span>'
        ];

        return $badges[$status] ?? '<span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">Unknown</span>';
    }

    /**
     * Get formatted amounts
     */
    public function getFormattedTotalFee() {
        return 'K' . number_format($this->getTotalFee(), 2);
    }

    public function getFormattedTotalPaid() {
        return 'K' . number_format($this->getTotalPaid(), 2);
    }

    public function getFormattedBalance() {
        return 'K' . number_format($this->getBalance(), 2);
    }
}
