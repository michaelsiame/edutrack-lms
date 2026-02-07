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
        
        $result = $this->db->query($sql, ['id' => $this->id])->fetch();
        $this->data = $result ?: [];
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
     * Generate PDF invoice using TCPDF
     */
    public function generatePDF() {
        if (!class_exists('TCPDF')) {
            require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
        }

        if (!class_exists('TCPDF')) {
            return $this->generateHTML();
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('EduTrack LMS');
        $pdf->SetAuthor('EduTrack Computer Training College');
        $pdf->SetTitle('Invoice ' . $this->getInvoiceNumber());
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        // Header
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Cell(0, 12, 'INVOICE', 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $this->getInvoiceNumber(), 0, 1, 'R');
        $pdf->Ln(5);

        // Company info
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, defined('SITE_NAME') ? SITE_NAME : 'EduTrack Computer Training College', 0, 1);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, 'Date: ' . formatDate($this->getCreatedAt()), 0, 1);
        if ($this->isPaid()) {
            $pdf->Cell(0, 5, 'Paid: ' . formatDate($this->getPaidAt()), 0, 1);
        }
        $pdf->Ln(8);

        // Bill To
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'Bill To:', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, $this->getUserName(), 0, 1);
        $pdf->Cell(0, 5, $this->getUserEmail(), 0, 1);
        if ($this->getUserPhone()) {
            $pdf->Cell(0, 5, $this->getUserPhone(), 0, 1);
        }
        if ($this->getUserAddress()) {
            $pdf->Cell(0, 5, $this->getUserAddress(), 0, 1);
        }
        $pdf->Ln(8);

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(100, 8, 'Description', 1, 0, 'L', true);
        $pdf->Cell(30, 8, 'Qty', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Currency', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Amount', 1, 1, 'R', true);

        // Table row
        $pdf->SetFont('helvetica', '', 10);
        $description = $this->getCourseTitle() ?: 'Course enrollment';
        $pdf->Cell(100, 8, $description, 1, 0, 'L');
        $pdf->Cell(30, 8, '1', 1, 0, 'C');
        $pdf->Cell(25, 8, $this->getCurrency(), 1, 0, 'C');
        $pdf->Cell(25, 8, number_format($this->getAmount(), 2), 1, 1, 'R');

        if ($this->getTaxAmount() > 0) {
            $pdf->Cell(155, 8, 'Tax', 1, 0, 'R');
            $pdf->Cell(25, 8, number_format($this->getTaxAmount(), 2), 1, 1, 'R');
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(155, 8, 'Total', 1, 0, 'R');
        $pdf->Cell(25, 8, number_format($this->getTotalAmount(), 2), 1, 1, 'R');

        // Status
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $statusColor = $this->isPaid() ? [0, 128, 0] : [200, 0, 0];
        $pdf->SetTextColor(...$statusColor);
        $pdf->Cell(0, 8, 'Status: ' . strtoupper($this->getStatus()), 0, 1, 'R');
        $pdf->SetTextColor(0, 0, 0);

        if ($this->getPaymentMethod()) {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 6, 'Payment Method: ' . $this->getPaymentMethod(), 0, 1, 'R');
        }

        // Save to file
        $outputDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/invoices/' : sys_get_temp_dir() . '/invoices/';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $filename = 'invoice-' . $this->getInvoiceNumber() . '.pdf';
        $filepath = $outputDir . $filename;
        $pdf->Output($filepath, 'F');

        return $filepath;
    }

    /**
     * Generate HTML invoice (fallback when TCPDF not available)
     */
    private function generateHTML() {
        $html = '<html><head><style>';
        $html .= 'body{font-family:Arial,sans-serif;margin:20px}';
        $html .= 'table{width:100%;border-collapse:collapse;margin:20px 0}';
        $html .= 'th,td{border:1px solid #ddd;padding:8px;text-align:left}';
        $html .= 'th{background:#f0f0f0}';
        $html .= '.total{font-weight:bold;font-size:1.1em}';
        $html .= '.status-paid{color:green}.status-unpaid{color:red}';
        $html .= '</style></head><body>';
        $html .= '<h1>INVOICE</h1>';
        $html .= '<p>' . sanitize($this->getInvoiceNumber()) . '</p>';
        $html .= '<p>Date: ' . formatDate($this->getCreatedAt()) . '</p>';
        $html .= '<h3>Bill To:</h3>';
        $html .= '<p>' . sanitize($this->getUserName()) . '<br>' . sanitize($this->getUserEmail()) . '</p>';
        $html .= '<table><tr><th>Description</th><th>Amount</th></tr>';
        $html .= '<tr><td>' . sanitize($this->getCourseTitle() ?: 'Course enrollment') . '</td>';
        $html .= '<td>' . sanitize($this->getCurrency()) . ' ' . number_format($this->getAmount(), 2) . '</td></tr>';
        if ($this->getTaxAmount() > 0) {
            $html .= '<tr><td>Tax</td><td>' . number_format($this->getTaxAmount(), 2) . '</td></tr>';
        }
        $html .= '<tr class="total"><td>Total</td>';
        $html .= '<td>' . sanitize($this->getCurrency()) . ' ' . number_format($this->getTotalAmount(), 2) . '</td></tr>';
        $html .= '</table>';
        $statusClass = $this->isPaid() ? 'status-paid' : 'status-unpaid';
        $html .= '<p class="' . $statusClass . '">Status: ' . strtoupper(sanitize($this->getStatus())) . '</p>';
        $html .= '</body></html>';

        return $html;
    }

    /**
     * Send invoice email
     */
    public function sendEmail() {
        try {
            if (!function_exists('sendEmail')) {
                require_once dirname(__DIR__) . '/includes/email.php';
            }

            $email = $this->getUserEmail();
            if (empty($email)) {
                error_log("Invoice email skipped - no email for invoice: " . $this->getInvoiceNumber());
                return false;
            }

            $subject = 'Invoice ' . $this->getInvoiceNumber() . ' from ' . (defined('SITE_NAME') ? SITE_NAME : 'EduTrack');

            $body = '<h2>Invoice ' . sanitize($this->getInvoiceNumber()) . '</h2>';
            $body .= '<p>Dear ' . sanitize($this->getUserName()) . ',</p>';
            $body .= '<p>Please find your invoice details below:</p>';
            $body .= '<table style="border-collapse:collapse;width:100%">';
            $body .= '<tr><td style="padding:8px;border:1px solid #ddd"><strong>Course</strong></td>';
            $body .= '<td style="padding:8px;border:1px solid #ddd">' . sanitize($this->getCourseTitle()) . '</td></tr>';
            $body .= '<tr><td style="padding:8px;border:1px solid #ddd"><strong>Amount</strong></td>';
            $body .= '<td style="padding:8px;border:1px solid #ddd">' . sanitize($this->getCurrency()) . ' ' . number_format($this->getTotalAmount(), 2) . '</td></tr>';
            $body .= '<tr><td style="padding:8px;border:1px solid #ddd"><strong>Status</strong></td>';
            $body .= '<td style="padding:8px;border:1px solid #ddd">' . sanitize($this->getStatus()) . '</td></tr>';
            $body .= '</table>';

            $attachments = [];
            try {
                $pdfPath = $this->generatePDF();
                if ($pdfPath && file_exists($pdfPath)) {
                    $attachments[] = $pdfPath;
                }
            } catch (Exception $e) {
                error_log("Invoice PDF generation failed: " . $e->getMessage());
            }

            return sendEmail($email, $subject, $body, $attachments);
        } catch (Exception $e) {
            error_log("Invoice email failed for invoice {$this->getInvoiceNumber()}: " . $e->getMessage());
            return false;
        }
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