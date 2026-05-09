<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Payment;

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

        return view('finance.dashboard', compact('stats', 'recentPayments'));
    }

    public function transactions()
    {
        $payments = Payment::with(['student', 'course'])->latest()->paginate(25);

        return view('finance.transactions', compact('payments'));
    }

    public function invoices()
    {
        return view('finance.invoices');
    }
}
