@extends('layouts.app')

@section('title', 'About Us - Edutrack Computer Training College')
@section('meta_description', 'Learn about Edutrack Computer Training College - TEVETA registered institution in Zambia. Our mission, vision, and commitment to quality education.')

@section('content')

<!-- Page Header -->
<section class="bg-primary-600 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">About Edutrack</h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Empowering Zambians through quality computer training and TEVETA-certified education
            </p>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Mission -->
            <div class="bg-blue-50 rounded-xl p-8 shadow-lg animate-slide-up">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-bullseye text-white text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
                <p class="text-gray-700 leading-relaxed">
                    To provide accessible, high-quality computer training and technical education that empowers
                    individuals with industry-relevant skills, enabling them to compete effectively in the digital economy
                    and contribute to Zambia's technological advancement.
                </p>
            </div>

            <!-- Vision -->
            <div class="bg-purple-50 rounded-xl p-8 shadow-lg animate-slide-up animation-delay-200">
                <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-eye text-white text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Vision</h2>
                <p class="text-gray-700 leading-relaxed">
                    To be Zambia's leading computer training institution, recognized for excellence in technical education,
                    innovation in teaching methodologies, and the success of our graduates in building rewarding careers
                    in the technology sector.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Students Choose Edutrack</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                We combine quality instruction, modern facilities, and industry partnerships to deliver exceptional results
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 animate-slide-up">
                <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-certificate text-yellow-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">TEVETA Certified</h3>
                <p class="text-gray-600">All our programs are registered and accredited by the Technical Education, Vocational and Entrepreneurship Training Authority.</p>
            </div>

            <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 animate-slide-up animation-delay-100">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-laptop-code text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Hands-On Training</h3>
                <p class="text-gray-600">Learn by doing in our modern computer labs equipped with the latest hardware and software.</p>
            </div>

            <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 animate-slide-up animation-delay-200">
                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-briefcase text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Career Support</h3>
                <p class="text-gray-600">We help you prepare for the job market with CV writing, interview preparation, and job placement assistance.</p>
            </div>

            <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 animate-slide-up">
                <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-chalkboard-teacher text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Expert Instructors</h3>
                <p class="text-gray-600">Learn from certified professionals with real-world industry experience and passion for teaching.</p>
            </div>

            <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 animate-slide-up animation-delay-100">
                <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-users text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Small Class Sizes</h3>
                <p class="text-gray-600">Personalized attention with limited students per class ensures you get the support you need.</p>
            </div>

            <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 animate-slide-up animation-delay-200">
                <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-money-bill-wave text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Affordable Fees</h3>
                <p class="text-gray-600">Quality education at competitive prices with flexible payment plans to suit your budget.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-primary-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Start Your Journey?</h2>
        <p class="text-xl text-primary-100 max-w-2xl mx-auto mb-8">
            Join thousands of students who have transformed their careers through Edutrack's TEVETA-certified programs.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('courses.index') }}" class="inline-flex items-center justify-center px-8 py-4 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-600 transition shadow-lg">
                <i class="fas fa-book mr-2"></i> Browse Courses
            </a>
            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-primary-600 transition">
                <i class="fas fa-phone mr-2"></i> Contact Us
            </a>
        </div>
    </div>
</section>

@endsection
