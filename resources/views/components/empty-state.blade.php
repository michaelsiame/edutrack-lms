@props(['icon','title','description' => null,'actionText' => null,'actionHref' => null,'actionRoute' => null])
<div class="text-center py-12 md:py-16">
 <div class="w-16 h-16 md:w-20 md:h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
 <i class="fas {{ $icon }} text-2xl md:text-3xl text-gray-400"></i>
 </div>
 <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ $title }}</h3>
 @if($description)
 <p class="text-gray-500 dark:text-gray-400 text-sm max-w-md mx-auto mb-6">{{ $description }}</p>
 @endif
 @if($actionText && ($actionHref || $actionRoute))
 <a href="{{ $actionHref ?? route($actionRoute) }}" class="inline-flex items-center px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition text-sm">
 <i class="fas fa-arrow-right mr-2 text-xs"></i>{{ $actionText }}
 </a>
 @endif
</div>
