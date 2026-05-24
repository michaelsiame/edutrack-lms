@props(['emptyIcon' => 'fa-inbox', 'emptyTitle' => 'No Data', 'emptyDescription' => null, 'columns' => null])

<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        @if($columns)
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    @foreach($columns as $col)
                        <th scope="col" class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ $col }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
            {{ $slot }}
        </tbody>
    </table>
</div>

@if(isset($empty) && $empty)
    <div class="px-6 py-12 md:py-16 text-center bg-white dark:bg-gray-800">
        <div class="w-14 h-14 md:w-16 md:h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas {{ $emptyIcon }} text-xl md:text-2xl text-gray-400"></i>
        </div>
        <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">{{ $emptyTitle }}</h3>
        @if($emptyDescription)
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">{{ $emptyDescription }}</p>
        @endif
    </div>
@endif
