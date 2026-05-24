@props(['value', 'max' => 100, 'size' => 'md', 'showLabel' => false, 'color' => 'primary'])

@php
$sizes = ['sm' => 'h-1.5', 'md' => 'h-2', 'lg' => 'h-2.5'];
$percentage = $max > 0 ? min(100, max(0, round(($value / $max) * 100))) : 0;

$colors = [
    'primary' => 'bg-primary-600',
    'success' => 'bg-success-500',
    'warning' => 'bg-warning-500',
    'danger'  => 'bg-danger-500',
];
$colorClass = $colors[$color] ?? $colors['primary'];
@endphp

<div class="w-full">
    @if($showLabel)
        <div class="flex items-center justify-between text-xs mb-1.5">
            <span class="text-gray-500 dark:text-gray-400">Progress</span>
            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $percentage }}%</span>
        </div>
    @endif
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden {{ $sizes[$size] ?? $sizes['md'] }}"
         role="progressbar"
         aria-valuenow="{{ $percentage }}"
         aria-valuemin="0"
         aria-valuemax="100">
        <div class="h-full rounded-full transition-all duration-700 ease-out {{ $colorClass }}"
             style="width: {{ $percentage }}%"></div>
    </div>
    <span class="sr-only">{{ $percentage }}% complete</span>
</div>
