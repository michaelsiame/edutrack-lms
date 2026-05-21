@extends('layouts.dashboard')

@section('title', ($user->full_name ?? $user->username) .' - Edutrack LMS')
@section('page_title', $user->full_name ?? $user->username)

@section('content')
<div class="max-w-4xl mx-auto">
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
 <div class="flex items-center justify-between mb-4">
 <h3 class="text-lg font-semibold text-gray-900 dark:text-white">User Details</h3>
 <a href="{{ route('admin.users.edit', $user) }}" class="text-primary-600 hover:underline">Edit</a>
 </div>
 <div class="space-y-3 text-sm">
 <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
 <span class="text-gray-500 dark:text-gray-400">Name</span>
 <span class="font-medium text-gray-900 dark:text-white">{{ $user->full_name ?? $user->username }}</span>
 </div>
 <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
 <span class="text-gray-500 dark:text-gray-400">Email</span>
 <span class="font-medium text-gray-900 dark:text-white">{{ $user->email }}</span>
 </div>
 <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
 <span class="text-gray-500 dark:text-gray-400">Status</span>
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $user->status ==='active' ?'bg-success-100 text-success-800' :'bg-gray-100 text-gray-800' }}">{{ ucfirst($user->status) }}</span>
 </div>
 <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
 <span class="text-gray-500 dark:text-gray-400">Enrollments</span>
 <span class="font-medium text-gray-900 dark:text-white">{{ $user->enrollments?->count() ?? 0 }}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-gray-500 dark:text-gray-400">Certificates</span>
 <span class="font-medium text-gray-900 dark:text-white">{{ $user->certificates?->count() ?? 0 }}</span>
 </div>
 </div>
 </div>
</div>
@endsection
