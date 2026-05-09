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
        // Join through enrollments for schema compatibility
        // (certificates table may or may not have user_id/course_id directly)
        // NOTE: only select columns guaranteed to exist in production schema.
        // Removed: e.final_grade, co.level, co.duration_hours (not present on Hostinger DB)
        $sql = "SELECT c.*, 
                       e.user_id, e.course_id,
                       u.first_name, u.last_name, u.email,
                       co.title as course_title,
                       i.first_name as instructor_fname, i.last_name as instructor_lname
                FROM certificates c
                JOIN enrollments e ON c.enrollment_id = e.id
                JOIN users u ON e.user_id = u.id
                JOIN courses co ON e.course_id = co.id
                LEFT JOIN instructors inst ON co.instructor_id = inst.id
                LEFT JOIN users i ON inst.user_id = i.id
                WHERE c.certificate_id = ?";
        
        $result = $this->db->query($sql, [$this->id])->fetch();
        $this->data = $result ?: [];
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
        error_log("[CERT-DEBUG] Certificate::findByVerificationCode() — code='" . substr($code, 0, 50) . "'");
        $db = Database::getInstance();
        $sql = "SELECT certificate_id FROM certificates WHERE verification_code = ?";
        $id = $db->fetchColumn($sql, [$code]);
        
        if ($id) {
            error_log("[CERT-DEBUG] Certificate::findByVerificationCode() — found cert_id={$id}");
        } else {
            error_log("[CERT-DEBUG] Certificate::findByVerificationCode() — no match found");
        }
        
        return $id ? new self($id) : null;
    }
    
    /**
     * Get user's certificates
     */
    public static function getByUser($userId) {
        $db = Database::getInstance();
        $sql = "SELECT c.*, co.title as course_title, co.thumbnail
                FROM certificates c
                JOIN enrollments e ON c.enrollment_id = e.id
                JOIN courses co ON e.course_id = co.id
                WHERE e.user_id = ?
                ORDER BY c.issued_date DESC";
        
        return $db->query($sql, [$userId])->fetchAll();
    }
    
    /**
     * Get certificate by user and course
     */
    public static function getByUserAndCourse($userId, $courseId) {
        $db = Database::getInstance();
        $sql = "SELECT c.certificate_id 
                FROM certificates c
                JOIN enrollments e ON c.enrollment_id = e.id
                WHERE e.user_id = ? AND e.course_id = ?";
        $id = $db->fetchColumn($sql, [$userId, $courseId]);
        
        return $id ? new self($id) : null;
    }
    
    /**
     * Generate certificate for a student
     * Uses transaction to prevent race conditions on duplicate certificates
     */
    public static function generate($userId, $courseId) {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            // Verify course completion and get enrollment
            require_once __DIR__ . '/Enrollment.php';
            $enrollment = Enrollment::findByUserAndCourse($userId, $courseId);
            
            if (!$enrollment || $enrollment->getProgress() < 100) {
                $db->rollBack();
                return false;
            }
            
            $enrollmentId = $enrollment->getId();
            
            // Check if certificate already exists (WITHIN transaction with FOR UPDATE)
            $existing = $db->fetchOne(
                "SELECT certificate_id FROM certificates WHERE enrollment_id = ? FOR UPDATE",
                [$enrollmentId]
            );
            
            if ($existing) {
                $db->commit(); // Nothing to do, return existing
                return new self($existing['certificate_id']);
            }
            
            // Generate certificate number and verification code
            $certNumber = self::generateCertificateNumber();
            $verifyCode = self::generateVerificationCode();
            
            // Create certificate record using only columns guaranteed to exist
            // (enrollment_id links to users/courses via enrollments table)
            $sql = "INSERT INTO certificates (
                enrollment_id, certificate_number, verification_code, issued_date
            ) VALUES (?, ?, ?, CURDATE())";
            
            $params = [
                $enrollmentId,
                $certNumber,
                $verifyCode
            ];
            
            if (!$db->query($sql, $params)) {
                throw new Exception('Failed to create certificate record');
            }
            
            $certId = $db->lastInsertId();
            $db->commit();
            return new self($certId);
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Certificate generation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate unique certificate number
     */
    private static function generateCertificateNumber() {
        // Format: EDUTRACK-YYYYMM-00001
        $prefix = 'EDUTRACK-' . date('Ym');
        
        $db = Database::getInstance();
        
        // Advisory lock to prevent race conditions on certificate number generation
        $db->query("SELECT GET_LOCK('cert_number_gen', 10)");
        
        try {
            $lastNumber = $db->fetchColumn("
                SELECT certificate_number 
                FROM certificates 
                WHERE certificate_number LIKE ?
                ORDER BY certificate_id DESC 
                LIMIT 1
            ", [$prefix . '-%']);
            
            if ($lastNumber) {
                $parts = explode('-', $lastNumber);
                $sequence = (int)end($parts) + 1;
            } else {
                $sequence = 1;
            }
            
            return $prefix . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
        } finally {
            $db->query("SELECT RELEASE_LOCK('cert_number_gen')");
        }
    }
    
    /**
     * Generate unique verification code
     */
    private static function generateVerificationCode() {
        return strtoupper(bin2hex(random_bytes(8))); // 16 character code
    }
    
    /**
     * Build the certificate HTML with all placeholders replaced.
     * Useful for debugging the template without involving TCPDF.
     */
    public function getDebugHtml() {
        if (!$this->exists()) {
            error_log('[CERT-DEBUG] Certificate::getDebugHtml() — certificate does not exist');
            return false;
        }

        $templatePath = SRC_PATH . '/templates/certificate-pdf.php';
        if (!file_exists($templatePath)) {
            error_log('[CERT-DEBUG] Certificate::getDebugHtml() — template not found: ' . $templatePath);
            return false;
        }

        $html = file_get_contents($templatePath);
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        $logoPath = PUBLIC_PATH . '/assets/images/logo-sm.png';
        $tevetaLogoPath = PUBLIC_PATH . '/assets/images/teveta-logo-sm.png';

        $replacements = [
            '{{logo_path}}'        => file_exists($logoPath) ? $logoPath : '',
            '{{teveta_logo_path}}' => file_exists($tevetaLogoPath) ? $tevetaLogoPath : '',
            '{{teveta_code}}'      => env('TEVETA_INSTITUTION_CODE', 'TVA/2064'),
            '{{student_name}}'     => strtoupper($this->getStudentName()),
            '{{course_title}}'     => htmlspecialchars($this->getCourseTitle()),
            '{{completion_date}}'  => date('F j, Y', strtotime($this->data['issued_at'] ?? $this->data['issued_date'] ?? 'now')),
            '{{certificate_number}}' => $this->getCertificateNumber(),
            '{{verify_url}}'       => url('verify-certificate.php?code=' . $this->getVerificationCode()),
            '{{director_name}}'    => 'Michael Siame',
            '{{instructor_name}}'  => $this->getInstructorName() ?: '',
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $html);
        $html = preg_replace('/<img[^>]+src=""[^>]*>/i', '', $html);
        return $html;
    }

    public function generatePDF() {
        $certId = $this->getId() ?? 'unknown';
        error_log("[CERT-DEBUG] Certificate::generatePDF() — start for cert_id={$certId}");

        if (!$this->exists()) {
            error_log("[CERT-DEBUG] Certificate::generatePDF() — certificate does not exist. Returning false.");
            return false;
        }

        if (!class_exists('TCPDF')) {
            error_log('[CERT-DEBUG] Certificate::generatePDF() — TCPDF class not available. Returning false.');
            return false;
        }
        error_log('[CERT-DEBUG] Certificate::generatePDF() — TCPDF class is available');

        // Load HTML template
        $templatePath = SRC_PATH . '/templates/certificate-pdf.php';
        if (!file_exists($templatePath)) {
            error_log('[CERT-DEBUG] Certificate::generatePDF() — template not found: ' . $templatePath);
            return false;
        }
        error_log('[CERT-DEBUG] Certificate::generatePDF() — template loaded: ' . $templatePath);
        
        $html = file_get_contents($templatePath);
        
        // Remove HTML comments (TCPDF may render them)
        $html = preg_replace('/<!--.*?-->/s', '', $html);
        
        // Build placeholder replacements
        $logoPath = PUBLIC_PATH . '/assets/images/logo-sm.png';
        $tevetaLogoPath = PUBLIC_PATH . '/assets/images/teveta-logo-sm.png';
        
        $logoExists = file_exists($logoPath);
        $tevetaExists = file_exists($tevetaLogoPath);
        error_log("[CERT-DEBUG] Certificate::generatePDF() — logo exists={$logoExists} path={$logoPath}, teveta exists={$tevetaExists} path={$tevetaLogoPath}");
        
        $replacements = [
            '{{logo_path}}'        => $logoExists ? $logoPath : '',
            '{{teveta_logo_path}}' => $tevetaExists ? $tevetaLogoPath : '',
            '{{teveta_code}}'      => env('TEVETA_INSTITUTION_CODE', 'TVA/2064'),
            '{{student_name}}'     => strtoupper($this->getStudentName()),
            '{{course_title}}'     => htmlspecialchars($this->getCourseTitle()),
            '{{completion_date}}'  => date('F j, Y', strtotime($this->data['issued_at'] ?? $this->data['issued_date'] ?? 'now')),
            '{{certificate_number}}' => $this->getCertificateNumber(),
            '{{verify_url}}'       => url('verify-certificate.php?code=' . $this->getVerificationCode()),
            '{{director_name}}'    => 'Michael Siame',
            '{{instructor_name}}'  => $this->getInstructorName() ?: '',
        ];
        
        $html = str_replace(array_keys($replacements), array_values($replacements), $html);
        
        // Remove <img> tags with empty src to avoid TCPDF warnings
        $html = preg_replace('/<img[^>]+src=""[^>]*>/i', '', $html);
        
        error_log('[CERT-DEBUG] Certificate::generatePDF() — placeholders replaced. HTML length=' . strlen($html));

        try {
            // Create PDF
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            error_log('[CERT-DEBUG] Certificate::generatePDF() — TCPDF instance created');
            
            $pdf->SetCreator('Edutrack LMS');
            $pdf->SetAuthor('Edutrack Computer Training College');
            $pdf->SetTitle('Certificate - ' . $this->getCertificateNumber());
            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(false);
            
            $pdf->AddPage();
            error_log('[CERT-DEBUG] Certificate::generatePDF() — PDF page added');
            
            // Render HTML to PDF
            $pdf->writeHTML($html, true, false, true, false, '');
            error_log('[CERT-DEBUG] Certificate::generatePDF() — writeHTML() completed');
            
            // Output as string (on-demand generation, not stored to disk)
            $output = $pdf->Output('', 'S');
            error_log('[CERT-DEBUG] Certificate::generatePDF() — Output() completed. Size=' . strlen($output) . ' bytes');
            return $output;
        } catch (Exception $e) {
            error_log('[CERT-DEBUG] Certificate::generatePDF() — TCPDF EXCEPTION: ' . get_class($e) . ' — ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return false;
        } catch (Error $e) {
            error_log('[CERT-DEBUG] Certificate::generatePDF() — TCPDF ERROR: ' . get_class($e) . ' — ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return false;
        }
    }
    
    // Getters
    public function getId() { return $this->data['certificate_id'] ?? null; }
    public function getUserId() { return $this->data['user_id'] ?? null; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCertificateNumber() { return $this->data['certificate_number'] ?? ''; }
    public function getVerificationCode() { return $this->data['verification_code'] ?? ''; }
    public function getFinalScore() { return $this->data['final_grade'] ?? $this->data['final_score'] ?? 0; }
    public function getIssuedAt() { return $this->data['issued_at'] ?? $this->data['issued_date'] ?? null; }
    public function getStudentName() { 
        return trim(($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''));
    }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getInstructorName() {
        return trim(($this->data['instructor_fname'] ?? '') . ' ' . ($this->data['instructor_lname'] ?? ''));
    }
    
    /**
     * Return raw certificate data array (for debugging)
     */
    public function getData() {
        return $this->data;
    }

    public function __get($key) {
        return $this->data[$key] ?? null;
    }
}