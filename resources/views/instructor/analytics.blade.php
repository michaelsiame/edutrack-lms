@extends('layouts.dashboard')

@section('title', 'Analytics - Edutrack LMS')
@section('page_title', 'Course Analytics')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 text-center">
    <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-chart-line text-3xl text-emerald-600 dark:text-emerald-400"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Course Analytics</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
        Track student engagement, completion rates, and course performance metrics.
    </p>
</div>
@endsection
