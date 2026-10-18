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
use App\Services\CertificateService;
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
        $courses = Course::with(['intakes' => fn ($q) => $q->orderBy('name')])
            ->orderBy('title')
            ->get(['id', 'title']);

        $selectedCourse = $request->filled('course') ? $courses->firstWhere('id', (int) $request->course) : null;

        $users = User::orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'label' => trim($u->first_name . ' ' . $u->last_name) . ' — ' . $u->email,
            ]);

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
        $query = Enrollment::with(['user', 'course', 'paymentPlan', 'certificate']);

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
        $grades = app(\App\Services\GradeAggregationService::class);
        $enrollments->getCollection()->each(fn ($e) => $e->has_recorded_marks = $grades->hasRecordedMarks($e));
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
            'final_grade' => 'nullable|numeric|min:0|max:100',
        ]);

        $grades = app(\App\Services\GradeAggregationService::class);

        $enrollment->update([
            'enrollment_status' => $validated['enrollment_status'],
            'progress' => $validated['progress'] ?? $enrollment->progress,
            'certificate_blocked' => $request->boolean('certificate_blocked'),
            'mode' => $validated['mode'],
            'funding_source' => $validated['funding_source'],
            'cdf_constituency' => $validated['funding_source'] === 'cdf' ? $validated['cdf_constituency'] : null,
            'sponsor_reference' => $validated['sponsor_reference'] ?: null,
        ]);

        // final_grade is computed from recorded assessments, so a hand-set value
        // would silently revert the next time a mark is recorded. Only a student
        // with no marks in the system (e.g. marked on paper) gets a typed grade.
        if ($grades->hasRecordedMarks($enrollment)) {
            $grades->recalculateFinalGrade($enrollment);
        } elseif ($request->has('final_grade')) {
            $enrollment->update(['final_grade' => $validated['final_grade']]);
        }

        // An issued certificate shows the score on its public check page
        $enrollment->certificate?->update(['final_score' => $enrollment->final_grade ?? 0]);

        return back()->with('success', 'Enrollment updated successfully.');
    }

    /**
     * Issue the student's certificate for this enrollment. An enrollment that
     * is not yet completed is marked completed first (the admin is confirming
     * the student finished). A fee block is never bypassed here: the admin
     * clears "Block Certificate" in Edit first if they mean to override it.
     */
    public function issueCertificate(Enrollment $enrollment)
    {
        $existing = $enrollment->certificate;
        if ($enrollment->certificate_issued && $existing) {
            return back()->with('success', "Certificate already issued: {$existing->certificate_number}.");
        }

        if ($enrollment->certificate_blocked) {
            return back()->with('error', 'The certificate is blocked until the fees are fully paid. Record the payment, or untick "Block Certificate" under Edit to override, then try again.');
        }

        if ($enrollment->enrollment_status === 'Dropped') {
            return back()->with('error', 'This student dropped the course. Change the status under Edit first if they did finish.');
        }

        $service = new CertificateService();

        $certificate = DB::transaction(function () use ($enrollment, $service) {
            if ($enrollment->enrollment_status !== 'Completed') {
                $enrollment->update([
                    'progress' => 100,
                    'enrollment_status' => 'Completed',
                    'completion_date' => $enrollment->completion_date ?? now(),
                ]);
            }

            return $service->issueCertificate($enrollment);
        });

        if (!$certificate) {
            return back()->with('error', 'The certificate could not be issued. Check the enrollment and try again.');
        }

        if ($certificate->wasRecentlyCreated) {
            $service->sendCertificateNotification($certificate);
        }

        return back()->with('success', "Certificate issued to {$enrollment->user?->full_name}: {$certificate->certificate_number}.");
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
     * Generate a sample acceptance letter PDF for one of the three fee templates.
     * ?template=3month|6month|1year
     */
    public function sampleAcceptanceLetter(Request $request)
    {
        $template = $request->query('template', '3month');

        $service = app(AcceptanceLetterService::class);

        $structures = [
            '3month' => ['structure' => $service->threeMonthFeeStructure(), 'duration' => '3 months', 'title' => 'Trade Certificate in Computer Studies Level III'],
            '6month' => ['structure' => $service->sixMonthFeeStructure(), 'duration' => '6 months', 'title' => 'Trade Certificate in Computer Studies Level II'],
            '1year'  => ['structure' => $service->oneYearFeeStructure(),   'duration' => '1 year (12 months)', 'title' => 'Trade Certificate in Computer Studies Level I'],
        ];

        $cfg = $structures[$template] ?? $structures['3month'];
        $feeSnapshot = [
            'type'     => $cfg['structure']['type'],
            'day'      => $cfg['structure']['day'],
            'boarding' => $cfg['structure']['boarding'],
        ];

        $letter = new \App\Models\AcceptanceLetter([
            'enrollment_id'     => 0,
            'reference_no'      => 'SAMPLE-' . strtoupper($template),
            'student_name'      => 'Sample Student',
            'course_title'      => $cfg['title'],
            'mode'              => 'Physical',
            'duration'          => $cfg['duration'],
            'commencement_date' => now()->toDate(),
            'fee_snapshot'      => $feeSnapshot,
            'issued_date'       => now()->toDate(),
            'signed_by'         => null,
        ]);

        $pdf = $service->render($letter);
        $filename = 'Sample-Acceptance-Letter-' . $template . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
