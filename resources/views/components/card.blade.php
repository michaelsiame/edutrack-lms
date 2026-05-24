@props(['class' => '', 'hover' => false, 'padding' => true, 'variant' => 'default'])

@php
$variants = [
    'default'     => 'bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm',
    'elevated'    => 'bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-md',
    'bordered'    => 'bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-600 shadow-sm',
    'interactive' => 'bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300',
];

$variantClass = $variants[$variant] ?? $variants['default'];
$hoverClass = ($hover && $variant !== 'interactive') ? 'shadow-card hover:shadow-card-hover transition-all duration-300 hover:-translate-y-0.5' : '';
$paddingClass = $padding ? 'p-5 md:p-6' : '';
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl overflow-hidden ' . $variantClass . ' ' . $hoverClass . ' ' . $paddingClass . ' ' . $class]) }}>
    @if(isset($header))
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                {{ $header }}
            </div>
            @if(isset($headerAction))
                {{ $headerAction }}
            @endif
        </div>
    @endif

    {{ $slot }}

    @if(isset($footer))
        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-700">
            {{ $footer }}
        </div>
    @endif
</div>
