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
            'intake_id' => 'nullable|exists:intakes,id',
            'meeting_room_id' => 'required|string|max:255',
            'scheduled_start_time' => 'required|date',
            'scheduled_end_time' => 'required|date|after:scheduled_start_time',
            'description' => 'nullable|string|max:1000',
            'max_participants' => 'nullable|integer|min:1',
            'enable_chat' => 'boolean',
            'enable_screen_share' => 'boolean',
        ]);

        // Verify lesson belongs to this course
        $lesson = \App\Models\Lesson::with('module')->find($validated['lesson_id']);
        if (!$lesson || !$lesson->module || $lesson->module->course_id !== $course->id) {
            return back()->with('error', 'Invalid lesson for this course.');
        }

        // Verify intake belongs to this course if provided
        if (!empty($validated['intake_id'])) {
            $intake = \App\Models\Intake::find($validated['intake_id']);
            if (!$intake || $intake->course_id !== $course->id) {
                return back()->with('error', 'Invalid intake for this course.');
            }
        }

        LiveSession::create([
            'lesson_id' => $validated['lesson_id'],
            'intake_id' => $validated['intake_id'] ?? null,
            'instructor_id' => Auth::user()->instructor?->id,
            'meeting_room_id' => $validated['meeting_room_id'],
            'scheduled_start_time' => $validated['scheduled_start_time'],
            'scheduled_end_time' => $validated['scheduled_end_time'],
            'status' => 'scheduled',
            'max_participants' => $validated['max_participants'] ?? null,
            'description' => $validated['description'],
            'enable_chat' => $request->boolean('enable_chat', true),
            'enable_screen_share' => $request->boolean('enable_screen_share', true),
        ]);

        return redirect()->route('instructor.live-sessions.index', $course)->with('success', 'Live session scheduled.');
    }

    public function edit(Course $course, LiveSession $session)
    {
        $this->authorizeInstructor($course);

        if ($session->lesson?->module?->course_id !== $course->id) {
            abort(403, 'This session does not belong to the specified course.');
        }

        $session->load('lesson');
        return view('instructor.live-sessions.edit', compact('course', 'session'));
    }

    public function update(Request $request, Course $course, LiveSession $session)
    {
        $this->authorizeInstructor($course);

        if ($session->lesson?->module?->course_id !== $course->id) {
            abort(403, 'This session does not belong to the specified course.');
        }

        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'intake_id' => 'nullable|exists:intakes,id',
            'meeting_room_id' => 'required|string|max:255',
            'scheduled_start_time' => 'required|date',
            'scheduled_end_time' => 'required|date|after:scheduled_start_time',
            'description' => 'nullable|string|max:1000',
            'max_participants' => 'nullable|integer|min:1',
            'enable_chat' => 'boolean',
            'enable_screen_share' => 'boolean',
        ]);

        // Verify lesson belongs to this course
        $lesson = \App\Models\Lesson::with('module')->find($validated['lesson_id']);
        if (!$lesson || !$lesson->module || $lesson->module->course_id !== $course->id) {
            return back()->with('error', 'Invalid lesson for this course.');
        }

        // Verify intake belongs to this course if provided
        if (!empty($validated['intake_id'])) {
            $intake = \App\Models\Intake::find($validated['intake_id']);
            if (!$intake || $intake->course_id !== $course->id) {
                return back()->with('error', 'Invalid intake for this course.');
            }
        }

        $session->update([
            'lesson_id' => $validated['lesson_id'],
            'intake_id' => $validated['intake_id'] ?? null,
            'meeting_room_id' => $validated['meeting_room_id'],
            'scheduled_start_time' => $validated['scheduled_start_time'],
            'scheduled_end_time' => $validated['scheduled_end_time'],
            'description' => $validated['description'] ?? null,
            'max_participants' => $validated['max_participants'] ?? null,
            'enable_chat' => $request->boolean('enable_chat', true),
            'enable_screen_share' => $request->boolean('enable_screen_share', true),
        ]);

        return redirect()->route('instructor.live-sessions.index', $course)->with('success', 'Live session updated.');
    }

    public function destroy(Course $course, LiveSession $session)
    {
        $this->authorizeInstructor($course);

        // Verify the session belongs to this course via its lesson
        if ($session->lesson?->module?->course_id !== $course->id) {
            abort(403, 'This session does not belong to the specified course.');
        }

        $session->delete();
        return redirect()->route('instructor.live-sessions.index', $course)->with('success', 'Session deleted.');
    }

    private function authorizeInstructor(Course $course)
    {
        $user = Auth::user();
        $isInstructor = $user->isInstructor();
        $ownsCourse = $course->instructor_id == $user->instructor?->id;
        if (!$isInstructor || !$ownsCourse) {
            abort(403);
        }
    }
}
