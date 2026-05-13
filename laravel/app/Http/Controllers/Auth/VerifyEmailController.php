<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Show email verification notice.
     */
    public function notice()
    {
        return view('auth.verify-email');
    }

    /**
     * Verify email with token.
     */
    public function verify(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('login')
                ->with('error', 'Invalid verification link.');
        }

        $user = User::where('email_verification_token', $token)
            ->where('email_verification_expires', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired verification link. Please request a new one.');
        }

        $user->update([
            'email_verified' => true,
            'email_verification_token' => null,
            'email_verification_expires' => null,
        ]);

        return redirect()->route('login')
            ->with('success', 'Your email has been verified! You can now log in.');
    }

    /**
     * Resend verification email.
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)
            ->where('email_verified', false)
            ->first();

        if (!$user) {
            return back()->with('info', 'If this email exists and is not verified, a new verification link has been sent.');
        }

        // Generate new token
        $token = bin2hex(random_bytes(32));
        $user->update([
            'email_verification_token' => $token,
            'email_verification_expires' => now()->addHours(24),
        ]);

        // TODO: Send verification email
        // For now, just show the token in development
        if (app()->environment('local', 'development')) {
            return back()->with('success', 'Verification link: ' . route('verification.verify', ['token' => $token]));
        }

        return back()->with('success', 'A new verification link has been sent to your email.');
    }
}
