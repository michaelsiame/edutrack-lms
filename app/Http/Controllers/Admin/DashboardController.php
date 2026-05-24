<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_courses' => Course::count(),
            'total_enrollments' => Enrollment::count(),
            'total_revenue' => Payment::where('payment_status', 'Completed')->sum('amount'),
            'pending_payments' => Payment::where('payment_status', 'Pending')->count(),
            'recent_enrollments' => Enrollment::with(['user', 'course'])->latest()->take(10)->get(),
            'recent_payments' => Payment::with(['student', 'course'])->latest()->take(10)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function reports()
    {
        return view('admin.reports');
    }

    public function settings()
    {
        $settings = [
            'app_name' => Setting::get('app_name', 'Edutrack LMS'),
            'app_email' => Setting::get('app_email', 'edutrackzambia@gmail.com'),
            'app_phone' => Setting::get('app_phone', '+260 770 666 937'),
            'app_address' => Setting::get('app_address', 'Kalomo, Zambia'),
            'currency' => Setting::get('currency', 'ZMW'),
            'min_deposit_percent' => Setting::get('min_deposit_percent', 30),
            'certificate_enabled' => Setting::get('certificate_enabled', true),
            'registration_fee' => Setting::get('registration_fee', 150),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_email' => 'required|email',
            'app_phone' => 'nullable|string|max:50',
            'app_address' => 'nullable|string',
            'currency' => 'required|string|max:10',
            'min_deposit_percent' => 'required|integer|min:0|max:100',
            'certificate_enabled' => 'boolean',
            'registration_fee' => 'required|numeric|min:0',
        ]);

        Setting::set('app_name', $validated['app_name'], 'general', 'string');
        Setting::set('app_email', $validated['app_email'], 'general', 'string');
        Setting::set('app_phone', $validated['app_phone'] ?? '', 'general', 'string');
        Setting::set('app_address', $validated['app_address'] ?? '', 'general', 'string');
        Setting::set('currency', $validated['currency'], 'general', 'string');
        Setting::set('min_deposit_percent', $validated['min_deposit_percent'], 'payment', 'integer');
        Setting::set('certificate_enabled', $request->boolean('certificate_enabled'), 'certificate', 'boolean');
        Setting::set('registration_fee', $validated['registration_fee'], 'payment', 'float');

        // Sync to legacy SystemSetting so public pages (footer, contact, campus) stay in sync
        SystemSetting::set('site_name', $validated['app_name'], 'string');
        SystemSetting::set('site_email', $validated['app_email'], 'string');
        SystemSetting::set('site_phone', $validated['app_phone'] ?? '', 'string');
        SystemSetting::set('site_address', $validated['app_address'] ?? '', 'string');
        SystemSetting::set('default_currency', $validated['currency'], 'string');
        SystemSetting::set('currency', $validated['currency'], 'string');
        SystemSetting::set('enrollment_min_deposit_percent', $validated['min_deposit_percent'], 'number');
        SystemSetting::set('registration_fee_amount', $validated['registration_fee'], 'number');

        return redirect()->route('admin.settings')->with('success', 'Settings updated successfully.');
    }

    public function exportReport(Request $request, string $type)
    {
        $from = $request->date('from');
        $to = $request->date('to');

        return match ($type) {
            'enrollments' => $this->exportEnrollments($from, $to),
            'payments' => $this->exportPayments($from, $to),
            'courses' => $this->exportCourses($from, $to),
            default => abort(404),
        };
    }

    private function exportEnrollments(?\Carbon\Carbon $from, ?\Carbon\Carbon $to)
    {
        $query = Enrollment::with(['user', 'course']);
        if ($from) $query->whereDate('enrolled_at', '>=', $from);
        if ($to) $query->whereDate('enrolled_at', '<=', $to);

        $enrollments = $query->get();

        $headers = ['Student Name', 'Email', 'Course', 'Status', 'Progress', 'Amount Paid', 'Enrolled At'];
        $rows = $enrollments->map(fn($e) => [
            $e->user?->full_name ?? 'N/A',
            $e->user?->email ?? '',
            $e->course?->title ?? 'N/A',
            $e->enrollment_status,
            $e->progress . '%',
            $e->amount_paid,
            $e->enrolled_at?->format('Y-m-d H:i') ?? '',
        ]);

        return $this->streamCsv('enrollments-' . now()->format('Y-m-d') . '.csv', $headers, $rows);
    }

    private function exportPayments(?\Carbon\Carbon $from, ?\Carbon\Carbon $to)
    {
        $query = Payment::with(['student', 'course']);
        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);

        $payments = $query->get();

        $headers = ['Student Name', 'Email', 'Course', 'Amount', 'Method', 'Status', 'Reference', 'Date'];
        $rows = $payments->map(fn($p) => [
            $p->student?->full_name ?? 'N/A',
            $p->student?->email ?? '',
            $p->course?->title ?? 'N/A',
            $p->amount,
            $p->payment_method,
            $p->payment_status,
            $p->transaction_reference ?? '',
            $p->created_at?->format('Y-m-d H:i') ?? '',
        ]);

        return $this->streamCsv('payments-' . now()->format('Y-m-d') . '.csv', $headers, $rows);
    }

    private function exportCourses(?\Carbon\Carbon $from, ?\Carbon\Carbon $to)
    {
        $courses = Course::withCount('enrollments')->get();

        $headers = ['Course', 'Category', 'Price', 'Enrollments', 'Status', 'Created At'];
        $rows = $courses->map(fn($c) => [
            $c->title,
            $c->category?->name ?? 'N/A',
            $c->discount_price ?? $c->price,
            $c->enrollments_count,
            $c->status,
            $c->created_at?->format('Y-m-d') ?? '',
        ]);

        return $this->streamCsv('courses-' . now()->format('Y-m-d') . '.csv', $headers, $rows);
    }

    private function streamCsv(string $filename, array $headers, \Illuminate\Support\Collection $rows)
    {
        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
