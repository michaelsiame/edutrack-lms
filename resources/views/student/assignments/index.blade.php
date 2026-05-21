@extends('layouts.dashboard')

@section('title','My Assignments - Edutrack LMS')
@section('page_title','My Assignments')

@section('content')
<div class="max-w-5xl mx-auto">
 <x-card class="overflow-hidden">
 <div class="p-5 md:p-6 border-b border-gray-100 dark:border-gray-700">
 <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assignments</h2>
 </div>

 @if($assignments->isEmpty())
 <x-empty-state icon="fa-clipboard-list" title="No Assignments" description="You don't have any assignments in your enrolled courses." />
 @else
 <div class="divide-y divide-gray-100 dark:divide-gray-700">
 @foreach($assignments as $assignment)
 <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
 <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
 <div class="flex-1 min-w-0">
 <h4 class="font-medium text-gray-900 dark:text-white">{{ $assignment->title }}</h4>
 <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
 {{ $assignment->course->title }}
 @if($assignment->due_date)
 &bull; Due: {{ $assignment->due_date->format('M d, Y') }}
 @endif
 </p>
 </div>
 <div class="flex items-center gap-3">
 @if($assignment->submission)
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
 {{ $assignment->submission->status ==='Graded' ?'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300' :'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300' }}">
 {{ $assignment->submission->status }}
 </span>
 @if($assignment->submission->points_earned !== null)
 <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
 {{ $assignment->submission->points_earned }}/{{ $assignment->max_points }}
 </span>
 @endif
 @else
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
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
 </x-card>
</div>
@endsection
