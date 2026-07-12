<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\PaymentVerificationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_revenue' => Payment::where('payment_status', 'Completed')->sum('amount'),
            'pending_payments' => Payment::where('payment_status', 'Pending')->count(),
            'today_revenue' => Payment::where('payment_status', 'Completed')
                ->whereDate('payment_date', today())
                ->sum('amount'),
            'month_revenue' => Payment::where('payment_status', 'Completed')
                ->whereMonth('payment_date', now()->month)
                ->sum('amount'),
        ];

        $recentPayments = Payment::with(['student', 'course'])->latest()->take(20)->get();

        // Monthly revenue chart data (last 6 months)
        $monthlyRevenue = Payment::where('payment_status', 'Completed')
            ->whereDate('payment_date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $chartLabels = [];
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $label = now()->subMonths($i)->format('M Y');
            $chartLabels[] = $label;
            $chartData[] = $monthlyRevenue[$month] ?? 0;
        }

        return view('finance.dashboard', compact('stats', 'recentPayments', 'chartLabels', 'chartData'));
    }

    public function transactions()
    {
        $payments = Payment::with(['student', 'course'])->latest()->paginate(25)->withQueryString();

        return view('finance.transactions', compact('payments'));
    }

    public function payments(Request $request)
    {
        $query = Payment::with(['student', 'course']);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $payments = $query->latest()->paginate(25)->withQueryString();

        return view('finance.payments', compact('payments'));
    }

    public function verifyForm(Payment $payment)
    {
        $payment->load(['student', 'course', 'enrollment.course', 'enrollment.intake']);

        $enrollment = $payment->enrollment;
        $enrollmentSummary = null;
        if ($enrollment) {
            $totalFee = $enrollment->effectivePrice();
            $discounts = $enrollment->totalDiscounts();
            $effectivePaid = (float) $enrollment->amount_paid + (float) $discounts;
            $enrollmentSummary = [
                'course_title' => $enrollment->course?->title ?? 'Unknown course',
                'intake_name' => $enrollment->intake?->name,
                'total_fee' => $totalFee,
                'amount_paid' => (float) $enrollment->amount_paid,
                'discounts' => $discounts,
                'effective_paid' => $effectivePaid,
                'outstanding' => max(0, $totalFee - $effectivePaid),
                'payment_status' => $enrollment->payment_status,
                'enrollment_status' => $enrollment->enrollment_status,
                'certificate_blocked' => $enrollment->certificate_blocked,
                'is_fully_paid' => $enrollment->isFullyPaid(),
            ];
        }

        return view('finance.verify', compact('payment', 'enrollmentSummary'));
    }

    public function verify(Payment $payment)
    {
        if ($payment->payment_status === 'Completed') {
            return redirect()->route('finance.payments')->with('info', 'Payment is already verified.');
        }

        $service = app(PaymentVerificationService::class);
        $service->verifyPayment($payment);

        return redirect()->route('finance.payments')->with('success', 'Payment verified successfully. Enrollment updated.');
    }

    public function invoices()
    {
        $invoices = Invoice::with(['student', 'course'])->latest()->paginate(25);
        return view('finance.invoices', compact('invoices'));
    }

    public function downloadInvoice(Invoice $invoice)
    {
        $service = app(InvoiceService::class);
        $pdf = $service->generatePdf($invoice);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice-' . $invoice->invoice_number . '.pdf"',
        ]);
    }
}
