@extends('layouts.app')

@section('title', 'Instructor Dashboard - Edutrack LMS')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Instructor Dashboard</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <dt class="text-sm font-medium text-gray-500 truncate">My Courses</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ $stats['total_courses'] }}</dd>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Students</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ $stats['total_students'] }}</dd>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <dt class="text-sm font-medium text-gray-500 truncate">Rating</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ number_format($stats['average_rating'], 1) }}/5</dd>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">My Courses</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($courses as $course)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $course->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $course->enrollments_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($course->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600">
                                        <a href="#" class="hover:text-indigo-900">Manage</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">No courses yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
