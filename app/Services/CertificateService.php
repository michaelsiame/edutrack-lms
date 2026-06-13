<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use TCPDF;

class CertificateService
{
    /**
     * Get the ordinal suffix for a day number.
     */
    protected function getDaySuffix(int $day): string
    {
        return match (true) {
            $day >= 11 && $day <= 13 => 'th',
            $day % 10 === 1 => 'st',
            $day % 10 === 2 => 'nd',
            $day % 10 === 3 => 'rd',
            default => 'th',
        };
    }

    /**
     * Generate a sequential certificate number like "ECTC26001"
     * (ECTC + two-digit year + zero-padded sequence). Must be called inside
     * a transaction (issueCertificate) so the MAX+1 sequence is race-safe.
     */
    public function generateCertificateNumber($user = null, ?int $courseId = null): string
    {
        $prefix = 'ECTC' . date('y');

        $last = Certificate::where('certificate_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING(certificate_number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)) as seq')
            ->value('seq');

        return $prefix . str_pad((string) (($last ?? 0) + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate student number like "26Edu249580"
     */
    protected function generateStudentNumber(Certificate $certificate): string
    {
        $yearSuffix = substr($certificate->graduation_ceremony_date?->format('Y') ?? date('Y'), -2);
        $userId = $certificate->user_id;

        if ($certificate->user && $certificate->user->national_id) {
            $numberPart = preg_replace('/[^0-9]/', '', $certificate->user->national_id);
            if (strlen($numberPart) > 6) $numberPart = substr($numberPart, -6);
        } else {
            $numberPart = str_pad((string) $userId, 6, '0', STR_PAD_LEFT);
        }

        return $yearSuffix . 'Edu' . $numberPart;
    }

    /**
     * Generate a verification code.
     */
    public function generateVerificationCode(): string
    {
        return Str::random(32);
    }

    /**
     * Issue a certificate for an enrollment.
     * Wrapped in a database transaction with row locking to prevent duplicates.
     */
    public function issueCertificate(Enrollment $enrollment): ?Certificate
    {
        return DB::transaction(function () use ($enrollment) {
            // Re-fetch with lock to prevent race conditions
            $lockedEnrollment = Enrollment::lockForUpdate()->find($enrollment->id);

            if (!$lockedEnrollment) {
                return null;
            }

            // Already issued or blocked
            if ($lockedEnrollment->certificate_issued) {
                return Certificate::where('enrollment_id', $lockedEnrollment->id)->first();
            }

            if ($lockedEnrollment->certificate_blocked) {
                return null;
            }

            // Recalculate final grade before issuing
            app(GradeAggregationService::class)->recalculateFinalGrade($lockedEnrollment);
            $lockedEnrollment->refresh();

            $certificate = Certificate::create([
                'user_id' => $lockedEnrollment->user_id,
                'course_id' => $lockedEnrollment->course_id,
                'enrollment_id' => $lockedEnrollment->id,
                'certificate_number' => $this->generateCertificateNumber($lockedEnrollment->user, $lockedEnrollment->course_id),
                'issued_date' => now(),
                'verification_code' => $this->generateVerificationCode(),
                'final_score' => $lockedEnrollment->final_grade ?? 0,
                'issued_at' => now(),
                'is_verified' => true,
            ]);

            $lockedEnrollment->update(['certificate_issued' => true]);

            return $certificate;
        });
    }

    /**
     * Send certificate notification email and in-app notification.
     */
    public function sendCertificateNotification(Certificate $certificate): void
    {
        try {
            $emailService = app(EmailQueueService::class);
            $enrollment = $certificate->enrollment;

            if (!$enrollment || !$enrollment->user) {
                return;
            }

            $emailService->sendTemplated($enrollment->user->email, 'certificate', [
                'name' => $enrollment->user->full_name,
                'course' => $enrollment->course->title,
                'certificate_number' => $certificate->certificate_number,
                'download_url' => route('certificates.download', $certificate),
            ]);

            $emailService->sendNotification(
                $enrollment->user_id,
                'Certificate Issued',
                "Your certificate for {$enrollment->course->title} is now available.",
                'certificate',
                route('certificates.download', $certificate)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send certificate notification: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF for a certificate.
     */
    public function generatePdf(Certificate $certificate): string
    {
        return $this->renderPdf($this->getCertificateData($certificate));
    }

    /** Certificate colors (RGB). */
    private const ORANGE = [237, 119, 47];
    private const BLUE_BORDER = [21, 56, 145];
    private const NAVY_TITLE = [15, 43, 112];
    private const BLACK = [0, 0, 0];
    private const WM_BLUE = [193, 214, 240];
    private const WM_ORANGE = [248, 220, 198];

    /** Page geometry (mm). */
    private const PAGE_W = 210;
    private const PAGE_H = 297;
    private const CENTER_X = 105;

    /**
     * Render the certificate PDF natively with TCPDF (no writeHTML) so the
     * watermark, custom fonts and decorative frame survive on shared hosting.
     */
    public function renderPdf(array $data): string
    {
        $template = resource_path('certificates/edutrack-certificate-template.pdf');

        if (is_file($template)) {
            return $this->renderFromTemplate($template, $data);
        }

        $pdf = new \App\Pdf\EdutrackPdf('P', 'mm', 'A4');
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack Computer Training College');
        $pdf->SetTitle('Certificate - ' . ($data['certificate_number'] ?? ''));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setFooterMargin(0);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetCellPadding(0);
        $pdf->SetCellMargins(0);
        $pdf->AddPage();

        $this->drawFrame($pdf);
        $this->drawWatermark($pdf);
        $this->drawCornerTriangles($pdf);
        $this->drawContent($pdf, $data);

        return $pdf->Output('', 'S');
    }

    /**
     * Render onto the official certificate artwork: the template PDF is the
     * original design with only the variable text removed, so every static
     * element (frame, watermark, logos, fonts, ornaments) is authentic.
     */
    protected function renderFromTemplate(string $template, array $data): string
    {
        $pdf = new \App\Pdf\EdutrackFpdi('P', 'mm', 'A4');
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack Computer Training College');
        $pdf->SetTitle('Certificate - ' . ($data['certificate_number'] ?? ''));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setSourceFile($template);
        $page = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($page, 0, 0, self::PAGE_W, self::PAGE_H);

        $this->drawVariableFields($pdf, $data);

        return $pdf->Output('', 'S');
    }

    /**
     * The dynamic fields stamped onto the template, at the baselines measured
     * from the original certificate. The template keeps its own underlines.
     */
    protected function drawVariableFields(TCPDF $pdf, array $data): void
    {
        $pdf->SetTextColor(...self::BLACK);

        // Student name
        $name = $data['student_name'] ?? '';
        $this->certFont($pdf, 'script', 34);
        $this->fitFont($pdf, 'script', 34, $name, 134);
        $this->centeredAtBaseline($pdf, 102.2, $name);

        // Course title
        $pdf->SetTextColor(...self::NAVY_TITLE);
        $this->drawCourseTitle($pdf, mb_strtoupper($data['course_title'] ?? ''));
        $pdf->SetTextColor(...self::BLACK);

        // "With {classification}"
        $classification = $data['classification'] ?? null;
        if ($classification && trim($classification) !== '' && strcasecmp($classification, 'Pass') !== 0) {
            $this->certFont($pdf, 'script', 28.5);
            $this->centeredAtBaseline($pdf, 169.7, 'With ' . $classification);
        }

        // Graduation date lines (the template's static text was removed with
        // the fills because the day/month/year sit inline)
        $this->centeredSegments($pdf, 195.3, [
            ['Ceremony held on the ', 'serif', 16.6, 0],
            [($data['graduation_day'] ?? ''), 'script', 19.8, 0],
            [($data['graduation_suffix'] ?? ''), 'script', 9.9, 3.5],
            [' day of ', 'serif', 16.6, 0],
            [($data['graduation_month'] ?? ''), 'script', 19.8, 0],
        ]);

        $this->centeredSegments($pdf, 204.6, [
            ['in the year ', 'serif', 16.6, 0],
            [($data['graduation_year'] ?? ''), 'script', 19.8, 0],
        ]);

        // Graduate's ID No. — filled with the student number, like the old
        // template did (the signature line stays blank for handwriting)
        if (!empty($data['student_number'])) {
            $this->certFont($pdf, 'script', 14);
            $this->centeredLabel($pdf, 135, 184, 247.3, $data['student_number']);
        }

        // Official signatures above the Principal / Director rules.
        $this->placeSignature($pdf, 'principal', 50.5, 228.5);
        $this->placeSignature($pdf, 'director', 159.5, 228.5);

        // Bottom row
        $this->certFont($pdf, 'serif', 14.8);
        $this->leftAtBaseline($pdf, 29, 270.2, $data['national_id'] ?? '');
        $this->rightAtBaseline($pdf, 184, 270.2, $data['certificate_number'] ?? '');
    }

    /**
     * Border frame: 0.4mm NAVY rule inset 4mm, then solid NAVY band 6.5-9.3mm.
     */
    protected function drawFrame(TCPDF $pdf): void
    {
        // 0.4mm NAVY rule inset 4mm from page edge
        $pdf->SetLineWidth(0.4);
        $pdf->SetDrawColor(...self::BLUE_BORDER);
        $pdf->Rect(4, 4, 202, 289, 'D');

        // Solid NAVY band from 6.5mm to 9.3mm inset
        $pdf->Rect(6.5, 6.5, 197, 284, 'F', [], self::BLUE_BORDER);
        $pdf->Rect(9.3, 9.3, 191.4, 278.4, 'F', [], [255, 255, 255]);
    }

    /**
     * Horizontal rows of repeating microtext inside the white area only.
     * Rows alternate between WM_ORANGE and WM_BLUE.
     */
    protected function drawWatermark(TCPDF $pdf): void
    {
        $phrase = 'Edutrack Computer Training College ';
        $pdf->StartTransform();
        // Clip to inner white area (~10mm inset)
        $pdf->Rect(10, 10, 190, 277, 'CNZ');

        $pdf->SetFont('helvetica', '', 5.5);

        $phraseWidth = $pdf->GetStringWidth($phrase);
        $rowPitch = 2.4;
        $startY = 10;
        $endY = self::PAGE_H - 10;

        $row = 0;
        for ($y = $startY; $y < $endY; $y += $rowPitch) {
            $pdf->SetTextColor(...($row % 2 === 0 ? self::WM_ORANGE : self::WM_BLUE));
            $offsetX = ($row % 2) * ($phraseWidth / 2);
            $x = 10 + $offsetX;
            while ($x < self::PAGE_W - 10) {
                $pdf->Text($x, $y, $phrase);
                $x += $phraseWidth;
            }
            $row++;
        }

        $pdf->StopTransform();
    }

    /**
     * Layered corner triangles at all 4 corners.
     * Orange (30mm), White (26.5mm), Navy (23mm).
     */
    protected function drawCornerTriangles(TCPDF $pdf): void
    {
        // Top-left
        $pdf->Polygon([0, 0, 30, 0, 0, 30], 'F', [], self::ORANGE);
        $pdf->Polygon([0, 0, 26.5, 0, 0, 26.5], 'F', [], [255, 255, 255]);
        $pdf->Polygon([0, 0, 23, 0, 0, 23], 'F', [], self::BLUE_BORDER);

        // Top-right
        $pdf->Polygon([210, 0, 180, 0, 210, 30], 'F', [], self::ORANGE);
        $pdf->Polygon([210, 0, 183.5, 0, 210, 26.5], 'F', [], [255, 255, 255]);
        $pdf->Polygon([210, 0, 187, 0, 210, 23], 'F', [], self::BLUE_BORDER);

        // Bottom-left
        $pdf->Polygon([0, 297, 30, 297, 0, 267], 'F', [], self::ORANGE);
        $pdf->Polygon([0, 297, 26.5, 297, 0, 270.5], 'F', [], [255, 255, 255]);
        $pdf->Polygon([0, 297, 23, 297, 0, 274], 'F', [], self::BLUE_BORDER);

        // Bottom-right
        $pdf->Polygon([210, 297, 180, 297, 210, 267], 'F', [], self::ORANGE);
        $pdf->Polygon([210, 297, 183.5, 297, 210, 270.5], 'F', [], [255, 255, 255]);
        $pdf->Polygon([210, 297, 187, 297, 210, 274], 'F', [], self::BLUE_BORDER);
    }

    /**
     * Small orange diamond with flanking dashes between the college name and subtitle.
     */
    protected function drawRibbon(TCPDF $pdf): void
    {
        // Small ORANGE diamond centered at (105, 49.5), ~2.2mm diagonal
        $pdf->SetFillColor(...self::ORANGE);
        $pdf->Polygon([105, 48.4, 106.1, 49.5, 105, 50.6, 103.9, 49.5], 'F', [], self::ORANGE);

        // Horizontal ORANGE dashes flanking the diamond
        $pdf->SetLineWidth(0.5);
        $pdf->SetDrawColor(...self::ORANGE);
        // Left dash: ~8mm long, 3mm gap from diamond
        $pdf->Line(92.9, 49.5, 100.9, 49.5);
        // Right dash: ~8mm long, 3mm gap from diamond
        $pdf->Line(109.1, 49.5, 117.1, 49.5);

        // Reset draw state
        $pdf->SetDrawColor(...self::BLACK);
        $pdf->SetLineWidth(0.3);
    }

    /**
     * All certificate text, logos, signatures and decorations.
     */
    protected function drawContent(TCPDF $pdf, array $data): void
    {
        $cx = self::CENTER_X;

        // --- Logos row ---
        $logo = public_path('assets/images/certificate/edutrack-logo.png');
        if (is_file($logo)) {
            $pdf->Image($logo, 19, 16, 21, 0);
        }
        $teveta = public_path('assets/images/certificate/teveta-logo.png');
        if (is_file($teveta)) {
            $pdf->Image($teveta, 163, 19, 29, 0);
        }

        // Element 1: "EDUTRACK COMPUTER"
        $pdf->SetTextColor(...self::BLUE_BORDER);
        $this->certFont($pdf, 'caps', 23.3);
        $this->centeredAtBaseline($pdf, 32.7, 'EDUTRACK COMPUTER');

        // Element 2: "TRAINING COLLEGE"
        $this->centeredAtBaseline($pdf, 42.8, 'TRAINING COLLEGE');

        $this->drawRibbon($pdf);

        // Element 3: "A skill training college"
        $pdf->SetTextColor(...self::BLACK);
        $this->certFont($pdf, 'serif', 14.6);
        $this->centeredAtBaseline($pdf, 58.6, 'A skill training college');

        // Element 4: "THIS IS TO CERTIFY THAT" with flanking ornaments
        $this->certFont($pdf, 'serif-bold', 21.3);
        $certifyText = 'THIS IS TO CERTIFY THAT';
        $certifyWidth = $pdf->GetStringWidth($certifyText);
        $ornamentWidth = 7 + 1.6 + 3; // 11.6
        $ruleGap = 4;
        $totalWidth = $ornamentWidth + $ruleGap + $certifyWidth + $ruleGap + $ornamentWidth;
        $startX = $cx - $totalWidth / 2;

        $pdf->SetLineWidth(0.5);
        $pdf->SetDrawColor(...self::ORANGE);
        $pdf->SetFillColor(...self::ORANGE);

        // Left ornament: dash (7mm) + diamond (1.6mm) + dash (3mm)
        $leftOrnamentX = $startX;
        $pdf->Line($leftOrnamentX, 83.8, $leftOrnamentX + 7, 83.8);
        $pdf->Polygon([
            $leftOrnamentX + 7.8, 83.8 - 0.8,
            $leftOrnamentX + 7.8 + 0.8, 83.8,
            $leftOrnamentX + 7.8, 83.8 + 0.8,
            $leftOrnamentX + 7.8 - 0.8, 83.8,
        ], 'F', [], self::ORANGE);
        $pdf->Line($leftOrnamentX + 8.6, 83.8, $leftOrnamentX + 11.6, 83.8);

        // Right ornament: dash (7mm) + diamond (1.6mm) + dash (3mm)
        $rightOrnamentX = $startX + $totalWidth - $ornamentWidth;
        $pdf->Line($rightOrnamentX, 83.8, $rightOrnamentX + 7, 83.8);
        $pdf->Polygon([
            $rightOrnamentX + 7.8, 83.8 - 0.8,
            $rightOrnamentX + 7.8 + 0.8, 83.8,
            $rightOrnamentX + 7.8, 83.8 + 0.8,
            $rightOrnamentX + 7.8 - 0.8, 83.8,
        ], 'F', [], self::ORANGE);
        $pdf->Line($rightOrnamentX + 8.6, 83.8, $rightOrnamentX + 11.6, 83.8);

        $pdf->SetTextColor(...self::BLUE_BORDER);
        $this->centeredAtBaseline($pdf, 83.8, $certifyText);
        $pdf->SetTextColor(...self::BLACK);

        // Reset line state
        $pdf->SetLineWidth(0.3);
        $pdf->SetDrawColor(...self::BLACK);

        // Element 5: student_name
        $this->certFont($pdf, 'script', 34);
        $name = $data['student_name'] ?? '';
        $this->fitFont($pdf, 'script', 34, $name, 134);
        $this->centeredAtBaseline($pdf, 102.2, $name);

        // Underline for name
        $pdf->SetLineStyle(['width' => 0.5, 'color' => self::ORANGE]);
        $pdf->Line(38, 106.5, 172, 106.5);

        // Element 6: "having satisfied the requirements for the"
        $this->certFont($pdf, 'serif', 15);
        $this->centeredAtBaseline($pdf, 120.8, 'having satisfied the requirements for the');

        // Element 7: "award of the certificate of"
        $this->centeredAtBaseline($pdf, 127.3, 'award of the certificate of');

        // Element 8: course_title (UPPERCASE)
        $pdf->SetTextColor(...self::NAVY_TITLE);
        $this->drawCourseTitle($pdf, mb_strtoupper($data['course_title'] ?? ''));
        $pdf->SetTextColor(...self::BLACK);

        // Element 9: "With {classification}"
        $classification = $data['classification'] ?? null;
        if ($classification && trim($classification) !== '' && strcasecmp($classification, 'Pass') !== 0) {
            $this->certFont($pdf, 'script', 28.5);
            $this->centeredAtBaseline($pdf, 169.7, 'With ' . $classification);

            // Small ORANGE diamond centered at (105, 175.5)
            $pdf->SetFillColor(...self::ORANGE);
            $pdf->Polygon([105, 174.5, 106, 175.5, 105, 176.5, 104, 175.5], 'F', [], self::ORANGE);
        }

        // Element 10: "Was admitted to the certificate at a Graduation"
        $this->certFont($pdf, 'serif', 16.6);
        $this->centeredAtBaseline($pdf, 187.0, 'Was admitted to the certificate at a Graduation');

        // Element 11: mixed line
        $this->centeredSegments($pdf, 195.3, [
            ['Ceremony held on the ', 'serif', 16.6, 0],
            [($data['graduation_day'] ?? ''), 'script', 19.8, 0],
            [($data['graduation_suffix'] ?? ''), 'script', 9.9, 3.5],
            [' day of ', 'serif', 16.6, 0],
            [($data['graduation_month'] ?? ''), 'script', 19.8, 0],
        ]);

        // Element 12: mixed line
        $this->centeredSegments($pdf, 204.6, [
            ['in the year ', 'serif', 16.6, 0],
            [($data['graduation_year'] ?? ''), 'script', 19.8, 0],
        ]);

        // --- Signature block ---
        // Official signature images sit just above each rule, if present.
        $this->placeSignature($pdf, 'principal', 50.5, 228.5);
        $this->placeSignature($pdf, 'director', 159.5, 228.5);

        $pdf->SetLineStyle(['width' => 0.4, 'color' => self::ORANGE]);

        // Top row rules
        $pdf->Line(26, 230.5, 75, 230.5);
        $pdf->Line(135, 230.5, 184, 230.5);

        $this->certFont($pdf, 'serif', 11);
        $this->centeredLabel($pdf, 26, 75, 234.4, 'Principal');
        $this->centeredLabel($pdf, 135, 184, 234.4, 'Director');

        // Bottom row rules
        $pdf->Line(26, 248.8, 75, 248.8);
        $pdf->Line(135, 248.8, 184, 248.8);

        $this->centeredLabel($pdf, 26, 75, 252.6, "Graduate's Signature");
        $this->centeredLabel($pdf, 135, 184, 252.6, "Graduate's ID No.");

        // Graduate's ID No. — filled with the student number, like the old
        // template did (the signature line stays blank for handwriting)
        if (!empty($data['student_number'])) {
            $this->certFont($pdf, 'script', 14);
            $this->centeredLabel($pdf, 135, 184, 247.3, $data['student_number']);
        }

        // --- Bottom row ---
        $this->certFont($pdf, 'serif', 14.8);
        $this->leftAtBaseline($pdf, 29, 270.2, $data['national_id'] ?? '');
        $this->rightAtBaseline($pdf, 184, 270.2, $data['certificate_number'] ?? '');

        // Bottom underlines
        $pdf->SetLineStyle(['width' => 0.3, 'color' => self::ORANGE]);
        $pdf->Line(26, 271.8, 78, 271.8);
        $pdf->Line(132, 271.8, 184, 271.8);
    }

    /**
     * Load a certificate font (with built-in fallback) and set it.
     */
    protected function certFont(TCPDF $pdf, string $key, float $size): void
    {
        $map = [
            'script' => 'mistral',
            'serif' => 'centuryschoolbook',
            'serif-bold' => 'centuryschoolbookb',
            'condensed' => 'impact',
            'caps' => 'bodoni72smallcapsbook',
        ];
        $name = $map[$key] ?? $key;
        $file = resource_path('fonts/tcpdf/' . $name . '.php');

        if (is_file($file)) {
            $pdf->AddFont($name, '', $file);
            $pdf->SetFont($name, '', $size);
            return;
        }

        // Fallback to TCPDF built-ins if the font files are missing
        $fallback = [
            'script' => ['times', 'I'],
            'serif' => ['times', ''],
            'serif-bold' => ['times', 'B'],
            'condensed' => ['helvetica', 'B'],
            'caps' => ['times', ''],
        ];
        [$family, $style] = $fallback[$key] ?? ['times', ''];
        $pdf->SetFont($family, $style, $size);
    }

    /**
     * Draw the course title in impact, shrinking to fit 170mm width.
     */
    protected function drawCourseTitle(TCPDF $pdf, string $course): void
    {
        $size = 36.5;
        $maxWidth = 170;
        $this->certFont($pdf, 'condensed', $size);
        $pdf->setFontStretching(88);
        while ($size > 20 && $pdf->GetStringWidth($course) > $maxWidth) {
            $size -= 1;
            $this->certFont($pdf, 'condensed', $size);
        }
        $this->centeredAtBaseline($pdf, 151.7, $course);
        $pdf->setFontStretching(100);
    }

    /**
     * Shrink the font size until the text fits the given width.
     */
    protected function fitFont(TCPDF $pdf, string $key, float $size, string $text, float $maxWidth): void
    {
        $this->certFont($pdf, $key, $size);
        while ($size > 14 && $pdf->GetStringWidth($text) > $maxWidth) {
            $size -= 1;
            $this->certFont($pdf, $key, $size);
        }
    }

    /**
     * Draw a horizontally centered line of text at the given baseline y.
     */
    protected function centeredAtBaseline(TCPDF $pdf, float $baselineY, string $text): void
    {
        $w = $pdf->GetStringWidth($text);
        $this->textAtBaseline($pdf, self::CENTER_X - $w / 2, $baselineY, $text);
    }

    /**
     * Draw text left-aligned at the given baseline y.
     */
    protected function leftAtBaseline(TCPDF $pdf, float $x, float $baselineY, string $text): void
    {
        $this->textAtBaseline($pdf, $x, $baselineY, $text);
    }

    /**
     * Draw text right-aligned at the given baseline y.
     */
    protected function rightAtBaseline(TCPDF $pdf, float $x, float $baselineY, string $text): void
    {
        $w = $pdf->GetStringWidth($text);
        $this->textAtBaseline($pdf, $x - $w, $baselineY, $text);
    }

    /**
     * Draw text with its baseline at the specified y.
     */
    protected function textAtBaseline(TCPDF $pdf, float $x, float $baselineY, string $text): void
    {
        $pdf->Text($x, $baselineY - $this->currentAscent($pdf), $text);
    }

    /**
     * Ascent of the current font in mm. Mistral's embedded ascent metric
     * under-reports the visual baseline by ~12.5% of the point size
     * (measured against the reference certificate), so correct for it.
     */
    protected function currentAscent(TCPDF $pdf): float
    {
        $ascent = $pdf->getFontAscent($pdf->getFontFamily(), $pdf->getFontStyle(), $pdf->getFontSizePt());

        if ($pdf->getFontFamily() === 'mistral') {
            $ascent += $pdf->getFontSizePt() * 0.125 * 0.3528;
        }

        return $ascent;
    }

    /**
     * Draw a centered label between two x coordinates at the given baseline.
     */
    protected function centeredLabel(TCPDF $pdf, float $x1, float $x2, float $baselineY, string $text): void
    {
        $center = ($x1 + $x2) / 2;
        $w = $pdf->GetStringWidth($text);
        $this->textAtBaseline($pdf, $center - $w / 2, $baselineY, $text);
    }

    /**
     * Draw a centered line built from segments with mixed fonts/sizes.
     * Each segment: [text, fontKey, size, verticalRise].
     */
    protected function centeredSegments(TCPDF $pdf, float $baselineY, array $segments): void
    {
        $widths = [];
        $total = 0;
        foreach ($segments as $i => $s) {
            $this->certFont($pdf, $s[1], $s[2]);
            $widths[$i] = $pdf->GetStringWidth($s[0]);
            $total += $widths[$i];
        }

        $x = self::CENTER_X - $total / 2;
        foreach ($segments as $i => $s) {
            $this->certFont($pdf, $s[1], $s[2]);
            $rise = $s[3] ?? 0;
            $pdf->Text($x, $baselineY - $this->currentAscent($pdf) - $rise, $s[0]);
            $x += $widths[$i];
        }
    }

    /**
     * Draw an official signature image centred on $centerX with its baseline
     * resting just above the signature rule. Silently skips if the file is
     * absent, so a missing signature simply leaves the rule blank.
     */
    protected function placeSignature(TCPDF $pdf, string $role, float $centerX, float $bottomY): void
    {
        $path = public_path("assets/images/cert-signatures/{$role}-signature.png");
        if (!is_file($path)) {
            return;
        }

        // Fixed width, auto height (h=0) preserves the signature's aspect ratio.
        $w = 36; // mm
        $pdf->Image($path, $centerX - $w / 2, $bottomY - 13, $w, 0, 'PNG', '', '', true, 300);
    }

    /**
     * Get certificate data for PDF generation.
     */
    public function getCertificateData(Certificate $certificate): array
    {
        $certificate->load(['user.student', 'course', 'enrollment']);

        $issuedDate = $certificate->issued_date ?? now();
        $graduationDate = $certificate->graduation_ceremony_date ?? $issuedDate;

        $nationalId = $certificate->user?->national_id;
        // The student number is the student's permanent ID, minted at first
        // enrolment. Fall back to lazy generation only for legacy rows.
        $studentNumber = $certificate->user?->student?->student_number
            ?: $this->generateStudentNumber($certificate);

        return [
            'certificate' => $certificate,
            'student_name' => $certificate->user?->full_name ?? 'Unknown',
            'course_title' => $certificate->course?->title ?? 'Unknown Course',
            'certificate_number' => $certificate->certificate_number,
            'issued_date' => $issuedDate->format('F d, Y'),
            'verification_code' => $certificate->verification_code,
            'verify_url' => route('certificates.verify', $certificate->verification_code),
            'final_score' => $certificate->final_score,
            'classification' => $certificate->classification ?? 'Pass',
            'graduation_day' => $graduationDate->format('j'),
            'graduation_suffix' => $this->getDaySuffix((int) $graduationDate->format('j')),
            'graduation_month' => $graduationDate->format('F'),
            'graduation_year' => $graduationDate->format('Y'),
            'student_number' => $studentNumber,
            'national_id' => $nationalId ? 'NRC ' . ltrim(preg_replace('/^\s*NRC\s*/i', '', $nationalId)) : '',
        ];
    }
}
