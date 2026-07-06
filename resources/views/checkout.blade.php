@extends('layouts.app')

@section('title','Checkout - ' . $course->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
<style>
    [x-cloak] { display: none !important; }
    .pm-option {
        display: flex; flex-direction: column; gap: .35rem;
        padding: .9rem .85rem; border: 1.5px solid var(--od-border);
        border-radius: 14px; background: var(--od-surface); cursor: pointer;
        transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
        text-align: left; position: relative;
    }
    .pm-option:hover { border-color: color-mix(in oklch, var(--od-navy) 40%, var(--od-border)); }
    .pm-option.is-active { border-color: var(--od-navy); background: color-mix(in oklch, var(--od-navy) 5%, var(--od-surface)); box-shadow: 0 0 0 3px color-mix(in oklch, var(--od-navy) 12%, transparent); }
    .pm-option.is-disabled { opacity: .55; cursor: not-allowed; }
    .pm-option.is-disabled:hover { border-color: var(--od-border); }
    .pm-icon { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 15px; }
    .pm-badge { position: absolute; top: .55rem; right: .55rem; font-size: 10px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; padding: 2px 7px; border-radius: 999px; background: var(--od-fg-soft); color: var(--od-muted); }
    .op-pill { display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 999px; }
    .op-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
    .field-input { width: 100%; padding: .75rem 1rem; border: 1.5px solid var(--od-border); border-radius: 12px; font-size: 14px; background: var(--od-surface); color: var(--od-fg); transition: border-color .15s ease; }
    .field-input:focus { outline: none; border-color: var(--od-navy); box-shadow: 0 0 0 3px color-mix(in oklch, var(--od-navy) 12%, transparent); }
    .field-input.is-invalid { border-color: var(--od-danger); }
    @keyframes pm-spin { to { transform: rotate(360deg); } }
    .pm-spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: pm-spin .7s linear infinite; }
</style>
@endpush

