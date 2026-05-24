@extends('layouts.dashboard')

@section('title','My Quizzes - Edutrack LMS')
@section('page_title','My Quizzes')

@section('content')
<div class="max-w-5xl mx-auto">
    <x-page-header title="All Quizzes" subtitle="Track your quiz attempts and scores across all courses" />

    <x-card variant="default" class="overflow-hidden">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <i class="fas fa-clipboard-list text-primary-500"></i>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">Quizzes</h3>
            </div>
            <x-slot:headerAction>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ count($quizData) }} quizzes</span>
            </x-slot:headerAction>
        </x-slot:header>

        @if(empty($quizData))
            <x-empty-state icon="fa-clipboard-list" title="No Quizzes Available" description="Your enrolled courses don't have any quizzes yet." />
        @else
            <x-data-table :columns="['Quiz', 'Course', 'Attempts', 'Best Score', 'Action']">
                @foreach($quizData as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-4 md:px-6 py-4">
                            <div class="font-medium text-gray-900 dark:text-white text-sm">{{ $item['quiz']->title }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Pass: {{ $item['quiz']->passing_score ?? 60 }}%</div>
                        </td>
                        <td class="px-4 md:px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $item['course']->title }}
                        </td>
                        <td class="px-4 md:px-6 py-4">
                            @if($item['attempts_count'] > 0)
                                <a href="{{ route('student.quizzes.attempts', $item['quiz']) }}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium transition-colors">
                                    {{ $item['attempts_count'] }} attempt{{ $item['attempts_count'] !== 1 ? 's' : '' }}
                                </a>
                            @else
                                <span class="text-sm text-gray-400 dark:text-gray-500">No attempts</span>
                            @endif
                        </td>
                        <td class="px-4 md:px-6 py-4">
                            @if($item['best_score'] !== null)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item['best_score'] >= ($item['quiz']->passing_score ?? 60) ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400' }}">
                                    {{ $item['best_score'] }}%
                                </span>
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 md:px-6 py-4 text-right">
                            @if($item['can_retake'])
                                <x-button :href="route('student.quizzes.take', $item['quiz'])" icon="fa-play" size="sm">
                                    {{ $item['attempts_count'] > 0 ? 'Retake' : 'Start' }}
                                </x-button>
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-500">Max attempts reached</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        @endif
    </x-card>
</div>
@endsection
