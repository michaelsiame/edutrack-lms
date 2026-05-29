@extends('layouts.app')

@section('title','Student Success Stories - Edutrack Testimonials')

@push('styles')
<style>
.od-public-header { background: var(--od-navy); color: var(--od-surface); }
.od-stats-bar { background: var(--od-accent); color: var(--od-fg); }
.od-testimonials-dark { background: var(--od-fg); color: var(--od-surface); }
.od-public-cta { background: var(--od-navy); color: var(--od-surface); }
</style>
@endpush

@section('content')

<!-- Page Header -->
<section class="od-public-header py-16">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center">
 <p class="od-eyebrow" style="color: var(--od-accent);">SUCCESS STORIES</p>
 <h1 class="od-h1 mt-2"><i class="fas fa-heart mr-3"></i>Student Success Stories</h1>
 <p class="od-lead mx-auto mt-4" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
 Real stories from real graduates who transformed their careers through Edutrack
 </p>
 </div>
 </div>
</section>

<!-- Stats Section -->
<section class="od-stats-bar py-12">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
 <div>
 <div class="text-4xl font-bold" style="color: var(--od-fg);">{{ number_format($stats['total_students'] ?? 0) }}+</div>
 <div class="text-sm mt-1" style="color: color-mix(in oklch, var(--od-fg), transparent 30%);">Success Stories</div>
 </div>
 <div>
 <div class="text-4xl font-bold" style="color: var(--od-fg);">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</div>
 <div class="text-sm mt-1" style="color: color-mix(in oklch, var(--od-fg), transparent 30%);">Average Rating</div>
 </div>
 <div>
 <div class="text-4xl font-bold" style="color: var(--od-fg);">{{ number_format($stats['total_enrollments'] ?? 0) }}</div>
 <div class="text-sm mt-1" style="color: color-mix(in oklch, var(--od-fg), transparent 30%);">Enrollments</div>
 </div>
 <div>
 <div class="text-4xl font-bold" style="color: var(--od-fg);">{{ number_format($stats['total_courses'] ?? 0) }}+</div>
 <div class="text-sm mt-1" style="color: color-mix(in oklch, var(--od-fg), transparent 30%);">Courses</div>
 </div>
 </div>
 </div>
</section>

<!-- Featured Testimonials -->
@if($featuredTestimonials->count() > 0)
<section class="od-testimonials-dark py-16">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-12">
 <p class="od-eyebrow" style="color: var(--od-accent);">FEATURED</p>
 <h2 class="od-h2 mt-2">Outstanding Graduates</h2>
 <p class="od-meta mt-2" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">Graduates who went above and beyond</p>
 </div>
 <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
 @foreach($featuredTestimonials as $t)
 <div class="od-card" style="background: color-mix(in oklch, var(--od-surface), transparent 92%); border-color: color-mix(in oklch, var(--od-surface), transparent 85%);">
 <div class="flex items-center mb-4">
 <i class="fas fa-quote-left text-2xl mr-3" style="color: var(--od-accent);"></i>
 <div class="flex" style="color: var(--od-accent);">
 @for($i = 0; $i < $t->rating; $i++)
 <i class="fas fa-star"></i>
 @endfor
 </div>
 </div>
 <p class="mb-6 leading-relaxed italic" style="color: color-mix(in oklch, var(--od-surface), transparent 15%);">"{{ $t->testimonial_text }}"</p>
 <div class="flex items-center">
 @if($t->avatar_url)
 <img src="{{ asset($t->avatar_url) }}" alt="{{ $t->name }}" class="w-12 h-12 rounded-full object-cover mr-4">
 @else
 <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg mr-4" style="background: var(--od-navy); color: var(--od-surface);">
 {{ strtoupper(substr($t->name, 0, 1)) }}
 </div>
 @endif
 <div>
 <div class="font-semibold" style="color: var(--od-surface);">{{ $t->name }}</div>
 <div class="text-sm" style="color: color-mix(in oklch, var(--od-surface), transparent 30%);">{{ $t->course_taken }} - Class of {{ $t->graduation_year }}</div>
 @if($t->job_title)
 <div class="text-xs" style="color: var(--od-accent);">{{ $t->job_title }}{{ $t->company ?' at' . $t->company :'' }}</div>
 @endif
 </div>
 </div>
 </div>
 @endforeach
 </div>
 </div>
</section>
@endif

