@php
$isActive = request()->routeIs($route . '*') || request()->routeIs($route);
@endphp
<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ $isActive 
       ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 border-r-3 border-primary-500' 
       : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-200' }}">
    <i class="fas {{ $icon }} w-5 text-center {{ $isActive ? 'text-primary-600 dark:text-primary-400' : '' }}"></i>
    <span>{{ $label }}</span>
    @if($isActive)
        <i class="fas fa-chevron-right ml-auto text-xs text-primary-400"></i>
    @endif
</a>
