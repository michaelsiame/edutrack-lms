@props(['route', 'routeParams' => [], 'label' => 'Back', 'class' => ''])

<a href="{{ is_array($routeParams) && !empty($routeParams) ? route($route, $routeParams) : route($route) }}" {{ $attributes->merge(['class' => 'inline-flex items-center text-sm font-medium text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors ' . $class]) }}>
    <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
    {{ $label }}
</a>
