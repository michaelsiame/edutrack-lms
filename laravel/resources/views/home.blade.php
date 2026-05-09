@extends('layouts.app')

@section('title', 'Home - Edutrack LMS')

@section('content')
<div class="relative bg-indigo-600 overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="relative z-10 pb-8 bg-indigo-600 sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
            <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                <div class="sm:text-center lg:text-left">
                    <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl">
                        <span class="block xl:inline">Learn Skills for</span>
                        <span class="block text-indigo-200 xl:inline">Your Future</span>
                    </h1>
                    <p class="mt-3 text-base text-indigo-100 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                        Professional courses in ICT, Business, and Technical skills. TEVETA registered and accredited.
                    </p>
                    <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                        <div class="rounded-md shadow">
                            <a href="{{ route('courses.index') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                                Browse Courses
                            </a>
                        </div>
                        @guest
                        <div class="mt-3 sm:mt-0 sm:ml-3">
                            <a href="{{ route('register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-500 hover:bg-indigo-600 md:py-4 md:text-lg md:px-10">
                                Get Started
                            </a>
                        </div>
                        @endguest
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Featured Courses -->
@if($featuredCourses->count() > 0)
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-base text-indigo-600 font-semibold tracking-wide uppercase">Featured</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Popular Courses
            </p>
        </div>

        <div class="mt-10 grid gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($featuredCourses as $course)
            <div class="flex flex-col rounded-lg shadow-lg overflow-hidden">
                <div class="flex-shrink-0">
                    <img class="h-48 w-full object-cover" src="{{ $course->thumbnail_url ?? 'https://via.placeholder.com/400x200' }}" alt="{{ $course->title }}">
                </div>
                <div class="flex-1 bg-white p-6 flex flex-col justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-indigo-600">
                            {{ $course->category->name ?? 'Uncategorized' }}
                        </p>
                        <a href="{{ route('courses.show', $course) }}" class="block mt-2">
                            <p class="text-xl font-semibold text-gray-900">{{ $course->title }}</p>
                            <p class="mt-3 text-base text-gray-500">{{ Str::limit($course->short_description, 100) }}</p>
                        </a>
                    </div>
                    <div class="mt-6 flex items-center justify-between">
                        <div class="flex items-center">
                            <p class="text-sm font-medium text-gray-900">{{ $course->formatted_price }}</p>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-500">{{ $course->level }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Latest Courses -->
@if($latestCourses->count() > 0)
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Latest Courses</h2>
            <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4">New skills added regularly to keep you ahead.</p>
        </div>

        <div class="mt-10 grid gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($latestCourses as $course)
            <div class="flex flex-col rounded-lg shadow-lg overflow-hidden">
                <div class="flex-shrink-0">
                    <img class="h-48 w-full object-cover" src="{{ $course->thumbnail_url ?? 'https://via.placeholder.com/400x200' }}" alt="{{ $course->title }}">
                </div>
                <div class="flex-1 bg-white p-6 flex flex-col justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-indigo-600">
                            {{ $course->category->name ?? 'Uncategorized' }}
                        </p>
                        <a href="{{ route('courses.show', $course) }}" class="block mt-2">
                            <p class="text-xl font-semibold text-gray-900">{{ $course->title }}</p>
                            <p class="mt-3 text-base text-gray-500">{{ Str::limit($course->short_description, 100) }}</p>
                        </a>
                    </div>
                    <div class="mt-6 flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">{{ $course->formatted_price }}</p>
                        <span class="text-sm text-gray-500">{{ $course->level }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('courses.index') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                View All Courses
            </a>
        </div>
    </div>
</section>
@endif
@endsection
