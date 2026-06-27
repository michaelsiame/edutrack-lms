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
     * Render a BLANK, printable acceptance-letter template for a CDF Computer
     * Studies level ('i', 'ii' or 'iii'). The course title, duration and the
     * correct fee table are pre-filled; applicant details are left blank for
     * hand-completion. Returns PDF bytes.
     */
    public function renderBlank(string $level): string
    {
        $level = strtolower(trim($level));
        $config = match ($level) {
            'i', '1', 'level-i' => [
                'title' => 'Trade Certificate in Computer Studies Level I',
                'fees' => $this->oneYearFeeStructure(),
                'duration' => '1 year (12 months)',
            ],
            'ii', '2', 'level-ii' => [
                'title' => 'Trade Certificate in Computer Studies Level II',
                'fees' => $this->sixMonthFeeStructure(),
                'duration' => '6 months',
            ],
            default => [
                'title' => 'Trade Certificate in Computer Studies Level III',
                'fees' => $this->threeMonthFeeStructure(),
                'duration' => '3 months',
            ],
        };

        $snapshot = $this->buildFeeSnapshot($config['fees']);

        $pdf = new \App\Pdf\EdutrackPdf('P', 'mm', 'A4');
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack Computer Training College');
        $pdf->SetTitle('Acceptance Letter Template - ' . $config['title']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(self::MARGIN, self::MARGIN, self::MARGIN);
        $pdf->SetAutoPageBreak(true, self::MARGIN);
        $pdf->SetCellPadding(0);
        $pdf->AddPage();

        $this->drawLetterhead($pdf);
        $this->drawBlankBody($pdf, $config['title'], $config['duration'], $snapshot);

        return $pdf->Output('', 'S');
    }

    /**
     * Draw the body of a blank, fill-in acceptance-letter template.
     */
    protected function drawBlankBody(TCPDF $pdf, string $courseTitle, string $duration, array $feeSnapshot): void
    {
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->Cell(0, 5.4, 'LETTER OF ACCEPTANCE', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9.5);
        $pdf->Cell(0, 3.6, '(CDF-Sponsored Programme)', 0, 1, 'C');
        $pdf->Ln(0.6);

        $pdf->SetFont('helvetica', '', 10.5);
        $pdf->Cell(0, 4.2, 'Date: ......................................', 0, 1, 'R');
        $pdf->Cell(0, 4.2, 'Ref No: EDU-ADM-........................', 0, 1, 'R');
        $pdf->Ln(0.6);

        // Applicant fill-in block
        $pdf->SetFont('helvetica', 'B', 10.5);
        $pdf->Cell(0, 4.2, 'APPLICANT DETAILS', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10.5);
        $pdf->Cell(0, 5.0, 'Full Name: ...........................................................................................', 0, 1, 'L');
        $pdf->Cell(0, 5.0, 'NRC No: ..............................................   Phone: ..............................', 0, 1, 'L');
        $pdf->Ln(0.6);

        $pdf->SetFont('helvetica', 'B', 10.5);
        $pdf->Cell(0, 4.2, 'RE: OFFER OF ADMISSION', 0, 1, 'L');
        $pdf->Ln(0.4);

        $pdf->SetFont('helvetica', '', 10.5);
        $pdf->Cell(0, 5.0, 'Dear .......................................................................,', 0, 1, 'L');
        $pdf->MultiCell(0, 4.2, 'We are pleased to inform you that your application to study at EDUTRACK COMPUTER TRAINING COLLEGE has been SUCCESSFULLY ACCEPTED. You have been offered admission into the following programme:', 0, 'L');
        $pdf->Ln(0.6);

        $pdf->SetFont('helvetica', 'B', 10.5);
        $pdf->Cell(0, 4.2, 'Course: ' . $courseTitle, 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10.5);
        $pdf->Cell(0, 4.2, 'Mode of Study: [  ] Physical    [  ] Online        Duration: ' . $duration, 0, 1, 'L');
        $pdf->Cell(0, 4.2, 'Commencement Date: ......................................', 0, 1, 'L');
        $pdf->Ln(1.0);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 5.0, 'FEES INFORMATION', 0, 1, 'L');
        $pdf->Ln(0.4);

        $this->drawFeeTable($pdf, 'DAY SCHOOL', $feeSnapshot['day'] ?? [], false);
        $pdf->Ln(1.6);
        $this->drawFeeTable($pdf, 'BOARDING', $feeSnapshot['boarding'] ?? [], true);
        $pdf->Ln(1.8);

        $pdf->SetFont('helvetica', 'B', 10.5);
        $pdf->Cell(0, 4.2, 'Payment can be made in cash or through:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10.5);
        $pdf->Cell(0, 4.2, 'Bank: ACCESS BANK   |   Account Name: EDUTRACK COMPUTER TRAINING SCHOOL', 0, 1, 'L');
        $pdf->Cell(0, 4.2, 'Account Number: 0106509665016', 0, 1, 'L');
        $pdf->Ln(2.2);

        $pdf->SetFont('helvetica', '', 10.5);
        $pdf->Cell(0, 4.2, 'Yours faithfully,', 0, 1, 'L');
        $pdf->Ln(3.2);
        $pdf->SetFont('helvetica', 'B', 10.5);
        $pdf->Cell(0, 4.2, 'Admissions Officer', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10.5);
        $pdf->Cell(0, 4.2, 'EDUTRACK COMPUTER TRAINING COLLEGE', 0, 1, 'L');
        $pdf->Cell(0, 4.2, 'NAME: ..............................    SIGN: ..............................', 0, 1, 'L');
        $pdf->Ln(2.2);

        $this->drawConditionsBox($pdf);
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
        $pdf->Cell(0, 6.2, 'EDUTRACK COMPUTER TRAINING COLLEGE', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 3.9, 'A Skills Training College', 0, 1, 'C');
        $pdf->Cell(0, 3.9, 'Kalomo District, Southern Province', 0, 1, 'C');
        $pdf->Cell(0, 3.9, 'Tel: 0965992967 | 0770666937', 0, 1, 'C');

        $pdf->Ln(2.2);
        $pdf->SetLineWidth(0.3);
        $pdf->SetDrawColor(128, 128, 128);
        $pdf->Line(self::MARGIN, $pdf->GetY(), self::PAGE_W - self::MARGIN, $pdf->GetY());
        $pdf->Ln(3.3);
    }

    /**
     * Draw the body of the acceptance letter.
     */
    protected function drawBody(TCPDF $pdf, AcceptanceLetter $letter): void
    {
        $yStart = $pdf->GetY();

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 6.2, 'LETTER OF ACCEPTANCE', 0, 1, 'C');
        $pdf->Ln(1.1);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 4.7, 'Date: ' . $letter->issued_date->format('d/m/Y'), 0, 1, 'R');
        $pdf->Ln(1.1);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 4.7, 'RE: OFFER OF ADMISSION', 0, 1, 'L');
        $pdf->Ln(1.1);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 4.7, 'Dear ' . $letter->student_name . ',', 0, 1, 'L');
        $pdf->Ln(1.1);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->MultiCell(0, 4.7, 'We are pleased to inform you that your application to study at EDUTRACK COMPUTER TRAINING COLLEGE has been SUCCESSFULLY ACCEPTED.', 0, 'L');
        $pdf->Ln(1.1);

        $pdf->Cell(0, 4.7, 'You have been offered admission into the following programme:', 0, 1, 'L');
        $pdf->Ln(0.6);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 4.7, 'Course: ' . $letter->course_title, 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $isPhysical = in_array($letter->mode, ['Physical', 'Hybrid'], true);
        $isOnline = $letter->mode === 'Online';
        $pdf->Cell(0, 4.7, 'Mode of Study: [' . ($isPhysical ? 'X' : ' ') . '] Physical    [' . ($isOnline ? 'X' : ' ') . '] Online', 0, 1, 'L');
        $pdf->Cell(0, 4.7, 'Duration: ' . $letter->duration, 0, 1, 'L');
        $pdf->Cell(0, 4.7, 'Commencement Date: ' . ($letter->commencement_date?->format('d/m/Y') ?: '______________'), 0, 1, 'L');
        $pdf->Ln(1.7);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 5.5, 'FEES INFORMATION', 0, 1, 'L');
        $pdf->Ln(0.6);

        $this->drawFeeTable($pdf, 'DAY SCHOOL', $letter->fee_snapshot['day'] ?? [], false);
        $pdf->Ln(2.2);
        $this->drawFeeTable($pdf, 'BOARDING', $letter->fee_snapshot['boarding'] ?? [], true);
        $pdf->Ln(2.8);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 4.7, 'Payment can be made in cash or through:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 4.7, 'Bank: ACCESS BANK', 0, 1, 'L');
        $pdf->Cell(0, 4.7, 'Account Name: EDUTRACK COMPUTER TRAINING SCHOOL', 0, 1, 'L');
        $pdf->Cell(0, 4.7, 'Account Number: 0106509665016', 0, 1, 'L');
        $pdf->Ln(3.3);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 4.7, 'Yours faithfully,', 0, 1, 'L');
        $pdf->Ln(4.4);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 4.7, 'Admissions Officer', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 4.7, 'EDUTRACK COMPUTER TRAINING COLLEGE', 0, 1, 'L');
        $pdf->Ln(1.1);
        $pdf->Cell(0, 4.7, 'NAME: ..............................    SIGN: ..............................', 0, 1, 'L');
        $pdf->Ln(3.3);

        $this->drawConditionsBox($pdf);
    }

    /**
     * Draw a fee table for day school or boarding.
     */
    protected function drawFeeTable(TCPDF $pdf, string $title, array $fees, bool $isBoarding): void
    {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 4.7, $title, 0, 1, 'L');

        if (empty($fees)) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 4.7, 'No fee information available.', 0, 1, 'L');
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
        $pdf->Cell($colWidth, 5.4, 'Item', 1, 0, 'L', true);
        $pdf->Cell($colWidth, 5.4, 'Amount (ZMW)', 1, 1, 'R', true);

        $pdf->SetFont('helvetica', '', 10);
        foreach ($lineItems as $item => $amount) {
            $pdf->Cell($colWidth, 5.4, $item, 1, 0, 'L');
            $pdf->Cell($colWidth, 5.4, number_format((float) $amount, 2), 1, 1, 'R');
        }

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($colWidth, 5.4, 'Total', 1, 0, 'L', true);
        $pdf->Cell($colWidth, 5.4, number_format((float) ($fees['Total'] ?? 0), 2), 1, 1, 'R', true);
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
        $pdf->Cell($itemWidth, 5.4, 'Item', 1, 0, 'L', true);
        foreach ($terms as $i => $term) {
            $label = $term['term'] ?? ('Term ' . ($i + 1));
            $pdf->Cell($termWidth, 5.4, $label, 1, 0, 'C', true);
        }
        $pdf->Cell($itemWidth, 5.4, 'Total (ZMW)', 1, 1, 'R', true);

        $lineItemKeys = $this->collectLineItemKeys($terms);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($lineItemKeys as $key) {
            if ($key === 'Total') {
                continue;
            }
            $pdf->Cell($itemWidth, 5.4, $key, 1, 0, 'L');
            $rowTotal = 0;
            foreach ($terms as $term) {
                $amount = $term[$key] ?? 0;
                $rowTotal += (float) $amount;
                $pdf->Cell($termWidth, 5.4, $amount ? number_format((float) $amount, 2) : '—', 1, 0, 'R');
            }
            $pdf->Cell($itemWidth, 5.4, number_format($rowTotal, 2), 1, 1, 'R');
        }

        // Total row
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell($itemWidth, 5.4, 'Total', 1, 0, 'L', true);
        $grandTotal = 0;
        foreach ($terms as $term) {
            $termTotal = $term['Total'] ?? array_sum(array_diff_key($term, ['term' => true]));
            $grandTotal += (float) $termTotal;
            $pdf->Cell($termWidth, 5.4, number_format((float) $termTotal, 2), 1, 0, 'R', true);
        }
        $pdf->Cell($itemWidth, 5.4, number_format($grandTotal, 2), 1, 1, 'R', true);
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
        $pdf->Cell(0, 5.5, 'ADMISSION CONDITIONS', 0, 1, 'L');

        $conditions = [
            '1. Pay the required fees before or on the reporting date.',
            '2. Submit copies of your NRC/Passport and academic certificates.',
            '3. Comply with all rules and regulations of the institution.',
            '4. Physical-class students report to our campus in Kalomo District, Southern Province. Online students receive platform access on confirmation of payment.',
        ];

        $boxY = $pdf->GetY();
        $pdf->SetFont('helvetica', '', 9.5);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);

        foreach ($conditions as $condition) {
            $pdf->MultiCell(self::PAGE_W - self::MARGIN * 2 - 4, 4.9, $condition, 0, 'L');
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
