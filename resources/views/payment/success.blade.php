@extends('layouts.app')

@section('title','Payment Successful - Edutrack LMS')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
 <div class="max-w-md w-full text-center">
 <div class="mb-6">
 <div class="mx-auto w-20 h-20 bg-success-100 rounded-full flex items-center justify-center">
 <i class="fas fa-check text-4xl text-success-600"></i>
 </div>
 </div>

 <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
 <p class="text-gray-600 mb-8">Your payment has been received and is being processed. You will receive a confirmation email shortly.</p>

 @if($course)
 <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
 <h3 class="font-semibold text-gray-900 mb-2">{{ $course->title }}</h3>
 <p class="text-sm text-gray-500 mb-4">You can now access your course content.</p>
 <a href="{{ route('enrollments.show', $course) }}"
 class="inline-block w-full py-3 px-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition-colors">
 Start Learning
 </a>
 </div>
 @endif

 <div class="space-y-3">
 <a href="{{ route('enrollments.index') }}" class="block text-primary-600 hover:text-primary-700 font-medium">
 View My Courses
 </a>
 <a href="{{ route('courses.index') }}" class="block text-gray-500 hover:text-gray-700">
 Browse More Courses
 </a>
 </div>
 </div>
</div>
@endsection
