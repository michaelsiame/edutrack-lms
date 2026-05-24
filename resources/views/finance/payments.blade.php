@extends('layouts.dashboard')

@section('title','All Payments - Finance')
@section('page_title','Payment Verification')

@section('content')
<div class="space-y-6">
    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
        <form action="{{ route('finance.payments') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</label>
                <select name="status" class="mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                    <option value="">All</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Failed" {{ request('status') == 'Failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
            @if(request()->hasAny(['status', 'from', 'to']))
            <a href="{{ route('finance.payments') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-times mr-2"></i>Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Payments Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-money-bill-wave text-success-500 mr-2"></i>All Payments
            </h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $payments->total() }} records</span>
        </div>
        <div class="overflow-x-auto">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="text-sm text-gray-500 dark:text-gray-400">#{{ $payment->payment_id ?? $payment->id }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold">
                                    {{ substr($payment->student->first_name ?? 'S', 0, 1) }}{{ substr($payment->student->last_name ?? '', 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $payment->student->full_name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->student->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm text-gray-600 dark:text-gray-400">{{ $payment->course->title ?? 'N/A' }}</td>
                        <td class="text-sm font-semibold text-gray-900 dark:text-white">ZMW {{ number_format($payment->amount, 2) }}</td>
                        <td class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                        <td>
                            @php
                            $statusColors = [
                                'Completed' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400',
                                'Pending' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400',
                                'Failed' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400',
                                'Refunded' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$payment->payment_status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ $payment->payment_status }}
                            </span>
                        </td>
                        <td class="text-sm text-gray-500 dark:text-gray-400">{{ $payment->created_at?->format('M d, Y') }}</td>
                        <td class="text-right">
                            @if($payment->payment_status === 'Pending')
                            <form action="{{ route('finance.payments.verify', $payment) }}" method="POST" class="inline" onsubmit="return confirm('Verify this payment of ZMW {{ number_format($payment->amount, 2) }}?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-success-600 hover:bg-success-700 text-white text-xs font-medium rounded-lg transition-colors">
                                    <i class="fas fa-check mr-1.5"></i>Verify
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-money-bill-wave text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
                            <p class="text-sm">No payments found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
