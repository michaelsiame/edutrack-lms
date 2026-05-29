@extends('layouts.dashboard')

@section('title', 'Promotion Details - Admin')
@section('page_title', 'Promotion Details')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $promotion->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Code: <span class="font-mono font-semibold">{{ $promotion->code }}</span></p>
            </div>
            <div class="flex items-center gap-2">
                @if($promotion->isValid())
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400">Active</span>
                @elseif($promotion->isExpired())
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Expired</span>
                @elseif($promotion->isUpcoming())
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400">Upcoming</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400">Inactive</span>
                @endif
            </div>
        </div>

        <div class="p-6 space-y-6">
            @if($promotion->description)
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $promotion->description }}</p>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Discount</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold" style="color: var(--od-accent);">{{ $promotion->formattedDiscount() }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Type: {{ ucfirst($promotion->discount_type) }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Usage</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $promotion->used_count }}{{ $promotion->max_uses ? ' / ' . $promotion->max_uses : '' }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $promotion->max_uses ? 'Maximum uses allowed' : 'Unlimited uses' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Valid Period</h3>
                    <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <p><span class="text-gray-500">Starts:</span> {{ $promotion->starts_at?->format('F d, Y h:i A') ?? 'Immediately' }}</p>
                        <p><span class="text-gray-500">Ends:</span> {{ $promotion->ends_at?->format('F d, Y h:i A') ?? 'Never' }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Restrictions</h3>
                    <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        @if($promotion->min_order_amount)
                        <p><span class="text-gray-500">Min Order:</span> K{{ number_format($promotion->min_order_amount, 2) }}</p>
                        @else
                        <p><span class="text-gray-500">Min Order:</span> None</p>
                        @endif
                        @if(!empty($promotion->applicable_courses))
                        <p><span class="text-gray-500">Applies to:</span> {{ count($promotion->applicable_courses) }} specific course(s)</p>
                        @else
                        <p><span class="text-gray-500">Applies to:</span> All courses</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>Created by {{ $promotion->creator?->full_name ?? 'Unknown' }} on {{ $promotion->created_at->format('M d, Y') }}</span>
                <span>Last updated {{ $promotion->updated_at->diffForHumans() }}</span>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('admin.promotions.edit', $promotion) }}" class="px-4 py-2 od-btn od-btn-primary text-sm">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <a href="{{ route('admin.promotions.index') }}" class="px-4 py-2 od-btn od-btn-ghost text-sm">Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection
