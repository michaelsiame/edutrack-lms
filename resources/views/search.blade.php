@extends('layouts.app')

@section('title', $query ?'Search:' . $query .' - Edutrack LMS' :'Search Courses - Edutrack LMS')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <!-- Search Header -->
 <div class="mb-8">
 <h1 class="text-3xl font-bold text-gray-900">Search Courses</h1>
 <p class="text-gray-600 mt-1">Find the perfect course for your career goals</p>
 </div>

 <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
 <!-- Filters -->
 <div class="lg:col-span-1">
 <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
 <h3 class="font-semibold text-gray-900 mb-4">Filters</h3>
 <form action="{{ route('search') }}" method="GET">
 @if($query)
 <input type="hidden" name="q" value="{{ $query }}">
 @endif

 <div class="mb-4">
 <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
 <select name="category"
 class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
 <option value="">All Categories</option>
 @foreach($categories as $cat)
 <option value="{{ $cat->id }}" {{ request('category') == $cat->id ?'selected' :'' }}>{{ $cat->name }}</option>
 @endforeach
 </select>
 </div>

 <div class="mb-4">
 <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
 <select name="level"
 class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
 <option value="">All Levels</option>
 @foreach($levels as $lvl)
 <option value="{{ $lvl }}" {{ request('level') == $lvl ?'selected' :'' }}>{{ $lvl }}</option>
 @endforeach
 </select>
 </div>

 <div class="mb-4">
 <label class="block text-sm font-medium text-gray-700 mb-2">Max Price (ZMW)</label>
 <input type="number" name="max_price" value="{{ request('max_price') }}" min="0"
 class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
 placeholder="e.g. 5000">
 </div>

 <div class="mb-4">
 <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
 <select name="sort"
 class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
 <option value="relevance" {{ request('sort') =='relevance' ?'selected' :'' }}>Relevance</option>
 <option value="newest" {{ request('sort') =='newest' ?'selected' :'' }}>Newest</option>
 <option value="price_low" {{ request('sort') =='price_low' ?'selected' :'' }}>Price: Low to High</option>
 <option value="price_high" {{ request('sort') =='price_high' ?'selected' :'' }}>Price: High to Low</option>
 <option value="rating" {{ request('sort') =='rating' ?'selected' :'' }}>Highest Rated</option>
 </select>
 </div>

 <button type="submit" class="w-full py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">
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
 class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
 placeholder="Search courses...">
 <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">
 <i class="fas fa-search"></i>
 </button>
 </div>
 </form>

 @if($query)
 <p class="text-sm text-gray-600 mb-4">Found {{ $courses->total() }} result{{ $courses->total() !== 1 ?'s' :'' }} for"{{ $query }}"</p>
 @endif

 @if($courses->isEmpty())
 <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
 <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
 <i class="fas fa-search text-2xl text-gray-400"></i>
 </div>
 <h3 class="text-lg font-medium text-gray-900 mb-2">No Courses Found</h3>
 <p class="text-gray-500">Try adjusting your search or filters.</p>
 </div>
 @else
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
 @foreach($courses as $course)
 <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
 <a href="{{ route('courses.show', $course) }}">
 <img src="{{ $course->thumbnail_url ?? asset('assets/images/course-placeholder.jpg') }}" alt="{{ $course->title }}"
 class="w-full h-40 object-cover">
 </a>
 <div class="p-4">
 <div class="flex items-center gap-2 mb-2">
 <span class="text-xs font-medium text-primary-600 bg-primary-50 px-2 py-0.5 rounded">{{ $course->level }}</span>
 @if($course->category)
 <span class="text-xs text-gray-500">{{ $course->category->name }}</span>
 @endif
 </div>
 <a href="{{ route('courses.show', $course) }}" class="block">
 <h3 class="font-semibold text-gray-900 hover:text-primary-600 transition-colors line-clamp-2">{{ $course->title }}</h3>
 </a>
 <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ Str::limit($course->short_description ?? $course->description, 80) }}</p>
 <div class="flex items-center justify-between mt-3">
 <div class="flex items-center gap-1">
 @if($course->rating > 0)
 <i class="fas fa-star text-warning-400 text-xs"></i>
 <span class="text-sm font-medium">{{ number_format($course->rating, 1) }}</span>
 <span class="text-xs text-gray-400">({{ $course->total_reviews }})</span>
 @endif
 </div>
 <span class="font-bold text-primary-600">{{ $course->formatted_price }}</span>
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
