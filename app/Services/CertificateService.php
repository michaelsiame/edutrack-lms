<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

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

        // Append course ID and random suffix, retry on collision
        $attempts = 0;
        do {
            $uniqueSuffix = ($courseId ?? '') . '-' . Str::random(8);
            $number = 'NRC ' . $nrcSuffix . '/' . $uniqueSuffix;
            $attempts++;
        } while (\App\Models\Certificate::where('certificate_number', $number)->exists() && $attempts < 10);

        return $number;
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

            // Extra guard: check if certificate already exists (handles edge cases)
            $existingCertificate = Certificate::where('enrollment_id', $lockedEnrollment->id)->first();
            if ($existingCertificate) {
                $lockedEnrollment->update(['certificate_issued' => true]);
                return $existingCertificate;
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
        $data = $this->getCertificateData($certificate);

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font_size' => 10,
            'default_font' => 'DejaVuSerif',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'title' => 'Certificate - ' . $certificate->certificate_number,
            'author' => 'Edutrack Computer Training College',
            'creator' => 'Edutrack LMS',
            'fontDir' => array_merge($fontDirs, [
                public_path('assets/fonts/cert/'),
            ]),
            'fontdata' => $fontData + [
                'greatvibes' => [
                    'R' => 'GreatVibes-Regular.ttf',
                ],
            ],
        ]);

        $mpdf->SetTitle('Certificate - ' . $certificate->certificate_number);
        $mpdf->SetAuthor('Edutrack Computer Training College');
        $mpdf->SetCreator('Edutrack LMS');

        $html = view('certificates.pdf', $data)->render();
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
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
