@extends('layouts.app')

@section('title','Forgot Password - Edutrack LMS')

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
 <h2 class="od-h2">Reset your password</h2>
 <p class="mt-2 text-sm od-meta">
 Enter your email and we'll send you a reset link
 </p>
 </div>

 {{-- Card --}}
 <div class="od-auth-card">
 <form class="space-y-5" action="{{ route('password.email') }}" method="POST">
 @csrf

 <div>
 <label for="email" class="od-form-label">Email address</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
 <i class="fas fa-envelope text-sm" style="color: var(--od-muted);"></i>
 </div>
 <input id="email" name="email" type="email" required
 class="od-input od-input-icon"
 placeholder="you@example.com"
 value="{{ old('email') }}">
 </div>
 @error('email')
 <p class="mt-1.5 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
 @enderror
 </div>

 <button type="submit" class="od-btn od-btn-primary w-full od-btn-lg">
 <i class="fas fa-paper-plane mr-2"></i>
 Send Reset Link
 </button>
 </form>

 <div class="mt-6 text-center">
 <a href="{{ route('login') }}" class="text-sm font-medium" style="color: var(--od-navy);">
 <i class="fas fa-arrow-left mr-1"></i>
 Back to login
 </a>
 </div>
 </div>
 </div>
</div>
@endsection
