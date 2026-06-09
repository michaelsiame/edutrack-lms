@extends('layouts.app')

@section('title','My Certificates - Edutrack LMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
<style>
.cert-card {
    position: relative;
    overflow: hidden;
}
.cert-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, var(--od-navy) 0%, var(--od-accent) 100%);
}
.cert-seal {
    width: 48px; height: 48px;
    opacity: 0.15;
    position: absolute;
    top: 12px; right: 12px;
}
.cert-seal img {
    width: 100%; height: 100%;
    object-fit: contain;
}
</style>
@endpush

@section('content')
<div class="py-12" style="background: var(--od-bg); min-height: 100vh;">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 mb-8">
            <img src="{{ asset('assets/images/certificate-seal.png') }}" alt="" class="w-12 h-12 opacity-60">
            <h2 class="od-h1">My Certificates</h2>
        </div>

        <div class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @forelse($certificates as $certificate)
                <div class="od-card cert-card overflow-hidden" style="padding: 0;">
                    <div class="cert-seal">
                        <img src="{{ asset('assets/images/certificate-seal.png') }}" alt="">
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: var(--od-green-soft);">
                                <i class="fas fa-award text-xl" style="color: var(--od-green);"></i>
                            </div>
                            <span class="od-meta">{{ $certificate->issued_date?->format('M d, Y') }}</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-1" style="color: var(--od-fg);">{{ $certificate->course?->title ??'Unknown Course' }}</h3>
                        <p class="text-sm od-meta mb-1">Cert. #{{ $certificate->certificate_number }}</p>
                        @if($certificate->final_score)
                        <p class="text-sm mb-4" style="color: var(--od-navy);">
                            <i class="fas fa-star mr-1" style="color: var(--od-accent);"></i>
                            Score: {{ $certificate->final_score }}%
                            @if($certificate->classification)
                                <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-medium" style="background: var(--od-accent-soft); color: var(--od-accent);">{{ $certificate->classification }}</span>
                            @endif
                        </p>
                        @else
                        <p class="text-sm mb-4" style="color: var(--od-muted);">TEVETA Accredited Certificate</p>
                        @endif
                        <div class="flex gap-2">
                            <a href="{{ route('certificates.preview', $certificate) }}" class="od-btn od-btn-secondary od-btn-sm flex-1 text-center">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ route('certificates.download', $certificate) }}" target="_blank" class="od-btn od-btn-primary od-btn-sm flex-1 text-center">
                                <i class="fas fa-download"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full od-card text-center py-12">
                    <img src="{{ asset('assets/images/certificate-seal.png') }}" alt="" class="w-16 h-16 mx-auto mb-4 opacity-20">
                    <p class="mb-2" style="color: var(--od-muted);">No certificates yet.</p>
                    <p class="text-sm" style="color: var(--od-muted);">Complete a course to earn your TEVETA-accredited certificate!</p>
                    <a href="{{ route('courses.index') }}" class="od-btn od-btn-primary od-btn-sm mt-4">Browse Courses</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
