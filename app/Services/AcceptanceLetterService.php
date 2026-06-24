<?php

namespace App\Services;

use App\Models\AcceptanceLetter;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use TCPDF;

class AcceptanceLetterService
{
    /** Page geometry (mm). */
    private const PAGE_W = 210;
    private const PAGE_H = 297;
    private const MARGIN = 15;

    /**
     * Generate (or reuse) an acceptance letter for an enrollment.
     * Reference numbers are sequential per calendar year: EDU-ADM-YY-####.
     */
    public function generate(Enrollment $enrollment): AcceptanceLetter
    {
        return DB::transaction(function () use ($enrollment) {
            $existing = AcceptanceLetter::where('enrollment_id', $enrollment->id)->first();
            if ($existing) {
                return $existing;
            }

            $enrollment->load(['user', 'course']);
            $user = $enrollment->user;
            $course = $enrollment->course;

            $feeStructure = $course?->fee_structure ?: $this->defaultFeeStructure($course?->duration);
            $feeSnapshot = $this->buildFeeSnapshot($feeStructure);

            $referenceNo = $this->generateReferenceNumber();
            $issuedDate = now()->toDate();

            return AcceptanceLetter::create([
                'enrollment_id' => $enrollment->id,
                'reference_no' => $referenceNo,
                'student_name' => $user?->full_name ?? 'Unknown',
                'course_title' => $course?->title ?? 'Unknown Course',
                'mode' => $this->mapMode($enrollment->mode),
                'duration' => $this->resolveDuration($course, $feeStructure),
                'commencement_date' => $enrollment->start_date ?? $enrollment->enrolled_at ?? $issuedDate,
                'fee_snapshot' => $feeSnapshot,
                'issued_date' => $issuedDate,
                'signed_by' => null,
            ]);
        });
    }

    /**
     * Generate a sequential reference number like EDU-ADM-26-0001.
     */
    protected function generateReferenceNumber(): string
    {
        $yearSuffix = date('y');
        $prefix = "EDU-ADM-{$yearSuffix}-";

        $last = AcceptanceLetter::where('reference_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING(reference_no, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)) as seq')
            ->value('seq');

        return $prefix . str_pad((string) (($last ?? 0) + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Render the acceptance letter as PDF bytes.
     */
    public function render(AcceptanceLetter $letter): string
    {
        $pdf = new \App\Pdf\EdutrackPdf('P', 'mm', 'A4');
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack Computer Training College');
        $pdf->SetTitle('Acceptance Letter - ' . $letter->reference_no);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(self::MARGIN, self::MARGIN, self::MARGIN);
        $pdf->SetAutoPageBreak(true, self::MARGIN);
        $pdf->SetCellPadding(0);
        $pdf->AddPage();

        $this->drawLetterhead($pdf);
        $this->drawBody($pdf, $letter);

        return $pdf->Output('', 'S');
    }

    /**
     * Draw the top letterhead with logos and contact details.
     */
    protected function drawLetterhead(TCPDF $pdf): void
    {
        $y = 12;

        // Left logo
        $logo = public_path('assets/images/certificate/edutrack-logo.png');
        if (is_file($logo)) {
            $pdf->Image($logo, self::MARGIN, $y, 22, 0);
        }

        // Right logo
        $teveta = public_path('assets/images/certificate/teveta-logo.png');
        if (is_file($teveta)) {
            $pdf->Image($teveta, self::PAGE_W - self::MARGIN - 28, $y + 2, 28, 0);
        }

        // Centered header text
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'EDUTRACK COMPUTER TRAINING', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 5, 'A Skill Training College', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Kalomo District, Southern Province', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Tel: 0965992967 | 0770666937', 0, 1, 'C');

        $pdf->Ln(4);
        $pdf->SetLineWidth(0.3);
        $pdf->SetDrawColor(128, 128, 128);
        $pdf->Line(self::MARGIN, $pdf->GetY(), self::PAGE_W - self::MARGIN, $pdf->GetY());
        $pdf->Ln(6);
    }

    /**
     * Draw the body of the acceptance letter.
     */
    protected function drawBody(TCPDF $pdf, AcceptanceLetter $letter): void
    {
        $yStart = $pdf->GetY();

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'LETTER OF ACCEPTANCE', 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 6, 'Date: ' . $letter->issued_date->format('d/m/Y'), 0, 1, 'R');
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, 'RE: OFFER OF ADMISSION', 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 6, 'Dear ' . $letter->student_name . ',', 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->MultiCell(0, 6, 'We are pleased to inform you that your application to study at EDUTRACK COMPUTER TRAINING has been SUCCESSFULLY ACCEPTED.', 0, 'L');
        $pdf->Ln(2);

        $pdf->Cell(0, 6, 'You have been offered admission into the following programme:', 0, 1, 'L');
        $pdf->Ln(1);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, 'Course: ' . $letter->course_title, 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $isPhysical = in_array($letter->mode, ['Physical', 'Hybrid'], true);
        $isOnline = $letter->mode === 'Online';
        $pdf->Cell(0, 6, 'Mode of Study: [' . ($isPhysical ? 'X' : ' ') . '] Physical    [' . ($isOnline ? 'X' : ' ') . '] Online', 0, 1, 'L');
        $pdf->Cell(0, 6, 'Duration: ' . $letter->duration, 0, 1, 'L');
        $pdf->Cell(0, 6, 'Commencement Date: ' . ($letter->commencement_date?->format('d/m/Y') ?: '______________'), 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, 'FEES INFORMATION', 0, 1, 'L');
        $pdf->Ln(1);

        $this->drawFeeTable($pdf, 'DAY SCHOOL', $letter->fee_snapshot['day'] ?? [], false);
        $pdf->Ln(4);
        $this->drawFeeTable($pdf, 'BOARDING', $letter->fee_snapshot['boarding'] ?? [], true);
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, 'Payment can be made in cash or through:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 6, 'Bank: ACCESS BANK', 0, 1, 'L');
        $pdf->Cell(0, 6, 'Account Name: EDUTRACK COMPUTER TRAINING SCHOOL', 0, 1, 'L');
        $pdf->Cell(0, 6, 'Account Number: 0106509665016', 0, 1, 'L');
        $pdf->Ln(6);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 6, 'Yours faithfully,', 0, 1, 'L');
        $pdf->Ln(8);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, 'Admissions Officer', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 6, 'EDUTRACK COMPUTER TRAINING', 0, 1, 'L');
        $pdf->Ln(2);
        $pdf->Cell(0, 6, 'NAME: ..............................    SIGN: ..............................', 0, 1, 'L');
        $pdf->Ln(6);

