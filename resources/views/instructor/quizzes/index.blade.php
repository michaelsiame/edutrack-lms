@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
 <div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold">Quizzes</h1>
 <a href="{{ route('instructor.quizzes.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">
 <i class="fas fa-plus mr-2"></i>New Quiz
 </a>
 </div>

 @if(session('success'))
 <div class="bg-success-100 border border-success-400 text-success-700 px-4 py-3 rounded mb-4">
 {{ session('success') }}
 </div>
 @endif

 <div class="bg-white rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-gray-200">
 <thead class="bg-gray-50">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Questions</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Passing Score</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
 </tr>
 </thead>
 <tbody class="bg-white divide-y divide-gray-200">
 @forelse($quizzes as $quiz)
 <tr>
 <td class="px-6 py-4">
 <div class="text-sm font-medium text-gray-900">{{ $quiz->title }}</div>
 </td>
 <td class="px-6 py-4 text-sm text-gray-500">{{ $quiz->course->title ??'N/A' }}</td>
 <td class="px-6 py-4 text-sm text-gray-500">{{ $quiz->questions_count ?? 0 }}</td>
 <td class="px-6 py-4 text-sm text-gray-500">{{ $quiz->passing_score ?? 60 }}%</td>
 <td class="px-6 py-4 text-sm font-medium space-x-2">
 <a href="{{ route('instructor.quizzes.show', $quiz) }}" class="text-primary-600 hover:text-primary-900">View</a>
 <a href="{{ route('instructor.quizzes.edit', $quiz) }}" class="text-secondary-600 hover:text-secondary-900">Edit</a>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="px-6 py-8 text-center text-gray-500">No quizzes found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 @if($quizzes->hasPages())
 <div class="mt-4">
 {{ $quizzes->links() }}
 </div>
 @endif
</div>
@endsection
