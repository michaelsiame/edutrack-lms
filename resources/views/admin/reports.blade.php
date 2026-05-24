@extends('layouts.dashboard')

@section('title','Reports - Edutrack LMS')
@section('page_title','Reports & Analytics')

@section('content')
<div class="space-y-6">
    <!-- Report Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Enrollment Report -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-primary-600 dark:text-primary-400 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Enrollments</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Export all enrollment records</p>
                </div>
            </div>
            <form action="{{ route('admin.reports.export', 'enrollments') }}" method="GET" class="space-y-3">
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="from" placeholder="From" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                    <input type="date" name="to" placeholder="To" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </button>
            </form>
        </div>

        <!-- Payments Report -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-success-600 dark:text-success-400 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Payments</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Export all payment records</p>
                </div>
            </div>
            <form action="{{ route('admin.reports.export', 'payments') }}" method="GET" class="space-y-3">
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="from" placeholder="From" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                    <input type="date" name="to" placeholder="To" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-success-600 hover:bg-success-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </button>
            </form>
        </div>

        <!-- Courses Report -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-warning-600 dark:text-warning-400 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Courses</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Export course completion stats</p>
                </div>
            </div>
            <form action="{{ route('admin.reports.export', 'courses') }}" method="GET" class="space-y-3">
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="from" placeholder="From" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                    <input type="date" name="to" placeholder="To" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-warning-600 hover:bg-warning-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Quick Stats</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\Enrollment::count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Enrollments</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\Payment::where('payment_status','Completed')->count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Completed Payments</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\Course::count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Courses</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\User::where('is_active', true)->count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Active Users</p>
            </div>
        </div>
    </div>
</div>
@endsection