@section('content')
<div class="min-h-screen py-8 sm:py-12" style="background: var(--od-bg);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center text-sm font-medium transition-colors" style="color: var(--od-muted);" onmouseover="this.style.color='var(--od-fg)'" onmouseout="this.style.color='var(--od-muted)'">
                <i class="fas fa-arrow-left mr-2"></i>Back to Course
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8" x-data="checkout({{ $balance }}, {{ $minDeposit }}, '{{ old('phone_number', auth()->user()->phone) }}')" x-cloak>
            <!-- Payment Form -->
            <div class="lg:col-span-2">
                <div class="od-card overflow-hidden" style="padding: 0;">
                    <div class="p-6" style="border-bottom: 1px solid var(--od-border);">
                        <h1 class="od-h2">Complete Your Payment</h1>
                        <p class="od-meta mt-1">Secure payment for {{ $course->title }}</p>
                    </div>

                    <div class="p-6">
                        <form action="{{ route('checkout.process', $course) }}" method="POST" @submit="onSubmit($event)">
                            @csrf
                            <input type="hidden" name="payment_method" :value="method">
                            <input type="hidden" name="promotion_id" :value="promotionId">

                            <!-- Payment Method -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium mb-3" style="color: var(--od-fg);">How would you like to pay?</label>
                                <div class="grid grid-cols-3 gap-2.5">
                                    <!-- Mobile Money -->
                                    <button type="button" class="pm-option" :class="{ 'is-active': method === 'mobile_money' }" @click="method = 'mobile_money'">
                                        <span class="pm-icon" style="background: var(--od-green-soft); color: var(--od-green);"><i class="fas fa-mobile-alt"></i></span>
                                        <span class="font-semibold text-sm" style="color: var(--od-fg);">Mobile Money</span>
                                        <span class="text-xs" style="color: var(--od-muted);">MTN · Airtel · Zamtel</span>
                                    </button>
                                    <!-- Card (placeholder) -->
                                    <button type="button" class="pm-option is-disabled" disabled>
                                        <span class="pm-badge">Soon</span>
                                        <span class="pm-icon" style="background: var(--od-fg-soft); color: var(--od-muted);"><i class="fas fa-credit-card"></i></span>
                                        <span class="font-semibold text-sm" style="color: var(--od-fg);">Card</span>
                                        <span class="text-xs" style="color: var(--od-muted);">Visa · Mastercard</span>
                                    </button>
                                    <!-- Bank Transfer -->
                                    <button type="button" class="pm-option" :class="{ 'is-active': method === 'bank_transfer' }" @click="method = 'bank_transfer'">
                                        <span class="pm-icon" style="background: color-mix(in oklch, var(--od-navy) 12%, transparent); color: var(--od-navy);"><i class="fas fa-university"></i></span>
                                        <span class="font-semibold text-sm" style="color: var(--od-fg);">Bank Transfer</span>
                                        <span class="text-xs" style="color: var(--od-muted);">Manual deposit</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Mobile Money panel -->
                            <div class="mb-6" x-show="method === 'mobile_money'" x-transition>
                                <label for="phone_number" class="block text-sm font-medium mb-2" style="color: var(--od-fg);">Mobile Money Number</label>
                                <input type="tel" name="phone_number" id="phone_number" x-model="phone" @input="onPhoneInput"
                                    placeholder="e.g. 097 123 4567"
                                    class="field-input" :class="{ 'is-invalid': phone.length >= 4 && !operator }">
                                <p class="mt-1.5 text-sm" x-show="!phone || phone.length < 4" style="color: var(--od-muted);">
                                    Enter the number you'll approve the payment on. We detect your network automatically.
                                </p>
                                <p class="mt-1.5 text-sm" x-show="phone.length >= 4 && !operator" style="color: var(--od-danger);">
                                    <i class="fas fa-exclamation-circle mr-1"></i>That doesn't look like a valid Zambian mobile number.
                                </p>
                                <div class="mt-2 flex items-center gap-2 text-sm" x-show="operator" x-transition style="color: var(--od-green);">
                                    <span class="op-pill" :style="`background: ${operator?.soft}; color: ${operator?.color};`">
                                        <span class="op-dot"></span><span x-text="operator?.name"></span>
                                    </span>
                                    <span>detected — you'll get an approval prompt on this number.</span>
                                </div>
                            </div>

                            <!-- Bank Transfer panel -->
                            <div class="mb-6" x-show="method === 'bank_transfer'" x-transition>
                                <div class="p-4 rounded-xl text-sm" style="background: var(--od-fg-soft); color: var(--od-fg);">
                                    <p class="font-semibold mb-2"><i class="fas fa-university mr-2" style="color: var(--od-navy);"></i>Manual Bank Transfer</p>
                                    <p style="color: var(--od-muted);">Click below and we'll email you the bank account details and your payment reference. Your course unlocks once we confirm the deposit.</p>
                                </div>
                            </div>

                            <!-- Payment Amount -->
                            <div class="mb-6">
                                <label for="amount" class="block text-sm font-medium mb-2" style="color: var(--od-fg);">Amount to Pay (ZMW)</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-medium od-num" style="color: var(--od-muted);">K</span>
                                    <input type="number" name="amount" id="amount" step="0.01"
                                        :min="minAmount" :max="balanceDue" x-model.number="amount"
                                        class="field-input" :class="{ 'is-invalid': amountError }" style="padding-left: 2rem;" required>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button type="button" class="od-btn od-btn-secondary" style="padding: .3rem .7rem; font-size: 12px;" @click="amount = Number(minAmount.toFixed(2))">
                                        30% deposit · K<span x-text="money(minAmount)"></span>
                                    </button>
                                    <button type="button" class="od-btn od-btn-secondary" style="padding: .3rem .7rem; font-size: 12px;" @click="amount = Number(balanceDue.toFixed(2))">
                                        Pay in full · K<span x-text="money(balanceDue)"></span>
                                    </button>
                                </div>
                                <p class="mt-1.5 text-sm" x-show="amountError" style="color: var(--od-danger);" x-text="amountError"></p>
                                @error('amount')
                                    <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Promo Code -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium mb-2" style="color: var(--od-fg);">Promo Code <span style="color: var(--od-muted); font-weight: 400;">(optional)</span></label>
                                <div class="flex gap-2">
                                    <input type="text" x-model="promoCode" @keydown.enter.prevent="validatePromo()"
                                        class="field-input uppercase" placeholder="Enter code">
                                    <button type="button" @click="validatePromo()" :disabled="promoLoading || !promoCode"
                                        class="od-btn od-btn-secondary" style="white-space: nowrap;"
                                        :class="{ 'opacity-50 cursor-not-allowed': promoLoading || !promoCode }">
                                        <span x-show="!promoLoading">Apply</span>
                                        <span x-show="promoLoading"><i class="fas fa-spinner fa-spin"></i></span>
                                    </button>
                                </div>
                                <template x-if="promoMessage">
                                    <p class="mt-1.5 text-sm" :style="`color: ${promoSuccess ? 'var(--od-green)' : 'var(--od-danger)'}`" x-text="promoMessage"></p>
                                </template>
                            </div>

                            <!-- Submit -->
                            <button type="submit" class="od-btn od-btn-primary od-btn-lg w-full justify-center"
                                :disabled="!canSubmit || submitting"
                                :class="{ 'opacity-60 cursor-not-allowed': !canSubmit || submitting }">
                                <template x-if="submitting">
                                    <span class="flex items-center gap-2"><span class="pm-spinner"></span> Processing…</span>
                                </template>
                                <template x-if="!submitting">
                                    <span>
                                        <span x-show="method === 'mobile_money'">Pay K<span x-text="money(amount)"></span> with Mobile Money</span>
                                        <span x-show="method === 'bank_transfer'">Get Bank Transfer Instructions</span>
                                    </span>
                                </template>
                            </button>
                            <p class="mt-3 text-center text-xs" style="color: var(--od-muted);">
                                <i class="fas fa-lock mr-1"></i>Payments are processed securely. You won't be charged extra fees.
                            </p>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="od-card lg:sticky lg:top-6 overflow-hidden" style="padding: 0;">
                    <div class="p-6" style="border-bottom: 1px solid var(--od-border);">
                        <h2 class="od-h3">Order Summary</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start space-x-4 mb-4">
                            <img src="{{ $course->thumbnail_image_url ?? asset('assets/images/course-placeholder.jpg') }}" alt="{{ $course->title }}"
                                class="w-20 h-20 object-cover rounded-lg">
                            <div>
                                <h3 class="font-medium text-sm" style="color: var(--od-fg);">{{ $course->title }}</h3>
                                <p class="text-sm od-meta">{{ $course->level }}</p>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span style="color: var(--od-muted);">Course Fee</span>
                                <span class="font-medium od-num">K{{ number_format($price, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span style="color: var(--od-muted);">Total Paid</span>
                                <span class="font-medium od-num" style="color: var(--od-green);">K{{ number_format($totalPaid, 2) }}</span>
                            </div>
                            <div class="flex justify-between" x-show="discount > 0" x-transition>
                                <span style="color: var(--od-green);">Promo Discount</span>
                                <span class="font-medium od-num" style="color: var(--od-green);" x-text="'-K' + money(discount)"></span>
                            </div>
                            <div class="flex justify-between pt-3" style="border-top: 1px solid var(--od-border);">
                                <span class="font-semibold" style="color: var(--od-fg);">Balance Due</span>
                                <span class="font-bold od-num" style="color: var(--od-navy);" x-text="'K' + money(balanceDue)"></span>
                            </div>
                            <div class="flex justify-between pt-2">
                                <span class="font-semibold" style="color: var(--od-fg);">You pay now</span>
                                <span class="font-bold od-num od-h3" style="color: var(--od-navy);" x-text="'K' + money(amount)"></span>
                            </div>
                        </div>

                        <div class="mt-6 p-4 rounded-lg" style="background: var(--od-accent-soft); border: 1px solid color-mix(in oklch, var(--od-accent) 20%, transparent);">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle mt-0.5 mr-2" style="color: var(--od-accent);"></i>
                                <div class="text-sm" style="color: color-mix(in oklch, var(--od-accent) 70%, black);">
                                    <p class="font-medium">Payment Rules</p>
                                    <p class="mt-1">
                                        <span class="font-semibold">30% minimum</span> deposit unlocks course content.<br>
                                        <span class="font-semibold">100% payment</span> unlocks certificate download.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function checkout(originalBalance, originalMinDeposit, initialPhone) {
    return {
        // Method
        method: 'mobile_money',

        // Phone / operator
        phone: initialPhone || '',
        operator: null,

        // Amount
        originalBalance: originalBalance,
        balanceDue: originalBalance,
        minDeposit: originalMinDeposit,
        amount: originalBalance,

        // Promo
        promoCode: '',
        promoLoading: false,
        promoMessage: '',
        promoSuccess: false,
        discount: 0,
        promotionId: null,

        // Submit
        submitting: false,

        init() {
            this.detectOperator();
        },

        money(v) {
            return Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        get minAmount() {
            return this.balanceDue > 0 ? Math.min(this.minDeposit, this.balanceDue) : 0;
        },

        get amountError() {
            const v = Number(this.amount);
            if (isNaN(v) || v < this.minAmount) return 'Amount must be at least K' + this.money(this.minAmount) + ' (30% deposit).';
            if (v > this.balanceDue) return 'Amount cannot exceed the balance of K' + this.money(this.balanceDue) + '.';
            return '';
        },

        get canSubmit() {
            if (this.amountError) return false;
            if (this.method === 'mobile_money') return !!this.operator;
            return true;
        },

        onPhoneInput() {
            this.detectOperator();
        },

        detectOperator() {
            let d = (this.phone || '').replace(/\D/g, '');
            if (d.startsWith('0')) d = '260' + d.slice(1);
            if (d.length && !d.startsWith('260')) d = '260' + d;

            // Need at least the 2 network digits after the 260 prefix.
            if (d.length < 5) { this.operator = null; return; }

            const p = d.substr(3, 2);
            const ops = {
                mtn:    { id: 'mtn',    name: 'MTN',    color: '#a8850a', soft: '#fff6d6' },
                airtel: { id: 'airtel', name: 'Airtel', color: '#c40000', soft: '#ffe0e0' },
                zamtel: { id: 'zamtel', name: 'Zamtel', color: '#00794a', soft: '#d6f5e6' },
            };
            if (['76', '96'].includes(p)) this.operator = ops.mtn;
            else if (['57', '77', '97'].includes(p)) this.operator = ops.airtel;
            else if (['75', '95'].includes(p)) this.operator = ops.zamtel;
            else this.operator = null;
        },

        onSubmit(e) {
            if (!this.canSubmit) { e.preventDefault(); return; }
            // Lock the UI so the user gets immediate feedback and can't double-submit.
            this.submitting = true;
        },

        validatePromo() {
            if (!this.promoCode.trim()) return;
            this.promoLoading = true;
            this.promoMessage = '';
            this.promoSuccess = false;
            this.discount = 0;
            this.promotionId = null;

            fetch('{{ route('api.promotions.validate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    code: this.promoCode,
                    course_id: {{ $course->id }},
                    amount: this.originalBalance
                })
            })
            .then(r => r.json())
            .then(data => {
                this.promoLoading = false;
                if (data.valid) {
                    this.promoSuccess = true;
                    this.promoMessage = (data.promotion?.name || 'Promo') + ' applied!';
                    this.discount = data.discount;
                    this.promotionId = data.promotion.id;
                    this.balanceDue = Math.max(0, this.originalBalance - this.discount);
                    this.minDeposit = this.balanceDue * 0.30;
                    if (this.amount > this.balanceDue) this.amount = this.balanceDue;
                } else {
                    this.promoSuccess = false;
                    this.promoMessage = data.message || 'Invalid or expired code.';
                }
            })
            .catch(() => {
                this.promoLoading = false;
                this.promoSuccess = false;
                this.promoMessage = 'Network error. Please try again.';
            });
        }
    };
}
</script>
@endpush
@endsection
