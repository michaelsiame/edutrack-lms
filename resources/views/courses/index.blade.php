@extends('layouts.app')

@section('title','Courses - Edutrack LMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="py-12" style="background: var(--od-bg); min-height: 100vh;">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <p class="od-eyebrow" style="margin-bottom: 8px;">COURSE CATALOG</p>
            <h1 class="od-h1">All Courses</h1>
            <p class="od-lead mt-2" style="margin: 8px auto 0;">Expand your skills with our professional courses</p>
        </div>

        <!-- Filters -->
        <div class="od-card mb-6">
            <form action="{{ route('courses.index') }}" method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" placeholder="Search courses..." value="{{ request('search') }}"
                        class="w-full px-4 py-2 border rounded-md text-sm"
                        style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);">
                </div>
                <div>
                    <select name="level" class="px-4 py-2 border rounded-md text-sm"
                        style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);">
                        <option value="">All Levels</option>
                        <option value="Beginner" {{ request('level') ==='Beginner' ?'selected' :'' }}>Beginner</option>
                        <option value="Intermediate" {{ request('level') ==='Intermediate' ?'selected' :'' }}>Intermediate</option>
                        <option value="Advanced" {{ request('level') ==='Advanced' ?'selected' :'' }}>Advanced</option>
                    </select>
                </div>
                <button type="submit" class="od-btn od-btn-primary">Filter</button>
            </form>
        </div>

        <!-- Course Grid -->
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($courses as $course)
                <div class="od-card overflow-hidden flex flex-col group" style="padding: 0; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                    <div class="h-48 overflow-hidden relative" style="background: var(--od-fg-soft);">
                        @if($course->thumbnail_image_url)
                            <img src="{{ $course->thumbnail_image_url }}" alt="{{ $course->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center" style="background: var(--od-navy-soft);">
                                <i class="fas fa-laptop-code text-5xl" style="color: var(--od-navy); opacity: 0.3;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium px-2 py-1 rounded" style="background: var(--od-navy-soft); color: var(--od-navy);">{{ $course->category?->name ??'General' }}</span>
                            <span class="od-meta">{{ $course->level }}</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-2" style="color: var(--od-fg);">
                            <a href="{{ route('courses.show', $course) }}" class="hover:opacity-70 transition-opacity">{{ $course->title }}</a>
                        </h3>
                        <p class="text-sm mb-4 flex-1" style="color: var(--od-muted);">{{ Str::limit($course->short_description, 100) }}</p>
                        <div class="flex items-center justify-between pt-4" style="border-top: 1px solid var(--od-border);">
                            <span class="text-lg font-bold od-num" style="color: var(--od-navy);">{{ $course->formatted_price }}</span>
                            <a href="{{ route('courses.show', $course) }}" class="od-btn od-btn-ghost od-btn-sm">
                                View Details <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full od-card text-center py-12">
                    <p style="color: var(--od-muted);">No courses found.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $courses->links() }}
        </div>
    </div>
</div>
@endsection
