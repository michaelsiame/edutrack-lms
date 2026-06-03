@extends('layouts.dashboard')

@section('title','Newsletter Subscribers')
@section('page_title','Newsletter Subscribers')

@section('content')
<div class="max-w-5xl mx-auto">
 <div class="flex items-center justify-between mb-6">
 <h2 class="text-xl font-bold text-gray-900 dark:text-white">Newsletter Subscribers</h2>
 <div class="text-sm text-gray-500">
 <span class="font-semibold text-success-600">{{ $subscribers->where('is_active', true)->count() }}</span> active /
 <span class="font-semibold">{{ $subscribers->count() }}</span> total
 </div>
 </div>

 @if(session('success'))
 <div class="mb-4 p-4 od-toast-success">{{ session('success') }}</div>
 @endif

 <div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="overflow-x-auto">
 <table class="w-full text-sm text-left">
 <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
 <tr>
 <th class="px-4 py-3 font-medium">Email</th>
 <th class="px-4 py-3 font-medium">Name</th>
 <th class="px-4 py-3 font-medium">Status</th>
 <th class="px-4 py-3 font-medium">Subscribed</th>
 <th class="px-4 py-3 font-medium">Actions</th>
 </tr>
 </thead>
 <tbody >
 @forelse($subscribers as $sub)
 <tr>
 <td class="px-4 py-3 font-mono text-xs">{{ $sub->email }}</td>
 <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $sub->name ?:'-' }}</td>
 <td class="px-4 py-3">
 @if($sub->is_active)
 <span class="text-xs bg-success-100 text-success-700 px-2 py-0.5 rounded-full">Active</span>
 @else
 <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Unsubscribed</span>
 @endif
 </td>
 <td class="px-4 py-3 text-gray-500">{{ $sub->subscribed_at ? $sub->subscribed_at->format('M d, Y') :'-' }}</td>
 <td class="px-4 py-3">
 <form action="{{ route('admin.newsletter.toggle', $sub) }}" method="POST" class="inline">
 @csrf
 <button type="submit" class="text-xs text-primary-600 hover:text-primary-700 mr-2">
 {{ $sub->is_active ?'Deactivate' :'Activate' }}
 </button>
 </form>
 <form action="{{ route('admin.newsletter.destroy', $sub) }}" method="POST" class="inline" data-confirm="Delete subscriber">
 @csrf @method('DELETE')
 <button type="submit" class="text-xs text-danger-600 hover:text-danger-700">Delete</button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="od-empty-sm">No subscribers yet.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 <div class="mt-4">
 {{ $subscribers->links() }}
 </div>
</div>
@endsection
