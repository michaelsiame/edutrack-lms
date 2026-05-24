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
    <x-card variant="default" class="overflow-hidden">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <i class="fas fa-receipt text-primary-500"></i>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">Payment History</h3>
            </div>
        </x-slot:header>

        @if($payments->isEmpty())
            <x-empty-state icon="fa-receipt" title="No Payments Found" description="Your payment history will appear here once you make a payment." />
        @else
            <x-data-table :columns="['Course', 'Amount', 'Method', 'Status', 'Date']">
                @foreach($payments as $payment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-4 md:px-6 py-4">
                            <div class="font-medium text-gray-900 dark:text-white text-sm">{{ $payment->course?->title ?? 'N/A' }}</div>
                            @if($payment->transaction_id)
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 font-mono">{{ $payment->transaction_id }}</div>
                            @endif
                        </td>
                        <td class="px-4 md:px-6 py-4">
                            <span class="text-sm font-bold text-gray-900 dark:text-white">K {{ number_format($payment->amount, 2) }}</span>
                        </td>
                        <td class="px-4 md:px-6 py-4">
                            @php
                                $methodIcons = [
                                    'card' => 'fa-credit-card',
                                    'mobile_money' => 'fa-mobile-alt',
                                    'bank_transfer' => 'fa-university',
                                    'cash' => 'fa-money-bill-wave',
                                ];
                                $methodIcon = $methodIcons[strtolower($payment->payment_method ?? '')] ?? 'fa-credit-card';
                            @endphp
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <i class="fas {{ $methodIcon }} text-gray-400 dark:text-gray-500 text-xs"></i>
                                {{ $payment->payment_method ?? 'Online' }}
                            </div>
                        </td>
                        <td class="px-4 md:px-6 py-4">
                            <x-status-badge :status="$payment->payment_status" size="sm" />
                        </td>
                        <td class="px-4 md:px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $payment->created_at?->format('M d, Y') }}
                                </span>
                                @if($payment->payment_status === 'Completed')
                                    <x-button :href="route('student.payments.receipt', $payment)" variant="ghost" size="sm" icon="fa-file-download" title="Download Receipt" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>

            @if($payments->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $payments->links() }}
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
