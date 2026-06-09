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
        // Compact bottom block, matching the reference's tight signature stack.
        $signaturesY = $dateY + 34;
        $layout[] = ['date',        $dateY,            28];
        $layout[] = ['signatures',  $signaturesY,      12];
        $layout[] = ['graduate',    $signaturesY + 14, 10];
        $layout[] = ['ids',         $signaturesY + 26, 10];

        foreach ($layout as [$section, $y, $h]) {
            if (!isset($sections[$section])) {
                continue;
            }
            $pdf->writeHTMLCell(190, $h, 10, $y, $sections[$section], 0, 1, false, true, '', true);
        }

        $orange = [242, 101, 34];
        $blue   = [30, 58, 138];

        // Decorative blue divider with centre diamond, above "A skill training
        // college" — matches the reference's tagline ornament.
        $taglineDecorY = 36;
        $pdf->SetLineWidth(0.4);
        $pdf->SetDrawColor(...$blue);
        $pdf->Line(60, $taglineDecorY, 100, $taglineDecorY);
        $pdf->Line(110, $taglineDecorY, 150, $taglineDecorY);
        $pdf->SetFillColor(...$orange);
        $pdf->Polygon([
            105, $taglineDecorY - 1.4,
            106.4, $taglineDecorY,
            105, $taglineDecorY + 1.4,
            103.6, $taglineDecorY,
        ], 'F');

        // Solid orange lines flanking "THIS IS TO CERTIFY THAT".
        $certifyY = 60;
        $pdf->SetLineWidth(0.4);
        $pdf->SetDrawColor(...$orange);
        $pdf->Line(30, $certifyY, 60, $certifyY);
        $pdf->Line(150, $certifyY, 180, $certifyY);

        // Orange underline beneath the student name. Drawn natively so the
        // line sits below the cursive descenders instead of cutting through them.
        $nameRow = $layout[3] ?? null;
        if ($nameRow) {
            $nameUnderlineY = $nameRow[1] + $nameRow[2] - 1;
            $pdf->SetLineWidth(0.4);
            $pdf->SetDrawColor(...$orange);
            $pdf->Line(35, $nameUnderlineY, 175, $nameUnderlineY);
        }

        // Solid orange underline beneath "With Merit" with a centre diamond.
        if ($hasMerit) {
            $classRow = $layout[6] ?? null; // classification row
            if ($classRow) {
                $meritUnderlineY = $classRow[1] + $classRow[2] - 2;
                $pdf->SetLineWidth(0.4);
                $pdf->SetDrawColor(...$orange);
                $pdf->Line(70, $meritUnderlineY, 100, $meritUnderlineY);
                $pdf->Line(110, $meritUnderlineY, 140, $meritUnderlineY);
                $pdf->SetFillColor(...$orange);
                $pdf->Polygon([
                    105, $meritUnderlineY - 1.2,
                    106.2, $meritUnderlineY,
                    105, $meritUnderlineY + 1.2,
                    103.8, $meritUnderlineY,
                ], 'F');
            }
        }

        // Solid signature lines drawn natively above each label row.
        $pdf->SetLineWidth(0.4);
        $pdf->SetDrawColor(0, 0, 0);
        foreach ([$signaturesY - 1, $signaturesY + 13, $signaturesY + 25] as $lineY) {
            $pdf->Line(20, $lineY, 95, $lineY);
            $pdf->Line(115, $lineY, 190, $lineY);
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
