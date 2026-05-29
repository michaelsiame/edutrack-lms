@extends('layouts.dashboard')

@section('title','Record Payment - Edutrack LMS')
@section('page_title','Record Payment')

@section('content')
<div class="max-w-3xl mx-auto">
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
 <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
 <h2 class="text-lg font-bold text-gray-900 dark:text-white">Record New Payment</h2>
 </div>

 <form action="{{ route('admin.payments.store') }}" method="POST" class="p-6 space-y-6">
 @csrf

 @if($errors->any())
 <div class="p-4 od-toast-error border rounded-lg text-sm">
 <ul class="list-disc list-inside space-y-1">
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student <span class="text-red-500">*</span></label>
 <select name="student_id" required
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <option value="">Select Student</option>
 @foreach($students as $student)
 <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>{{ $student->full_name ?? ($student->user?->username ?? 'Student #'.$student->id) }} ({{ $student->email ?? 'no email' }})</option>
 @endforeach
 </select>
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Course <span class="text-red-500">*</span></label>
 <select name="course_id" required
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <option value="">Select Course</option>
 @foreach($courses as $course)
 <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
 @endforeach
 </select>
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount (ZMW) <span class="text-red-500">*</span></label>
 <input type="number" name="amount" value="{{ old('amount') }}" required step="0.01" min="0"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method <span class="text-red-500">*</span></label>
 <select name="payment_method_id" required
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <option value="">Select Method</option>
 @foreach($paymentMethods as $method)
 <option value="{{ $method->payment_method_id }}" {{ old('payment_method_id') == $method->payment_method_id ? 'selected' : '' }}>{{ $method->method_name }}</option>
 @endforeach
 </select>
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-500">*</span></label>
 <select name="payment_status" required
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <option value="Pending" {{ old('payment_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
 <option value="Completed" {{ old('payment_status') == 'Completed' ? 'selected' : '' }}>Completed</option>
 <option value="Failed" {{ old('payment_status') == 'Failed' ? 'selected' : '' }}>Failed</option>
 <option value="Refunded" {{ old('payment_status') == 'Refunded' ? 'selected' : '' }}>Refunded</option>
 </select>
 </div>
 </div>

 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transaction ID</label>
 <input type="text" name="transaction_id" value="{{ old('transaction_id') }}" maxlength="255"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"
 placeholder="Reference number or transaction ID">
 </div>

 <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
 <button type="submit" class="px-4 py-2 od-btn od-btn-primary text-sm">Record Payment</button>
 <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 od-btn od-btn-ghost text-sm">Cancel</a>
 </div>
 </form>
 </div>
</div>
@endsection
