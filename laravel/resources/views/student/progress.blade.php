@extends('layouts.dashboard')

@section('title', 'My Progress - Edutrack LMS')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">My Learning Progress</h1>

    <!-- Overall Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl font-bold text-primary-600">{{ $totalCourses ?? 0 }}</div>
            <div class="text-sm text-gray-600 mt-1">Enrolled Courses</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl font-bold text-green-600">{{ $completedCourses ?? 0 }}</div>
            <div class="text-sm text-gray-600 mt-1">Completed</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl font-bold text-yellow-600">{{ $inProgressCourses ?? 0 }}</div>
            <div class="text-sm text-gray-600 mt-1">In Progress</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl font-bold text-purple-600">{{ $totalCertificates ?? 0 }}</div>
            <div class="text-sm text-gray-600 mt-1">Certificates</div>
        </div>
    </div>

    <!-- Course Progress -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-bold">Course Progress</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($enrollments ?? [] as $enrollment)
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ $enrollment->course->title }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $enrollment->course->category->name ?? 'General' }}</p>
                    </div>
                    <div class="w-full md:w-1/3">
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-600">{{ $enrollment->progress ?? 0 }}% Complete</span>
                            <span class="text-xs text-gray-500">{{ $enrollment->completed_lessons ?? 0 }}/{{ $enrollment->total_lessons ?? 0 }} lessons</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-primary-600 h-2.5 rounded-full" style="width: {{ $enrollment->progress ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('enrollments.show', $enrollment) }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                            Continue <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-chart-line text-4xl text-gray-300 mb-3"></i>
                <p>You haven't enrolled in any courses yet.</p>
                <a href="{{ route('courses.index') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                    <i class="fas fa-search mr-2"></i> Browse Courses
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
