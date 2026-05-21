@props(['icon','iconColor' =>'primary','value','label','href' => null])
@php
$iconColors = ['primary' =>'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400','success' =>'bg-success-50 text-success-600 dark:bg-success-900/30 dark:text-success-400','warning' =>'bg-warning-50 text-warning-600 dark:bg-warning-900/30 dark:text-warning-400','danger' =>'bg-danger-50 text-danger-600 dark:bg-danger-900/30 dark:text-danger-400','purple' =>'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400','secondary' =>'bg-secondary-50 text-secondary-600 dark:bg-secondary-900/30 dark:text-secondary-400',
];
@endphp

@if($href)
 <a href="{{ $href }}" class="block">
@endif
<div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-5 md:p-6 shadow-card hover:shadow-card-hover transition-all duration-300 hover:-translate-y-0.5">
 <div class="flex items-center gap-3 mb-3">
 <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $iconColors[$iconColor] ?? $iconColors['primary'] }}">
 <i class="fas {{ $icon }} text-sm"></i>
 </div>
 </div>
 <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $value }}</div>
 <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $label }}</div>
</div>
@if($href)
 </a>
@endif
