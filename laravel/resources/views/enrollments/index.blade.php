@extends('layouts.app')

@section('title', 'My Courses - Edutrack LMS')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">My Courses</h2>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($enrollments as $enrollment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $enrollment->course?->title ?? 'Unknown' }}</div>
                                            <div class="text-xs text-gray-500">Enrolled {{ $enrollment->enrolled_at?->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 max-w-[100px]">
                                        <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $enrollment->progress }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ number_format($enrollment->progress, 0) }}%</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @match($enrollment->enrollment_status)
                                            @case('Completed') bg-green-100 text-green-800 @break
                                            @case('In Progress') bg-blue-100 text-blue-800 @break
                                            @case('Dropped') bg-red-100 text-red-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endmatch">
                                        {{ $enrollment->enrollment_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $enrollment->payment_status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($enrollment->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600">
                                    <a href="{{ route('enrollments.show', $enrollment->course) }}" class="hover:text-indigo-900">Continue</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    No enrolled courses yet.
                                    <a href="{{ route('courses.index') }}" class="text-indigo-600 hover:text-indigo-800">Browse courses</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4">
                {{ $enrollments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
