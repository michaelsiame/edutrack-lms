@extends('layouts.dashboard')

@section('title','Promotions - Admin')
@section('page_title','Promotions')

@section('content')
<div class="max-w-6xl mx-auto">
 @if(session('success'))
 <div class="mb-4 p-4 od-toast-success border rounded-lg">{{ session('success') }}</div>
 @endif

 <div class="flex items-center justify-between mb-6">
 <h2 class="text-xl font-bold text-gray-900 dark:text-white">All Promotions</h2>
 <a href="{{ route('admin.promotions.create') }}" class="px-4 py-2 od-btn od-btn-primary text-sm">
 <i class="fas fa-plus mr-1"></i>New Promotion
 </a>
 </div>

 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <div class="overflow-x-auto">
 <table class="w-full text-sm min-w-[640px]">
 <thead class="bg-gray-50 dark:bg-gray-700/50">
 <tr>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Code</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Name</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Discount</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Status</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Uses</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Period</th>
 <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300" scope="col">Actions</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
 @forelse($promotions as $promotion)
 <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
 <td class="px-4 py-3">
 <div class="font-mono font-bold text-gray-900 dark:text-white">{{ $promotion->code }}</div>
 </td>
 <td class="px-4 py-3">
 <div class="font-medium text-gray-900 dark:text-white">{{ $promotion->name }}</div>
 </td>
 <td class="px-4 py-3">
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-accent-100 text-accent-800 dark:bg-accent-900/30 dark:text-accent-400">
 {{ $promotion->formattedDiscount() }}
 </span>
 </td>
 <td class="px-4 py-3">
 @if($promotion->isValid())
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400">Active</span>
 @elseif($promotion->isExpired())
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Expired</span>
 @elseif($promotion->isUpcoming())
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400">Upcoming</span>
 @else
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400">Inactive</span>
 @endif
 </td>
 <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
 {{ $promotion->used_count }}{{ $promotion->max_uses ? '/'.$promotion->max_uses : '' }}
 </td>
 <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
 {{ $promotion->starts_at?->format('M d') ?? 'Now' }} - {{ $promotion->ends_at?->format('M d, Y') ?? 'Never' }}
 </td>
 <td class="px-4 py-3 text-right">
 <a href="{{ route('admin.promotions.edit', $promotion) }}" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg mr-1" aria-label="Edit promotion">
 <i class="fas fa-edit" aria-hidden="true"></i>
 </a>
 <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" class="inline" onsubmit="return confirm('Delete this promotion?')">
 @csrf
 @method('DELETE')
 <button type="submit" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-danger-600 hover:text-danger-700 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg" aria-label="Delete promotion">
 <i class="fas fa-trash" aria-hidden="true"></i>
 </button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No promotions yet.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 <div class="mt-4">
 {{ $promotions->links() }}
 </div>
</div>
@endsection
