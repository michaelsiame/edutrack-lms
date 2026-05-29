<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LencoTransaction;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Promotion;
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

        // Pre-check promotion to calculate effective balance for validation
        $promotion = null;
        $promotionDiscount = 0;
        $promotionId = $request->input('promotion_id');
        if ($promotionId) {
            $promotion = Promotion::find($promotionId);
            if ($promotion && $promotion->isValid() && $promotion->appliesToCourse($course->id)) {
                $promotionDiscount = $promotion->calculateDiscount($balance);
            }
        }
        $effectiveBalance = max(0, $balance - $promotionDiscount);
        $minDeposit = $effectiveBalance * 0.30;
        $minAmount = $effectiveBalance > 0 ? min($minDeposit, $effectiveBalance) : 0;

        $validated = $request->validate([
            'payment_method' => 'required|string|in:lenco,bank_transfer,mobile_money',
            'amount' => 'required|numeric|min:' . $minAmount . '|max:' . $effectiveBalance,
            'phone_number' => 'nullable|string|max:20',
            'promotion_id' => 'nullable|integer|exists:promotions,id',
        ]);

        $amount = (float) $validated['amount'];
        $paymentMethod = $validated['payment_method'];
        $reference = 'EDU-' . $user->id . '-' . $course->id . '-' . time();

        // Map payment method string to payment_method_id
        $methodMap = [
            'mobile_money' => 2,   // Mobile Money
            'lenco' => 6,          // Lenco Bank Transfer
            'bank_transfer' => 3,  // Bank Transfer (manual)
        ];
        $paymentMethodId = $methodMap[$paymentMethod] ?? null;

        // Determine discount and payment type based on whether this completes the payment
        $discount = 0;
        if ($promotion && $amount >= $effectiveBalance) {
            $discount = $promotionDiscount;
            $promotion->increment('used_count');
        }

        $isFullPayment = $amount >= $effectiveBalance;

        // Create pending payment record
        $payment = Payment::create([
            'student_id' => $user->student?->id ?? $user->id,
            'course_id' => $course->id,
            'enrollment_id' => $enrollment->id,
            'payment_method_id' => $paymentMethodId,
            'amount' => $amount,
            'currency' => 'ZMW',
            'payment_type' => $isFullPayment ? 'course_fee' : 'partial_payment',
            'payment_status' => 'Pending',
            'transaction_id' => $reference,
            'phone_number' => $validated['phone_number'] ?? $user->phone,
            'promotion_id' => $promotion?->id,
            'discount_amount' => $discount,
        ]);

        // Lenco v2 collections
        if (in_array($paymentMethod, ['lenco', 'mobile_money'])) {
            // Prevent duplicate pending payments within the last 30 minutes
            $existingPendingTx = LencoTransaction::where('enrollment_id', $enrollment->id)
                ->where('status', 'pending')
                ->where('created_at', '>=', now()->subMinutes(30))
                ->first();

            if ($existingPendingTx) {
                return redirect()->route('payment.success', ['course' => $course->slug])
                    ->with('info', 'You already have a pending payment. Please check your phone to authorize the transaction, or wait a few minutes before trying again.');
            }

            $service = app(LencoPaymentService::class);

            if ($paymentMethod === 'mobile_money') {
                $result = $service->initializeMobileMoneyCollection([
                    'amount' => $amount,
                    'currency' => 'ZMW',
                    'reference' => $reference,
                    'phone_number' => $validated['phone_number'] ?? $user->phone,
                    'callback_url' => route('lenco.webhook'),
                ]);
            } else {
                $result = $service->initializeBankTransferCollection([
                    'amount' => $amount,
                    'currency' => 'ZMW',
                    'reference' => $reference,
                    'email' => $user->email,
                    'phone_number' => $validated['phone_number'] ?? $user->phone,
                    'customer_name' => $user->full_name,
                    'customer_first_name' => $user->first_name,
                    'customer_last_name' => $user->last_name,
                    'callback_url' => route('lenco.webhook'),
                    'redirect_url' => route('payment.success', ['course' => $course->slug]),
                ]);
            }

            if ($result['success']) {
                // Store Lenco transaction reference
                LencoTransaction::create([
                    'reference' => $reference,
                    'user_id' => $user->id,
                    'payment_id' => $payment->payment_id,
                    'enrollment_id' => $enrollment->id,
                    'course_id' => $course->id,
                    'amount' => $amount,
                    'currency' => 'ZMW',
                    'lenco_transaction_id' => $result['lenco_id'] ?? $reference,
                    'status' => 'pending',
                    'payment_method' => $paymentMethod,
                    'phone_number' => $validated['phone_number'] ?? $user->phone,
                ]);

                // For bank transfer/card, redirect to authorization URL if provided
                $authUrl = $result['authorization_url'] ?? null;
                if ($authUrl) {
                    return redirect()->away($authUrl);
                }

                // For mobile money, show pending page
                if ($paymentMethod === 'mobile_money') {
                    return redirect()->route('payment.success', ['course' => $course->slug])
                        ->with('success', 'Payment initiated! Please check your phone and authorize the mobile money transaction. You will receive confirmation once the payment is complete.');
                }

                return redirect()->route('payment.success', ['course' => $course->slug])
                    ->with('success', 'Payment initiated successfully. Please complete the payment.');
            }

            // Mark payment as failed
            $payment->update(['payment_status' => 'Failed']);

            return redirect()->route('payment.failed', ['course' => $course->slug])
                ->with('error', $result['error'] ?? 'Payment initialization failed. Please try again.');
        }

        // For manual bank transfer only, show instructions
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
