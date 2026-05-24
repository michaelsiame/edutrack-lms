@extends('layouts.app')

@section('title', $event->title . ' - Edutrack Events')

@section('content')
<!-- Event Header -->
<section class="bg-primary-600 text-white py-12 md:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('events') }}" class="text-primary-200 hover:text-white text-sm transition-colors">
                <i class="fas fa-arrow-left mr-1"></i>Back to Events
            </a>
        </div>
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white">
                {{ $event->category }}
            </span>
            @if($event->is_featured)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-secondary-400 text-gray-900">
                <i class="fas fa-star mr-1"></i>Featured
            </span>
            @endif
            @if($event->status === 'upcoming')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success-400 text-success-900">
                <i class="fas fa-calendar mr-1"></i>Upcoming
            </span>
            @elseif($event->status === 'ongoing')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-warning-400 text-warning-900">
                <i class="fas fa-play mr-1"></i>Ongoing
            </span>
            @elseif($event->status === 'completed')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-300 text-gray-700">
                <i class="fas fa-check mr-1"></i>Completed
            </span>
            @endif
        </div>
        <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $event->title }}</h1>
        <div class="flex flex-wrap items-center gap-6 text-primary-100">
            <div class="flex items-center">
                <i class="far fa-calendar-alt mr-2"></i>
                {{ $event->event_date?->format('l, F j, Y') }}
            </div>
            @if($event->event_date)
            <div class="flex items-center">
                <i class="far fa-clock mr-2"></i>
                {{ $event->event_date->format('g:i A') }} CAT
            </div>
            @endif
            @if($event->location)
            <div class="flex items-center">
                <i class="fas fa-map-marker-alt mr-2"></i>
                {{ $event->location }}
            </div>
            @endif
        </div>
    </div>
</section>

<!-- Event Content -->
<section class="py-12 md:py-16 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                @if($event->cover_image)
                <div class="rounded-xl overflow-hidden shadow-lg mb-8">
                    <img src="{{ asset($event->cover_image) }}" alt="{{ $event->title }}"
                         class="w-full h-64 md:h-80 object-cover">
                </div>
                @endif

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 md:p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">About This Event</h2>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                </div>

                @if($event->excerpt && $event->excerpt !== $event->description)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 md:p-8 mt-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Highlights</h2>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($event->excerpt)) !!}
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Event Details Card -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Event Details</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="far fa-calendar-alt text-primary-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Date</p>
                                <p class="text-sm text-gray-600">{{ $event->event_date?->format('F j, Y') ?? 'TBD' }}</p>
                            </div>
                        </div>
                        @if($event->event_date)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="far fa-clock text-primary-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Time</p>
                                <p class="text-sm text-gray-600">{{ $event->event_date->format('g:i A') }} CAT</p>
                            </div>
                        </div>
                        @endif
                        @if($event->location)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-primary-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Location</p>
                                <p class="text-sm text-gray-600">{{ $event->location }}</p>
                            </div>
                        </div>
                        @endif
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-tag text-primary-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Category</p>
                                <p class="text-sm text-gray-600 capitalize">{{ $event->category }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact CTA -->
                <div class="bg-primary-600 rounded-xl p-6 text-white">
                    <h3 class="text-lg font-bold mb-2">Interested?</h3>
                    <p class="text-primary-100 text-sm mb-4">Contact us for more information about this event.</p>
                    <a href="{{ route('contact') }}" class="inline-flex items-center px-4 py-2 bg-white text-primary-700 rounded-lg font-medium hover:bg-primary-50 transition-colors text-sm">
                        <i class="fas fa-envelope mr-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Events -->
@if($relatedEvents->isNotEmpty())
<section class="py-12 md:py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">More Events</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($relatedEvents as $related)
            <a href="{{ route('events.show', $related) }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all">
                <div class="relative h-40 overflow-hidden bg-gray-50">
                    @if($related->cover_image)
                    <img src="{{ asset($related->cover_image) }}" alt="{{ $related->title }}"
                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-4xl text-gray-300"></i>
                    </div>
                    @endif
                </div>
                <div class="p-4">
                    <div class="flex items-center text-xs text-gray-500 mb-1">
                        <i class="far fa-calendar-alt mr-1.5 text-primary-500"></i>
                        {{ $related->formatted_date }}
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 group-hover:text-primary-600 transition-colors line-clamp-2">{{ $related->title }}</h3>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
