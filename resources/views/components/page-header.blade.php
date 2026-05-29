@props(['title', 'subtitle' => null, 'backRoute' => null, 'backLabel' => 'Back', 'actionHref' => null, 'actionText' => null, 'actionIcon' => null, 'variant' => 'default'])

@php
$isOd = $variant === 'od';
@endphp

<div class="mb-6 md:mb-8">
    @if($backRoute)
        <x-back-link :route="$backRoute" :label="$backLabel" class="mb-3" :variant="$variant" />
    @endif

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            @if($isOd)
                <h1 class="od-h1">{{ $title }}</h1>
            @else
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white leading-tight">
                    {{ $title }}
                </h1>
            @endif
            @if($subtitle)
                @if($isOd)
                    <p class="od-lead mt-1">{{ $subtitle }}</p>
                @else
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
                @endif
            @endif
        </div>

        @if($actionHref && $actionText)
            @if($isOd)
                <a href="{{ $actionHref }}" class="od-btn od-btn-primary od-btn-sm shrink-0">
                    @if($actionIcon)<i class="fas {{ $actionIcon }}"></i>@endif
                    {{ $actionText }}
                </a>
            @else
                <x-button :href="$actionHref" :icon="$actionIcon" size="sm" class="shrink-0">
                    {{ $actionText }}
                </x-button>
            @endif
        @endif
    </div>
</div>
