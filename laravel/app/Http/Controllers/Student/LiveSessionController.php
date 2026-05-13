<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LiveSession;
use Illuminate\Support\Facades\Auth;

class LiveSessionController extends Controller
{
    public function index(Course $course)
    {
        $this->authorizeAccess($course);
        $sessions = $course->liveSessions()->orderBy('scheduled_start_time', 'desc')->get();
        return view('student.live-sessions.index', compact('course', 'sessions'));
    }

    public function join(LiveSession $session)
    {
        $lesson = $session->lesson;
        if (!$lesson || !$lesson->module) {
            abort(404);
        }
        $course = $lesson->module->course;
        $this->authorizeAccess($course);

        // Update status if session is in scheduled window
        if ($session->status === 'scheduled' && now() >= $session->scheduled_start_time && now() <= $session->scheduled_end_time) {
            $session->update(['status' => 'live']);
        }

        return view('student.live-sessions.join', compact('session'));
    }

    private function authorizeAccess(Course $course)
    {
        $enrolled = Auth::user()->enrollments()->where('course_id', $course->id)->exists();
        $isStaff = Auth::user()->roles()->whereIn('role_id', [1, 2])->exists();
        if (!$enrolled && !$isStaff) {
            abort(403, 'You must be enrolled in this course.');
        }
    }
}
