@props(['status', 'size' => 'md', 'pulse' => false])

@php
$status = trim($status ?? '');

$colors = [
    'completed'     => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300',
    'enrolled'      => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300',
    'in progress'   => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300',
    'active'        => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300',
    'pending'       => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-300',
    'failed'        => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300',
    'graded'        => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300',
    'submitted'     => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300',
    'returned'      => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300',
    'late'          => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300',
    'live'          => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300',
    'upcoming'      => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300',
    'not submitted' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'not started'   => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'passed'        => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300',
    'dropped'       => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'expired'       => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'cancelled'     => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'refunded'      => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
];

$key = strtolower($status);
$colorClass = $colors[$key] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';

$sizes = [
    'sm' => 'px-2 py-0.5 text-[10px]',
    'md' => 'px-2.5 py-1 text-xs',
    'lg' => 'px-3 py-1.5 text-sm',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<span class="inline-flex items-center rounded-full font-medium whitespace-nowrap {{ $sizeClass }} {{ $colorClass }}">
    @if($pulse)
        <span class="w-1.5 h-1.5 rounded-full mr-1.5 animate-pulse {{ $key === 'live' ? 'bg-danger-600' : 'bg-current opacity-70' }}"></span>
    @endif
    {{ $status }}
</span>
