<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $certificates = auth()->user()->certificates()
            ->with('course')
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $certificates,
        ]);
    }

    public function verify(string $code)
    {
        $certificate = Certificate::with(['user', 'course'])
            ->where('verification_code', $code)
            ->orWhere('certificate_number', $code)
            ->first();

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'certificate_number' => $certificate->certificate_number,
                'student_name' => $certificate->user?->full_name,
                'course_title' => $certificate->course?->title,
                'issued_date' => $certificate->issued_date?->format('F d, Y'),
                'is_verified' => $certificate->is_verified,
            ],
        ]);
    }
}
