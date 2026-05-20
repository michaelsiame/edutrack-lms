@extends('layouts.dashboard')

@section('title', 'My Assignments - Edutrack LMS')
@section('page_title', 'My Assignments')

@section('content')
<div class="max-w-5xl mx-auto">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assignments</h2>
        </div>

        @if($assignments->isEmpty())
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-list text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Assignments</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm">You don't have any assignments in your enrolled courses.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($assignments as $assignment)
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $assignment->title }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $assignment->course->title }}
                                    @if($assignment->due_date)
                                        &bull; Due: {{ $assignment->due_date->format('M d, Y') }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if($assignment->submission)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                        {{ $assignment->submission->status === 'Graded' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $assignment->submission->status }}
                                    </span>
                                    @if($assignment->submission->points_earned !== null)
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ $assignment->submission->points_earned }}/{{ $assignment->max_points }}
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Not Submitted
                                    </span>
                                @endif
                                <a href="{{ route('student.assignments.show', [$assignment->course, $assignment]) }}"
                                    class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
