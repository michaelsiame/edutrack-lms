@extends('layouts.app')

@section('title', $event->title . ' - Edutrack Events')

@push('styles')
<style>
.od-event-header { background: var(--od-navy); color: var(--od-surface); }
</style>
@endpush

@section('content')
<!-- Event Header -->
<section class="od-event-header py-12 md:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('events') }}" class="text-sm transition-colors hover:opacity-80" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
                <i class="fas fa-arrow-left mr-1"></i>Back to Events
            </a>
        </div>
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <span class="od-badge od-badge-info">{{ $event->category }}</span>
            @if($event->is_featured)
            <span class="od-badge od-badge-warn"><i class="fas fa-star mr-1"></i>Featured</span>
            @endif
            @if($event->status === 'upcoming')
            <span class="od-badge od-badge-success"><i class="fas fa-calendar mr-1"></i>Upcoming</span>
            @elseif($event->status === 'ongoing')
            <span class="od-badge od-badge-warn"><i class="fas fa-play mr-1"></i>Ongoing</span>
            @elseif($event->status === 'completed')
            <span class="od-badge" style="background: var(--od-fg-soft); color: var(--od-muted);"><i class="fas fa-check mr-1"></i>Completed</span>
            @endif
        </div>
        <h1 class="od-h1 mb-4">{{ $event->title }}</h1>
        <div class="flex flex-wrap items-center gap-6" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
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
<section class="py-12 md:py-16" style="background: var(--od-bg);">
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

                <div class="od-card">
                    <h2 class="od-h3 mb-4">About This Event</h2>
                    <div class="prose max-w-none od-meta">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                </div>

                @if($event->excerpt && $event->excerpt !== $event->description)
                <div class="od-card mt-6">
                    <h2 class="od-h3 mb-4">Highlights</h2>
                    <div class="prose max-w-none od-meta">
                        {!! nl2br(e($event->excerpt)) !!}
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Event Details Card -->
                <div class="od-card">
                    <h3 class="od-h3 mb-4">Event Details</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium" style="color: var(--od-fg);">Date</p>
                                <p class="od-meta">{{ $event->event_date?->format('F j, Y') ?? 'TBD' }}</p>
                            </div>
                        </div>
                        @if($event->event_date)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
                                <i class="far fa-clock"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium" style="color: var(--od-fg);">Time</p>
                                <p class="od-meta">{{ $event->event_date->format('g:i A') }} CAT</p>
                            </div>
                        </div>
                        @endif
                        @if($event->location)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium" style="color: var(--od-fg);">Location</p>
                                <p class="od-meta">{{ $event->location }}</p>
                            </div>
                        </div>
                        @endif
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium" style="color: var(--od-fg);">Category</p>
                                <p class="od-meta capitalize">{{ $event->category }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact CTA -->
                <div class="od-card" style="background: var(--od-navy); color: var(--od-surface);">
                    <h3 class="od-h3 mb-2">Interested?</h3>
                    <p class="text-sm mb-4" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">Contact us for more information about this event.</p>
                    <a href="{{ route('contact') }}" class="od-btn od-btn-sm" style="background: var(--od-surface); color: var(--od-fg);">
                        <i class="fas fa-envelope mr-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Events -->
@if($relatedEvents->isNotEmpty())
<section class="py-12 md:py-16" style="background: var(--od-surface);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="od-h2 mb-8">More Events</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($relatedEvents as $related)
            <a href="{{ route('events.show', $related) }}" class="group od-card hover:shadow-md transition-all" style="padding: 0; overflow: hidden;">
                <div class="relative h-40 overflow-hidden" style="background: var(--od-bg);">
                    @if($related->cover_image)
                    <img src="{{ asset($related->cover_image) }}" alt="{{ $related->title }}"
                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-4xl" style="color: var(--od-border);"></i>
                    </div>
                    @endif
                </div>
                <div class="p-4">
                    <div class="flex items-center text-xs od-meta mb-1">
                        <i class="far fa-calendar-alt mr-1.5" style="color: var(--od-navy);"></i>
                        {{ $related->formatted_date }}
                    </div>
                    <h3 class="text-sm font-bold od-link-hover transition-colors line-clamp-2" style="color: var(--od-fg);">{{ $related->title }}</h3>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
