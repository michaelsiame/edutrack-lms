<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('success', 'If an account with that email exists, a password reset link has been sent.');
        }

        $token = Str::random(64);

        // Invalidate any existing reset tokens for this user
        \DB::table('remember_tokens')->where('user_id', $user->id)->delete();

        // Store token in remember_tokens table
        \DB::table('remember_tokens')->insert([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(60),
            'created_at' => now(),
        ]);

        // Queue password reset email
        $resetUrl = route('password.reset', $token);
        $emailService = app(EmailQueueService::class);

        $emailService->sendUrgent(
            $user->email,
            'Password Reset - Edutrack LMS',
            view('emails.password-reset', ['user' => $user, 'resetUrl' => $resetUrl])->render()
        );

        return back()->with('success', 'If an account with that email exists, a password reset link has been sent.');
    }

    public function resetForm(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $tokenRecord = \DB::table('remember_tokens')
            ->where('token', hash('sha256', $request->token))
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord) {
            return back()->with('error', 'Invalid or expired token.');
        }

        $user = User::find($tokenRecord->user_id);

        if (!$user || $user->email !== $request->email) {
            return back()->with('error', 'Invalid email address.');
        }

        $user->update([
            'password_hash' => bcrypt($request->password),
        ]);

        // Delete used token
        \DB::table('remember_tokens')->where('id', $tokenRecord->id)->delete();

        return redirect()->route('login')->with('success', 'Password has been reset successfully.');
    }
}
