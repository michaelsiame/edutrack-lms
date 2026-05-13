<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\LiveSession;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Get active enrollments with course schedules
        $enrollments = Enrollment::where('user_id', $user->id)
            ->whereIn('enrollment_status', ['Enrolled', 'In Progress'])
            ->with('course')
            ->get();

        // Get live sessions for enrolled courses via lessons
        $lessonIds = \App\Models\Lesson::whereHas('module.course', function ($q) use ($enrollments) {
            $q->whereIn('courses.id', $enrollments->pluck('course_id'));
        })->pluck('lessons.id');
        $liveSessions = LiveSession::whereIn('lesson_id', $lessonIds)
            ->whereBetween('scheduled_start_time', [$weekStart, $weekEnd])
            ->with('lesson.module.course')
            ->orderBy('scheduled_start_time')
            ->get();

        // Build weekly schedule
        $schedule = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($days as $day) {
            $schedule[$day] = [];
        }

        foreach ($liveSessions as $session) {
            $dayName = $session->scheduled_start_time->format('l');
            $schedule[$dayName][] = [
                'type' => 'live_session',
                'title' => $session->description ?: 'Live Session',
                'course' => $session->lesson?->module?->course?->title ?? 'Course',
                'time' => $session->scheduled_start_time->format('h:i A') . ' - ' . $session->scheduled_end_time->format('h:i A'),
                'url' => 'https://meet.jit.si/' . $session->meeting_room_id,
            ];
        }

        // Add course start dates
        foreach ($enrollments as $enrollment) {
            if ($enrollment->course->start_date) {
                $dayName = $enrollment->course->start_date->format('l');
                $schedule[$dayName][] = [
                    'type' => 'course_start',
                    'title' => $enrollment->course->title . ' Starts',
                    'course' => $enrollment->course->title,
                    'time' => 'All Day',
                    'url' => null,
                ];
            }
        }

        return view('student.schedule', compact('schedule', 'days', 'weekStart', 'weekEnd'));
    }
}
