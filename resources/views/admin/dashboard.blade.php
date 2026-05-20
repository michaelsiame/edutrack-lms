@extends('layouts.dashboard')

@section('title', 'Admin Dashboard - Edutrack LMS')
@section('page_title', 'Admin Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="stat-card bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_users']) }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-indigo-600 dark:text-indigo-400 text-lg"></i>
            </div>
        </div>
        <div class="mt-3 flex items-center text-xs">
            <span class="text-green-600 dark:text-green-400 font-medium"><i class="fas fa-arrow-up mr-1"></i>Active</span>
            <span class="text-gray-400 dark:text-gray-500 ml-2">All roles</span>
        </div>
    </div>

    <div class="stat-card bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Courses</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_courses']) }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-book text-emerald-600 dark:text-emerald-400 text-lg"></i>
            </div>
        </div>
        <div class="mt-3 flex items-center text-xs">
            <span class="text-green-600 dark:text-green-400 font-medium"><i class="fas fa-check mr-1"></i>Published</span>
            <span class="text-gray-400 dark:text-gray-500 ml-2">Across categories</span>
        </div>
    </div>

    <div class="stat-card bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Enrollments</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_enrollments']) }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-graduate text-amber-600 dark:text-amber-400 text-lg"></i>
            </div>
        </div>
        <div class="mt-3 flex items-center text-xs">
            <span class="text-blue-600 dark:text-blue-400 font-medium"><i class="fas fa-chart-line mr-1"></i>Growing</span>
            <span class="text-gray-400 dark:text-gray-500 ml-2">This year</span>
        </div>
    </div>

    <div class="stat-card bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">ZMW {{ number_format($stats['total_revenue'], 0) }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-coins text-purple-600 dark:text-purple-400 text-lg"></i>
            </div>
        </div>
        <div class="mt-3 flex items-center text-xs">
            <span class="text-yellow-600 dark:text-yellow-400 font-medium"><i class="fas fa-clock mr-1"></i>{{ $stats['pending_payments'] }} Pending</span>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <!-- Recent Enrollments -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                <i class="fas fa-user-plus text-primary-500 mr-2"></i>Recent Enrollments
            </h3>
            <a href="#" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($stats['recent_enrollments'] as $enrollment)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-sm">
                            {{ strtoupper(substr($enrollment->user?->first_name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $enrollment->user?->full_name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $enrollment->course?->title ?? 'Unknown Course' }}</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $enrollment->created_at?->diffForHumans() }}</span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-inbox text-3xl mb-2 text-gray-300 dark:text-gray-600"></i>
                    <p>No recent enrollments</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>Recent Payments
            </h3>
            <a href="#" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($stats['recent_payments'] as $payment)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400">
                            <i class="fas fa-check text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $payment->student?->full_name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">ZMW {{ number_format($payment->amount, 2) }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $payment->payment_status === 'Completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                        {{ $payment->payment_status }}
                    </span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-inbox text-3xl mb-2 text-gray-300 dark:text-gray-600"></i>
                    <p>No recent payments</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
