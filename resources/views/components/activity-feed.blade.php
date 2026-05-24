@props(['items' => []])

@if(count($items))
    <div class="flow-root">
        <ul class="-mb-2">
            @foreach($items as $item)
                @php
                    $icon = $item['icon'] ?? 'fa-circle';
                    $iconColor = $item['iconColor'] ?? 'primary';
                    $colors = [
                        'primary'  => 'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400',
                        'success'  => 'bg-success-50 text-success-600 dark:bg-success-900/30 dark:text-success-400',
                        'warning'  => 'bg-warning-50 text-warning-600 dark:bg-warning-900/30 dark:text-warning-400',
                        'danger'   => 'bg-danger-50 text-danger-600 dark:bg-danger-900/30 dark:text-danger-400',
                        'secondary'=> 'bg-secondary-50 text-secondary-600 dark:bg-secondary-900/30 dark:text-secondary-400',
                    ];
                @endphp
                <li>
                    <div class="relative pb-4">
                        @if(!$loop->last)
                            <span class="absolute top-5 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                        @endif
                        <div class="relative flex items-start gap-3">
                            <div class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $colors[$iconColor] ?? $colors['primary'] }}">
                                <i class="fas {{ $icon }} text-xs"></i>
                            </div>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <p class="text-sm text-gray-800 dark:text-gray-200">
                                    @if(isset($item['url']))
                                        <a href="{{ $item['url'] }}" class="font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 hover:underline">
                                            {{ $item['title'] ?? '' }}
                                        </a>
                                    @else
                                        <span class="font-medium">{{ $item['title'] ?? '' }}</span>
                                    @endif
                                    @if(isset($item['description']))
                                        <span class="text-gray-500 dark:text-gray-400"> — {{ $item['description'] }}</span>
                                    @endif
                                </p>
                                @if(isset($item['time']))
                                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $item['time'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@else
    <div class="text-center py-8">
        <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity.</p>
    </div>
@endif
