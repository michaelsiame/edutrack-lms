@extends('layouts.dashboard')

@section('title', 'Submissions - Edutrack LMS')
@section('page_title', 'Student Submissions')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 text-center">
    <div class="w-20 h-20 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-clipboard-check text-3xl text-amber-600 dark:text-amber-400"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Student Submissions</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
        Review and grade student assignment submissions and quiz attempts.
    </p>
</div>
@endsection
