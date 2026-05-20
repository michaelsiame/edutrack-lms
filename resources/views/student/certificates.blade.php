@extends('layouts.dashboard')

@section('title', 'My Certificates - Edutrack LMS')
@section('page_title', 'My Certificates')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">My Certificates</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($certificates ?? [] as $certificate)
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-all duration-300">
            <div class="bg-gradient-to-r from-primary-600 to-primary-800 p-6 text-white">
                <div class="flex items-center justify-between">
                    <i class="fas fa-certificate text-4xl text-yellow-400"></i>
                    <span class="text-xs bg-white/20 px-2 py-1 rounded">Certified</span>
                </div>
                <h3 class="text-lg font-bold mt-4">Certificate of Completion</h3>
                <p class="text-primary-100 text-sm">{{ $certificate->enrollment->course->title ?? 'Course' }}</p>
            </div>
            <div class="p-6">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Certificate #</span>
                        <span class="font-medium text-gray-900">{{ $certificate->certificate_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Issued</span>
                        <span class="font-medium text-gray-900">{{ $certificate->issued_at?->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Score</span>
                        <span class="font-medium text-gray-900">{{ $certificate->final_score ?? 'N/A' }}%</span>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <a href="{{ route('certificates.download', $certificate) }}" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm">
                        <i class="fas fa-download mr-2"></i> Download
                    </a>
                    <a href="{{ route('certificates.verify', $certificate->certificate_number) }}" target="_blank" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                        <i class="fas fa-check-circle mr-2"></i> Verify
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-certificate text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Certificates Yet</h3>
            <p class="text-gray-600 mb-6">Complete a course to earn your first professional certificate.</p>
            <a href="{{ route('courses.index') }}" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                <i class="fas fa-search mr-2"></i> Browse Courses
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection
