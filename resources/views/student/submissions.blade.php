@extends('layouts.dashboard')

@section('title', 'My Submissions - Edutrack LMS')
@section('page_title', 'My Submissions')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="max-w-5xl mx-auto">
        <h2 class="od-h2 mb-6">My Submissions</h2>

        <!-- Assignment Submissions -->
        <div class="od-card mb-8">
            <div class="od-card-header">
                <h3 class="od-h3"><i class="fas fa-tasks mr-2" style="color: var(--od-accent);"></i>Assignment Submissions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="od-table min-w-[640px]">
                    <thead>
                        <tr>
                            <th>Assignment</th>
                            <th>Course</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignmentSubmissions as $submission)
                            <tr>
                                <td>
                                    <span class="font-medium" style="color: var(--od-fg);">{{ $submission->assignment->title }}</span>
                                    @if($submission->is_late)
                                        <span class="od-badge od-badge-danger od-badge-sm ml-2">Late</span>
                                    @endif
                                </td>
                                <td class="text-sm" style="color: var(--od-muted);">{{ $submission->assignment->course->title }}</td>
                                <td class="text-sm od-meta">{{ $submission->submitted_at?->diffForHumans() ?? 'N/A' }}</td>
                                <td>
                                    <span class="od-badge {{ $submission->status === 'Graded' ? 'od-badge-success' : 'od-badge-warn' }} od-badge-sm">
                                        {{ $submission->status }}
                                    </span>
                                </td>
                                <td class="text-sm font-medium od-num">
                                    @if($submission->status === 'Graded' && $submission->points_earned !== null)
                                        {{ $submission->points_earned }}/{{ $submission->assignment->max_points }}
                                    @else
                                        <span class="od-meta">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="od-empty-sm">
                                        <i class="fas fa-inbox text-3xl"></i>
                                        <p class="text-sm">No assignment submissions yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assignmentSubmissions->hasPages())
                <div class="p-4">{{ $assignmentSubmissions->links() }}</div>
            @endif
        </div>

        <!-- Quiz Attempts -->
        <div class="od-card">
            <div class="od-card-header">
                <h3 class="od-h3"><i class="fas fa-clipboard-list mr-2" style="color: var(--od-navy);"></i>Quiz Attempts</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="od-table min-w-[640px]">
                    <thead>
                        <tr>
                            <th>Quiz</th>
                            <th>Course</th>
                            <th>Attempt</th>
                            <th>Status</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quizAttempts as $attempt)
                            <tr>
                                <td>
                                    <span class="font-medium" style="color: var(--od-fg);">{{ $attempt->quiz->title }}</span>
                                </td>
                                <td class="text-sm" style="color: var(--od-muted);">{{ $attempt->quiz->course->title }}</td>
                                <td class="text-sm od-meta">#{{ $attempt->attempt_number }}</td>
                                <td>
                                    <span class="od-badge {{ $attempt->status === 'Graded' ? 'od-badge-success' : ($attempt->status === 'In Progress' ? 'od-badge-info' : 'od-badge-warn') }} od-badge-sm">
                                        {{ $attempt->status }}
                                    </span>
                                </td>
                                <td class="text-sm font-medium od-num">
                                    @if($attempt->score !== null)
                                        {{ $attempt->score }}%
                                    @else
                                        <span class="od-meta">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="od-empty-sm">
                                        <i class="fas fa-inbox text-3xl"></i>
                                        <p class="text-sm">No quiz attempts yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($quizAttempts->hasPages())
                <div class="p-4">{{ $quizAttempts->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
