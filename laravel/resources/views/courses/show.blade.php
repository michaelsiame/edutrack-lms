@extends('layouts.app')

@section('title', $course->title . ' - Edutrack LMS')

@section('content')
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="relative h-64 md:h-96">
                <img src="{{ $course->thumbnail_url ?? asset('assets/images/course-placeholder.jpg') }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <span class="inline-block px-3 py-1 text-xs font-semibold text-white bg-indigo-600 rounded-full mb-2">{{ $course->level }}</span>
                    <h1 class="text-3xl md:text-4xl font-bold text-white">{{ $course->title }}</h1>
                </div>
            </div>

            <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">About This Course</h2>
                        <p class="text-gray-600 mb-6">{{ $course->description }}</p>

                        @if($course->learning_outcomes)
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">What You'll Learn</h3>
                            <ul class="list-disc list-inside text-gray-600 mb-6 space-y-1">
                                @foreach(explode("\n", $course->learning_outcomes) as $outcome)
                                    @if(trim($outcome))
                                        <li>{{ trim($outcome) }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif

                        @if($course->modules->count() > 0)
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Course Content</h3>
                            <div class="border rounded-lg divide-y">
                                @foreach($course->modules as $module)
                                    <div class="p-4">
                                        <h4 class="font-medium text-gray-900">{{ $module->title }}</h4>
                                        @if($module->lessons->count() > 0)
                                            <ul class="mt-2 space-y-1">
                                                @foreach($module->lessons as $lesson)
                                                    <li class="text-sm text-gray-500 flex items-center">
                                                        <span class="w-2 h-2 bg-indigo-400 rounded-full mr-2"></span>
                                                        {{ $lesson->title }}
                                                        @if($lesson->is_preview)
                                                            <span class="ml-2 text-xs text-green-600">(Preview)</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Reviews -->
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Student Reviews</h3>

                            @if($course->reviews->count() > 0)
                                <div class="space-y-4">
                                    @foreach($course->reviews->take(5) as $review)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-sm">
                                                        {{ strtoupper(substr($review->user->first_name, 0, 1)) }}
                                                    </div>
                                                    <span class="font-medium text-gray-900">{{ $review->user->full_name }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star text-xs {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-300' }}"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-600">{{ $review->review }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ $review->created_at->diffForHumans() }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">No reviews yet. Be the first to review!</p>
                            @endif

                            @auth
                                @php
                                    $canReview = \App\Models\Enrollment::where('user_id', auth()->id())
                                        ->where('course_id', $course->id)
                                        ->whereIn('enrollment_status', ['In Progress', 'Completed'])
                                        ->exists();
                                @endphp
                                @if($canReview)
                                    <form action="{{ route('reviews.store', $course) }}" method="POST" class="mt-4 p-4 bg-gray-50 rounded-lg">
                                        @csrf
                                        <h4 class="font-medium text-gray-900 mb-3">Write a Review</h4>
                                        <div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                                            <div class="flex items-center gap-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <label class="cursor-pointer">
                                                        <input type="radio" name="rating" value="{{ $i }}" class="sr-only peer" required>
                                                        <i class="fas fa-star text-xl peer-checked:text-amber-400 text-gray-300 hover:text-amber-300 transition-colors"></i>
                                                    </label>
                                                @endfor
                                            </div>
                                            @error('rating')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="review" class="block text-sm font-medium text-gray-700 mb-1">Your Review</label>
                                            <textarea name="review" id="review" rows="3" required minlength="10"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                placeholder="Share your experience with this course..."></textarea>
                                            @error('review')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm">
                                            Submit Review
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-gray-50 rounded-lg p-6 sticky top-6">
                            <div class="text-center mb-6">
                                <p class="text-3xl font-bold text-gray-900">{{ $course->formatted_price }}</p>
                                @if($course->discount_price)
                                    <p class="text-sm text-gray-500 line-through">ZMW {{ number_format($course->price, 2) }}</p>
                                @endif
                            </div>

                            @auth
                                @php
                                    $enrollment = \App\Models\Enrollment::where('user_id', auth()->id())->where('course_id', $course->id)->first();
                                    $hasPaidRegFee = \App\Models\RegistrationFee::where('user_id', auth()->id())->where('payment_status', 'completed')->exists();
                                @endphp

                                @if($enrollment)
                                    @if($enrollment->isFullyPaid())
                                        <a href="{{ route('enrollments.show', $course) }}" class="block w-full text-center py-3 px-4 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium">
                                            <i class="fas fa-play mr-2"></i>Continue Learning
                                        </a>
                                    @else
                                        <a href="{{ route('checkout.show', $course) }}" class="block w-full text-center py-3 px-4 bg-amber-600 text-white rounded-md hover:bg-amber-700 font-medium">
                                            <i class="fas fa-credit-card mr-2"></i>Complete Payment
                                        </a>
                                        <p class="mt-2 text-xs text-center text-gray-500">
                                            Paid: K{{ number_format($enrollment->amount_paid, 2) }} / K{{ number_format($course->discount_price ?? $course->price, 2) }}
                                        </p>
                                    @endif
                                @else
                                    @if(!$hasPaidRegFee)
                                        <a href="{{ route('registration-fee.show') }}" class="block w-full text-center py-3 px-4 bg-gray-600 text-white rounded-md hover:bg-gray-700 font-medium">
                                            <i class="fas fa-lock mr-2"></i>Pay Registration Fee First
                                        </a>
                                        <p class="mt-2 text-xs text-center text-gray-500">K150 one-time registration fee required</p>
                                    @else
                                        <form action="{{ route('enrollments.store', $course) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="block w-full text-center py-3 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium">
                                                <i class="fas fa-user-plus mr-2"></i>Enroll Now
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="block w-full text-center py-3 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Login to Enroll
                                </a>
                            @endauth

                            <div class="mt-6 space-y-3 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>Instructor</span>
                                    <span class="font-medium">{{ $course->instructor?->user?->full_name ?? 'TBA' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Duration</span>
                                    <span class="font-medium">{{ $course->duration_weeks ?? 'N/A' }} weeks</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Students</span>
                                    <span class="font-medium">{{ $course->enrollment_count }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Language</span>
                                    <span class="font-medium">{{ $course->language }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
