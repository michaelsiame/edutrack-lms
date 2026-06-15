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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $user = auth()->user();
        $instructor = $user->instructor;
        if (!$user->isAdmin() && !$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $courseIds = $user->isAdmin()
            ? Course::pluck('id')
            : $instructor->courses()->pluck('id');

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
        $user = auth()->user();
        $instructor = $user->instructor;
        if (!$user->isAdmin() && !$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $courses = $user->isAdmin()
            ? Course::with(['enrollments' => fn ($q) => $q->whereHas('user')->with('student.user'), 'modules.lessons'])->latest()->get()
            : $instructor->courses()
                ->with(['enrollments' => fn ($q) => $q->whereHas('user')->with('student.user'), 'modules.lessons'])
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
        if (!auth()->user()->isAdmin() && (!$instructor || $course->instructor_id !== $instructor->id)) {
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

    public function markComplete(Course $course, Enrollment $enrollment)
    {
        $instructor = auth()->user()->instructor;
        if (!auth()->user()->isAdmin() && (!$instructor || $course->instructor_id !== $instructor->id)) {
            abort(403, 'You do not own this course.');
        }

        if ($enrollment->course_id !== $course->id) {
            abort(404, 'Enrollment not found for this course.');
        }

        $certificate = null;
        $wasAlreadyIssued = $enrollment->certificate_issued;

        DB::transaction(function () use ($enrollment, &$certificate) {
            $enrollment->update([
                'progress' => 100,
                'enrollment_status' => 'Completed',
                'completion_date' => $enrollment->completion_date ?? now(),
            ]);

            if ($enrollment->certificate_blocked) {
                return;
            }

            if (!$enrollment->certificate_issued) {
                $service = new CertificateService();
                $certificate = $service->issueCertificate($enrollment);
            }
        });

        if ($enrollment->certificate_blocked) {
            return back()->with('warning', 'Student marked as complete, but the certificate is blocked until full payment is received.');
        }

        if ($wasAlreadyIssued) {
            return back()->with('success', 'Student marked as complete. Certificate was already issued.');
        }

        if ($certificate) {
            $service = new CertificateService();
            $service->sendCertificateNotification($certificate);

            return back()->with('success', 'Student marked as complete and certificate issued: ' . $certificate->certificate_number);
        }

        return back()->with('success', 'Student marked as complete.');
    }

    /**
     * Show a per-course gradebook: students down the rows, assessments across columns.
     */
    public function gradebook(Course $course)
    {
        $this->authorizeInstructor($course);

        $quizzes = $course->quizzes()
            ->where('is_published', true)
            ->orderBy('id')
            ->get();

        $assignments = $course->assignments()
            ->orderBy('id')
            ->get();

        $enrollments = $course->enrollments()
            ->where('enrollment_status', '!=', 'Dropped')
            ->whereHas('user')
            ->with(['user', 'student'])
            ->get();

        $quizIds = $quizzes->pluck('id');
        $assignmentIds = $assignments->pluck('id');
        $studentIds = $enrollments->pluck('student_id');

        // Best attempt per quiz per student.
        $quizAttempts = QuizAttempt::whereIn('quiz_id', $quizIds)
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', ['Graded', 'Submitted'])
            ->selectRaw('quiz_id, student_id, MAX(score) as score')
            ->groupBy('quiz_id', 'student_id')
            ->get();

        $quizBest = $quizAttempts->keyBy(fn ($attempt) => $attempt->quiz_id . ':' . $attempt->student_id);

        // Best submission per assignment per student, expressed as a percentage.
        $assignmentSubmissions = AssignmentSubmission::whereIn('assignment_id', $assignmentIds)
            ->whereIn('student_id', $studentIds)
            ->whereNotNull('points_earned')
            ->with('assignment:id,max_points')
            ->get();

        $assignmentBest = $assignmentSubmissions
            ->groupBy(fn ($submission) => $submission->assignment_id . ':' . $submission->student_id)
            ->map(fn ($group) => round($group->max(function ($submission) {
                $max = $submission->assignment->max_points ?? 100;
                return $max > 0 ? ($submission->points_earned / $max) * 100 : 0;
            }), 2));

        $rows = $enrollments->map(function ($enrollment) use ($quizzes, $assignments, $quizBest, $assignmentBest) {
            $studentId = $enrollment->student_id;

            $quizScores = [];
            foreach ($quizzes as $quiz) {
                $quizScores[$quiz->id] = $quizBest->get($quiz->id . ':' . $studentId)?->score;
            }

            $assignmentScores = [];
            foreach ($assignments as $assignment) {
                $assignmentScores[$assignment->id] = $assignmentBest->get($assignment->id . ':' . $studentId);
            }

            return [
                'enrollment' => $enrollment,
                'user' => $enrollment->user,
                'student' => $enrollment->student,
                'name' => $enrollment->user?->full_name ?? 'Unknown',
                'mode' => $enrollment->modeLabel(),
                'quiz_scores' => $quizScores,
                'assignment_scores' => $assignmentScores,
                'final_grade' => $enrollment->final_grade,
            ];
        });

        return view('instructor.gradebook', compact('course', 'quizzes', 'assignments', 'rows'));
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

    /**
     * Ensure the current user owns the course or is an admin.
     */
    private function authorizeInstructor(Course $course): void
    {
        $user = Auth::user();

        if ($user?->isAdmin()) {
            return;
        }

        $instructor = $user?->instructor;

        if (!$instructor || $course->instructor_id !== $instructor->id) {
            abort(403, 'You do not have access to this course.');
        }
    }
}
