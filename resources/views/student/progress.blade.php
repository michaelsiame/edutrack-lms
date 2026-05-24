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
    <x-card variant="default" class="overflow-hidden">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <i class="fas fa-chart-pie text-primary-500"></i>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">Course Progress</h3>
            </div>
        </x-slot:header>

        <div class="divide-y divide-gray-100 dark:divide-gray-700 -mx-5 md:-mx-6">
            @forelse($enrollments ?? [] as $enrollment)
                <div class="px-5 md:px-6 py-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->course->title }}</h3>
                                <x-status-badge :status="$enrollment->enrollment_status" size="sm" />
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $enrollment->course->category->name ?? 'General' }}</p>
                            @if($enrollment->total_time_spent)
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    <i class="far fa-clock mr-1"></i>{{ floor($enrollment->total_time_spent / 60) }}h {{ $enrollment->total_time_spent % 60 }}m spent
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-4 md:w-2/5">
                            <div class="flex-1">
                                <x-progress-bar :value="$enrollment->progress ?? 0" size="md" showLabel :color="$enrollment->progress == 100 ? 'success' : 'primary'" />
                            </div>
                            <a href="{{ route('enrollments.show', $enrollment->course) }}" class="shrink-0 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">
                                Continue <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 md:px-6 py-12">
                    <x-empty-state icon="fa-chart-line" title="No Progress Yet" description="You haven't enrolled in any courses yet. Start learning to see your progress here." actionText="Browse Courses" actionRoute="courses.index" />
                </div>
            @endforelse
        </div>
    </x-card>
</div>
@endsection
