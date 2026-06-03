@extends('layouts.dashboard')

@section('title','Admin Dashboard - Edutrack LMS')
@section('page_title','Admin Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8 stagger-children">
    <div class="od-stat-card">
        <div>
            <p class="od-stat-label">Total Users</p>
            <p class="od-stat-value od-num">{{ number_format($stats['total_users']) }}</p>
            <div class="mt-3 flex items-center text-xs gap-2">
                <span class="od-badge od-badge-success"><i class="fas fa-arrow-up mr-1"></i>Active</span>
                <span class="od-meta">All roles</span>
            </div>
        </div>
        <div class="od-stat-icon" style="background: var(--od-navy-soft); color: var(--od-navy);">
            <i class="fas fa-users text-lg"></i>
        </div>
    </div>

    <div class="od-stat-card">
        <div>
            <p class="od-stat-label">Total Courses</p>
            <p class="od-stat-value od-num">{{ number_format($stats['total_courses']) }}</p>
            <div class="mt-3 flex items-center text-xs gap-2">
                <span class="od-badge od-badge-success"><i class="fas fa-check mr-1"></i>Published</span>
                <span class="od-meta">Across categories</span>
            </div>
        </div>
        <div class="od-stat-icon" style="background: var(--od-green-soft); color: var(--od-green);">
            <i class="fas fa-book text-lg"></i>
        </div>
    </div>

    <div class="od-stat-card">
        <div>
            <p class="od-stat-label">Enrollments</p>
            <p class="od-stat-value od-num">{{ number_format($stats['total_enrollments']) }}</p>
            <div class="mt-3 flex items-center text-xs gap-2">
                <span class="od-badge od-badge-info"><i class="fas fa-chart-line mr-1"></i>Growing</span>
                <span class="od-meta">This year</span>
            </div>
        </div>
        <div class="od-stat-icon" style="background: var(--od-accent-soft); color: var(--od-accent);">
            <i class="fas fa-user-graduate text-lg"></i>
        </div>
    </div>

    <div class="od-stat-card">
        <div>
            <p class="od-stat-label">Total Revenue</p>
            <p class="od-stat-value od-num">{{ setting('currency', 'ZMW') }} {{ number_format($stats['total_revenue'], 0) }}</p>
            <div class="mt-3 flex items-center text-xs gap-2">
                <span class="od-badge od-badge-warn"><i class="fas fa-clock mr-1"></i>{{ $stats['pending_payments'] }} Pending</span>
            </div>
        </div>
        <div class="od-stat-icon" style="background: var(--od-accent-soft); color: var(--od-accent);">
            <i class="fas fa-coins text-lg"></i>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <!-- Recent Enrollments -->
    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="od-card-header">
            <h3 class="od-h3"><i class="fas fa-user-plus mr-2" style="color: var(--od-navy);"></i>Recent Enrollments</h3>
            <a href="{{ route('admin.enrollments.index') }}" class="od-link">View All</a>
        </div>
        <div class="divide-y" style="border-color: var(--od-border);">
            @forelse($stats['recent_enrollments'] as $enrollment)
            <div class="od-list-row">
                <div class="flex items-center gap-3">
                    <div class="od-avatar od-avatar-sm" style="background: var(--od-navy-soft); color: var(--od-navy);">
                        {{ strtoupper(substr($enrollment->user?->first_name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium" style="color: var(--od-fg);">{{ $enrollment->user?->full_name ?? 'Unknown' }}</p>
                        <p class="text-xs od-meta">{{ $enrollment->course?->title ?? 'Unknown Course' }}</p>
                    </div>
                </div>
                <span class="od-meta">{{ $enrollment->created_at?->diffForHumans() }}</span>
            </div>
            @empty
            <div class="od-empty-sm">
                <i class="fas fa-inbox text-3xl"></i>
                <p class="text-sm">No recent enrollments</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="od-card-header">
            <h3 class="od-h3"><i class="fas fa-money-bill-wave mr-2" style="color: var(--od-green);"></i>Recent Payments</h3>
            <a href="{{ route('admin.payments.index') }}" class="od-link">View All</a>
        </div>
        <div class="divide-y" style="border-color: var(--od-border);">
            @forelse($stats['recent_payments'] as $payment)
            <div class="od-list-row">
                <div class="flex items-center gap-3">
                    <div class="od-icon-box od-icon-box-sm" style="background: var(--od-green-soft); color: var(--od-green);">
                        <i class="fas fa-check text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium" style="color: var(--od-fg);">{{ $payment->student?->full_name ?? 'Unknown' }}</p>
                        <p class="text-xs od-meta">ZMW {{ number_format($payment->amount, 2) }}</p>
                    </div>
                </div>
                <span class="od-badge {{ $payment->payment_status === 'Completed' ? 'od-badge-success' : 'od-badge-warn' }}">
                    {{ $payment->payment_status }}
                </span>
            </div>
            @empty
            <div class="od-empty-sm">
                <i class="fas fa-inbox text-3xl"></i>
                <p class="text-sm">No recent payments</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
