<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false', darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val)); $watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Edutrack LMS'))</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/tailwind.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">

    <!-- Dashboard Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-200">
    <div class="flex h-screen overflow-hidden">

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity.duration.300ms class="fixed inset-0 z-40 bg-black/50 md:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed md:relative z-50 h-full bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out flex flex-col" :style="sidebarOpen ? 'width: 260px;' : 'width: 0; overflow: hidden;'" style="width: 260px;">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/logo-sm.png') }}" alt="Edutrack" class="h-9 w-auto">
                    <div class="flex flex-col">
                        <span class="text-lg font-bold text-primary-600 dark:text-primary-400 leading-tight">Edutrack</span>
                        <span class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">LMS Dashboard</span>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                @auth
                    @php
                        $user = auth()->user();
                        $role = $user->isAdmin() ? 'admin' : ($user->isInstructor() ? 'instructor' : ($user->isStudent() ? 'student' : 'finance'));
                    @endphp

                    @if($user->isAdmin())
                        <x-dashboard-nav-item route="admin.dashboard" icon="fa-tachometer-alt" label="Dashboard" />
                        @if(Route::has('admin.users.index'))
                        <x-dashboard-nav-item route="admin.users.index" icon="fa-users" label="Users" />
                        @endif
                        @if(Route::has('admin.courses.index'))
                        <x-dashboard-nav-item route="admin.courses.index" icon="fa-book" label="Courses" />
                        @endif
                        @if(Route::has('admin.payments.index'))
                        <x-dashboard-nav-item route="admin.payments.index" icon="fa-money-bill-wave" label="Payments" />
                        @endif
                        <x-dashboard-nav-item route="admin.reports" icon="fa-chart-bar" label="Reports" />
                        <x-dashboard-nav-item route="admin.settings" icon="fa-cog" label="Settings" />
                    @elseif($user->isInstructor())
                        <x-dashboard-nav-item route="instructor.dashboard" icon="fa-tachometer-alt" label="Dashboard" />
                        @if(Route::has('instructor.courses.index'))
                        <x-dashboard-nav-item route="instructor.courses.index" icon="fa-book" label="My Courses" />
                        @endif
                        <x-dashboard-nav-item route="instructor.submissions" icon="fa-clipboard-check" label="Submissions" />
                        <x-dashboard-nav-item route="instructor.analytics" icon="fa-chart-line" label="Analytics" />
                    @elseif($user->isStudent())
                        <x-dashboard-nav-item route="student.dashboard" icon="fa-tachometer-alt" label="Dashboard" />
                        <x-dashboard-nav-item route="enrollments.index" icon="fa-book-open" label="My Courses" />
                        <x-dashboard-nav-item route="student.progress" icon="fa-chart-pie" label="Progress" />
                        <x-dashboard-nav-item route="student.certificates" icon="fa-certificate" label="Certificates" />
                        <x-dashboard-nav-item route="student.payments" icon="fa-credit-card" label="Payments" />
                    @elseif($user->isFinance())
                        <x-dashboard-nav-item route="finance.dashboard" icon="fa-tachometer-alt" label="Dashboard" />
                        <x-dashboard-nav-item route="finance.transactions" icon="fa-money-bill-wave" label="Transactions" />
                        <x-dashboard-nav-item route="finance.invoices" icon="fa-file-invoice" label="Invoices" />
                    @endif

                    <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                        <x-dashboard-nav-item route="home" icon="fa-home" label="Back to Site" />
                    </div>
                @endauth
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <i class="fas fa-sign-out-alt w-5 text-center"></i>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Top Bar -->
            <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4 md:px-6 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-800 dark:text-white hidden sm:block">@yield('page_title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-3 md:gap-4">
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors" title="Toggle Dark Mode">
                        <i class="fas fa-moon" x-show="!darkMode"></i>
                        <i class="fas fa-sun" x-show="darkMode" style="display: none;"></i>
                    </button>

                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center gap-3 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-sm">
                                {{ strtoupper(substr(auth()->user()->first_name ?? auth()->user()->username ?? 'U', 0, 1)) }}
                            </div>
                            <div class="hidden md:block text-left">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ auth()->user()->full_name ?? auth()->user()->username }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ $role ?? 'User' }}</div>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-gray-400 hidden md:block"></i>
                        </button>

                        <div x-show="open" x-transition class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50">
                            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                <div class="text-sm font-medium text-gray-800 dark:text-white">{{ auth()->user()->full_name ?? auth()->user()->username }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</div>
                            </div>
                            <a href="{{ route('home') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fas fa-home mr-2 w-4"></i> Back to Site
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <i class="fas fa-sign-out-alt mr-2 w-4"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
