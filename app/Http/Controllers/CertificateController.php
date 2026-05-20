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
        // Verify ownership
        if ($certificate->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        // Check if certificate is blocked
        if ($certificate->enrollment && $certificate->enrollment->certificate_blocked) {
            abort(403, 'Certificate is blocked until full payment is received.');
        }

        $pdf = $this->generatePdf($certificate);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="certificate-' . $certificate->certificate_number . '.pdf"',
        ]);
    }

    protected function generatePdf(Certificate $certificate): string
    {
        $service = new CertificateService();
        return $service->generatePdf($certificate);
    }
}
