<?php
/**
 * Certificate PDF Generator
 * Generate TEVETA certificates using FPDF
 */

require_once __DIR__ . '/../../vendor/autoload.php'; // If using Composer for FPDF
// Or use: require_once __DIR__ . '/fpdf/fpdf.php';

class CertificateGenerator {
    
    private $pdf;
    private $certificate;
    private $outputPath;
    
    public function __construct($certificate) {
        $this->certificate = $certificate;
        $this->outputPath = STORAGE_PATH . '/certificates/';
        
        // Create directory if not exists
        if (!file_exists($this->outputPath)) {
            mkdir($this->outputPath, 0755, true);
        }
    }
    
    /**
     * Generate certificate PDF
     * 
     * @return string Path to generated PDF
     */
    public function generate() {
        // Initialize FPDF in landscape mode
        $this->pdf = new FPDF('L', 'mm', 'A4');
        $this->pdf->AddPage();
        
        // Add border
        $this->pdf->SetDrawColor(46, 112, 218); // Primary blue
        $this->pdf->SetLineWidth(2);
        $this->pdf->Rect(10, 10, 277, 190);
        
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Rect(12, 12, 273, 186);
        
        // Logo (if exists)
        $logoPath = PUBLIC_PATH . '/assets/images/logo.png';
        if (file_exists($logoPath)) {
            $this->pdf->Image($logoPath, 25, 20, 30);
        }
        
        // TEVETA Logo (if exists)
        $tevataLogoPath = PUBLIC_PATH . '/assets/images/teveta-logo.png';
        if (file_exists($tevataLogoPath)) {
            $this->pdf->Image($tevataLogoPath, 242, 20, 30);
        }
        
        // Institution Name
        $this->pdf->SetFont('Arial', 'B', 20);
        $this->pdf->SetTextColor(46, 112, 218);
        $this->pdf->SetY(25);
        $this->pdf->Cell(0, 10, TEVETA_NAME, 0, 1, 'C');
        
        // TEVETA Registration
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->Cell(0, 5, 'TEVETA Registered: ' . TEVETA_CODE, 0, 1, 'C');
        
        // Certificate Title
        $this->pdf->SetY(55);
        $this->pdf->SetFont('Arial', 'B', 32);
        $this->pdf->SetTextColor(246, 183, 69); // Gold color
        $this->pdf->Cell(0, 15, 'CERTIFICATE OF COMPLETION', 0, 1, 'C');
        
        // Decorative line
        $this->pdf->SetDrawColor(246, 183, 69);
        $this->pdf->SetLineWidth(1);
        $this->pdf->Line(80, 73, 217, 73);
        
        // This certifies that
        $this->pdf->SetY(80);
        $this->pdf->SetFont('Arial', 'I', 14);
        $this->pdf->SetTextColor(80, 80, 80);
        $this->pdf->Cell(0, 8, 'This is to certify that', 0, 1, 'C');
        
        // Student Name
        $this->pdf->SetY(92);
        $this->pdf->SetFont('Arial', 'B', 24);
        $this->pdf->SetTextColor(0, 0, 0);
        $studentName = $this->certificate['student_first_name'] . ' ' . $this->certificate['student_last_name'];
        $this->pdf->Cell(0, 12, strtoupper($studentName), 0, 1, 'C');
        
        // Underline
        $nameWidth = $this->pdf->GetStringWidth(strtoupper($studentName)) + 20;
        $startX = (297 - $nameWidth) / 2;
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line($startX, 105, $startX + $nameWidth, 105);
        
        // Has successfully completed
        $this->pdf->SetY(110);
        $this->pdf->SetFont('Arial', '', 12);
        $this->pdf->SetTextColor(80, 80, 80);
        $this->pdf->Cell(0, 7, 'has successfully completed the course', 0, 1, 'C');
        
        // Course Name
        $this->pdf->SetY(120);
        $this->pdf->SetFont('Arial', 'B', 18);
        $this->pdf->SetTextColor(46, 112, 218);
        $this->pdf->Cell(0, 10, $this->certificate['course_title'], 0, 1, 'C');
        
        // Course details
        $this->pdf->SetY(133);
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->SetTextColor(100, 100, 100);
        
        $completionDate = date('F j, Y', strtotime($this->certificate['completion_date']));
        $details = sprintf(
            'Completed on %s | Score: %d%% | Duration: %s',
            $completionDate,
            $this->certificate['final_score'],
            $this->certificate['course_duration']
        );
        $this->pdf->Cell(0, 6, $details, 0, 1, 'C');
        
        // Certificate Number
        $this->pdf->SetY(145);
        $this->pdf->SetFont('Arial', 'I', 9);
        $this->pdf->SetTextColor(120, 120, 120);
        $this->pdf->Cell(0, 5, 'Certificate No: ' . $this->certificate['certificate_number'], 0, 1, 'C');
        
        // Verification URL
        $verifyUrl = url('verify-certificate.php?code=' . $this->certificate['verification_code']);
        $this->pdf->SetFont('Arial', 'I', 8);
        $this->pdf->Cell(0, 5, 'Verify at: ' . $verifyUrl, 0, 1, 'C');
        
        // QR Code (if QR code library available)
        $this->addQRCode($verifyUrl);
        
        // Signatures
        $this->pdf->SetY(165);
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->SetTextColor(0, 0, 0);
        
        // Instructor signature
        $this->pdf->SetX(50);
        $this->pdf->Cell(80, 5, '', 'B', 0, 'C');
        
        // Admin signature
        $this->pdf->SetX(167);
        $this->pdf->Cell(80, 5, '', 'B', 1, 'C');
        
        // Signature labels
        $this->pdf->SetY(172);
        $this->pdf->SetFont('Arial', 'I', 9);
        $this->pdf->SetTextColor(100, 100, 100);
        
        $this->pdf->SetX(50);
        $this->pdf->Cell(80, 5, 'Course Instructor', 0, 0, 'C');
        
        $this->pdf->SetX(167);
        $this->pdf->Cell(80, 5, 'Principal', 0, 1, 'C');
        
        // Instructor and Principal names
        $this->pdf->SetY(177);
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->SetTextColor(0, 0, 0);
        
        $this->pdf->SetX(50);
        $this->pdf->Cell(80, 5, $this->certificate['instructor_name'], 0, 0, 'C');
        
        $this->pdf->SetX(167);
        $this->pdf->Cell(80, 5, APP_NAME, 0, 1, 'C');
        
        // Footer - Issue date
        $this->pdf->SetY(190);
        $this->pdf->SetFont('Arial', 'I', 8);
        $this->pdf->SetTextColor(150, 150, 150);
        $this->pdf->Cell(0, 5, 'Issued on ' . date('F j, Y'), 0, 0, 'C');
        
        // Save PDF
        $filename = 'certificate_' . $this->certificate['certificate_number'] . '.pdf';
        $filepath = $this->outputPath . $filename;
        
        $this->pdf->Output('F', $filepath);
        
        return $filepath;
    }
    
