<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;
        $achievements = $student
            ? $student->achievements()->with('badge')->orderByDesc('earned_date')->get()
            : collect();
        return view('student.achievements.index', compact('achievements'));
    }
}
