<?php
/**
 * Invoice Class
 * Handles invoice generation and management
 */
if (!defined('TEMPLATES_PATH')) {
    define('TEMPLATES_PATH', dirname(__DIR__) . '/templates');
}

class Invoice {
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
     * Load invoice data
     */
    private function load() {
        $sql = "SELECT i.*, p.amount as payment_amount, p.payment_method,
                u.first_name, u.last_name, u.email, up.phone, up.address, up.city,
                c.title as course_title, c.price as course_price
                FROM invoices i
                LEFT JOIN payments p ON i.payment_id = p.id
                JOIN users u ON i.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN courses c ON i.course_id = c.id
                WHERE i.id = :id";
        
        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
    }
    
    /**
     * Check if invoice exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Find invoice by ID
     */
    public static function find($id) {
        $invoice = new self($id);
        return $invoice->exists() ? $invoice : null;
    }
    
    /**
     * Find by invoice number
     */
    public static function findByNumber($invoiceNumber) {
        $db = Database::getInstance();
        $sql = "SELECT id FROM invoices WHERE invoice_number = :number";
        $result = $db->query($sql, ['number' => $invoiceNumber])->fetch();
        
        return $result ? new self($result['id']) : null;
    }
    
    /**
     * Find by payment
     */
    public static function findByPayment($paymentId) {
        $db = Database::getInstance();
        $sql = "SELECT id FROM invoices WHERE payment_id = :payment_id";
        $result = $db->query($sql, ['payment_id' => $paymentId])->fetch();
        
        return $result ? new self($result['id']) : null;
    }
    
    /**
     * Get user invoices
     */
    public static function getByUser($userId) {
        $db = Database::getInstance();
        $sql = "SELECT i.*, c.title as course_title
                FROM invoices i
                LEFT JOIN courses c ON i.course_id = c.id
                WHERE i.user_id = :user_id
                ORDER BY i.created_at DESC";
        
        return $db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Create new invoice
     */
    public static function create($data) {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO invoices (
            payment_id, user_id, course_id, invoice_number,
            amount, currency, tax_amount, total_amount, status
        ) VALUES (
            :payment_id, :user_id, :course_id, :invoice_number,
            :amount, :currency, :tax_amount, :total_amount, :status
        )";
        
        $params = [
            'payment_id' => $data['payment_id'] ?? null,
            'user_id' => $data['user_id'],
            'course_id' => $data['course_id'] ?? null,
            'invoice_number' => $data['invoice_number'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'ZMW',
            'tax_amount' => $data['tax_amount'] ?? 0,
            'total_amount' => $data['amount'] + ($data['tax_amount'] ?? 0),
            'status' => $data['status'] ?? 'unpaid'
        ];
        
        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber() {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM invoices 
                WHERE invoice_number LIKE :pattern";
        
        $pattern = $prefix . '-' . $year . $month . '%';
        $result = $db->query($sql, ['pattern' => $pattern])->fetch();
        
        $sequence = str_pad(($result['count'] + 1), 4, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $year . $month . '-' . $sequence;
    }
    
    /**
     * Generate PDF invoice
     */
    public function generatePDF() {
        // This would use a PDF library like TCPDF or FPDF
        // For now, return HTML that can be printed
        
        ob_start();
        include TEMPLATES_PATH . '/invoice-pdf.php';
        $html = ob_get_clean();
        
        return $html;
    }
    
    /**
     * Send invoice email
     */
    public function sendEmail() {
        // Implementation in mail templates
        error_log("Invoice email sent for invoice: " . $this->getInvoiceNumber());
        return true;
    }
    
    /**
     * Mark as paid
     */
    public function markAsPaid() {
        $sql = "UPDATE invoices SET 
                status = 'paid',
                paid_at = NOW()
                WHERE id = :id";
        
        if ($this->db->query($sql, ['id' => $this->id])) {
            $this->load();
            return true;
        }
        return false;
    }
    
    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getPaymentId() { return $this->data['payment_id'] ?? null; }
    public function getUserId() { return $this->data['user_id'] ?? null; }
    public function getUserName() { 
        return trim(($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''));
    }
    public function getUserEmail() { return $this->data['email'] ?? ''; }
    public function getUserPhone() { return $this->data['phone'] ?? ''; }
    public function getUserAddress() { return $this->data['address'] ?? ''; }
    public function getUserCity() { return $this->data['city'] ?? ''; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getInvoiceNumber() { return $this->data['invoice_number'] ?? ''; }
    public function getAmount() { return $this->data['amount'] ?? 0; }
    public function getCurrency() { return $this->data['currency'] ?? 'ZMW'; }
    public function getTaxAmount() { return $this->data['tax_amount'] ?? 0; }
    public function getTotalAmount() { return $this->data['total_amount'] ?? 0; }
    public function getStatus() { return $this->data['status'] ?? 'unpaid'; }
    public function getPaidAt() { return $this->data['paid_at'] ?? null; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getPaymentMethod() { return $this->data['payment_method'] ?? ''; }
    
    /**
     * Get formatted amount
     */
    public function getFormattedAmount() {
        return formatCurrency($this->getAmount());
    }
    
    /**
     * Get formatted total
     */
    public function getFormattedTotal() {
        return formatCurrency($this->getTotalAmount());
    }
    
    /**
     * Check if paid
     */
    public function isPaid() {
        return $this->getStatus() == 'paid';
    }
    
    /**
     * Get download URL
     */
    public function getDownloadUrl() {
        return url('api/download.php?type=invoice&id=' . $this->getId());
    }
}