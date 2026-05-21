@extends('layouts.dashboard')

@section('title','My Progress - Edutrack LMS')
@section('page_title','My Progress')

@section('content')
<div class="max-w-5xl mx-auto">
 <!-- Stats -->
 <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
 <x-stat-card icon="fa-book" iconColor="primary" :value="$totalCourses ?? 0" label="Enrolled" />
 <x-stat-card icon="fa-check-circle" iconColor="success" :value="$completedCourses ?? 0" label="Completed" />
 <x-stat-card icon="fa-clock" iconColor="warning" :value="$inProgressCourses ?? 0" label="In Progress" />
 <x-stat-card icon="fa-certificate" iconColor="secondary" :value="$totalCertificates ?? 0" label="Certificates" />
 </div>

 <!-- Course Progress -->
 <x-card class="overflow-hidden">
 <div class="p-5 md:p-6 border-b border-gray-100 dark:border-gray-700">
 <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Course Progress</h2>
 </div>
 <div class="divide-y divide-gray-100 dark:divide-gray-700">
 @forelse($enrollments ?? [] as $enrollment)
 <div class="p-5 md:p-6 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
 <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
 <div class="flex-1 min-w-0">
 <h3 class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->course->title }}</h3>
 <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $enrollment->course->category->name ??'General' }}</p>
 </div>
 <div class="flex items-center gap-4 md:w-1/3">
 <x-progress-bar :value="$enrollment->progress ?? 0" size="md" showLabel />
 <a href="{{ route('enrollments.show', $enrollment->course) }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium shrink-0">
 Continue <i class="fas fa-arrow-right ml-1 text-xs"></i>
 </a>
 </div>
 </div>
 </div>
 @empty
 <x-empty-state icon="fa-chart-line" title="No Progress Yet" description="You haven't enrolled in any courses yet. Start learning to see your progress here." actionText="Browse Courses" actionRoute="courses.index" />
 @endforelse
 </div>
 </x-card>
</div>
@endsection
