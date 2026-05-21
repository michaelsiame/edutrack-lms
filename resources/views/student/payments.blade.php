@extends('layouts.dashboard')

@section('title','My Payments - Edutrack LMS')
@section('page_title','My Payments')

@section('content')
<div class="max-w-5xl mx-auto">
 <!-- Stats -->
 <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
 <x-stat-card icon="fa-check-circle" iconColor="success" :value="'K' . number_format($totalPaid ?? 0, 2)" label="Total Paid" />
 <x-stat-card icon="fa-clock" iconColor="warning" :value="'K' . number_format($totalPending ?? 0, 2)" label="Total Pending" />
 <x-stat-card icon="fa-book" iconColor="primary" :value="$activeEnrollments ?? 0" label="Active Enrollments" />
 </div>

 <!-- Payments Table -->
 <x-card class="overflow-hidden">
 <div class="p-5 md:p-6 border-b border-gray-100 dark:border-gray-700">
 <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Payment History</h2>
 </div>
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
 <thead class="bg-gray-50 dark:bg-gray-700/50">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Course</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Method</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
 </tr>
 </thead>
 <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
 @forelse($payments ?? [] as $payment)
 <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
 <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $payment->course->title ??'N/A' }}</td>
 <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">K {{ number_format($payment->amount, 2) }}</td>
 <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->payment_method }}</td>
 <td class="px-6 py-4">
 @php
 $statusColors = ['Completed' =>'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300','Pending' =>'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-300','Failed' =>'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300',
 ];
 @endphp
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$payment->payment_status] ??'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
 {{ $payment->payment_status }}
 </span>
 </td>
 <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->created_at?->format('M d, Y') }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="px-6 py-12 text-center">
 <x-empty-state icon="fa-receipt" title="No Payments Found" description="Your payment history will appear here once you make a payment." />
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </x-card>
</div>
@endsection
