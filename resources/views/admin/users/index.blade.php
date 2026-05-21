@extends('layouts.dashboard')

@section('title','Users - Edutrack LMS')
@section('page_title','User Management')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
 <h3 class="text-base font-semibold text-gray-800 dark:text-white">All Users</h3>
 <span class="text-sm text-gray-500 dark:text-gray-400">{{ $users->total() }} total</span>
 </div>
 <div class="overflow-x-auto">
 <table class="dashboard-table">
 <thead>
 <tr>
 <th>Name</th>
 <th>Email</th>
 <th>Role</th>
 <th>Status</th>
 <th>Joined</th>
 </tr>
 </thead>
 <tbody>
 @forelse($users as $user)
 <tr>
 <td>
 <div class="flex items-center gap-3">
 <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-xs">
 {{ strtoupper(substr($user->first_name ?? $user->username, 0, 1)) }}
 </div>
 <span class="font-medium text-gray-900 dark:text-white">{{ $user->full_name ?? $user->username }}</span>
 </div>
 </td>
 <td class="text-gray-600 dark:text-gray-400">{{ $user->email }}</td>
 <td>
 @foreach($user->roles as $role)
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 mr-1">
 {{ $role->name }}
 </span>
 @endforeach
 </td>
 <td>
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $user->status ==='active' ?'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
 {{ ucfirst($user->status) }}
 </span>
 </td>
 <td class="text-gray-500 dark:text-gray-400 text-sm">{{ $user->created_at?->format('M d, Y') }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="text-center py-10 text-gray-500 dark:text-gray-400">No users found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
 {{ $users->links() }}
 </div>
</div>
@endsection
