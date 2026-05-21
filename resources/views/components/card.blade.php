@props(['class' =>'','hover' => false,'padding' => true])
<div {{ $attributes->merge(['class' =>'bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl' . ($hover ?'shadow-card hover:shadow-card-hover transition-all duration-300 hover:-translate-y-0.5' :'shadow-sm') . ($padding ?'p-5 md:p-6' :'') . $class]) }}>
 {{ $slot }}
</div>
