<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;

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
        return view('admin.settings');
    }
}
