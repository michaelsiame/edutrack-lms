@extends('layouts.app')

@section('title','Payment Failed - Edutrack LMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4" style="background: var(--od-bg);">
    <div class="max-w-md w-full text-center">
        <div class="mb-6">
            <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center" style="background: color-mix(in oklch, var(--od-danger) 10%, transparent);">
                <i class="fas fa-times text-4xl" style="color: var(--od-danger);"></i>
            </div>
        </div>

        <p class="od-eyebrow mb-2">PAYMENT ERROR</p>
        <h1 class="od-h1 mb-3">Payment Failed</h1>
        <p class="od-meta mb-8">{{ $error }}</p>

        @if($course)
            <div class="od-card p-6 mb-6">
                <h3 class="font-semibold" style="color: var(--od-fg);">{{ $course->title }}</h3>
                <p class="text-sm mb-4 od-meta">Your enrollment is still active. Please try the payment again.</p>
                <a href="{{ route('checkout.show', $course) }}"
                    class="od-btn od-btn-primary block text-center">
                    Try Again
                </a>
            </div>
        @endif

        <div class="space-y-3">
            <a href="{{ route('student.payments') }}" class="block font-medium" style="color: var(--od-navy);">
                View Payment History
            </a>
            <a href="{{ route('courses.index') }}" class="block od-meta">
                Browse Courses
            </a>
        </div>
    </div>
</div>
@endsection
