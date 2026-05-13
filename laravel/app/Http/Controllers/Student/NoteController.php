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

        // Verify enrollment
        Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        $note = LessonNote::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        return view('student.notes.show', compact('course', 'lesson', 'note'));
    }

    public function store(Request $request, Course $course, Lesson $lesson)
    {
        $user = auth()->user();

        Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

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
