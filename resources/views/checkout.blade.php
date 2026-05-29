@extends('layouts.app')

@section('title','Checkout -' . $course->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="min-h-screen py-12" style="background: var(--od-bg);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center text-sm font-medium transition-colors" style="color: var(--od-muted);" onmouseover="this.style.color='var(--od-fg)'" onmouseout="this.style.color='var(--od-muted)'">
                <i class="fas fa-arrow-left mr-2"></i>Back to Course
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="checkout({{ $balance }}, {{ $minDeposit }})">
            <!-- Payment Form -->
            <div class="lg:col-span-2">
                <div class="od-card overflow-hidden" style="padding: 0;">
                    <div class="p-6" style="border-bottom: 1px solid var(--od-border);">
                        <h1 class="od-h2">Complete Your Payment</h1>
                        <p class="od-meta mt-1">Secure payment for {{ $course->title }}</p>
                    </div>

                    <div class="p-6">
                        <form action="{{ route('checkout.process', $course) }}" method="POST">
                            @csrf

                            <!-- Promo Code -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium mb-2" style="color: var(--od-fg);">Promo Code</label>
                                <div class="flex gap-2">
                                    <input type="text" x-model="promoCode" @keydown.enter.prevent="validatePromo()"
                                        class="flex-1 px-4 py-3 border rounded-lg text-sm uppercase"
                                        style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);"
                                        placeholder="Enter promo code">
                                    <button type="button" @click="validatePromo()" :disabled="promoLoading || !promoCode"
                                        class="px-4 py-2 od-btn od-btn-secondary text-sm"
                                        :class="{ 'opacity-50 cursor-not-allowed': promoLoading || !promoCode }">
                                        <span x-show="!promoLoading">Apply</span>
                                        <span x-show="promoLoading"><i class="fas fa-spinner fa-spin"></i></span>
                                    </button>
                                </div>
                                <template x-if="promoMessage">
                                    <p class="mt-1 text-sm" :style="`color: ${promoSuccess ? 'var(--od-green)' : 'var(--od-danger)'}`" x-text="promoMessage"></p>
                                </template>
                                <template x-if="discount > 0">
                                    <div class="mt-2 p-3 rounded-lg" style="background: var(--od-green-soft);">
                                        <div class="flex justify-between text-sm">
                                            <span style="color: var(--od-green);">Discount applied</span>
                                            <span class="font-bold od-num" style="color: var(--od-green);" x-text="'K' + discount.toFixed(2)"></span>
                                        </div>
                                    </div>
                                </template>
                                <input type="hidden" name="promotion_id" :value="promotionId">
                            </div>

                            <!-- Payment Amount -->
                            <div class="mb-6">
                                <label for="amount" class="block text-sm font-medium mb-2" style="color: var(--od-fg);">Payment Amount (ZMW)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 font-medium od-num" style="color: var(--od-muted);">K</span>
                                    <input type="number" name="amount" id="amount" step="0.01"
                                        :min="minAmount" :max="balanceDue"
                                        :value="amountValue"
                                        class="w-full pl-8 pr-4 py-3 border rounded-lg text-sm"
                                        style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);"
                                        required>
                                </div>
                                <p class="mt-1 text-sm" style="color: var(--od-muted);">
                                    Minimum deposit: <span class="font-semibold od-num" style="color: var(--od-navy);" x-text="'K' + minDeposit.toFixed(2)"></span> (30%)
                                </p>
                                <p id="amount-error" class="mt-1 text-sm hidden" style="color: var(--od-danger);"
                                    x-text="'Amount must be at least K' + minAmount.toFixed(2)">
                                </p>
                                @error('amount')
                                    <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium mb-3" style="color: var(--od-fg);">Payment Method</label>
                                <div class="space-y-3">
                                    <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors" style="border-color: var(--od-border); background: var(--od-surface);" onmouseover="this.style.background='var(--od-fg-soft)'" onmouseout="this.style.background='var(--od-surface)'">
                                        <input type="radio" name="payment_method" value="mobile_money" class="w-4 h-4" style="accent-color: var(--od-navy);" checked>
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center">
                                                <i class="fas fa-mobile-alt mr-2" style="color: var(--od-green);"></i>
                                                <span class="font-medium text-sm" style="color: var(--od-fg);">Mobile Money</span>
                                            </div>
                                            <p class="text-sm mt-1" style="color: var(--od-muted);">MTN or Airtel mobile money — fastest option</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors" style="border-color: var(--od-border); background: var(--od-surface);" onmouseover="this.style.background='var(--od-fg-soft)'" onmouseout="this.style.background='var(--od-surface)'">
                                        <input type="radio" name="payment_method" value="lenco" class="w-4 h-4" style="accent-color: var(--od-navy);">
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center">
                                                <i class="fas fa-credit-card mr-2" style="color: var(--od-navy);"></i>
                                                <span class="font-medium text-sm" style="color: var(--od-fg);">Lenco (Bank Transfer / Card)</span>
                                            </div>
                                            <p class="text-sm mt-1" style="color: var(--od-muted);">Pay securely via bank transfer, debit/credit card</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors" style="border-color: var(--od-border); background: var(--od-surface);" onmouseover="this.style.background='var(--od-fg-soft)'" onmouseout="this.style.background='var(--od-surface)'">
                                        <input type="radio" name="payment_method" value="bank_transfer" class="w-4 h-4" style="accent-color: var(--od-navy);">
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center">
                                                <i class="fas fa-university mr-2" style="color: var(--od-navy);"></i>
                                                <span class="font-medium text-sm" style="color: var(--od-fg);">Manual Bank Transfer</span>
                                            </div>
                                            <p class="text-sm mt-1" style="color: var(--od-muted);">Deposit at any bank and upload proof</p>
                                        </div>
                                    </label>
                                </div>
                                @error('payment_method')
                                    <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="mb-6">
                                <label for="phone_number" class="block text-sm font-medium mb-2" style="color: var(--od-fg);">Phone Number (for mobile money)</label>
                                <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', auth()->user()->phone) }}"
                                    placeholder="+260 XXX XXX XXX"
                                    class="w-full px-4 py-3 border rounded-lg text-sm"
                                    style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);">
                                @error('phone_number')
                                    <p class="mt-1 text-sm" style="color: var(--od-danger);">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" id="pay-button" class="od-btn od-btn-primary od-btn-lg w-full justify-center"
                                x-on:click="document.getElementById('pay-button').disabled = true; document.getElementById('pay-button').textContent = 'Processing...';">
                                Pay K<span x-text="amountValue.toFixed(2)"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="od-card sticky top-6 overflow-hidden" style="padding: 0;">
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
                            <div id="promo-discount-row" class="flex justify-between hidden">
                                <span style="color: var(--od-green);">Promo Discount</span>
                                <span class="font-medium od-num" style="color: var(--od-green);" id="promo-discount-amount">-K0.00</span>
                            </div>
                            <div class="flex justify-between pt-3" style="border-top: 1px solid var(--od-border);">
                                <span class="font-semibold" style="color: var(--od-fg);">Balance Due</span>
                                <span class="font-bold od-num" style="color: var(--od-navy);" x-text="'K' + balanceDue.toFixed(2)"></span>
                            </div>
                        </div>

                        <div class="mt-6 p-4 rounded-lg" style="background: var(--od-accent-soft); border: 1px solid color-mix(in oklch, var(--od-accent) 20%, transparent);">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle mt-0.5 mr-2" style="color: var(--od-accent);"></i>
                                <div class="text-sm" style="color: color-mix(in oklch, var(--od-accent) 70%, black);">
                                    <p class="font-medium">Payment Rules</p>
                                    <p class="mt-1">
                                        <span class="font-semibold">30% minimum</span> deposit unlocks course content access.<br>
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
function checkout(originalBalance, originalMinDeposit) {
    return {
        // Promo state
        promoCode: '',
        promoLoading: false,
        promoMessage: '',
        promoSuccess: false,
        discount: 0,
        promotionId: null,

        // Amount state
        originalBalance: originalBalance,
        balanceDue: originalBalance,
        minDeposit: originalMinDeposit,
        get minAmount() {
            return this.balanceDue > 0 ? Math.min(this.minDeposit, this.balanceDue) : 0;
        },
        get amountValue() {
            return this.balanceDue;
        },

        // Validate promo via API
        validatePromo() {
            if (!this.promoCode.trim()) return;
            this.promoLoading = true;
            this.promoMessage = '';
            this.promoSuccess = false;

            // Reset previous discount
            this.discount = 0;
            this.promotionId = null;
            document.getElementById('promo-discount-row').classList.add('hidden');

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
                    this.promoMessage = data.promotion.name + ' applied!';
                    this.discount = data.discount;
                    this.promotionId = data.promotion.id;

                    // Update amounts
                    this.balanceDue = Math.max(0, this.originalBalance - this.discount);
                    this.minDeposit = this.balanceDue * 0.30;

                    // Update UI
                    document.getElementById('promo-discount-row').classList.remove('hidden');
                    document.getElementById('promo-discount-amount').textContent = '-K' + this.discount.toFixed(2);
                } else {
                    this.promoSuccess = false;
                    this.promoMessage = data.message || 'Invalid code.';
                }
            })
            .catch(() => {
                this.promoLoading = false;
                this.promoSuccess = false;
                this.promoMessage = 'Network error. Please try again.';
            });
        },

        // Amount validation
        validateAmount() {
            const input = document.getElementById('amount');
            const error = document.getElementById('amount-error');
            const btn = document.getElementById('pay-button');
            const val = parseFloat(input.value);

            if (isNaN(val) || val < this.minAmount) {
                input.style.borderColor = 'var(--od-danger)';
                error.classList.remove('hidden');
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                return false;
            } else if (val > this.balanceDue) {
                input.style.borderColor = 'var(--od-danger)';
                error.textContent = 'Amount cannot exceed K' + this.balanceDue.toFixed(2);
                error.classList.remove('hidden');
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                return false;
            } else {
                input.style.borderColor = 'var(--od-border)';
                error.classList.add('hidden');
                error.textContent = 'Amount must be at least K' + this.minAmount.toFixed(2);
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                return true;
            }
        },

        init() {
            const input = document.getElementById('amount');
            input.addEventListener('input', () => this.validateAmount());
            input.addEventListener('blur', () => this.validateAmount());
            input.closest('form').addEventListener('submit', (e) => {
                if (!this.validateAmount()) {
                    e.preventDefault();
                    input.focus();
                }
            });
        }
    };
}
</script>
@endpush
@endsection
