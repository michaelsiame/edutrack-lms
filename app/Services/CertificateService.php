<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
     * Generate PDF for a certificate using Puppeteer (headless Chromium).
     * Produces pixel-perfect output matching the browser print preview.
     */
    public function generatePdf(Certificate $certificate): string
    {
        $data = $this->getCertificateData($certificate);
        $html = view('certificates.pdf-browser', $data)->render();

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempHtml = tempnam($tempDir, 'cert_html_') . '.html';
        $tempPdf = tempnam($tempDir, 'cert_pdf_') . '.pdf';

        file_put_contents($tempHtml, $html);

        $scriptPath = base_path('scripts/generate-pdf.cjs');
        $cmd = 'node ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($tempHtml) . ' ' . escapeshellarg($tempPdf) . ' 2>&1';
        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($tempPdf) || filesize($tempPdf) === 0) {
            @unlink($tempHtml);
            throw new \RuntimeException('PDF generation failed via Puppeteer: ' . implode("\n", $output));
        }

        $pdfContent = file_get_contents($tempPdf);

        @unlink($tempHtml);
        @unlink($tempPdf);

        return $pdfContent;
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
