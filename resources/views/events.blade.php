@extends('layouts.app')

@section('title','Recent Events & News - Edutrack Computer Training College')

@push('styles')
<style>
.od-public-header { background: var(--od-navy); color: var(--od-surface); }
</style>
@endpush

@section('content')

<!-- Page Header -->
<section class="od-public-header py-16">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center">
 <p class="od-eyebrow" style="color: var(--od-accent);">STAY UPDATED</p>
 <h1 class="od-h1 mt-2"><i class="fas fa-calendar-alt mr-3"></i>Events & News</h1>
 <p class="od-lead mx-auto mt-4" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
 Stay updated with the latest happenings at Edutrack. From graduation ceremonies to workshops, corporate partnerships, and student achievements.
 </p>
 </div>
 </div>
</section>

<!-- Upcoming Events -->
<section class="py-20" style="background: var(--od-bg);">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="flex items-center justify-between mb-12">
 <h2 class="od-h2 flex items-center"><i class="fas fa-bullhorn mr-3" style="color: var(--od-navy);"></i> Upcoming Events</h2>
 <span class="od-meta">{{ $upcomingEvents->total() }} event{{ $upcomingEvents->total() !== 1 ?'s' :'' }}</span>
 </div>

 @if($upcomingEvents->count() > 0)
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
 @foreach($upcomingEvents as $event)
 <a href="{{ route('events.show', $event) }}" class="group block od-card hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1" style="padding: 0; overflow: hidden;">
 <div class="relative h-48 overflow-hidden" style="background: var(--od-navy-soft);">
 @if($event->cover_image)
 <img src="{{ asset($event->cover_image) }}" alt="{{ $event->title }}"
 class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
 @else
 <div class="w-full h-full flex items-center justify-center">
 <i class="fas fa-calendar-alt text-5xl" style="color: var(--od-navy);"></i>
 </div>
 @endif
 <div class="absolute top-3 left-3">
 <span class="od-badge od-badge-info">{{ $event->category }}</span>
 </div>
 @if($event->is_featured)
 <div class="absolute top-3 right-3">
 <span class="od-badge od-badge-warn"><i class="fas fa-star mr-1"></i> Featured</span>
 </div>
 @endif
 </div>
 <div class="p-6">
 <div class="flex items-center text-sm od-meta mb-2">
 <i class="far fa-calendar-alt mr-2" style="color: var(--od-navy);"></i>
 {{ $event->formatted_date }}
 </div>
 @if($event->location)
 <div class="flex items-center text-sm od-meta mb-3">
 <i class="fas fa-map-marker-alt mr-2" style="color: var(--od-navy);"></i>
 {{ $event->location }}
 </div>
 @endif
 <h3 class="text-lg font-bold mb-2 od-link-hover transition-colors" style="color: var(--od-fg);">{{ $event->title }}</h3>
 <p class="od-meta line-clamp-3">{{ $event->excerpt ?? Str::limit($event->description, 150) }}</p>
 <div class="mt-3 text-sm font-medium" style="color: var(--od-navy);">
 Read More <i class="fas fa-arrow-right ml-1 text-xs"></i>
 </div>
 </div>
 </a>
 @endforeach
 </div>

 <div class="mt-12">
 {{ $upcomingEvents->links() }}
 </div>
 @else
 <div class="text-center py-16 od-card">
 <i class="fas fa-calendar-times text-5xl mb-4" style="color: var(--od-border);"></i>
 <h3 class="od-h3 mb-2">No upcoming events</h3>
 <p class="od-meta mb-6">Check back soon for new workshops, graduations, and community events.</p>
 <a href="{{ route('contact') }}" class="od-btn od-btn-primary">
 <i class="fas fa-envelope mr-2"></i> Get Notified
 </a>
 </div>
 @endif
 </div>
</section>

<!-- Past Events -->
@if($pastEvents->count() > 0)
<section class="py-20" style="background: var(--od-surface);">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <h2 class="od-h2 mb-12 flex items-center"><i class="fas fa-history mr-3" style="color: var(--od-navy);"></i> Past Events</h2>

 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
 @foreach($pastEvents as $event)
 <a href="{{ route('events.show', $event) }}" class="group block od-card hover:shadow-md transition-all duration-300" style="padding: 0; overflow: hidden;">
 <div class="relative h-40 overflow-hidden" style="background: var(--od-bg);">
 @if($event->cover_image)
 <img src="{{ asset($event->cover_image) }}" alt="{{ $event->title }}"
 class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500 opacity-80">
 @else
 <div class="w-full h-full flex items-center justify-center">
 <i class="fas fa-calendar-check text-4xl" style="color: var(--od-border);"></i>
 </div>
 @endif
 <div class="absolute top-3 left-3">
 <span class="od-badge" style="background: var(--od-fg-soft); color: var(--od-muted);">{{ $event->category }}</span>
 </div>
 </div>
 <div class="p-5">
 <div class="flex items-center text-sm od-meta mb-2">
 <i class="far fa-calendar-alt mr-2"></i>
 {{ $event->formatted_date }}
 </div>
 <h3 class="text-base font-bold mb-1 od-link-hover transition-colors" style="color: var(--od-fg);">{{ $event->title }}</h3>
 <p class="od-meta line-clamp-2">{{ $event->excerpt ?? Str::limit($event->description, 100) }}</p>
 </div>
 </a>
 @endforeach
 </div>
 </div>
</section>
@endif

@endsection
