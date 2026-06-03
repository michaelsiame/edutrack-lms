@extends('layouts.dashboard')

@section('title','Payments - Edutrack LMS')
@section('page_title','Payment Management')

@section('content')
<div class="space-y-6">
<!-- Filters -->
<div class="od-card p-4">
 <form action="{{ route('admin.payments.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
 <div>
 <label class="od-form-label">From</label>
 <input type="date" name="from" value="{{ request('from') }}"
 class="od-input">
 </div>
 <div>
 <label class="od-form-label">To</label>
 <input type="date" name="to" value="{{ request('to') }}"
 class="od-input">
 </div>
 <div>
 <label class="od-form-label">Status</label>
 <select name="status" class="od-input">
 <option value="">All</option>
 <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
 <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
 <option value="Failed" {{ request('status') === 'Failed' ? 'selected' : '' }}>Failed</option>
 <option value="Refunded" {{ request('status') === 'Refunded' ? 'selected' : '' }}>Refunded</option>
 </select>
 </div>
 <button type="submit" class="od-btn od-btn-primary od-btn-sm">Filter</button>
 <a href="{{ route('admin.payments.index') }}" class="od-btn od-btn-secondary od-btn-sm">Clear</a>
 <a href="{{ route('admin.reports.export', 'payments') }}?{{ http_build_query(request()->only(['from','to','status'])) }}" class="od-btn od-btn-success od-btn-sm">
 <i class="fas fa-download mr-1"></i>CSV
 </a>
 </form>
</div>

<div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="od-card-header">
 <h3 class="od-h3">All Payments</h3>
 <span class="od-meta">{{ $payments->total() }} total</span>
 </div>
 <div class="overflow-x-auto">
 <table class="od-table min-w-[640px]">
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
 <span class="font-medium" style="color: var(--od-fg);">{{ $payment->student?->full_name ??'Unknown' }}</span>
 </td>
 <td class="od-meta">{{ $payment->course?->title ?? ($payment->payment_type === 'registration' ? 'Registration Fee' : 'N/A') }}</td>
 <td class="font-medium" style="color: var(--od-fg);">{{ setting('currency', 'ZMW') }} {{ number_format($payment->amount, 2) }}</td>
 <td>
 <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $payment->payment_status ==='Completed' ?'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400' }}">
 {{ $payment->payment_status }}
 </span>
 </td>
 <td class="od-meta">{{ $payment->created_at?->format('M d, Y') }}</td>
 <td class="text-right">
 <a href="{{ route('admin.payments.show', $payment) }}" class="od-btn od-btn-ghost od-btn-sm" aria-label="View payment">
 <i class="fas fa-eye text-sm"></i>
 </a>
 <a href="{{ route('admin.payments.edit', $payment) }}" class="od-btn od-btn-ghost od-btn-sm" aria-label="Edit payment">
 <i class="fas fa-edit text-sm"></i>
 </a>
 <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" class="inline" data-confirm="Delete this payment record">
 @csrf
 @method('DELETE')
 <button type="submit" class="od-btn od-btn-ghost od-btn-sm text-danger-600 hover:text-danger-700" aria-label="Delete payment">
 <i class="fas fa-trash text-sm"></i>
 </button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="od-empty-sm">No payments found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="od-card-header" style="border-top: 1px solid var(--od-border); border-bottom: none;">
 {{ $payments->links() }}
 </div>
</div>
</div>
@endsection