<!-- All Testimonials Grid -->
<section class="py-20" style="background: var(--od-bg);">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-16">
 <p class="od-eyebrow">TESTIMONIALS</p>
 <h2 class="od-h2 mt-2">What Our Students Say</h2>
 <p class="od-lead mx-auto mt-3">
 Hear directly from the students whose lives have been changed by quality education
 </p>
 </div>

 <!-- Filters -->
 <div class="od-card mb-10">
 <form action="{{ route('testimonials') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
 <div class="flex-1">
 <label class="od-form-label">Search</label>
 <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, course, or keyword..." class="od-input">
 </div>
 @if($courses->count() > 0)
 <div class="md:w-48">
 <label class="od-form-label">Course</label>
 <select name="course" class="od-input">
 <option value="">All Courses</option>
 @foreach($courses as $c)
 <option value="{{ $c }}" {{ request('course') == $c ? 'selected' : '' }}>{{ $c }}</option>
 @endforeach
 </select>
 </div>
 @endif
 <div class="md:w-40">
 <label class="od-form-label">Rating</label>
 <select name="rating" class="od-input">
 <option value="">Any Rating</option>
 <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Stars</option>
 <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Stars</option>
 <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Stars</option>
 <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Stars</option>
 <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Star</option>
 </select>
 </div>
 <div class="md:w-40">
 <label class="od-form-label">Sort By</label>
 <select name="sort" class="od-input">
 <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest First</option>
 <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
 <option value="highest" {{ request('sort') == 'highest' ? 'selected' : '' }}>Highest Rated</option>
 <option value="lowest" {{ request('sort') == 'lowest' ? 'selected' : '' }}>Lowest Rated</option>
 </select>
 </div>
 <div class="flex gap-2">
 <button type="submit" class="od-btn od-btn-primary od-btn-sm">
 <i class="fas fa-filter mr-1"></i> Filter
 </button>
 <a href="{{ route('testimonials') }}" class="od-btn od-btn-secondary od-btn-sm">Clear</a>
 </div>
 </form>
 </div>

 @if($testimonials->count() > 0)
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
 @foreach($testimonials as $t)
 <div class="od-card hover:shadow-xl transition-all duration-300 animate-slide-up">
 <div class="flex items-center mb-4">
 <div class="flex" style="color: var(--od-accent);">
 @for($i = 0; $i < $t->rating; $i++)
 <i class="fas fa-star"></i>
 @endfor
 </div>
 </div>
 <p class="od-meta mb-6 leading-relaxed">"{{ $t->testimonial_text }}"</p>
 <div class="flex items-center pt-4" style="border-top: 1px solid var(--od-border);">
 @if($t->avatar_url)
 <img src="{{ asset($t->avatar_url) }}" alt="{{ $t->name }}" class="w-12 h-12 rounded-full object-cover mr-4">
 @else
 <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg mr-4" style="background: var(--od-navy-soft); color: var(--od-navy);">
 {{ strtoupper(substr($t->name, 0, 1)) }}
 </div>
 @endif
 <div>
 <div class="font-semibold" style="color: var(--od-fg);">{{ $t->name }}</div>
 <div class="text-sm od-meta">{{ $t->course_taken }} - Class of {{ $t->graduation_year }}</div>
 @if($t->job_title)
 <div class="text-xs font-medium" style="color: var(--od-navy);">{{ $t->job_title }}{{ $t->company ?' at' . $t->company :'' }}</div>
 @endif
 </div>
 </div>
 </div>
 @endforeach
 </div>

 <div class="mt-12">
 {{ $testimonials->links() }}
 </div>
 @else
 <div class="text-center py-16">
 <i class="fas fa-comment-dots text-5xl mb-4" style="color: var(--od-border);"></i>
 <h3 class="od-h3 mb-2">No testimonials yet</h3>
 <p class="od-meta">Be the first to share your success story after completing a course.</p>
 </div>
 @endif
 </div>
</section>

<!-- CTA -->
<section class="od-public-cta py-16">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
 <h2 class="od-h2 mb-4">Start Your Success Story Today</h2>
 <p class="od-lead mx-auto mb-8" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
 Join thousands of students who have transformed their careers through Edutrack's professional programs.
 </p>
 <a href="{{ route('courses.index') }}" class="od-btn od-btn-primary od-btn-lg">
 <i class="fas fa-rocket mr-2"></i> Explore Courses
 </a>
 </div>
</section>

@endsection
