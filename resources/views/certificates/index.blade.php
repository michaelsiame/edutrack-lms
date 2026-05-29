@extends('layouts.app')

@section('title','My Certificates - Edutrack LMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="py-12" style="background: var(--od-bg); min-height: 100vh;">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="od-h1 mb-8">My Certificates</h2>

        <div class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @forelse($certificates as $certificate)
                <div class="od-card overflow-hidden" style="padding: 0;">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: var(--od-green-soft);">
                                <i class="fas fa-check text-xl" style="color: var(--od-green);"></i>
                            </div>
                            <span class="od-meta">{{ $certificate->issued_date?->format('M d, Y') }}</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-1" style="color: var(--od-fg);">{{ $certificate->course?->title ??'Unknown Course' }}</h3>
                        <p class="text-sm od-meta mb-4">Cert. #{{ $certificate->certificate_number }}</p>
                        <a href="{{ route('certificates.download', $certificate) }}" class="od-btn od-btn-primary od-btn-sm">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full od-card text-center py-12">
                    <p class="mb-2" style="color: var(--od-muted);">No certificates yet.</p>
                    <p class="text-sm" style="color: var(--od-muted);">Complete a course to earn your certificate!</p>
                    <a href="{{ route('courses.index') }}" class="od-btn od-btn-primary od-btn-sm mt-4">Browse Courses</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
