@extends('layouts.app')

@section('title', 'Edutrack Computer Training College | Tech Training in Zambia')
@section('meta_description', 'Edutrack Computer Training College - TEVETA-registered computer training in Kalomo, Zambia. Learn web development, digital marketing, graphic design, and more. Enroll today.')

@push('styles')
<style>
.od-hero { position: relative; color: var(--od-surface); overflow: hidden; min-height: 640px; }
.od-hero-overlay { position: absolute; inset: 0; background: color-mix(in oklch, var(--od-fg), transparent 25%); }
.od-hero-content { position: relative; }
.od-intake-banner { background: var(--od-accent); color: var(--od-fg); }
.od-testimonials-dark { background: var(--od-fg); color: var(--od-surface); }
.od-step-line { position: absolute; left: 28px; top: 56px; bottom: 0; width: 2px; background: var(--od-border); }
@media (min-width: 768px) {
  .od-step-line { left: 50%; transform: translateX(-1px); }
}
</style>
@endpush

@section('content')

<!-- Hero Section -->
<section class="od-hero">
  <!-- Background Image with Overlay -->
  <div class="absolute inset-0">
    <img src="{{ asset('assets/images/hero-bg-1.jpg') }}" alt="Edutrack Campus" class="w-full h-full object-cover">
    <div class="od-hero-overlay"></div>
  </div>

  <div class="od-hero-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32">
    <div class="text-center rounded-3xl p-6 md:p-12" style="background: color-mix(in oklch, var(--od-fg), transparent 75%); backdrop-filter: blur(10px);">
      <div class="mb-6 animate-fade-in">
        <span class="inline-flex items-center px-5 py-2.5 rounded-full text-sm font-bold shadow-lg border-2" style="background: var(--od-accent); color: var(--od-fg); border-color: color-mix(in oklch, var(--od-accent), black 10%);">
          <i class="fas fa-certificate mr-2"></i>
          TEVETA Registered Institution
        </span>
      </div>
      <h1 class="od-h1 animate-fade-in" style="color: var(--od-surface);">
        Transform Your Future with
        <span class="block mt-2" style="color: var(--od-accent);">Edutrack Computer Training College</span>
      </h1>
      <p class="text-lg md:text-xl lg:text-2xl mb-10 max-w-3xl mx-auto leading-relaxed" style="color: color-mix(in oklch, var(--od-surface), transparent 12%);">
        Practical computer training in Kalomo. No theory overload. Just real skills — web design, digital marketing, graphic design, and office packages — taught by instructors who've done the work.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center items-center max-w-2xl mx-auto pb-6">
        @auth
        <a href="{{ route('dashboard') }}" class="od-btn od-btn-lg" style="background: var(--od-surface); color: var(--od-fg);">
          <i class="fas fa-tachometer-alt mr-2"></i>
          Go to Dashboard
        </a>
        @else
        <a href="{{ route('register') }}" class="od-btn od-btn-primary od-btn-lg">
          <i class="fas fa-user-plus mr-2"></i>
          Start Learning Free
        </a>
        @endauth
        <a href="{{ route('courses.index') }}" class="od-btn od-btn-lg od-btn-secondary" style="color: var(--od-surface); border-color: color-mix(in oklch, var(--od-surface), transparent 50%);">
          <i class="fas fa-book mr-2"></i>
          Browse Courses
        </a>
      </div>
      <p class="text-sm" style="color: color-mix(in oklch, var(--od-surface), transparent 35%);">
        <i class="fas fa-check-circle mr-1" style="color: var(--od-accent);"></i> No prior experience needed &nbsp;|&nbsp;
        <i class="fas fa-check-circle mr-1" style="color: var(--od-accent);"></i> Flexible payment plans &nbsp;|&nbsp;
        <i class="fas fa-check-circle mr-1" style="color: var(--od-accent);"></i> Certificate on completion
      </p>
    </div>

    <!-- Trust Indicators -->
    <div class="mt-10 md:mt-14 pt-8" style="border-top: 1px solid color-mix(in oklch, var(--od-surface), transparent 80%);">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        <div class="animate-slide-up animation-delay-100 rounded-2xl p-5 md:p-6 text-center border transition-all duration-300 hover:bg-white/15" style="background: color-mix(in oklch, var(--od-surface), transparent 90%); backdrop-filter: blur(8px); border-color: color-mix(in oklch, var(--od-surface), transparent 70%);">
          <div class="w-14 h-14 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background: color-mix(in oklch, var(--od-accent), transparent 80%);">
            <i class="fas fa-certificate text-2xl" style="color: var(--od-accent);"></i>
          </div>
          <h3 class="text-base md:text-lg font-bold" style="color: var(--od-surface);">TEVETA Registered</h3>
          <p class="text-xs mt-1" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Government Certified (TVA/2064)</p>
        </div>
        <div class="animate-slide-up animation-delay-200 rounded-2xl p-5 md:p-6 text-center border transition-all duration-300 hover:bg-white/15" style="background: color-mix(in oklch, var(--od-surface), transparent 90%); backdrop-filter: blur(8px); border-color: color-mix(in oklch, var(--od-surface), transparent 70%);">
          <div class="w-14 h-14 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background: color-mix(in oklch, var(--od-navy), transparent 80%);">
            <i class="fas fa-users text-2xl" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);"></i>
          </div>
          <h3 class="text-base md:text-lg font-bold" style="color: var(--od-surface);">
            @if(($stats['total_students'] ?? 0) > 0)
            {{ number_format($stats['total_students']) }}+ Graduates
            @else
            Growing Alumni
            @endif
          </h3>
          <p class="text-xs mt-1" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Across Zambia</p>
        </div>
        <div class="animate-slide-up animation-delay-300 rounded-2xl p-5 md:p-6 text-center border transition-all duration-300 hover:bg-white/15" style="background: color-mix(in oklch, var(--od-surface), transparent 90%); backdrop-filter: blur(8px); border-color: color-mix(in oklch, var(--od-surface), transparent 70%);">
          <div class="w-14 h-14 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background: color-mix(in oklch, var(--od-accent), transparent 80%);">
            <i class="fas fa-briefcase text-2xl" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);"></i>
          </div>
          <h3 class="text-base md:text-lg font-bold" style="color: var(--od-surface);">Job-Ready Skills</h3>
          <p class="text-xs mt-1" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Project-Based Learning</p>
        </div>
        <div class="animate-slide-up animation-delay-400 rounded-2xl p-5 md:p-6 text-center border transition-all duration-300 hover:bg-white/15" style="background: color-mix(in oklch, var(--od-surface), transparent 90%); backdrop-filter: blur(8px); border-color: color-mix(in oklch, var(--od-surface), transparent 70%);">
          <div class="w-14 h-14 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background: color-mix(in oklch, var(--od-navy), transparent 80%);">
            <i class="fas fa-hand-holding-usd text-2xl" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);"></i>
          </div>
          <h3 class="text-base md:text-lg font-bold" style="color: var(--od-surface);">Pay in Instalments</h3>
          <p class="text-xs mt-1" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Mobile Money Accepted</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- How It Works Section -->
