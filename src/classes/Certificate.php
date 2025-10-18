<?php
/**
 * Certificate Class
 * Handles TEVETA certificate generation and management
 */

class Certificate {
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
     * Load certificate data
     */
    private function load() {
        $sql = "SELECT c.*, 
                       u.first_name, u.last_name, u.email,
                       co.title as course_title, co.level,
                       i.first_name as instructor_fname, i.last_name as instructor_lname
                FROM certificates c
                JOIN users u ON c.user_id = u.id
                JOIN courses co ON c.course_id = co.id
                LEFT JOIN users i ON co.instructor_id = i.id
                WHERE c.id = ?";
        
        $this->data = $this->db->query($sql, [$this->id])->fetch();
    }
    
    /**
     * Generate certificate for a student
     */
    public static function generate($userId, $courseId) {
        $db = Database::getInstance();
        
        // Check if certificate already exists
        $existing = $db->query(
            "SELECT id FROM certificates WHERE user_id = ? AND course_id = ?",
            [$userId, $courseId]
        )->fetch();
        
        if ($existing) {
            return new self($existing['id']);
        }
        
        // Check if user has completed the course
        $progress = $db->query(
            "SELECT completion_percentage FROM enrollments WHERE user_id = ? AND course_id = ?",
            [$userId, $courseId]
        )->fetch();
        
        if (!$progress || $progress['completion_percentage'] < 100) {
            throw new Exception('Course not completed');
        }
        
        // Get course details
        $course = $db->query(
            "SELECT title, level FROM courses WHERE id = ?",
            [$courseId]
        )->fetch();
        
        // Generate unique certificate number
        $certificateNumber = self::generateCertificateNumber();
        
        // Generate verification code
        $verificationCode = self::generateVerificationCode();
        
        // Insert certificate
        $sql = "INSERT INTO certificates (
                    user_id, course_id, certificate_number, verification_code,
                    issue_date, status
                ) VALUES (?, ?, ?, ?, NOW(), 'issued')";
        
        $db->query($sql, [
            $userId, $courseId, $certificateNumber, $verificationCode
        ]);
        
        $certificateId = $db->lastInsertId();
        
        // Generate PDF
        $certificate = new self($certificateId);
        $certificate->generatePDF();
        
        // Send email
        $certificate->sendEmail();
        
