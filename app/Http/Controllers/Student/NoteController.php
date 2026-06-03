<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonNote;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notes = LessonNote::where('user_id', $user->id)
            ->with(['lesson.module', 'course'])
            ->latest()
            ->paginate(20);

        return view('student.notes.index', compact('notes'));
    }

    public function show(Course $course, Lesson $lesson)
    {
        $user = auth()->user();

        if (!$lesson->module || $lesson->module->course_id !== $course->id) {
            abort(404);
        }

        // Verify enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        if (!$enrollment->canAccessContent()) {
            return redirect()->route('checkout.show', $course)
                ->with('warning', 'Please complete at least a 30% deposit to access notes.');
        }

        $note = LessonNote::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        return view('student.notes.show', compact('course', 'lesson', 'note'));
    }

    public function store(Request $request, Course $course, Lesson $lesson)
    {
        $user = auth()->user();

        if (!$lesson->module || $lesson->module->course_id !== $course->id) {
            abort(404);
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        if (!$enrollment->canAccessContent()) {
            abort(403, 'Please complete at least a 30% deposit to save notes.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        LessonNote::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'course_id' => $course->id,
                'content' => $validated['content'],
            ]
        );

        return back()->with('success', 'Note saved successfully.');
    }
}
