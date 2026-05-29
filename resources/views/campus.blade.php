@extends('layouts.app')

@section('title','Campus & Facilities - Edutrack Computer Training College')

@push('styles')
<style>
.od-public-header { background: var(--od-navy); color: var(--od-surface); }
.od-stats-bar { background: var(--od-fg); color: var(--od-surface); }
</style>
@endpush

@section('content')

<!-- Page Header -->
<section class="od-public-header py-20">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center">
 <p class="od-eyebrow" style="color: var(--od-accent);">OUR CAMPUS</p>
 <h1 class="od-h1 mt-2">Our Campus & Facilities</h1>
 <p class="od-lead mx-auto mt-4" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
 Explore our modern learning environment designed for hands-on computer training
 </p>
 </div>
 </div>
</section>

<!-- Stats -->
<section class="od-stats-bar py-12">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
 <div>
 <div class="text-4xl font-bold" style="color: var(--od-accent);">{{ number_format($stats['total_students'] ?? 0) }}+</div>
 <div class="text-sm mt-1" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Students Trained</div>
 </div>
 <div>
 <div class="text-4xl font-bold" style="color: var(--od-accent);">{{ number_format($stats['total_courses'] ?? 0) }}</div>
 <div class="text-sm mt-1" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Courses Offered</div>
 </div>
 <div>
 <div class="text-4xl font-bold" style="color: var(--od-accent);">{{ number_format($stats['total_enrollments'] ?? 0) }}+</div>
 <div class="text-sm mt-1" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Total Enrollments</div>
 </div>
 </div>
 </div>
</section>

<!-- Facilities -->
<section class="py-20" style="background: var(--od-surface);">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-16">
 <p class="od-eyebrow">INFRASTRUCTURE</p>
 <h2 class="od-h2 mt-2">Our Facilities</h2>
 <p class="od-lead mx-auto mt-3">
 State-of-the-art infrastructure to support effective learning
 </p>
 </div>

 @if(isset($facilities) && count($facilities) > 0)
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
 @foreach($facilities as $f)
 <div class="od-card hover:shadow-xl transition-all duration-300 animate-slide-up">
 <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-building text-2xl"></i>
 </div>
 <h3 class="text-xl font-bold mb-3" style="color: var(--od-fg);">{{ $f->name ?? $f['name'] }}</h3>
 <p class="od-meta">{{ $f->description ?? $f['description'] }}</p>
 </div>
 @endforeach
 </div>
 @else
 <div class="text-center py-12">
 <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: var(--od-fg-soft);">
 <i class="fas fa-building text-2xl" style="color: var(--od-muted);"></i>
 </div>
 <h3 class="od-h3 mb-2">Facilities coming soon</h3>
 <p class="od-meta">Check back later for details about our campus facilities.</p>
 </div>
 @endif
 </div>
</section>

<!-- Photo Gallery -->
<section class="py-20" style="background: var(--od-bg);">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-16">
 <p class="od-eyebrow">GALLERY</p>
 <h2 class="od-h2 mt-2">Campus Life</h2>
 <p class="od-lead mx-auto mt-3">
 Glimpses of student life, activities, and our learning environment
 </p>
 </div>

 @if($photos->count() > 0)
 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
 @foreach($photos as $photo)
 <div class="group relative overflow-hidden rounded-xl shadow-lg aspect-[4/3]">
 <img src="{{ asset($photo->image_path) }}" alt="{{ $photo->title }}"
 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
 <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
 <div class="absolute bottom-4 left-4 right-4">
 <h3 class="text-white font-semibold">{{ $photo->title }}</h3>
 @if($photo->description)
 <p class="text-white/80 text-sm">{{ $photo->description }}</p>
 @endif
 </div>
 </div>
 </div>
 @endforeach
 </div>
 @else
 <div class="text-center py-12">
 <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: var(--od-fg-soft);">
 <i class="fas fa-images text-2xl" style="color: var(--od-muted);"></i>
 </div>
 <h3 class="od-h3 mb-2">No photos yet</h3>
 <p class="od-meta">Campus photos will be added soon.</p>
 </div>
 @endif
 </div>
</section>

<!-- Location -->
<section class="py-20" style="background: var(--od-surface);">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
 <div>
 <p class="od-eyebrow">VISIT US</p>
 <h2 class="od-h2 mt-2 mb-6">Visit Our Campus</h2>
 <p class="od-meta text-base mb-8">
 Located in the heart of Kalomo, our campus is easily accessible and provides a conducive environment for learning. Come see our facilities and meet our team.
 </p>

 @php
 $campusAddress = \App\Models\SystemSetting::get('site_address','Kalomo, Zambia');
 $campusPhone = \App\Models\SystemSetting::get('site_phone');
 $campusEmail = \App\Models\SystemSetting::get('site_email');
 @endphp
 <div class="space-y-4">
 <div class="flex items-start">
 <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4 flex-shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-map-marker-alt text-xl"></i>
 </div>
 <div>
 <h3 class="font-semibold" style="color: var(--od-fg);">Address</h3>
 <p class="od-meta">{{ $campusAddress }}</p>
 </div>
 </div>
 <div class="flex items-start">
 <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4 flex-shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-clock text-xl"></i>
 </div>
 <div>
 <h3 class="font-semibold" style="color: var(--od-fg);">Office Hours</h3>
 <p class="od-meta">Monday - Friday: 8:00 AM - 5:00 PM</p>
 <p class="od-meta">Saturday: 8:00 AM - 1:00 PM</p>
 </div>
 </div>
 @if($campusPhone || $campusEmail)
 <div class="flex items-start">
 <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4 flex-shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-phone text-xl"></i>
 </div>
 <div>
 <h3 class="font-semibold" style="color: var(--od-fg);">Contact</h3>
 @if($campusPhone)<p class="od-meta">{{ $campusPhone }}</p>@endif
 @if($campusEmail)<p class="od-meta">{{ $campusEmail }}</p>@endif
 </div>
 </div>
 @endif
 </div>
 </div>

 <div class="rounded-xl overflow-hidden h-96 flex items-center justify-center" style="background: var(--od-bg);">
 <div class="text-center od-meta">
 <i class="fas fa-map-marked-alt text-6xl mb-4" style="color: var(--od-border);"></i>
 <p class="text-lg">{{ $campusAddress }}</p>
 <a href="https://maps.google.com/?q={{ urlencode($campusAddress) }}" target="_blank" class="inline-flex items-center mt-4 font-medium" style="color: var(--od-navy);">
 <i class="fas fa-external-link-alt mr-2"></i> Open in Google Maps
 </a>
 </div>
 </div>
 </div>
 </div>
</section>

@endsection
