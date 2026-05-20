<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LiveSessionController extends Controller
{
    public function index(Course $course)
    {
        $this->authorizeInstructor($course);
        $sessions = $course->liveSessions()->orderBy('scheduled_start_time', 'desc')->get();
        return view('instructor.live-sessions.index', compact('course', 'sessions'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeInstructor($course);
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'meeting_room_id' => 'required|string|max:255',
            'scheduled_start_time' => 'required|date',
            'scheduled_end_time' => 'required|date|after:scheduled_start_time',
            'description' => 'nullable|string|max:1000',
            'max_participants' => 'nullable|integer|min:1',
            'enable_chat' => 'boolean',
            'enable_screen_share' => 'boolean',
        ]);

        LiveSession::create([
            'lesson_id' => $validated['lesson_id'] ?? null,
            'instructor_id' => Auth::user()->instructor?->id,
            'meeting_room_id' => $validated['meeting_room_id'],
            'scheduled_start_time' => $validated['scheduled_start_time'],
            'scheduled_end_time' => $validated['scheduled_end_time'],
            'status' => 'scheduled',
            'max_participants' => $validated['max_participants'] ?? null,
            'description' => $validated['description'],
            'enable_chat' => $validated['enable_chat'] ?? true,
            'enable_screen_share' => $validated['enable_screen_share'] ?? true,
        ]);

        return redirect()->route('instructor.live-sessions.index', $course)->with('success', 'Live session scheduled.');
    }

    public function destroy(Course $course, LiveSession $session)
    {
        $this->authorizeInstructor($course);
        $session->delete();
        return redirect()->route('instructor.live-sessions.index', $course)->with('success', 'Session deleted.');
    }

    private function authorizeInstructor(Course $course)
    {
        $user = Auth::user();
        $isInstructor = $user->roles()->where('role_id', 3)->exists();
        $ownsCourse = $course->instructor_id == $user->instructor?->id;
        if (!$isInstructor || !$ownsCourse) {
            abort(403);
        }
    }
}
