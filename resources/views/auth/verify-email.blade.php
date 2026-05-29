@extends('layouts.app')

@section('title','Verify Email - Edutrack LMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-auth-page">
 <div class="mx-auto w-full max-w-md">
 <div class="text-center mb-8">
 <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-4" style="background: var(--od-navy-soft);">
 <i class="fas fa-envelope text-2xl" style="color: var(--od-navy);"></i>
 </div>
 <h2 class="od-h2">Verify Your Email</h2>
 <p class="mt-2 od-meta">Please check your email for a verification link.</p>
 </div>

 <div class="od-auth-card">
 @if(session('success'))
 <div class="mb-4 p-4 rounded-lg text-sm font-medium" style="background: var(--od-green-soft); color: var(--od-green);">
 {{ session('success') }}
 </div>
 @endif

 @if(session('warning'))
 <div class="mb-4 p-4 rounded-lg text-sm font-medium" style="background: color-mix(in oklch, var(--od-accent) 10%, transparent); color: color-mix(in oklch, var(--od-accent) 70%, black);">
 {{ session('warning') }}
 </div>
 @endif

 @if(session('verification_link'))
 <div class="mb-4 p-4 rounded-lg" style="background: var(--od-navy-soft);">
 <p class="text-sm font-medium mb-2" style="color: var(--od-navy);">Development Mode - Verification Link:</p>
 <a href="{{ session('verification_link') }}" class="text-sm break-all hover:underline" style="color: var(--od-navy);">
 {{ session('verification_link') }}
 </a>
 </div>
 @endif

 <p class="text-sm od-meta mb-6 text-center">
 Didn't receive the email? Enter your email below to request a new verification link.
 </p>

 <form action="{{ route('verification.resend') }}" method="POST">
 @csrf
 <div class="mb-4">
 <label for="email" class="sr-only">Email address</label>
 <input id="email" name="email" type="email" required
 value="{{ session('email', old('email')) }}"
 class="od-input"
 placeholder="Email address">
 @error('email')
 <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
 @enderror
 </div>

 <button type="submit" class="od-btn od-btn-primary w-full">
 Resend Verification Email
 </button>
 </form>

 <div class="mt-6 text-center">
 <a href="{{ route('login') }}" class="text-sm font-medium" style="color: var(--od-navy);">
 Back to Login
 </a>
 </div>
 </div>
 </div>
</div>
@endsection
