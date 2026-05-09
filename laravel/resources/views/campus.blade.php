@extends('layouts.app')

@section('title', 'Campus & Facilities - Edutrack Computer Training College')

@section('content')

<!-- Page Header -->
<section class="bg-primary-600 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Our Campus & Facilities</h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Explore our modern learning environment designed for hands-on computer training
            </p>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="py-12 bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-4xl font-bold text-secondary-500">{{ number_format($stats['total_students'] ?? 5000) }}+</div>
                <div class="text-gray-400 mt-1">Students Trained</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-secondary-500">{{ number_format($stats['total_courses'] ?? 25) }}</div>
                <div class="text-gray-400 mt-1">Courses Offered</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-secondary-500">{{ number_format($stats['total_enrollments'] ?? 8000) }}+</div>
                <div class="text-gray-400 mt-1">Total Enrollments</div>
            </div>
        </div>
    </div>
</section>

<!-- Facilities -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Our Facilities</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                State-of-the-art infrastructure to support effective learning
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @php
            $facilities = [
                ['icon' => 'fa-desktop', 'color' => 'blue', 'title' => 'Computer Labs', 'desc' => 'Modern computer labs with high-speed internet and the latest software for hands-on practice.'],
                ['icon' => 'fa-wifi', 'color' => 'green', 'title' => 'High-Speed Internet', 'desc' => 'Campus-wide fiber internet connectivity for seamless online learning and research.'],
                ['icon' => 'fa-chalkboard', 'color' => 'purple', 'title' => 'Smart Classrooms', 'desc' => 'Equipped with projectors, whiteboards, and multimedia systems for interactive lessons.'],
                ['icon' => 'fa-book', 'color' => 'yellow', 'title' => 'Resource Library', 'desc' => 'Access to textbooks, e-books, journals, and digital learning materials.'],
                ['icon' => 'fa-users', 'color' => 'indigo', 'title' => 'Discussion Rooms', 'desc' => 'Collaborative spaces for group projects, study sessions, and peer learning.'],
                ['icon' => 'fa-shield-alt', 'color' => 'red', 'title' => 'Secure Campus', 'desc' => '24/7 security personnel and CCTV surveillance for a safe learning environment.'],
            ];
            @endphp

            @foreach($facilities as $f)
            <div class="bg-gray-50 rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 animate-slide-up">
                <div class="w-14 h-14 bg-{{ $f['color'] }}-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas {{ $f['icon'] }} text-{{ $f['color'] }}-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $f['title'] }}</h3>
                <p class="text-gray-600">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Photo Gallery -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Campus Life</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Glimpses of student life, activities, and our learning environment
            </p>
        </div>

        @php
        $fallbackPhotos = [
            ['image_path' => 'assets/images/group-campus-front-01.jpg', 'title' => 'Students at Campus Front'],
            ['image_path' => 'assets/images/students-outdoor-class-01.jpg', 'title' => 'Outdoor Learning Session'],
            ['image_path' => 'assets/images/group-campus-front-02.jpg', 'title' => 'Student Group with Flag'],
            ['image_path' => 'assets/images/students-outdoor-class-03.jpg', 'title' => 'Practical Training'],
            ['image_path' => 'assets/images/students-banner-portrait-01.jpg', 'title' => 'Student Success Stories'],
            ['image_path' => 'assets/images/students-outdoor-class-05.jpg', 'title' => 'Instructor-led Session'],
            ['image_path' => 'assets/images/group-campus-front-03.jpg', 'title' => 'Campus Activities'],
            ['image_path' => 'assets/images/students-banner-portrait-04.jpg', 'title' => 'Graduate Spotlight'],
            ['image_path' => 'assets/images/students-outdoor-class-02.jpg', 'title' => 'Collaborative Learning'],
        ];
        $displayPhotos = $photos->count() > 0 ? $photos : collect($fallbackPhotos);
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($displayPhotos as $photo)
            <div class="group relative overflow-hidden rounded-xl shadow-lg aspect-[4/3]">
                <img src="{{ asset($photo->image_path ?? $photo['image_path']) }}" alt="{{ $photo->title ?? $photo['title'] }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="absolute bottom-4 left-4 right-4">
                        <h3 class="text-white font-semibold">{{ $photo->title ?? $photo['title'] }}</h3>
                        @if(isset($photo->description) && $photo->description)
                            <p class="text-white/80 text-sm">{{ $photo->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Location -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Visit Our Campus</h2>
                <p class="text-lg text-gray-600 mb-8">
                    Located in the heart of Kalomo, our campus is easily accessible and provides a conducive environment for learning. Come see our facilities and meet our team.
                </p>

                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-primary-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Address</h3>
                            <p class="text-gray-600">Kalomo, Zambia</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-clock text-primary-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Office Hours</h3>
                            <p class="text-gray-600">Monday - Friday: 8:00 AM - 5:00 PM</p>
                            <p class="text-gray-600">Saturday: 8:00 AM - 1:00 PM</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-phone text-primary-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Contact</h3>
                            <p class="text-gray-600">+260 770 666 937</p>
                            <p class="text-gray-600">edutrackzambia@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-200 rounded-xl overflow-hidden h-96 flex items-center justify-center">
                <div class="text-center text-gray-500">
                    <i class="fas fa-map-marked-alt text-6xl mb-4"></i>
                    <p class="text-lg">Kalomo, Zambia</p>
                    <a href="https://maps.google.com/?q=Kalomo,Zambia" target="_blank" class="inline-flex items-center mt-4 text-primary-600 hover:text-primary-800 font-medium">
                        <i class="fas fa-external-link-alt mr-2"></i> Open in Google Maps
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
