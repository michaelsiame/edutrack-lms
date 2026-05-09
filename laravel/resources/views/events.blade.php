@extends('layouts.app')

@section('title', 'Recent Events & News - Edutrack Computer Training College')

@section('content')

<!-- Page Header -->
<section class="bg-primary-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-calendar-alt mr-3"></i>Recent Events & News
            </h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Stay updated with the latest happenings at Edutrack. From graduation ceremonies to workshops, corporate partnerships, and student achievements.
            </p>
        </div>
    </div>
</section>

<!-- Events Grid -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @php
        $events = [
            [
                'title' => 'Graduation Ceremony 2025',
                'date' => '2025-03-15',
                'category' => 'Graduation',
                'image' => 'assets/images/group-campus-front-01.jpg',
                'excerpt' => 'Over 200 students graduated with TEVETA-certified diplomas and certificates. The ceremony was attended by industry leaders and government officials.',
                'featured' => true,
            ],
            [
                'title' => 'Cybersecurity Workshop with Industry Experts',
                'date' => '2025-02-20',
                'category' => 'Workshop',
                'image' => 'assets/images/students-outdoor-class-03.jpg',
                'excerpt' => 'A hands-on workshop covering the latest in cybersecurity threats, defense strategies, and career opportunities in the field.',
                'featured' => true,
            ],
            [
                'title' => 'Partnership with MTN Zambia',
                'date' => '2025-01-10',
                'category' => 'Partnership',
                'image' => 'assets/images/group-campus-front-02.jpg',
                'excerpt' => 'Edutrack signed an MOU with MTN Zambia to provide internship opportunities for our top-performing students.',
                'featured' => false,
            ],
            [
                'title' => 'Digital Marketing Bootcamp',
                'date' => '2024-12-05',
                'category' => 'Bootcamp',
                'image' => 'assets/images/students-outdoor-class-01.jpg',
                'excerpt' => 'An intensive 3-day bootcamp covering social media marketing, SEO, content creation, and analytics.',
                'featured' => false,
            ],
            [
                'title' => 'Student Hackathon 2024',
                'date' => '2024-11-18',
                'category' => 'Competition',
                'image' => 'assets/images/students-outdoor-class-02.jpg',
                'excerpt' => 'Teams of students competed to build innovative web applications. The winning team received a full scholarship for advanced courses.',
                'featured' => false,
            ],
            [
                'title' => 'New Computer Lab Opening',
                'date' => '2024-10-30',
                'category' => 'Facility',
                'image' => 'assets/images/students-outdoor-class-05.jpg',
                'excerpt' => 'We opened a new state-of-the-art computer lab with 50 high-performance workstations, dedicated servers, and networking equipment.',
                'featured' => false,
            ],
        ];
        @endphp

        <!-- Featured Events -->
        @php $featured = array_filter($events, fn($e) => $e['featured']); @endphp
        @if(!empty($featured))
        <div class="mb-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-8 flex items-center">
                <i class="fas fa-star text-yellow-500 mr-2"></i> Featured Events
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach($featured as $event)
                <div class="group bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="relative h-64 overflow-hidden">
                        <img src="{{ asset($event['image']) }}" alt="{{ $event['title'] }}"
                             class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-4 left-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-600 text-white">
                                {{ $event['category'] }}
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <i class="far fa-calendar-alt mr-2"></i>
                            {{ date('F j, Y', strtotime($event['date'])) }}
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors">{{ $event['title'] }}</h3>
                        <p class="text-gray-600">{{ $event['excerpt'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- All Events -->
        <h2 class="text-2xl font-bold text-gray-900 mb-8">All Events</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($events as $event)
            <div class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300">
                <div class="relative h-48 overflow-hidden">
                    <img src="{{ asset($event['image']) }}" alt="{{ $event['title'] }}"
                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-600 text-white">
                            {{ $event['category'] }}
                        </span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <i class="far fa-calendar-alt mr-2"></i>
                        {{ date('F j, Y', strtotime($event['date'])) }}
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors">{{ $event['title'] }}</h3>
                    <p class="text-gray-600 text-sm">{{ $event['excerpt'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
