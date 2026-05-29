@extends('layouts.dashboard')

@section('title','Payments - Edutrack LMS')
@section('page_title','Payment Management')

@section('content')
<div class="space-y-6">
<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
 <form action="{{ route('admin.payments.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">From</label>
 <input type="date" name="from" value="{{ request('from') }}"
 class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">To</label>
 <input type="date" name="to" value="{{ request('to') }}"
 class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Status</label>
 <select name="status" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
 <option value="">All</option>
 <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
 <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
 <option value="Failed" {{ request('status') === 'Failed' ? 'selected' : '' }}>Failed</option>
 <option value="Refunded" {{ request('status') === 'Refunded' ? 'selected' : '' }}>Refunded</option>
 </select>
 </div>
 <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium">Filter</button>
 <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Clear</a>
 <a href="{{ route('admin.reports.export', 'payments') }}?{{ http_build_query(request()->only(['from','to','status'])) }}" class="px-4 py-2 bg-success-600 text-white rounded-lg hover:bg-success-700 text-sm font-medium">
 <i class="fas fa-download mr-1"></i>CSV
 </a>
 </form>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
 <h3 class="text-base font-semibold text-gray-800 dark:text-white">All Payments</h3>
 <span class="text-sm text-gray-500 dark:text-gray-400">{{ $payments->total() }} total</span>
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
 <th class="text-right">Actions</th>
 </tr>
 </thead>
 <tbody>
 @forelse($payments as $payment)
 <tr>
 <td>
 <span class="font-medium text-gray-900 dark:text-white">{{ $payment->student?->full_name ??'Unknown' }}</span>
 </td>
 <td class="text-gray-600 dark:text-gray-400">{{ $payment->course?->title ?? ($payment->payment_type === 'registration' ? 'Registration Fee' : 'N/A') }}</td>
 <td class="font-medium text-gray-900 dark:text-white">{{ setting('currency', 'ZMW') }} {{ number_format($payment->amount, 2) }}</td>
 <td>
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $payment->payment_status ==='Completed' ?'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400' }}">
 {{ $payment->payment_status }}
 </span>
 </td>
 <td class="text-gray-500 dark:text-gray-400 text-sm">{{ $payment->created_at?->format('M d, Y') }}</td>
 <td class="text-right">
 <a href="{{ route('admin.payments.show', $payment) }}" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] text-gray-500 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg mr-1" aria-label="View payment">
 <i class="fas fa-eye text-sm"></i>
 </a>
 <a href="{{ route('admin.payments.edit', $payment) }}" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] text-gray-500 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg mr-1" aria-label="Edit payment">
 <i class="fas fa-edit text-sm"></i>
 </a>
 <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" class="inline" onsubmit="return confirm('Delete this payment record?')">
 @csrf
 @method('DELETE')
 <button type="submit" class="inline-flex items-center justify-center min-w-[36px] min-h-[36px] text-gray-500 hover:text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg" aria-label="Delete payment">
 <i class="fas fa-trash text-sm"></i>
 </button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="text-center py-10 text-gray-500 dark:text-gray-400">No payments found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
 {{ $payments->links() }}
 </div>
</div>
</div>
@endsection
