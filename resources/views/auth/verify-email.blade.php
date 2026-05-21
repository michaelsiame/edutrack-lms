@extends('layouts.app')

@section('title','Verify Email - Edutrack LMS')

@section('content')
<div class="min-h-screen flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
 <div class="mx-auto w-full max-w-md">
 <div class="text-center mb-8">
 <div class="mx-auto w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mb-4">
 <i class="fas fa-envelope text-2xl text-primary-600"></i>
 </div>
 <h2 class="text-3xl font-extrabold text-gray-900">Verify Your Email</h2>
 <p class="mt-2 text-gray-600">Please check your email for a verification link.</p>
 </div>

 <div class="bg-white py-8 px-4 shadow rounded-xl sm:px-10">
 @if(session('success'))
 <div class="mb-4 p-4 bg-success-50 border border-success-200 rounded-lg text-success-700 text-sm">
 {{ session('success') }}
 </div>
 @endif

 @if(session('warning'))
 <div class="mb-4 p-4 bg-warning-50 border border-warning-200 rounded-lg text-warning-700 text-sm">
 {{ session('warning') }}
 </div>
 @endif

 @if(session('verification_link'))
 <div class="mb-4 p-4 bg-primary-50 border border-primary-200 rounded-lg">
 <p class="text-sm text-primary-800 font-medium mb-2">Development Mode - Verification Link:</p>
 <a href="{{ session('verification_link') }}" class="text-sm text-primary-600 break-all hover:underline">
 {{ session('verification_link') }}
 </a>
 </div>
 @endif

 <p class="text-sm text-gray-600 mb-6 text-center">
 Didn't receive the email? Enter your email below to request a new verification link.
 </p>

 <form action="{{ route('verification.resend') }}" method="POST">
 @csrf
 <div class="mb-4">
 <label for="email" class="sr-only">Email address</label>
 <input id="email" name="email" type="email" required
 value="{{ session('email', old('email')) }}"
 class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
 placeholder="Email address">
 @error('email')
 <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
 @enderror
 </div>

 <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
 Resend Verification Email
 </button>
 </form>

 <div class="mt-6 text-center">
 <a href="{{ route('login') }}" class="text-sm text-primary-600 hover:text-primary-500 font-medium">
 Back to Login
 </a>
 </div>
 </div>
 </div>
</div>
@endsection
