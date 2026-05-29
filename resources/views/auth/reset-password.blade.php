@extends('layouts.app')

@section('title','Reset Password - Edutrack LMS')

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
 <h2 class="od-h2">Set new password</h2>
 <p class="mt-2 text-sm od-meta">
 Create a strong password for your account
 </p>
 </div>

 {{-- Card --}}
 <div class="od-auth-card">
 <form class="space-y-5" action="{{ route('password.update') }}" method="POST">
 @csrf
 <input type="hidden" name="token" value="{{ $token }}">

 <div>
 <label for="email" class="od-form-label">Email</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
 <i class="fas fa-envelope text-sm" style="color: var(--od-muted);"></i>
 </div>
 <input id="email" name="email" type="email" required
 class="od-input od-input-icon"
 value="{{ old('email') }}">
 </div>
 @error('email')
 <p class="mt-1.5 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
 @enderror
 </div>

 <div>
 <label for="password" class="od-form-label">New Password</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
 <i class="fas fa-lock text-sm" style="color: var(--od-muted);"></i>
 </div>
 <input id="password" name="password" type="password" required
 class="od-input od-input-icon od-input-icon-right"
 placeholder="Min. 8 characters">
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

 <div>
 <label for="password_confirmation" class="od-form-label">Confirm Password</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
 <i class="fas fa-lock text-sm" style="color: var(--od-muted);"></i>
 </div>
 <input id="password_confirmation" name="password_confirmation" type="password" required
 class="od-input od-input-icon od-input-icon-right"
 placeholder="Repeat your password">
 <button type="button" onclick="togglePassword('password_confirmation', this)"
 class="absolute inset-y-0 right-0 pr-3 flex items-center od-meta hover:text-gray-600 focus:outline-none"
 aria-label="Toggle password visibility">
 <i class="fas fa-eye text-sm"></i>
 </button>
 </div>
 </div>

 <button type="submit" class="od-btn od-btn-primary w-full od-btn-lg">
 <i class="fas fa-check mr-2"></i>
 Reset Password
 </button>
 </form>
 </div>
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
