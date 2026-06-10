<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()->min(8)->mixedCase()->numbers()],
        ]);

        $verificationToken = Str::random(64);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password_hash' => Hash::make($request->password),
            'status' => 'active',
            'email_verification_token' => $verificationToken,
            'email_verification_expires' => now()->addHours(24),
            'email_verified' => false,
        ]);

        // Assign student role by default
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => 4, // Student
        ]);

        event(new Registered($user));

        $emailService = app(\App\Services\EmailQueueService::class);

        // Welcome email
        $emailService->sendTemplated($user->email, 'Welcome', [
            'name' => $user->full_name,
            'email' => $user->email,
            'login_url' => route('login'),
        ]);
        $emailService->sendNotification($user->id, 'Welcome to Edutrack!', 'Your account has been created successfully.', 'welcome');

        // Verification email
        $subject = 'Verify your email address';
        $body = view('emails.verify-email', ['user' => $user, 'token' => $verificationToken])->render();
        $emailService->queue($user->email, $subject, $body);

        return redirect()->route('verification.notice')
            ->with('email', $user->email);
    }
}
