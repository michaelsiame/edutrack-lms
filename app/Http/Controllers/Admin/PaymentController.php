<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Role;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['student', 'course']);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $payments = $query->latest()->paginate(20)->withQueryString();
        return view('admin.payments.index', compact('payments'));
    }

    public function create()
    {
        $students = \App\Models\Student::with(['user', 'user.enrollments.course', 'user.enrollments.paymentPlan'])
            ->whereHas('user', function ($q) {
                $q->whereHas('roles', function ($r) {
                    $r->where('role_id', Role::STUDENT); // Student role
                });
            })
            ->orWhereDoesntHave('user.roles')
            ->orderBy('id')
            ->get();

        $studentOptions = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->full_name ?? ('Student #' . $student->id),
                'email' => $student->email ?? 'no email',
                'enrollments' => ($student->user?->enrollments ?? collect())->map(function ($enrollment) {
                    $course = $enrollment->course;
                    $totalFee = $enrollment->effectivePrice();
                    $discounts = $enrollment->totalDiscounts();
                    $effectivePaid = (float) $enrollment->amount_paid + (float) $discounts;

                    return [
                        'course_id' => $enrollment->course_id,
                        'course_title' => $course?->title ?? 'Unknown course',
                        'total_fee' => $totalFee,
                        'amount_paid' => (float) $enrollment->amount_paid,
                        'discounts' => $discounts,
                        'effective_paid' => $effectivePaid,
                        'outstanding' => max(0, $totalFee - $effectivePaid),
                        'payment_status' => $enrollment->payment_status,
                        'enrollment_status' => $enrollment->enrollment_status,
                        'is_fully_paid' => $enrollment->isFullyPaid(),
                    ];
                })->values(),
            ];
        })->values();

        $courses = \App\Models\Course::orderBy('title')->get(['id', 'title', 'price', 'discount_price']);
        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('method_name')->get();

        return view('admin.payments.create', compact('studentOptions', 'courses', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'amount' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,payment_method_id',
            'payment_status' => 'required|in:Pending,Completed,Failed,Refunded',
            'transaction_id' => 'nullable|string|max:255',
            'payment_type' => 'nullable|in:registration,course_fee,partial_payment',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['currency'] = 'ZMW';
        $validated['recorded_by'] = auth()->id();

        // Find or create enrollment for this student and course
        $student = \App\Models\Student::find($validated['student_id']);
        $enrollment = \App\Models\Enrollment::where('user_id', $student?->user_id)
            ->where('course_id', $validated['course_id'])
            ->first();

        if (!$enrollment) {
            $enrollment = \App\Models\Enrollment::create([
                'user_id' => $student?->user_id,
                'student_id' => $student?->id,
                'course_id' => $validated['course_id'],
                'enrolled_at' => now(),
                'enrollment_status' => 'Enrolled',
                'payment_status' => 'pending',
                'amount_paid' => 0,
                'certificate_blocked' => true,
            ]);

            \App\Models\EnrollmentPaymentPlan::create([
                'enrollment_id' => $enrollment->id,
                'user_id' => $student?->user_id,
                'course_id' => $validated['course_id'],
                'total_fee' => \App\Models\Course::find($validated['course_id'])?->discount_price ?? \App\Models\Course::find($validated['course_id'])?->price ?? 0,
                'total_paid' => 0,
                'currency' => 'ZMW',
                'payment_status' => 'pending',
            ]);
        }

        $validated['enrollment_id'] = $enrollment->id;

        // Determine payment type: full payment if amount covers outstanding balance.
        $totalFee = $enrollment->effectivePrice();
        $discounts = $enrollment->totalDiscounts();
        $outstanding = max(0, $totalFee - (float) $enrollment->amount_paid - (float) $discounts);
        $validated['payment_type'] = ((float) $validated['amount'] >= $outstanding) ? 'course_fee' : 'partial_payment';

        $payment = Payment::create($validated);

        // If payment is marked as completed, update enrollment and generate invoice
        if ($validated['payment_status'] === 'Completed' && $enrollment) {
            $verificationService = app(\App\Services\PaymentVerificationService::class);
            $verificationService->verifyPayment($payment);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['student', 'course']);
        return view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $payment->load(['student', 'course', 'enrollment.course', 'enrollment.intake']);
        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('method_name')->get();

        $enrollment = $payment->enrollment;
        $enrollmentSummary = null;
        if ($enrollment) {
            $totalFee = $enrollment->effectivePrice();
            $discounts = $enrollment->totalDiscounts();
            $effectivePaid = (float) $enrollment->amount_paid + (float) $discounts;
            $enrollmentSummary = [
                'course_title' => $enrollment->course?->title ?? 'Unknown course',
                'intake_name' => $enrollment->intake?->name,
                'total_fee' => $totalFee,
                'amount_paid' => (float) $enrollment->amount_paid,
                'discounts' => $discounts,
                'effective_paid' => $effectivePaid,
                'outstanding' => max(0, $totalFee - $effectivePaid),
                'payment_status' => $enrollment->payment_status,
                'enrollment_status' => $enrollment->enrollment_status,
                'certificate_blocked' => $enrollment->certificate_blocked,
                'is_fully_paid' => $enrollment->isFullyPaid(),
            ];
        }

        return view('admin.payments.edit', compact('payment', 'paymentMethods', 'enrollmentSummary'));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:Pending,Completed,Failed,Refunded',
            'amount' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,payment_method_id',
            'transaction_id' => 'nullable|string|max:255',
            'payment_type' => 'nullable|in:registration,course_fee,partial_payment',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $wasCompleted = $payment->isCompleted();
        $payment->update($validated);

        // If payment status changed, recalculate enrollment state
        if ($payment->enrollment) {
            $verificationService = app(\App\Services\PaymentVerificationService::class);

            if ($validated['payment_status'] === 'Completed' && !$wasCompleted) {
                // Just marked as completed
                $verificationService->verifyPayment($payment);
            } elseif ($wasCompleted && $validated['payment_status'] !== 'Completed') {
                // Downgraded from Completed → recalculate from remaining payments
                $this->recalculateEnrollmentState($payment->enrollment);
            }
        }

        // A registration payment has no enrollment; its RegistrationFee is what
        // unlocks enrolling, so carry the new status over (and send the receipt
        // the first time it is marked Completed).
        if ($payment->payment_type === 'registration') {
            $verificationService = app(\App\Services\PaymentVerificationService::class);

            if ($validated['payment_status'] === 'Completed' && !$wasCompleted) {
                $verificationService->verifyPayment($payment);
            } else {
                $verificationService->syncRegistrationFee($payment);
            }
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $enrollment = $payment->enrollment;
        $wasCompleted = $payment->isCompleted();

        $payment->delete();

        // Recalculate enrollment if a completed payment was removed
        if ($wasCompleted && $enrollment) {
            $this->recalculateEnrollmentState($enrollment);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment deleted successfully.');
    }

    /**
     * Recalculate enrollment state from all remaining completed payments.
     */
    protected function recalculateEnrollmentState($enrollment): void
    {
        $verificationService = app(\App\Services\PaymentVerificationService::class);
        $verificationService->recalculateEnrollmentState($enrollment);
    }
}
