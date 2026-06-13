<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Intake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IntakeController extends Controller
{
    public function index(Course $course)
    {
        $this->authorizeInstructor($course);
        $intakes = $course->intakes()->withCount('enrollments')->orderBy('display_order')->get();
        return view('instructor.intakes.index', compact('course', 'intakes'));
    }

    public function create(Course $course)
    {
        $this->authorizeInstructor($course);
        return view('instructor.intakes.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeInstructor($course);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'application_deadline' => 'nullable|date',
            'learning_deadline' => 'nullable|date|after_or_equal:start_date',
            'max_students' => 'nullable|integer|min:0',
            'price_override' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,open,closed,in_progress,completed',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['course_id'] = $course->id;
        $validated['is_default'] = false;
        $validated['display_order'] = $validated['display_order'] ?? ($course->intakes()->max('display_order') + 1);

        Intake::create($validated);

        return redirect()->route('instructor.courses.intakes.index', $course)
            ->with('success', 'Intake created successfully.');
    }

    public function edit(Course $course, Intake $intake)
    {
        $this->authorizeInstructor($course);

        if ($intake->course_id !== $course->id) {
            abort(403, 'This intake does not belong to the specified course.');
        }

        return view('instructor.intakes.edit', compact('course', 'intake'));
    }

    public function update(Request $request, Course $course, Intake $intake)
    {
        $this->authorizeInstructor($course);

        if ($intake->course_id !== $course->id) {
            abort(403, 'This intake does not belong to the specified course.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'application_deadline' => 'nullable|date',
            'learning_deadline' => 'nullable|date|after_or_equal:start_date',
            'max_students' => 'nullable|integer|min:0',
            'price_override' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,open,closed,in_progress,completed',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $intake->update($validated);
        $intake->checkCapacity();

        return redirect()->route('instructor.courses.intakes.index', $course)
            ->with('success', 'Intake updated successfully.');
    }

    public function destroy(Course $course, Intake $intake)
    {
        $this->authorizeInstructor($course);

        if ($intake->course_id !== $course->id) {
            abort(403, 'This intake does not belong to the specified course.');
        }

        if ($intake->enrollments()->count() > 0) {
            return back()->with('error', 'Cannot delete an intake that has enrollments.');
        }

        $intake->delete();

        return redirect()->route('instructor.courses.intakes.index', $course)
            ->with('success', 'Intake deleted successfully.');
    }

    public function close(Course $course, Intake $intake)
    {
        $this->authorizeInstructor($course);

        if ($intake->course_id !== $course->id) {
            abort(403);
        }

        $intake->update(['status' => 'closed']);

        return back()->with('success', 'Intake closed successfully.');
    }

    public function reopen(Course $course, Intake $intake)
    {
        $this->authorizeInstructor($course);

        if ($intake->course_id !== $course->id) {
            abort(403);
        }

        $intake->update(['status' => 'open']);

        return back()->with('success', 'Intake reopened successfully.');
    }

    private function authorizeInstructor(Course $course)
    {
        $user = Auth::user();
        if ($user?->isAdmin()) {
            return;
        }

        $isInstructor = $user->isInstructor();
        $ownsCourse = $course->instructor_id == $user->instructor?->id;
        if (!$isInstructor || !$ownsCourse) {
            abort(403);
        }
    }
}
