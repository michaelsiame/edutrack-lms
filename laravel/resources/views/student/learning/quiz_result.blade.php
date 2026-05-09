@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <div class="bg-white rounded-lg shadow p-8 text-center">
        @if($attempt->passed)
            <div class="mb-4">
                <i class="fas fa-trophy text-green-500 text-5xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-green-700 mb-2">Congratulations!</h1>
            <p class="text-gray-600 mb-6">You passed the quiz.</p>
        @else
            <div class="mb-4">
                <i class="fas fa-times-circle text-red-500 text-5xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-red-700 mb-2">Quiz Not Passed</h1>
            <p class="text-gray-600 mb-6">You didn't meet the passing score. You can retake the quiz.</p>
        @endif

        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600">Your Score</p>
                <p class="text-2xl font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                    {{ $attempt->score }}%
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600">Passing Score</p>
                <p class="text-2xl font-bold text-gray-900">{{ $quiz->passing_score ?? 60 }}%</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600">Correct Answers</p>
                <p class="text-2xl font-bold text-gray-900">{{ $correctCount ?? 0 }}/{{ $totalQuestions ?? 0 }}</p>
            </div>
        </div>

        <div class="space-x-4">
            <a href="{{ route('student.courses.show', $quiz->course) }}" class="inline-block bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 font-medium">
                Back to Course
            </a>
            @if(!$attempt->passed)
                <a href="{{ route('student.quizzes.take', $quiz) }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
                    <i class="fas fa-redo mr-2"></i>Retake Quiz
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
