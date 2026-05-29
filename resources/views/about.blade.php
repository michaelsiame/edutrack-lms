@extends('layouts.app')

@section('title','About Us - Edutrack Computer Training College')
@section('meta_description','Learn about Edutrack Computer Training College - Quality computer training in Zambia. Our mission, vision, and commitment to quality education.')

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
 <p class="od-eyebrow" style="color: var(--od-accent);">ABOUT US</p>
 <h1 class="od-h1 mt-2">About Edutrack</h1>
 <p class="od-lead mx-auto mt-4" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
 Empowering Zambians through quality computer training and professional education
 </p>
 </div>
 </div>
</section>

<!-- Mission & Vision -->
<section class="py-16" style="background: var(--od-surface);">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
 <!-- Mission -->
 <div class="od-card">
 <div class="w-16 h-16 rounded-full flex items-center justify-center mb-6" style="background: var(--od-navy); color: var(--od-surface);">
 <i class="fas fa-bullseye text-white text-2xl"></i>
 </div>
 <h2 class="od-h2 mb-4">Our Mission</h2>
 <p class="od-meta text-base leading-relaxed">
 To provide accessible, high-quality computer training and technical education that empowers
 individuals with industry-relevant skills, enabling them to compete effectively in the digital economy
 and contribute to Zambia's technological advancement.
 </p>
 </div>

 <!-- Vision -->
 <div class="od-card">
 <div class="w-16 h-16 rounded-full flex items-center justify-center mb-6" style="background: var(--od-navy); color: var(--od-surface);">
 <i class="fas fa-eye text-white text-2xl"></i>
 </div>
 <h2 class="od-h2 mb-4">Our Vision</h2>
 <p class="od-meta text-base leading-relaxed">
 To be Zambia's leading computer training institution, recognized for excellence in technical education,
 innovation in teaching methodologies, and the success of our graduates in building rewarding careers
 in the technology sector.
 </p>
 </div>
 </div>
 </div>
</section>

<!-- Meet Our Team -->
@php
$teamMembers = \App\Models\TeamMember::orderBy('display_order')->get();
@endphp
@if($teamMembers->count() > 0)
<section class="py-20" style="background: var(--od-bg);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <p class="od-eyebrow">OUR PEOPLE</p>
      <h2 class="od-h2 mt-2">Meet Our Team</h2>
      <p class="od-lead mx-auto mt-3">
        The dedicated professionals driving excellence at Edutrack
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
      @foreach($teamMembers as $member)
      <div class="group od-card hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2" style="padding: 0; overflow: hidden;">
        <!-- Photo -->
        <div class="relative h-64 overflow-hidden" style="background: var(--od-fg-soft);">
          @if($member->image_url && file_exists(public_path('uploads/team/' . $member->image_url)))
          <img src="{{ asset('uploads/team/' . $member->image_url) }}"
               alt="{{ $member->name }}"
               class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
          @else
          <div class="w-full h-full flex items-center justify-center" style="background: var(--od-navy);">
            <div class="text-center" style="color: var(--od-surface);">
              <div class="text-4xl font-bold mb-2">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
              <i class="fas fa-user text-2xl opacity-50"></i>
            </div>
          </div>
          @endif
          <!-- Bottom gradient on hover -->
          <div class="absolute inset-0 transition-opacity duration-300 opacity-0 group-hover:opacity-100"
               style="background: linear-gradient(to top, color-mix(in oklch, var(--od-fg), transparent 40%), transparent);"></div>
        </div>

        <!-- Info -->
        <div class="p-5">
          <h3 class="text-lg font-bold mb-1" style="color: var(--od-fg);">{{ $member->name }}</h3>
          <div class="text-xs font-semibold uppercase tracking-wide mb-3" style="color: var(--od-navy);">
            {{ $member->position }}
          </div>
          @if($member->qualifications)
          <div class="pt-3" style="border-top: 1px solid var(--od-border);">
            <p class="text-sm leading-relaxed" style="color: var(--od-muted);">{{ $member->qualifications }}</p>
          </div>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- Why Choose Us -->
