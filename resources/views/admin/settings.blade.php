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

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
            @csrf

            @if($errors->any())
            <div class="p-4 od-toast-error border rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- General -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">General</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="app_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">App Name</label>
                        <input type="text" id="app_name" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                        @error('app_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="app_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Email</label>
                        <input type="email" id="app_email" name="app_email" value="{{ old('app_email', $settings['app_email']) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                        @error('app_email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="app_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Phone</label>
                        <input type="text" id="app_phone" name="app_phone" value="{{ old('app_phone', $settings['app_phone']) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('app_phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="app_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                        <input type="text" id="app_address" name="app_address" value="{{ old('app_address', $settings['app_address']) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('app_address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="logo_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Logo URL</label>
                        <input type="url" id="logo_url" name="logo_url" value="{{ old('logo_url', $settings['logo_url'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="https://example.com/logo.png">
                        @error('logo_url')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Social Links -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Social Links</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="social_facebook" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facebook</label>
                        <input type="url" id="social_facebook" name="social_facebook" value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="https://facebook.com/...">
                    </div>
                    <div>
                        <label for="social_twitter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Twitter / X</label>
                        <input type="url" id="social_twitter" name="social_twitter" value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="https://twitter.com/...">
                    </div>
                    <div>
                        <label for="social_linkedin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">LinkedIn</label>
                        <input type="url" id="social_linkedin" name="social_linkedin" value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="https://linkedin.com/...">
                    </div>
                    <div>
                        <label for="social_instagram" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Instagram</label>
                        <input type="url" id="social_instagram" name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="https://instagram.com/...">
                    </div>
                    <div>
                        <label for="social_youtube" class="block text-sm font-medium text-gray-700 dark:text-gray-300">YouTube</label>
                        <input type="url" id="social_youtube" name="social_youtube" value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="https://youtube.com/...">
                    </div>
                    <div>
                        <label for="social_whatsapp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp Group Link</label>
                        <input type="url" id="social_whatsapp" name="social_whatsapp" value="{{ old('social_whatsapp', $settings['social_whatsapp'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="https://chat.whatsapp.com/...">
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">SEO & Meta</h3>
                <div class="space-y-4">
                    <div>
                        <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" rows="2" maxlength="500" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $settings['meta_keywords'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="computer training, Zambia, courses, certification">
                    </div>
                    <div>
                        <label for="footer_about" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Footer About Text</label>
                        <textarea id="footer_about" name="footer_about" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">{{ old('footer_about', $settings['footer_about'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Homepage -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Homepage</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="hero_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hero Title</label>
                        <input type="text" id="hero_title" name="hero_title" value="{{ old('hero_title', $settings['hero_title'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                    </div>
                    <div>
                        <label for="hero_subtitle" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hero Subtitle</label>
                        <input type="text" id="hero_subtitle" name="hero_subtitle" value="{{ old('hero_subtitle', $settings['hero_subtitle'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                    </div>
                    <div>
                        <label for="next_intake_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Next Intake Date</label>
                        <input type="date" id="next_intake_date" name="next_intake_date" value="{{ old('next_intake_date', $settings['next_intake_date'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                    </div>
                    <div>
                        <label for="next_intake_label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Next Intake Label</label>
                        <input type="text" id="next_intake_label" name="next_intake_label" value="{{ old('next_intake_label', $settings['next_intake_label'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="e.g. May 2026 Intake">
                    </div>
                    <div class="md:col-span-2">
                        <label for="opening_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Opening Hours</label>
                        <input type="text" id="opening_hours" name="opening_hours" value="{{ old('opening_hours', $settings['opening_hours'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="e.g. Monday - Friday: 8:00 AM - 5:00 PM">
                    </div>
                </div>
            </div>

            <!-- Payment -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Payment</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                        <input type="text" id="currency" name="currency" value="{{ old('currency', $settings['currency']) }}" maxlength="10" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                        @error('currency')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="min_deposit_percent" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Deposit (%)</label>
                        <input type="number" id="min_deposit_percent" name="min_deposit_percent" value="{{ old('min_deposit_percent', $settings['min_deposit_percent']) }}" min="0" max="100" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                        @error('min_deposit_percent')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="registration_fee" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registration Fee</label>
                        <input type="number" id="registration_fee" name="registration_fee" value="{{ old('registration_fee', $settings['registration_fee']) }}" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                        @error('registration_fee')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', $settings['bank_name'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                    </div>
                    <div>
                        <label for="bank_account_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Name</label>
                        <input type="text" id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name', $settings['bank_account_name'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                    </div>
                    <div>
                        <label for="bank_account_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Number</label>
                        <input type="text" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $settings['bank_account_number'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                    </div>
                </div>
            </div>

            <!-- Certificates -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Certificates</h3>
                <div class="flex items-center">
                    <input type="checkbox" name="certificate_enabled" id="certificate_enabled" value="1" {{ old('certificate_enabled', $settings['certificate_enabled']) ? 'checked' : '' }} class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label for="certificate_enabled" class="ml-2 block text-sm text-gray-900 dark:text-white">Enable certificate generation</label>
                </div>
                <div class="mt-4">
                    <label for="teveta_registration_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">TEVETA Registration Number</label>
                    <input type="text" id="teveta_registration_number" name="teveta_registration_number" value="{{ old('teveta_registration_number', $settings['teveta_registration_number'] ?? '') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="e.g. TVA/2064">
                </div>
            </div>

            <!-- Feature Toggles -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Feature Toggles</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="maintenance_mode" class="ml-2 block text-sm text-gray-900 dark:text-white">Maintenance Mode</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="enable_email_notifications" id="enable_email_notifications" value="1" {{ old('enable_email_notifications', $settings['enable_email_notifications'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="enable_email_notifications" class="ml-2 block text-sm text-gray-900 dark:text-white">Email Notifications</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="google_login_enabled" id="google_login_enabled" value="1" {{ old('google_login_enabled', $settings['google_login_enabled'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="google_login_enabled" class="ml-2 block text-sm text-gray-900 dark:text-white">Google Login</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="lenco_enabled" id="lenco_enabled" value="1" {{ old('lenco_enabled', $settings['lenco_enabled'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="lenco_enabled" class="ml-2 block text-sm text-gray-900 dark:text-white">Lenco Payments</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="registration_fee_required" id="registration_fee_required" value="1" {{ old('registration_fee_required', $settings['registration_fee_required'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="registration_fee_required" class="ml-2 block text-sm text-gray-900 dark:text-white">Registration Fee Required</label>
                    </div>
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
