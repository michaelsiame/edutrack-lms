<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LencoTransaction;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\LencoPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Show checkout page for a course.
     */
    public function show(Course $course)
    {
        $user = auth()->user();

        // Verify user is enrolled
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->with('paymentPlan')
            ->first();

        if (!$enrollment) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Please enroll in this course first.');
        }

        // If already fully paid
        if ($enrollment->isFullyPaid()) {
            return redirect()->route('enrollments.show', $course)
                ->with('info', 'You have already paid for this course.');
        }

        $price = $course->discount_price ?? $course->price;
        $totalPaid = $enrollment->amount_paid;
        $balance = $price - $totalPaid;
        $minDeposit = $price * 0.30;

        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return view('checkout', compact('course', 'enrollment', 'price', 'totalPaid', 'balance', 'minDeposit', 'paymentMethods'));
    }

    /**
     * Process payment for a course.
     */
    public function process(Request $request, Course $course)
    {
        $user = auth()->user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->with('paymentPlan')
            ->firstOrFail();

        $price = $course->discount_price ?? $course->price;
        $totalPaid = $enrollment->amount_paid;
        $balance = $price - $totalPaid;

        $validated = $request->validate([
            'payment_method' => 'required|string|in:lenco,bank_transfer,mobile_money',
            'amount' => 'required|numeric|min:1|max:' . $balance,
            'phone_number' => 'nullable|string|max:20',
        ]);

        $amount = (float) $validated['amount'];
        $paymentMethod = $validated['payment_method'];
        $reference = 'EDU-' . $user->id . '-' . $course->id . '-' . time();

        // Create pending payment record
        $payment = Payment::create([
            'student_id' => $user->id,
            'course_id' => $course->id,
            'enrollment_id' => $enrollment->id,
            'amount' => $amount,
            'currency' => 'ZMW',
            'payment_type' => $amount >= $balance ? 'course_fee' : 'partial_payment',
            'payment_status' => 'Pending',
            'transaction_id' => $reference,
            'phone_number' => $validated['phone_number'] ?? $user->phone,
        ]);

        if ($paymentMethod === 'lenco') {
            $service = app(LencoPaymentService::class);

            $result = $service->initializePayment([
                'amount' => $amount,
                'currency' => 'ZMW',
                'description' => 'Payment for ' . $course->title,
                'callback_url' => route('lenco.webhook'),
                'reference' => $reference,
                'email' => $user->email,
                'phone_number' => $validated['phone_number'] ?? $user->phone,
                'customer_name' => $user->full_name,
            ]);

            if ($result['success']) {
                // Store Lenco transaction reference
                LencoTransaction::create([
                    'lenco_transaction_id' => $result['data']['id'] ?? $reference,
                    'payment_id' => $payment->payment_id,
                    'enrollment_id' => $enrollment->id,
                    'amount' => $amount,
                    'currency' => 'ZMW',
                    'status' => 'pending',
                    'phone_number' => $validated['phone_number'] ?? $user->phone,
                ]);

                // Redirect to Lenco checkout URL if provided
                if (isset($result['data']['checkoutUrl'])) {
                    return redirect()->away($result['data']['checkoutUrl']);
                }

                return redirect()->route('payment.success', ['course' => $course->slug])
                    ->with('success', 'Payment initiated successfully. Please complete the payment.');
            }

            // Mark payment as failed
            $payment->update(['payment_status' => 'Failed']);

            return redirect()->route('payment.failed', ['course' => $course->slug])
                ->with('error', $result['error'] ?? 'Payment initialization failed. Please try again.');
        }

        // For bank transfer and mobile money, show instructions
        return redirect()->route('checkout.show', $course)
            ->with('info', 'Please follow the payment instructions sent to your email. Your enrollment will be activated once payment is verified.');
    }

    /**
     * Payment success page.
     */
    public function success(Request $request)
    {
        $courseSlug = $request->query('course');
        $course = $courseSlug ? Course::where('slug', $courseSlug)->first() : null;

        return view('payment.success', compact('course'));
    }

    /**
     * Payment failed page.
     */
    public function failed(Request $request)
    {
        $courseSlug = $request->query('course');
        $course = $courseSlug ? Course::where('slug', $courseSlug)->first() : null;
        $error = $request->query('error', session('error', 'Payment could not be completed.'));

        return view('payment.failed', compact('course', 'error'));
    }
}
