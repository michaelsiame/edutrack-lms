@extends('layouts.app')

@section('title', $course->title .' - Edutrack LMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div style="background: var(--od-bg); min-height: 100vh;">
    <!-- Hero -->
    <div class="relative h-64 md:h-96">
        <img src="{{ $course->thumbnail_image_url ?? asset('assets/images/course-placeholder.jpg') }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
        <div class="absolute inset-0" style="background: color-mix(in oklch, var(--od-fg) 60%, transparent);"></div>
        <div class="absolute bottom-0 left-0 right-0 p-6">
            <div class="max-w-7xl mx-auto">
                <span class="inline-block px-3 py-1 text-xs font-semibold text-white rounded-full mb-2" style="background: var(--od-accent);">{{ $course->level }}</span>
                <h1 class="od-h1 text-white">{{ $course->title }}</h1>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <!-- About -->
                <div class="od-card">
                    <h2 class="od-h3 mb-4">About This Course</h2>
                    <p class="leading-relaxed" style="color: var(--od-muted);">{{ $course->description }}</p>

                    @if($course->learning_outcomes)
                        <h3 class="text-lg font-semibold mt-6 mb-3" style="color: var(--od-fg);">What You'll Learn</h3>
                        <ul class="list-disc list-inside space-y-1" style="color: var(--od-muted);">
                            @foreach(explode("\n", $course->learning_outcomes) as $outcome)
                                @if(trim($outcome))
                                    <li>{{ trim($outcome) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Course Content -->
                @if($course->modules->count() > 0)
                    <div class="od-card">
                        <h2 class="od-h3 mb-4">Course Content</h2>
                        <div class="space-y-3">
                            @foreach($course->modules as $module)
                                <div class="rounded-xl overflow-hidden" style="border: 1px solid var(--od-border);">
                                    <div class="px-4 py-3 font-medium" style="background: var(--od-fg-soft); color: var(--od-fg);">
                                        {{ $module->title }}
                                    </div>
                                    <div style="border-top: 1px solid var(--od-border);">
                                        @foreach($module->lessons as $lesson)
                                            <div class="flex items-center px-4 py-3 text-sm" style="color: var(--od-muted);">
                                                <span class="w-2 h-2 rounded-full mr-2" style="background: var(--od-navy);"></span>
                                                {{ $lesson->title }}
                                                @if($lesson->is_preview)
                                                    <span class="ml-2 text-xs" style="color: var(--od-green);">(Preview)</span>
                                                @endif
                                            </div>
                                            @if(!$loop->last)
                                                <div style="border-top: 1px solid var(--od-border);"></div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Reviews -->
                <div class="od-card">
                    <h2 class="od-h3 mb-4">Student Reviews</h2>

                    @if($course->reviews->count() > 0)
                        <div class="space-y-4">
                            @foreach($course->reviews->take(5) as $review)
                                <div class="p-4 rounded-xl" style="background: var(--od-fg-soft);">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold" style="background: var(--od-navy-soft); color: var(--od-navy);">
                                                {{ strtoupper(substr($review->user->first_name, 0, 1)) }}
                                            </div>
                                            <span class="font-medium text-sm" style="color: var(--od-fg);">{{ $review->user->full_name }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star text-xs {{ $i <= $review->rating ? '' : '' }}" style="color: {{ $i <= $review->rating ? 'var(--od-accent)' : 'var(--od-border)' }};"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="text-sm" style="color: var(--od-muted);">{{ $review->review }}</p>
                                    <p class="od-meta mt-1">{{ $review->created_at->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm" style="color: var(--od-muted);">No reviews yet. Be the first to review!</p>
                    @endif

                    @auth
                        @php
                            $canReview = \App\Models\Enrollment::where('user_id', auth()->id())
                                ->where('course_id', $course->id)
                                ->whereIn('enrollment_status', ['In Progress','Completed'])
                                ->exists();
                        @endphp
                        @if($canReview)
                            <form action="{{ route('student.reviews.store', $course) }}" method="POST" class="mt-4 p-4 rounded-xl" style="background: var(--od-fg-soft);">
                                @csrf
                                <h4 class="font-medium mb-3" style="color: var(--od-fg);">Write a Review</h4>
                                <div class="mb-3">
                                    <label class="block text-sm font-medium mb-1" style="color: var(--od-fg);">Rating</label>
                                    <div class="flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="rating" value="{{ $i }}" class="sr-only peer" required>
                                                <i class="fas fa-star text-xl od-peer-accent transition-colors" style="color: var(--od-border);" onmouseover="this.style.color='var(--od-accent)'" onmouseout="if(!this.previousElementSibling.checked) this.style.color='var(--od-border)'"></i>
                                            </label>
                                        @endfor
                                    </div>
                                    @error('rating')
                                        <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="review" class="block text-sm font-medium mb-1" style="color: var(--od-fg);">Your Review</label>
                                    <textarea name="review" id="review" rows="3" required minlength="10"
                                        class="w-full px-3 py-2 border rounded-lg text-sm resize-y"
                                        style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);"
                                        placeholder="Share your experience with this course..."></textarea>
                                    @error('review')
                                        <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="od-btn od-btn-primary od-btn-sm">Submit Review</button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="od-card sticky top-6">
                    <div class="text-center mb-6">
                        <p class="text-3xl font-bold od-num" style="color: var(--od-fg);">{{ $course->formatted_price }}</p>
                        @if($course->discount_price)
                            <p class="text-sm od-meta line-through">ZMW {{ number_format($course->price, 2) }}</p>
                        @endif
                    </div>

                    @auth
                        @php
                            $enrollment = \App\Models\Enrollment::where('user_id', auth()->id())->where('course_id', $course->id)->first();
                            $hasPaidRegFee = \App\Models\RegistrationFee::where('user_id', auth()->id())->where('payment_status','completed')->exists();
                        @endphp

                        @if($enrollment)
                            @if($enrollment->isFullyPaid())
                                <a href="{{ route('enrollments.show', $course) }}" class="od-btn od-btn-success w-full justify-center">
                                    <i class="fas fa-play mr-2"></i>Continue Learning
                                </a>
                            @else
                                <a href="{{ route('checkout.show', $course) }}" class="od-btn od-btn-primary w-full justify-center">
                                    <i class="fas fa-credit-card mr-2"></i>Complete Payment
                                </a>
                                <p class="mt-2 text-xs text-center od-meta">
                                    Paid: K{{ number_format($enrollment->amount_paid, 2) }} / K{{ number_format($course->discount_price ?? $course->price, 2) }}
                                </p>
                            @endif
                        @else
                            @if(!$hasPaidRegFee)
                                <a href="{{ route('registration-fee.show') }}" class="od-btn od-btn-secondary w-full justify-center">
                                    <i class="fas fa-lock mr-2"></i>Pay Registration Fee First
                                </a>
                                <p class="mt-2 text-xs text-center od-meta">K150 one-time registration fee required</p>
                            @else
                                <form action="{{ route('enrollments.store', $course) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="od-btn od-btn-primary w-full justify-center">
                                        <i class="fas fa-user-plus mr-2"></i>Enroll Now
                                    </button>
                                </form>
                            @endif
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="od-btn od-btn-primary w-full justify-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login to Enroll
                        </a>
                    @endauth

                    <div class="mt-6 space-y-3 text-sm" style="color: var(--od-muted);">
                        <div class="flex justify-between">
                            <span>Instructor</span>
                            <span class="font-medium" style="color: var(--od-fg);">{{ $course->instructor?->user?->full_name ??'TBA' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Duration</span>
                            <span class="font-medium" style="color: var(--od-fg);">{{ $course->duration_weeks ??'N/A' }} weeks</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Students</span>
                            <span class="font-medium" style="color: var(--od-fg);">{{ $course->enrollment_count }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Language</span>
                            <span class="font-medium" style="color: var(--od-fg);">{{ $course->language }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
