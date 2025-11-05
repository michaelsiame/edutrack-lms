<?php
/**
 * Certificate Class
 * Handles TEVETA certificate generation and management
 */

// Conditionally load TCPDF if vendor/autoload.php exists
$vendorPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (file_exists($vendorPath)) {
    require_once $vendorPath;
}

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
        require_once __DIR__ . '/Enrollment.php';
        $enrollment = Enrollment::getByUserAndCourse($userId, $courseId);
        
        if (!$enrollment || $enrollment['progress'] < 100) {
            return false;
        }
        
        // Generate certificate number and verification code
        $certNumber = self::generateCertificateNumber();
        $verifyCode = self::generateVerificationCode();
        
        // Create certificate record
        $sql = "INSERT INTO certificates (
            user_id, course_id, certificate_number, verification_code,
            final_score, issued_at
        ) VALUES (?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $userId,
            $courseId,
            $certNumber,
            $verifyCode,
            $enrollment['final_score'] ?? 0
        ];
        
        if ($db->query($sql, $params)) {
            $certId = $db->lastInsertId();
            return new self($certId);
        }
        
        return false;
    }
    
    /**
     * Generate unique certificate number
     */
    private static function generateCertificateNumber() {
        // Format: EDUTRACK-YYYYMM-00001
        $prefix = 'EDUTRACK-' . date('Ym');
        
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

        // Check if TCPDF is available
        if (!class_exists('TCPDF')) {
            error_log('TCPDF library not available. Run: composer install');
            return false;
        }

        // Create new PDF document
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack computer training college');
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
        $logoPath = PUBLIC_PATH . '/assets/images/logo.png';
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 25, 20, 30);
        }
        
        // TEVETA Logo (if exists)
        $tevataLogoPath = PUBLIC_PATH . '/assets/images/teveta-logo.png';
        if (file_exists($tevataLogoPath)) {
            $pdf->Image($tevataLogoPath, 242, 20, 30);
        }
        
        // Add content...
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(46, 112, 218);
        $pdf->SetY(35);
        $pdf->Cell(0, 10, 'EDUTRACK COMPUTER TRAINING COLLEGE', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'TEVETA Registered Institution', 0, 1, 'C');
        
        // Certificate Title
        $pdf->SetY(60);
        $pdf->SetFont('helvetica', 'B', 36);
        $pdf->SetTextColor(246, 183, 69);
        $pdf->Cell(0, 15, 'CERTIFICATE OF COMPLETION', 0, 1, 'C');
        
        // Student Name
        $pdf->SetY(85);
        $pdf->SetFont('helvetica', 'B', 28);
        $pdf->SetTextColor(0, 0, 0);
        $studentName = $this->data['first_name'] . ' ' . $this->data['last_name'];
        $pdf->Cell(0, 12, strtoupper($studentName), 0, 1, 'C');
        
        // Course Title
        $pdf->SetY(105);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(46, 112, 218);
        $pdf->Cell(0, 10, $this->data['course_title'], 0, 1, 'C');
        
        // Details
        $pdf->SetY(120);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(100, 100, 100);
        $completionDate = date('F j, Y', strtotime($this->data['issued_at']));
        $pdf->Cell(0, 6, 'Completed on ' . $completionDate, 0, 1, 'C');
        
        // Certificate Number
        $pdf->SetY(130);
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->Cell(0, 5, 'Certificate No: ' . $this->data['certificate_number'], 0, 1, 'C');
        
        // Verification URL
        $verifyUrl = url('verify-certificate.php?code=' . $this->data['verification_code']);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Verify at: ' . $verifyUrl, 0, 1, 'C');
        
        // Output PDF
        $filename = 'certificate-' . $this->data['certificate_number'] . '.pdf';
        $filepath = STORAGE_PATH . '/certificates/' . $filename;
        
        // Create directory if not exists
        if (!is_dir(STORAGE_PATH . '/certificates')) {
            mkdir(STORAGE_PATH . '/certificates', 0755, true);
        }
        
        $pdf->Output($filepath, 'F');
        
        return $filepath;
    }
    
    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getUserId() { return $this->data['user_id'] ?? null; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCertificateNumber() { return $this->data['certificate_number'] ?? ''; }
    public function getVerificationCode() { return $this->data['verification_code'] ?? ''; }
    public function getFinalScore() { return $this->data['final_score'] ?? 0; }
    public function getIssuedAt() { return $this->data['issued_at'] ?? null; }
    public function getStudentName() { 
        return trim(($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''));
    }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getInstructorName() {
        return trim(($this->data['instructor_fname'] ?? '') . ' ' . ($this->data['instructor_lname'] ?? ''));
    }
    
    public function __get($key) {
        return $this->data[$key] ?? null;
    }
}