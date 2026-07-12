@extends('layouts.dashboard')

@section('title','Verify Payment - Finance')
@section('page_title','Verify Payment')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('finance.payments') }}" class="od-btn od-btn-secondary od-btn-sm mb-4">&larr; Back to Payments</a>

    <div class="od-card">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Verify Payment #{{ $payment->payment_id ?? $payment->id }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Review the payment and enrollment details before confirming.
            </p>
        </div>

        <div class="p-6 space-y-6">
            {{-- Payment details --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-3 rounded-lg" style="background: var(--od-fg-soft);">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Student</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $payment->student?->full_name ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->student?->email ?? '' }}</p>
                </div>
                <div class="p-3 rounded-lg" style="background: var(--od-fg-soft);">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Course</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $payment->course?->title ?? 'N/A' }}</p>
                    @if($enrollmentSummary['intake_name'] ?? null)
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $enrollmentSummary['intake_name'] }}</p>
                    @endif
                </div>
                <div class="p-3 rounded-lg" style="background: var(--od-fg-soft);">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Amount</p>
                    <p class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format($payment->amount, 2) }}</p>
                </div>
                <div class="p-3 rounded-lg" style="background: var(--od-fg-soft);">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Method / Reference</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $payment->transaction_id ?: 'No reference' }}</p>
                </div>
            </div>

            @if($enrollmentSummary)
            {{-- Enrollment summary --}}
            <div class="p-4 rounded-lg border" style="background: var(--od-fg-soft); border-color: var(--od-border);">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Enrollment Summary</h3>
                    <span class="od-badge {{ $enrollmentSummary['is_fully_paid'] ? 'od-badge-success' : 'od-badge-warn' }}">
                        {{ $enrollmentSummary['is_fully_paid'] ? 'Fully Paid' : 'Balance Due' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Fee</p>
                        <p class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format($enrollmentSummary['total_fee'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Paid</p>
                        <p class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format($enrollmentSummary['effective_paid'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Discounts</p>
                        <p class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format($enrollmentSummary['discounts'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Outstanding</p>
                        <p class="font-medium {{ $enrollmentSummary['outstanding'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                            ZMW {{ number_format($enrollmentSummary['outstanding'], 2) }}
                        </p>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                    Enrollment: <span class="font-medium">{{ $enrollmentSummary['enrollment_status'] }}</span> ·
                    Payment: <span class="font-medium">{{ ucfirst($enrollmentSummary['payment_status']) }}</span> ·
                    Certificate: <span class="font-medium">{{ $enrollmentSummary['certificate_blocked'] ? 'Blocked' : 'Unblocked' }}</span>
                </p>
            </div>
            @endif

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                @if($payment->payment_status !== 'Completed')
                    <form action="{{ route('finance.payments.verify', $payment) }}" method="POST">
                        @csrf
                        <button type="submit" class="od-btn od-btn-success">
                            <i class="fas fa-check mr-1.5"></i> Confirm Verification
                        </button>
                    </form>
                @else
                    <span class="od-badge od-badge-success">Already Verified</span>
                @endif
                <a href="{{ route('finance.payments') }}" class="od-btn od-btn-ghost">Cancel</a>
            </div>
        </div>
    </div>
</div>
@endsection
