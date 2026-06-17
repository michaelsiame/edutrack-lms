@extends('layouts.dashboard')

@section('title','New Promotion - Admin')
@section('page_title','New Promotion')

@section('content')
<div class="max-w-3xl mx-auto">
 <div class="od-card">
 <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
 <h2 class="text-lg font-bold text-gray-900 dark:text-white">Create Promotion</h2>
 </div>

 <form action="{{ route('admin.promotions.store') }}" method="POST" class="p-6 space-y-6">
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

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Promotion Code <span class="text-red-500">*</span></label>
 <input type="text" name="code" value="{{ old('code') }}" required maxlength="50"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm uppercase"
 placeholder="e.g. SUMMER2026">
 <p class="text-xs text-gray-500 mt-1">Unique code students will enter at checkout.</p>
 </div>

 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
 <input type="text" name="name" value="{{ old('name') }}" required maxlength="255"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"
 placeholder="e.g. Summer Intake Discount">
 </div>
 </div>

 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
 <textarea name="description" rows="2" maxlength="1000"
 class="rich-editor w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"
 placeholder="Optional description for internal reference">{{ old('description') }}</textarea>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Type <span class="text-red-500">*</span></label>
 <select name="discount_type" required
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
 <option value="fixed_amount" {{ old('discount_type') === 'fixed_amount' ? 'selected' : '' }}>Fixed Amount (ZMW)</option>
 </select>
 </div>

 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Value <span class="text-red-500">*</span></label>
 <input type="number" name="discount_value" value="{{ old('discount_value') }}" required step="0.01" min="0.01"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"
 placeholder="e.g. 20">
 </div>

 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Uses</label>
 <input type="number" name="max_uses" value="{{ old('max_uses') }}" min="1"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"
 placeholder="Unlimited if empty">
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
 <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <p class="text-xs text-gray-500 mt-1">Leave empty to start immediately.</p>
 </div>

 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
 <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 <p class="text-xs text-gray-500 mt-1">Leave empty for no expiry.</p>
 </div>

 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Order (ZMW)</label>
 <input type="number" name="min_order_amount" value="{{ old('min_order_amount') }}" step="0.01" min="0"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"
 placeholder="No minimum">
 </div>
 </div>

 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Applicable Courses</label>
 <select name="applicable_courses[]" multiple size="5"
 class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
 @foreach($courses as $course)
 <option value="{{ $course->id }}" {{ in_array($course->id, old('applicable_courses', [])) ? 'selected' : '' }}>{{ $course->title }}</option>
 @endforeach
 </select>
 <p class="text-xs text-gray-500 mt-1">Leave unselected to apply to all courses. Hold Ctrl/Cmd to select multiple.</p>
 </div>

 <div class="flex items-center">
 <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}
 class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
 <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
 </div>

 <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
 <button type="submit" class="px-4 py-2 od-btn od-btn-primary text-sm">Create Promotion</button>
 <a href="{{ route('admin.promotions.index') }}" class="px-4 py-2 od-btn od-btn-ghost text-sm">Cancel</a>
 </div>
 </form>
 </div>
</div>
@endsection

@push('scripts')
    @include('partials.rich-editor')
@endpush
