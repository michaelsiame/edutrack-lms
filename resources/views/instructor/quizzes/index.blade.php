@extends('layouts.dashboard')

@section('title','Quizzes - Instructor')
@section('page_title','Quizzes')

@section('content')
<div class="max-w-6xl mx-auto">
 <div class="flex items-center justify-between mb-6">
 <h2 class="text-xl font-bold text-gray-900 dark:text-white">Quizzes</h2>
 <a href="{{ route('instructor.quizzes.create') }}" class="od-btn od-btn-primary od-btn-sm font-medium text-sm">
 <i class="fas fa-plus mr-1"></i>New Quiz
 </a>
 </div>

 @if(session('success'))
 <div class="mb-4 p-4 od-toast-success">{{ session('success') }}</div>
 @endif

 <div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="overflow-x-auto">
 <table class="od-table min-w-[640px]">
 <thead >
 <tr>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Title</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Course</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Questions</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Passing Score</th>
 <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300" scope="col">Actions</th>
 </tr>
 </thead>
 <tbody >
 @forelse($quizzes as $quiz)
 <tr >
 <td class="px-4 py-3">
 <div class="font-medium" style="color: var(--od-fg);">{{ $quiz->title }}</div>
 </td>
 <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $quiz->course->title ?? 'N/A' }}</td>
 <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $quiz->questions_count ?? 0 }}</td>
 <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $quiz->passing_score ?? 60 }}%</td>
 <td class="px-4 py-3 text-right">
 <a href="{{ route('instructor.quizzes.show', $quiz) }}" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg mr-1" aria-label="View quiz">
 <i class="fas fa-eye" aria-hidden="true"></i>
 </a>
 <a href="{{ route('instructor.quizzes.edit', $quiz) }}" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-secondary-600 hover:text-secondary-700 hover:bg-secondary-50 dark:hover:bg-secondary-900/20 rounded-lg" aria-label="Edit quiz">
 <i class="fas fa-edit" aria-hidden="true"></i>
 </a>
 <a href="{{ route('instructor.quizzes.attempts', $quiz) }}" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-gray-600 hover:text-gray-800 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-lg" title="Attempts / record offline score" aria-label="Attempts and record offline score">
 	<i class="fas fa-clipboard-check" aria-hidden="true"></i>
 </a>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No quizzes found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 @if($quizzes->hasPages())
 <div class="mt-4">
 {{ $quizzes->links() }}
 </div>
 @endif
</div>
@endsection
