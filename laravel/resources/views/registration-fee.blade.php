@extends('layouts.app')

@section('title', 'Registration Fee - Edutrack LMS')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Registration Fee</h1>
                <p class="mt-1 text-gray-500">A one-time fee required before enrolling in any course</p>
            </div>

            <div class="p-6">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-700">
                        {{ session('warning') }}
                    </div>
                @endif

                @if($hasPaid)
                    <div class="text-center py-8">
                        <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-check text-2xl text-green-600"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Registration Fee Paid</h2>
                        <p class="text-gray-600 mb-6">You have already paid the registration fee. You can now enroll in any course.</p>
                        <a href="{{ route('courses.index') }}" class="inline-block py-3 px-6 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition-colors">
                            Browse Courses
                        </a>
                    </div>
                @else
                    <div class="text-center py-6">
                        <div class="inline-block p-6 bg-primary-50 rounded-xl mb-6">
                            <p class="text-sm text-primary-700 font-medium uppercase tracking-wide">Amount Due</p>
                            <p class="text-5xl font-bold text-primary-600">K150.00</p>
                            <p class="text-sm text-primary-600 mt-1">Zambian Kwacha</p>
                        </div>

                        <p class="text-gray-600 mb-8">This is a mandatory one-time registration fee for all new students at Edutrack Computer Training College. This fee must be paid before you can enroll in any course.</p>
                    </div>

                    <form action="{{ route('registration-fee.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="flex flex-col items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="bank_deposit" class="w-4 h-4 text-primary-600 mb-2" checked>
                                    <i class="fas fa-university text-blue-600 mb-1"></i>
                                    <span class="text-sm font-medium">Bank Deposit</span>
                                </label>
                                <label class="flex flex-col items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="bank_transfer" class="w-4 h-4 text-primary-600 mb-2">
                                    <i class="fas fa-exchange-alt text-green-600 mb-1"></i>
                                    <span class="text-sm font-medium">Bank Transfer</span>
                                </label>
                                <label class="flex flex-col items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="mobile_money" class="w-4 h-4 text-primary-600 mb-2">
                                    <i class="fas fa-mobile-alt text-purple-600 mb-1"></i>
                                    <span class="text-sm font-medium">Mobile Money</span>
                                </label>
                            </div>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                                <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="e.g., Zanaco">
                                @error('bank_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="bank_reference" class="block text-sm font-medium text-gray-700 mb-1">Reference / Slip Number</label>
                                <input type="text" name="bank_reference" id="bank_reference" value="{{ old('bank_reference') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Transaction reference">
                                @error('bank_reference')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="deposit_date" class="block text-sm font-medium text-gray-700 mb-1">Deposit Date</label>
                                <input type="date" name="deposit_date" id="deposit_date" value="{{ old('deposit_date') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @error('deposit_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', auth()->user()->phone) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="+260 XXX XXX XXX">
                                @error('phone_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                            <textarea name="notes" id="notes" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Any additional information...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full py-3 px-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                            Submit Payment Details
                        </button>

                        <p class="text-xs text-gray-500 text-center">
                            Your payment will be verified by our finance team within 24-48 hours. You will be notified via email once approved.
                        </p>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
