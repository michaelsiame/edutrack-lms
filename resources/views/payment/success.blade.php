@extends('layouts.app')

@section('title','Payment Successful - Edutrack LMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4" style="background: var(--od-bg);">
    <div class="max-w-md w-full text-center">
        <div class="mb-6">
            <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center" style="background: var(--od-green-soft);">
                <i class="fas fa-check text-4xl" style="color: var(--od-green);"></i>
            </div>
        </div>

        <p class="od-eyebrow mb-2">PAYMENT CONFIRMED</p>
        <h1 class="od-h1 mb-3">Payment Successful!</h1>
        <p class="od-meta mb-8">Your payment has been received and is being processed. You will receive a confirmation email shortly.</p>

        @if($course)
            <div class="od-card p-6 mb-6">
                <h3 class="font-semibold" style="color: var(--od-fg);">{{ $course->title }}</h3>
                <p class="text-sm mb-4 od-meta">You can now access your course content.</p>
                <a href="{{ route('enrollments.show', $course) }}"
                    class="od-btn od-btn-primary block text-center">
                    Start Learning
                </a>
            </div>
        @endif

        <div class="space-y-3">
            <a href="{{ route('enrollments.index') }}" class="block font-medium" style="color: var(--od-navy);">
                View My Courses
            </a>
            <a href="{{ route('courses.index') }}" class="block od-meta">
                Browse More Courses
            </a>
        </div>
    </div>
</div>
@endsection
