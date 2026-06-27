<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CdfDisbursement;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * CDF funding report: lists CDF-funded enrollments grouped by constituency.
     */
    public function cdfReport(Request $request)
    {
        $query = Enrollment::with(['user', 'course'])
            ->where('funding_source', 'cdf')
            ->when($request->filled('constituency'), function ($q) use ($request) {
                $q->where('cdf_constituency', 'like', '%' . $request->constituency . '%');
            })
            ->when($request->filled('course'), function ($q) use ($request) {
                $q->where('course_id', $request->course);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('enrollment_status', $request->status);
            });

        $enrollments = $query->orderBy('cdf_constituency')->orderBy('enrolled_at', 'desc')->get();

        $groups = $enrollments->groupBy(fn($e) => $e->cdf_constituency ?: 'Unspecified');
        $courses = Course::published()->orderBy('title')->get();

        $reconciliation = $this->buildCdfReconciliation($groups);
        $reconciliationTotals = [
            'students' => $reconciliation->sum('students'),
            'expected' => $reconciliation->sum('expected'),
            'received' => $reconciliation->sum('received'),
            'outstanding' => $reconciliation->sum('outstanding'),
        ];

        return view('admin.reports.cdf', compact('groups', 'courses', 'reconciliation', 'reconciliationTotals'));
    }

    /**
     * Export the CDF funding report to CSV.
     */
    public function exportCdfReport(Request $request)
    {
        $query = Enrollment::with(['user', 'course'])
            ->where('funding_source', 'cdf')
            ->when($request->filled('constituency'), function ($q) use ($request) {
                $q->where('cdf_constituency', 'like', '%' . $request->constituency . '%');
            })
            ->when($request->filled('course'), function ($q) use ($request) {
                $q->where('course_id', $request->course);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('enrollment_status', $request->status);
            });

        $enrollments = $query->orderBy('cdf_constituency')->orderBy('enrolled_at', 'desc')->get();

        $groups = $enrollments->groupBy(fn($e) => $e->cdf_constituency ?: 'Unspecified');
        $reconciliation = $this->buildCdfReconciliation($groups);

        $headers = ['Constituency', 'Student Name', 'Email', 'Course', 'Amount Paid', 'Status', 'Sponsor Reference', 'Enrolled At', 'Constituency Expected', 'Constituency Received', 'Constituency Outstanding'];
        $rows = $enrollments->map(function ($e) use ($reconciliation) {
            $constituency = $e->cdf_constituency ?: 'Unspecified';
            $summary = $reconciliation->get($constituency);

            return [
                $constituency,
                $e->user?->full_name ?? 'N/A',
                $e->user?->email ?? '',
                $e->course?->title ?? 'N/A',
                $e->amount_paid,
                $e->enrollment_status,
                $e->sponsor_reference ?? '',
                $e->enrolled_at?->format('Y-m-d') ?? '',
                $summary ? $summary['expected'] : 0,
                $summary ? $summary['received'] : 0,
                $summary ? $summary['outstanding'] : 0,
            ];
        });

        return $this->streamCsv('cdf-enrollments-' . now()->format('Y-m-d') . '.csv', $headers, $rows);
    }

    /**
     * Build per-constituency reconciliation summary.
     *
     * @param  \Illuminate\Support\Collection  $groups
     * @return \Illuminate\Support\Collection
     */
    private function buildCdfReconciliation($groups)
    {
        $constituencies = $groups->keys()->filter()->all();

        $receivedByConstituency = $constituencies
            ? CdfDisbursement::select('constituency', DB::raw('SUM(amount) as total'))
                ->whereIn('constituency', $constituencies)
                ->groupBy('constituency')
                ->pluck('total', 'constituency')
                ->map(fn($amount) => (float) $amount)
            : collect();

        return $groups->map(function ($enrollments, $constituency) use ($receivedByConstituency) {
            $expected = $enrollments->sum(fn($e) => (float) ($e->course?->price ?? 0));
            $received = $receivedByConstituency->get($constituency, 0);

            return [
                'constituency' => $constituency,
                'students' => $enrollments->count(),
                'expected' => $expected,
                'received' => $received,
                'outstanding' => $expected - $received,
            ];
        });
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

            // Social
            'social_facebook' => Setting::get('social_facebook', ''),
            'social_twitter' => Setting::get('social_twitter', ''),
            'social_linkedin' => Setting::get('social_linkedin', ''),
            'social_instagram' => Setting::get('social_instagram', ''),
            'social_youtube' => Setting::get('social_youtube', ''),
            'social_whatsapp' => Setting::get('social_whatsapp', ''),

            // SEO / Content
            'meta_description' => Setting::get('meta_description', ''),
            'meta_keywords' => Setting::get('meta_keywords', ''),
            'footer_about' => Setting::get('footer_about', ''),
            'logo_url' => Setting::get('logo_url', ''),

            // Homepage
            'hero_title' => Setting::get('hero_title', ''),
            'hero_subtitle' => Setting::get('hero_subtitle', ''),
            'next_intake_date' => Setting::get('next_intake_date', ''),
            'next_intake_label' => Setting::get('next_intake_label', ''),
            'opening_hours' => Setting::get('opening_hours', 'Monday - Friday: 8:00 AM - 5:00 PM'),

            // Bank
            'bank_name' => Setting::get('bank_name', ''),
            'bank_account_name' => Setting::get('bank_account_name', ''),
            'bank_account_number' => Setting::get('bank_account_number', ''),

            // Toggles
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'enable_email_notifications' => Setting::get('enable_email_notifications', true),
            'google_login_enabled' => Setting::get('google_login_enabled', true),
            'lenco_enabled' => Setting::get('lenco_enabled', true),
            'registration_fee_required' => Setting::get('registration_fee_required', true),
            'teveta_registration_number' => Setting::get('teveta_registration_number', ''),
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
            'logo_url' => 'nullable|url|max:500',
            'currency' => 'required|string|max:10',
            'min_deposit_percent' => 'required|integer|min:0|max:100',
            'certificate_enabled' => 'boolean',
            'registration_fee' => 'required|numeric|min:0',
            'social_facebook' => 'nullable|url|max:500',
            'social_twitter' => 'nullable|url|max:500',
            'social_linkedin' => 'nullable|url|max:500',
            'social_instagram' => 'nullable|url|max:500',
            'social_youtube' => 'nullable|url|max:500',
            'social_whatsapp' => 'nullable|url|max:500',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'footer_about' => 'nullable|string|max:1000',
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:255',
            'next_intake_date' => 'nullable|date',
            'next_intake_label' => 'nullable|string|max:255',
            'opening_hours' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'maintenance_mode' => 'boolean',
            'enable_email_notifications' => 'boolean',
            'google_login_enabled' => 'boolean',
            'lenco_enabled' => 'boolean',
            'registration_fee_required' => 'boolean',
            'teveta_registration_number' => 'nullable|string|max:100',
        ]);

        // Helper to save setting + sync to legacy
        $save = function (string $key, $value, string $group = 'general', string $type = 'string') {
            Setting::set($key, $value, $group, $type);
        };

        $save('app_name', $validated['app_name']);
        $save('app_email', $validated['app_email']);
        $save('app_phone', $validated['app_phone'] ?? '', 'general', 'string');
        $save('app_address', $validated['app_address'] ?? '', 'general', 'string');
        $save('logo_url', $validated['logo_url'] ?? '', 'general', 'string');
        $save('currency', $validated['currency']);
        $save('min_deposit_percent', $validated['min_deposit_percent'], 'payment', 'integer');
        $save('certificate_enabled', $request->boolean('certificate_enabled'), 'certificate', 'boolean');
        $save('registration_fee', $validated['registration_fee'], 'payment', 'float');

        // Social
        $save('social_facebook', $validated['social_facebook'] ?? '', 'social', 'string');
        $save('social_twitter', $validated['social_twitter'] ?? '', 'social', 'string');
        $save('social_linkedin', $validated['social_linkedin'] ?? '', 'social', 'string');
        $save('social_instagram', $validated['social_instagram'] ?? '', 'social', 'string');
        $save('social_youtube', $validated['social_youtube'] ?? '', 'social', 'string');
        $save('social_whatsapp', $validated['social_whatsapp'] ?? '', 'social', 'string');

        // SEO / Content
        $save('meta_description', $validated['meta_description'] ?? '', 'seo', 'string');
        $save('meta_keywords', $validated['meta_keywords'] ?? '', 'seo', 'string');
        $save('footer_about', $validated['footer_about'] ?? '', 'content', 'string');

        // Homepage
        $save('hero_title', $validated['hero_title'] ?? '', 'homepage', 'string');
        $save('hero_subtitle', $validated['hero_subtitle'] ?? '', 'homepage', 'string');
        $save('next_intake_date', $validated['next_intake_date'] ?? '', 'homepage', 'string');
        $save('next_intake_label', $validated['next_intake_label'] ?? '', 'homepage', 'string');
        $save('opening_hours', $validated['opening_hours'] ?? 'Monday - Friday: 8:00 AM - 5:00 PM', 'homepage', 'string');

        // Bank
        $save('bank_name', $validated['bank_name'] ?? '', 'payment', 'string');
        $save('bank_account_name', $validated['bank_account_name'] ?? '', 'payment', 'string');
        $save('bank_account_number', $validated['bank_account_number'] ?? '', 'payment', 'string');

        // Toggles
        $save('maintenance_mode', $request->boolean('maintenance_mode'), 'system', 'boolean');
        $save('enable_email_notifications', $request->boolean('enable_email_notifications'), 'system', 'boolean');
        $save('google_login_enabled', $request->boolean('google_login_enabled'), 'system', 'boolean');
        $save('lenco_enabled', $request->boolean('lenco_enabled'), 'system', 'boolean');
        $save('registration_fee_required', $request->boolean('registration_fee_required'), 'payment', 'boolean');
        $save('teveta_registration_number', $validated['teveta_registration_number'] ?? '', 'certificate', 'string');

        // Sync to legacy SystemSetting so public pages stay in sync
        SystemSetting::set('site_name', $validated['app_name'], 'string');
        SystemSetting::set('site_email', $validated['app_email'], 'string');
        SystemSetting::set('site_phone', $validated['app_phone'] ?? '', 'string');
        SystemSetting::set('site_address', $validated['app_address'] ?? '', 'string');
        SystemSetting::set('default_currency', $validated['currency'], 'string');
        SystemSetting::set('currency', $validated['currency'], 'string');
        SystemSetting::set('enrollment_min_deposit_percent', $validated['min_deposit_percent'], 'number');
        SystemSetting::set('registration_fee_amount', $validated['registration_fee'], 'number');
        SystemSetting::set('next_intake_date', $validated['next_intake_date'] ?? '', 'string');
        SystemSetting::set('next_intake_label', $validated['next_intake_label'] ?? '', 'string');
        SystemSetting::set('bank_name', $validated['bank_name'] ?? '', 'string');
        SystemSetting::set('bank_account_name', $validated['bank_account_name'] ?? '', 'string');
        SystemSetting::set('bank_account_number', $validated['bank_account_number'] ?? '', 'string');
        SystemSetting::set('maintenance_mode', $request->boolean('maintenance_mode') ? '1' : '0', 'boolean');
        SystemSetting::set('enable_email_notifications', $request->boolean('enable_email_notifications') ? '1' : '0', 'boolean');
        SystemSetting::set('google_login_enabled', $request->boolean('google_login_enabled') ? '1' : '0', 'boolean');
        SystemSetting::set('lenco_enabled', $request->boolean('lenco_enabled') ? '1' : '0', 'boolean');
        SystemSetting::set('registration_fee_required', $request->boolean('registration_fee_required') ? '1' : '0', 'boolean');
        SystemSetting::set('teveta_registration_number', $validated['teveta_registration_number'] ?? '', 'string');

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
            $p->transaction_id ?? '',
            $p->created_at?->format('Y-m-d H:i') ?? '',
        ]);

        return $this->streamCsv('payments-' . now()->format('Y-m-d') . '.csv', $headers, $rows);
    }

    private function exportCourses(?\Carbon\Carbon $from, ?\Carbon\Carbon $to)
    {
        $query = Course::withCount('enrollments');
        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);
        $courses = $query->get();

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
