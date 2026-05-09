@extends('layouts.app')

@section('title', 'Courses - Edutrack LMS')

@section('content')
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900">All Courses</h2>
            <p class="mt-2 text-gray-600">Expand your skills with our professional courses</p>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <form action="{{ route('courses.index') }}" method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" placeholder="Search courses..." value="{{ request('search') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <select name="level" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Levels</option>
                        <option value="Beginner" {{ request('level') === 'Beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="Intermediate" {{ request('level') === 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="Advanced" {{ request('level') === 'Advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filter</button>
            </form>
        </div>

        <!-- Course Grid -->
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($courses as $course)
                <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
                    <div class="h-48 bg-gray-200">
                        <img src="{{ $course->thumbnail_url ?? 'https://via.placeholder.com/400x200' }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded">{{ $course->category?->name ?? 'General' }}</span>
                            <span class="text-xs text-gray-500">{{ $course->level }}</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600">{{ $course->title }}</a>
                        </h3>
                        <p class="text-sm text-gray-500 mb-4 flex-1">{{ Str::limit($course->short_description, 100) }}</p>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <span class="text-lg font-bold text-gray-900">{{ $course->formatted_price }}</span>
                            <a href="{{ route('courses.show', $course) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">View Details &rarr;</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500">No courses found.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $courses->links() }}
        </div>
    </div>
</div>
@endsection
