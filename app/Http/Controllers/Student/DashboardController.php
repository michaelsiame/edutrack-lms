<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $enrollments = auth()->user()->enrollments()
            ->with('course')
            ->latest()
            ->take(5)
            ->get();

        $certificates = auth()->user()->certificates()
            ->with('course')
            ->latest()
            ->take(5)
            ->get();

        return view('student.dashboard', compact('enrollments', 'certificates'));
    }

    public function progress()
    {
        $enrollments = auth()->user()->enrollments()
            ->with('course')
            ->latest()
            ->get();

        $totalCourses = $enrollments->count();
        $completedCourses = $enrollments->where('enrollment_status', 'Completed')->count();
        $inProgressCourses = $enrollments->where('enrollment_status', 'In Progress')->count();
        $totalCertificates = auth()->user()->certificates()->count();

        return view('student.progress', compact('enrollments', 'totalCourses', 'completedCourses', 'inProgressCourses', 'totalCertificates'));
    }

    public function payments()
    {
        $payments = auth()->user()->payments()
            ->with('course')
            ->latest()
            ->paginate(10);

        $totalPaid = auth()->user()->payments()
            ->where('payment_status', 'Completed')
            ->sum('amount');

        $totalPending = auth()->user()->payments()
            ->where('payment_status', 'Pending')
            ->sum('amount');

        $activeEnrollments = auth()->user()->enrollments()
            ->whereIn('enrollment_status', ['Enrolled', 'In Progress'])
            ->count();

        return view('student.payments', compact('payments', 'totalPaid', 'totalPending', 'activeEnrollments'));
    }

    public function certificates()
    {
        $certificates = auth()->user()->certificates()
            ->with('course')
            ->latest()
            ->paginate(10);

        return view('student.certificates', compact('certificates'));
    }

    public function downloadReceipt(Request $request, Payment $payment)
    {
        // Ensure the payment belongs to the current user
        if ($payment->student_id !== auth()->user()->student?->id && $payment->student_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $service = new InvoiceService();

        // Find or generate invoice for this payment
        $invoice = $payment->invoice;
        if (!$invoice) {
            $invoice = $service->generateInvoice($payment);
        }

        $pdf = $service->generatePdf($invoice);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receipt-' . $invoice->invoice_number . '.pdf"',
        ]);
    }
}
