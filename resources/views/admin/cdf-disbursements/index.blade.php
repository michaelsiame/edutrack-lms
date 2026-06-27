@extends('layouts.dashboard')

@section('title','CDF Disbursements - Admin')
@section('page_title','CDF Disbursements')

@section('content')
<div class="max-w-6xl mx-auto">
    @if(session('success'))
    <div class="mb-4 p-4 od-toast-success">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <a href="{{ route('admin.reports.cdf') }}" class="od-btn od-btn-secondary od-btn-sm">&larr; Back to CDF Report</a>
        <a href="{{ route('admin.cdf-disbursements.create') }}" class="od-btn od-btn-primary od-btn-sm">
            <i class="fas fa-plus mr-1"></i>Record a Disbursement
        </a>
    </div>

    <!-- Filter -->
    <div class="od-card p-4 mb-6">
        <form action="{{ route('admin.cdf-disbursements.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="od-form-label">Constituency</label>
                <input type="text" name="constituency" value="{{ request('constituency') }}" class="od-input" placeholder="Search constituency">
            </div>
            <button type="submit" class="od-btn od-btn-primary od-btn-sm">Filter</button>
            <a href="{{ route('admin.cdf-disbursements.index') }}" class="od-btn od-btn-secondary od-btn-sm">Clear</a>
        </form>
    </div>

    <!-- Disbursements List -->
    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="overflow-x-auto">
            <table class="od-table min-w-[640px]">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left" scope="col">Constituency</th>
                        <th class="px-4 py-3 text-left" scope="col">Received Date</th>
                        <th class="px-4 py-3 text-right" scope="col">Amount</th>
                        <th class="px-4 py-3 text-left" scope="col">Reference</th>
                        <th class="px-4 py-3 text-left" scope="col">Recorded By</th>
                        <th class="px-4 py-3 text-right" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disbursements as $disbursement)
                    <tr>
                        <td class="px-4 py-3 font-medium" style="color: var(--od-fg);">{{ $disbursement->constituency }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $disbursement->received_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-right">{{ $disbursement->currency }} {{ number_format($disbursement->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $disbursement->reference ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $disbursement->recordedBy?->full_name ?? 'System' }}</td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('admin.cdf-disbursements.destroy', $disbursement) }}" method="POST" class="inline" data-confirm="Delete this disbursement">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-danger-600 hover:text-danger-700 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg" aria-label="Delete disbursement">
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="od-empty-sm">No CDF disbursements recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $disbursements->links() }}
    </div>
</div>
@endsection
