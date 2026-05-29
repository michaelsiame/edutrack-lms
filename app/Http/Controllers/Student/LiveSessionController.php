<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LiveSession;
use App\Models\LiveSessionAttendance;
use App\Services\EmailQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LiveSessionController extends Controller
{
    protected EmailQueueService $emailService;

    public function __construct(EmailQueueService $emailService)
    {
        $this->emailService = $emailService;
    }

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

        $user = Auth::user();

        // Auto-start session if in scheduled window
        if ($session->status === 'scheduled' && now() >= $session->scheduled_start_time && now() <= $session->scheduled_end_time) {
            $session->update(['status' => 'live']);
        }

        // Auto-end session if past end time
        if ($session->status === 'live' && now() > $session->scheduled_end_time) {
            $session->update(['status' => 'ended']);
        }

        if ($session->status === 'ended') {
            return redirect()->route('student.live-sessions.index', $course)
                ->with('error', 'This session has already ended.');
        }

        // Check max participants
        if ($session->max_participants && $session->status === 'live') {
            $currentCount = LiveSessionAttendance::where('live_session_id', $session->id)
                ->whereNull('left_at')
                ->count();
            if ($currentCount >= $session->max_participants) {
                return redirect()->route('student.live-sessions.index', $course)
                    ->with('error', 'This session has reached the maximum number of participants.');
            }
        }

        // Record or update attendance
        $attendance = LiveSessionAttendance::firstOrNew([
            'live_session_id' => $session->id,
            'user_id' => $user->id,
        ]);

        if (!$attendance->exists) {
            $attendance->joined_at = now();
            $attendance->is_moderator = $user->isInstructor() && $course->instructor_id === $user->instructor?->id;
            $attendance->save();
        } elseif ($attendance->left_at !== null) {
            // Re-joining after leaving
            $attendance->left_at = null;
            $attendance->save();
        }

        return view('student.live-sessions.join', compact('session', 'attendance', 'course'));
    }

    public function leave(Request $request, LiveSession $session)
    {
        $lesson = $session->lesson;
        if (!$lesson || !$lesson->module) {
            abort(404);
        }
        $course = $lesson->module->course;
        $this->authorizeAccess($course);

        $user = Auth::user();

        $attendance = LiveSessionAttendance::where('live_session_id', $session->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if ($attendance) {
            $attendance->left_at = now();
            $joinedAt = $attendance->joined_at;
            if ($joinedAt) {
                $durationSeconds = now()->diffInSeconds($joinedAt);
                $attendance->duration_seconds = ($attendance->duration_seconds ?? 0) + max(0, $durationSeconds);
            }
            $attendance->save();
        }

        return redirect()->route('student.live-sessions.index', $course)
            ->with('success', 'You have left the session.');
    }

    private function authorizeAccess(Course $course)
    {
        $user = Auth::user();
        $isAdminOrFinance = $user->isAdmin() || $user->isFinance();
        $isInstructor = $user->isInstructor() && $course->instructor_id === $user->instructor?->id;
        $enrollment = $user->enrollments()->where('course_id', $course->id)->first();

        if (!$isAdminOrFinance && !$isInstructor && !$enrollment) {
            abort(403, 'You must be enrolled in this course.');
        }

        if ($enrollment && !$enrollment->canAccessContent() && !$isAdminOrFinance && !$isInstructor) {
            abort(403, 'Please complete at least a 30% deposit to join live sessions.');
        }
    }
}
