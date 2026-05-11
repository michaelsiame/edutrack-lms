@extends('layouts.dashboard')

@section('title', $course->title . ' - Edutrack LMS')
@section('page_title', $course->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Course Details</h3>
            <a href="{{ route('instructor.courses.edit', $course) }}" class="text-primary-600 hover:underline">Edit</a>
        </div>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Title</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $course->title }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Students</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $course->enrollments?->count() ?? 0 }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Price</span>
                <span class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format($course->price, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Status</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $course->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($course->status) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
