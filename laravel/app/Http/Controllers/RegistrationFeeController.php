<?php

namespace App\Http\Controllers;

use App\Models\RegistrationFee;
use Illuminate\Http\Request;

class RegistrationFeeController extends Controller
{
    /**
     * Show registration fee payment page.
     */
    public function show()
    {
        $user = auth()->user();
        $fee = RegistrationFee::where('user_id', $user->id)->latest()->first();
        $hasPaid = $fee && $fee->payment_status === 'completed';

        return view('registration-fee', compact('fee', 'hasPaid'));
    }

    /**
     * Submit registration fee payment (manual/bank transfer).
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Check if already paid
        $existing = RegistrationFee::where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->first();

        if ($existing) {
            return redirect()->route('courses.index')
                ->with('info', 'You have already paid the registration fee.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|in:bank_transfer,bank_deposit,mobile_money',
            'bank_reference' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'deposit_date' => 'nullable|date',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        RegistrationFee::create([
            'user_id' => $user->id,
            'amount' => 150.00,
            'currency' => 'ZMW',
            'payment_status' => 'pending',
            'payment_method' => $validated['payment_method'],
            'bank_reference' => $validated['bank_reference'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'deposit_date' => $validated['deposit_date'] ?? null,
            'phone_number' => $validated['phone_number'] ?? $user->phone,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('registration-fee.show')
            ->with('success', 'Your registration fee payment has been submitted and is pending verification. You will be notified once approved.');
    }
}
