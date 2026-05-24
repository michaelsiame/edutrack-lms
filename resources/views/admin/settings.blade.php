@extends('layouts.dashboard')

@section('title','Settings - Admin')
@section('page_title','System Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">System Settings</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Configure application-wide settings.</p>
        </div>

        @if(session('success'))
        <div class="bg-success-100 border border-success-400 text-success-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">General</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="app_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">App Name</label>
                        <input type="text" id="app_name" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                    </div>
                    <div>
                        <label for="app_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Email</label>
                        <input type="email" id="app_email" name="app_email" value="{{ old('app_email', $settings['app_email']) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                    </div>
                    <div>
                        <label for="app_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Phone</label>
                        <input type="text" id="app_phone" name="app_phone" value="{{ old('app_phone', $settings['app_phone']) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div class="md:col-span-2">
                        <label for="app_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                        <input type="text" id="app_address" name="app_address" value="{{ old('app_address', $settings['app_address']) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Payment</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                        <input type="text" id="currency" name="currency" value="{{ old('currency', $settings['currency']) }}" maxlength="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                    </div>
                    <div>
                        <label for="min_deposit_percent" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Deposit (%)</label>
                        <input type="number" id="min_deposit_percent" name="min_deposit_percent" value="{{ old('min_deposit_percent', $settings['min_deposit_percent']) }}" min="0" max="100" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                    </div>
                    <div>
                        <label for="registration_fee" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registration Fee</label>
                        <input type="number" id="registration_fee" name="registration_fee" value="{{ old('registration_fee', $settings['registration_fee']) }}" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Certificates</h3>
                <div class="flex items-center">
                    <input type="checkbox" name="certificate_enabled" id="certificate_enabled" value="1" {{ old('certificate_enabled', $settings['certificate_enabled']) ? 'checked' : '' }} class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label for="certificate_enabled" class="ml-2 block text-sm text-gray-900 dark:text-white">Enable certificate generation</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors text-sm font-medium">
                    <i class="fas fa-save mr-2"></i>Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
