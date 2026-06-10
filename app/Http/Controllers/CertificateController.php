<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = auth()->user()->certificates()->with('course')->latest()->get();

        return view('certificates.index', compact('certificates'));
    }

    /**
     * Preview a certificate rendered with Tailwind CSS in the browser.
     * Uses real certificate data if ID provided, otherwise shows a demo.
     */
    public function preview(?Certificate $certificate = null)
    {
        $service = new CertificateService();

        // Public preview only shows demo data — never real certificates
        if ($certificate && auth()->check()) {
            // Authenticated users can preview their own certificates
            $user = auth()->user();
            $isOwner = $certificate->user_id === $user->id;
            $isStaff = $user->roles()->whereIn('role_id', [1, 2, 3, 6])->exists();

            if ($isOwner || $isStaff) {
                $data = $service->getCertificateData($certificate);
                return view('certificates.preview', $data);
            }
        }

        // Demo data for public / unauthorized access
        $data = [
            'student_name' => 'Catherine Namakanda',
            'course_title' => 'General Basic Computing',
            'classification' => 'Merit',
            'graduation_day' => '27',
            'graduation_suffix' => 'th',
            'graduation_month' => 'March',
            'graduation_year' => '2026',
            'student_number' => '26Edu249580',
            'certificate_number' => 'NRC 2495807/1/1',
            'verification_code' => 'EDU-ABC123XYZ',
            'verify_url' => route('certificates.verify', 'EDU-ABC123XYZ'),
            'national_id' => 'NRC 249580/11/3',
            'final_score' => 87,
        ];

        return view('certificates.preview', $data);
    }

    public function verify(string $code)
    {
        $certificate = Certificate::with(['user', 'course'])
            ->where('verification_code', $code)
            ->orWhere('certificate_number', $code)
            ->firstOrFail();

        return view('certificates.verify', compact('certificate'));
    }

    public function download(Certificate $certificate)
    {
        // Verify ownership — only the student or super-admin/finance can download
        $user = auth()->user();
        $isOwner = $certificate->user_id === $user->id;
        $isAdmin = $user->roles()->whereIn('role_id', [1, 2])->exists(); // Super Admin, Admin only

        if (!$isOwner && !$isAdmin) {
            abort(403, 'You do not have permission to download this certificate.');
        }

        // Check if certificate is blocked
        if ($certificate->enrollment && $certificate->enrollment->certificate_blocked) {
            abort(403, 'Certificate is blocked until full payment is received.');
        }

        $pdf = $this->generatePdf($certificate);

        $filename = 'certificate-' . preg_replace('/[^A-Za-z0-9\-]+/', '-', $certificate->certificate_number) . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function generatePdf(Certificate $certificate): string
    {
        $service = new CertificateService();
        return $service->generatePdf($certificate);
    }
}
