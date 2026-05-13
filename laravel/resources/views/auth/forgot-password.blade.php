@extends('layouts.app')

@section('title', 'Forgot Password - Edutrack LMS')

@section('content')
<div class="min-h-screen flex flex-col justify-center py-10 px-4 sm:px-6 bg-gray-50">
    <div class="mx-auto w-full max-w-md">
        {{-- Logo / Header --}}
        <div class="text-center mb-6">
            <a href="{{ url('/') }}" class="inline-block mb-4">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Edutrack Logo" class="h-16 w-auto mx-auto">
            </a>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">
                Reset your password
            </h2>
            <p class="mt-2 text-sm text-gray-500">
                Enter your email and we'll send you a reset link
            </p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <form class="space-y-5" action="{{ route('password.email') }}" method="POST">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400 text-sm"></i>
                        </div>
                        <input id="email" name="email" type="email" required
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                            placeholder="you@example.com"
                            value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send Reset Link
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back to login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
