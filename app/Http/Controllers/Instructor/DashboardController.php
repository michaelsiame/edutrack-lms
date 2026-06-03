<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $instructor = auth()->user()->instructor;

        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $stats = [
            'total_courses' => $instructor->courses()->count(),
            'total_students' => Enrollment::whereIn('course_id', $instructor->courses()->pluck('id'))->count(),
            'average_rating' => $instructor->rating,
        ];

        $courses = $instructor->courses()->withCount('enrollments')->latest()->get();

        return view('instructor.dashboard', compact('stats', 'courses'));
    }

    public function submissions()
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $courseIds = $instructor->courses()->pluck('id');

        $assignmentSubmissions = AssignmentSubmission::whereHas('assignment', function ($q) use ($courseIds) {
                $q->whereIn('course_id', $courseIds);
            })
            ->with(['student.user', 'assignment.course'])
            ->latest('submitted_at')
            ->paginate(20, ['*'], 'assignment_page');

        $quizAttempts = QuizAttempt::whereHas('quiz', function ($q) use ($courseIds) {
            $q->whereIn('course_id', $courseIds);
        })->with(['student.user', 'quiz.course'])
            ->latest('completed_at')
            ->paginate(20, ['*'], 'quiz_page');

        return view('instructor.submissions', compact('assignmentSubmissions', 'quizAttempts'));
    }

    public function progress()
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $courses = $instructor->courses()
            ->with(['enrollments.student.user', 'modules.lessons'])
            ->latest()
            ->get();

        $courseIds = $courses->pluck('id');

        // Get lesson progress for all students in instructor's courses
        $enrollmentIds = Enrollment::whereIn('course_id', $courseIds)->pluck('id');
        $lessonProgress = LessonProgress::whereIn('enrollment_id', $enrollmentIds)
            ->selectRaw('enrollment_id, COUNT(*) as completed_count')
            ->where('status', 'Completed')
            ->groupBy('enrollment_id')
            ->pluck('completed_count', 'enrollment_id');

        // Get total lessons per course
        $totalLessonsPerCourse = [];
        foreach ($courses as $course) {
            $totalLessonsPerCourse[$course->id] = $course->lessons()->count();
        }

        return view('instructor.progress', compact('courses', 'lessonProgress', 'totalLessonsPerCourse'));
    }

    public function issueCertificate(Course $course, Enrollment $enrollment)
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor || $course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this course.');
        }

        if ($enrollment->course_id !== $course->id) {
            abort(404, 'Enrollment not found for this course.');
        }

        if ($enrollment->certificate_issued) {
            return back()->with('info', 'Certificate has already been issued for this student.');
        }

        if ($enrollment->certificate_blocked) {
            return back()->with('warning', 'Certificate is blocked until full payment is received.');
        }

        $service = new CertificateService();
        $certificate = $service->issueCertificate($enrollment);

        if (!$certificate) {
            return back()->with('info', 'Certificate has already been issued or is blocked.');
        }

        $service->sendCertificateNotification($certificate);

        return back()->with('success', 'Certificate issued successfully: ' . $certificate->certificate_number);
    }

    public function analytics()
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $courses = $instructor->courses()
            ->withCount(['enrollments', 'lessons'])
            ->withAvg('reviews', 'rating')
            ->latest()
            ->get();

        $courseIds = $courses->pluck('id');

        $totalStudents = Enrollment::whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id');
        $totalEnrollments = Enrollment::whereIn('course_id', $courseIds)->count();
        $completedEnrollments = Enrollment::whereIn('course_id', $courseIds)->where('enrollment_status', 'Completed')->count();
        $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 1) : 0;

        $avgQuizScore = QuizAttempt::whereHas('quiz', function ($q) use ($courseIds) {
            $q->whereIn('course_id', $courseIds);
        })->avg('score') ?? 0;

        $monthlyEnrollments = Enrollment::whereIn('course_id', $courseIds)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();

        return view('instructor.analytics', compact(
            'courses', 'totalStudents', 'totalEnrollments',
            'completedEnrollments', 'completionRate', 'avgQuizScore', 'monthlyEnrollments'
        ));
    }
}
