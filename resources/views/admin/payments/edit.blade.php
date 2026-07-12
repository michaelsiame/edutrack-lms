@extends('layouts.dashboard')

@section('title','Edit Payment - Edutrack LMS')
@section('page_title','Edit Payment')

@section('content')
<div class="max-w-3xl mx-auto">
 <div class="od-card">
 <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
 <h2 class="text-lg font-bold text-gray-900 dark:text-white">Edit Payment #{{ $payment->payment_id }}</h2>
 <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
 Student: {{ $payment->student?->full_name ?? 'Unknown' }} | Course: {{ $payment->course?->title ?? 'Unknown' }}
 </p>
 </div>

 @if($enrollmentSummary)
 <div class="mx-6 mt-6 p-4 rounded-lg border" style="background: var(--od-fg-soft); border-color: var(--od-border);">
 <div class="flex items-center justify-between mb-3">
 <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Enrollment Summary</h3>
 <span class="od-badge {{ $enrollmentSummary['is_fully_paid'] ? 'od-badge-success' : 'od-badge-warn' }}">
 {{ $enrollmentSummary['is_fully_paid'] ? 'Fully Paid' : 'Balance Due' }}
 </span>
 </div>
 <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
 <div>
 <p class="text-xs text-gray-500 dark:text-gray-400">Course</p>
 <p class="font-medium text-gray-900 dark:text-white truncate" title="{{ $enrollmentSummary['course_title'] }}">{{ $enrollmentSummary['course_title'] }}</p>
 </div>
 <div>
 <p class="text-xs text-gray-500 dark:text-gray-400">Total Fee</p>
 <p class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format($enrollmentSummary['total_fee'], 2) }}</p>
 </div>
 <div>
 <p class="text-xs text-gray-500 dark:text-gray-400">Paid</p>
 <p class="font-medium text-gray-900 dark:text-white">ZMW {{ number_format($enrollmentSummary['effective_paid'], 2) }}</p>
 </div>
 <div>
 <p class="text-xs text-gray-500 dark:text-gray-400">Outstanding</p>
 <p class="font-medium {{ $enrollmentSummary['outstanding'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
 ZMW {{ number_format($enrollmentSummary['outstanding'], 2) }}
 </p>
 </div>
 </div>
 @if($enrollmentSummary['discounts'] > 0)
 <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Includes ZMW {{ number_format($enrollmentSummary['discounts'], 2) }} in discounts.</p>
 @endif
 <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
 Enrollment: <span class="font-medium">{{ $enrollmentSummary['enrollment_status'] }}</span> ·
 Payment: <span class="font-medium">{{ ucfirst($enrollmentSummary['payment_status']) }}</span> ·
 Certificate: <span class="font-medium">{{ $enrollmentSummary['certificate_blocked'] ? 'Blocked' : 'Unblocked' }}</span>
 </p>
 </div>
 @endif

 <form action="{{ route('admin.payments.update', $payment) }}" method="POST" class="p-6 space-y-6">
 @csrf
 @method('PUT')

 @if($errors->any())
 <div class="p-4 od-toast-error border rounded-lg text-sm">
 <ul class="list-disc list-inside space-y-1">
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount (ZMW) <span class="text-red-500">*</span></label>
 <input type="number" name="amount" value="{{ old('amount', $payment->amount) }}" required step="0.01" min="0"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method <span class="text-red-500">*</span></label>
 <select name="payment_method_id" required
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <option value="">Select Method</option>
 @foreach($paymentMethods as $method)
 <option value="{{ $method->payment_method_id }}" {{ old('payment_method_id', $payment->payment_method_id) == $method->payment_method_id ? 'selected' : '' }}>{{ $method->method_name }}</option>
 @endforeach
 </select>
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-500">*</span></label>
 <select name="payment_status" required
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <option value="Pending" {{ old('payment_status', $payment->payment_status) == 'Pending' ? 'selected' : '' }}>Pending</option>
 <option value="Completed" {{ old('payment_status', $payment->payment_status) == 'Completed' ? 'selected' : '' }}>Completed</option>
 <option value="Failed" {{ old('payment_status', $payment->payment_status) == 'Failed' ? 'selected' : '' }}>Failed</option>
 <option value="Refunded" {{ old('payment_status', $payment->payment_status) == 'Refunded' ? 'selected' : '' }}>Refunded</option>
 </select>
 </div>
 </div>

 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transaction ID</label>
 <input type="text" name="transaction_id" value="{{ old('transaction_id', $payment->transaction_id) }}" maxlength="255"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"
 placeholder="Reference number or transaction ID">
 </div>

 <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
 <button type="submit" class="px-4 py-2 od-btn od-btn-primary text-sm">Update Payment</button>
 <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 od-btn od-btn-ghost text-sm">Cancel</a>
 </div>
 </form>
 </div>
</div>
@endsection
