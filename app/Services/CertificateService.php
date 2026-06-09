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
     * Generate a unique certificate number in the format: NRC-{userId}-{random}
     */
    public function generateCertificateNumber($user = null, ?int $courseId = null): string
    {
        $nrcSuffix = '2495807';

        if ($user && $user->national_id) {
            $nrcSuffix = preg_replace('/[^0-9\/]/', '', $user->national_id);
            if (empty($nrcSuffix)) $nrcSuffix = '2495807/1/1';
        } elseif ($user && $user->id) {
            $nrcSuffix = $user->id . '/1/1';
        }

        // Append course ID and random suffix to guarantee uniqueness per certificate
        $uniqueSuffix = ($courseId ?? '') . '-' . Str::random(6);

        return 'NRC ' . $nrcSuffix . '/' . $uniqueSuffix;
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
        $this->ensureCustomFontsAvailable();

        $pdf = new TCPDF('P', 'mm', 'A4');
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack Computer Training College');
        $pdf->SetTitle('Certificate - ' . $certificate->certificate_number);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetCellPadding(0);
        $pdf->SetCellMargins(0);
        $pdf->setImageScale(1);
        $pdf->SetFont('dejavuserif', '', 10);

        // Register the cursive font shipped in public/assets/fonts/tcpdf
        try {
            $pdf->AddFont('greatvibes', '', 'greatvibes.php');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Great Vibes font missing, falling back: ' . $e->getMessage());
        }

        $pdf->AddPage();

        // Layer 1: tiled "Edutrack Computer Training College" watermark
        $this->drawWatermark($pdf);

        // Layer 2: outer/inner frames + decorative orange corner triangles
        $this->drawFrames($pdf);

        // Layer 3: logos (EduTrack shield top-left, TEVETA top-right) and seal
        $logoPath = public_path('assets/images/logo-pdf.png'); // transparent variant
        if (!file_exists($logoPath)) {
            $logoPath = public_path('assets/images/logo.png');
        }
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 16, 16, 32, 32, '', '', '', true, 300, '', false, false, 0);
        }

        $tevetaPath = public_path('assets/images/teveta-logo.png');
        if (!file_exists($tevetaPath)) {
            $tevetaPath = public_path('assets/images/teveta-logo.jpg');
        }
        if (file_exists($tevetaPath)) {
            $pdf->Image($tevetaPath, 162, 20, 38, 0, '', '', '', true, 300, '', false, false, 0);
        }

        // Layer 4: the main HTML content, rendered as fragments positioned by Y
        $data = $this->getCertificateData($certificate);
        $html = view('certificates.pdf', $data)->render();
        $sections = $this->splitSections($html);

        $hasMerit = ($data['classification'] ?? 'Pass') !== 'Pass';

        // (yPos, height) layout — tuned to match the reference PDF's compact
        // vertical rhythm. The bottom block (signatures + graduate + IDs)
        // stacks right after the date paragraph.
        $layout = [
            ['header',         16,  16],
            ['tagline',        38,  10],
            ['certify',        54,  12],
            ['name',           70,  22],
            ['requirement',    98,  12],
            ['course',        114,  16],
        ];
        if ($hasMerit) {
            $layout[] = ['classification', 136, 18];
            $dateY = 158;
        } else {
            $dateY = 136;
        }
        // Date paragraph (3 lines), then a gap for the seal, then the
        // signature block stacked compactly.
        $sealY        = $dateY + 30;
        $sealHeight   = 32;
        $signaturesY  = $sealY + $sealHeight + 4;

        $layout[] = ['date',        $dateY,           28];
        $layout[] = ['signatures',  $signaturesY,     14];
        $layout[] = ['graduate',    $signaturesY + 18, 10];
        $layout[] = ['ids',         $signaturesY + 32, 10];

        foreach ($layout as [$section, $y, $h]) {
            if (!isset($sections[$section])) {
                continue;
            }
            $pdf->writeHTMLCell(190, $h, 10, $y, $sections[$section], 0, 1, false, true, '', true);
        }

        // Orange underline beneath the student name. Drawn natively so the
        // line sits below the cursive descenders instead of cutting through them.
        $nameRow = $layout[3] ?? null; // ['name', y, h]
        if ($nameRow) {
            $nameUnderlineY = $nameRow[1] + $nameRow[2] - 1;
            $pdf->SetLineWidth(0.4);
            $pdf->SetDrawColor(242, 101, 34);
            $pdf->Line(35, $nameUnderlineY, 175, $nameUnderlineY);
        }

        // Seal image — centred between the date paragraph and the signature
        // block, so it sits in the space the reference layout leaves blank.
        $sealPath = public_path('assets/images/certificate-seal.png');
        if (file_exists($sealPath)) {
            $sealWidth = 22;
            $pdf->Image($sealPath, 105 - $sealWidth / 2, $sealY, $sealWidth, $sealHeight, '', '', '', true, 300, '', false, false, 0);
        }

        return $pdf->Output('', 'S');
    }

    /**
     * Split the rendered blade into named sections using {{-- @section:NAME --}} markers.
     */
    protected function splitSections(string $html): array
    {
        $sections = [];
        if (preg_match_all('/##(\w+)##\s*-->(.*?)<!--\s*##end##/s', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $sections[$m[1]] = trim($m[2]);
            }
        }
        return $sections;
    }

    /**
     * Copy the bundled Great Vibes font files into TCPDF's font directory if they
     * aren't already there. Lets shared-hosting deploys keep working after a
     * fresh `composer install` wipes vendor/.
     */
    protected function ensureCustomFontsAvailable(): void
    {
        $sourceDir = public_path('assets/fonts/tcpdf/');
        $targetDir = base_path('vendor/tecnickcom/tcpdf/fonts/');

        if (!is_dir($targetDir) || !is_dir($sourceDir)) {
            return;
        }

        foreach (['greatvibes.php', 'greatvibes.z', 'greatvibes.ctg.z'] as $file) {
            $target = $targetDir . $file;
            $source = $sourceDir . $file;
            if (!file_exists($target) && file_exists($source)) {
                @copy($source, $target);
            }
        }
    }

    /**
     * Draw the orange + blue page frames and the four orange corner triangles.
     * Layout matches the reference: thin orange outer ring, a small gap, then
     * a double-line blue frame, then orange corner triangles with blue
     * diagonal trim lines on the inside of each triangle.
     */
    protected function drawFrames(TCPDF $pdf): void
    {
        $orange = [242, 101, 34];
        $blue   = [30, 58, 138];

        // Outer orange ring (thin)
        $pdf->SetLineWidth(0.5);
        $pdf->SetDrawColor(...$orange);
        $pdf->Rect(6, 6, 198, 285);

        // Blue double-line frame (two lines with a thin white channel between)
        $pdf->SetDrawColor(...$blue);
        $pdf->SetLineWidth(0.6);
        $pdf->Rect(9, 9, 192, 279);
        $pdf->SetLineWidth(0.6);
        $pdf->Rect(12, 12, 186, 273);

        // Orange corner triangles
        $pdf->SetFillColor(...$orange);
        $pdf->SetDrawColor(...$orange);
        $size = 22;

        $pdf->Polygon([6, 6, 6 + $size, 6, 6, 6 + $size], 'F');
        $pdf->Polygon([204 - $size, 6, 204, 6, 204, 6 + $size], 'F');
        $pdf->Polygon([6, 291 - $size, 6, 291, 6 + $size, 291], 'F');
        $pdf->Polygon([204, 291 - $size, 204, 291, 204 - $size, 291], 'F');

        // Blue diagonal stripes inside each triangle, parallel to the
        // hypotenuse — the layered "trim" detail visible in the reference.
        $pdf->SetDrawColor(...$blue);
        $pdf->SetLineWidth(0.7);
        foreach ([4, 7] as $inset) {
            // top-left: hypotenuse (6+size,6) -> (6,6+size); inset toward (6,6)
            $pdf->Line(6 + $size - $inset, 6,            6,            6 + $size - $inset);
            // top-right: hypotenuse (204-size,6) -> (204,6+size); inset toward (204,6)
            $pdf->Line(204 - $size + $inset, 6,           204,          6 + $size - $inset);
            // bottom-left: hypotenuse (6,291-size) -> (6+size,291); inset toward (6,291)
            $pdf->Line(6,            291 - $size + $inset, 6 + $size - $inset, 291);
            // bottom-right: hypotenuse (204-size,291) -> (204,291-size); inset toward (204,291)
            $pdf->Line(204 - $size + $inset, 291,          204,          291 - $size + $inset);
        }
    }

    /**
     * Render the tiled "Edutrack Computer Training College" watermark
     * across the whole page at low opacity.
     */
    protected function drawWatermark(TCPDF $pdf): void
    {
        $pdf->SetAlpha(0.07);
        $pdf->SetFont('helvetica', '', 4);
        $pdf->SetTextColor(30, 58, 138);

        $unit = 'Edutrack Computer Training College  ';
        $row  = str_repeat($unit, 16);

        for ($y = 14; $y < 286; $y += 2.8) {
            $pdf->SetXY(8, $y);
            $pdf->Cell(196, 2.4, $row, 0, 0, 'L');
        }

        $pdf->SetAlpha(1);
        $pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Get certificate data for PDF generation.
     */
    public function getCertificateData(Certificate $certificate): array
    {
        $certificate->load(['user', 'course', 'enrollment']);

        $issuedDate = $certificate->issued_date ?? now();
        $graduationDate = $certificate->graduation_ceremony_date ?? $issuedDate;

        return [
            'certificate' => $certificate,
            'student_name' => $certificate->user?->full_name ?? 'Unknown',
            'course_title' => $certificate->course?->title ?? 'Unknown Course',
            'certificate_number' => $certificate->certificate_number,
            'issued_date' => $issuedDate->format('F d, Y'),
            'verification_code' => $certificate->verification_code,
            'final_score' => $certificate->final_score,
            'classification' => $certificate->classification ?? 'Pass',
            'graduation_day' => $graduationDate->format('j'),
            'graduation_suffix' => $this->getDaySuffix((int) $graduationDate->format('j')),
            'graduation_month' => $graduationDate->format('F'),
            'graduation_year' => $graduationDate->format('Y'),
            'student_number' => $this->generateStudentNumber($certificate),
        ];
    }
}
