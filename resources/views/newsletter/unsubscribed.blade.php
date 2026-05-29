@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <i class="fas fa-envelope-open-text text-6xl text-gray-400 mb-4"></i>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Unsubscribed</h2>
            @if(session('success'))
                <p class="mt-2 text-sm text-gray-600">{{ session('success') }}</p>
            @else
                <p class="mt-2 text-sm text-gray-600">You have been unsubscribed from our newsletter.</p>
            @endif
        </div>
        <div class="mt-8">
            <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-[#1e3a5f] hover:bg-[#152a45]">
                Return to Home
            </a>
        </div>
    </div>
</div>
@endsection
