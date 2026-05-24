@props(['icon', 'iconColor' => 'primary', 'value', 'label', 'sublabel' => null, 'trend' => null, 'trendDirection' => null, 'href' => null])

@php
$iconColors = [
    'primary'   => 'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400',
    'success'   => 'bg-success-50 text-success-600 dark:bg-success-900/30 dark:text-success-400',
    'warning'   => 'bg-warning-50 text-warning-600 dark:bg-warning-900/30 dark:text-warning-400',
    'danger'    => 'bg-danger-50 text-danger-600 dark:bg-danger-900/30 dark:text-danger-400',
    'purple'    => 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
    'secondary' => 'bg-secondary-50 text-secondary-600 dark:bg-secondary-900/30 dark:text-secondary-400',
    'gray'      => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
];

$trendColors = [
    'up'    => 'text-success-600 dark:text-success-400',
    'down'  => 'text-danger-600 dark:text-danger-400',
    'neutral' => 'text-gray-500 dark:text-gray-400',
];

$trendIcons = [
    'up'    => 'fa-arrow-up',
    'down'  => 'fa-arrow-down',
    'neutral' => 'fa-minus',
];
@endphp

@if($href)
    <a href="{{ $href }}" class="block group">
@endif

<div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-5 md:p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5 {{ $href ? 'group-hover:border-primary-200 dark:group-hover:border-primary-800' : '' }}">
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $iconColors[$iconColor] ?? $iconColors['primary'] }}">
                <i class="fas {{ $icon }} text-sm"></i>
            </div>
        </div>

        @if($trend && $trendDirection)
            <div class="flex items-center gap-1 text-xs font-medium {{ $trendColors[$trendDirection] ?? $trendColors['neutral'] }}">
                <i class="fas {{ $trendIcons[$trendDirection] ?? $trendIcons['neutral'] }}"></i>
                <span>{{ $trend }}</span>
            </div>
        @endif
    </div>

    <div class="mt-4">
        <div class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $value }}</div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $label }}</div>
        @if($sublabel)
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $sublabel }}</div>
        @endif
    </div>
</div>

@if($href)
    </a>
@endif
