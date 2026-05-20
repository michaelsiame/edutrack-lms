<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

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
        $completedCourses = $enrollments->where('enrollment_status', 'completed')->count();
        $inProgressCourses = $enrollments->where('enrollment_status', 'active')->count();
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
            ->where('enrollment_status', 'active')
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
}
