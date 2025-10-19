<?php
/**
 * Certificate Class
 * Handles TEVETA certificate generation and management
 */

require_once __DIR__ . '/../vendor/autoload.php'; // For TCPDF

use TCPDF;

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
                       co.title as course_title, co.level, co.duration_hours,
                       i.first_name as instructor_fname, i.last_name as instructor_lname
                FROM certificates c
                JOIN users u ON c.user_id = u.id
                JOIN courses co ON c.course_id = co.id
                LEFT JOIN users i ON co.instructor_id = i.id
                WHERE c.id = ?";
        
        $this->data = $this->db->query($sql, [$this->id])->fetch();
    }
    
    /**
     * Check if certificate exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Find certificate by ID
     */
    public static function find($id) {
        $cert = new self($id);
        return $cert->exists() ? $cert : null;
    }
    
    /**
     * Find certificate by verification code
     */
    public static function findByVerificationCode($code) {
        $db = Database::getInstance();
        $sql = "SELECT id FROM certificates WHERE verification_code = ?";
        $id = $db->fetchColumn($sql, [$code]);
        
        return $id ? new self($id) : null;
    }
    
    /**
     * Get user's certificates
     */
    public static function getByUser($userId) {
        $db = Database::getInstance();
        $sql = "SELECT c.*, co.title as course_title, co.thumbnail
                FROM certificates c
                JOIN courses co ON c.course_id = co.id
                WHERE c.user_id = ?
                ORDER BY c.issued_at DESC";
        
        return $db->query($sql, [$userId])->fetchAll();
    }
    
    /**
     * Get certificate by user and course
     */
    public static function getByUserAndCourse($userId, $courseId) {
        $db = Database::getInstance();
        $sql = "SELECT id FROM certificates WHERE user_id = ? AND course_id = ?";
        $id = $db->fetchColumn($sql, [$userId, $courseId]);
        
        return $id ? new self($id) : null;
    }
    
    /**
     * Generate certificate for a student
     */
    public static function generate($userId, $courseId) {
        $db = Database::getInstance();
        
        // Check if certificate already exists
        $existing = self::getByUserAndCourse($userId, $courseId);
        if ($existing) {
            return $existing;
        }
        
        // Verify course completion
        require_once __DIR__ . '/Progress.php';
        $progress = new Progress();
        $courseProgress = $progress->getCourseProgress($userId, $courseId);
        
        if ($courseProgress['percentage'] < 100) {
            return false; // Course not completed
        }
        
        // Generate unique certificate number and verification code
        $certificateNumber = self::generateCertificateNumber();
        $verificationCode = self::generateVerificationCode();
        
        // Get final grade (average of all quiz scores)
        $finalGrade = $db->fetchColumn("
            SELECT AVG(qa.score)
            FROM quiz_attempts qa
            JOIN quizzes q ON qa.quiz_id = q.id
            WHERE qa.user_id = ? AND q.course_id = ?
        ", [$userId, $courseId]) ?? 0;
        
        $sql = "INSERT INTO certificates (
            user_id, course_id, certificate_number, verification_code,
            final_grade, issued_at
        ) VALUES (?, ?, ?, ?, ?, NOW())";
        
        if ($db->query($sql, [$userId, $courseId, $certificateNumber, $verificationCode, $finalGrade])) {
            $certificateId = $db->lastInsertId();
            $certificate = new self($certificateId);
            
            // Generate PDF
            $certificate->generatePDF();
            
            // Send email notification
            $certificate->sendEmail();
            
            return $certificate;
        }
        
        return false;
    }
    
    /**
     * Generate unique certificate number
     * Format: TEVETA-YYYY-MM-XXXXX
     */
    private static function generateCertificateNumber() {
        $prefix = 'TEVETA-' . date('Y-m');
        
        $db = Database::getInstance();
        $lastNumber = $db->fetchColumn("
            SELECT certificate_number 
            FROM certificates 
            WHERE certificate_number LIKE ?
            ORDER BY id DESC 
            LIMIT 1
        ", [$prefix . '-%']);
        
        if ($lastNumber) {
            $parts = explode('-', $lastNumber);
            $sequence = (int)end($parts) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate unique verification code
     */
    private static function generateVerificationCode() {
        return strtoupper(bin2hex(random_bytes(8))); // 16 character code
    }
    
    /**
     * Generate PDF certificate
     */
    public function generatePDF() {
        if (!$this->exists()) {
            return false;
        }
        
        // Create new PDF document
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack Computer Training College');
        $pdf->SetTitle('Certificate of Completion');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 12);
        
        // Colors
        $primaryBlue = [46, 112, 218];
        $accentGold = [246, 183, 69];
        
        // Border
        $pdf->SetLineStyle(['width' => 2, 'color' => $primaryBlue]);
        $pdf->Rect(10, 10, 277, 190);
        $pdf->SetLineStyle(['width' => 1, 'color' => $accentGold]);
        $pdf->Rect(12, 12, 273, 186);
        
        // Logo (if exists)
        $logoPath = PUBLIC_PATH . '/assets/images/teveta-logo.png';
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 125, 20, 40);
        }
        
        // Title
        $pdf->SetY(45);
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor($primaryBlue[0], $primaryBlue[1], $primaryBlue[2]);
        $pdf->Cell(0, 15, 'CERTIFICATE OF COMPLETION', 0, 1, 'C');
        
        // TEVETA Badge
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor($accentGold[0], $accentGold[1], $accentGold[2]);
        $pdf->Cell(0, 8, 'TEVETA CERTIFIED TRAINING', 0, 1, 'C');
        
        // Student name
        $pdf->SetY(75);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'This is to certify that', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor($primaryBlue[0], $primaryBlue[1], $primaryBlue[2]);
        $pdf->Cell(0, 12, strtoupper($this->getStudentName()), 0, 1, 'C');
        
        // Course info
        $pdf->SetY(105);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'has successfully completed the course', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor($primaryBlue[0], $primaryBlue[1], $primaryBlue[2]);
        $pdf->Cell(0, 10, $this->getCourseTitle(), 0, 1, 'C');
        
        // Duration and grade
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $durationText = 'Duration: ' . ($this->data['duration_hours'] ?? 'N/A') . ' hours';
        $gradeText = 'Final Grade: ' . round($this->getFinalGrade(), 1) . '%';
        $pdf->Cell(0, 8, $durationText . '  |  ' . $gradeText, 0, 1, 'C');
        
        // Issue date
        $pdf->SetY(140);
        $pdf->Cell(0, 8, 'Issued on ' . date('F j, Y', strtotime($this->getIssuedAt())), 0, 1, 'C');
        
        // Certificate number and verification
        $pdf->SetY(160);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(135, 6, 'Certificate No: ' . $this->getCertificateNumber(), 0, 0, 'L');
        $pdf->Cell(135, 6, 'Verification Code: ' . $this->getVerificationCode(), 0, 1, 'R');
        
        // Signatures
        $pdf->SetY(170);
        
        // Director signature
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(135, 6, '________________________', 0, 0, 'C');
        $pdf->Cell(135, 6, '________________________', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(135, 5, 'Director', 0, 0, 'C');
        $pdf->Cell(135, 5, 'Instructor', 0, 1, 'C');
        
        $pdf->Cell(135, 5, 'Edutrack Computer Training College', 0, 0, 'C');
        $instructorName = ($this->data['instructor_fname'] ?? '') . ' ' . ($this->data['instructor_lname'] ?? '');
        $pdf->Cell(135, 5, $instructorName, 0, 1, 'C');
        
        // Verification URL
        $pdf->SetY(195);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $verifyUrl = url('verify-certificate.php?code=' . $this->getVerificationCode());
        $pdf->Cell(0, 5, 'Verify this certificate at: ' . $verifyUrl, 0, 1, 'C');
        
        // Save PDF
        $filename = 'certificate_' . $this->getCertificateNumber() . '.pdf';
        $filepath = STORAGE_PATH . '/certificates/' . $filename;
        
        // Ensure directory exists
        if (!file_exists(STORAGE_PATH . '/certificates')) {
            mkdir(STORAGE_PATH . '/certificates', 0777, true);
        }
        
        $pdf->Output($filepath, 'F');
        
        // Update database with PDF filename
        $sql = "UPDATE certificates SET pdf_file = ? WHERE id = ?";
        $this->db->query($sql, [$filename, $this->id]);
        
        // Reload data
        $this->load();
        
        return $filepath;
    }
    
    /**
     * Send certificate email
     */
    public function sendEmail() {
        if (!$this->exists()) {
            return false;
        }
        
        require_once __DIR__ . '/Email.php';
        
        $email = new Email();
        $pdfPath = $this->getPDFPath();
        
        $subject = 'Congratulations! Your TEVETA Certificate is Ready';
        
        $body = "
        <h2>Congratulations, {$this->getStudentName()}!</h2>
        <p>You have successfully completed <strong>{$this->getCourseTitle()}</strong> and earned your TEVETA-certified certificate!</p>
        
        <div style='background: #f5f5f5; padding: 20px; margin: 20px 0; border-left: 4px solid #2E70DA;'>
            <p><strong>Certificate Number:</strong> {$this->getCertificateNumber()}</p>
            <p><strong>Verification Code:</strong> {$this->getVerificationCode()}</p>
            <p><strong>Final Grade:</strong> " . round($this->getFinalGrade(), 1) . "%</p>
            <p><strong>Issued:</strong> " . date('F j, Y', strtotime($this->getIssuedAt())) . "</p>
        </div>
        
        <p>Your certificate is attached to this email. You can also download it anytime from your dashboard.</p>
        
        <p>To verify your certificate, visit: " . url('verify-certificate.php?code=' . $this->getVerificationCode()) . "</p>
        
        <p>Share your achievement on social media!</p>
        
        <p>Keep learning with Edutrack!</p>
        ";
        
        return $email->send(
            $this->data['email'],
            $subject,
            $body,
            $pdfPath ? [$pdfPath] : []
        );
    }
    
    /**
     * Revoke certificate
     */
    public function revoke($reason = '') {
        $sql = "UPDATE certificates 
                SET revoked = 1, revoked_at = NOW(), revoke_reason = ? 
                WHERE id = ?";
        
        $result = $this->db->query($sql, [$reason, $this->id]);
        
        if ($result) {
            $this->load();
        }
        
        return $result;
    }
    
    /**
     * Get PDF path
     */
    public function getPDFPath() {
        if ($this->data['pdf_file']) {
            $path = STORAGE_PATH . '/certificates/' . $this->data['pdf_file'];
            return file_exists($path) ? $path : null;
        }
        return null;
    }
    
    /**
     * Get PDF URL (for download)
     */
    public function getPDFUrl() {
        if ($this->data['pdf_file']) {
            return url('download-certificate.php?id=' . $this->id);
        }
        return null;
    }
    
    /**
     * Getters
     */
    public function getId() { return $this->data['id'] ?? null; }
    public function getUserId() { return $this->data['user_id'] ?? null; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCertificateNumber() { return $this->data['certificate_number'] ?? ''; }
    public function getVerificationCode() { return $this->data['verification_code'] ?? ''; }
    public function getFinalGrade() { return $this->data['final_grade'] ?? 0; }
    public function getIssuedAt() { return $this->data['issued_at'] ?? null; }
    public function isRevoked() { return $this->data['revoked'] == 1; }
    public function getRevokedAt() { return $this->data['revoked_at'] ?? null; }
    public function getRevokeReason() { return $this->data['revoke_reason'] ?? ''; }
    public function getStudentName() { return ($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''); }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getPDFFile() { return $this->data['pdf_file'] ?? null; }
}