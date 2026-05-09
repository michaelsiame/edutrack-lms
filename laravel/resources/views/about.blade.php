@extends('layouts.app')

@section('title', 'About Us - Edutrack LMS')

@section('content')
<div class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-gray-900">About Edutrack</h2>
            <p class="mt-4 text-lg text-gray-500">Empowering learners across Zambia with quality education</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-16">
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h3>
                <p class="text-gray-600 mb-4">Edutrack Computer Training College is a TEVETA-registered vocational training institution based in Kalomo, Zambia. We are committed to providing accessible, high-quality education in ICT, business, and technical skills.</p>
                <p class="text-gray-600">Our programs are designed to equip students with practical skills that meet industry demands, ensuring they are job-ready upon graduation.</p>
            </div>
            <div class="bg-indigo-50 rounded-lg p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Why Choose Us?</h3>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="h-6 w-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-600">TEVETA Accredited Programs</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-6 w-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-600">Industry-Experienced Instructors</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-6 w-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-600">Flexible Learning Options</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-6 w-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-600">Recognized Certificates</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
