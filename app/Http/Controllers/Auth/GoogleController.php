<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth callback failed', [
                'error' => $e->getMessage(),
                'request' => request()->only(['error', 'error_description', 'state']),
            ]);
            return redirect()->route('login')->with('error', 'Google authentication failed. Please try again or use email login.');
        }

        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if ($user) {
            // Update google_id if not set
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->id]);
            }
        } else {
            // Create new user
            $user = User::create([
                'first_name' => $googleUser->user['given_name'] ?? explode(' ', $googleUser->name)[0],
                'last_name' => $googleUser->user['family_name'] ?? (explode(' ', $googleUser->name)[1] ?? ''),
                'username' => $googleUser->email,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar_url' => $googleUser->avatar,
                'password_hash' => bcrypt(uniqid()),
                'status' => 'active',
                'email_verified' => true,
            ]);

            // Assign student role
            UserRole::create([
                'user_id' => $user->id,
                'role_id' => Role::STUDENT, // Student
            ]);
        }

        if ($user->status !== 'active') {
            return redirect()->route('login')
                ->with('error', 'Your account has been suspended. Please contact support.');
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
