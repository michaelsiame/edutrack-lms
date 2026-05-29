@extends('layouts.dashboard')

@section('title', $enrollment->course->title .' - Edutrack LMS')
@section('page_title', $enrollment->course->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="mb-6">
        <x-back-link route="enrollments.index" label="Back to My Courses" variant="od" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Course Header -->
            <div class="od-card">
                <div class="flex flex-col md:flex-row gap-6">
                    @if($enrollment->course->thumbnail_image_url)
                        <img src="{{ $enrollment->course->thumbnail_image_url }}" alt="{{ $enrollment->course->title }}" class="w-full md:w-48 h-32 object-cover rounded-xl">
                    @else
                        <div class="w-full md:w-48 h-32 rounded-xl flex items-center justify-center" style="background: var(--od-fg-soft);">
                            <i class="fas fa-book text-4xl" style="color: var(--od-muted);"></i>
                        </div>
                    @endif
                    <div class="flex-1">
                        <h1 class="od-h2">{{ $enrollment->course->title }}</h1>
                        <p class="od-meta mt-1">{{ $enrollment->course->category->name ??'General' }}</p>
                        <div class="flex items-center mt-3 space-x-4 text-sm" style="color: var(--od-muted);">
                            <span><i class="fas fa-chalkboard-teacher mr-1"></i> {{ $enrollment->course->instructor->user->full_name ?? $enrollment->course->instructor->user->username ??'TBA' }}</span>
                            <span><i class="fas fa-clock mr-1"></i> {{ $enrollment->course->duration_weeks ??'N/A' }} weeks</span>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span style="color: var(--od-muted);">Progress</span>
                                <span class="font-semibold od-num" style="color: var(--od-navy);">{{ $enrollment->progress ?? 0 }}%</span>
                            </div>
                            <div class="od-progress-track">
                                @php $pc = ($enrollment->progress ?? 0) >= 75 ? 'green' : (($enrollment->progress ?? 0) >= 40 ? 'navy' : 'accent'); @endphp
                                <div class="od-progress-fill {{ $pc }}" style="width: {{ $enrollment->progress ?? 0 }}%"></div>
                            </div>
                        </div>

                        @php
                            $firstLesson = $enrollment->course->modules->flatMap->lessons->first();
                        @endphp
                        @if($firstLesson)
                            <a href="{{ route('student.learning.show', [$enrollment->course, $firstLesson]) }}"
                               class="od-btn od-btn-primary mt-4">
                                <i class="fas fa-play-circle mr-2"></i>
                                {{ $enrollment->progress > 0 ?'Continue Learning' :'Start Learning' }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Course Content -->
            <div class="od-card">
                <h2 class="od-h3 mb-4">Course Content</h2>
                <div class="space-y-4">
                    @forelse($enrollment->course->modules ?? [] as $module)
                        <div class="rounded-xl overflow-hidden" style="border: 1px solid var(--od-border);">
                            <div class="px-4 py-3 font-medium" style="background: var(--od-fg-soft); color: var(--od-fg);">
                                {{ $module->title }}
                            </div>
                            <div style="border-top: 1px solid var(--od-border);">
                                @foreach($module->lessons ?? [] as $lesson)
                                    <a href="{{ route('student.learning.show', [$enrollment->course, $lesson]) }}" class="flex items-center px-4 py-3 transition-colors hover:bg-gray-50" style="color: var(--od-fg);">
                                        <div class="mr-3">
                                            @if($lesson->is_completed ?? false)
                                                <i class="fas fa-check-circle" style="color: var(--od-green);"></i>
                                            @else
                                                <i class="far fa-circle" style="color: var(--od-muted);"></i>
                                            @endif
                                        </div>
                                        <span class="flex-1 text-sm">{{ $lesson->title }}</span>
                                        @if($lesson->duration_minutes)
                                            <span class="text-sm od-meta">{{ $lesson->duration_minutes }} min</span>
                                        @endif
                                    </a>
                                    @if(!$loop->last)
                                        <div style="border-top: 1px solid var(--od-border);"></div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-8" style="color: var(--od-muted);">No modules available yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Enrollment Status -->
            <div class="od-card">
                <h3 class="od-h3 mb-4">Enrollment Details</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span style="color: var(--od-muted);">Status</span>
                        @if($enrollment->enrollment_status ==='completed')
                            <span class="od-badge od-badge-success">Completed</span>
                        @else
                            <span class="od-badge od-badge-info">{{ ucfirst($enrollment->enrollment_status) }}</span>
                        @endif
                    </div>
                    <div class="flex justify-between">
                        <span style="color: var(--od-muted);">Enrolled On</span>
                        <span class="font-medium">{{ $enrollment->enrolled_at?->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color: var(--od-muted);">Completed On</span>
                        <span class="font-medium">{{ $enrollment->completion_date?->format('M d, Y') ??'In Progress' }}</span>
                    </div>
                </div>
            </div>

            <!-- Course Community -->
            <div class="od-card">
                <h3 class="od-h3 mb-4">Course Community</h3>
                <div class="space-y-2">
                    <a href="{{ route('student.discussions.index', $enrollment->course) }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors" style="border: 1px solid var(--od-border); color: var(--od-fg);" onmouseover="this.style.background='var(--od-fg-soft)'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-comments w-5" style="color: var(--od-navy);"></i> Discussions
                    </a>
                    <a href="{{ route('student.live-sessions.index', $enrollment->course) }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors" style="border: 1px solid var(--od-border); color: var(--od-fg);" onmouseover="this.style.background='var(--od-fg-soft)'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-video w-5" style="color: var(--od-danger);"></i> Live Sessions
                    </a>
                </div>
            </div>

            <!-- Certificate -->
            @if($enrollment->enrollment_status ==='completed')
                <div class="od-card">
                    <h3 class="od-h3 mb-4">Certificate</h3>
                    <p class="text-sm mb-4" style="color: var(--od-muted);">Congratulations! You have earned a certificate for this course.</p>
                    <a href="{{ route('certificates.index') }}" class="od-btn od-btn-primary w-full justify-center">
                        <i class="fas fa-certificate mr-2"></i> View Certificate
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