    /**
     * Add QR code to certificate
     * Requires PHP QR Code library
     * 
     * @param string $url URL to encode
     */
    private function addQRCode($url) {
        // Check if QR code library is available
        if (!class_exists('QRcode')) {
            return;
        }
        
        try {
            $qrPath = STORAGE_PATH . '/cache/qr_' . md5($url) . '.png';
            QRcode::png($url, $qrPath, 'L', 3, 2);
            
            if (file_exists($qrPath)) {
                $this->pdf->Image($qrPath, 260, 165, 25, 25);
                unlink($qrPath); // Clean up
            }
        } catch (Exception $e) {
            // Silently fail if QR code generation fails
        }
    }
    
    /**
     * Generate and download certificate
     */
    public function download() {
        $this->generate();

        $safeCertNum = preg_replace('/[^a-zA-Z0-9_\-]/', '', $this->certificate['certificate_number']);
        $filename = 'certificate_' . $safeCertNum . '.pdf';
        $filepath = $this->outputPath . $filename;

        if (file_exists($filepath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        }
    }
    
    /**
     * Generate and display certificate inline
     */
    public function display() {
        $this->generate();

        $safeCertNum = preg_replace('/[^a-zA-Z0-9_\-]/', '', $this->certificate['certificate_number']);
        $filename = 'certificate_' . $safeCertNum . '.pdf';
        $filepath = $this->outputPath . $filename;

        if (file_exists($filepath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"');
            readfile($filepath);
            exit;
        }
    }
}

/**
 * Alternative: HTML to PDF using mPDF or TCPDF
 * If you prefer HTML-based certificates
 */
class HTMLCertificateGenerator {
    
    private $certificate;
    
    public function __construct($certificate) {
        $this->certificate = $certificate;
    }
    
    public function generateHTML() {
        $studentName = htmlspecialchars(strtoupper($this->certificate['student_first_name'] . ' ' . $this->certificate['student_last_name']), ENT_QUOTES, 'UTF-8');
        $courseTitle = htmlspecialchars($this->certificate['course_title'], ENT_QUOTES, 'UTF-8');
        $completionDate = date('F j, Y', strtotime($this->certificate['completion_date']));
        $certificateNumber = htmlspecialchars($this->certificate['certificate_number'], ENT_QUOTES, 'UTF-8');
        $verifyUrl = url('verify-certificate.php?code=' . urlencode($this->certificate['verification_code']));
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { size: A4 landscape; margin: 0; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20mm; }
        .certificate { border: 5px double #2E70DA; padding: 30px; height: 100%; position: relative; }
        .header { text-align: center; color: #2E70DA; }
        .title { font-size: 48px; color: #F6B745; margin: 30px 0; text-align: center; }
        .student-name { font-size: 36px; font-weight: bold; text-align: center; margin: 20px 0; border-bottom: 2px solid #000; display: inline-block; padding: 0 40px; }
        .course-title { font-size: 24px; color: #2E70DA; text-align: center; margin: 20px 0; }
        .footer { position: absolute; bottom: 50px; width: calc(100% - 120px); }
        .signatures { display: flex; justify-content: space-around; margin-top: 50px; }
        .signature { text-align: center; }
        .signature-line { border-top: 2px solid #000; width: 200px; margin: 0 auto; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="header">
            <h1>{$GLOBALS['config']['teveta']['institution_name']}</h1>
            <p>TEVETA Registered: {$GLOBALS['config']['teveta']['institution_code']}</p>
        </div>
        
        <div class="title">CERTIFICATE OF COMPLETION</div>
        
        <p style="text-align: center; font-style: italic; color: #666;">This is to certify that</p>
        
        <div style="text-align: center;">
            <div class="student-name">{$studentName}</div>
        </div>
        
        <p style="text-align: center; margin: 30px 0;">has successfully completed the course</p>
        
        <div class="course-title">{$courseTitle}</div>
        
        <p style="text-align: center; color: #666; margin: 20px 0;">
            Completed on {$completionDate} | Score: {$this->certificate['final_score']}%
        </p>
        
        <div class="footer">
            <p style="text-align: center; font-size: 12px; color: #999;">
                Certificate No: {$certificateNumber}<br>
                Verify at: {$verifyUrl}
            </p>
            
            <div class="signatures">
                <div class="signature">
                    <div class="signature-line">{$this->certificate['instructor_name']}</div>
                    <p>Course Instructor</p>
                </div>
                <div class="signature">
                    <div class="signature-line">Principal</div>
                    <p>{$GLOBALS['config']['name']}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }
}