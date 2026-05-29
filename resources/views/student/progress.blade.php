@extends('layouts.dashboard')

@section('title','My Progress - Edutrack LMS')
@section('page_title','My Progress')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <p class="od-eyebrow">PROGRESS TRACKER</p>
    <h1 class="od-h1 mb-8">My Learning Progress</h1>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="od-stat-card">
            <div>
                <div class="od-stat-value od-num">{{ $totalCourses ?? 0 }}</div>
                <div class="od-stat-label">Enrolled</div>
            </div>
            <div class="od-stat-icon" style="background: var(--od-navy-soft); color: var(--od-navy);">
                <i class="fas fa-book text-sm"></i>
            </div>
        </div>
        <div class="od-stat-card">
            <div>
                <div class="od-stat-value od-num">{{ $completedCourses ?? 0 }}</div>
                <div class="od-stat-label">Completed</div>
            </div>
            <div class="od-stat-icon" style="background: var(--od-green-soft); color: var(--od-green);">
                <i class="fas fa-check-circle text-sm"></i>
            </div>
        </div>
        <div class="od-stat-card">
            <div>
                <div class="od-stat-value od-num">{{ $inProgressCourses ?? 0 }}</div>
                <div class="od-stat-label">In Progress</div>
            </div>
            <div class="od-stat-icon" style="background: var(--od-accent-soft); color: var(--od-accent);">
                <i class="fas fa-clock text-sm"></i>
            </div>
        </div>
        <div class="od-stat-card">
            <div>
                <div class="od-stat-value od-num">{{ $totalCertificates ?? 0 }}</div>
                <div class="od-stat-label">Certificates</div>
            </div>
            <div class="od-stat-icon" style="background: var(--od-navy-soft); color: var(--od-navy);">
                <i class="fas fa-certificate text-sm"></i>
            </div>
        </div>
    </div>

    <!-- Course Progress -->
    <div class="od-card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="od-h3">Course Progress</h3>
        </div>
        <div>
            @forelse($enrollments ?? [] as $enrollment)
                <div class="od-course-row">
                    <div class="od-course-thumb">
                        @if($enrollment->course?->thumbnail_image_url)
                            <img src="{{ $enrollment->course->thumbnail_image_url }}" alt="" />
                        @else
                            <i class="fas fa-book text-sm" style="color: var(--od-muted);"></i>
                        @endif
                    </div>
                    <div class="od-course-info">
                        <h4>{{ $enrollment->course->title }}</h4>
                        <p>{{ $enrollment->course->category->name ?? 'General' }}</p>
                        @if($enrollment->total_time_spent)
                            <p class="od-meta mt-0.5">{{ floor($enrollment->total_time_spent / 60) }}h {{ $enrollment->total_time_spent % 60 }}m spent</p>
                        @endif
                    </div>
                    <div class="od-course-progress">
                        <span class="od-num">{{ round($enrollment->progress ?? 0) }}% complete</span>
                        <div class="od-progress-track">
                            @php $pc = $enrollment->progress == 100 ? 'green' : ($enrollment->progress >= 50 ? 'navy' : 'accent'); @endphp
                            <div class="od-progress-fill {{ $pc }}" style="width: {{ $enrollment->progress ?? 0 }}%;"></div>
                        </div>
                    </div>
                    <div class="od-course-action">
                        <a href="{{ route('enrollments.show', $enrollment->course) }}" class="od-btn od-btn-primary od-btn-sm">Continue</a>
                    </div>
                </div>
            @empty
                <x-empty-state icon="fa-chart-line" title="No Progress Yet" description="You haven't enrolled in any courses yet. Start learning to see your progress here." actionText="Browse Courses" actionRoute="courses.index" variant="od" />
            @endforelse
        </div>
    </div>
</div>
@endsection
