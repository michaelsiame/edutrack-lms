@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Assignments</h1>
        <a href="{{ route('instructor.assignments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>New Assignment
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submissions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($assignments as $assignment)
                <tr>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $assignment->title }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $assignment->course->title ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $assignment->due_date?->format('M d, Y') ?? 'No deadline' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $assignment->submissions_count ?? 0 }} submissions
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium space-x-2">
                        <a href="{{ route('instructor.assignments.show', $assignment) }}" class="text-blue-600 hover:text-blue-900">View</a>
                        <a href="{{ route('instructor.assignments.edit', $assignment) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No assignments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($assignments->hasPages())
        <div class="mt-4">
            {{ $assignments->links() }}
        </div>
    @endif
</div>
@endsection
