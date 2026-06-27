<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\EnrollmentPaymentPlan;
use App\Models\Course;
use App\Models\Intake;
use App\Models\Student;
use App\Models\User;
use App\Services\AcceptanceLetterService;
use App\Services\StudentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    /**
     * Show the "enrol a student" form (admin-driven onboarding, e.g. for
     * in-person students who paid at the desk).
     */
    public function create(Request $request)
    {
        $courses = Course::orderBy('title')->get(['id', 'title']);
        $selectedCourse = $request->filled('course')
            ? Course::with('intakes')->find($request->course)
            : null;
        $users = User::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);

        return view('admin.enrollments.create', compact('courses', 'selectedCourse', 'users'));
    }

    /**
     * Create an enrolment on a student's behalf. Bypasses the K150
     * registration-fee gate (admin is onboarding directly) and lets the admin
     * choose the delivery mode. Intake counts self-heal via EnrollmentObserver.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'intake_id' => 'nullable|exists:intakes,id',
            'mode' => 'required|in:online,in_person,hybrid',
            'funding_source' => 'required|in:self,cdf,bursary,employer',
            'cdf_constituency' => 'nullable|string|max:255',
            'sponsor_reference' => 'nullable|string|max:255',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $user = User::findOrFail($validated['user_id']);

        if (Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->exists()) {
            return back()->withInput()->with('error', 'This student is already enrolled in that course (use the enrolment list to edit it).');
        }

        // Resolve intake: explicit choice, else the course default.
        $intake = !empty($validated['intake_id'])
            ? Intake::find($validated['intake_id'])
            : $course->defaultIntake;

        if (!$intake || $intake->course_id !== $course->id) {
            return back()->withInput()->with('error', 'Please choose a valid intake for this course.');
        }

        $price = $intake->effective_price ?? $course->discount_price ?? $course->price ?? 0;
        $isFree = $price <= 0;

        $fundingData = [
            'funding_source' => $validated['funding_source'],
            'cdf_constituency' => $validated['funding_source'] === 'cdf' ? $validated['cdf_constituency'] : null,
            'sponsor_reference' => $validated['sponsor_reference'] ?: null,
        ];

        DB::transaction(function () use ($user, $course, $intake, $validated, $price, $isFree, $fundingData) {
            $student = $user->student ?: Student::create([
                'user_id' => $user->id,
                'student_number' => StudentNumberService::generate((int) now()->year),
                'enrollment_date' => now(),
            ]);

            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
                'intake_id' => $intake->id,
                'enrolled_at' => now(),
                'enrollment_status' => 'Enrolled',
                'payment_status' => $isFree ? 'completed' : 'pending',
                'amount_paid' => 0,
                'certificate_blocked' => !$isFree,
                'mode' => $validated['mode'],
            ] + $fundingData);

            EnrollmentPaymentPlan::create([
                'enrollment_id' => $enrollment->id,
                'user_id' => $user->id,
                'course_id' => $course->id,
                'total_fee' => $price,
                'total_paid' => 0,
                'currency' => 'ZMW',
                'payment_status' => $isFree ? 'completed' : 'pending',
            ]);
        });

        return redirect()->route('admin.enrollments.index')
            ->with('success', "{$user->full_name} enrolled in {$course->title}." . ($isFree ? '' : ' Record their payment to unlock the certificate.'));
    }

    public function index(Request $request)
    {
        $query = Enrollment::with(['user', 'course', 'paymentPlan']);

        if ($request->filled('course')) {
            $query->where('course_id', $request->course);
        }

        if ($request->filled('status')) {
            $query->where('enrollment_status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('enrolled_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('enrolled_at', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->latest('enrolled_at')->paginate(20)->withQueryString();
        $courses = Course::published()->orderBy('title')->get();

        return view('admin.enrollments.index', compact('enrollments', 'courses'));
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'enrollment_status' => 'required|in:Enrolled,In Progress,Completed,Dropped,Expired',
            'progress' => 'nullable|numeric|min:0|max:100',
            'certificate_blocked' => 'nullable|boolean',
            'mode' => 'required|in:online,in_person,hybrid',
            'funding_source' => 'required|in:self,cdf,bursary,employer',
            'cdf_constituency' => 'nullable|string|max:255',
            'sponsor_reference' => 'nullable|string|max:255',
        ]);

        $enrollment->update([
            'enrollment_status' => $validated['enrollment_status'],
            'progress' => $validated['progress'] ?? $enrollment->progress,
            'certificate_blocked' => $request->boolean('certificate_blocked'),
            'mode' => $validated['mode'],
            'funding_source' => $validated['funding_source'],
            'cdf_constituency' => $validated['funding_source'] === 'cdf' ? $validated['cdf_constituency'] : null,
            'sponsor_reference' => $validated['sponsor_reference'] ?: null,
        ]);

        // final_grade is computed from recorded assessments, never hand-set,
        // so it can't silently revert the next time a mark is recorded.
        app(\App\Services\GradeAggregationService::class)->recalculateFinalGrade($enrollment);

        return back()->with('success', 'Enrollment updated successfully.');
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return back()->with('success', 'Enrollment deleted successfully.');
    }

    /**
     * Generate (or reuse) an acceptance letter for the enrollment and stream it
     * as a PDF download.
     */
    public function generateAcceptanceLetter(Enrollment $enrollment)
    {
        $service = app(AcceptanceLetterService::class);
        $letter = $service->generate($enrollment);
        $pdf = $service->render($letter);

        $filename = 'Acceptance-Letter-' . preg_replace('/[^A-Za-z0-9_-]/', '_', $letter->reference_no) . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Download a blank, printable CDF acceptance-letter template for a
     * Computer Studies level (i | ii | iii), for applicants to fill in by hand.
     */
    public function blankAcceptanceLetter(string $level)
    {
        $pdf = app(AcceptanceLetterService::class)->renderBlank($level);

        $filename = 'Acceptance-Letter-Template-Computer-Studies-Level-' . strtoupper($level) . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
