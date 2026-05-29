@extends('layouts.dashboard')

@section('title','My Courses - Edutrack LMS')
@section('page_title','My Courses')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <p class="od-eyebrow">LEARNING</p>
    <h1 class="od-h1 mb-8">My Courses</h1>

    <div class="od-card overflow-hidden" style="padding: 0;">
        <div class="p-6" style="border-bottom: 1px solid var(--od-border);">
            <h2 class="od-h3">Enrolled Courses</h2>
        </div>

        @if($enrollments->isEmpty())
            <div class="p-12 text-center">
                <div class="od-empty-icon mx-auto mb-4">
                    <i class="fas fa-book-open text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium mb-2" style="color: var(--od-fg);">No Courses Yet</h3>
                <p class="text-sm mb-6" style="color: var(--od-muted);">You haven't enrolled in any courses yet.</p>
                <a href="{{ route('courses.index') }}" class="od-btn od-btn-primary">
                    <i class="fas fa-search mr-2"></i>Browse Courses
                </a>
            </div>
        @else
            <div>
                @foreach($enrollments as $enrollment)
                    @php $firstLesson = $enrollment->course?->modules?->flatMap->lessons->first(); @endphp
                    <div class="od-course-row px-6">
                        <div class="od-course-thumb">
                            @if($enrollment->course?->thumbnail_image_url)
                                <img src="{{ $enrollment->course->thumbnail_image_url }}" alt="" />
                            @else
                                <i class="fas fa-book text-sm" style="color: var(--od-muted);"></i>
                            @endif
                        </div>
                        <div class="od-course-info">
                            <h4>{{ $enrollment->course?->title ??'Unknown' }}</h4>
                            <p class="od-meta">Enrolled {{ $enrollment->enrolled_at?->format('M d, Y') }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                @if($enrollment->enrollment_status ==='Completed')
                                    <span class="od-badge od-badge-success">Completed</span>
                                @elseif($enrollment->enrollment_status ==='In Progress')
                                    <span class="od-badge od-badge-info">In Progress</span>
                                @elseif($enrollment->enrollment_status ==='Dropped')
                                    <span class="od-badge od-badge-danger">Dropped</span>
                                @else
                                    <span class="od-badge od-badge-warn">{{ $enrollment->enrollment_status }}</span>
                                @endif
                                @if($enrollment->payment_status ==='completed')
                                    <span class="od-badge od-badge-success">Paid</span>
                                @else
                                    <span class="od-badge od-badge-warn">Payment Pending</span>
                                @endif
                            </div>
                        </div>
                        <div class="od-course-progress">
                            <span class="od-num">{{ number_format($enrollment->progress, 0) }}% complete</span>
                            <div class="od-progress-track">
                                @php $pc = $enrollment->progress >= 75 ? 'green' : ($enrollment->progress >= 40 ? 'navy' : 'accent'); @endphp
                                <div class="od-progress-fill {{ $pc }}" style="width: {{ $enrollment->progress }}%"></div>
                            </div>
                        </div>
                        <div class="od-course-action">
                            <a href="{{ $firstLesson ? route('student.learning.show', [$enrollment->course, $firstLesson]) : route('enrollments.show', $enrollment->course) }}"
                               class="od-btn od-btn-primary od-btn-sm">
                                {{ $enrollment->progress > 0 ?'Continue' :'Start' }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-4" style="border-top: 1px solid var(--od-border);">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
