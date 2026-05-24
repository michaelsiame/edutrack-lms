@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'icon' => null,
    'iconRight' => false,
    'disabled' => false,
    'type' => 'button',
])

@php
$base = 'inline-flex items-center justify-center font-medium transition-all duration-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed';

$variants = [
    'primary'  => 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 active:bg-primary-800 shadow-sm hover:shadow-md',
    'secondary'=> 'bg-gray-100 text-gray-900 hover:bg-gray-200 focus:ring-gray-500 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600',
    'danger'   => 'bg-danger-600 text-white hover:bg-danger-700 focus:ring-danger-500 active:bg-danger-800 shadow-sm hover:shadow-md',
    'warning'  => 'bg-warning-600 text-white hover:bg-warning-700 focus:ring-warning-500 active:bg-warning-800 shadow-sm hover:shadow-md',
    'success'  => 'bg-success-600 text-white hover:bg-success-700 focus:ring-success-500 active:bg-success-800 shadow-sm hover:shadow-md',
    'ghost'    => 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-500 dark:text-gray-300 dark:hover:bg-gray-800',
    'outline'  => 'bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800',
];

$sizes = [
    'sm' => 'px-3 py-1.5 text-xs gap-1.5',
    'md' => 'px-4 py-2.5 text-sm gap-2',
    'lg' => 'px-6 py-3 text-base gap-2.5',
];

$classes = implode(' ', [$base, $variants[$variant] ?? $variants['primary'], $sizes[$size] ?? $sizes['md'], $attributes->get('class') ?? '']);
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->except(['href','type','disabled'])->merge(['class' => $classes]) }}>
        @if($icon && !$iconRight)
            <i class="fas {{ $icon }} text-[0.85em]"></i>
        @endif
        {{ $slot }}
        @if($icon && $iconRight)
            <i class="fas {{ $icon }} text-[0.85em]"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->except(['href','type','disabled'])->merge(['class' => $classes]) }}>
        @if($icon && !$iconRight)
            <i class="fas {{ $icon }} text-[0.85em]"></i>
        @endif
        {{ $slot }}
        @if($icon && $iconRight)
            <i class="fas {{ $icon }} text-[0.85em]"></i>
        @endif
    </button>
@endif
