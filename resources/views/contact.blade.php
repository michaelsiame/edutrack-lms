@extends('layouts.app')

@section('title','Contact Us - Edutrack Computer Training College')
@section('meta_description','Get in touch with Edutrack Computer Training College. Located in Kalomo, Zambia.')

@section('content')

<!-- Page Header -->
<section class="bg-primary-600 text-white py-20">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center">
 <h1 class="text-4xl md:text-5xl font-bold mb-4">Contact Us</h1>
 <p class="text-xl text-primary-100 max-w-3xl mx-auto">
 We'd love to hear from you. Reach out for inquiries, course information, or just to say hello.
 </p>
 </div>
 </div>
</section>

<!-- Contact Content -->
<section class="py-16 bg-white">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

 <!-- Contact Form -->
 <div class="bg-white rounded-xl shadow-lg p-8 animate-slide-up">
 <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>

 @if(session('success'))
 <div class="bg-success-100 border border-success-400 text-success-700 px-4 py-3 rounded mb-6">
 {{ session('success') }}
 </div>
 @endif

 <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
 @csrf

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
 <input type="text" id="name" name="name" required value="{{ old('name') }}"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
 @error('name')
 <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
 @enderror
 </div>
 <div>
 <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
 <input type="email" id="email" name="email" required value="{{ old('email') }}"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
 @error('email')
 <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
 @enderror
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
 <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
 </div>
 <div>
 <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
 <input type="text" id="subject" name="subject" required value="{{ old('subject') }}"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
 @error('subject')
 <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
 @enderror
 </div>
 </div>

 <div>
 <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
 <textarea id="message" name="message" rows="5" required
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">{{ old('message') }}</textarea>
 @error('message')
 <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
 @enderror
 </div>

 <button type="submit" class="w-full md:w-auto px-8 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-lg">
 <i class="fas fa-paper-plane mr-2"></i> Send Message
 </button>
 </form>
 </div>

 <!-- Contact Info -->
 <div class="space-y-8 animate-slide-up animation-delay-200">
 <div class="bg-gray-50 rounded-xl p-8">
 <h2 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h2>

 <div class="space-y-6">
 @php
 $contactAddress = \App\Models\SystemSetting::get('site_address','Kalomo, Zambia');
 $contactPhone = \App\Models\SystemSetting::get('site_phone');
 $contactEmail = \App\Models\SystemSetting::get('site_email');
 @endphp

 <div class="flex items-start">
 <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
 <i class="fas fa-map-marker-alt text-primary-600 text-xl"></i>
 </div>
 <div>
 <h3 class="font-semibold text-gray-900">Address</h3>
 <p class="text-gray-600">{{ $contactAddress }}</p>
 </div>
 </div>

 @if($contactPhone)
 <div class="flex items-start">
 <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
 <i class="fas fa-phone text-primary-600 text-xl"></i>
 </div>
 <div>
 <h3 class="font-semibold text-gray-900">Phone</h3>
 <p class="text-gray-600">{{ $contactPhone }}</p>
 </div>
 </div>
 @endif

 @if($contactEmail)
 <div class="flex items-start">
 <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
 <i class="fas fa-envelope text-primary-600 text-xl"></i>
 </div>
 <div>
 <h3 class="font-semibold text-gray-900">Email</h3>
 <p class="text-gray-600">{{ $contactEmail }}</p>
 </div>
 </div>
 @endif


 </div>
 </div>

 <!-- Social Media -->
 <div class="bg-gray-50 rounded-xl p-8">
 <h3 class="text-xl font-bold text-gray-900 mb-4">Follow Us</h3>
 <div class="flex space-x-4">
 <a href="#" class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center text-white hover:bg-primary-700 transition">
 <i class="fab fa-facebook-f text-xl"></i>
 </a>
 <a href="#" class="w-12 h-12 bg-primary-500 rounded-full flex items-center justify-center text-white hover:bg-primary-600 transition">
 <i class="fab fa-twitter text-xl"></i>
 </a>
 <a href="#" class="w-12 h-12 bg-primary-700 rounded-full flex items-center justify-center text-white hover:bg-primary-800 transition">
 <i class="fab fa-linkedin-in text-xl"></i>
 </a>
 <a href="#" class="w-12 h-12 bg-secondary-600 rounded-full flex items-center justify-center text-white hover:bg-secondary-700 transition">
 <i class="fab fa-instagram text-xl"></i>
 </a>
 </div>
 </div>
 </div>
 </div>
 </div>
</section>

@endsection
