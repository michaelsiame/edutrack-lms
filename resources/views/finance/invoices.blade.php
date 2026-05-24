@extends('layouts.dashboard')

@section('title','Invoices - Finance')
@section('page_title','Invoices')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-file-invoice text-primary-500 mr-2"></i>All Invoices
            </h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $invoices->total() }} records</span>
        </div>
        <div class="overflow-x-auto">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td class="text-sm font-medium text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold">
                                    {{ substr($invoice->student->first_name ?? 'S', 0, 1) }}{{ substr($invoice->student->last_name ?? '', 0, 1) }}
                                </div>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $invoice->student->full_name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="text-sm text-gray-600 dark:text-gray-400">{{ $invoice->course->title ?? 'N/A' }}</td>
                        <td class="text-sm font-semibold text-gray-900 dark:text-white">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                        <td>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $invoice->status === 'paid' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :
                                   ($invoice->status === 'draft' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' :
                                   'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400') }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="text-sm text-gray-500 dark:text-gray-400">{{ $invoice->invoice_date?->format('M d, Y') }}</td>
                        <td class="text-right">
                            <a href="{{ route('finance.invoices.download', $invoice) }}" class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-lg transition-colors">
                                <i class="fas fa-download mr-1.5"></i>PDF
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-file-invoice text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
                            <p class="text-sm">No invoices found. Invoices are generated automatically when payments are verified.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
