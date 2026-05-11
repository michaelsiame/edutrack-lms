@extends('layouts.dashboard')

@section('title', 'Finance Dashboard - Edutrack LMS')
@section('page_title', 'Finance Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
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
    </div>

    <div class="stat-card bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Revenue</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">ZMW {{ number_format($stats['today_revenue'], 0) }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-day text-blue-600 dark:text-blue-400 text-lg"></i>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Month Revenue</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">ZMW {{ number_format($stats['month_revenue'], 0) }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-chart-line text-emerald-600 dark:text-emerald-400 text-lg"></i>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Payments</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['pending_payments'] }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-amber-600 dark:text-amber-400 text-lg"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-800 dark:text-white">
            <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>Recent Transactions
        </h3>
        <a href="{{ route('finance.transactions') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentPayments as $payment)
                    <tr>
                        <td>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $payment->student?->full_name ?? 'Unknown' }}</span>
                        </td>
                        <td class="text-gray-600 dark:text-gray-400">{{ $payment->course?->title ?? 'N/A' }}</td>
                        <td class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format($payment->amount, 2) }}</td>
                        <td>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $payment->payment_status === 'Completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                                {{ $payment->payment_status }}
                            </span>
                        </td>
                        <td class="text-gray-500 dark:text-gray-400 text-sm">{{ $payment->created_at?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-500 dark:text-gray-400">No recent transactions</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
