@extends('layouts.app')

@section('title', 'Registration Fee - Edutrack LMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
<style>
.payment-field-group { transition: opacity 0.2s ease, transform 0.2s ease; }
.payment-field-group.hidden-group { display: none; }
.od-radio-card { position: relative; }
.od-radio-card input:checked + .check-icon { opacity: 1; transform: scale(1); }
.check-icon {
    position: absolute;
    top: 8px; right: 8px;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: var(--od-navy);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.2s ease;
}
</style>
@endpush

@section('content')
<div class="min-h-screen py-12" style="background: var(--od-bg);">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="od-card overflow-hidden" style="padding: 0;">
            <div class="p-6" style="border-bottom: 1px solid var(--od-border);">
                <h1 class="od-h2">Registration Fee</h1>
                <p class="od-meta mt-1">A one-time fee required before enrolling in any course</p>
            </div>

            <div class="p-6">
                @if(session('success'))
                <div class="mb-6 p-4 rounded-lg text-sm font-medium" style="background: var(--od-green-soft); color: var(--od-green);">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 p-4 rounded-lg text-sm font-medium" style="background: color-mix(in oklch, var(--od-danger) 8%, transparent); color: var(--od-danger); border: 1px solid color-mix(in oklch, var(--od-danger) 20%, transparent);">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
                @endif

                @if(session('info'))
                <div class="mb-6 p-4 rounded-lg text-sm font-medium" style="background: var(--od-navy-soft); color: var(--od-navy); border: 1px solid color-mix(in oklch, var(--od-navy) 20%, transparent);">
                    <i class="fas fa-info-circle mr-1"></i> {{ session('info') }}
                </div>
                @endif

                @if(session('warning'))
                <div class="mb-6 p-4 rounded-lg text-sm font-medium" style="background: color-mix(in oklch, var(--od-accent) 10%, transparent); color: color-mix(in oklch, var(--od-accent) 70%, black);">
                    <i class="fas fa-clock mr-1"></i> {{ session('warning') }}
                </div>
                @endif

                {{-- STATE 1: Already Paid --}}
                @if($hasPaid)
                <div class="text-center py-8">
                    <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-4" style="background: var(--od-green-soft);">
                        <i class="fas fa-check text-2xl" style="color: var(--od-green);"></i>
                    </div>
                    <h2 class="od-h3 mb-2">Registration Fee Paid</h2>
                    <p class="od-meta mb-6">You have already paid the registration fee. You can now enroll in any course.</p>
                    <a href="{{ route('courses.index') }}" class="od-btn od-btn-primary od-btn-lg">
                        Browse Courses
                    </a>
                </div>

                {{-- STATE 2: Pending Lenco Payment (automated) --}}
                @elseif($fee && in_array($fee->payment_method, ['mobile_money', 'bank_transfer']) && $fee->payment_status === 'pending')
                <div class="text-center py-6">
                    <div class="inline-block p-6 rounded-xl mb-6" style="background: var(--od-accent-soft); border: 1px solid color-mix(in oklch, var(--od-accent) 20%, transparent);">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background: var(--od-accent);">
                            <i class="fas fa-clock text-xl" style="color: var(--od-fg);"></i>
                        </div>
                        <p class="od-eyebrow mb-1">Payment In Progress</p>
                        <p class="od-h2" style="color: var(--od-fg);">{{ setting('currency', 'ZMW') }} {{ number_format($fee->amount, 2) }}</p>
                        <p class="od-meta mt-1">{{ ucfirst(str_replace('_', ' ', $fee->payment_method)) }}</p>
                    </div>

                    <p class="od-meta mb-6">Your payment is being processed. If you were not redirected to complete the payment, or if you have already completed it on your phone, click the button below to verify.</p>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('registration-fee.check') }}" class="od-btn od-btn-primary od-btn-lg">
                            <i class="fas fa-sync-alt mr-2"></i> Check Payment Status
                        </a>
                    </div>
                </div>

                {{-- STATE 3: Pending Manual Bank Deposit --}}
                @elseif($fee && $fee->payment_method === 'bank_deposit' && $fee->payment_status === 'pending')
                <div class="text-center py-6">
                    <div class="inline-block p-6 rounded-xl mb-6" style="background: var(--od-navy-soft);">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background: var(--od-navy);">
                            <i class="fas fa-university text-xl" style="color: var(--od-surface);"></i>
                        </div>
                        <p class="od-eyebrow mb-1" style="color: var(--od-navy);">Submitted for Verification</p>
                        <p class="od-h2" style="color: var(--od-navy);">{{ setting('currency', 'ZMW') }} {{ number_format($fee->amount, 2) }}</p>
                        <p class="od-meta mt-1">Bank Deposit</p>
                    </div>

                    <div class="text-left max-w-md mx-auto p-4 rounded-lg mb-6" style="background: var(--od-bg); border: 1px solid var(--od-border);">
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <span style="color: var(--od-muted);">Bank:</span>
                            <span style="color: var(--od-fg);">{{ $fee->bank_name ?: 'N/A' }}</span>
                            <span style="color: var(--od-muted);">Reference:</span>
                            <span style="color: var(--od-fg);">{{ $fee->bank_reference }}</span>
                            <span style="color: var(--od-muted);">Date:</span>
                            <span style="color: var(--od-fg);">{{ $fee->deposit_date?->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <p class="od-meta">Our finance team is verifying your deposit. You will be notified via email once approved (usually within 24-48 hours).</p>
                </div>

                {{-- STATE 4: Not Paid -- Show Payment Form --}}
                @else
                <div class="text-center py-6">
                    <div class="inline-block p-6 rounded-xl mb-6" style="background: var(--od-navy-soft);">
                        <p class="od-eyebrow mb-1">Amount Due</p>
                        <p class="od-h1" style="color: var(--od-navy);">{{ setting('currency', 'ZMW') }} {{ number_format($feeAmount, 2) }}</p>
                        <p class="od-meta mt-1">{{ setting('currency', 'ZMW') === 'ZMW' ? 'Zambian Kwacha' : setting('currency', 'ZMW') }}</p>
                    </div>
                    <p class="od-meta mb-8">This is a mandatory one-time registration fee for all new students at Edutrack Computer Training College.</p>
                </div>

                <form action="{{ route('registration-fee.store') }}" method="POST" class="space-y-6" id="registration-fee-form">
                    @csrf

                    {{-- Payment Method --}}
                    <div>
                        <label class="od-form-label mb-3 block">Choose Payment Method</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Mobile Money --}}
                            <label class="od-radio-card" id="card-mobile" onclick="updatePaymentForm()">
                                <input type="radio" name="payment_method" value="mobile_money" class="sr-only" {{ old('payment_method', 'mobile_money') === 'mobile_money' ? 'checked' : '' }}>
                                <div class="check-icon"><i class="fas fa-check"></i></div>
                                <i class="fas fa-mobile-alt text-xl" style="color: var(--od-green);"></i>
                                <span class="font-medium">Mobile Money</span>
                                <span class="text-xs" style="color: var(--od-muted);">MTN &amp; Airtel only</span>
                            </label>

                            {{-- Bank Transfer --}}
                            <label class="od-radio-card" id="card-transfer" onclick="updatePaymentForm()">
                                <input type="radio" name="payment_method" value="bank_transfer" class="sr-only" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}>
                                <div class="check-icon"><i class="fas fa-check"></i></div>
                                <i class="fas fa-exchange-alt text-xl" style="color: var(--od-accent);"></i>
                                <span class="font-medium">Bank Transfer</span>
                                <span class="text-xs" style="color: var(--od-muted);">Online</span>
                            </label>

                            {{-- Bank Deposit --}}
                            <label class="od-radio-card" id="card-deposit" onclick="updatePaymentForm()">
                                <input type="radio" name="payment_method" value="bank_deposit" class="sr-only" {{ old('payment_method') === 'bank_deposit' ? 'checked' : '' }}>
                                <div class="check-icon"><i class="fas fa-check"></i></div>
                                <i class="fas fa-university text-xl" style="color: var(--od-navy);"></i>
                                <span class="font-medium">Bank Deposit</span>
                                <span class="text-xs" style="color: var(--od-muted);">In-branch</span>
                            </label>
                        </div>
                        @error('payment_method')
                        <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Automated Payment Fields (Mobile Money + Bank Transfer) --}}
                    <div id="fields-automated" class="payment-field-group space-y-4">
                        <div class="p-4 rounded-lg" style="background: var(--od-green-soft); border: 1px solid color-mix(in oklch, var(--od-green) 20%, transparent);">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-bolt mt-0.5" style="color: var(--od-green);"></i>
                                <div class="text-sm" style="color: color-mix(in oklch, var(--od-green) 70%, black);">
                                    <p class="font-medium">Automated Payment</p>
                                    <p class="mt-1">You will be redirected to our secure payment partner to complete the transaction. Once confirmed, your registration will be activated instantly.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="phone_number_auto" class="od-form-label">Phone Number <span style="color: var(--od-muted);">(optional)</span></label>
                            <input type="tel" name="phone_number" id="phone_number_auto" value="{{ old('phone_number', auth()->user()->phone) }}"
                                class="od-input" placeholder="+260 XXX XXX XXX">
                            <p class="text-xs mt-1" style="color: var(--od-muted);">Used for mobile money notifications</p>
                            @error('phone_number')
                            <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" id="pay-button" class="od-btn od-btn-primary w-full od-btn-lg"
                            onclick="this.disabled = true; this.innerHTML = '<i class=\'fas fa-spinner fa-spin mr-2\'></i> Processing...';">
                            <i class="fas fa-lock mr-2"></i> Pay {{ setting('currency', 'ZMW') }} {{ number_format($feeAmount, 2) }}
                        </button>
                    </div>

                    {{-- Manual Bank Deposit Fields --}}
                    <div id="fields-manual" class="payment-field-group hidden-group space-y-4">
                        <div class="p-4 rounded-lg" style="background: var(--od-navy-soft); border: 1px solid color-mix(in oklch, var(--od-navy) 20%, transparent);">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-info-circle mt-0.5" style="color: var(--od-navy);"></i>
                                <div class="text-sm" style="color: color-mix(in oklch, var(--od-navy) 70%, black);">
                                    <p class="font-medium">Bank Deposit Instructions</p>
                                    <p class="mt-1">Deposit <strong>{{ setting('currency', 'ZMW') }} {{ number_format($feeAmount, 2) }}</strong> at any branch:</p>
                                    <p class="mt-1">Bank: <strong>{{ setting('bank_name', 'Not configured') }}</strong></p>
                                    <p>Account: <strong>{{ setting('bank_account_name', 'Not configured') }}</strong></p>
                                    <p>A/C No: <strong>{{ setting('bank_account_number', 'Not configured') }}</strong></p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="bank_name" class="od-form-label">Bank Name <span style="color: var(--od-muted);">(optional)</span></label>
                                <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}"
                                    class="od-input" placeholder="e.g., Zanaco">
                                @error('bank_name')
                                <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="bank_reference" class="od-form-label">Reference / Slip Number <span style="color: var(--od-danger);">*</span></label>
                                <input type="text" name="bank_reference" id="bank_reference" value="{{ old('bank_reference') }}"
                                    class="od-input" placeholder="Transaction reference">
                                @error('bank_reference')
                                <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="deposit_date" class="od-form-label">Deposit Date <span style="color: var(--od-danger);">*</span></label>
                                <input type="date" name="deposit_date" id="deposit_date" value="{{ old('deposit_date') }}"
                                    class="od-input">
                                @error('deposit_date')
                                <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="phone_number_manual" class="od-form-label">Phone Number <span style="color: var(--od-muted);">(optional)</span></label>
                                <input type="tel" name="phone_number" id="phone_number_manual" value="{{ old('phone_number', auth()->user()->phone) }}"
                                    class="od-input" placeholder="+260 XXX XXX XXX">
                                @error('phone_number')
                                <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="od-form-label">Additional Notes <span style="color: var(--od-muted);">(optional)</span></label>
                            <textarea name="notes" id="notes" rows="3"
                                class="od-input" placeholder="Any additional information...">{{ old('notes') }}</textarea>
                            @error('notes')
                            <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" id="submit-button" class="od-btn od-btn-primary w-full od-btn-lg"
                            onclick="this.disabled = true; this.innerHTML = '<i class=\'fas fa-spinner fa-spin mr-2\'></i> Submitting...';">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Deposit Details
                        </button>

                        <p class="text-xs text-center od-meta">
                            Your payment will be verified by our finance team within 24-48 hours.
                        </p>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updatePaymentForm() {
    const method = document.querySelector('input[name="payment_method"]:checked').value;
    const automatedFields = document.getElementById('fields-automated');
    const manualFields = document.getElementById('fields-manual');

    if (method === 'bank_deposit') {
        automatedFields.classList.add('hidden-group');
        manualFields.classList.remove('hidden-group');

        // Require manual fields
        document.getElementById('bank_reference').required = true;
        document.getElementById('deposit_date').required = true;
    } else {
        automatedFields.classList.remove('hidden-group');
        manualFields.classList.add('hidden-group');

        // Don't require manual fields
        document.getElementById('bank_reference').required = false;
        document.getElementById('deposit_date').required = false;
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', updatePaymentForm);
</script>
@endpush
@endsection
