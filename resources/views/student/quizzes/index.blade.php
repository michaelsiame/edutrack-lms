@extends('layouts.dashboard')

@section('title','My Quizzes - Edutrack LMS')
@section('page_title','My Quizzes')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <x-page-header title="My Quizzes" subtitle="Quizzes are grouped by course and ordered by module. Finish a module's lessons to unlock its quiz." variant="od" />

    @if(empty($courses))
        <div class="od-card">
            <x-empty-state icon="fa-clipboard-list" title="No Quizzes Available" description="Your enrolled courses don't have any quizzes yet." variant="od" />
        </div>
    @else
        <p class="od-meta mb-4">{{ $totalQuizzes }} quiz{{ $totalQuizzes !== 1 ? 'zes' : '' }} across {{ count($courses) }} course{{ count($courses) !== 1 ? 's' : '' }}</p>

        @foreach($courses as $group)
            <div class="od-card mb-5" x-data="{ open: false }">
                {{-- Course header --}}
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between text-left">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center rounded-lg"
                              style="width:38px;height:38px;background:var(--od-navy-soft);color:var(--od-navy);">
                            <i class="fas fa-book"></i>
                        </span>
                        <div>
                            <h3 class="od-h3" style="margin:0;">{{ $group['course']->title }}</h3>
                            <span class="od-meta">{{ $group['passed'] }}/{{ $group['total'] }} passed</span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"
                       style="color:var(--od-muted);"></i>
                </button>

                <div x-show="open" x-collapse class="mt-4 overflow-x-auto">
                    <table class="od-table">
                        <thead>
                            <tr>
                                <th style="width:42px;"></th>
                                <th>Quiz</th>
                                <th>Module</th>
                                <th class="num-col">Attempts</th>
                                <th class="num-col">Best</th>
                                <th class="num-col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group['quizzes'] as $item)
                                <tr>
                                    {{-- status icon --}}
                                    <td>
                                        @if($item['passed'])
                                            <i class="fas fa-circle-check" style="color: var(--od-green);" title="Passed"></i>
                                        @elseif($item['locked'])
                                            <i class="fas fa-lock" style="color: var(--od-muted);" title="Locked"></i>
                                        @elseif($item['attempts_count'] > 0)
                                            <i class="fas fa-rotate-right" style="color: var(--od-accent);" title="Attempted"></i>
                                        @else
                                            <i class="far fa-circle" style="color: var(--od-border);" title="Not started"></i>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="font-medium text-sm">{{ $item['quiz']->title }}</div>
                                        <div class="od-meta">
                                            Pass: {{ $item['quiz']->passing_score ?? 60 }}%
                                            @if($item['quiz']->time_limit_minutes)
                                                · {{ $item['quiz']->time_limit_minutes }} min
                                            @endif
                                        </div>
                                    </td>

                                    <td class="text-sm" style="color: var(--od-muted);">
                                        @if($item['module_title'])
                                            {{ $item['module_title'] }}
                                        @else
                                            <span style="opacity:.6;">General</span>
                                        @endif
                                    </td>

                                    <td class="num-col">
                                        @if($item['attempts_count'] > 0)
                                            <a href="{{ route('student.quizzes.attempts', $item['quiz']) }}" class="text-sm font-medium" style="color: var(--od-navy);">
                                                {{ $item['attempts_count'] }}
                                            </a>
                                        @else
                                            <span class="text-sm" style="color: var(--od-muted);">&mdash;</span>
                                        @endif
                                    </td>

                                    <td class="num-col">
                                        @if($item['best_score'] !== null)
                                            <span class="od-badge {{ $item['passed'] ? 'od-badge-success' : 'od-badge-danger' }}">
                                                {{ $item['best_score'] }}%
                                            </span>
                                        @else
                                            <span class="text-xs" style="color: var(--od-muted);">&mdash;</span>
                                        @endif
                                    </td>

                                    <td class="num-col">
                                        @if($item['locked'])
                                            <span class="text-xs inline-flex items-center gap-1" style="color: var(--od-muted);"
                                                  title="Complete this module's lessons to unlock">
                                                <i class="fas fa-lock"></i> Locked
                                            </span>
                                        @elseif($item['can_retake'])
                                            <a href="{{ route('student.quizzes.take', $item['quiz']) }}" class="od-btn od-btn-navy od-btn-sm">
                                                <i class="fas fa-play"></i> {{ $item['attempts_count'] > 0 ? 'Retake' : 'Start' }}
                                            </a>
                                        @elseif($item['attempts_count'] > 0)
                                            <a href="{{ route('student.quizzes.attempts', $item['quiz']) }}" class="od-btn od-btn-ghost od-btn-sm">
                                                View
                                            </a>
                                        @else
                                            <span class="text-xs" style="color: var(--od-muted);">Max attempts</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
