@extends('layouts.dashboard')

@section('title','Financial Dashboard - Finance')
@section('page_title','Financial Dashboard')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
 <!-- Stats Cards -->
 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
 <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
 <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">ZMW {{ number_format($totalRevenue ?? 0, 0) }}</p>
 </div>
 <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
 <i class="fas fa-wallet text-success-600 dark:text-success-400 text-lg"></i>
 </div>
 </div>
 </div>

 <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Payments</p>
 <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">ZMW {{ number_format($pendingRevenue ?? 0, 0) }}</p>
 </div>
 <div class="w-12 h-12 bg-secondary-100 dark:bg-secondary-900/30 rounded-xl flex items-center justify-center">
 <i class="fas fa-clock text-secondary-600 dark:text-secondary-400 text-lg"></i>
 </div>
 </div>
 </div>

 <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Collections</p>
 <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">ZMW {{ number_format($todayRevenue ?? 0, 0) }}</p>
 </div>
 <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
 <i class="fas fa-calendar-day text-primary-600 dark:text-primary-400 text-lg"></i>
 </div>
 </div>
 </div>

 <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Overdue</p>
 <p class="text-2xl font-bold text-danger-600 dark:text-danger-400 mt-1">{{ $overdueCount ?? 0 }}</p>
 </div>
 <div class="w-12 h-12 bg-danger-100 dark:bg-danger-900/30 rounded-xl flex items-center justify-center">
 <i class="fas fa-exclamation-triangle text-danger-600 dark:text-danger-400 text-lg"></i>
 </div>
 </div>
 </div>
 </div>

 <!-- Recent Payments -->
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
 <h3 class="text-base font-semibold text-gray-900 dark:text-white">
 <i class="fas fa-money-bill-wave text-success-500 mr-2"></i>Recent Payments
 </h3>
 <a href="{{ route('finance.payments') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">View All</a>
 </div>
 <div class="overflow-x-auto">
 <table class="w-full text-sm min-w-[640px]">
 <thead class="bg-gray-50 dark:bg-gray-700/50">
 <tr>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Student</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Course</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Amount</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Method</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Status</th>
 <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300" scope="col">Date</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
 @forelse($recentPayments ?? [] as $payment)
 <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
 <td class="px-4 py-3">
 <div class="font-medium text-gray-900 dark:text-white">{{ $payment->user->name ?? 'N/A' }}</div>
 <div class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->user->email ?? '' }}</div>
 </td>
 <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $payment->course->title ?? 'N/A' }}</td>
 <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">ZMW {{ number_format($payment->amount, 2) }}</td>
 <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $payment->payment_method }}</td>
 <td class="px-4 py-3">
 @php
 $statusColors = [
 'Completed' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400',
 'Pending' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400',
 'Failed' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400',
 'Refunded' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
 ];
 @endphp
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$payment->payment_status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
 {{ $payment->payment_status }}
 </span>
 </td>
 <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $payment->created_at?->format('M d, Y') }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
 <i class="fas fa-money-bill-wave text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
 <p class="text-sm">No payments found.</p>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
</div>
@endsection
