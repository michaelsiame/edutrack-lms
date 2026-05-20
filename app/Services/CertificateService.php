<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Support\Str;
use TCPDF;

class CertificateService
{
    /**
     * Generate a unique certificate number.
     */
    public function generateCertificateNumber(): string
    {
        $prefix = 'EDT';
        $year = date('Y');
        $random = Str::upper(Str::random(6));

        return "{$prefix}-{$year}-{$random}";
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
     */
    public function issueCertificate(Enrollment $enrollment): Certificate
    {
        $certificate = Certificate::create([
            'user_id' => $enrollment->user_id,
            'course_id' => $enrollment->course_id,
            'enrollment_id' => $enrollment->id,
            'certificate_number' => $this->generateCertificateNumber(),
            'issued_date' => now(),
            'verification_code' => $this->generateVerificationCode(),
            'final_score' => $enrollment->final_grade ?? 0,
            'issued_at' => now(),
            'is_verified' => true,
        ]);

        $enrollment->update(['certificate_issued' => true]);

        return $certificate;
    }

    /**
     * Generate PDF for a certificate.
     */
    public function generatePdf(Certificate $certificate): string
    {
        $pdf = new TCPDF('L', 'mm', 'A4');
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack Computer Training College');
        $pdf->SetTitle('Certificate - ' . $certificate->certificate_number);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        $data = $this->getCertificateData($certificate);
        $html = view('certificates.pdf', $data)->render();

        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output('', 'S');
    }

    /**
     * Get certificate data for PDF generation.
     */
    protected function getCertificateData(Certificate $certificate): array
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
            'graduation_month' => $graduationDate->format('F'),
            'graduation_year' => $graduationDate->format('Y'),
            'student_number' => 'EDU-' . str_pad($certificate->user_id, 6, '0', STR_PAD_LEFT),
        ];
    }
}
