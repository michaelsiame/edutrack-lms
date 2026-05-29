@extends('layouts.app')

@section('title','Contact Us - Edutrack Computer Training College')
@section('meta_description','Get in touch with Edutrack Computer Training College. Located in Kalomo, Zambia.')

@push('styles')
<style>
.od-public-header { background: var(--od-navy); color: var(--od-surface); }
.od-public-cta { background: var(--od-navy); color: var(--od-surface); }
</style>
@endpush

@section('content')

<!-- Page Header -->
<section class="od-public-header py-20">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center">
 <p class="od-eyebrow" style="color: var(--od-accent);">GET IN TOUCH</p>
 <h1 class="od-h1 mt-2">Contact Us</h1>
 <p class="od-lead mx-auto mt-4" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
 We'd love to hear from you. Reach out for inquiries, course information, or just to say hello.
 </p>
 </div>
 </div>
</section>

<!-- Contact Content -->
<section class="py-16" style="background: var(--od-surface);">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

 <!-- Contact Form -->
 <div class="od-card animate-slide-up">
 <h2 class="od-h2 mb-6">Send us a Message</h2>

 @if(session('success'))
 <div class="mb-6 p-4 rounded-lg text-sm font-medium" style="background: var(--od-green-soft); color: var(--od-green);">
 {{ session('success') }}
 </div>
 @endif

 <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
 @csrf

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label for="name" class="od-form-label">Full Name *</label>
 <input type="text" id="name" name="name" required value="{{ old('name') }}" class="od-input">
 @error('name')
 <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
 @enderror
 </div>
 <div>
 <label for="email" class="od-form-label">Email Address *</label>
 <input type="email" id="email" name="email" required value="{{ old('email') }}" class="od-input">
 @error('email')
 <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
 @enderror
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label for="phone" class="od-form-label">Phone Number</label>
 <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" class="od-input">
 </div>
 <div>
 <label for="subject" class="od-form-label">Subject *</label>
 <input type="text" id="subject" name="subject" required value="{{ old('subject') }}" class="od-input">
 @error('subject')
 <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
 @enderror
 </div>
 </div>

 <div>
 <label for="message" class="od-form-label">Message *</label>
 <textarea id="message" name="message" rows="5" required class="od-input">{{ old('message') }}</textarea>
 @error('message')
 <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
 @enderror
 </div>

 <button type="submit" class="od-btn od-btn-primary od-btn-lg">
 <i class="fas fa-paper-plane mr-2"></i> Send Message
 </button>
 </form>
 </div>

 <!-- Contact Info -->
 <div class="space-y-8 animate-slide-up animation-delay-200">
 <div class="od-card">
 <h2 class="od-h2 mb-6">Contact Information</h2>

 <div class="space-y-6">
 @php
 $contactAddress = \App\Models\SystemSetting::get('site_address','Kalomo, Zambia');
 $contactPhone = \App\Models\SystemSetting::get('site_phone');
 $contactEmail = \App\Models\SystemSetting::get('site_email');
 @endphp

 <div class="flex items-start">
 <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4 flex-shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-map-marker-alt text-xl"></i>
 </div>
 <div>
 <h3 class="font-semibold" style="color: var(--od-fg);">Address</h3>
 <p class="od-meta">{{ $contactAddress }}</p>
 </div>
 </div>

 @if($contactPhone)
 <div class="flex items-start">
 <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4 flex-shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-phone text-xl"></i>
 </div>
 <div>
 <h3 class="font-semibold" style="color: var(--od-fg);">Phone</h3>
 <p class="od-meta">{{ $contactPhone }}</p>
 </div>
 </div>
 @endif

 @if($contactEmail)
 <div class="flex items-start">
 <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4 flex-shrink-0" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-envelope text-xl"></i>
 </div>
 <div>
 <h3 class="font-semibold" style="color: var(--od-fg);">Email</h3>
 <p class="od-meta">{{ $contactEmail }}</p>
 </div>
 </div>
 @endif

 </div>
 </div>

 <!-- Social Media -->
 <div class="od-card">
 <h3 class="od-h3 mb-4">Follow Us</h3>
 <div class="flex space-x-4">
 <a href="#" class="w-12 h-12 rounded-full flex items-center justify-center text-white transition hover:opacity-90" style="background: var(--od-navy);">
 <i class="fab fa-facebook-f text-xl"></i>
 </a>
 <a href="#" class="w-12 h-12 rounded-full flex items-center justify-center text-white transition hover:opacity-90" style="background: var(--od-accent);">
 <i class="fab fa-twitter text-xl"></i>
 </a>
 <a href="#" class="w-12 h-12 rounded-full flex items-center justify-center text-white transition hover:opacity-90" style="background: var(--od-navy);">
 <i class="fab fa-linkedin-in text-xl"></i>
 </a>
 <a href="#" class="w-12 h-12 rounded-full flex items-center justify-center text-white transition hover:opacity-90" style="background: var(--od-accent);">
 <i class="fab fa-instagram text-xl"></i>
 </a>
 </div>
 </div>
 </div>
 </div>
 </div>
</section>

@endsection