        $this->drawConditionsBox($pdf);
    }

    /**
     * Draw a fee table for day school or boarding.
     */
    protected function drawFeeTable(TCPDF $pdf, string $title, array $fees, bool $isBoarding): void
    {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, $title, 0, 1, 'L');

        if (empty($fees)) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, 'No fee information available.', 0, 1, 'L');
            return;
        }

        $hasTerms = array_is_list($fees);

        if ($hasTerms) {
            $this->drawTermFeeTable($pdf, $fees, $isBoarding);
        } else {
            $this->drawSingleFeeTable($pdf, $fees);
        }
    }

    /**
     * Draw a single-payment fee table.
     */
    protected function drawSingleFeeTable(TCPDF $pdf, array $fees): void
    {
        $lineItems = $fees;
        unset($lineItems['Total']);

        $colWidth = (self::PAGE_W - self::MARGIN * 2) / 2;
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell($colWidth, 7, 'Item', 1, 0, 'L', true);
        $pdf->Cell($colWidth, 7, 'Amount (ZMW)', 1, 1, 'R', true);

        $pdf->SetFont('helvetica', '', 10);
        foreach ($lineItems as $item => $amount) {
            $pdf->Cell($colWidth, 7, $item, 1, 0, 'L');
            $pdf->Cell($colWidth, 7, number_format((float) $amount, 2), 1, 1, 'R');
        }

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($colWidth, 7, 'Total', 1, 0, 'L', true);
        $pdf->Cell($colWidth, 7, number_format((float) ($fees['Total'] ?? 0), 2), 1, 1, 'R', true);
    }

    /**
     * Draw a per-term fee table.
     */
    protected function drawTermFeeTable(TCPDF $pdf, array $terms, bool $isBoarding): void
    {
        $availableWidth = self::PAGE_W - self::MARGIN * 2;
        $termCount = count($terms);
        $termWidth = $availableWidth * 0.18;
        $itemWidth = ($availableWidth - ($termWidth * $termCount)) / 2;

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell($itemWidth, 7, 'Item', 1, 0, 'L', true);
        foreach ($terms as $i => $term) {
            $label = $term['term'] ?? ('Term ' . ($i + 1));
            $pdf->Cell($termWidth, 7, $label, 1, 0, 'C', true);
        }
        $pdf->Cell($itemWidth, 7, 'Total (ZMW)', 1, 1, 'R', true);

        $lineItemKeys = $this->collectLineItemKeys($terms);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($lineItemKeys as $key) {
            if ($key === 'Total') {
                continue;
            }
            $pdf->Cell($itemWidth, 7, $key, 1, 0, 'L');
            $rowTotal = 0;
            foreach ($terms as $term) {
                $amount = $term[$key] ?? 0;
                $rowTotal += (float) $amount;
                $pdf->Cell($termWidth, 7, $amount ? number_format((float) $amount, 2) : '—', 1, 0, 'R');
            }
            $pdf->Cell($itemWidth, 7, number_format($rowTotal, 2), 1, 1, 'R');
        }

        // Total row
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell($itemWidth, 7, 'Total', 1, 0, 'L', true);
        $grandTotal = 0;
        foreach ($terms as $term) {
            $termTotal = $term['Total'] ?? array_sum(array_diff_key($term, ['term' => true]));
            $grandTotal += (float) $termTotal;
            $pdf->Cell($termWidth, 7, number_format((float) $termTotal, 2), 1, 0, 'R', true);
        }
        $pdf->Cell($itemWidth, 7, number_format($grandTotal, 2), 1, 1, 'R', true);
    }

    /**
     * Collect all line-item keys used across terms.
     */
    protected function collectLineItemKeys(array $terms): array
    {
        $keys = [];
        foreach ($terms as $term) {
            foreach (array_keys($term) as $key) {
                if ($key === 'term') {
                    continue;
                }
                $keys[$key] = true;
            }
        }

        $order = ['Tuition Fees', 'Identity Card', 'Boarding fee', 'Internet', 'Exam Fees', 'Total'];
        $ordered = [];
        foreach ($order as $key) {
            if (isset($keys[$key])) {
                $ordered[] = $key;
                unset($keys[$key]);
            }
        }

        return array_merge($ordered, array_keys($keys));
    }

    /**
     * Draw the boxed admission conditions.
     */
    protected function drawConditionsBox(TCPDF $pdf): void
    {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, 'ADMISSION CONDITIONS', 0, 1, 'L');

        $conditions = [
            '1. Pay the required fees before or on the reporting date.',
            '2. Submit copies of your NRC/Passport and academic certificates.',
            '3. Comply with all rules and regulations of the institution.',
            '4. Students attending physical classes should report to our campus in Kalomo District, Southern Province. Online students will receive platform access details upon confirmation of payment.',
        ];

        $boxY = $pdf->GetY();
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);

        foreach ($conditions as $condition) {
            $pdf->MultiCell(self::PAGE_W - self::MARGIN * 2 - 4, 6, $condition, 0, 'L');
        }

        $boxH = $pdf->GetY() - $boxY;
        $pdf->Rect(self::MARGIN, $boxY, self::PAGE_W - self::MARGIN * 2, $boxH, 'D');
    }

    /**
     * Map enrollment mode to the letter's mode label.
     */
    protected function mapMode(?string $mode): string
    {
        $m = strtolower(str_replace([' ', '-', '_'], '', (string) $mode));
        return match ($m) {
            'online' => 'Online',
            'inperson', 'physical', 'hybrid' => 'Physical',
            default => 'Physical',
        };
    }

    /**
     * Resolve the duration string from the course or fee structure.
     */
    protected function resolveDuration(?object $course, array $feeStructure): string
    {
        if ($course && !empty($course->duration)) {
            return $course->duration;
        }

        if (!empty($feeStructure['duration'])) {
            return $feeStructure['duration'];
        }

        if ($course && !empty($course->duration_weeks)) {
            $map = [12 => '3 months', 24 => '6 months', 48 => '1 year (12 months)'];
            return $map[$course->duration_weeks] ?? ($course->duration_weeks . ' weeks');
        }

        return 'As stated in the course brochure';
    }

    /**
     * Build a normalised fee snapshot from the course fee structure.
     */
    protected function buildFeeSnapshot(array $feeStructure): array
    {
        $type = $feeStructure['type'] ?? 'single';

        if ($type === 'per_term') {
            return [
                'type' => 'per_term',
                'day' => $feeStructure['day'] ?? [],
                'boarding' => $feeStructure['boarding'] ?? [],
            ];
        }

        return [
            'type' => 'single',
            'day' => $feeStructure['day'] ?? $this->defaultDaySchoolFees(),
            'boarding' => $feeStructure['boarding'] ?? $this->defaultBoardingFees(),
        ];
    }

    /**
     * Fallback fee structure when a course has none configured.
     */
    protected function defaultFeeStructure(?string $duration): array
    {
        $durationLower = strtolower($duration ?? '');

        if (str_contains($durationLower, 'year') || str_contains($durationLower, '12 month')) {
            return $this->oneYearFeeStructure();
        }

        if (str_contains($durationLower, '6 month') || str_contains($durationLower, 'six month')) {
            return $this->sixMonthFeeStructure();
        }

        return $this->threeMonthFeeStructure();
    }

    /**
     * Three-month (single payment) fee structure.
     */
    public function threeMonthFeeStructure(): array
    {
        return [
            'type' => 'single',
            'duration' => '3 months',
            'day' => [
                'Tuition Fees' => 2700,
                'Identity Card' => 50,
                'Internet' => 100,
                'Exam Fees' => 150,
                'Total' => 3000,
            ],
            'boarding' => [
                'Tuition Fees' => 2700,
                'Identity Card' => 50,
                'Boarding fee' => 500,
                'Internet' => 100,
                'Exam Fees' => 150,
                'Total' => 3500,
            ],
        ];
    }

    /**
     * Six-month (per-term) fee structure.
     */
    public function sixMonthFeeStructure(): array
    {
        return [
            'type' => 'per_term',
            'duration' => '6 months',
            'day' => [
                ['term' => 'Term 1', 'Tuition Fees' => 2700, 'Identity Card' => 50, 'Internet' => 100, 'Exam Fees' => 150, 'Total' => 3000],
                ['term' => 'Term 2', 'Tuition Fees' => 2700, 'Internet' => 100, 'Total' => 2800],
            ],
            'boarding' => [
                ['term' => 'Term 1', 'Tuition Fees' => 2700, 'Identity Card' => 50, 'Boarding fee' => 500, 'Internet' => 100, 'Exam Fees' => 150, 'Total' => 3500],
                ['term' => 'Term 2', 'Tuition Fees' => 2700, 'Boarding fee' => 500, 'Internet' => 100, 'Total' => 3300],
            ],
        ];
    }

    /**
     * One-year (per-term) fee structure.
     */
    public function oneYearFeeStructure(): array
    {
        return [
            'type' => 'per_term',
            'duration' => '1 year (12 months)',
            'day' => [
                ['term' => 'Term 1', 'Tuition Fees' => 2700, 'Identity Card' => 50, 'Internet' => 100, 'Exam Fees' => 150, 'Total' => 3000],
                ['term' => 'Term 2', 'Tuition Fees' => 2700, 'Internet' => 100, 'Total' => 2800],
                ['term' => 'Term 3', 'Tuition Fees' => 2700, 'Internet' => 100, 'Total' => 2800],
            ],
            'boarding' => [
                ['term' => 'Term 1', 'Tuition Fees' => 2700, 'Identity Card' => 50, 'Boarding fee' => 500, 'Internet' => 100, 'Exam Fees' => 150, 'Total' => 3500],
                ['term' => 'Term 2', 'Tuition Fees' => 2700, 'Boarding fee' => 500, 'Internet' => 100, 'Total' => 3300],
                ['term' => 'Term 3', 'Tuition Fees' => 2700, 'Boarding fee' => 500, 'Internet' => 100, 'Total' => 3300],
            ],
        ];
    }

    protected function defaultDaySchoolFees(): array
    {
        return $this->threeMonthFeeStructure()['day'];
    }

    protected function defaultBoardingFees(): array
    {
        return $this->threeMonthFeeStructure()['boarding'];
    }
}
