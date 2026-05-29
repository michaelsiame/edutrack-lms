@extends('layouts.dashboard')

@section('title','Finance Dashboard - Edutrack LMS')
@section('page_title','Finance Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
 <div class="od-stat-card">
 <div>
 <p class="od-stat-label">Total Revenue</p>
 <p class="od-stat-value od-num">ZMW {{ number_format($stats['total_revenue'], 0) }}</p>
 </div>
 <div class="od-stat-icon" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-coins text-lg"></i>
 </div>
 </div>

 <div class="od-stat-card">
 <div>
 <p class="od-stat-label">Today's Revenue</p>
 <p class="od-stat-value od-num">ZMW {{ number_format($stats['today_revenue'], 0) }}</p>
 </div>
 <div class="od-stat-icon" style="background: var(--od-navy-soft); color: var(--od-navy);">
 <i class="fas fa-calendar-day text-lg"></i>
 </div>
 </div>

 <div class="od-stat-card">
 <div>
 <p class="od-stat-label">Month Revenue</p>
 <p class="od-stat-value od-num">ZMW {{ number_format($stats['month_revenue'], 0) }}</p>
 </div>
 <div class="od-stat-icon" style="background: var(--od-green-soft); color: var(--od-green);">
 <i class="fas fa-chart-line text-lg"></i>
 </div>
 </div>

 <div class="od-stat-card">
 <div>
 <p class="od-stat-label">Pending Payments</p>
 <p class="od-stat-value">{{ $stats['pending_payments'] }}</p>
 </div>
 <div class="od-stat-icon" style="background: color-mix(in oklch, var(--od-accent) 12%, transparent); color: var(--od-accent);">
 <i class="fas fa-clock text-lg"></i>
 </div>
 </div>
</div>

<!-- Revenue Chart -->
<div class="od-card mb-8">
 <h3 class="od-h3 mb-4"><i class="fas fa-chart-line mr-2" style="color: var(--od-navy);"></i>Revenue Trend (Last 6 Months)</h3>
 <div class="h-72">
 <canvas id="revenueChart"></canvas>
 </div>
</div>

<!-- Recent Transactions -->
<div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="px-6 py-4 flex items-center justify-between" style="border-bottom: 1px solid var(--od-border);">
 <h3 class="od-h3"><i class="fas fa-money-bill-wave mr-2" style="color: var(--od-green);"></i>Recent Transactions</h3>
 <a href="{{ route('finance.transactions') }}" class="text-sm font-medium" style="color: var(--od-navy);">View All</a>
 </div>
 <div class="overflow-x-auto">
 <table class="dashboard-table od-table">
 <thead>
 <tr>
 <th>Student</th>
 <th>Course</th>
 <th class="num-col">Amount</th>
 <th>Status</th>
 <th>Date</th>
 </tr>
 </thead>
 <tbody>
 @forelse($recentPayments as $payment)
 <tr>
 <td>
 <span class="font-medium" style="color: var(--od-fg);">{{ $payment->student?->full_name ??'Unknown' }}</span>
 </td>
 <td class="od-meta">{{ $payment->course?->title ??'N/A' }}</td>
 <td class="font-medium num-col" style="color: var(--od-fg);">ZMW {{ number_format($payment->amount, 2) }}</td>
 <td>
 <span class="od-badge {{ $payment->payment_status ==='Completed' ?'od-badge-success' :'od-badge-warn' }}">
 {{ $payment->payment_status }}
 </span>
 </td>
 <td class="od-meta">{{ $payment->created_at?->format('M d, Y') }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="text-center py-10 od-meta">No recent transactions</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: 'Revenue (ZMW)',
            data: {!! json_encode($chartData) !!},
            backgroundColor: 'color-mix(in oklch, var(--od-navy), transparent 60%)',
            borderColor: 'var(--od-navy)',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'ZMW ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'ZMW ' + value.toLocaleString();
                    }
                }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>
@endpush
