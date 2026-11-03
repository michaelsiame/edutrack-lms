<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\SystemSetting;
use App\Services\CertificateService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = Certificate::with(['user', 'course']);

        if ($request->filled('course')) {
            $query->where('course_id', $request->course);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                  ->orWhere('verification_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $certificates = $query->latest('issued_at')->paginate(20)->withQueryString();
        $courses = Course::orderBy('title')->get(['id', 'title']);

        // The common issue date, shown only while it still applies (today or later).
        $common = SystemSetting::get(CertificateService::COMMON_ISSUE_DATE);
        $commonIssueDate = $common && Carbon::parse($common)->gte(today()) ? Carbon::parse($common) : null;

        return view('admin.certificates.index', compact('certificates', 'courses', 'commonIssueDate'));
    }

    /**
     * Set one issue date for the certificates issued from now on, e.g. the day
     * a class's certificates come out. It cannot be in the past; clearing it
     * dates each certificate the day it is issued.
     */
    public function setIssueDate(Request $request)
    {
        if ($request->boolean('clear')) {
            SystemSetting::where('setting_key', CertificateService::COMMON_ISSUE_DATE)->delete();

            return back()->with('success', 'Certificates are now dated the day each one is issued.');
        }

        $validated = $request->validate([
            'issue_date' => 'required|date|after_or_equal:today',
        ], [
            'issue_date.after_or_equal' => 'The issue date cannot be in the past: certificates are never backdated.',
        ]);

        $date = Carbon::parse($validated['issue_date']);
        SystemSetting::set(CertificateService::COMMON_ISSUE_DATE, $date->toDateString());

        return back()->with('success', 'Certificates issued from now on will be dated ' . $date->format('j F Y') . '.');
    }
}
