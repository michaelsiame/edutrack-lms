<?php

namespace App\Http\Controllers;

use App\Models\LencoTransaction;
use App\Models\Payment;
use App\Models\RegistrationFee;
use App\Services\LencoPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegistrationFeeController extends Controller
{
    /**
     * Show registration fee payment page.
     */
    public function show()
    {
        $user = auth()->user();
        $feeAmount = setting('registration_fee', 150);

        // Find latest registration fee record for this user
        $fee = RegistrationFee::where('user_id', $user->id)->latest()->first();

        // If there's a pending fee with a Lenco transaction, sync status
        if ($fee && $fee->payment_status === 'pending' && $fee->reference) {
            $lencoTx = LencoTransaction::where('reference', $fee->reference)->first();
            if ($lencoTx && $lencoTx->status === 'completed' && $fee->payment_status !== 'completed') {
                $fee->update(['payment_status' => 'completed', 'verified_at' => now()]);

                // Also sync the associated Payment record
                $payment = Payment::where('transaction_id', $fee->reference)
                    ->where('payment_type', 'registration')
                    ->first();
                if ($payment && !$payment->isCompleted()) {
                    $payment->update(['payment_status' => 'Completed', 'payment_date' => now()]);
                }
            }
        }

        $hasPaid = $fee && $fee->payment_status === 'completed';

        // Refresh after potential sync
        if ($fee) {
            $fee->refresh();
        }

        return view('registration-fee', compact('fee', 'hasPaid', 'feeAmount'));
    }

    /**
     * Submit registration fee payment.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $feeAmount = setting('registration_fee', 150);
        $currency = setting('currency', 'ZMW');

        // Check if already paid
        $existing = RegistrationFee::where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->first();

        if ($existing) {
            return redirect()->route('courses.index')
                ->with('info', 'You have already paid the registration fee.');
        }

        $paymentMethod = $request->input('payment_method');

        // === AUTOMATED PAYMENT VIA LENCO ===
        if (in_array($paymentMethod, ['mobile_money', 'bank_transfer'])) {
            return $this->initiateLencoPayment($request, $user, $feeAmount, $currency, $paymentMethod);
        }

        // === MANUAL BANK DEPOSIT ===
        return $this->storeManualDeposit($request, $user, $feeAmount, $currency);
    }

    /**
     * Initiate a Lenco payment for mobile money or bank transfer.
     */
    protected function initiateLencoPayment(Request $request, $user, float $feeAmount, string $currency, string $paymentMethod)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:mobile_money,bank_transfer',
            'phone_number' => 'nullable|string|max:20',
        ]);

        // Prevent duplicate pending payments within the last 30 minutes
        $existingPendingTx = LencoTransaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(30))
            ->first();

        if ($existingPendingTx) {
            return redirect()->route('registration-fee.show')
                ->with('info', 'You already have a pending payment. Please check your phone to authorize the transaction, or wait a few minutes before trying again.');
        }

        $reference = 'REG-' . $user->id . '-' . time() . '-' . strtoupper(Str::random(6));

        $result = DB::transaction(function () use ($user, $feeAmount, $currency, $paymentMethod, $validated, $reference) {
            // Create pending payment record
            $payment = Payment::create([
                'student_id' => $user->student?->id,
                'course_id' => null,
                'enrollment_id' => null,
                'amount' => $feeAmount,
                'currency' => $currency,
                'payment_type' => 'registration',
                'payment_status' => 'Pending',
                'transaction_id' => $reference,
                'phone_number' => $validated['phone_number'] ?? $user->phone,
            ]);

            // Create pending registration fee record
            $fee = RegistrationFee::create([
                'user_id' => $user->id,
                'amount' => $feeAmount,
                'currency' => $currency,
                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,
                'reference' => $reference,
                'phone_number' => $validated['phone_number'] ?? $user->phone,
            ]);

            $service = app(LencoPaymentService::class);

            if ($paymentMethod === 'mobile_money') {
                $lencoResult = $service->initializeMobileMoneyCollection([
                    'amount' => $feeAmount,
                    'currency' => $currency,
                    'reference' => $reference,
                    'phone_number' => $validated['phone_number'] ?? $user->phone,
                    'callback_url' => route('lenco.webhook'),
                ]);
            } else {
                $lencoResult = $service->initializeBankTransferCollection([
                    'amount' => $feeAmount,
                    'currency' => $currency,
                    'reference' => $reference,
                    'email' => $user->email,
                    'phone_number' => $validated['phone_number'] ?? $user->phone,
                    'customer_name' => $user->full_name ?? $user->name,
                    'customer_first_name' => $user->first_name,
                    'customer_last_name' => $user->last_name,
                    'callback_url' => route('lenco.webhook'),
                    'redirect_url' => route('registration-fee.show'),
                ]);
            }

            if (!$lencoResult['success']) {
                $fee->update(['payment_status' => 'failed']);
                $payment->update(['payment_status' => 'Failed']);

                return [
                    'type' => 'lenco_failed',
                    'error' => $lencoResult['error'] ?? 'Unable to initiate payment. Please try again.',
                ];
            }

            // Store Lenco transaction record
            $lencoId = $lencoResult['lenco_id'] ?? $reference;
            LencoTransaction::create([
                'reference' => $reference,
                'user_id' => $user->id,
                'payment_id' => $payment->payment_id,
                'amount' => $feeAmount,
                'currency' => $currency,
                'lenco_transaction_id' => $lencoId,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'phone_number' => $validated['phone_number'] ?? $user->phone,
            ]);

            $fee->update(['lenco_transaction_id' => $lencoId]);

            return [
                'type' => 'lenco_success',
                'payment_method' => $paymentMethod,
                'auth_url' => $lencoResult['authorization_url'] ?? null,
            ];
        });

        if ($result['type'] === 'lenco_failed') {
            return redirect()->route('registration-fee.show')
                ->with('error', $result['error']);
        }

        // For bank transfer/card, redirect to authorization URL if provided
        $authUrl = $result['auth_url'] ?? null;
        if ($authUrl) {
            return redirect()->away($authUrl);
        }

        // For mobile money, show pending message
        if ($result['payment_method'] === 'mobile_money') {
            return redirect()->route('registration-fee.show')
                ->with('warning', 'Payment initiated! Please check your phone and authorize the mobile money transaction. You will receive confirmation once the payment is complete.');
        }

        // If no redirect URL, show pending page
        return redirect()->route('registration-fee.show')
            ->with('warning', 'Payment initiated. Please complete the payment using the instructions sent to your phone or email.');
    }

    /**
     * Store a manual bank deposit record.
     */
    protected function storeManualDeposit(Request $request, $user, float $feeAmount, string $currency)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:bank_deposit',
            'bank_reference' => 'required|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'deposit_date' => 'required|date',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $reference = 'REG-MANUAL-' . $user->id . '-' . time() . '-' . strtoupper(Str::random(6));

        Payment::create([
            'student_id' => $user->student?->id,
            'course_id' => null,
            'enrollment_id' => null,
            'amount' => $feeAmount,
            'currency' => $currency,
            'payment_type' => 'registration',
            'payment_status' => 'Pending',
            'transaction_id' => $reference,
            'phone_number' => $validated['phone_number'] ?? $user->phone,
            'notes' => $validated['notes'] ?? null,
        ]);

        RegistrationFee::create([
            'user_id' => $user->id,
            'amount' => $feeAmount,
            'currency' => $currency,
            'payment_status' => 'pending',
            'payment_method' => 'bank_deposit',
            'reference' => $reference,
            'bank_reference' => $validated['bank_reference'],
            'bank_name' => $validated['bank_name'] ?? null,
            'deposit_date' => $validated['deposit_date'],
            'phone_number' => $validated['phone_number'] ?? $user->phone,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('registration-fee.show')
            ->with('success', 'Your bank deposit details have been submitted. Our finance team will verify within 24-48 hours.');
    }

    /**
     * Manually check registration fee status (for users waiting on Lenco).
     */
    public function checkStatus()
    {
        $user = auth()->user();
        $fee = RegistrationFee::where('user_id', $user->id)
            ->where('payment_status', 'pending')
            ->whereNotNull('reference')
            ->latest()
            ->first();

        if (!$fee) {
            return redirect()->route('registration-fee.show');
        }

        // Try to find and poll the Lenco transaction
        $lencoTx = LencoTransaction::where('reference', $fee->reference)
            ->orWhere('lenco_transaction_id', $fee->lenco_transaction_id)
            ->first();

        if ($lencoTx) {
            $service = app(LencoPaymentService::class);
            $wasUpdated = $service->pollTransaction($lencoTx);

            if ($wasUpdated && $lencoTx->fresh()->status === 'completed') {
                return redirect()->route('registration-fee.show')
                    ->with('success', 'Payment confirmed! Your registration fee has been received.');
            }
        }

        return redirect()->route('registration-fee.show')
            ->with('info', 'Payment is still being processed. Please check again in a few minutes.');
    }
}
