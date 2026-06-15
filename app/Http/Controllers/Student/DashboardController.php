<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\LiveSessionAttendance;
use App\Models\Payment;
use App\Models\QuizAttempt;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $enrollments = auth()->user()->enrollments()
            ->with('course.modules.lessons')
            ->latest()
            ->take(5)
            ->get();

        $certificates = auth()->user()->certificates()
            ->with('course')
            ->latest()
            ->take(5)
            ->get();

        // Payment summary for dashboard
        $payments = auth()->user()->payments()
            ->with('course')
            ->where('payment_status', 'Completed')
            ->latest()
            ->take(4)
            ->get();

        $totalPaid = auth()->user()->payments()
            ->where('payment_status', 'Completed')
            ->sum('amount');

        $balanceDue = auth()->user()->enrollments()
            ->whereIn('enrollment_status', ['Enrolled', 'In Progress'])
            ->leftJoin('courses', 'enrollments.course_id', '=', 'courses.id')
            ->selectRaw('SUM(GREATEST(0, COALESCE(courses.price, 0) - COALESCE(enrollments.amount_paid, 0))) as balance')
            ->value('balance') ?? 0;

        // Onboarding state — drives the "getting started" strip for new students.
        $hasRegistrationFee = \App\Models\RegistrationFee::where('user_id', auth()->id())
            ->where('payment_status', 'completed')
            ->exists();
        $hasEnrolment = auth()->user()->enrollments()->exists();
        $firstCourse = $enrollments->first()?->course;

        return view('student.dashboard', compact(
            'enrollments', 'certificates', 'payments', 'totalPaid', 'balanceDue',
            'hasRegistrationFee', 'hasEnrolment', 'firstCourse'
        ));
    }

    public function progress()
    {
        $enrollments = auth()->user()->enrollments()
            ->with('course')
            ->latest()
            ->get();

        $totalCourses = $enrollments->count();
        $completedCourses = $enrollments->where('enrollment_status', 'Completed')->count();
        $inProgressCourses = $enrollments->where('enrollment_status', 'In Progress')->count();
        $totalCertificates = auth()->user()->certificates()->count();

        return view('student.progress', compact('enrollments', 'totalCourses', 'completedCourses', 'inProgressCourses', 'totalCertificates'));
    }

    public function payments()
    {
        $payments = auth()->user()->payments()
            ->with('course')
            ->latest()
            ->paginate(10);

        $totalPaid = auth()->user()->payments()
            ->where('payment_status', 'Completed')
            ->sum('amount');

        $totalPending = auth()->user()->payments()
            ->where('payment_status', 'Pending')
            ->sum('amount');

        $activeEnrollments = auth()->user()->enrollments()
            ->whereIn('enrollment_status', ['Enrolled', 'In Progress'])
            ->count();

        return view('student.payments', compact('payments', 'totalPaid', 'totalPending', 'activeEnrollments'));
    }

    public function certificates()
    {
        $certificates = auth()->user()->certificates()
            ->with('course')
            ->latest()
            ->paginate(10);

        return view('student.certificates', compact('certificates'));
    }

    public function submissions()
    {
        $studentId = auth()->user()->student?->id;

        $assignmentSubmissions = AssignmentSubmission::where('student_id', $studentId)
            ->with(['assignment.course'])
            ->latest('submitted_at')
            ->paginate(10, ['*'], 'assignment_page');

        $quizAttempts = QuizAttempt::where('student_id', $studentId)
            ->with(['quiz.course'])
            ->latest('completed_at')
            ->paginate(10, ['*'], 'quiz_page');

        return view('student.submissions', compact('assignmentSubmissions', 'quizAttempts'));
    }

    public function analytics()
    {
        $user = auth()->user();
        $studentId = $user->student?->id;

        $enrollments = $user->enrollments()
            ->with('course')
            ->latest()
            ->get();

        $totalCourses = $enrollments->count();
        $completedCourses = $enrollments->where('enrollment_status', 'Completed')->count();
        $inProgressCourses = $enrollments->where('enrollment_status', 'In Progress')->count();

        // Quiz analytics
        $quizAttempts = QuizAttempt::where('student_id', $studentId)
            ->with('quiz.course')
            ->latest()
            ->get();
        $avgQuizScore = $quizAttempts->whereNotNull('score')->avg('score') ?? 0;
        $totalQuizzesTaken = $quizAttempts->count();
        $quizzesPassed = $quizAttempts->filter(fn($a) => $a->isPassed())->count();

        // Assignment analytics
        $assignments = AssignmentSubmission::where('student_id', $studentId)
            ->with('assignment.course')
            ->latest()
            ->get();
        $avgAssignmentScore = $assignments->whereNotNull('points_earned')->avg('points_earned') ?? 0;
        $totalAssignmentsSubmitted = $assignments->count();
        $assignmentsGraded = $assignments->where('status', 'Graded')->count();

        // Live session attendance time
        $totalLiveMinutes = LiveSessionAttendance::where('user_id', $user->id)
            ->sum('duration_seconds') / 60;

        // Monthly enrollment trend
        $monthlyEnrollments = $enrollments
            ->groupBy(fn($e) => $e->enrolled_at?->format('Y-m') ?? 'Unknown')
            ->map->count()
            ->sortKeys();

        return view('student.analytics', compact(
            'totalCourses', 'completedCourses', 'inProgressCourses',
            'avgQuizScore', 'totalQuizzesTaken', 'quizzesPassed',
            'avgAssignmentScore', 'totalAssignmentsSubmitted', 'assignmentsGraded',
            'totalLiveMinutes', 'monthlyEnrollments', 'quizAttempts', 'assignments'
        ));
    }

    public function downloadReceipt(Request $request, Payment $payment)
    {
        // Ensure the payment belongs to the current user
        if ($payment->student_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $service = app(InvoiceService::class);

        // Find or generate invoice for this payment
        $invoice = $payment->invoice;
        if (!$invoice) {
            $invoice = $service->generateInvoice($payment);
        }

        $pdf = $service->generatePdf($invoice);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receipt-' . $invoice->invoice_number . '.pdf"',
        ]);
    }
}