<section class="py-20" style="background: var(--od-surface);">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-16">
 <p class="od-eyebrow">WHY EDUTRACK</p>
 <h2 class="od-h2 mt-2">Why Students Choose Edutrack</h2>
 <p class="od-lead mx-auto mt-3">
 As a registered institution, we're committed to excellence
 </p>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
 <div class="od-card hover:shadow-lg transition-all duration-300">
 <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6" style="background: var(--od-accent-soft); color: var(--od-accent);">
 <i class="fas fa-certificate text-2xl"></i>
 </div>
 <h3 class="text-xl font-bold mb-3" style="color: var(--od-fg);">Professionally Certified</h3>
 <p class="od-meta">All our programs are registered and accredited by the Technical Education, Vocational and Entrepreneurship Training Authority.</p>
 </div>

 <div class="od-card hover:shadow-lg transition-all duration-300">
 <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-laptop-code text-2xl"></i>
 </div>
 <h3 class="text-xl font-bold mb-3" style="color: var(--od-fg);">Hands-On Training</h3>
 <p class="od-meta">Learn by doing in our modern computer labs equipped with the latest hardware and software.</p>
 </div>

 <div class="od-card hover:shadow-lg transition-all duration-300">
 <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6" style="background: var(--od-green-soft); color: var(--od-green);">
 <i class="fas fa-briefcase text-2xl"></i>
 </div>
 <h3 class="text-xl font-bold mb-3" style="color: var(--od-fg);">Career Support</h3>
 <p class="od-meta">We help you prepare for the job market with CV writing, interview preparation, and job placement assistance.</p>
 </div>

 <div class="od-card hover:shadow-lg transition-all duration-300">
 <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-chalkboard-teacher text-2xl"></i>
 </div>
 <h3 class="text-xl font-bold mb-3" style="color: var(--od-fg);">Expert Instructors</h3>
 <p class="od-meta">Learn from certified professionals with real-world industry experience and passion for teaching.</p>
 </div>

 <div class="od-card hover:shadow-lg transition-all duration-300">
 <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6" style="background: color-mix(in oklch, var(--od-danger) 10%, transparent); color: var(--od-danger);">
 <i class="fas fa-users text-2xl"></i>
 </div>
 <h3 class="text-xl font-bold mb-3" style="color: var(--od-fg);">Small Class Sizes</h3>
 <p class="od-meta">Personalized attention with limited students per class ensures you get the support you need.</p>
 </div>

 <div class="od-card hover:shadow-lg transition-all duration-300">
 <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6" style="background: var(--od-accent-soft); color: var(--od-accent);">
 <i class="fas fa-money-bill-wave text-2xl"></i>
 </div>
 <h3 class="text-xl font-bold mb-3" style="color: var(--od-fg);">Affordable Fees</h3>
 <p class="od-meta">Quality education at competitive prices with flexible payment plans to suit your budget.</p>
 </div>
 </div>
 </div>
</section>

<!-- CTA Section -->
<section class="od-public-cta py-16">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
 <h2 class="od-h2 mb-4">Ready to Start Your Journey?</h2>
 <p class="od-lead mx-auto mb-8" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
 Join thousands of students who have transformed their careers through Edutrack's professional programs.
 </p>
 <div class="flex flex-col sm:flex-row gap-4 justify-center">
 <a href="{{ route('courses.index') }}" class="od-btn od-btn-primary od-btn-lg">
 <i class="fas fa-book mr-2"></i> Browse Courses
 </a>
 <a href="{{ route('contact') }}" class="od-btn od-btn-secondary od-btn-lg" style="color: var(--od-surface); border-color: color-mix(in oklch, var(--od-surface), transparent 50%);">
 <i class="fas fa-phone mr-2"></i> Contact Us
 </a>
 </div>
 </div>
</section>

@endsection
