<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isInstructor()) {
            return redirect()->route('instructor.dashboard');
        }

        if ($user->isFinance()) {
            return redirect()->route('finance.dashboard');
        }

        return redirect()->route('student.dashboard');
    }
}
