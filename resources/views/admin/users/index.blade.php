@extends('layouts.dashboard')

@section('title','Users - Edutrack LMS')
@section('page_title','User Management')

@section('content')
<div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="od-card-header">
 <h3 class="od-h3">All Users</h3>
 <span class="od-meta">{{ $users->total() }} total</span>
 </div>
 <div class="overflow-x-auto">
 <table class="od-table min-w-[640px]">
 <thead>
 <tr>
 <th>Name</th>
 <th>Email</th>
 <th>Role</th>
 <th>Status</th>
 <th>Joined</th>
 <th class="text-right">Actions</th>
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
 <span class="font-medium" style="color: var(--od-fg);">{{ $user->full_name ?? $user->username }}</span>
 </div>
 </td>
 <td class="od-meta">{{ $user->email }}</td>
 <td>
 @foreach($user->roles as $role)
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 mr-1">
 {{ $role->role_name }}
 </span>
 @endforeach
 </td>
 <td>
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $user->status ==='active' ?'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
 {{ ucfirst($user->status) }}
 </span>
 </td>
 <td class="od-meta">{{ $user->created_at?->format('M d, Y') }}</td>
 <td class="text-right">
 <a href="{{ route('admin.users.show', $user) }}" class="od-btn od-btn-ghost od-btn-sm" aria-label="View user">
 <i class="fas fa-eye text-sm"></i>
 </a>
 <a href="{{ route('admin.users.edit', $user) }}" class="od-btn od-btn-ghost od-btn-sm" aria-label="Edit user">
 <i class="fas fa-edit text-sm"></i>
 </a>
 <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" data-confirm="Delete this user">
 @csrf
 @method('DELETE')
 <button type="submit" class="od-btn od-btn-ghost od-btn-sm text-danger-600 hover:text-danger-700" aria-label="Delete user">
 <i class="fas fa-trash text-sm"></i>
 </button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="od-empty-sm">No users found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="od-card-header" style="border-top: 1px solid var(--od-border); border-bottom: none;">
 {{ $users->links() }}
 </div>
</div>
@endsection
