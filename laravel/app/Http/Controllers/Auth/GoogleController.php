<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            return redirect()->route('login')->with('error', 'Google authentication failed.');
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
                'role_id' => 4, // Student
            ]);
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
