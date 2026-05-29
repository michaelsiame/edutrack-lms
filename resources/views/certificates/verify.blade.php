@extends('layouts.app')

@section('title','Verify Certificate - Edutrack LMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="py-12" style="background: var(--od-bg); min-height: 100vh;">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="od-card text-center py-10">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4" style="background: var(--od-green-soft);">
                <i class="fas fa-check text-2xl" style="color: var(--od-green);"></i>
            </div>
            <h2 class="od-h2 mb-2">Certificate Verified</h2>
            <p class="od-lead mb-8" style="margin: 0 auto 32px;">This certificate is authentic and issued by Edutrack Computer Training College.</p>

            <div class="p-6 text-left max-w-lg mx-auto rounded-xl" style="background: var(--od-fg-soft);">
                <div class="grid grid-cols-1 gap-4">
                    <div class="flex justify-between pb-2" style="border-bottom: 1px solid var(--od-border);">
                        <span class="text-sm" style="color: var(--od-muted);">Certificate Number</span>
                        <span class="text-sm font-medium od-num" style="color: var(--od-fg);">{{ $certificate->certificate_number }}</span>
                    </div>
                    <div class="flex justify-between pb-2" style="border-bottom: 1px solid var(--od-border);">
                        <span class="text-sm" style="color: var(--od-muted);">Student Name</span>
                        <span class="text-sm font-medium" style="color: var(--od-fg);">{{ $certificate->user?->full_name ??'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between pb-2" style="border-bottom: 1px solid var(--od-border);">
                        <span class="text-sm" style="color: var(--od-muted);">Course</span>
                        <span class="text-sm font-medium" style="color: var(--od-fg);">{{ $certificate->course?->title ??'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between pb-2" style="border-bottom: 1px solid var(--od-border);">
                        <span class="text-sm" style="color: var(--od-muted);">Issue Date</span>
                        <span class="text-sm font-medium" style="color: var(--od-fg);">{{ $certificate->issued_date?->format('F d, Y') }}</span>
                    </div>
                    @if($certificate->final_score)
                    <div class="flex justify-between">
                        <span class="text-sm" style="color: var(--od-muted);">Final Score</span>
                        <span class="text-sm font-medium" style="color: var(--od-green);">{{ $certificate->final_score }}%</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-8">
                <a href="{{ route('home') }}" class="od-btn od-btn-secondary">Back to Home</a>
            </div>
        </div>
    </div>
</div>
@endsection
