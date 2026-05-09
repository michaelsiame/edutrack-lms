@extends('layouts.app')

@section('title', 'Student Dashboard - Edutrack LMS')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">My Learning</h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- My Courses -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">My Courses</h3>
                        <a href="{{ route('enrollments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                    </div>
                    <div class="space-y-4">
                        @forelse($enrollments as $enrollment)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $enrollment->course?->title ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $enrollment->enrollment_status }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">{{ number_format($enrollment->progress, 0) }}%</div>
                                    <div class="w-24 bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $enrollment->progress }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">No enrolled courses yet. <a href="{{ route('courses.index') }}" class="text-indigo-600">Browse courses</a></p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Certificates -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">My Certificates</h3>
                        <a href="{{ route('student.certificates') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                    </div>
                    <div class="space-y-4">
                        @forelse($certificates as $certificate)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $certificate->course?->title ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $certificate->issued_date?->format('M d, Y') }}</p>
                                </div>
                                <a href="{{ route('certificates.download', $certificate) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    Download
                                </a>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">No certificates yet. Complete a course to earn one!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
