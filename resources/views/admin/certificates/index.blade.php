@extends('layouts.dashboard')

@section('title','Certificates - Admin')
@section('page_title','Certificates')

@section('content')
<div class="max-w-6xl mx-auto">
    @if(session('success'))
        <div class="mb-4 p-4 od-toast-success">{{ session('success') }}</div>
    @endif

    <!-- Filters -->
    <div class="od-card p-4 mb-6">
        <form action="{{ route('admin.certificates.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="od-form-label">Course</label>
                <select name="course" class="od-input">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="od-form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Student name, cert #, or code"
                    class="od-input">
            </div>
            <button type="submit" class="od-btn od-btn-primary od-btn-sm">Filter</button>
            <a href="{{ route('admin.certificates.index') }}" class="od-btn od-btn-secondary od-btn-sm">Clear</a>
        </form>
    </div>

    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="overflow-x-auto">
            <table class="od-table min-w-[640px]">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left" scope="col">Student</th>
                        <th class="px-4 py-3 text-left" scope="col">Course</th>
                        <th class="px-4 py-3 text-left" scope="col">Certificate #</th>
                        <th class="px-4 py-3 text-left" scope="col">Score</th>
                        <th class="px-4 py-3 text-left" scope="col">Issued</th>
                        <th class="px-4 py-3 text-right" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certificates as $certificate)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium" style="color: var(--od-fg);">{{ $certificate->user?->full_name ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-500">{{ $certificate->user?->email ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $certificate->course?->title ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="od-num text-xs">{{ $certificate->certificate_number }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($certificate->final_score)
                                    <span class="font-bold" style="color: var(--od-green);">{{ $certificate->final_score }}%</span>
                                    @if($certificate->classification)
                                        <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-medium" style="background: var(--od-accent-soft); color: var(--od-accent);">{{ $certificate->classification }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-sm">{{ $certificate->issued_at?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('certificates.preview', $certificate) }}" target="_blank" class="od-btn od-btn-ghost od-btn-sm" aria-label="Preview certificate">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <a href="{{ route('certificates.download', $certificate) }}" class="od-btn od-btn-ghost od-btn-sm" aria-label="Download certificate">
                                    <i class="fas fa-download text-sm"></i>
                                </a>
                                <a href="{{ route('certificates.verify', $certificate->certificate_number) }}" target="_blank" class="od-btn od-btn-ghost od-btn-sm" aria-label="Verify certificate">
                                    <i class="fas fa-check-circle text-sm"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                <i class="fas fa-certificate text-4xl mb-3 opacity-20"></i>
                                <p>No certificates found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($certificates->hasPages())
        <div class="mt-6">
            {{ $certificates->links() }}
        </div>
    @endif
</div>
@endsection
