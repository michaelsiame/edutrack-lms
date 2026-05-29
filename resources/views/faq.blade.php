@extends('layouts.app')

@section('title','FAQ - Frequently Asked Questions - Edutrack')

@push('styles')
<style>
.od-public-header { background: var(--od-navy); color: var(--od-surface); }
.od-public-cta { background: var(--od-navy); color: var(--od-surface); }
</style>
@endpush

@section('content')

<!-- Page Header -->
<section class="od-public-header py-20">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center">
 <p class="od-eyebrow" style="color: var(--od-accent);">HELP CENTER</p>
 <h1 class="od-h1 mt-2">Frequently Asked Questions</h1>
 <p class="od-lead mx-auto mt-4" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
 Find answers to common questions about admissions, fees, courses, and more
 </p>
 </div>
 </div>
</section>

<!-- FAQ Content -->
<section class="py-16" style="background: var(--od-surface);">
 <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

 @if(isset($faqs) && count($faqs) > 0)
 <!-- Category Tabs -->
 <div class="flex flex-wrap justify-center gap-3 mb-12" x-data="{ activeCategory:'{{ array_key_first($faqs) }}' }">
 @foreach(array_keys($faqs) as $key)
 <button @click="activeCategory ='{{ $key }}'"
 :class="activeCategory ==='{{ $key }}' ?'od-btn od-btn-primary' :'od-btn od-btn-secondary'"
 class="flex items-center capitalize">
 <i class="fas fa-folder mr-2"></i> {{ $key }}
 </button>
 @endforeach

 @foreach($faqs as $category => $questions)
 <div x-show="activeCategory ==='{{ $category }}'" x-cloak x-transition class="w-full">
 <div class="space-y-4" x-data="{ openIndex: null }">
 @foreach($questions as $index => $item)
 <div class="od-card" style="padding: 0; overflow: hidden;">
 <button @click="openIndex === {{ $index }} ? openIndex = null : openIndex = {{ $index }}"
 class="w-full flex items-center justify-between px-6 py-4 text-left transition" style="background: transparent;" onmouseover="this.style.background='var(--od-fg-soft)'" onmouseout="this.style.background='transparent'">
 <span class="font-semibold" style="color: var(--od-fg);">{{ $item->question ?? $item['question'] }}</span>
 <i class="fas fa-chevron-down transition-transform" style="color: var(--od-muted);"
 :class="openIndex === {{ $index }} ?'rotate-180' :''"></i>
 </button>
 <div x-show="openIndex === {{ $index }}" x-collapse class="px-6 py-4" style="border-top: 1px solid var(--od-border);">
 <p class="od-meta leading-relaxed">{{ $item->answer ?? $item['answer'] }}</p>
 </div>
 </div>
 @endforeach
 </div>
 </div>
 @endforeach
 </div>
 @else
 <div class="text-center py-12">
 <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: var(--od-fg-soft);">
 <i class="fas fa-question-circle text-2xl" style="color: var(--od-muted);"></i>
 </div>
 <h3 class="od-h3 mb-2">No FAQs yet</h3>
 <p class="od-meta">Frequently asked questions will be added soon. Contact us for any inquiries.</p>
 </div>
 @endif
 </div>
</section>

<!-- CTA -->
<section class="od-public-cta py-16">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
 <h2 class="od-h2 mb-4">Still Have Questions?</h2>
 <p class="od-lead mx-auto mb-8" style="color: color-mix(in oklch, var(--od-surface), transparent 20%);">
 Our admissions team is ready to help. Reach out via WhatsApp, phone, or email.
 </p>
 <div class="flex flex-col sm:flex-row gap-4 justify-center">
 <a href="https://chat.whatsapp.com/HkqCis0yejbJybxyTbsG2e" target="_blank" class="od-btn od-btn-success od-btn-lg">
 <i class="fab fa-whatsapp mr-2"></i> WhatsApp Us
 </a>
 <a href="{{ route('contact') }}" class="od-btn od-btn-secondary od-btn-lg" style="color: var(--od-surface); border-color: color-mix(in oklch, var(--od-surface), transparent 50%);">
 <i class="fas fa-envelope mr-2"></i> Contact Form
 </a>
 </div>
 </div>
</section>

@endsection
