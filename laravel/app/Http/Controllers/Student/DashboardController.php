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

        return view('student.progress', compact('enrollments'));
    }

    public function payments()
    {
        $payments = auth()->user()->payments()
            ->with('course')
            ->latest()
            ->paginate(10);

        return view('student.payments', compact('payments'));
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
