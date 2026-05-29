@php
$promotions = App\Models\Promotion::active()->available()
    ->where(function($q) {
        $q->whereNull('applicable_courses')
          ->orWhereRaw('JSON_LENGTH(applicable_courses) = 0');
    })
    ->orderBy('ends_at')
    ->take(3)
    ->get();
@endphp

@if($promotions->isNotEmpty())
<div class="relative overflow-hidden" style="background: linear-gradient(135deg, var(--od-navy) 0%, #1e3a8a 100%);">
    <div class="absolute inset-0 opacity-10">
        <svg width="100%" height="100%">
            <pattern id="promo-dots" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                <circle cx="2" cy="2" r="1" fill="#fff"/>
            </pattern>
            <rect width="100%" height="100%" fill="url(#promo-dots)"/>
        </svg>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 relative">
        <div class="flex items-center justify-center gap-4 flex-wrap">
            @foreach($promotions as $promotion)
            <div class="flex items-center gap-3 px-4 py-2 rounded-full" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);">
                <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--od-accent);">{{ $promotion->formattedDiscount() }}</span>
                <span class="text-sm text-white">{{ $promotion->name }}</span>
                <span class="text-xs text-white/70">Use code <span class="font-mono font-bold text-white">{{ $promotion->code }}</span></span>
                @if($promotion->ends_at)
                <span class="text-xs text-white/60">&middot; Ends {{ $promotion->ends_at->diffForHumans() }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
