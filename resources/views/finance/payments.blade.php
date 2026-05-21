@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
 <div class="flex justify-between items-center mb-6">
 <h1 class="text-2xl font-bold">All Payments</h1>
 <div class="flex space-x-2">
 <a href="{{ route('finance.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
 <i class="fas fa-chart-line mr-2"></i>Dashboard
 </a>
 </div>
 </div>

 <!-- Filters -->
 <div class="bg-white rounded-lg shadow p-4 mb-6">
 <form action="{{ route('finance.payments') }}" method="GET" class="flex flex-wrap gap-4 items-end">
 <div>
 <label class="block text-sm font-medium text-gray-700">Status</label>
 <select name="status" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
 <option value="">All</option>
 <option value="Completed" {{ request('status') =='Completed' ?'selected' :'' }}>Completed</option>
 <option value="Pending" {{ request('status') =='Pending' ?'selected' :'' }}>Pending</option>
 <option value="Failed" {{ request('status') =='Failed' ?'selected' :'' }}>Failed</option>
 </select>
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700">Date From</label>
 <input type="date" name="from" value="{{ request('from') }}" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700">Date To</label>
 <input type="date" name="to" value="{{ request('to') }}" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
 </div>
 <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">
 <i class="fas fa-filter mr-2"></i>Filter
 </button>
 </form>
 </div>

 <div class="bg-white rounded-lg shadow overflow-hidden">
 <table class="min-w-full divide-y divide-gray-200">
 <thead class="bg-gray-50">
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
 </tr>
 </thead>
 <tbody class="bg-white divide-y divide-gray-200">
 @forelse($payments as $payment)
 <tr>
 <td class="px-6 py-4 text-sm text-gray-500">#{{ $payment->payment_id ?? $payment->id }}</td>
 <td class="px-6 py-4">
 <div class="text-sm font-medium text-gray-900">{{ $payment->user->name ??'N/A' }}</div>
 <div class="text-sm text-gray-500">{{ $payment->user->email ??'' }}</div>
 </td>
 <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->course->title ??'N/A' }}</td>
 <td class="px-6 py-4 text-sm font-medium text-gray-900">K {{ number_format($payment->amount, 2) }}</td>
 <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->payment_method }}</td>
 <td class="px-6 py-4">
 @php
 $statusColors = ['Completed' =>'bg-success-100 text-success-800','Pending' =>'bg-secondary-100 text-secondary-800','Failed' =>'bg-danger-100 text-danger-800','Refunded' =>'bg-gray-100 text-gray-800',
 ];
 @endphp
 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$payment->payment_status] ??'bg-gray-100 text-gray-800' }}">
 {{ $payment->payment_status }}
 </span>
 </td>
 <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->created_at?->format('M d, Y H:i') }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="7" class="px-6 py-8 text-center text-gray-500">No payments found.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 @if($payments->hasPages())
 <div class="mt-4">
 {{ $payments->links() }}
 </div>
 @endif
</div>
@endsection
