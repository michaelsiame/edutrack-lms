@extends('layouts.dashboard')

@section('title','My Quizzes - Edutrack LMS')
@section('page_title','My Quizzes')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <x-page-header title="All Quizzes" subtitle="Track your quiz attempts and scores across all courses" variant="od" />

    <div class="od-card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="od-h3">Quizzes</h3>
            <span class="od-meta">{{ count($quizData) }} quizzes</span>
        </div>

        @if(empty($quizData))
            <x-empty-state icon="fa-clipboard-list" title="No Quizzes Available" description="Your enrolled courses don't have any quizzes yet." variant="od" />
        @else
            <table class="od-table">
                <thead>
                    <tr><th>Quiz</th><th>Course</th><th class="num-col">Attempts</th><th class="num-col">Best Score</th><th class="num-col">Action</th></tr>
                </thead>
                <tbody>
                    @foreach($quizData as $item)
                        <tr>
                            <td>
                                <div class="font-medium text-sm">{{ $item['quiz']->title }}</div>
                                <div class="od-meta">Pass: {{ $item['quiz']->passing_score ?? 60 }}%</div>
                            </td>
                            <td class="text-sm" style="color: var(--od-muted);">{{ $item['course']->title }}</td>
                            <td class="num-col">
                                @if($item['attempts_count'] > 0)
                                    <a href="{{ route('student.quizzes.attempts', $item['quiz']) }}" class="text-sm font-medium" style="color: var(--od-navy);">
                                        {{ $item['attempts_count'] }} attempt{{ $item['attempts_count'] !== 1 ? 's' : '' }}
                                    </a>
                                @else
                                    <span class="text-sm" style="color: var(--od-muted);">No attempts</span>
                                @endif
                            </td>
                            <td class="num-col">
                                @if($item['best_score'] !== null)
                                    <span class="od-badge {{ $item['best_score'] >= ($item['quiz']->passing_score ?? 60) ? 'od-badge-success' : 'od-badge-danger' }}">
                                        {{ $item['best_score'] }}%
                                    </span>
                                @else
                                    <span class="text-xs" style="color: var(--od-muted);">&mdash;</span>
                                @endif
                            </td>
                            <td class="num-col">
                                @if($item['can_retake'])
                                    <a href="{{ route('student.quizzes.take', $item['quiz']) }}" class="od-btn od-btn-navy od-btn-sm">
                                        <i class="fas fa-play"></i> {{ $item['attempts_count'] > 0 ? 'Retake' : 'Start' }}
                                    </a>
                                @else
                                    <span class="text-xs" style="color: var(--od-muted);">Max attempts</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
