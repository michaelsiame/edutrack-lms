@extends('layouts.app')

@section('title', 'FAQ - Frequently Asked Questions - Edutrack')

@section('content')

<!-- Page Header -->
<section class="bg-primary-600 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Frequently Asked Questions</h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Find answers to common questions about admissions, fees, courses, and more
            </p>
        </div>
    </div>
</section>

<!-- FAQ Content -->
<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if(isset($faqs) && count($faqs) > 0)
            <!-- Category Tabs -->
            <div class="flex flex-wrap justify-center gap-3 mb-12" x-data="{ activeCategory: '{{ array_key_first($faqs) }}' }">
                @foreach(array_keys($faqs) as $key)
                <button @click="activeCategory = '{{ $key }}'"
                        :class="activeCategory === '{{ $key }}' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-6 py-3 rounded-lg font-medium transition flex items-center capitalize">
                    <i class="fas fa-folder mr-2"></i> {{ $key }}
                </button>
                @endforeach

                @foreach($faqs as $category => $questions)
                <div x-show="activeCategory === '{{ $category }}'" x-cloak x-transition class="w-full">
                    <div class="space-y-4" x-data="{ openIndex: null }">
                        @foreach($questions as $index => $item)
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <button @click="openIndex === {{ $index }} ? openIndex = null : openIndex = {{ $index }}"
                                    class="w-full flex items-center justify-between px-6 py-4 bg-gray-50 hover:bg-gray-100 transition text-left">
                                <span class="font-semibold text-gray-900">{{ $item->question ?? $item['question'] }}</span>
                                <i class="fas fa-chevron-down text-gray-500 transition-transform"
                                   :class="openIndex === {{ $index }} ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="openIndex === {{ $index }}" x-collapse class="px-6 py-4 bg-white">
                                <p class="text-gray-600 leading-relaxed">{{ $item->answer ?? $item['answer'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-question-circle text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No FAQs yet</h3>
                <p class="text-gray-500">Frequently asked questions will be added soon. Contact us for any inquiries.</p>
            </div>
        @endif
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-primary-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Still Have Questions?</h2>
        <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
            Our admissions team is ready to help. Reach out via WhatsApp, phone, or email.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="https://chat.whatsapp.com/HkqCis0yejbJybxyTbsG2e" target="_blank" class="inline-flex items-center justify-center px-8 py-4 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition shadow-lg">
                <i class="fab fa-whatsapp mr-2"></i> WhatsApp Us
            </a>
            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-primary-600 transition">
                <i class="fas fa-envelope mr-2"></i> Contact Form
            </a>
        </div>
    </div>
</section>

@endsection
