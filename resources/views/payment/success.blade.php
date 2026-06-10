@extends('layouts.app')

@section('title','Payment Status - Edutrack LMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4" style="background: var(--od-bg);">
    <div class="max-w-md w-full text-center" x-data="paymentStatus()" x-init="init()">
        <div class="mb-6">
            <template x-if="status === 'pending'">
                <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center" style="background: #fef3c7;">
                    <i class="fas fa-clock text-4xl" style="color: #d97706;"></i>
                </div>
            </template>
            <template x-if="status === 'completed'">
                <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center" style="background: var(--od-green-soft);">
                    <i class="fas fa-check text-4xl" style="color: var(--od-green);"></i>
                </div>
            </template>
            <template x-if="status === 'failed' || status === 'cancelled'">
                <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center" style="background: #fee2e2;">
                    <i class="fas fa-times text-4xl" style="color: #dc2626;"></i>
                </div>
            </template>
        </div>

        <template x-if="status === 'pending'">
            <div>
                <p class="od-eyebrow mb-2">PAYMENT PROCESSING</p>
                <h1 class="od-h1 mb-3">Processing Payment…</h1>
                <p class="od-meta mb-8">Please authorize the transaction on your phone if prompted. This page will update automatically.</p>
            </div>
        </template>

        <template x-if="status === 'completed'">
            <div>
                <p class="od-eyebrow mb-2">PAYMENT CONFIRMED</p>
                <h1 class="od-h1 mb-3">Payment Successful!</h1>
                <p class="od-meta mb-8">Your payment has been received and is being processed. You will receive a confirmation email shortly.</p>
            </div>
        </template>

        <template x-if="status === 'failed' || status === 'cancelled'">
            <div>
                <p class="od-eyebrow mb-2" style="color: #dc2626;">PAYMENT FAILED</p>
                <h1 class="od-h1 mb-3">Payment Failed</h1>
                <p class="od-meta mb-8">We could not confirm your payment. Please try again.</p>
            </div>
        </template>

        @if($course)
            <div class="od-card p-6 mb-6">
                <h3 class="font-semibold" style="color: var(--od-fg);">{{ $course->title }}</h3>
                <template x-if="status === 'pending'">
                    <p class="text-sm mb-4 od-meta">Awaiting payment confirmation…</p>
                </template>
                <template x-if="status === 'completed'">
                    <div>
                        <p class="text-sm mb-4 od-meta">You can now access your course content.</p>
                        <a href="{{ route('enrollments.show', $course) }}"
                            class="od-btn od-btn-primary block text-center">
                            Start Learning
                        </a>
                    </div>
                </template>
                <template x-if="status === 'failed' || status === 'cancelled'">
                    <a href="{{ route('checkout.show', $course) }}"
                        class="od-btn od-btn-primary block text-center mt-4">
                        Try Again
                    </a>
                </template>
            </div>
        @endif

        <div class="space-y-3">
            <a href="{{ route('enrollments.index') }}" class="block font-medium" style="color: var(--od-navy);">
                View My Courses
            </a>
            <a href="{{ route('courses.index') }}" class="block od-meta">
                Browse More Courses
            </a>
        </div>
    </div>
</div>

<script>
function paymentStatus() {
    return {
        status: 'pending',
        course: @json($course?->slug),
        pollInterval: null,
        startTime: Date.now(),
        init() {
            this.poll();
            this.pollInterval = setInterval(() => {
                if (this.status !== 'pending') {
                    clearInterval(this.pollInterval);
                    return;
                }
                if (Date.now() - this.startTime > 300000) {
                    clearInterval(this.pollInterval);
                    return;
                }
                this.poll();
            }, 5000);
        },
        poll() {
            let url = '{{ route('payment.status') }}';
            if (this.course) {
                url += '?course=' + encodeURIComponent(this.course);
            }
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data.status !== 'none' && data.status !== 'pending') {
                        this.status = data.status;
                        clearInterval(this.pollInterval);
                    }
                })
                .catch(() => {});
        }
    }
}
</script>
@endsection
