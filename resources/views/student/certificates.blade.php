@extends('layouts.dashboard')

@section('title','My Certificates - Edutrack LMS')
@section('page_title','My Certificates')

@section('content')
<div class="max-w-5xl mx-auto">
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
 @forelse($certificates ?? [] as $certificate)
 <x-card hover class="overflow-hidden">
 <div class="bg-primary-600 p-6 text-white">
 <div class="flex items-center justify-between">
 <i class="fas fa-certificate text-4xl text-secondary-400"></i>
 <span class="text-xs bg-white/20 px-2 py-1 rounded-lg">Certified</span>
 </div>
 <h3 class="text-lg font-bold mt-4">Certificate of Completion</h3>
 <p class="text-primary-100 text-sm">{{ $certificate->course?->title ??'Course' }}</p>
 </div>
 <div class="p-6">
 <div class="space-y-3 text-sm">
 <div class="flex justify-between">
 <span class="text-gray-500 dark:text-gray-400">Certificate #</span>
 <span class="font-medium text-gray-900 dark:text-white">{{ $certificate->certificate_number }}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-gray-500 dark:text-gray-400">Issued</span>
 <span class="font-medium text-gray-900 dark:text-white">{{ $certificate->issued_at?->format('M d, Y') }}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-gray-500 dark:text-gray-400">Score</span>
 <span class="font-medium text-gray-900 dark:text-white">{{ $certificate->final_score ??'N/A' }}%</span>
 </div>
 </div>
 <div class="mt-6 flex gap-3">
 <a href="{{ route('certificates.download', $certificate) }}" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition text-sm font-medium">
 <i class="fas fa-download mr-2"></i> Download
 </a>
 <a href="{{ route('certificates.verify', $certificate->certificate_number) }}" target="_blank" class="inline-flex justify-center items-center px-4 py-2 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium">
 <i class="fas fa-check-circle mr-2"></i> Verify
 </a>
 </div>
 </div>
 </x-card>
 @empty
 <div class="col-span-full">
 <x-card>
 <x-empty-state icon="fa-certificate" title="No Certificates Yet" description="Complete a course to earn your first professional certificate." actionText="Browse Courses" actionRoute="courses.index" />
 </x-card>
 </div>
 @endforelse
 </div>
</div>
@endsection
