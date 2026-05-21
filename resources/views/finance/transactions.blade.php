@extends('layouts.dashboard')

@section('title','Transactions - Edutrack LMS')
@section('page_title','All Transactions')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
 <h3 class="text-base font-semibold text-gray-800 dark:text-white">All Transactions</h3>
 <span class="text-sm text-gray-500 dark:text-gray-400">{{ $payments->total() }} total</span>
 </div>
 <div class="overflow-x-auto">
 <table class="dashboard-table">
 <thead>
 <tr>
 <th>Student</th>
 <th>Course</th>
 <th>Amount</th>
 <th>Method</th>
 <th>Status</th>
 <th>Date</th>
 </tr>
 </thead>
 <tbody>
 @forelse($payments as $payment)
 <tr>
 <td>
 <span class="font-medium text-gray-900 dark:text-white">{{ $payment->student?->full_name ?? ($payment->user?->name ??'Unknown') }}</span>
 </td>
 <td class="text-gray-600 dark:text-gray-400">{{ $payment->course?->title ??'N/A' }}</td>
 <td class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format($payment->amount, 2) }}</td>
 <td class="text-gray-600 dark:text-gray-400">{{ $payment->payment_method ??'N/A' }}</td>
 <td>
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $payment->payment_status ==='Completed' ?'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400' }}">
 {{ $payment->payment_status }}
 </span>
 </td>
 <td class="text-gray-500 dark:text-gray-400 text-sm">{{ $payment->created_at?->format('M d, Y H:i') }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="text-center py-10 text-gray-500 dark:text-gray-400">No transactions found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
 {{ $payments->links() }}
 </div>
</div>
@endsection
