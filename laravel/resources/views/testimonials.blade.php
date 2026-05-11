@extends('layouts.app')

@section('title', 'Student Success Stories - Edutrack Testimonials')

@section('content')

<!-- Page Header -->
<section class="bg-primary-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-heart mr-3"></i>Student Success Stories
            </h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Real stories from real graduates who transformed their careers through Edutrack
            </p>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-12 bg-secondary-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl font-bold text-gray-900">{{ number_format($stats['total_students'] ?? 0) }}+</div>
                <div class="text-gray-800">Success Stories</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-gray-900">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</div>
                <div class="text-gray-800">Average Rating</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-gray-900">{{ number_format($stats['total_enrollments'] ?? 0) }}</div>
                <div class="text-gray-800">Enrollments</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-gray-900">{{ number_format($stats['total_courses'] ?? 0) }}+</div>
                <div class="text-gray-800">Courses</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Testimonials -->
@if($featuredTestimonials->count() > 0)
<section class="py-16 bg-gradient-to-br from-gray-900 via-gray-800 to-primary-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">Featured Stories</h2>
            <p class="text-gray-300">Outstanding graduates who went above and beyond</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($featuredTestimonials as $t)
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 border border-white/10 hover:bg-white/15 transition-all duration-300">
                <div class="flex items-center mb-4">
                    <i class="fas fa-quote-left text-secondary-500 text-2xl mr-3"></i>
                    <div class="flex text-yellow-400">
                        @for($i = 0; $i < $t->rating; $i++)
                            <i class="fas fa-star"></i>
                        @endfor
                    </div>
                </div>
                <p class="text-gray-200 mb-6 leading-relaxed italic">"{{ $t->testimonial_text }}"</p>
                <div class="flex items-center">
                    @if($t->avatar_url)
                        <img src="{{ asset($t->avatar_url) }}" alt="{{ $t->name }}" class="w-12 h-12 rounded-full object-cover mr-4">
                    @else
                        <div class="w-12 h-12 rounded-full bg-primary-500 flex items-center justify-center text-white font-bold text-lg mr-4">
                            {{ strtoupper(substr($t->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="font-semibold text-white">{{ $t->name }}</div>
                        <div class="text-sm text-gray-400">{{ $t->course_taken }} - Class of {{ $t->graduation_year }}</div>
                        @if($t->job_title)
                            <div class="text-xs text-secondary-400">{{ $t->job_title }}{{ $t->company ? ' at ' . $t->company : '' }}</div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- All Testimonials Grid -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">What Our Students Say</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Hear directly from the students whose lives have been changed by quality education
            </p>
        </div>

        @if($testimonials->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($testimonials as $t)
                <div class="bg-white rounded-xl shadow-md p-8 hover:shadow-xl transition-all duration-300 animate-slide-up">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            @for($i = 0; $i < $t->rating; $i++)
                                <i class="fas fa-star"></i>
                            @endfor
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">"{{ $t->testimonial_text }}"</p>
                    <div class="flex items-center pt-4 border-t border-gray-100">
                        @if($t->avatar_url)
                            <img src="{{ asset($t->avatar_url) }}" alt="{{ $t->name }}" class="w-12 h-12 rounded-full object-cover mr-4">
                        @else
                            <div class="w-12 h-12 rounded-full bg-primary-500 flex items-center justify-center text-white font-bold text-lg mr-4">
                                {{ strtoupper(substr($t->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <div class="font-semibold text-gray-900">{{ $t->name }}</div>
                            <div class="text-sm text-gray-500">{{ $t->course_taken }} - Class of {{ $t->graduation_year }}</div>
                            @if($t->job_title)
                                <div class="text-xs text-primary-600">{{ $t->job_title }}{{ $t->company ? ' at ' . $t->company : '' }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $testimonials->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <i class="fas fa-comment-dots text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No testimonials yet</h3>
                <p class="text-gray-500">Be the first to share your success story after completing a course.</p>
            </div>
        @endif
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-primary-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Start Your Success Story Today</h2>
        <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
            Join thousands of students who have transformed their careers through Edutrack's TEVETA-certified programs.
        </p>
        <a href="{{ route('courses.index') }}" class="inline-flex items-center justify-center px-8 py-4 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-600 transition shadow-lg transform hover:-translate-y-1">
            <i class="fas fa-rocket mr-2"></i> Explore Courses
        </a>
    </div>
</section>

@endsection
