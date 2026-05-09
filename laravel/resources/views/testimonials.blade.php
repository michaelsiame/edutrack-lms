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
                <div class="text-4xl font-bold text-gray-900">5,000+</div>
                <div class="text-gray-800">Success Stories</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-gray-900">4.8</div>
                <div class="text-gray-800">Average Rating</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-gray-900">85%</div>
                <div class="text-gray-800">Job Placement</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-gray-900">25+</div>
                <div class="text-gray-800">Courses</div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Grid -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">What Our Students Say</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Hear directly from the students whose lives have been changed by quality education
            </p>
        </div>

        @php
        $testimonials = [
            ['name' => 'Chileshe Banda', 'course' => 'Web Development', 'year' => '2024', 'rating' => 5, 'text' => 'Edutrack changed my life. I went from being unemployed to working as a junior developer at a tech startup in Lusaka. The practical skills I gained were exactly what employers were looking for.'],
            ['name' => 'Mutale Mumba', 'course' => 'Digital Marketing', 'year' => '2023', 'rating' => 5, 'text' => 'The digital marketing course gave me the confidence to start my own agency. Within 6 months of graduating, I had 5 clients and was earning more than my previous job. TEVETA certification really helped.'],
            ['name' => 'Bwalya Chanda', 'course' => 'Data Science', 'year' => '2024', 'rating' => 5, 'text' => 'The instructors at Edutrack are world-class. They don\'t just teach theory - they make sure you can actually build things. The career support after graduation was exceptional.'],
            ['name' => 'Micheal Siame', 'course' => 'Cybersecurity', 'year' => '2024', 'rating' => 5, 'text' => 'I always wanted to work in IT security but didn\'t know where to start. The Cybersecurity program at Edutrack gave me hands-on experience with real tools and scenarios. Now I work as a security analyst.'],
            ['name' => 'Grace Lungu', 'course' => 'Microsoft Office Specialist', 'year' => '2023', 'rating' => 4, 'text' => 'As an administrative assistant, improving my Office skills was essential. The course was practical and immediately applicable to my job. I got a promotion within 3 months of completing the program.'],
            ['name' => 'John Phiri', 'course' => 'Computer Hardware & Networking', 'year' => '2024', 'rating' => 5, 'text' => 'The networking course was comprehensive and practical. We worked with real Cisco equipment and configured actual networks. The certification helped me land a job at an ISP in Livingstone.'],
        ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($testimonials as $t)
            <div class="bg-white rounded-xl shadow-md p-8 hover:shadow-xl transition-all duration-300 animate-slide-up">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400">
                        @for($i = 0; $i < $t['rating']; $i++)
                            <i class="fas fa-star"></i>
                        @endfor
                    </div>
                </div>
                <p class="text-gray-600 mb-6 leading-relaxed">"{{ $t['text'] }}"</p>
                <div class="flex items-center pt-4 border-t border-gray-100">
                    <div class="w-12 h-12 rounded-full bg-primary-500 flex items-center justify-center text-white font-bold text-lg mr-4">
                        {{ strtoupper(substr($t['name'], 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $t['name'] }}</div>
                        <div class="text-sm text-gray-500">{{ $t['course'] }} - Class of {{ $t['year'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
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
