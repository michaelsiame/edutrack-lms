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
        return $this->renderPdf($this->getCertificateData($certificate));
    }

    /** Certificate palette. */
    private const GOLD = [212, 149, 42];
    private const GOLD_LIGHT = [232, 184, 74];
    private const NAVY = [27, 58, 107];
    private const NAVY_DARK = [15, 43, 82];
    private const DARK = [10, 22, 40];

    /** Page geometry (mm). */
    private const PAGE_W = 210;
    private const PAGE_H = 297;
    private const CENTER_X = 105;
    private const GOLD_FRAME = 5.3;   // outer gold frame thickness
    private const CORNER_INSET = 6.3; // corner triangles inset
    private const CORNER_LEG = 21.2;  // corner triangle leg length

    /**
     * Render the certificate PDF natively with TCPDF (no writeHTML) so the
     * watermark, custom fonts and decorative frame survive on shared hosting.
     */
    public function renderPdf(array $data): string
    {
        $pdf = new TCPDF('P', 'mm', 'A4');
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack Computer Training College');
        $pdf->SetTitle('Certificate - ' . ($data['certificate_number'] ?? ''));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetCellPadding(0);
        $pdf->SetCellMargins(0);
        $pdf->AddPage();

        $this->drawFrame($pdf);
        $this->drawWatermark($pdf);
        $this->drawBorders($pdf);
        $this->drawContent($pdf, $data);

        return $pdf->Output('', 'S');
    }

    /**
     * Gold outer frame and white sheet.
     */
    protected function drawFrame(TCPDF $pdf): void
    {
        $pdf->Rect(0, 0, self::PAGE_W, self::PAGE_H, 'F', [], self::GOLD);
        $t = self::GOLD_FRAME;
        $pdf->Rect($t, $t, self::PAGE_W - 2 * $t, self::PAGE_H - 2 * $t, 'F', [], [255, 255, 255]);
    }

    /**
     * Tiled, rotated institution-name watermark across the sheet.
     */
    protected function drawWatermark(TCPDF $pdf): void
    {
        $inset = self::CORNER_INSET;

        $pdf->StartTransform();
        // Clip the tiles to the area inside the gold frame
        $pdf->Rect($inset, $inset, self::PAGE_W - 2 * $inset, self::PAGE_H - 2 * $inset, 'CNZ');
        $pdf->SetAlpha(0.055);
        $pdf->SetTextColor(...self::NAVY);
        $this->certFont($pdf, 'serif-bold', 10.5);
        $pdf->setFontSpacing(0.25);

        $text = 'EDUTRACK COMPUTER TRAINING COLLEGE';
        $stepX = $pdf->GetStringWidth($text) + 16;
        $stepY = 13;

        // TCPDF clamps cell coordinates to the page box, so the tile grid
        // cannot simply be drawn under one global rotation. Instead, each
        // tile anchor is computed on the rotated lattice and the tile is
        // rotated about its own anchor — geometrically identical. Tiles
        // whose anchor falls off-page are trimmed character by character
        // (advancing along the tilted baseline) until they enter the page.
        $angle = 14;
        $cos = cos(deg2rad($angle));
        $sin = sin(deg2rad($angle));
        $cx = self::PAGE_W / 2;
        $cy = self::PAGE_H / 2;

        for ($j = -16; $j <= 16; $j++) {
            $gy = $j * $stepY;
            $phase = (abs($j) % 2) * ($stepX / 2);
            for ($i = -3; $i <= 3; $i++) {
                $gx = $i * $stepX + $phase - $stepX / 2;
                // Lattice point rotated about the page center (y-down coords)
                $x = $cx + $gx * $cos + $gy * $sin;
                $y = $cy - $gx * $sin + $gy * $cos;

                // Trim leading characters until the anchor is on the page
                $tile = $text;
                while ($tile !== '' && ($x < 1 || $y < 2 || $y > self::PAGE_H - 2)) {
                    $w = $pdf->GetStringWidth($tile[0]);
                    $x += $w * $cos;
                    $y -= $w * $sin;
                    $tile = substr($tile, 1);
                }
                if ($tile === '' || $x > self::PAGE_W - 2 || $y < 2 || $y > self::PAGE_H - 2) {
                    continue;
                }

                $pdf->StartTransform();
                $pdf->Rotate($angle, $x, $y);
                $pdf->Text($x, $y, $tile);
                $pdf->StopTransform();
            }
        }

        $pdf->setFontSpacing(0);
        $pdf->StopTransform();
        $pdf->SetAlpha(1);
    }

    /**
     * Navy inner border and corner triangles (drawn above the watermark).
     */
    protected function drawBorders(TCPDF $pdf): void
    {
        // Navy ring: band from 9.0mm to 11.6mm inset, stroked on its centerline
        $pdf->SetLineStyle(['width' => 2.6, 'color' => self::NAVY]);
        $pdf->Rect(10.3, 10.3, self::PAGE_W - 20.6, self::PAGE_H - 20.6, 'D');

        $c = self::CORNER_INSET;
        $l = self::CORNER_LEG;
        $w = self::PAGE_W;
        $h = self::PAGE_H;
        $pdf->Polygon([$c, $c, $c + $l, $c, $c, $c + $l], 'F', [], self::NAVY);                          // top-left
        $pdf->Polygon([$w - $c, $c, $w - $c - $l, $c, $w - $c, $c + $l], 'F', [], self::NAVY);           // top-right
        $pdf->Polygon([$c, $h - $c, $c + $l, $h - $c, $c, $h - $c - $l], 'F', [], self::NAVY);           // bottom-left
        $pdf->Polygon([$w - $c, $h - $c, $w - $c - $l, $h - $c, $w - $c, $h - $c - $l], 'F', [], self::NAVY); // bottom-right
    }

    /**
     * All certificate text, logos, QR code and decorations.
     */
    protected function drawContent(TCPDF $pdf, array $data): void
    {
        $cx = self::CENTER_X;

        // --- Logos row ---
        $logo = public_path('assets/images/logo.png');
        if (is_file($logo)) {
            $pdf->Image($logo, 27, 16, 0, 28);
        }
        $teveta = public_path('assets/images/teveta-logo.png');
        if (is_file($teveta)) {
            [$iw, $ih] = getimagesize($teveta);
            $h = 16;
            $w = $ih > 0 ? $h * $iw / $ih : $h;
            $pdf->Image($teveta, 183 - $w, 22, $w, $h);
        }

        // --- Institution name ---
        $pdf->SetTextColor(...self::NAVY_DARK);
        $this->certFont($pdf, 'serif-bold', 20);
        $pdf->setFontSpacing(0.66);
        $this->centeredLine($pdf, 47, 'EDUTRACK COMPUTER');
        $this->centeredLine($pdf, 56, 'TRAINING COLLEGE');
        $pdf->setFontSpacing(0);

        $pdf->SetTextColor(...self::DARK);
        $this->certFont($pdf, 'serif-italic', 13);
        $this->centeredLine($pdf, 65, 'A skill training college');

        // --- Certify banner between decorative rules ---
        $this->decoRule($pdf, 75);

        $this->certFont($pdf, 'serif-bold', 17);
        $pdf->setFontSpacing(0.9);
        $pdf->SetTextColor(...self::NAVY);
        $certify = 'THIS IS TO CERTIFY THAT';
        $textW = $pdf->GetStringWidth($certify) + 0.9 * strlen($certify);
        $this->centeredLine($pdf, 80, $certify);
        $pdf->setFontSpacing(0);
        $this->certifyFlanks($pdf, 83.7, $textW / 2 + 5);

        $this->decoRule($pdf, 90);

        // --- Recipient name ---
        $pdf->SetTextColor(...self::DARK);
        $name = $data['student_name'] ?? '';
        $this->fitFont($pdf, 'script', 50, $name, 165);
        $this->centeredLine($pdf, 94, $name, 22);

        // --- Body text ---
        $this->certFont($pdf, 'serif-italic', 13);
        $this->centeredLine($pdf, 119, 'having satisfied the requirements for the award of the certificate of');

        // --- Course title ---
        $pdf->SetTextColor(...self::NAVY);
        $this->drawCourseTitle($pdf, mb_strtoupper($data['course_title'] ?? ''));

        // --- Classification ---
        $classification = $data['classification'] ?? null;
        if ($classification && strcasecmp($classification, 'Pass') !== 0) {
            $pdf->SetTextColor(...self::DARK);
            $this->certFont($pdf, 'script', 38);
            $this->centeredLine($pdf, 143, 'With ' . $classification, 18);
        }

        $this->decoRule($pdf, 165);

        // --- Graduation date ---
        $pdf->SetTextColor(...self::DARK);
        $this->certFont($pdf, 'serif-italic', 13);
        $this->centeredLine($pdf, 170, 'Was admitted to the certificate at a Graduation');
        $this->centeredSegments($pdf, 177, [
            ['Ceremony held on the ', 'serif-italic', 13, 0],
            [($data['graduation_day'] ?? '') , 'serif-italic', 13, 0],
            [($data['graduation_suffix'] ?? ''), 'serif', 8, 1.2],
            [' day of ' . ($data['graduation_month'] ?? ''), 'serif-italic', 13, 0],
        ]);
        $this->centeredSegments($pdf, 184, [
            ['in the year ', 'serif-italic', 13, 0],
            [($data['graduation_year'] ?? ''), 'serif-bold', 13, 0],
        ]);

        // --- QR verification code ---
        if (!empty($data['verify_url'])) {
            $pdf->write2DBarcode(
                $data['verify_url'],
                'QRCODE,M',
                $cx - 10, 212, 20, 20,
                ['border' => 0, 'padding' => 0, 'fgcolor' => self::NAVY_DARK, 'bgcolor' => false],
                'N'
            );
            $this->certFont($pdf, 'serif', 6.5);
            $pdf->SetTextColor(...self::NAVY);
            $pdf->setFontSpacing(0.3);
            $this->centeredLine($pdf, 233, 'SCAN TO VERIFY');
            $pdf->setFontSpacing(0);
        }

        // --- Signature lines ---
        $pdf->SetLineStyle(['width' => 0.4, 'color' => self::DARK]);
        $pdf->Line(31, 243, 81, 243);
        $pdf->Line(129, 243, 179, 243);
        $this->certFont($pdf, 'serif-bold', 10.5);
        $pdf->SetTextColor(...self::DARK);
        $pdf->SetXY(31, 244.5);
        $pdf->Cell(50, 5, 'Principal', 0, 0, 'C');
        $pdf->SetXY(129, 244.5);
        $pdf->Cell(50, 5, 'Director', 0, 0, 'C');

        // --- Bottom row: NRC and student number ---
        $this->certFont($pdf, 'serif-bold', 10);
        $pdf->SetXY(28, 256);
        $pdf->Cell(70, 5, "Graduate's Signature", 0, 0, 'L');
        $pdf->SetXY(112, 256);
        $pdf->Cell(70, 5, "Graduate's ID No.", 0, 0, 'R');

        $pdf->SetFont('courier', 'B', 13);
        $pdf->SetXY(28, 261.5);
        $pdf->Cell(70, 6, $data['national_id'] ?? '', 0, 0, 'L');
        $pdf->SetXY(112, 261.5);
        $pdf->Cell(70, 6, $data['student_number'] ?? '', 0, 0, 'R');

        // --- Verification footer ---
        if (!empty($data['verification_code'])) {
            $this->certFont($pdf, 'serif-italic', 7.5);
            $pdf->SetTextColor(90, 100, 115);
            $this->centeredLine(
                $pdf,
                271.5,
                'Certificate No. ' . ($data['certificate_number'] ?? '')
                    . '  ·  Verify at ' . preg_replace('#^https?://#', '', $data['verify_url'] ?? '')
            );
        }
    }

    /**
     * Load a certificate font (with built-in fallback) and set it.
     */
    protected function certFont(TCPDF $pdf, string $key, float $size): void
    {
        $map = [
            'script' => 'greatvibes',
            'serif' => 'playfairdisplay',
            'serif-bold' => 'playfairdisplayb',
            'serif-black' => 'playfairdisplayblack',
            'serif-italic' => 'playfairdisplayi',
        ];
        $name = $map[$key] ?? $key;
        $file = resource_path('fonts/tcpdf/' . $name . '.php');

        if (is_file($file)) {
            $pdf->AddFont($name, '', $file);
            $pdf->SetFont($name, '', $size);
            return;
        }

        // Fallback to TCPDF built-ins if the font files are missing
        $fallback = ['script' => ['times', 'I'], 'serif-italic' => ['times', 'I'], 'serif-bold' => ['times', 'B'], 'serif-black' => ['times', 'B']];
        [$family, $style] = $fallback[$key] ?? ['times', ''];
        $pdf->SetFont($family, $style, $size);
    }

    /**
     * Draw the course title, shrinking to fit and wrapping onto two
     * balanced lines when a single line cannot hold it.
     */
    protected function drawCourseTitle(TCPDF $pdf, string $course): void
    {
        $spacing = 0.7;
        $maxWidth = 162;
        $pdf->setFontSpacing($spacing);
        $lineWidth = function (string $text) use ($pdf, $spacing): float {
            return $pdf->GetStringWidth($text) + $spacing * mb_strlen($text);
        };

        $size = 32;
        $this->certFont($pdf, 'serif-black', $size);
        while ($size > 22 && $lineWidth($course) > $maxWidth) {
            $size -= 1;
            $this->certFont($pdf, 'serif-black', $size);
        }

        if ($lineWidth($course) <= $maxWidth) {
            $this->centeredLine($pdf, 128, $course, 14);
            $pdf->setFontSpacing(0);
            return;
        }

        // Split into two lines at the word boundary closest to the middle
        $words = explode(' ', $course);
        $bestSplit = 1;
        $bestDiff = PHP_INT_MAX;
        for ($i = 1; $i < count($words); $i++) {
            $left = implode(' ', array_slice($words, 0, $i));
            $right = implode(' ', array_slice($words, $i));
            $diff = abs(mb_strlen($left) - mb_strlen($right));
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $bestSplit = $i;
            }
        }
        $line1 = implode(' ', array_slice($words, 0, $bestSplit));
        $line2 = implode(' ', array_slice($words, $bestSplit));

        $size = 24;
        $this->certFont($pdf, 'serif-black', $size);
        while ($size > 13 && max($lineWidth($line1), $lineWidth($line2)) > $maxWidth) {
            $size -= 1;
            $this->certFont($pdf, 'serif-black', $size);
        }

        $this->centeredLine($pdf, 124, $line1, 9);
        $this->centeredLine($pdf, 133, $line2, 9);
        $pdf->setFontSpacing(0);
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
     * Draw a horizontally centered line of text at the given y.
     */
    protected function centeredLine(TCPDF $pdf, float $y, string $text, float $height = 7): void
    {
        $pdf->SetXY(self::GOLD_FRAME, $y);
        $pdf->Cell(self::PAGE_W - 2 * self::GOLD_FRAME, $height, $text, 0, 0, 'C');
    }

    /**
     * Draw a centered line built from segments with mixed fonts/sizes.
     * Each segment: [text, fontKey, size, verticalRise].
     */
    protected function centeredSegments(TCPDF $pdf, float $y, array $segments): void
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
            $pdf->SetXY($x, $y - ($s[3] ?? 0));
            $pdf->Cell($widths[$i] + 0.5, 6, $s[0], 0, 0, 'L');
            $x += $widths[$i];
        }
    }

    /**
     * Decorative rule: line — small diamond — diamond — small diamond — line.
     */
    protected function decoRule(TCPDF $pdf, float $cy): void
    {
        $cx = self::CENTER_X;
        $pdf->SetLineStyle(['width' => 0.55, 'color' => self::GOLD]);
        $pdf->Line($cx - 52, $cy, $cx - 7, $cy);
        $pdf->Line($cx + 7, $cy, $cx + 52, $cy);
        $this->diamond($pdf, $cx - 4.4, $cy, 0.9, self::GOLD_LIGHT);
        $this->diamond($pdf, $cx, $cy, 1.4, self::GOLD);
        $this->diamond($pdf, $cx + 4.4, $cy, 0.9, self::GOLD_LIGHT);
    }

    /**
     * Flanking decorations either side of the certify banner.
     */
    protected function certifyFlanks(TCPDF $pdf, float $cy, float $halfGap): void
    {
        $cx = self::CENTER_X;
        $pdf->SetLineStyle(['width' => 0.55, 'color' => self::GOLD]);
        foreach ([-1, 1] as $side) {
            $edge = $cx + $side * $halfGap;
            $this->diamond($pdf, $edge, $cy, 1.0, self::GOLD);
            $this->diamond($pdf, $edge + $side * 3, $cy, 0.7, self::GOLD_LIGHT);
            $pdf->Line($edge + $side * 5.5, $cy, $edge + $side * 18.5, $cy);
        }
    }

    /**
     * Filled diamond (rotated square) centered at (cx, cy).
     */
    protected function diamond(TCPDF $pdf, float $cx, float $cy, float $r, array $color): void
    {
        $pdf->Polygon([$cx, $cy - $r, $cx + $r, $cy, $cx, $cy + $r, $cx - $r, $cy], 'F', [], $color);
    }

    /**
     * Get certificate data for PDF generation.
     */
    public function getCertificateData(Certificate $certificate): array
    {
        $certificate->load(['user', 'course', 'enrollment']);

        $issuedDate = $certificate->issued_date ?? now();
        $graduationDate = $certificate->graduation_ceremony_date ?? $issuedDate;

        $nationalId = $certificate->user?->national_id;

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
            'student_number' => $this->generateStudentNumber($certificate),
            'national_id' => $nationalId ? 'NRC ' . ltrim(preg_replace('/^\s*NRC\s*/i', '', $nationalId)) : '',
        ];
    }
}
