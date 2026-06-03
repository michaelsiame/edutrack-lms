<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Intake;
use Illuminate\Http\Request;

class IntakeController extends Controller
{
    public function index(Request $request)
    {
        $query = Intake::with(['course', 'course.instructor.user'])
            ->orderBy('start_date', 'desc');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $intakes = $query->paginate(20);
        $courses = Course::orderBy('title')->get();

        return view('admin.intakes.index', compact('intakes', 'courses'));
    }

    public function show(Intake $intake)
    {
        $intake->load(['course', 'enrollments.user', 'enrollments.student']);
        return view('admin.intakes.show', compact('intake'));
    }

    public function transferStudent(Request $request, Intake $intake)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'target_intake_id' => 'required|exists:intakes,id',
        ]);

        $enrollment = $intake->enrollments()->findOrFail($validated['enrollment_id']);
        $targetIntake = Intake::findOrFail($validated['target_intake_id']);

        if ($targetIntake->course_id !== $intake->course_id) {
            return back()->with('error', 'Target intake must be for the same course.');
        }

        if (!$targetIntake->canEnroll()) {
            return back()->with('error', 'Target intake is not open for enrollment.');
        }

        $oldIntakeId = $enrollment->intake_id;

        $enrollment->update(['intake_id' => $targetIntake->id]);

        // Update counts
        Intake::find($oldIntakeId)?->decrementEnrollmentCount();
        $targetIntake->incrementEnrollmentCount();

        return back()->with('success', 'Student transferred to ' . $targetIntake->name . ' successfully.');
    }
}
