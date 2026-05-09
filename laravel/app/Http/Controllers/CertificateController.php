<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use TCPDF;

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
        $pdf = new TCPDF('L', 'mm', 'A4');
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack Computer Training College');
        $pdf->SetTitle('Certificate - ' . $certificate->certificate_number);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        // Load certificate template view
        $html = view('certificates.pdf', compact('certificate'))->render();

        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output('', 'S');
    }
}
