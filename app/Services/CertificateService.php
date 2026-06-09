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
     *
     * The fixed certificate design (borders, watermark, logos, headings,
     * static text, signature labels) lives in a pre-rendered PNG at
     * public/assets/images/certificate-template.png — generated from
     * resources/views/certificates/template.blade.php via wkhtmltopdf
     * (see scripts/generate-certificate-template.php). Here we only overlay
     * the dynamic per-student text on top of that background.
     *
     * Y positions below are calibrated empirically against the rendered
     * template — see the comment block above each overlay for the
     * corresponding template anchor.
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

        try {
            $pdf->AddFont('greatvibes', '', 'greatvibes.php');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Great Vibes font missing, falling back: ' . $e->getMessage());
        }

        $pdf->AddPage();

        // Full-page template background — contains the fixed design.
        $template = public_path('assets/images/certificate-template.png');
        if (file_exists($template)) {
            $pdf->Image($template, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
        } else {
            // Fallback: draw the frames + watermark natively if the template
            // image is missing (e.g., template not yet generated on this host).
            $this->drawWatermark($pdf);
            $this->drawFrames($pdf);
        }

        $data = $this->getCertificateData($certificate);
        $hasMerit = ($data['classification'] ?? 'Pass') !== 'Pass';
        $orange = [242, 101, 34];

        // 1. Student name — sits just above the orange underline at y≈100mm.
        $pdf->writeHTMLCell(190, 18, 10, 80,
            '<div style="text-align:center;">'
            . '<span style="font-family:greatvibes; font-size:42px; color:#111111;">' . e($data['student_name']) . '</span>'
            . '</div>',
            0, 0, false, true, '', true);

        // 2. Course title — well below "award of the certificate of" (y≈122mm).
        $pdf->writeHTMLCell(190, 14, 10, 140,
            '<div style="text-align:center;">'
            . '<span style="font-family:helvetica; font-size:28px; font-weight:bold; color:#1e3a8a; letter-spacing:1px;">' . strtoupper(e($data['course_title'])) . '</span>'
            . '</div>',
            0, 0, false, true, '', true);

        // 3. Classification "With <X>" + orange underline with centre diamond.
        if ($hasMerit) {
            $pdf->writeHTMLCell(190, 12, 10, 162,
                '<div style="text-align:center;">'
                . '<span style="font-family:greatvibes; font-size:30px; color:#111111;">With ' . e($data['classification']) . '</span>'
                . '</div>',
                0, 0, false, true, '', true);

            $meritY = 180;
            $pdf->SetLineWidth(0.4);
            $pdf->SetDrawColor(...$orange);
            $pdf->Line(75, $meritY, 100, $meritY);
            $pdf->Line(110, $meritY, 135, $meritY);
            $pdf->SetFillColor(...$orange);
            $pdf->Polygon([
                105, $meritY - 1.2, 106.4, $meritY,
                105, $meritY + 1.2, 103.6, $meritY,
            ], 'F');
        }

        // 4. Date paragraph — multi-font block (sans body + cursive
        //    day/month/year). Between the merit divider and the signature row
        //    (template's first sig line is at y≈250mm).
        $dateY = $hasMerit ? 190 : 175;
        $dateHtml = '<div style="text-align:center; font-family:helvetica; font-size:11px; color:#333333; line-height:1.6;">'
                  . 'Was admitted to the certificate at a Graduation<br>'
                  . 'Ceremony held on the '
                  . '<span style="font-family:greatvibes; font-size:20px; color:#1e3a8a;">' . e($data['graduation_day'] . $data['graduation_suffix']) . '</span>'
                  . ' day of '
                  . '<span style="font-family:greatvibes; font-size:20px; color:#1e3a8a;">' . e($data['graduation_month']) . '</span>'
                  . '<br>in the year '
                  . '<span style="font-family:greatvibes; font-size:20px; color:#1e3a8a;">' . e($data['graduation_year']) . '</span>'
                  . '</div>';
        $pdf->SetTextColor(0, 0, 0);
        $pdf->writeHTMLCell(190, 28, 10, $dateY, $dateHtml, 0, 0, false, true, '', true);

        // 5. Certificate number (left) and student number (right) — sit on the
        //    template's third (no-label) signature line at y≈275mm.
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(28, 271);
        $pdf->Cell(60, 5, $data['certificate_number'], 0, 0, 'C');
        $pdf->SetXY(122, 271);
        $pdf->Cell(60, 5, $data['student_number'], 0, 0, 'C');

        return $pdf->Output('', 'S');
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
     * Heavier multi-band frame matching the reference: thick orange outer,
     * thick blue inner, thin gold accent between them.
     */
    protected function drawFrames(TCPDF $pdf): void
    {
        $orange = [242, 101, 34];
        $blue   = [30, 58, 138];
        $gold   = [212, 175, 55];

        // Thick orange outer band
        $pdf->SetLineWidth(2.0);
        $pdf->SetDrawColor(...$orange);
        $pdf->Rect(6, 6, 198, 285);

        // Thin gold accent line between the orange and blue bands
        $pdf->SetLineWidth(0.3);
        $pdf->SetDrawColor(...$gold);
        $pdf->Rect(9, 9, 192, 279);

        // Thick blue inner band
        $pdf->SetLineWidth(2.0);
        $pdf->SetDrawColor(...$blue);
        $pdf->Rect(12, 12, 186, 273);

        // Orange corner triangles with blue diagonal trim stripes inside
        $pdf->SetFillColor(...$orange);
        $pdf->SetDrawColor(...$orange);
        $size = 22;
        $pdf->Polygon([6, 6, 6 + $size, 6, 6, 6 + $size], 'F');
        $pdf->Polygon([204 - $size, 6, 204, 6, 204, 6 + $size], 'F');
        $pdf->Polygon([6, 291 - $size, 6, 291, 6 + $size, 291], 'F');
        $pdf->Polygon([204, 291 - $size, 204, 291, 204 - $size, 291], 'F');

        $pdf->SetDrawColor(...$blue);
        $pdf->SetLineWidth(0.7);
        foreach ([4, 7] as $inset) {
            $pdf->Line(6 + $size - $inset, 6,            6,            6 + $size - $inset);
            $pdf->Line(204 - $size + $inset, 6,           204,          6 + $size - $inset);
            $pdf->Line(6,            291 - $size + $inset, 6 + $size - $inset, 291);
            $pdf->Line(204 - $size + $inset, 291,          204,          291 - $size + $inset);
        }
    }

    /**
     * Render the tiled "Edutrack Computer Training College" watermark
     * across the whole page at low opacity.
     */
    protected function drawWatermark(TCPDF $pdf): void
    {
        // Dense tiled text watermark — covers the whole inner area like the
        // reference. Kept faint enough that body text stays legible on top.
        $pdf->SetAlpha(0.13);
        $pdf->SetFont('helvetica', '', 4.5);
        $pdf->SetTextColor(30, 58, 138);

        $unit = 'Edutrack Computer Training College ';
        $row  = str_repeat($unit, 18);

        for ($y = 14; $y < 287; $y += 2.2) {
            $pdf->SetXY(8, $y);
            $pdf->Cell(196, 2, $row, 0, 0, 'L');
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
