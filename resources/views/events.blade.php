@extends('layouts.app')

@section('title','Recent Events & News - Edutrack Computer Training College')

@section('content')

<!-- Page Header -->
<section class="bg-primary-600 text-white py-16">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center">
 <h1 class="text-4xl md:text-5xl font-bold mb-4">
 <i class="fas fa-calendar-alt mr-3"></i>Events & News
 </h1>
 <p class="text-xl text-primary-100 max-w-3xl mx-auto">
 Stay updated with the latest happenings at Edutrack. From graduation ceremonies to workshops, corporate partnerships, and student achievements.
 </p>
 </div>
 </div>
</section>

<!-- Upcoming Events -->
<section class="py-20 bg-gray-50">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="flex items-center justify-between mb-12">
 <h2 class="text-2xl md:text-3xl font-bold text-gray-900 flex items-center">
 <i class="fas fa-bullhorn text-primary-500 mr-3"></i> Upcoming Events
 </h2>
 <span class="text-sm text-gray-500">{{ $upcomingEvents->total() }} event{{ $upcomingEvents->total() !== 1 ?'s' :'' }}</span>
 </div>

 @if($upcomingEvents->count() > 0)
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
 @foreach($upcomingEvents as $event)
 <div class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
 <div class="relative h-48 overflow-hidden bg-primary-50">
 @if($event->cover_image)
 <img src="{{ asset($event->cover_image) }}" alt="{{ $event->title }}"
 class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
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
 @if($event->is_featured)
 <div class="absolute top-3 right-3">
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-500 text-gray-900">
 <i class="fas fa-star mr-1"></i> Featured
 </span>
 </div>
 @endif
 </div>
 <div class="p-6">
 <div class="flex items-center text-sm text-gray-500 mb-2">
 <i class="far fa-calendar-alt mr-2 text-primary-500"></i>
 {{ $event->formatted_date }}
 </div>
 @if($event->location)
 <div class="flex items-center text-sm text-gray-500 mb-3">
 <i class="fas fa-map-marker-alt mr-2 text-primary-500"></i>
 {{ $event->location }}
 </div>
 @endif
 <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors">{{ $event->title }}</h3>
 <p class="text-gray-600 text-sm line-clamp-3">{{ $event->excerpt ?? Str::limit($event->description, 150) }}</p>
 </div>
 </div>
 @endforeach
 </div>

 <div class="mt-12">
 {{ $upcomingEvents->links() }}
 </div>
 @else
 <div class="text-center py-16 bg-white rounded-xl shadow-sm">
 <i class="fas fa-calendar-times text-5xl text-gray-300 mb-4"></i>
 <h3 class="text-xl font-semibold text-gray-700 mb-2">No upcoming events</h3>
 <p class="text-gray-500 mb-6">Check back soon for new workshops, graduations, and community events.</p>
 <a href="{{ route('contact') }}" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition">
 <i class="fas fa-envelope mr-2"></i> Get Notified
 </a>
 </div>
 @endif
 </div>
</section>

<!-- Past Events -->
@if($pastEvents->count() > 0)
<section class="py-20 bg-white">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-12 flex items-center">
 <i class="fas fa-history text-primary-500 mr-3"></i> Past Events
 </h2>

 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
 @foreach($pastEvents as $event)
 <div class="group bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 border border-gray-100">
 <div class="relative h-40 overflow-hidden bg-gray-50">
 @if($event->cover_image)
 <img src="{{ asset($event->cover_image) }}" alt="{{ $event->title }}"
 class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500 opacity-80">
 @else
 <div class="w-full h-full flex items-center justify-center">
 <i class="fas fa-calendar-check text-4xl text-gray-300"></i>
 </div>
 @endif
 <div class="absolute top-3 left-3">
 <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-600 text-white">
 {{ $event->category }}
 </span>
 </div>
 </div>
 <div class="p-5">
 <div class="flex items-center text-sm text-gray-500 mb-2">
 <i class="far fa-calendar-alt mr-2"></i>
 {{ $event->formatted_date }}
 </div>
 <h3 class="text-base font-bold text-gray-900 mb-1 group-hover:text-primary-600 transition-colors">{{ $event->title }}</h3>
 <p class="text-gray-500 text-sm line-clamp-2">{{ $event->excerpt ?? Str::limit($event->description, 100) }}</p>
 </div>
 </div>
 @endforeach
 </div>
 </div>
</section>
@endif

@endsection
