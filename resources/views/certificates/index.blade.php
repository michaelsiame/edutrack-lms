@extends('layouts.app')

@section('title', 'My Certificates - Edutrack LMS')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">My Certificates</h2>

        <div class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @forelse($certificates as $certificate)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-indigo-100 p-3 rounded-full">
                                <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-xs text-gray-500">{{ $certificate->issued_date?->format('M d, Y') }}</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $certificate->course?->title ?? 'Unknown Course' }}</h3>
                        <p class="text-sm text-gray-500 mb-4">Cert. #{{ $certificate->certificate_number }}</p>
                        <a href="{{ route('certificates.download', $certificate) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Download PDF
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 bg-white rounded-lg shadow">
                    <p class="text-gray-500 mb-2">No certificates yet.</p>
                    <p class="text-sm text-gray-400">Complete a course to earn your certificate!</p>
                    <a href="{{ route('courses.index') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">Browse Courses</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
