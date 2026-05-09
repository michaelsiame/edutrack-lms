@extends('layouts.app')

@section('title', $course->title . ' - Edutrack LMS')

@section('content')
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="relative h-64 md:h-96">
                <img src="{{ $course->thumbnail_url ?? 'https://via.placeholder.com/1200x400' }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <span class="inline-block px-3 py-1 text-xs font-semibold text-white bg-indigo-600 rounded-full mb-2">{{ $course->level }}</span>
                    <h1 class="text-3xl md:text-4xl font-bold text-white">{{ $course->title }}</h1>
                </div>
            </div>

            <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">About This Course</h2>
                        <p class="text-gray-600 mb-6">{{ $course->description }}</p>

                        @if($course->learning_outcomes)
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">What You'll Learn</h3>
                            <ul class="list-disc list-inside text-gray-600 mb-6 space-y-1">
                                @foreach(explode("\n", $course->learning_outcomes) as $outcome)
                                    @if(trim($outcome))
                                        <li>{{ trim($outcome) }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif

                        @if($course->modules->count() > 0)
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Course Content</h3>
                            <div class="border rounded-lg divide-y">
                                @foreach($course->modules as $module)
                                    <div class="p-4">
                                        <h4 class="font-medium text-gray-900">{{ $module->title }}</h4>
                                        @if($module->lessons->count() > 0)
                                            <ul class="mt-2 space-y-1">
                                                @foreach($module->lessons as $lesson)
                                                    <li class="text-sm text-gray-500 flex items-center">
                                                        <span class="w-2 h-2 bg-indigo-400 rounded-full mr-2"></span>
                                                        {{ $lesson->title }}
                                                        @if($lesson->is_preview)
                                                            <span class="ml-2 text-xs text-green-600">(Preview)</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-gray-50 rounded-lg p-6 sticky top-6">
                            <div class="text-center mb-6">
                                <p class="text-3xl font-bold text-gray-900">{{ $course->formatted_price }}</p>
                                @if($course->discount_price)
                                    <p class="text-sm text-gray-500 line-through">ZMW {{ number_format($course->price, 2) }}</p>
                                @endif
                            </div>

                            @auth
                                @if($isEnrolled)
                                    <a href="{{ route('enrollments.show', $course) }}" class="block w-full text-center py-3 px-4 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium">
                                        Continue Learning
                                    </a>
                                @else
                                    <form action="{{ route('enrollments.store', $course) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="block w-full text-center py-3 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium">
                                            Enroll Now
                                        </button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="block w-full text-center py-3 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium">
                                    Login to Enroll
                                </a>
                            @endauth

                            <div class="mt-6 space-y-3 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>Instructor</span>
                                    <span class="font-medium">{{ $course->instructor?->user?->full_name ?? 'TBA' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Duration</span>
                                    <span class="font-medium">{{ $course->duration_weeks ?? 'N/A' }} weeks</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Students</span>
                                    <span class="font-medium">{{ $course->enrollment_count }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Language</span>
                                    <span class="font-medium">{{ $course->language }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
