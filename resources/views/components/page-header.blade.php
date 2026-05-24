@props(['title', 'subtitle' => null, 'backRoute' => null, 'backLabel' => 'Back', 'actionHref' => null, 'actionText' => null, 'actionIcon' => null])

<div class="mb-6 md:mb-8">
    @if($backRoute)
        <x-back-link :route="$backRoute" :label="$backLabel" class="mb-3" />
    @endif

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white leading-tight">
                {{ $title }}
            </h1>
            @if($subtitle)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
            @endif
        </div>

        @if($actionHref && $actionText)
            <x-button :href="$actionHref" :icon="$actionIcon" size="sm" class="shrink-0">
                {{ $actionText }}
            </x-button>
        @endif
    </div>
</div>
