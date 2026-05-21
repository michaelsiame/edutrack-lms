@props(['type' =>'success','message' =>'','dismissible' => true])

@php
$icons = ['success' =>'fa-check-circle','error' =>'fa-times-circle','warning' =>'fa-exclamation-triangle','info' =>'fa-info-circle',
];

$colors = ['success' =>'bg-success-50 border-success-400 text-success-700 dark:bg-success-900/20 dark:border-success-600 dark:text-success-300','error' =>'bg-danger-50 border-danger-400 text-danger-700 dark:bg-danger-900/20 dark:border-danger-600 dark:text-danger-300','warning' =>'bg-warning-50 border-warning-400 text-warning-700 dark:bg-warning-900/20 dark:border-warning-600 dark:text-warning-300','info' =>'bg-primary-50 border-primary-400 text-primary-700 dark:bg-primary-900/20 dark:border-primary-600 dark:text-primary-300',
];

$iconColor = ['success' =>'text-success-500','error' =>'text-danger-500','warning' =>'text-warning-500','info' =>'text-primary-500',
];
@endphp

<div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="mb-3">
 <div class="border rounded-lg px-4 py-3 shadow-sm {{ $colors[$type] ?? $colors['info'] }}" role="alert">
 <div class="flex items-start gap-3">
 <i class="fas {{ $icons[$type] ?? $icons['info'] }} mt-0.5 {{ $iconColor[$type] ?? $iconColor['info'] }}"></i>
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
