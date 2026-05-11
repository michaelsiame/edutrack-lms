@extends('layouts.app')

@section('title', 'Edutrack Computer Training College | TEVETA-Certified Tech Training in Zambia')
@section('meta_description', 'Edutrack Computer Training College - TEVETA registered institution offering quality computer training in Zambia. Transform your future with industry-recognized certification programs.')

@section('content')

<!-- Hero Section -->
<section class="relative text-white overflow-hidden" style="min-height: 600px;">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0">
        <img src="{{ asset('assets/images/hero-bg-1.jpg') }}" alt="Edutrack Campus" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-primary-900/90 to-black/85"></div>
        <div class="absolute inset-0 bg-black/30"></div>
    </div>
    <!-- Decorative pattern overlay -->
    <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
        <div class="text-center bg-black/20 backdrop-blur-sm rounded-3xl p-6 md:p-10">
            <div class="mb-6 animate-fade-in">
                <span class="inline-flex items-center px-5 py-2.5 rounded-full text-sm font-bold bg-yellow-500 text-gray-900 shadow-lg border-2 border-yellow-400">
                    <i class="fas fa-certificate mr-2"></i>
                    TEVETA Registered Institution
                </span>
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 animate-fade-in leading-tight">
                Transform Your Future with
                <span class="block text-yellow-400 mt-2 drop-shadow-lg">Edutrack Computer Training College</span>
            </h1>
            <p class="text-lg md:text-xl lg:text-2xl text-blue-100 mb-10 max-w-3xl mx-auto leading-relaxed">
                Zambia's premier TEVETA-certified computer training institution. Join thousands of students mastering industry-relevant skills.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center max-w-2xl mx-auto pb-6">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-xl text-primary-700 bg-white hover:bg-gray-50 transition duration-200 shadow-xl transform hover:-translate-y-1">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 border-2 border-yellow-400 text-base font-medium rounded-xl text-gray-900 bg-yellow-500 hover:bg-yellow-400 transition duration-200 shadow-xl transform hover:-translate-y-1">
                        <i class="fas fa-user-plus mr-2"></i>
                        Get Started Free
                    </a>
                @endauth
                <a href="{{ route('courses.index') }}" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white/60 text-base font-medium rounded-xl text-white hover:bg-white/10 hover:border-white transition duration-200 backdrop-blur-sm">
                    <i class="fas fa-book mr-2"></i>
                    View Our Courses
                </a>
            </div>
        </div>

        <!-- Trust Indicators -->
        <div class="mt-10 md:mt-14 pt-8 border-t border-white/10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <div class="animate-slide-up animation-delay-100 bg-white/10 backdrop-blur-md rounded-2xl p-5 md:p-6 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-14 h-14 mx-auto mb-3 bg-yellow-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-certificate text-2xl text-yellow-400"></i>
                    </div>
                    <h3 class="text-base md:text-lg font-bold text-white">TEVETA Registered</h3>
                    <p class="text-xs text-blue-200 mt-1">Government Certified</p>
                </div>
                <div class="animate-slide-up animation-delay-200 bg-white/10 backdrop-blur-md rounded-2xl p-5 md:p-6 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-14 h-14 mx-auto mb-3 bg-blue-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-blue-300"></i>
                    </div>
                    <h3 class="text-base md:text-lg font-bold text-white">
                        @if(($stats['total_students'] ?? 0) > 0)
                            {{ number_format($stats['total_students']) }}+ Students
                        @else
                            Growing Community
                        @endif
                    </h3>
                    <p class="text-xs text-blue-200 mt-1">Nationwide Community</p>
                </div>
                <div class="animate-slide-up animation-delay-300 bg-white/10 backdrop-blur-md rounded-2xl p-5 md:p-6 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-14 h-14 mx-auto mb-3 bg-green-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-2xl text-green-300"></i>
                    </div>
                    <h3 class="text-base md:text-lg font-bold text-white">Expert Instructors</h3>
                    <p class="text-xs text-blue-200 mt-1">Industry Professionals</p>
                </div>
                <div class="animate-slide-up animation-delay-400 bg-white/10 backdrop-blur-md rounded-2xl p-5 md:p-6 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-14 h-14 mx-auto mb-3 bg-purple-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-award text-2xl text-purple-300"></i>
                    </div>
                    <h3 class="text-base md:text-lg font-bold text-white">Career Ready</h3>
                    <p class="text-xs text-blue-200 mt-1">Job Placement Support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Explore by Category Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Explore Our Certified Programs</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Choose your learning path from Edutrack's comprehensive government-recognized training programs
            </p>
        </div>

        @if(!empty($featuredByCategory))
            <div class="space-y-16">
                @foreach($featuredByCategory as $categoryName => $categoryCourses)
                    @php $firstCourse = $categoryCourses->first(); $color = $firstCourse->category->color ?? 'blue'; @endphp
                    <!-- Category Header -->
                    <div class="animate-slide-up animation-delay-100">
                        <div class="flex items-center mb-8 border-b pb-4 border-gray-200">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-{{ $color }}-100 rounded-xl flex items-center justify-center shadow-sm">
                                    <i class="fas fa-layer-group text-{{ $color }}-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-2xl font-bold text-gray-900">{{ $categoryName }}</h3>
                            </div>
                        </div>

                        <!-- Course Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            @foreach($categoryCourses as $course)
                                @php
                                    $thumbnailUrl = null;
                                    if (!empty($course->thumbnail_url)) {
                                        $thumbnailUrl = filter_var($course->thumbnail_url, FILTER_VALIDATE_URL)
                                            ? $course->thumbnail_url
                                            : asset('uploads/courses/' . $course->thumbnail_url);
                                    }
                                    $courseColor = $course->category->color ?? 'blue';
                                @endphp
                                <div class="group course-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                                    <!-- Thumbnail -->
                                    <div class="relative h-48 bg-{{ $courseColor }}-50 overflow-hidden">
                                        @if($thumbnailUrl)
                                            <img src="{{ $thumbnailUrl }}" alt="{{ $course->title }}"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i class="fas fa-laptop-code text-4xl text-{{ $courseColor }}-600 opacity-60"></i>
                                            </div>
                                        @endif

                                        <!-- Level Badge -->
                                        <div class="absolute top-3 left-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $courseColor }}-100 text-{{ $courseColor }}-800">
                                                <i class="fas fa-tag mr-1"></i>
                                                {{ $course->level }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="p-6">
                                        <h4 class="text-lg font-bold text-gray-900 line-clamp-2 mb-3 group-hover:text-primary-600 transition-colors min-h-[56px]">
                                            {{ $course->title }}
                                        </h4>

                                        <!-- Instructor & Duration -->
                                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4 border-b border-gray-100">
                                            <span class="flex items-center">
                                                <i class="fas fa-chalkboard-teacher mr-1.5 text-primary-500"></i>
                                                {{ $course->instructor->user->name ?? 'Edutrack Team' }}
                                            </span>
                                            <span class="flex items-center">
                                                <i class="fas fa-clock mr-1.5 text-primary-500"></i>
                                                {{ $course->duration_weeks ? $course->duration_weeks . ' weeks' : 'Flexible' }}
                                            </span>
                                        </div>

                                        <!-- Price and Button -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-xl font-bold text-primary-600">
                                                {{ $course->price == 0 ? 'Free' : 'ZMW ' . number_format($course->price, 2) }}
                                            </span>
                                            <a href="{{ route('courses.show', $course) }}"
                                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 transition duration-200">
                                                View
                                                <i class="fas fa-arrow-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center mt-8 mb-12">
                            <a href="{{ route('courses.index') }}"
                               class="inline-flex items-center px-6 py-2 border border-{{ $color }}-200 text-sm font-medium rounded-full text-{{ $color }}-700 bg-{{ $color }}-50 hover:bg-{{ $color }}-100 transition duration-200">
                                View All {{ $categoryName }}
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500 text-lg">Loading categories...</p>
            </div>
        @endif
    </div>
</section>

<!-- Featured Courses Section (Recent) -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Latest Additions</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Check out the newest TEVETA-certified training programs
            </p>
        </div>

        @if($topFeatured->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($topFeatured as $index => $course)
                    @php
                        $thumbnailUrl = null;
                        if (!empty($course->thumbnail_url)) {
                            $thumbnailUrl = filter_var($course->thumbnail_url, FILTER_VALIDATE_URL)
                                ? $course->thumbnail_url
                                : asset('uploads/courses/' . $course->thumbnail_url);
                        }
                        $courseColor = $course->category->color ?? 'blue';
                    @endphp
                    <div class="group course-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 animate-slide-up animation-delay-{{ $index * 100 }}">
                        <!-- Thumbnail -->
                        <div class="relative h-48 bg-{{ $courseColor }}-50 overflow-hidden">
                            @if($thumbnailUrl)
                                <img src="{{ $thumbnailUrl }}" alt="{{ $course->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                    <i class="fas fa-laptop-code text-4xl text-{{ $courseColor }}-600"></i>
                                </div>
                            @endif

                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 shadow-sm">
                                    <i class="fas fa-check mr-1"></i> New
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <div class="mb-2">
                                <span class="text-xs font-bold text-{{ $courseColor }}-600 uppercase tracking-wide">
                                    {{ $course->category->name ?? 'General' }}
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 line-clamp-2 mb-2 group-hover:text-primary-600 transition-colors min-h-[56px]">
                                {{ $course->title }}
                            </h3>

                            <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed min-h-[60px]">
                                {{ Str::limit($course->short_description ?? $course->description, 100) }}
                            </p>

                            <!-- Instructor & Time -->
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4 border-b border-gray-100">
                                <span class="flex items-center">
                                    <i class="fas fa-user-tie mr-1.5 text-primary-500"></i>
                                    {{ $course->instructor->user->name ?? 'Edutrack Team' }}
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-clock mr-1.5 text-primary-500"></i>
                                    {{ $course->duration_weeks ? $course->duration_weeks . ' wks' : 'Flex' }}
                                </span>
                            </div>

                            <!-- Action -->
                            <div class="flex items-center justify-between">
                                <div class="text-2xl font-bold text-primary-600">
                                    {{ $course->price == 0 ? 'Free' : 'ZMW ' . number_format($course->price, 2) }}
                                </div>
                                <a href="{{ route('courses.show', $course) }}"
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 transition duration-200">
                                    View Course
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<!-- Upcoming Events Preview -->
@if($upcomingEvents->count() > 0)
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Upcoming Events</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Stay connected with workshops, graduations, and community events
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($upcomingEvents as $event)
            <div class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
                <div class="relative h-48 overflow-hidden bg-gradient-to-br from-primary-50 to-blue-50">
                    @if($event->cover_image)
                        <img src="{{ asset($event->cover_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-5xl text-primary-300"></i>
                        </div>
                    @endif
                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-600 text-white">
                            {{ $event->category }}
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <i class="far fa-calendar-alt mr-2 text-primary-500"></i>
                        {{ $event->formatted_date }}
                        @if($event->location)
                            <span class="mx-2">&bull;</span>
                            <i class="fas fa-map-marker-alt mr-1 text-primary-500"></i>
                            {{ $event->location }}
                        @endif
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors">{{ $event->title }}</h3>
                    <p class="text-gray-600 text-sm line-clamp-2">{{ $event->excerpt ?? Str::limit($event->description, 120) }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('events') }}" class="inline-flex items-center px-6 py-3 border border-primary-200 text-primary-700 font-medium rounded-lg hover:bg-primary-50 transition duration-200">
                All Events <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>
@endif

@php
$nextIntakeDate = \App\Models\SystemSetting::get('next_intake_date');
$intakeLabel = \App\Models\SystemSetting::get('next_intake_label');
@endphp
@if($nextIntakeDate || $intakeLabel)
<!-- Next Intake Banner -->
<section class="py-6 bg-secondary-500 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-rocket text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold">{{ $intakeLabel ?: 'Next Intake Coming Soon' }}</h3>
                    @if($nextIntakeDate)
                    <p class="text-white text-opacity-90">Limited spots available</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-6">
                @if($nextIntakeDate)
                <div class="text-center hidden md:block">
                    <div class="text-3xl font-bold" id="countdown-days">--</div>
                    <div class="text-xs uppercase tracking-wide opacity-80">Days Left</div>
                </div>
                @endif
                <a href="{{ route('courses.index') }}" class="px-8 py-3 bg-white text-orange-600 rounded-lg font-bold hover:bg-gray-100 transition shadow-lg">
                    Enroll Now <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Testimonials Section -->
<section class="py-20 bg-gradient-to-br from-gray-900 via-gray-800 to-primary-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">What Our Students Say</h2>
            <p class="text-xl text-gray-300 max-w-2xl mx-auto">
                Real stories from real students who transformed their careers
            </p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-16 text-center">
            <div>
                <div class="text-3xl md:text-4xl font-bold text-secondary-500">{{ number_format($stats['total_students'] ?? 0) }}+</div>
                <div class="text-gray-400 text-sm mt-1">Graduates</div>
            </div>
            <div>
                <div class="text-3xl md:text-4xl font-bold text-secondary-500">{{ number_format($stats['total_enrollments'] ?? 0) }}</div>
                <div class="text-gray-400 text-sm mt-1">Enrollments</div>
            </div>
            <div>
                <div class="text-3xl md:text-4xl font-bold text-secondary-500">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</div>
                <div class="text-gray-400 text-sm mt-1">Average Rating</div>
            </div>
            <div>
                <div class="text-3xl md:text-4xl font-bold text-secondary-500">{{ number_format($stats['total_courses'] ?? 0) }}+</div>
                <div class="text-gray-400 text-sm mt-1">Courses</div>
            </div>
        </div>

        <!-- Testimonial Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($featuredTestimonials as $t)
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/10 animate-slide-up">
                <div class="flex items-center mb-4">
                    <i class="fas fa-quote-left text-secondary-500 text-2xl mr-3"></i>
                    <div class="flex text-yellow-400">
                        @for($i = 0; $i < $t->rating; $i++)
                            <i class="fas fa-star"></i>
                        @endfor
                    </div>
                </div>
                <p class="text-gray-300 mb-6 leading-relaxed">"{{ $t->testimonial_text }}"</p>
                <div class="flex items-center">
                    @if($t->avatar_url)
                        <img src="{{ asset($t->avatar_url) }}" alt="{{ $t->name }}" class="w-10 h-10 rounded-full object-cover mr-3">
                    @else
                        <div class="w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center text-white font-bold text-sm mr-3">
                            {{ strtoupper(substr($t->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="font-semibold text-white">{{ $t->name }}</div>
                        <div class="text-sm text-gray-400">{{ $t->course_taken }} - Class of {{ $t->graduation_year }}</div>
                        @if($t->job_title)
                            <div class="text-xs text-gray-500">{{ $t->job_title }}{{ $t->company ? ' at ' . $t->company : '' }}</div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comment-alt text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">No testimonials yet</h3>
                    <p class="text-gray-400">Be the first to share your success story with us.</p>
                </div>
            @endforelse
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('testimonials') }}" class="inline-flex items-center px-6 py-3 border border-white/30 text-white font-medium rounded-lg hover:bg-white/10 transition duration-200">
                View All Stories <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Edutrack Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose Edutrack?</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                As a TEVETA-registered institution, we're committed to excellence
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center animate-slide-up animation-delay-100">
                <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-certificate text-yellow-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">TEVETA Registered</h3>
                <p class="text-gray-600">Officially recognized by the Technical Education, Vocational and Entrepreneurship Training Authority.</p>
            </div>

            <div class="text-center animate-slide-up animation-delay-200">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-laptop-code text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Practical Skills</h3>
                <p class="text-gray-600">Curriculum designed with industry experts to ensure you learn skills employers actually need.</p>
            </div>

            <div class="text-center animate-slide-up animation-delay-300">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-chalkboard-teacher text-purple-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Expert Tutors</h3>
                <p class="text-gray-600">Learn from certified instructors with real-world industry experience.</p>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
// Countdown timer
@php
$countdownDate = \App\Models\SystemSetting::get('next_intake_date');
@endphp
@if($countdownDate)
const intakeDate = new Date('{{ $countdownDate }}');
function updateCountdown() {
    const now = new Date();
    const diff = intakeDate - now;
    const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
    const el = document.getElementById('countdown-days');
    if (el) el.textContent = days > 0 ? days : 0;
}
updateCountdown();
setInterval(updateCountdown, 60000);
@endif
</script>
@endpush

@endsection
