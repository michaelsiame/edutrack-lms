@extends('layouts.app')

@section('title', $query ?'Search:' . $query .' - Edutrack LMS' :'Search Courses - Edutrack LMS')

@push('styles')
<style>
.od-public-header { background: var(--od-navy); color: var(--od-surface); }
</style>
@endpush

@section('content')
<div class="min-h-screen py-12" style="background: var(--od-bg);">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <!-- Search Header -->
 <div class="mb-8">
 <p class="od-eyebrow">FIND YOUR COURSE</p>
 <h1 class="od-h2 mt-1">Search Courses</h1>
 <p class="od-meta mt-1">Find the perfect course for your career goals</p>
 </div>

 <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
 <!-- Filters -->
 <div class="lg:col-span-1">
 <div class="od-card sticky top-6">
 <h3 class="od-h3 mb-4">Filters</h3>
 <form action="{{ route('search') }}" method="GET">
 @if($query)
 <input type="hidden" name="q" value="{{ $query }}">
 @endif

 <div class="mb-4">
 <label class="od-form-label">Category</label>
 <select name="category" class="od-input">
 <option value="">All Categories</option>
 @foreach($categories as $cat)
 <option value="{{ $cat->id }}" {{ request('category') == $cat->id ?'selected' :'' }}>{{ $cat->name }}</option>
 @endforeach
 </select>
 </div>

 <div class="mb-4">
 <label class="od-form-label">Level</label>
 <select name="level" class="od-input">
 <option value="">All Levels</option>
 @foreach($levels as $lvl)
 <option value="{{ $lvl }}" {{ request('level') == $lvl ?'selected' :'' }}>{{ $lvl }}</option>
 @endforeach
 </select>
 </div>

 <div class="mb-4">
 <label class="od-form-label">Max Price (ZMW)</label>
 <input type="number" name="max_price" value="{{ request('max_price') }}" min="0"
 class="od-input" placeholder="e.g. 5000">
 </div>

 <div class="mb-4">
 <label class="od-form-label">Sort By</label>
 <select name="sort" class="od-input">
 <option value="relevance" {{ request('sort') =='relevance' ?'selected' :'' }}>Relevance</option>
 <option value="newest" {{ request('sort') =='newest' ?'selected' :'' }}>Newest</option>
 <option value="price_low" {{ request('sort') =='price_low' ?'selected' :'' }}>Price: Low to High</option>
 <option value="price_high" {{ request('sort') =='price_high' ?'selected' :'' }}>Price: High to Low</option>
 <option value="rating" {{ request('sort') =='rating' ?'selected' :'' }}>Highest Rated</option>
 </select>
 </div>

 <button type="submit" class="od-btn od-btn-primary w-full">
 Apply Filters
 </button>
 </form>
 </div>
 </div>

 <!-- Results -->
 <div class="lg:col-span-3">
 <!-- Search Bar -->
 <form action="{{ route('search') }}" method="GET" class="mb-6">
 <div class="flex gap-2">
 <input type="text" name="q" value="{{ $query }}"
 class="od-input flex-1" placeholder="Search courses...">
 <button type="submit" class="od-btn od-btn-primary">
 <i class="fas fa-search"></i>
 </button>
 </div>
 </form>

 @if($query)
 <p class="text-sm od-meta mb-4">Found {{ $courses->total() }} result{{ $courses->total() !== 1 ?'s' :'' }} for "{{ $query }}"</p>
 @endif

 @if($courses->isEmpty())
 <div class="od-card p-12 text-center">
 <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: var(--od-fg-soft);">
 <i class="fas fa-search text-2xl" style="color: var(--od-muted);"></i>
 </div>
 <h3 class="od-h3 mb-2">No Courses Found</h3>
 <p class="od-meta">Try adjusting your search or filters.</p>
 </div>
 @else
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
 @foreach($courses as $course)
 <div class="od-card hover:shadow-md transition-shadow" style="padding: 0; overflow: hidden;">
 <a href="{{ route('courses.show', $course) }}">
 <img src="{{ $course->thumbnail_image_url ?? asset('assets/images/course-placeholder.jpg') }}" alt="{{ $course->title }}"
 class="w-full h-40 object-cover">
 </a>
 <div class="p-4">
 <div class="flex items-center gap-2 mb-2">
 <span class="od-badge od-badge-info">{{ $course->level }}</span>
 @if($course->category)
 <span class="od-meta">{{ $course->category->name }}</span>
 @endif
 </div>
 <a href="{{ route('courses.show', $course) }}" class="block">
 <h3 class="font-semibold hover:opacity-70 transition-opacity line-clamp-2" style="color: var(--od-fg);">{{ $course->title }}</h3>
 </a>
 <p class="od-meta mt-1 line-clamp-2">{{ Str::limit($course->short_description ?? $course->description, 80) }}</p>
 <div class="flex items-center justify-between mt-3">
 <div class="flex items-center gap-1">
 @if($course->rating > 0)
 <i class="fas fa-star text-xs" style="color: var(--od-accent);"></i>
 <span class="text-sm font-medium">{{ number_format($course->rating, 1) }}</span>
 <span class="text-xs od-meta">({{ $course->total_reviews }})</span>
 @endif
 </div>
 <span class="font-bold" style="color: var(--od-navy);">{{ $course->formatted_price }}</span>
 </div>
 </div>
 </div>
 @endforeach
 </div>

 <div class="mt-6">
 {{ $courses->links() }}
 </div>
 @endif
 </div>
 </div>
 </div>
</div>
@endsection
