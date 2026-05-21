@extends('layouts.dashboard')

@section('title', $quiz->title .' - Edutrack LMS')
@section('page_title', $quiz->title)

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
 <!-- Quiz Header -->
 <div class="bg-white rounded-lg shadow p-6 mb-6">
 <div class="flex justify-between items-start">
 <div>
 <h1 class="text-2xl font-bold text-gray-900">{{ $quiz->title }}</h1>
 <p class="text-gray-600 mt-1">{{ $quiz->course->title }}</p>
 </div>
 @if($quiz->time_limit)
 <div class="text-center bg-danger-50 px-4 py-2 rounded-lg">
 <div class="text-sm text-danger-600 font-medium">Time Remaining</div>
 <div id="timer" class="text-2xl font-bold text-danger-700" data-minutes="{{ $quiz->time_limit_minutes }}">
 {{ $quiz->time_limit_minutes }}:00
 </div>
 </div>
 @endif
 </div>

 @if($quiz->description)
 <div class="mt-4 p-4 bg-gray-50 rounded-lg">
 <p class="text-gray-700">{{ $quiz->description }}</p>
 </div>
 @endif

 <div class="mt-4 flex items-center space-x-6 text-sm text-gray-600">
 <span><i class="fas fa-question-circle mr-1"></i> {{ $questions->count() }} Questions</span>
 <span><i class="fas fa-percentage mr-1"></i> Passing: {{ $quiz->passing_score ?? 60 }}%</span>
 </div>
 </div>

 <!-- Quiz Form -->
 <form action="{{ route('student.quizzes.submit', $quiz) }}" method="POST" id="quiz-form" class="space-y-6">
 @csrf

 @foreach($questions as $index => $question)
 <div class="bg-white rounded-lg shadow p-6">
 <div class="flex items-start mb-4">
 <span class="flex-shrink-0 w-8 h-8 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center font-medium text-sm mr-3">
 {{ $index + 1 }}
 </span>
 <h3 class="text-lg font-medium text-gray-900">{{ $question->question_text }}</h3>
 </div>

 <div class="ml-11 space-y-3">
 @if($question->question_type ==='Multiple Choice')
 @foreach($question->options as $option)
 <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
 <input type="radio" name="answers[{{ $question->question_id }}]" value="{{ $option->id }}" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300">
 <span class="ml-3 text-gray-700">{{ $option->option_text }}</span>
 </label>
 @endforeach
 @elseif($question->question_type ==='True/False')
 @foreach($question->options as $option)
 <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
 <input type="radio" name="answers[{{ $question->question_id }}]" value="{{ $option->option_text }}" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300">
 <span class="ml-3 text-gray-700">{{ $option->option_text }}</span>
 </label>
 @endforeach
 @else
 <textarea name="answers[{{ $question->question_id }}]" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Enter your answer..."></textarea>
 @endif
 </div>
 </div>
 @endforeach

 <div class="flex justify-between items-center pt-4">
 <p class="text-sm text-gray-600">
 <i class="fas fa-info-circle mr-1"></i>
 Make sure to answer all questions before submitting.
 </p>
 <button type="submit" class="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 font-medium text-lg">
 <i class="fas fa-paper-plane mr-2"></i>Submit Quiz
 </button>
 </div>
 </form>
</div>

@if($quiz->time_limit)
@push('scripts')
<script>
 document.addEventListener('DOMContentLoaded', function() {
 const timerEl = document.getElementById('timer');
 const form = document.getElementById('quiz-form');
 let minutes = parseInt(timerEl.dataset.minutes);
 let seconds = 0;
 let totalSeconds = minutes * 60;

 function updateTimer() {
 if (totalSeconds <= 0) {
 form.submit();
 return;
 }

 totalSeconds--;
 const m = Math.floor(totalSeconds / 60);
 const s = totalSeconds % 60;
 timerEl.textContent = `${m}:${s.toString().padStart(2,'0')}`;

 if (totalSeconds < 60) {
 timerEl.classList.add('text-danger-800');
 }
 }

 setInterval(updateTimer, 1000);
 });
</script>
@endpush
@endif
@endsection
