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

    /** @var array Static cache for file existence checks (avoids repeated disk I/O) */
    private static $fileCache = [];

    public function __construct($id = null, array $preloadedData = []) {
        $this->db = Database::getInstance();
        if (!empty($preloadedData)) {
            $this->id = $preloadedData['certificate_id'] ?? $id;
            $this->data = $preloadedData;
        } elseif ($id) {
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
                WHERE c.verification_code = ?";
        $row = $db->fetchOne($sql, [$code]);

        if ($row) {
            error_log("[CERT-DEBUG] Certificate::findByVerificationCode() — found cert_id={$row['certificate_id']}");
            $cert = new self(null, $row);
            return $cert;
        }

        error_log("[CERT-DEBUG] Certificate::findByVerificationCode() — no match found");
        return null;
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
                WHERE e.user_id = ? AND e.course_id = ?";
        $row = $db->fetchOne($sql, [$userId, $courseId]);

        return $row ? new self(null, $row) : null;
    }
    
    /**
     * Get all certificates (for admin API)
     */
    public static function all() {
        $db = Database::getInstance();
        $sql = "SELECT c.*, co.title as course_title,
                       u.first_name, u.last_name
                FROM certificates c
                JOIN enrollments e ON c.enrollment_id = e.id
                JOIN courses co ON e.course_id = co.id
                JOIN users u ON e.user_id = u.id
                ORDER BY c.issued_date DESC";
        return $db->query($sql)->fetchAll();
    }

    /**
     * Find certificate by enrollment ID
     */
    public static function findByEnrollment($enrollmentId) {
        $db = Database::getInstance();
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
                WHERE c.enrollment_id = ?";
        $row = $db->fetchOne($sql, [$enrollmentId]);
        return $row ? new self(null, $row) : null;
    }

    /**
     * Issue a certificate for an enrollment (admin API)
     */
    public static function issue($enrollmentId) {
        $db = Database::getInstance();
        $enrollment = $db->fetchOne("SELECT user_id, course_id FROM enrollments WHERE id = ?", [$enrollmentId]);
        if (!$enrollment) {
            return false;
        }
        $cert = self::generate($enrollment['user_id'], $enrollment['course_id']);
        return $cert ? $cert->getId() : false;
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
        return $this->buildCertificateHtml();
    }

    /**
     * Generate PDF on-demand and return the binary content.
     * Returns PDF binary string on success, false on failure.
     */
    public function generatePDF() {
        $certId = $this->getId() ?? 'unknown';
        error_log("[CERT-DEBUG] Certificate::generatePDF() — start for cert_id={$certId}");

        if (!$this->exists()) {
            error_log("[CERT-DEBUG] Certificate::generatePDF() — certificate does not exist. Returning false.");
            return false;
        }

        // Use Dompdf if available, fallback to TCPDF
        $useDompdf = class_exists('Dompdf\Dompdf');
        $useTcpdf = class_exists('TCPDF');

        if (!$useDompdf && !$useTcpdf) {
            error_log('[CERT-DEBUG] Certificate::generatePDF() — No PDF library available. Returning false.');
            return false;
        }

        error_log('[CERT-DEBUG] Certificate::generatePDF() — Using ' . ($useDompdf ? 'Dompdf' : 'TCPDF'));

        // Build HTML with placeholders replaced
        $html = $this->buildCertificateHtml();
        if ($html === false) {
            return false;
        }

        try {
            if ($useDompdf) {
                $options = new \Dompdf\Options();
                $options->set('isRemoteEnabled', true);
                $options->set('isPhpEnabled', false);
                $options->set('defaultFont', 'DejaVu Sans');
                $options->set('chroot', PUBLIC_PATH);

                $dompdf = new \Dompdf\Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                $output = $dompdf->output();
                error_log('[CERT-DEBUG] Certificate::generatePDF() — Dompdf rendered. Size=' . strlen($output) . ' bytes');
                return $output;
            } else {
                // TCPDF fallback
                $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetCreator('Edutrack LMS');
                $pdf->SetAuthor('Edutrack Computer Training College');
                $pdf->SetTitle('Certificate - ' . $this->getCertificateNumber());
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetMargins(10, 10, 10);
                $pdf->SetAutoPageBreak(false);
                $pdf->AddPage();
                $pdf->writeHTML($html, true, false, true, false, '');
                $output = $pdf->Output('', 'S');
                error_log('[CERT-DEBUG] Certificate::generatePDF() — TCPDF rendered. Size=' . strlen($output) . ' bytes');
                return $output;
            }
        } catch (Exception $e) {
            error_log('[CERT-DEBUG] Certificate::generatePDF() — EXCEPTION: ' . get_class($e) . ' — ' . $e->getMessage());
            return false;
        } catch (Error $e) {
            error_log('[CERT-DEBUG] Certificate::generatePDF() — ERROR: ' . get_class($e) . ' — ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cached file_exists() to avoid repeated disk I/O during PDF generation.
     */
    private static function fileExistsCached(string $path): bool {
        if (!array_key_exists($path, self::$fileCache)) {
            self::$fileCache[$path] = file_exists($path);
        }
        return self::$fileCache[$path];
    }

    /**
     * Build the certificate HTML with all placeholders replaced.
     */
    private function buildCertificateHtml() {
        $useDompdf = class_exists('Dompdf\Dompdf');
        $templateName = $useDompdf ? 'certificate-dompdf.php' : 'certificate-pdf.php';
        $templatePath = SRC_PATH . '/templates/' . $templateName;

        if (!file_exists($templatePath)) {
            error_log('[CERT-DEBUG] Certificate::buildCertificateHtml() — template not found: ' . $templatePath);
            return false;
        }

        $html = file_get_contents($templatePath);
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        $logoPath = PUBLIC_PATH . '/assets/images/logo-sm.png';
        $tevetaLogoPath = PUBLIC_PATH . '/assets/images/teveta-logo-sm.png';
        $logoExists = self::fileExistsCached($logoPath);
        $tevetaExists = self::fileExistsCached($tevetaLogoPath);

        $directorSigPath = PUBLIC_PATH . '/assets/images/signatures/director.png';
        $instructorSigPath = PUBLIC_PATH . '/assets/images/signatures/instructor.png';
        $qrPath = PUBLIC_PATH . '/assets/images/qr-codes/cert-' . $this->getCertificateNumber() . '.png';

        $directorSig = self::fileExistsCached($directorSigPath)
            ? '<img src="' . $directorSigPath . '" style="max-height:36px; display:block; margin:0 auto 2px;">'
            : '';
        $instructorSig = self::fileExistsCached($instructorSigPath)
            ? '<img src="' . $instructorSigPath . '" style="max-height:36px; display:block; margin:0 auto 2px;">'
            : '';
        $qrImg = self::fileExistsCached($qrPath)
            ? '<img src="' . $qrPath . '" style="width:40px; height:40px; vertical-align:middle; margin-right:4px;">'
            : '';

        $sealPath = PUBLIC_PATH . '/assets/images/certificate-seal.png';
        $cornerTl = PUBLIC_PATH . '/assets/images/cert-corner-tl.png';
        $cornerTr = PUBLIC_PATH . '/assets/images/cert-corner-tr.png';
        $cornerBl = PUBLIC_PATH . '/assets/images/cert-corner-bl.png';
        $cornerBr = PUBLIC_PATH . '/assets/images/cert-corner-br.png';

        $issuedDate = $this->data['issued_at'] ?? $this->data['issued_date'] ?? 'now';
        $formattedDate = date('F j, Y', strtotime($issuedDate));
        $formalDate = $this->getFormalDate($issuedDate);
        $formalDateHtml = $this->getFormalDateHtml($issuedDate);
        $meritText = $this->getMeritText();

        $replacements = [
            '{{logo_path}}'            => $logoExists ? $logoPath : '',
            '{{teveta_logo_path}}'     => $tevetaExists ? $tevetaLogoPath : '',
            '{{teveta_code}}'          => env('TEVETA_INSTITUTION_CODE', 'TVA/2064'),
            '{{student_name}}'         => htmlspecialchars($this->getStudentName()),
            '{{course_title}}'         => htmlspecialchars($this->getCourseTitle()),
            '{{completion_date}}'      => $formattedDate,
            '{{formal_date}}'          => $formalDate,
            '{{formal_date_html}}'     => $formalDateHtml,
            '{{certificate_number}}'   => $this->getCertificateNumber(),
            '{{verify_url}}'           => url('verify-certificate.php?code=' . $this->getVerificationCode()),
            '{{director_name}}'        => 'Michael Siame',
            '{{principal_name}}'       => env('CERTIFICATE_PRINCIPAL_NAME', 'Michael Siame'),
            '{{instructor_name}}'      => $this->getInstructorName() ?: '',
            '{{director_signature}}'   => $directorSig,
            '{{instructor_signature}}' => $instructorSig,
            '{{qr_code}}'              => $qrImg,
            '{{student_number}}'       => $this->getStudentNumber(),
            '{{merit_text}}'           => $meritText,
            '{{graduate_id}}'          => '',
            '{{seal_path}}'            => self::fileExistsCached($sealPath) ? $sealPath : '',
            '{{corner_tl}}'            => self::fileExistsCached($cornerTl) ? $cornerTl : '',
            '{{corner_tr}}'            => self::fileExistsCached($cornerTr) ? $cornerTr : '',
            '{{corner_bl}}'            => self::fileExistsCached($cornerBl) ? $cornerBl : '',
            '{{corner_br}}'            => self::fileExistsCached($cornerBr) ? $cornerBr : '',
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $html);
        $html = preg_replace('/<img[^>]+src=""[^>]*>/i', '', $html);

        return $html;
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

    /**
     * Get merit text based on final score
     */
    public function getMeritText() {
        $score = (float) ($this->data['final_score'] ?? 0);
        if ($score >= 90) {
            return 'With Distinction';
        } elseif ($score >= 80) {
            return 'With Merit';
        } elseif ($score >= 70) {
            return 'With Credit';
        } elseif ($score > 0) {
            return 'Pass';
        }
        return '';
    }

    /**
     * Get formal date string e.g. "27th day of March in the year 2026"
     */
    private function getFormalDate($dateStr) {
        $ts = strtotime($dateStr);
        $day = date('j', $ts);
        $month = date('F', $ts);
        $year = date('Y', $ts);

        $suffix = 'th';
        if (!in_array(($day % 100), [11, 12, 13])) {
            switch ($day % 10) {
                case 1: $suffix = 'st'; break;
                case 2: $suffix = 'nd'; break;
                case 3: $suffix = 'rd'; break;
            }
        }

        // Return plain text (no HTML tags) for TCPDF compatibility
        return "{$day}{$suffix} day of {$month} in the year {$year}";
    }

    /**
     * Get formal date with HTML formatting for Dompdf
     * e.g. "27<sup>th</sup> day of <em>March</em> in the year <strong>2026</strong>"
     */
    private function getFormalDateHtml($dateStr) {
        $ts = strtotime($dateStr);
        $day = date('j', $ts);
        $month = date('F', $ts);
        $year = date('Y', $ts);

        $suffix = 'th';
        if (!in_array(($day % 100), [11, 12, 13])) {
            switch ($day % 10) {
                case 1: $suffix = 'st'; break;
                case 2: $suffix = 'nd'; break;
                case 3: $suffix = 'rd'; break;
            }
        }

        return "<strong>{$day}<sup>{$suffix}</sup></strong> day of <em>{$month}</em> in the year <strong>{$year}</strong>";
    }

    /**
     * Generate a student number for the certificate
     */
    private function getStudentNumber() {
        $userId = $this->data['user_id'] ?? 0;
        $certNum = $this->getCertificateNumber();
        if ($userId) {
            return date('y') . 'Edu' . str_pad($userId, 6, '0', STR_PAD_LEFT);
        }
        return $certNum;
    }

    public function __get($key) {
        return $this->data[$key] ?? null;
    }
}