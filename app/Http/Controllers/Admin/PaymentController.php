<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
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
        $students = \App\Models\Student::with('user')
            ->whereHas('user', function ($q) {
                $q->whereHas('roles', function ($r) {
                    $r->where('role_id', 4); // Student role
                });
            })
            ->orWhereDoesntHave('user.roles')
            ->orderBy('id')
            ->get();
        $courses = \App\Models\Course::orderBy('title')->get(['id', 'title']);
        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('method_name')->get();
        return view('admin.payments.create', compact('students', 'courses', 'paymentMethods'));
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
        $validated['payment_type'] = $validated['payment_type'] ?? 'course_fee';

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
        $payment->load(['student', 'course']);
        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('method_name')->get();
        return view('admin.payments.edit', compact('payment', 'paymentMethods'));
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
        $totalPaid = \App\Models\Payment::where('enrollment_id', $enrollment->id)
            ->where('payment_status', 'Completed')
            ->sum('amount');

        $coursePrice = $enrollment->course?->discount_price ?? $enrollment->course?->price ?? 0;
        $isFullyPaid = $totalPaid >= $coursePrice;
        $percentagePaid = $coursePrice > 0 ? ($totalPaid / $coursePrice) * 100 : 100;

        $enrollmentStatus = $enrollment->enrollment_status;
        if ($percentagePaid >= 30 && in_array($enrollmentStatus, ['Enrolled'])) {
            $enrollmentStatus = 'In Progress';
        }

        $enrollment->update([
            'amount_paid' => $totalPaid,
            'payment_status' => $isFullyPaid ? 'completed' : ($totalPaid > 0 ? 'partial' : 'pending'),
            'enrollment_status' => $enrollmentStatus,
            'certificate_blocked' => !$isFullyPaid,
        ]);

        $paymentPlan = $enrollment->paymentPlan;
        if ($paymentPlan) {
            $paymentPlan->update([
                'total_paid' => $totalPaid,
                'payment_status' => $isFullyPaid ? 'completed' : ($totalPaid > 0 ? 'partial' : 'pending'),
            ]);
        }
    }
}
