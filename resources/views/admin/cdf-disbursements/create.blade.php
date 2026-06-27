@extends('layouts.dashboard')

@section('title','Record CDF Disbursement - Admin')
@section('page_title','Record CDF Disbursement')

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.cdf-disbursements.index') }}" class="od-btn od-btn-secondary od-btn-sm mb-4">&larr; Back to Disbursements</a>

    <div class="od-card p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Record a CDF Disbursement</h3>

        <form action="{{ route('admin.cdf-disbursements.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="od-form-label" for="constituency">Constituency <span class="text-danger-500">*</span></label>
                    <input type="text" name="constituency" id="constituency" value="{{ old('constituency', $preselectedConstituency) }}" required
                        class="od-input @error('constituency') border-danger-500 @enderror" placeholder="e.g. Kalomo Central">
                    @error('constituency')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="od-form-label" for="amount">Amount ({{ setting('currency', 'ZMW') }}) <span class="text-danger-500">*</span></label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required
                        class="od-input @error('amount') border-danger-500 @enderror" placeholder="0.00">
                    @error('amount')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="od-form-label" for="received_date">Received Date <span class="text-danger-500">*</span></label>
                    <input type="date" name="received_date" id="received_date" value="{{ old('received_date', now()->format('Y-m-d')) }}" required
                        class="od-input @error('received_date') border-danger-500 @enderror">
                    @error('received_date')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="od-form-label" for="reference">Reference / Voucher No</label>
                    <input type="text" name="reference" id="reference" value="{{ old('reference') }}"
                        class="od-input @error('reference') border-danger-500 @enderror" placeholder="Transfer or voucher reference">
                    @error('reference')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="od-form-label" for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="od-input @error('notes') border-danger-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.cdf-disbursements.index') }}" class="od-btn od-btn-secondary">Cancel</a>
                <button type="submit" class="od-btn od-btn-primary">Record Disbursement</button>
            </div>
        </form>
    </div>
</div>
@endsection
