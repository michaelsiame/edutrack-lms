@extends('layouts.dashboard')

@section('title','My Assignments - Edutrack LMS')
@section('page_title','My Assignments')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <x-page-header title="My Assignments" subtitle="Track and submit assignments across your enrolled courses" variant="od" />

    <div class="od-card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="od-h3">Assignments</h3>
        </div>

        @if($assignments->isEmpty())
            <x-empty-state icon="fa-clipboard-list" title="No Assignments" description="You don't have any assignments in your enrolled courses." variant="od" />
        @else
            <div>
                @foreach($assignments as $assignment)
                    <div class="od-course-row">
                        <div class="od-course-thumb" style="background: var(--od-accent-soft);">
                            <i class="fas fa-tasks text-sm" style="color: var(--od-accent);"></i>
                        </div>
                        <div class="od-course-info">
                            <div class="flex items-center gap-2">
                                <h4>{{ $assignment->title }}</h4>
                                @if($assignment->submission?->is_late)
                                    <span class="od-badge od-badge-danger">Late</span>
                                @endif
                            </div>
                            <p>
                                {{ $assignment->course->title }}
                                @if($assignment->due_date)
                                    <span class="mx-1">&bull;</span>
                                    @if($assignment->due_date->isPast() && !$assignment->submission)
                                        <span style="color: var(--od-danger);">Overdue {{ $assignment->due_date->diffForHumans() }}</span>
                                    @else
                                        <span>Due {{ $assignment->due_date->format('M d, Y') }}</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            @if($assignment->submission)
                                @if($assignment->submission->status === 'Graded')
                                    <span class="od-badge od-badge-success">Graded</span>
                                @elseif($assignment->submission->status === 'Submitted')
                                    <span class="od-badge od-badge-info">Submitted</span>
                                @endif
                                @if($assignment->submission->points_earned !== null)
                                    <span class="od-num text-sm font-bold">{{ $assignment->submission->points_earned }}/{{ $assignment->max_points }}</span>
                                @endif
                            @else
                                <span class="od-badge od-badge-warn">Not Submitted</span>
                            @endif
                            <a href="{{ route('student.assignments.show', [$assignment->course, $assignment]) }}" class="od-btn od-btn-primary od-btn-sm">
                                View <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
