@extends('layouts.app')

@section('title', 'Verify Certificate - Edutrack LMS')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Certificate Verified</h2>
                <p class="text-gray-500 mb-6">This certificate is authentic and issued by Edutrack Computer Training College.</p>

                <div class="bg-gray-50 rounded-lg p-6 text-left max-w-lg mx-auto">
                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between border-b border-gray-200 pb-2">
                            <span class="text-sm text-gray-500">Certificate Number</span>
                            <span class="text-sm font-medium text-gray-900">{{ $certificate->certificate_number }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-200 pb-2">
                            <span class="text-sm text-gray-500">Student Name</span>
                            <span class="text-sm font-medium text-gray-900">{{ $certificate->user?->full_name ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-200 pb-2">
                            <span class="text-sm text-gray-500">Course</span>
                            <span class="text-sm font-medium text-gray-900">{{ $certificate->course?->title ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-200 pb-2">
                            <span class="text-sm text-gray-500">Issue Date</span>
                            <span class="text-sm font-medium text-gray-900">{{ $certificate->issued_date?->format('F d, Y') }}</span>
                        </div>
                        @if($certificate->final_score)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Final Score</span>
                                <span class="text-sm font-medium text-gray-900">{{ $certificate->final_score }}%</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
