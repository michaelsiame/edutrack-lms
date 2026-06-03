@extends('layouts.dashboard')

@section('title','Transactions - Edutrack LMS')
@section('page_title','All Transactions')

@section('content')
<div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="od-card-header">
 <h3 class="od-h3">All Transactions</h3>
 <span class="od-meta">{{ $payments->total() }} total</span>
 </div>
 <div class="overflow-x-auto">
 <table class="od-table min-w-[640px]">
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
 <span class="font-medium" style="color: var(--od-fg);">{{ $payment->student?->full_name ?? ($payment->user?->name ??'Unknown') }}</span>
 </td>
 <td class="od-meta">{{ $payment->course?->title ??'N/A' }}</td>
 <td class="font-medium" style="color: var(--od-fg);">ZMW {{ number_format($payment->amount, 2) }}</td>
 <td class="od-meta">{{ $payment->payment_method ??'N/A' }}</td>
 <td>
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $payment->payment_status ==='Completed' ?'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400' }}">
 {{ $payment->payment_status }}
 </span>
 </td>
 <td class="text-gray-500 dark:text-gray-400 text-sm">{{ $payment->created_at?->format('M d, Y H:i') }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="od-empty-sm">
 <i class="fas fa-money-bill-wave text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
 <p class="text-sm">No transactions found.</p>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="od-card-header" style="border-top: 1px solid var(--od-border); border-bottom: none;">
 {{ $payments->links() }}
 </div>
</div>
@endsection
