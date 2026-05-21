@props(['value','max' => 100,'size' =>'md','showLabel' => false])
@php
$sizes = ['sm' =>'h-1.5','md' =>'h-2','lg' =>'h-2.5',
];
$percentage = $max > 0 ? min(100, max(0, round(($value / $max) * 100))) : 0;
@endphp
<div class="w-full">
 @if($showLabel)
 <div class="flex items-center justify-between text-xs mb-1">
 <span class="text-gray-500 dark:text-gray-400">Progress</span>
 <span class="font-medium text-gray-700 dark:text-gray-300">{{ $percentage }}%</span>
 </div>
 @endif
 <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full {{ $sizes[$size] ?? $sizes['md'] }}">
 <div class="bg-primary-600 h-full rounded-full transition-all duration-500 ease-out" style="width: {{ $percentage }}%"></div>
 </div>
</div>
