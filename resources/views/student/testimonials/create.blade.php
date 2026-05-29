@extends('layouts.dashboard')

@section('title', 'Write a Review - ' . $course->title)
@section('page_title', 'Write a Review')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
<style>
.od-star-rating {
  display: inline-flex;
  gap: 8px;
  direction: rtl;
}
.od-star-rating input {
  display: none;
}
.od-star-rating label {
  font-size: 32px;
  color: var(--od-border);
  cursor: pointer;
  transition: color 0.15s;
  line-height: 1;
}
.od-star-rating label:hover,
.od-star-rating label:hover ~ label,
.od-star-rating input:checked ~ label {
  color: var(--od-accent);
}
.od-star-rating label:hover {
  transform: scale(1.1);
}
</style>
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
  <div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <a href="{{ route('student.dashboard') }}" class="od-btn od-btn-ghost od-btn-sm mb-4">
        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
      </a>
      <p class="od-eyebrow">SHARE YOUR EXPERIENCE</p>
      <h1 class="od-h1 mt-2">How was {{ $course->title }}?</h1>
      <p class="od-lead mt-3">
        Your review helps future students decide if this course is right for them. Be honest and specific.
      </p>
    </div>

    <!-- Form -->
    <div class="od-card">
      <form action="{{ route('student.testimonials.store', $enrollment) }}" method="POST">
        @csrf

        <!-- Star Rating -->
        <div class="mb-6">
          <label class="od-form-label mb-3">How would you rate this course?</label>
          <div class="od-star-rating">
            @for($i = 5; $i >= 1; $i--)
            <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
            <label for="star{{ $i }}"><i class="fas fa-star"></i></label>
            @endfor
          </div>
          @error('rating')
          <p class="text-sm mt-2" style="color: var(--od-danger);">{{ $message }}</p>
          @enderror
        </div>

        <!-- Testimonial Text -->
        <div class="mb-6">
          <label for="testimonial_text" class="od-form-label">Tell us about your experience</label>
          <textarea
            name="testimonial_text"
            id="testimonial_text"
            rows="6"
            class="od-input"
            placeholder="What did you learn? How has it helped your career? What would you tell someone considering this course?"
            required
            minlength="20"
            maxlength="2000"
          >{{ old('testimonial_text') }}</textarea>
          @error('testimonial_text')
          <p class="text-sm mt-2" style="color: var(--od-danger);">{{ $message }}</p>
          @enderror
          <p class="text-xs mt-2 od-meta">Minimum 20 characters</p>
        </div>

        <!-- Optional Job Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <label for="job_title" class="od-form-label">Your Job Title <span class="od-meta">(optional)</span></label>
            <input
              type="text"
              name="job_title"
              id="job_title"
              class="od-input"
              placeholder="e.g. Web Designer"
              value="{{ old('job_title') }}"
              maxlength="100"
            >
            @error('job_title')
            <p class="text-sm mt-2" style="color: var(--od-danger);">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label for="company" class="od-form-label">Company <span class="od-meta">(optional)</span></label>
            <input
              type="text"
              name="company"
              id="company"
              class="od-input"
              placeholder="e.g. Self-employed"
              value="{{ old('company') }}"
              maxlength="100"
            >
            @error('company')
            <p class="text-sm mt-2" style="color: var(--od-danger);">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <!-- Info Box -->
        <div class="mb-6 p-4 rounded-xl" style="background: var(--od-navy-soft); border: 1px solid color-mix(in oklch, var(--od-navy) 20%, transparent);">
          <div class="flex items-start gap-3">
            <i class="fas fa-info-circle mt-0.5" style="color: var(--od-navy);"></i>
            <div class="text-sm" style="color: var(--od-navy);">
              <p class="font-medium mb-1">What happens next?</p>
              <p>Your review will be reviewed by our team before appearing publicly. This usually takes 1-2 business days.</p>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex flex-col sm:flex-row gap-3">
          <button type="submit" class="od-btn od-btn-primary od-btn-lg">
            <i class="fas fa-paper-plane mr-2"></i> Submit Review
          </button>
          <a href="{{ route('student.dashboard') }}" class="od-btn od-btn-secondary od-btn-lg">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
