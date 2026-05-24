@extends('layouts.dashboard')

@section('title','My Assignments - Edutrack LMS')
@section('page_title','My Assignments')

@section('content')
<div class="max-w-5xl mx-auto">
    <x-page-header title="My Assignments" subtitle="Track and submit assignments across your enrolled courses" />

    <x-card variant="default" class="overflow-hidden">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <i class="fas fa-tasks text-primary-500"></i>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">Assignments</h3>
            </div>
        </x-slot:header>

        @if($assignments->isEmpty())
            <x-empty-state icon="fa-clipboard-list" title="No Assignments" description="You don't have any assignments in your enrolled courses." />
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-700 -mx-5 md:-mx-6">
                @foreach($assignments as $assignment)
                    <div class="px-5 md:px-6 py-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $assignment->title }}</h4>
                                    @if($assignment->submission?->is_late)
                                        <x-status-badge status="Late" size="sm" />
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $assignment->course->title }}
                                    @if($assignment->due_date)
                                        <span class="mx-1">&bull;</span>
                                        <span class="{{ $assignment->due_date->isPast() && !$assignment->submission ? 'text-danger-600 dark:text-danger-400 font-medium' : '' }}">
                                            Due {{ $assignment->due_date->format('M d, Y') }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                @if($assignment->submission)
                                    <x-status-badge :status="$assignment->submission->status" size="sm" />
                                    @if($assignment->submission->points_earned !== null)
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ $assignment->submission->points_earned }}/{{ $assignment->max_points }}
                                        </span>
                                    @endif
                                @else
                                    <x-status-badge status="Not Submitted" size="sm" />
                                @endif
                                <x-button :href="route('student.assignments.show', [$assignment->course, $assignment])" variant="ghost" size="sm">
                                    View <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                </x-button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>
</div>
@endsection
