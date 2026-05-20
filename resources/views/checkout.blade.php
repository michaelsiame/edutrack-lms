@extends('layouts.app')

@section('title', 'Checkout - ' . $course->title)

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('courses.show', $course) }}" class="text-primary-600 hover:text-primary-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back to Course
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Payment Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h1 class="text-2xl font-bold text-gray-900">Complete Your Payment</h1>
                        <p class="mt-1 text-gray-500">Secure payment for {{ $course->title }}</p>
                    </div>

                    <div class="p-6">
                        @if(session('info'))
                            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-700">
                                {{ session('info') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('checkout.process', $course) }}" method="POST">
                            @csrf

                            <!-- Payment Amount -->
                            <div class="mb-6">
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Payment Amount (ZMW)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">K</span>
                                    <input type="number" name="amount" id="amount" step="0.01" min="{{ min($minDeposit, $balance) }}" max="{{ $balance }}"
                                        value="{{ old('amount', $balance) }}"
                                        class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                        required>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Minimum deposit: <span class="font-semibold text-primary-600">K{{ number_format($minDeposit, 2) }}</span> (30%)
                                </p>
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                                <div class="space-y-3">
                                    <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="radio" name="payment_method" value="lenco" class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500" checked>
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center">
                                                <i class="fas fa-credit-card text-primary-600 mr-2"></i>
                                                <span class="font-medium text-gray-900">Lenco (Bank Transfer / Card)</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">Pay securely via bank transfer, debit/credit card</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="radio" name="payment_method" value="mobile_money" class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center">
                                                <i class="fas fa-mobile-alt text-green-600 mr-2"></i>
                                                <span class="font-medium text-gray-900">Mobile Money</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">MTN, Airtel, or Zamtel mobile money</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="radio" name="payment_method" value="bank_transfer" class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center">
                                                <i class="fas fa-university text-blue-600 mr-2"></i>
                                                <span class="font-medium text-gray-900">Manual Bank Transfer</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">Deposit at any bank and upload proof</p>
                                        </div>
                                    </label>
                                </div>
                                @error('payment_method')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone Number (for mobile money) -->
                            <div class="mb-6">
                                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number (for mobile money)</label>
                                <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', auth()->user()->phone) }}"
                                    placeholder="+260 XXX XXX XXX"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @error('phone_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="w-full py-3 px-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                                Pay K{{ number_format($balance, 2) }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start space-x-4 mb-4">
                            <img src="{{ $course->thumbnail_url ?? asset('assets/images/course-placeholder.jpg') }}" alt="{{ $course->title }}"
                                class="w-20 h-20 object-cover rounded-lg">
                            <div>
                                <h3 class="font-medium text-gray-900">{{ $course->title }}</h3>
                                <p class="text-sm text-gray-500">{{ $course->level }}</p>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Course Fee</span>
                                <span class="font-medium">K{{ number_format($price, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Paid</span>
                                <span class="font-medium text-green-600">K{{ number_format($totalPaid, 2) }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3 flex justify-between">
                                <span class="font-semibold text-gray-900">Balance Due</span>
                                <span class="font-bold text-primary-600">K{{ number_format($balance, 2) }}</span>
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-amber-500 mt-0.5 mr-2"></i>
                                <div class="text-sm text-amber-800">
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
@endsection