<section class="py-20" style="background: var(--od-surface);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <p class="od-eyebrow">SIMPLE PROCESS</p>
      <h2 class="od-h2 mt-2">How Edutrack Works</h2>
      <p class="od-lead mx-auto mt-3">
        From your first enquiry to your certificate — we've made it straightforward
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
      @php
      $steps = [
        ['icon' => 'fa-search', 'title' => 'Choose a Course', 'desc' => 'Browse our programs and pick what fits your goals and budget.'],
        ['icon' => 'fa-edit', 'title' => 'Register Online', 'desc' => 'Fill out a simple form. Pay a small registration fee to reserve your spot.'],
        ['icon' => 'fa-laptop', 'title' => 'Learn by Doing', 'desc' => 'Attend classes, complete real projects, and get feedback from instructors.'],
        ['icon' => 'fa-award', 'title' => 'Get Certified', 'desc' => 'Pass your assessments and receive a recognized certificate to show employers.'],
      ];
      @endphp
      @foreach($steps as $index => $step)
      <div class="relative text-center animate-slide-up animation-delay-{{ ($index + 1) * 100 }}">
        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5 text-xl font-bold shadow-md" style="background: {{ $index % 2 === 0 ? 'var(--od-navy)' : 'var(--od-accent)' }}; color: {{ $index % 2 === 0 ? 'var(--od-surface)' : 'var(--od-fg)' }};">
          {{ $index + 1 }}
        </div>
        <h3 class="text-lg font-semibold mb-2" style="color: var(--od-fg);">{{ $step['title'] }}</h3>
        <p class="text-sm leading-relaxed text-center" style="color: var(--od-muted);">{{ $step['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- Explore by Category Section -->
<section class="py-20" style="background: var(--od-bg);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <p class="od-eyebrow">CERTIFIED PROGRAMS</p>
      <h2 class="od-h2 mt-2">Explore Our Programs</h2>
      <p class="od-lead mx-auto mt-3">
        Government-recognized training programs designed for the Zambian job market
      </p>
    </div>

    @if(!empty($featuredByCategory))
    <div class="space-y-16">
      @foreach($featuredByCategory as $categoryName => $categoryCourses)
      @php
        $firstCourse = $categoryCourses->first();
        $catIcon = match($categoryName) {
          'Web Development', 'Programming' => 'fa-code',
          'Digital Marketing', 'Marketing' => 'fa-bullhorn',
          'Graphic Design', 'Design' => 'fa-paint-brush',
          'Data Science', 'Data' => 'fa-chart-line',
          'Cybersecurity' => 'fa-shield-alt',
          'Office Applications', 'ICT' => 'fa-desktop',
          default => 'fa-layer-group',
        };
        $isOdd = $loop->iteration % 2 === 1;
      @endphp
      <!-- Category Header -->
      <div class="animate-slide-up animation-delay-100">
        <div class="flex items-center mb-8 border-b pb-4" style="border-color: var(--od-border);">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-sm" style="background: {{ $isOdd ? 'var(--od-navy-soft)' : 'var(--od-accent-soft)' }};">
              <i class="fas {{ $catIcon }} text-lg" style="color: {{ $isOdd ? 'var(--od-navy)' : 'var(--od-accent)' }};"></i>
            </div>
          </div>
          <div class="ml-4">
            <h3 class="od-h3">{{ $categoryName }}</h3>
          </div>
        </div>

        <!-- Course Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          @foreach($categoryCourses as $course)
          @php
          $thumbnailUrl = $course->thumbnail_image_url;
          @endphp
          <div class="group od-card hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2" style="padding: 0; overflow: hidden;">
            <!-- Thumbnail -->
            <div class="relative h-48 overflow-hidden" style="background: var(--od-bg);">
              @if($thumbnailUrl)
              <img src="{{ $thumbnailUrl }}" alt="{{ $course->title }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
              @else
              <div class="w-full h-full flex items-center justify-center">
                <i class="fas fa-laptop-code text-4xl" style="color: var(--od-muted); opacity: 0.5;"></i>
              </div>
              @endif

              <!-- Level Badge -->
              <div class="absolute top-3 left-3">
                <span class="od-badge" style="background: var(--od-navy-soft); color: var(--od-navy);">
                  <i class="fas fa-tag mr-1"></i>
                  {{ $course->level }}
                </span>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6">
              <h4 class="text-lg font-bold line-clamp-2 mb-3 od-link-hover transition-colors min-h-[56px]" style="color: var(--od-fg);">
                {{ $course->title }}
              </h4>

              <!-- Instructor & Duration -->
              <div class="flex items-center justify-between text-sm od-meta mb-4 pb-4" style="border-bottom: 1px solid var(--od-border);">
                <span class="flex items-center">
                  <i class="fas fa-chalkboard-teacher mr-1.5" style="color: var(--od-navy);"></i>
                  {{ $course->instructor->user->name ?? 'Edutrack Team' }}
                </span>
                <span class="flex items-center">
                  <i class="fas fa-clock mr-1.5" style="color: var(--od-navy);"></i>
                  {{ $course->duration_weeks ? $course->duration_weeks . ' weeks' : 'Flexible' }}
                </span>
              </div>

              <!-- Price and Button -->
              <div class="flex items-center justify-between">
                <span class="text-xl font-bold" style="color: var(--od-navy);">
                  {{ $course->price == 0 ? 'Free' : 'ZMW ' . number_format($course->price, 2) }}
                </span>
                <a href="{{ route('courses.show', $course) }}"
                  class="od-btn od-btn-primary od-btn-sm">
                  View Details
                  <i class="fas fa-arrow-right ml-1"></i>
                </a>
              </div>
            </div>
          </div>
          @endforeach
        </div>

        <div class="text-center mt-8 mb-12">
          <a href="{{ route('courses.index') }}"
            class="od-btn od-btn-secondary">
            All {{ $categoryName }} Courses
            <i class="fas fa-arrow-right ml-2"></i>
          </a>
        </div>
      </div>
      @endforeach
    </div>
    @else
    <div class="text-center py-12">
      <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: var(--od-fg-soft);">
        <i class="fas fa-spinner fa-spin text-2xl" style="color: var(--od-muted);"></i>
      </div>
      <p class="od-meta text-lg">Loading programs...</p>
    </div>
    @endif
  </div>
</section>

<!-- Featured Courses Section (Recent) -->
<section class="py-20" style="background: var(--od-surface);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <p class="od-eyebrow">NEW ARRIVALS</p>
      <h2 class="od-h2 mt-2">Latest Courses</h2>
      <p class="od-lead mx-auto mt-3">
        Fresh training programs added to keep you ahead of the curve
      </p>
    </div>

    @if($topFeatured->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      @foreach($topFeatured as $index => $course)
      @php
      $thumbnailUrl = $course->thumbnail_image_url;
      @endphp
      <div class="group od-card hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 animate-slide-up animation-delay-{{ $index * 100 }}" style="padding: 0; overflow: hidden;">
        <!-- Thumbnail -->
        <div class="relative h-48 overflow-hidden" style="background: var(--od-bg);">
          @if($thumbnailUrl)
          <img src="{{ $thumbnailUrl }}" alt="{{ $course->title }}"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
          @else
          <div class="w-full h-full flex items-center justify-center">
            <i class="fas fa-laptop-code text-4xl" style="color: var(--od-muted); opacity: 0.5;"></i>
          </div>
          @endif

          <div class="absolute top-3 right-3">
            <span class="od-badge od-badge-warn">
              <i class="fas fa-check mr-1"></i> New
            </span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="mb-2">
            <span class="text-xs font-bold uppercase tracking-wide" style="color: var(--od-navy);">
              {{ $course->category->name ?? 'General' }}
            </span>
          </div>

          <h3 class="text-lg font-bold line-clamp-2 mb-2 od-link-hover transition-colors min-h-[56px]" style="color: var(--od-fg);">
            {{ $course->title }}
          </h3>

          <p class="od-meta mb-4 line-clamp-3 leading-relaxed min-h-[60px]">
            {{ Str::limit($course->short_description ?? $course->description, 100) }}
          </p>

          <!-- Instructor & Time -->
          <div class="flex items-center justify-between text-sm od-meta mb-4 pb-4" style="border-bottom: 1px solid var(--od-border);">
            <span class="flex items-center">
              <i class="fas fa-user-tie mr-1.5" style="color: var(--od-navy);"></i>
              {{ $course->instructor->user->name ?? 'Edutrack Team' }}
            </span>
            <span class="flex items-center">
              <i class="fas fa-clock mr-1.5" style="color: var(--od-navy);"></i>
              {{ $course->duration_weeks ? $course->duration_weeks . ' wks' : 'Flex' }}
            </span>
          </div>

          <!-- Action -->
          <div class="flex items-center justify-between">
            <div class="text-2xl font-bold" style="color: var(--od-navy);">
              {{ $course->price == 0 ? 'Free' : 'ZMW ' . number_format($course->price, 2) }}
            </div>
            <a href="{{ route('courses.show', $course) }}"
              class="od-btn od-btn-primary od-btn-sm">
              View Course
            </a>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @endif
  </div>
</section>

<!-- Upcoming Events Preview -->
@if($upcomingEvents->count() > 0)
<section class="py-20" style="background: var(--od-bg);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <p class="od-eyebrow">COMMUNITY</p>
      <h2 class="od-h2 mt-2">Upcoming Events</h2>
      <p class="od-lead mx-auto mt-3">
        Workshops, open days, and networking events in Kalomo
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      @foreach($upcomingEvents as $event)
      <div class="group od-card hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1" style="padding: 0; overflow: hidden;">
        <div class="relative h-48 overflow-hidden" style="background: var(--od-navy-soft);">
          @if($event->cover_image)
          <img src="{{ asset($event->cover_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
          @else
          <div class="w-full h-full flex items-center justify-center">
            <i class="fas fa-calendar-alt text-5xl" style="color: var(--od-navy);"></i>
          </div>
          @endif
          <div class="absolute top-3 left-3">
            <span class="od-badge od-badge-info">{{ $event->category }}</span>
          </div>
        </div>
        <div class="p-6">
          <div class="flex items-center text-sm od-meta mb-2">
            <i class="far fa-calendar-alt mr-2" style="color: var(--od-navy);"></i>
            {{ $event->formatted_date }}
            @if($event->location)
            <span class="mx-2">&bull;</span>
            <i class="fas fa-map-marker-alt mr-1" style="color: var(--od-navy);"></i>
            {{ $event->location }}
            @endif
          </div>
          <h3 class="text-lg font-bold mb-2 od-link-hover transition-colors" style="color: var(--od-fg);">{{ $event->title }}</h3>
          <p class="od-meta line-clamp-2">{{ $event->excerpt ?? Str::limit($event->description, 120) }}</p>
        </div>
      </div>
      @endforeach
    </div>

    <div class="text-center mt-10">
      <a href="{{ route('events') }}" class="od-btn od-btn-secondary od-btn-lg">
        All Events <i class="fas fa-arrow-right ml-2"></i>
      </a>
    </div>
  </div>
</section>
@endif

@php
$nextIntakeDate = \App\Models\SystemSetting::get('next_intake_date');
$intakeLabel = \App\Models\SystemSetting::get('next_intake_label');
@endphp
@if($nextIntakeDate || $intakeLabel)
<!-- Next Intake Banner -->
<section class="od-intake-banner py-6">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full flex items-center justify-center" style="background: color-mix(in oklch, var(--od-fg), transparent 80%);">
          <i class="fas fa-rocket text-2xl" style="color: var(--od-fg);"></i>
        </div>
        <div>
          <h3 class="text-xl font-bold" style="color: var(--od-fg);">{{ $intakeLabel ?: 'Next Intake Coming Soon' }}</h3>
          @if($nextIntakeDate)
          <p style="color: color-mix(in oklch, var(--od-fg), transparent 30%);">Limited spots — register early to secure your place</p>
          @endif
        </div>
      </div>
      <div class="flex items-center gap-6">
        @if($nextIntakeDate)
        <div class="text-center hidden md:block">
          <div class="text-3xl font-bold od-num" id="countdown-days">--</div>
          <div class="text-xs uppercase tracking-wide" style="color: color-mix(in oklch, var(--od-fg), transparent 30%);">Days Left</div>
        </div>
        @endif
        <a href="{{ route('courses.index') }}" class="od-btn od-btn-lg" style="background: var(--od-fg); color: var(--od-surface);">
          Reserve Your Spot <i class="fas fa-arrow-right ml-2"></i>
        </a>
      </div>
    </div>
  </div>
</section>
@endif

<!-- Testimonials Section -->
<section class="od-testimonials-dark py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <p class="od-eyebrow" style="color: var(--od-accent);">TESTIMONIALS</p>
      <h2 class="od-h2 mt-2">What Our Graduates Say</h2>
      <p class="od-lead mx-auto mt-3" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">
        Real stories from students who started exactly where you are
      </p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-16 text-center">
      <div>
        <div class="text-3xl md:text-4xl font-bold od-num" style="color: var(--od-accent);">{{ number_format($stats['total_students'] ?? 0) }}+</div>
        <div class="text-sm mt-1" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Graduates</div>
      </div>
      <div>
        <div class="text-3xl md:text-4xl font-bold od-num" style="color: var(--od-accent);">{{ number_format($stats['total_enrollments'] ?? 0) }}</div>
        <div class="text-sm mt-1" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Enrollments</div>
      </div>
      <div>
        <div class="text-3xl md:text-4xl font-bold od-num" style="color: var(--od-accent);">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</div>
        <div class="text-sm mt-1" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Average Rating</div>
      </div>
      <div>
        <div class="text-3xl md:text-4xl font-bold od-num" style="color: var(--od-accent);">{{ number_format($stats['total_courses'] ?? 0) }}+</div>
        <div class="text-sm mt-1" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Courses</div>
      </div>
    </div>

    <!-- Testimonial Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      @forelse($featuredTestimonials as $t)
      <div class="od-card animate-slide-up" style="background: color-mix(in oklch, var(--od-surface), transparent 92%); border-color: color-mix(in oklch, var(--od-surface), transparent 85%);">
        <div class="flex items-center mb-4">
          <i class="fas fa-quote-left text-2xl mr-3" style="color: var(--od-accent);"></i>
          <div class="flex" style="color: var(--od-accent);">
            @for($i = 0; $i < $t->rating; $i++)
            <i class="fas fa-star"></i>
            @endfor
          </div>
        </div>
        <p class="mb-6 leading-relaxed" style="color: color-mix(in oklch, var(--od-surface), transparent 15%);">"{{ $t->testimonial_text }}"</p>
        <div class="flex items-center">
          @if($t->avatar_url)
          <img src="{{ asset($t->avatar_url) }}" alt="{{ $t->name }}" class="w-10 h-10 rounded-full object-cover mr-3">
          @else
          <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mr-3" style="background: var(--od-navy); color: var(--od-surface);">
            {{ strtoupper(substr($t->name, 0, 1)) }}
          </div>
          @endif
          <div>
            <div class="font-semibold" style="color: var(--od-surface);">{{ $t->name }}</div>
            <div class="text-sm" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">{{ $t->course_taken }} - Class of {{ $t->graduation_year }}</div>
            @if($t->job_title)
            <div class="text-xs" style="color: var(--od-accent);">{{ $t->job_title }}{{ $t->company ? ' at ' . $t->company : '' }}</div>
            @endif
          </div>
        </div>
      </div>
      @empty
      <div class="col-span-full text-center py-12">
        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: color-mix(in oklch, var(--od-surface), transparent 85%);">
          <i class="fas fa-comment-alt text-2xl" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);"></i>
        </div>
        <h3 class="text-xl font-semibold mb-2" style="color: var(--od-surface);">No testimonials yet</h3>
        <p style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Be the first to share your success story with us.</p>
      </div>
      @endforelse
    </div>

    <div class="text-center mt-12">
      <a href="{{ route('testimonials') }}" class="od-btn od-btn-lg od-btn-secondary" style="color: var(--od-surface); border-color: color-mix(in oklch, var(--od-surface), transparent 50%);">
        Read All Stories <i class="fas fa-arrow-right ml-2"></i>
      </a>
    </div>
  </div>
</section>

<!-- Why Choose Edutrack Section -->
<section class="py-20" style="background: var(--od-bg);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <p class="od-eyebrow">WHY US</p>
      <h2 class="od-h2 mt-2">Why Students Choose Edutrack</h2>
      <p class="od-lead mx-auto mt-3">
        We're not the biggest college in Zambia — we're the one that gets you hired
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      @php
      $reasons = [
        ['icon' => 'fa-certificate', 'title' => 'TEVETA Registered', 'desc' => 'Fully certified by the Technical Education, Vocational and Entrepreneurship Training Authority. Your certificate is recognized nationwide.'],
        ['icon' => 'fa-laptop-code', 'title' => 'Learn by Building', 'desc' => 'Every course includes real projects — websites, marketing campaigns, design portfolios — that you can show employers.'],
        ['icon' => 'fa-chalkboard-teacher', 'title' => 'Instructors Who Work', 'desc' => 'Our tutors are active freelancers and agency professionals. They teach what actually works today, not outdated theory.'],
        ['icon' => 'fa-hand-holding-usd', 'title' => 'Flexible Payments', 'desc' => 'Pay the full fee upfront or spread it across instalments. We accept MTN, Airtel, Zamtel, and bank transfers.'],
        ['icon' => 'fa-briefcase', 'title' => 'Job Placement Help', 'desc' => 'We connect strong graduates with local businesses and remote freelance opportunities. Many of our students start earning before they finish.'],
        ['icon' => 'fa-headset', 'title' => 'Support That Lasts', 'desc' => 'Stuck on a project after class? Our instructors are available via WhatsApp and email to help you keep moving.'],
      ];
      @endphp
      @foreach($reasons as $index => $reason)
      <div class="od-card animate-slide-up animation-delay-{{ ($index + 1) * 100 }}" style="padding: 28px;">
        <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-5 shadow-sm" style="background: {{ $index % 2 === 0 ? 'var(--od-navy-soft)' : 'var(--od-accent-soft)' }}; color: {{ $index % 2 === 0 ? 'var(--od-navy)' : 'var(--od-accent)' }};">
          <i class="fas {{ $reason['icon'] }} text-xl"></i>
        </div>
        <h3 class="text-lg font-semibold mb-2" style="color: var(--od-fg);">{{ $reason['title'] }}</h3>
        <p class="text-sm leading-relaxed" style="color: var(--od-muted);">{{ $reason['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- Mini FAQ Teaser -->
<section class="py-20" style="background: var(--od-surface);">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <p class="od-eyebrow">COMMON QUESTIONS</p>
      <h2 class="od-h2 mt-2">Still Unsure?</h2>
      <p class="od-lead mx-auto mt-3">
        Here are the questions we get asked most often
      </p>
    </div>

    <div class="space-y-4" x-data="{ openFaq: null }">
      @php
      $faqs = [
        ['q' => 'Do I need my own computer to join?', 'a' => 'No. Edutrack has a fully equipped computer lab with modern machines and fast internet. You just need to show up ready to learn.'],
        ['q' => 'Can I pay in instalments?', 'a' => 'Yes. We offer flexible payment plans. You pay a registration fee to secure your spot, then clear the balance in agreed instalments. We accept MTN Mobile Money, Airtel Money, Zamtel Kwacha, and bank transfer.'],
        ['q' => 'Will I get a certificate after finishing?', 'a' => 'Absolutely. Every graduate receives a certificate recognized by TEVETA. You also get a digital portfolio of projects to show potential employers.'],
      ];
      @endphp
      @foreach($faqs as $i => $faq)
      <div class="od-card" style="padding: 0; overflow: hidden;">
        <button @click="openFaq === {{ $i }} ? openFaq = null : openFaq = {{ $i }}"
          class="w-full flex items-center justify-between px-6 py-4 text-left transition"
          style="background: transparent;"
          onmouseover="this.style.background='var(--od-fg-soft)'"
          onmouseout="this.style.background='transparent'">
          <span class="font-semibold" style="color: var(--od-fg);">{{ $faq['q'] }}</span>
          <i class="fas fa-chevron-down transition-transform" style="color: var(--od-muted);"
            :class="openFaq === {{ $i }} ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="openFaq === {{ $i }}" x-collapse class="px-6 py-4" style="border-top: 1px solid var(--od-border);">
          <p class="od-meta leading-relaxed">{{ $faq['a'] }}</p>
        </div>
      </div>
      @endforeach
    </div>

    <div class="text-center mt-10">
      <a href="{{ route('faq') }}" class="od-btn od-btn-secondary od-btn-lg">
        View All FAQs <i class="fas fa-arrow-right ml-2"></i>
      </a>
    </div>
  </div>
</section>

<!-- Final CTA -->
<section class="py-20" style="background: var(--od-navy);">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <h2 class="od-h2 mb-4" style="color: var(--od-surface);">Your Future Won't Build Itself</h2>
    <p class="text-lg mb-8 max-w-2xl mx-auto" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
      The best time to learn a valuable skill was yesterday. The second-best time is right now. Join Edutrack and start building something real.
    </p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      @auth
      <a href="{{ route('dashboard') }}" class="od-btn od-btn-lg" style="background: var(--od-accent); color: var(--od-fg);">
        <i class="fas fa-tachometer-alt mr-2"></i> My Dashboard
      </a>
      @else
      <a href="{{ route('register') }}" class="od-btn od-btn-lg" style="background: var(--od-accent); color: var(--od-fg);">
        <i class="fas fa-user-plus mr-2"></i> Create Free Account
      </a>
      @endauth
      <a href="{{ route('contact') }}" class="od-btn od-btn-lg od-btn-secondary" style="color: var(--od-surface); border-color: color-mix(in oklch, var(--od-surface), transparent 50%);">
        <i class="fas fa-phone mr-2"></i> Speak to Admissions
      </a>
    </div>
    <p class="text-sm mt-6" style="color: color-mix(in oklch, var(--od-surface), transparent 40%);">
      <i class="fas fa-map-marker-alt mr-1"></i> Kalomo, Zambia &nbsp;|&nbsp;
      <i class="fas fa-phone mr-1"></i> {{ \App\Models\SystemSetting::get('site_phone', '+260 770 666 937') }}
    </p>
  </div>
</section>

@push('scripts')
<script>
// Countdown timer
@php
$countdownDate = \App\Models\SystemSetting::get('next_intake_date');
@endphp
@if($countdownDate)
const intakeDate = new Date('{{ $countdownDate }}');
function updateCountdown() {
  const now = new Date();
  const diff = intakeDate - now;
  const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
  const el = document.getElementById('countdown-days');
  if (el) el.textContent = days > 0 ? days : 0;
}
updateCountdown();
setInterval(updateCountdown, 60000);
@endif
</script>
@endpush

@endsection
