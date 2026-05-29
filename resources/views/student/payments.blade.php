@extends('layouts.dashboard')

@section('title','My Payments - Edutrack LMS')
@section('page_title','My Payments')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <p class="od-eyebrow">BILLING</p>
    <h1 class="od-h1 mb-8">My Payments</h1>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="od-stat-card">
            <div>
                <div class="od-stat-value od-num">ZMW {{ number_format($totalPaid ?? 0, 0) }}</div>
                <div class="od-stat-label">Total Paid</div>
            </div>
            <div class="od-stat-icon" style="background: var(--od-green-soft); color: var(--od-green);">
                <i class="fas fa-check-circle text-sm"></i>
            </div>
        </div>
        <div class="od-stat-card">
            <div>
                <div class="od-stat-value od-num">ZMW {{ number_format($totalPending ?? 0, 0) }}</div>
                <div class="od-stat-label">Total Pending</div>
            </div>
            <div class="od-stat-icon" style="background: var(--od-accent-soft); color: var(--od-accent);">
                <i class="fas fa-clock text-sm"></i>
            </div>
        </div>
        <div class="od-stat-card">
            <div>
                <div class="od-stat-value od-num">{{ $activeEnrollments ?? 0 }}</div>
                <div class="od-stat-label">Active Enrollments</div>
            </div>
            <div class="od-stat-icon" style="background: var(--od-navy-soft); color: var(--od-navy);">
                <i class="fas fa-book text-sm"></i>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="od-card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="od-h3">Payment History</h3>
        </div>

        @if($payments->isEmpty())
            <x-empty-state icon="fa-receipt" title="No Payments Found" description="Your payment history will appear here once you make a payment." variant="od" />
        @else
            <table class="od-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        @php
                            $methodIcons = [
                                'card' => 'fa-credit-card',
                                'mobile_money' => 'fa-mobile-alt',
                                'bank_transfer' => 'fa-university',
                                'cash' => 'fa-money-bill-wave',
                            ];
                            $methodIcon = $methodIcons[strtolower($payment->payment_method ?? '')] ?? 'fa-credit-card';
                        @endphp
                        <tr>
                            <td>
                                <div class="font-medium text-sm">{{ $payment->course?->title ?? ($payment->payment_type === 'registration' ? 'Registration Fee' : 'N/A') }}</div>
                                @if($payment->transaction_id)
                                    <div class="od-num text-xs" style="color: var(--od-muted);">{{ $payment->transaction_id }}</div>
                                @endif
                            </td>
                            <td class="num-col">
                                <span class="text-sm font-bold">ZMW {{ number_format($payment->amount, 2) }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2 text-sm" style="color: var(--od-muted);">
                                    <i class="fas {{ $methodIcon }} text-xs"></i>
                                    {{ $payment->payment_method ?? 'Online' }}
                                </div>
                            </td>
                            <td>
                                @if($payment->payment_status === 'Completed')
                                    <span class="od-badge od-badge-success">{{ $payment->payment_status }}</span>
                                @elseif($payment->payment_status === 'Pending')
                                    <span class="od-badge od-badge-warn">{{ $payment->payment_status }}</span>
                                @else
                                    <span class="od-badge od-badge-danger">{{ $payment->payment_status }}</span>
                                @endif
                            </td>
                            <td class="num-col">
                                <span class="text-sm" style="color: var(--od-muted);">{{ $payment->created_at?->format('M d, Y') }}</span>
                            </td>
                            <td class="num-col">
                                @if($payment->payment_status === 'Completed')
                                    <a href="{{ route('student.payments.receipt', $payment) }}" class="od-btn od-btn-ghost od-btn-sm" title="Download Receipt">
                                        <i class="fas fa-file-download"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($payments->hasPages())
                <div class="mt-4 pt-4" style="border-top: 1px solid var(--od-border);">
                    {{ $payments->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
