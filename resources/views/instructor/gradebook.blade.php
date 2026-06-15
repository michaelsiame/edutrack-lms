@extends('layouts.dashboard')

@section('title', 'Gradebook — ' . $course->title)
@section('page_title', 'Gradebook — ' . $course->title)

@section('content')
@php
$scoreBadgeClass = function ($score) {
    if ($score === null) {
        return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
    }

    return $score >= 60
        ? 'od-badge-success'
        : 'od-badge-danger';
};

$hasAssessments = $quizzes->isNotEmpty() || $assignments->isNotEmpty();
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('instructor.courses.show', $course) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <i class="fas fa-arrow-left mr-1"></i>Back to Course Builder
            </a>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-2">Gradebook</h1>
            <p class="od-meta">{{ $rows->count() }} student{{ $rows->count() !== 1 ? 's' : '' }} &bull; {{ $quizzes->count() }} quiz{{ $quizzes->count() !== 1 ? 'zes' : '' }} &bull; {{ $assignments->count() }} assignment{{ $assignments->count() !== 1 ? 's' : '' }}</p>
        </div>
    </div>

    @if(!$hasAssessments)
    <div class="od-card p-8 text-center">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-clipboard-list text-2xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Assessments Yet</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Add quizzes and assignments to this course to populate the gradebook.</p>
    </div>
    @elseif($rows->isEmpty())
    <div class="od-card p-8 text-center">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-user-slash text-2xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Enrolled Students</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Students must be enrolled in this course before they appear in the gradebook.</p>
    </div>
    @else
    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="overflow-x-auto">
            <table class="od-table min-w-[768px]">
                <thead>
                    <tr>
                        <th class="text-left sticky left-0 bg-gray-50 dark:bg-gray-700/50 z-10">Student</th>
                        @foreach($quizzes as $quiz)
                        <th class="text-center">
                            <a href="{{ route('instructor.quizzes.attempts', $quiz) }}" class="inline-block max-w-[140px] truncate hover:text-primary-600 dark:hover:text-primary-400" title="{{ $quiz->title }} — click to record marks">
                                <i class="fas fa-question-circle text-xs text-primary-500 mr-1"></i>{{ $quiz->title }}
                            </a>
                        </th>
                        @endforeach
                        @foreach($assignments as $assignment)
                        <th class="text-center">
                            <a href="{{ route('instructor.assignments.index') }}" class="inline-block max-w-[140px] truncate hover:text-primary-600 dark:hover:text-primary-400" title="{{ $assignment->title }} — click to record marks">
                                <i class="fas fa-file-alt text-xs text-secondary-500 mr-1"></i>{{ $assignment->title }}
                            </a>
                        </th>
                        @endforeach
                        <th class="text-center">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                    <tr>
                        <td class="sticky left-0 bg-white dark:bg-gray-800 z-10">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold">
                                    {{ substr($row['name'], 0, 1) }}
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</span>
                                    @if($row['mode'] !== 'Online')
                                    <span class="ml-1 text-[10px] bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 px-1.5 py-0.5 rounded">{{ $row['mode'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @foreach($quizzes as $quiz)
                        @php $score = $row['quiz_scores'][$quiz->id] ?? null; @endphp
                        <td class="text-center">
                            <span class="od-badge {{ $scoreBadgeClass($score) }}">
                                {{ $score !== null ? number_format($score, 1) . '%' : '—' }}
                            </span>
                        </td>
                        @endforeach
                        @foreach($assignments as $assignment)
                        @php $score = $row['assignment_scores'][$assignment->id] ?? null; @endphp
                        <td class="text-center">
                            <span class="od-badge {{ $scoreBadgeClass($score) }}">
                                {{ $score !== null ? number_format($score, 1) . '%' : '—' }}
                            </span>
                        </td>
                        @endforeach
                        <td class="text-center">
                            <span class="od-badge {{ $scoreBadgeClass($row['final_grade']) }}">
                                {{ $row['final_grade'] !== null ? number_format($row['final_grade'], 1) . '%' : '—' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                <i class="fas fa-info-circle mr-1"></i>Tip: click a column header to record offline marks for that assessment.
            </p>
        </div>
    </div>
    @endif
</div>
@endsection
