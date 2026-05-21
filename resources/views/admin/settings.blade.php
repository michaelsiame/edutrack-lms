@extends('layouts.dashboard')

@section('title','Settings - Edutrack LMS')
@section('page_title','System Settings')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 text-center">
 <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
 <i class="fas fa-cog text-3xl text-gray-600 dark:text-gray-400"></i>
 </div>
 <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">System Settings</h3>
 <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
 Configure application settings, payment gateways, email templates, and more.
 </p>
</div>
@endsection
