@extends('layouts.app')

@section('title','Login - Edutrack LMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-auth-page">
 <div class="mx-auto w-full max-w-md">
 {{-- Logo / Header --}}
 <div class="text-center mb-6">
 <a href="{{ url('/') }}" class="inline-block mb-4">
 <img src="{{ asset('assets/images/logo.png') }}" alt="Edutrack Logo" class="h-16 w-auto mx-auto">
 </a>
 <h2 class="od-h2">Welcome back</h2>
 <p class="mt-2 text-sm od-meta">
 Sign in to access your courses and track your progress
 </p>
 </div>

 {{-- Card --}}
 <div class="od-auth-card">
 <form class="space-y-5" action="{{ route('login') }}" method="POST">
 @csrf

 {{-- Email --}}
 <div>
 <label for="email" class="od-form-label">Email address</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
 <i class="fas fa-envelope text-sm" style="color: var(--od-muted);"></i>
 </div>
 <input id="email" name="email" type="email" autocomplete="email" required
 class="od-input od-input-icon"
 placeholder="you@example.com"
 value="{{ old('email') }}">
 </div>
 @error('email')
 <p class="mt-1.5 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
 @enderror
 </div>

 {{-- Password with toggle --}}
 <div>
 <label for="password" class="od-form-label">Password</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
 <i class="fas fa-lock text-sm" style="color: var(--od-muted);"></i>
 </div>
 <input id="password" name="password" type="password" autocomplete="current-password" required
 class="od-input od-input-icon od-input-icon-right"
 placeholder="Enter your password">
 <button type="button" onclick="togglePassword('password', this)"
 class="absolute inset-y-0 right-0 pr-3 flex items-center od-meta hover:text-gray-600 focus:outline-none"
 aria-label="Toggle password visibility">
 <i class="fas fa-eye text-sm"></i>
 </button>
 </div>
 @error('password')
 <p class="mt-1.5 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
 @enderror
 </div>

 {{-- Remember & Forgot --}}
 <div class="flex items-center justify-between">
 <div class="flex items-center">
 <input id="remember" name="remember" type="checkbox"
 class="h-4 w-4 rounded cursor-pointer" style="accent-color: var(--od-navy);">
 <label for="remember" class="ml-2 block text-sm cursor-pointer" style="color: var(--od-muted);">
 Remember me
 </label>
 </div>
 <a href="{{ route('password.request') }}" class="text-sm font-medium" style="color: var(--od-navy);">
 Forgot password?
 </a>
 </div>

 {{-- Submit --}}
 <button type="submit"
 class="od-btn od-btn-primary w-full od-btn-lg">
 <i class="fas fa-sign-in-alt mr-2"></i>
 Sign in
 </button>
 </form>

 {{-- Divider --}}
 <div class="mt-6 relative">
 <div class="absolute inset-0 flex items-center">
 <div class="w-full" style="border-top: 1px solid var(--od-border);"></div>
 </div>
 <div class="relative flex justify-center text-sm">
 <span class="px-3 od-meta" style="background: var(--od-surface);">or continue with</span>
 </div>
 </div>

 {{-- Google Button --}}
 <div class="mt-5">
 <a href="{{ route('google.login') }}"
 class="od-btn od-btn-secondary w-full">
 <svg class="h-5 w-5 mr-2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
 <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
 <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
 <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
 <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
 </svg>
 Sign in with Google
 </a>
 </div>
 </div>

 {{-- Footer link --}}
 <p class="mt-6 text-center text-sm od-meta">
 Don't have an account?
 <a href="{{ route('register') }}" class="font-medium" style="color: var(--od-navy);">
 Create one now
 </a>
 </p>
 </div>
</div>

<script>
function togglePassword(inputId, btn) {
 const input = document.getElementById(inputId);
 const icon = btn.querySelector('i');
 if (input.type ==='password') {
 input.type ='text';
 icon.classList.remove('fa-eye');
 icon.classList.add('fa-eye-slash');
 } else {
 input.type ='password';
 icon.classList.remove('fa-eye-slash');
 icon.classList.add('fa-eye');
 }
}
</script>
@endsection
