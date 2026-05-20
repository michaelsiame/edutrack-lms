@extends('layouts.dashboard')

@section('title', 'Payment Details - Edutrack LMS')
@section('page_title', 'Payment Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payment #{{ $payment->id }}</h3>
            <a href="{{ route('admin.payments.edit', $payment) }}" class="text-primary-600 hover:underline">Edit</a>
        </div>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Student</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $payment->student?->full_name ?? 'Unknown' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Course</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $payment->course?->title ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Amount</span>
                <span class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Status</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $payment->payment_status === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $payment->payment_status }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Date</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $payment->created_at?->format('M d, Y') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
