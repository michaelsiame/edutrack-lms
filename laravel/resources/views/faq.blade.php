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
<section class="py-16 bg-white" x-data="{ activeCategory: 'admissions' }">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Category Tabs -->
        <div class="flex flex-wrap justify-center gap-3 mb-12">
            @php
            $categories = [
                'admissions' => ['title' => 'Admissions', 'icon' => 'fa-user-plus'],
                'payments' => ['title' => 'Fees & Payments', 'icon' => 'fa-money-bill-wave'],
                'programs' => ['title' => 'Courses', 'icon' => 'fa-graduation-cap'],
                'general' => ['title' => 'General', 'icon' => 'fa-info-circle'],
            ];
            @endphp

            @foreach($categories as $key => $cat)
            <button @click="activeCategory = '{{ $key }}'"
                    :class="activeCategory === '{{ $key }}' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-6 py-3 rounded-lg font-medium transition flex items-center">
                <i class="fas {{ $cat['icon'] }} mr-2"></i> {{ $cat['title'] }}
            </button>
            @endforeach
        </div>

        @php
        $faqs = [
            'admissions' => [
                ['q' => 'What are the admission requirements?', 'a' => 'Basic requirements include: (1) Minimum Grade 12 certificate or equivalent, (2) Valid ID (NRC/Passport), (3) Completed application form, and (4) Registration fee payment. Some advanced courses may require prior computer literacy or specific subject passes.'],
                ['q' => 'How do I apply for a course?', 'a' => 'You can apply online through our website by creating an account and selecting your desired course, or visit our campus in Kalomo to apply in person. Our admissions team is also available via WhatsApp to guide you through the process.'],
                ['q' => 'When are the intakes?', 'a' => 'We have three main intakes per year: January, May, and September. Short courses and workshops may have more frequent start dates. Check our events page or contact admissions for the next available intake for your specific course.'],
                ['q' => 'Can I switch courses after enrolling?', 'a' => 'Yes, course transfers are possible within the first two weeks of the program start date, subject to availability and approval. A small administrative fee may apply. Please contact the registrar for assistance.'],
            ],
            'payments' => [
                ['q' => 'How much are the course fees?', 'a' => 'Course fees vary depending on the program. Certificate courses range from ZMW 1,500 to ZMW 4,500, while Diploma programs range from ZMW 6,000 to ZMW 12,000. Visit individual course pages for exact pricing or request a fee structure from our admissions office.'],
                ['q' => 'Do you offer payment plans?', 'a' => 'Yes! We understand that investing in education is significant. We offer flexible installment plans: (1) Pay 50% upfront and 50% after one month, or (2) Three equal monthly payments. Corporate-sponsored students may have different arrangements.'],
                ['q' => 'What payment methods do you accept?', 'a' => 'We accept: (1) Bank transfers to our Zanaco account, (2) MTN Mobile Money, (3) Airtel Money, (4) Cash payments at our campus, and (5) Lenco online payments through our website. All payments receive official receipts.'],
                ['q' => 'Is there a refund policy?', 'a' => 'Yes. Full refunds are available if you cancel before the course starts. If you withdraw within the first week, you receive 75% refund. After the first week, refunds are prorated based on attendance. Registration fees are non-refundable.'],
                ['q' => 'Are there any scholarships available?', 'a' => 'We offer limited scholarships for outstanding students and those facing financial hardship. We also have special discounts for: (1) Early bird registrations (10% off), (2) Group enrollments (3+ students), and (3) Returning students (15% off second course).'],
            ],
            'programs' => [
                ['q' => 'What courses do you offer?', 'a' => 'We offer TEVETA-certified programs in: Cybersecurity, Web Development, Digital Marketing, Microsoft Office Specialist, Computer Hardware & Networking, Data Science, Graphic Design, and more. Visit our courses page for the complete catalog.'],
                ['q' => 'How long are the courses?', 'a' => 'Course duration varies: Short courses (2-4 weeks), Certificate programs (3-6 months), and Diploma programs (12-18 months). Duration depends on whether you attend full-time, part-time, or evening classes.'],
                ['q' => 'What is the difference between Certificate and Diploma?', 'a' => 'Certificate programs are shorter (3-6 months) focused on specific skills, ideal for quick entry into the workforce. Diploma programs are comprehensive (12-18 months) with deeper theoretical and practical training, leading to advanced positions and higher salaries.'],
                ['q' => 'Do you offer online classes?', 'a' => 'Yes! We offer hybrid learning options. Theory classes can be attended online via our LMS, while practical sessions are held on campus. This flexible approach allows working professionals to balance studies with their careers.'],
            ],
            'general' => [
                ['q' => 'Where is Edutrack located?', 'a' => 'Edutrack Computer Training College is located in Kalomo, Zambia. Our campus is easily accessible and features modern computer labs, classrooms, and student facilities.'],
                ['q' => 'Is Edutrack TEVETA registered?', 'a' => 'Yes, Edutrack is officially registered with the Technical Education, Vocational and Entrepreneurship Training Authority (TEVETA). Our registration number is TEVETA/CTR/2024/001.'],
                ['q' => 'Do you provide certificates after completion?', 'a' => 'Yes, all students who successfully complete their programs receive TEVETA-accredited certificates that are recognized by employers across Zambia and beyond.'],
                ['q' => 'Can I get a job after graduating?', 'a' => 'Absolutely! Our curriculum is designed with industry input to ensure relevance. We also provide career support services including CV writing, interview preparation, and job placement assistance. Many of our graduates work at top companies.'],
            ],
        ];
        @endphp

        @foreach($faqs as $category => $questions)
        <div x-show="activeCategory === '{{ $category }}'" x-cloak x-transition>
            <div class="space-y-4" x-data="{ openIndex: null }">
                @foreach($questions as $index => $item)
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button @click="openIndex === {{ $index }} ? openIndex = null : openIndex = {{ $index }}"
                            class="w-full flex items-center justify-between px-6 py-4 bg-gray-50 hover:bg-gray-100 transition text-left">
                        <span class="font-semibold text-gray-900">{{ $item['q'] }}</span>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform"
                           :class="openIndex === {{ $index }} ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openIndex === {{ $index }}" x-collapse class="px-6 py-4 bg-white">
                        <p class="text-gray-600 leading-relaxed">{{ $item['a'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
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
