@extends('layouts.dashboard')

@section('title','My Certificates - Edutrack LMS')
@section('page_title','My Certificates')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <p class="od-eyebrow">ACCOMPLISHMENTS</p>
    <h1 class="od-h1 mb-8">My Certificates</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($certificates ?? [] as $certificate)
            <div class="od-card flex flex-col overflow-hidden" style="padding: 0;">
                <!-- Certificate Header -->
                <div class="p-6 text-white relative" style="background: var(--od-navy);">
                    <div class="flex items-center justify-between relative z-10">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: color-mix(in oklch, white 15%, transparent);">
                            <i class="fas fa-award text-lg" style="color: var(--od-accent);"></i>
                        </div>
                        <span class="od-badge od-badge-success">Completed</span>
                    </div>
                    <h3 class="text-lg font-bold mt-4 relative z-10" style="font-family: var(--font-display);">Certificate of Completion</h3>
                    <p class="text-sm mt-1 relative z-10 opacity-80 line-clamp-2">{{ $certificate->course?->title ?? 'Course' }}</p>
                </div>

                <!-- Certificate Details -->
                <div class="p-6 flex-1 flex flex-col">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span style="color: var(--od-muted);">Certificate #</span>
                            <span class="od-num font-medium text-xs">{{ $certificate->certificate_number }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style="color: var(--od-muted);">Issued</span>
                            <span class="font-medium">{{ $certificate->issued_at?->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style="color: var(--od-muted);">Score</span>
                            <span class="font-bold" style="color: var(--od-green);">{{ $certificate->final_score ?? 'N/A' }}%</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-5 flex gap-3" style="border-top: 1px solid var(--od-border);">
                        <a href="{{ route('certificates.preview', $certificate) }}" target="_blank" class="od-btn od-btn-secondary od-btn-sm flex-1 justify-center">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('certificates.download', $certificate) }}" target="_blank" class="od-btn od-btn-primary od-btn-sm flex-1 justify-center">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <a href="{{ route('certificates.verify', $certificate->certificate_number) }}" target="_blank" class="od-btn od-btn-ghost od-btn-sm">
                            <i class="fas fa-check-circle"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="od-card">
                    <x-empty-state icon="fa-certificate" title="No Certificates Yet" description="Complete a course to earn your first professional certificate." actionText="Browse Courses" actionRoute="courses.index" variant="od" />
                </div>
            </div>
        @endforelse
    </div>

    @if($certificates->hasPages())
        <div class="mt-6">
            {{ $certificates->links() }}
        </div>
    @endif
</div>
@endsection
