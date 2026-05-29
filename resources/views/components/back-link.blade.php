@props(['route', 'routeParams' => [], 'label' => 'Back', 'class' => '', 'variant' => 'default'])

@php
$isOd = $variant === 'od';
$url = is_array($routeParams) && !empty($routeParams) ? route($route, $routeParams) : route($route);
@endphp

@if($isOd)
<a href="{{ $url }}" {{ $attributes->merge(['class' => 'inline-flex items-center text-sm font-medium transition-colors ' . $class]) }} style="color: var(--od-muted);" onmouseover="this.style.color='var(--od-fg)'" onmouseout="this.style.color='var(--od-muted)'">
    <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
    {{ $label }}
</a>
@else
<a href="{{ $url }}" {{ $attributes->merge(['class' => 'inline-flex items-center text-sm font-medium text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors ' . $class]) }}>
    <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
    {{ $label }}
</a>
@endif
