@extends('layouts.dashboard')

@section('title', 'My Analytics - Edutrack LMS')
@section('page_title', 'My Analytics')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="max-w-5xl mx-auto">
        <h2 class="od-h2 mb-6">My Learning Analytics</h2>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="od-stat-card">
                <div>
                    <p class="od-stat-label">Courses</p>
                    <p class="od-stat-value od-num">{{ $totalCourses }}</p>
                </div>
                <div class="od-stat-icon" style="background: var(--od-navy-soft); color: var(--od-navy);">
                    <i class="fas fa-book text-lg"></i>
                </div>
            </div>
            <div class="od-stat-card">
                <div>
                    <p class="od-stat-label">Completed</p>
                    <p class="od-stat-value od-num">{{ $completedCourses }}</p>
                </div>
                <div class="od-stat-icon" style="background: var(--od-green-soft); color: var(--od-green);">
                    <i class="fas fa-check-circle text-lg"></i>
                </div>
            </div>
            <div class="od-stat-card">
                <div>
                    <p class="od-stat-label">Avg Quiz Score</p>
                    <p class="od-stat-value od-num">{{ number_format($avgQuizScore, 1) }}%</p>
                </div>
                <div class="od-stat-icon" style="background: var(--od-accent-soft); color: var(--od-accent);">
                    <i class="fas fa-percentage text-lg"></i>
                </div>
            </div>
            <div class="od-stat-card">
                <div>
                    <p class="od-stat-label">Live Minutes</p>
                    <p class="od-stat-value od-num">{{ number_format($totalLiveMinutes) }}</p>
                </div>
                <div class="od-stat-icon" style="background: var(--od-warn-soft); color: var(--od-warn);">
                    <i class="fas fa-video text-lg"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Quiz Performance -->
            <div class="od-card">
                <div class="od-card-header">
                    <h3 class="od-h3"><i class="fas fa-clipboard-list mr-2" style="color: var(--od-navy);"></i>Quiz Performance</h3>
                </div>
                <div class="p-4">
                    @if($quizAttempts->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($quizAttempts->take(10) as $attempt)
                                <div class="flex items-center justify-between p-3 rounded-lg" style="background: var(--od-fg-soft);">
                                    <div>
                                        <p class="text-sm font-medium" style="color: var(--od-fg);">{{ $attempt->quiz->title }}</p>
                                        <p class="text-xs od-meta">{{ $attempt->quiz->course->title }} · Attempt #{{ $attempt->attempt_number }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold od-num" style="color: {{ $attempt->isPassed() ? 'var(--od-green)' : 'var(--od-danger)' }};">
                                            {{ $attempt->score !== null ? $attempt->score . '%' : '—' }}
                                        </p>
                                        <p class="text-xs od-meta">{{ $attempt->status }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="od-empty-sm py-8">
                            <i class="fas fa-inbox text-3xl"></i>
                            <p class="text-sm">No quiz attempts yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Assignment Performance -->
            <div class="od-card">
                <div class="od-card-header">
                    <h3 class="od-h3"><i class="fas fa-tasks mr-2" style="color: var(--od-accent);"></i>Assignment Performance</h3>
                </div>
                <div class="p-4">
                    @if($assignments->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($assignments->take(10) as $submission)
                                <div class="flex items-center justify-between p-3 rounded-lg" style="background: var(--od-fg-soft);">
                                    <div>
                                        <p class="text-sm font-medium" style="color: var(--od-fg);">{{ $submission->assignment->title }}</p>
                                        <p class="text-xs od-meta">{{ $submission->assignment->course->title }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold od-num">
                                            @if($submission->status === 'Graded' && $submission->points_earned !== null)
                                                {{ $submission->points_earned }}/{{ $submission->assignment->max_points }}
                                            @else
                                                <span class="od-meta">—</span>
                                            @endif
                                        </p>
                                        <p class="text-xs od-meta">{{ $submission->status }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="od-empty-sm py-8">
                            <i class="fas fa-inbox text-3xl"></i>
                            <p class="text-sm">No assignment submissions yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Enrollment Trend -->
        @if($monthlyEnrollments->isNotEmpty())
            <div class="od-card">
                <div class="od-card-header">
                    <h3 class="od-h3"><i class="fas fa-chart-line mr-2" style="color: var(--od-navy);"></i>Enrollment Trend</h3>
                </div>
                <div class="p-4">
                    <div class="flex items-end gap-2 h-32">
                        @foreach($monthlyEnrollments as $month => $count)
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <div class="w-full rounded-t-md transition-all" style="height: {{ max(4, ($count / max(1, $monthlyEnrollments->max())) * 100) }}%; background: var(--od-navy);"></div>
                                <span class="text-xs od-meta">{{ $month }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
