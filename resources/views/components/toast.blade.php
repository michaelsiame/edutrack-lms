@props(['type' => 'success', 'message' => '', 'dismissible' => true])

@php
$icons = [
    'success' => 'fa-check-circle',
    'error' => 'fa-times-circle',
    'warning' => 'fa-exclamation-triangle',
    'info' => 'fa-info-circle',
];

$classes = [
    'success' => 'od-toast-success',
    'error' => 'od-toast-error',
    'warning' => 'od-toast-warning',
    'info' => 'od-toast-info',
];
@endphp

<div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="mb-3">
    <div class="{{ $classes[$type] ?? $classes['info'] }}" role="alert">
        <div class="flex items-start gap-3">
            <i class="fas {{ $icons[$type] ?? $icons['info'] }} mt-0.5"></i>
            <div class="flex-1 text-sm">
                {{ $message }}
            </div>
            @if($dismissible)
            <button @click="show = false" class="ml-auto -mr-1 p-1 rounded hover:bg-black/5 dark:hover:bg-white/10 transition-colors" aria-label="Dismiss">
                <i class="fas fa-times text-xs opacity-60"></i>
            </button>
            @endif
        </div>
    </div>
</div>
