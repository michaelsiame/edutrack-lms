@extends('layouts.dashboard')

@section('title','My Certificates - Edutrack LMS')
@section('page_title','My Certificates')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($certificates ?? [] as $certificate)
            <x-card variant="interactive" class="overflow-hidden flex flex-col">
                <!-- Certificate Header -->
                <div class="bg-gradient-to-br from-primary-600 to-primary-700 p-6 text-white relative">
                    <div class="flex items-center justify-between relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <i class="fas fa-certificate text-xl text-secondary-300"></i>
                        </div>
                        <x-status-badge status="Completed" size="sm" />
                    </div>
                    <h3 class="text-lg font-bold mt-4 relative z-10">Certificate of Completion</h3>
                    <p class="text-primary-100 text-sm mt-1 relative z-10 line-clamp-2">{{ $certificate->course?->title ?? 'Course' }}</p>
                </div>

                <!-- Certificate Details -->
                <div class="p-6 flex-1 flex flex-col">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">Certificate #</span>
                            <span class="font-mono font-medium text-gray-900 dark:text-white text-xs">{{ $certificate->certificate_number }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">Issued</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $certificate->issued_at?->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">Score</span>
                            <span class="font-bold text-success-600 dark:text-success-400">{{ $certificate->final_score ?? 'N/A' }}%</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-700 flex gap-3">
                        <x-button :href="route('certificates.download', $certificate)" icon="fa-download" size="sm" class="flex-1 justify-center">
                            Download
                        </x-button>
                        <x-button :href="route('certificates.verify', $certificate->certificate_number)" variant="outline" icon="fa-check-circle" size="sm" target="_blank">
                            Verify
                        </x-button>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card variant="elevated">
                    <x-empty-state icon="fa-certificate" title="No Certificates Yet" description="Complete a course to earn your first professional certificate." actionText="Browse Courses" actionRoute="courses.index" />
                </x-card>
            </div>
        @endforelse
    </div>

    @if($certificates->hasPages())
        <div class="mt-6">
            {{ $certificates->links() }}
        </div>
    @endif
</div>
@endsection