        return $certificate;
    }
    
    /**
     * Generate unique certificate number
     * Format: TEVETA-YEAR-MONTH-XXXXX
     */
    private static function generateCertificateNumber() {
        $prefix = 'TEVETA-' . date('Y-m');
        $db = Database::getInstance();
        
        // Get last number for this month
        $result = $db->query(
            "SELECT certificate_number FROM certificates 
             WHERE certificate_number LIKE ? 
             ORDER BY id DESC LIMIT 1",
            [$prefix . '%']
        )->fetch();
        
        if ($result) {
            // Extract number and increment
            $lastNum = intval(substr($result['certificate_number'], -5));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }
        
        return $prefix . '-' . str_pad($newNum, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate verification code
     */
    private static function generateVerificationCode() {
        return strtoupper(bin2hex(random_bytes(8))); // 16 character hex
    }
    
    /**
     * Generate PDF certificate
     */
    public function generatePDF() {
        require_once '../vendor/autoload.php'; // TCPDF or similar
        
        // Create PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('LMS Platform');
        $pdf->SetAuthor('TEVETA Zambia');
        $pdf->SetTitle('Certificate of Completion');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Add a page
        $pdf->AddPage('L'); // Landscape
        
        // Set margins
        $pdf->SetMargins(20, 20, 20);
        
        // Background image (if you have one)
        // $pdf->Image('../public/assets/images/certificate-bg.jpg', 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);
        
        // Certificate border
        $pdf->SetLineStyle(['width' => 2, 'color' => [0, 51, 102]]);
        $pdf->Rect(15, 15, 267, 180);
        
        $pdf->SetLineStyle(['width' => 0.5, 'color' => [0, 51, 102]]);
        $pdf->Rect(18, 18, 261, 174);
        
        // Logo and header
        // $pdf->Image('../public/assets/images/teveta-logo.png', 125, 25, 30, 30);
        
        // Title
        $pdf->SetFont('helvetica', 'B', 36);
        $pdf->SetTextColor(0, 51, 102);
        $pdf->SetXY(20, 65);
        $pdf->Cell(257, 15, 'CERTIFICATE OF COMPLETION', 0, 1, 'C');
        
        // Subtitle
        $pdf->SetFont('helvetica', 'I', 14);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(20, 82);
        $pdf->Cell(257, 8, 'This is to certify that', 0, 1, 'C');
        
        // Student name
        $pdf->SetFont('helvetica', 'B', 28);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(20, 95);
        $studentName = $this->data['first_name'] . ' ' . $this->data['last_name'];
        $pdf->Cell(257, 12, strtoupper($studentName), 0, 1, 'C');
        
        // Course name
        $pdf->SetFont('helvetica', 'I', 12);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(20, 112);
        $pdf->Cell(257, 8, 'has successfully completed the course', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(0, 51, 102);
        $pdf->SetXY(20, 122);
        $pdf->Cell(257, 10, strtoupper($this->data['course_title']), 0, 1, 'C');
        
        // Level (if applicable)
        if (!empty($this->data['level'])) {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetXY(20, 134);
            $pdf->Cell(257, 8, 'Level: ' . $this->data['level'], 0, 1, 'C');
        }
        
        // Certificate details
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        
        // Issue date
        $pdf->SetXY(40, 155);
        $pdf->Cell(80, 6, 'Issue Date: ' . date('F d, Y', strtotime($this->data['issue_date'])), 0, 0, 'L');
        
        // Certificate number
        $pdf->SetXY(40, 162);
        $pdf->Cell(80, 6, 'Certificate No: ' . $this->data['certificate_number'], 0, 0, 'L');
        
        // Verification code
        $pdf->SetXY(40, 169);
        $pdf->Cell(80, 6, 'Verification: ' . $this->data['verification_code'], 0, 0, 'L');
        
        // Signatures
        $pdf->SetFont('helvetica', '', 9);
        
        // Instructor signature
        $pdf->SetXY(160, 155);
        $pdf->Line(160, 165, 220, 165);
        $pdf->SetXY(160, 166);
        $instructorName = $this->data['instructor_fname'] . ' ' . $this->data['instructor_lname'];
        $pdf->Cell(60, 5, $instructorName, 0, 0, 'C');
        $pdf->SetXY(160, 171);
        $pdf->Cell(60, 5, 'Course Instructor', 0, 0, 'C');
        
        // Director signature
        $pdf->SetXY(220, 155);
        $pdf->Line(230, 165, 270, 165);
        $pdf->SetXY(220, 166);
        $pdf->Cell(60, 5, 'TEVETA Director', 0, 0, 'C');
        $pdf->SetXY(220, 171);
        $pdf->Cell(60, 5, 'TEVETA Zambia', 0, 0, 'C');
        
        // QR Code for verification
        $verifyUrl = SITE_URL . '/verify-certificate.php?code=' . $this->data['verification_code'];
        $pdf->write2DBarcode($verifyUrl, 'QRCODE,H', 245, 25, 30, 30, '', 'N');
        
        // Footer
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetXY(20, 185);
        $pdf->Cell(257, 5, 'Verify this certificate at: ' . SITE_URL . '/verify', 0, 0, 'C');
        
        // Save PDF
        $filename = 'certificate_' . $this->data['certificate_number'] . '.pdf';
        $filepath = '../public/certificates/' . $filename;
        
        // Create directory if not exists
        if (!file_exists('../public/certificates/')) {
            mkdir('../public/certificates/', 0755, true);
        }
        
        $pdf->Output($filepath, 'F');
        
        // Update database with PDF path
        $this->db->query(
            "UPDATE certificates SET pdf_path = ? WHERE id = ?",
            [$filename, $this->id]
        );
        
        $this->data['pdf_path'] = $filename;
        
        return $filepath;
    }
    
    /**
     * Send certificate email
     */
    public function sendEmail() {
        if (!$this->data) {
            throw new Exception('Certificate not loaded');
        }
        
        $to = $this->data['email'];
        $subject = '🎉 Your Certificate of Completion - ' . $this->data['course_title'];
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                          color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .certificate-box { background: white; padding: 20px; border-radius: 8px; 
                                   margin: 20px 0; border-left: 4px solid #667eea; }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; 
                         color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
                .footer { text-align: center; margin-top: 30px; color: #999; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Congratulations!</h1>
                    <p>You've earned your certificate!</p>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($this->data['first_name']) . ",</p>
                    
                    <p>Congratulations on completing <strong>" . htmlspecialchars($this->data['course_title']) . "</strong>!</p>
                    
                    <div class='certificate-box'>
                        <h3>📜 Certificate Details</h3>
                        <p><strong>Certificate Number:</strong> " . $this->data['certificate_number'] . "</p>
                        <p><strong>Issue Date:</strong> " . date('F d, Y', strtotime($this->data['issue_date'])) . "</p>
                        <p><strong>Verification Code:</strong> " . $this->data['verification_code'] . "</p>
                    </div>
                    
                    <p>Your certificate is attached to this email. You can also:</p>
                    
                    <div style='text-align: center;'>
                        <a href='" . SITE_URL . "/download-certificate.php?id=" . $this->id . "' class='button'>
                            📥 Download Certificate
                        </a>
                        <a href='" . SITE_URL . "/verify-certificate.php?code=" . $this->data['verification_code'] . "' class='button'>
                            ✅ Verify Certificate
                        </a>
                    </div>
                    
                    <p>Share your achievement on social media and with potential employers!</p>
                    
                    <p><strong>Next Steps:</strong></p>
                    <ul>
                        <li>Add this certificate to your LinkedIn profile</li>
                        <li>Include it in your resume/CV</li>
                        <li>Share it with your network</li>
                        <li>Continue learning with more courses!</li>
                    </ul>
                    
                    <p>Keep up the great work!</p>
                    
                    <p>Best regards,<br>
                    The LMS Team</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email. Please do not reply.</p>
                    <p>&copy; " . date('Y') . " LMS Platform. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Send email with attachment
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . SITE_NAME . " <noreply@" . parse_url(SITE_URL, PHP_URL_HOST) . ">\r\n";
        
        // Attach PDF if exists
        if (!empty($this->data['pdf_path'])) {
            $pdfPath = '../public/certificates/' . $this->data['pdf_path'];
            if (file_exists($pdfPath)) {
                // Use PHPMailer for attachment
                // For now, just send link
            }
        }
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Verify certificate by code
     */
    public static function verify($code) {
        $db = Database::getInstance();
        
        $sql = "SELECT c.*, 
                       u.first_name, u.last_name,
                       co.title as course_title
                FROM certificates c
                JOIN users u ON c.user_id = u.id
                JOIN courses co ON c.course_id = co.id
                WHERE c.verification_code = ? AND c.status = 'issued'";
        
        return $db->query($sql, [$code])->fetch();
    }
    
    /**
     * Get certificate by ID
     */
    public static function find($id) {
        return new self($id);
    }
    
    /**
     * Get user certificates
     */
    public static function getUserCertificates($userId) {
        $db = Database::getInstance();
        
        $sql = "SELECT c.*, co.title as course_title, co.thumbnail
                FROM certificates c
                JOIN courses co ON c.course_id = co.id
                WHERE c.user_id = ?
                ORDER BY c.issue_date DESC";
        
        return $db->query($sql, [$userId])->fetchAll();
    }
    
    /**
     * Revoke certificate
     */
    public function revoke($reason = '') {
        $this->db->query(
            "UPDATE certificates SET status = 'revoked', revoked_at = NOW(), revoke_reason = ? WHERE id = ?",
            [$reason, $this->id]
        );
        
        $this->data['status'] = 'revoked';
        return true;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getData() { return $this->data; }
    public function getCertificateNumber() { return $this->data['certificate_number'] ?? null; }
    public function getVerificationCode() { return $this->data['verification_code'] ?? null; }
    public function getPdfPath() { return $this->data['pdf_path'] ?? null; }
    public function getStatus() { return $this->data['status'] ?? null; }
    public function getIssueDate() { return $this->data['issue_date'] ?? null; }
}