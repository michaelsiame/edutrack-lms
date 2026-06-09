@php
$currentRoute = Route::currentRouteName() ?? '';
$userRoles = [];
$hasMultipleRoles = false;
$activeRole = null;

if (auth()->check()) {
    $user = auth()->user();
    $userRoles = $user->roles->pluck('name')->toArray();
    $hasMultipleRoles = count($userRoles) > 1;
    $activeRole = session('active_role', $userRoles[0] ?? null);
}
@endphp

<nav class="bg-white shadow-md sticky top-0 z-50" x-data="{ mobileMenuOpen: false, moreOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center min-w-0">
                <a href="{{ url('/') }}" class="flex items-center min-w-0">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Edutrack Logo" class="h-9 w-auto mr-2 shrink-0">
                    <div class="hidden sm:flex flex-col min-w-0">
                        <span class="text-lg font-bold leading-tight" style="color: var(--od-navy);">Edutrack</span>
                        <span class="text-[10px] truncate" style="color: var(--od-muted);">COMPUTER TRAINING</span>
                    </div>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="{{ url('/') }}" class="od-nav-link {{ request()->routeIs('home') ? 'od-nav-link-active' : '' }}">
                    Home
                </a>
                <a href="{{ route('courses.index') }}" class="od-nav-link {{ request()->routeIs('courses.*') ? 'od-nav-link-active' : '' }}">
                    Courses
                </a>
                <a href="{{ route('about') }}" class="od-nav-link {{ request()->routeIs('about') ? 'od-nav-link-active' : '' }}">
                    About
                </a>

                <!-- More Dropdown -->
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button @click="open = !open" class="od-nav-link flex items-center {{ request()->routeIs('campus','events','contact','faq','testimonials') ? 'od-nav-link-active' : '' }}">
                        More
                        <i class="fas fa-chevron-down ml-1 text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1" x-cloak class="od-nav-dropdown absolute left-0 mt-1 w-48 py-1 z-50">
                        <a href="{{ route('campus') }}" class="od-nav-dropdown-link">
                            <i class="fas fa-university od-nav-dropdown-icon"></i> Campus
                        </a>
                        <a href="{{ route('events') }}" class="od-nav-dropdown-link">
                            <i class="fas fa-calendar-alt od-nav-dropdown-icon"></i> Events
                        </a>
                        <a href="{{ route('testimonials') }}" class="od-nav-dropdown-link">
                            <i class="fas fa-star od-nav-dropdown-icon"></i> Testimonials
                        </a>
                        <a href="{{ route('faq') }}" class="od-nav-dropdown-link">
                            <i class="fas fa-question-circle od-nav-dropdown-icon"></i> FAQ
                        </a>
                        <a href="{{ route('contact') }}" class="od-nav-dropdown-link">
                            <i class="fas fa-envelope od-nav-dropdown-icon"></i> Contact
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="flex items-center space-x-2">
                <!-- Search Icon Button -->
                <a href="{{ route('search') }}" class="od-nav-icon-btn hidden sm:flex" title="Search courses">
                    <i class="fas fa-search text-sm"></i>
                </a>

                @auth
                <!-- Logged In Menu -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 font-medium focus:outline-none px-2 py-1 rounded-md transition-colors hover:bg-[var(--od-fg-soft)]">
                        <div class="od-nav-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden md:inline text-sm" style="color: var(--od-fg);">{{ auth()->user()->name }}</span>
                        <i class="fas fa-chevron-down text-xs" style="color: var(--od-muted);"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition x-cloak class="od-nav-dropdown absolute right-0 mt-2 w-56 py-1 z-50">
                        @if($hasMultipleRoles)
                        <div class="px-4 py-2 border-b od-nav-divider">
                            <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--od-muted);">Roles</p>
                            <div class="mt-1 space-y-0.5">
                                @foreach($userRoles as $role)
                                @php
                                $roleIcon = match($role) {
                                    'admin' => 'fa-shield-alt',
                                    'instructor' => 'fa-chalkboard-teacher',
                                    'student' => 'fa-user-graduate',
                                    'finance' => 'fa-money-bill-wave',
                                    default => 'fa-user'
                                };
                                $roleColor = match($role) {
                                    'admin' => 'color: var(--od-danger);',
                                    'instructor' => 'color: var(--od-navy);',
                                    'student' => 'color: var(--od-green);',
                                    'finance' => 'color: var(--od-accent);',
                                    default => 'color: var(--od-muted);'
                                };
                                @endphp
                                <div class="flex items-center px-2 py-1 text-sm" style="color: var(--od-muted);">
                                    <i class="fas {{ $roleIcon }} w-5" style="{{ $roleColor }}"></i>
                                    <span class="ml-2">{{ ucfirst($role) }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="od-nav-dropdown-link">
                            <i class="fas fa-tachometer-alt w-4 text-center mr-2" style="color: var(--od-muted);"></i> Admin Panel
                        </a>
                        @endif
                        @if(auth()->user()->isInstructor())
                        <a href="{{ route('instructor.dashboard') }}" class="od-nav-dropdown-link">
                            <i class="fas fa-chalkboard-teacher w-4 text-center mr-2" style="color: var(--od-muted);"></i> Instructor Panel
                        </a>
                        @endif
                        @if(auth()->user()->isFinance())
                        <a href="{{ route('finance.dashboard') }}" class="od-nav-dropdown-link">
                            <i class="fas fa-money-bill-wave w-4 text-center mr-2" style="color: var(--od-muted);"></i> Finance Panel
                        </a>
                        @endif

                        <a href="{{ route('dashboard') }}" class="od-nav-dropdown-link">
                            <i class="fas fa-th-large w-4 text-center mr-2" style="color: var(--od-muted);"></i> Dashboard
                        </a>
                        <a href="{{ route('enrollments.index') }}" class="od-nav-dropdown-link">
                            <i class="fas fa-book w-4 text-center mr-2" style="color: var(--od-muted);"></i> My Courses
                        </a>
                        <a href="{{ route('certificates.index') }}" class="od-nav-dropdown-link">
                            <i class="fas fa-certificate w-4 text-center mr-2" style="color: var(--od-muted);"></i> Certificates
                        </a>
                        <hr class="my-1 od-nav-divider">
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="od-nav-logout">
                                <i class="fas fa-sign-out-alt w-4 text-center mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <!-- Guest Menu -->
                <a href="{{ route('login') }}" class="od-nav-btn-ghost hidden sm:inline-flex">
                    Login
                </a>
                <a href="{{ route('register') }}" class="od-nav-btn-navy hidden sm:inline-flex">
                    Register
                </a>
                @endauth

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        :aria-expanded="mobileMenuOpen.toString()"
                        aria-label="Toggle navigation menu"
                        class="od-nav-icon-btn">
                        <i class="fas fa-bars text-xl" x-show="!mobileMenuOpen"></i>
                        <i class="fas fa-times text-xl" x-show="mobileMenuOpen" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        @click.away="mobileMenuOpen = false"
        x-cloak
        class="od-nav-mobile-menu md:hidden max-h-[calc(100vh-4rem)] overflow-y-auto overscroll-contain">
        <div class="px-4 pt-2 pb-4 space-y-1">
            <a href="{{ url('/') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-link {{ request()->routeIs('home') ? 'od-nav-mobile-link-active' : '' }}">
                Home
            </a>
            <a href="{{ route('courses.index') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-link {{ request()->routeIs('courses.*') ? 'od-nav-mobile-link-active' : '' }}">
                Courses
            </a>
            <a href="{{ route('about') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-link {{ request()->routeIs('about') ? 'od-nav-mobile-link-active' : '' }}">
                About Us
            </a>

            <!-- Mobile More Section -->
            <div x-data="{ moreExpanded: false }" class="border-t od-nav-divider mt-1 pt-1">
                <button @click="moreExpanded = !moreExpanded" class="w-full flex items-center justify-between od-nav-mobile-link">
                    <span>More</span>
                    <i class="fas fa-chevron-down text-xs transition-transform" style="color: var(--od-muted);" :class="moreExpanded ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="moreExpanded" x-collapse x-cloak class="pl-4 space-y-1">
                    <a href="{{ route('search') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-sublink">
                        <i class="fas fa-search w-4 text-center"></i> Search
                    </a>
                    <a href="{{ route('campus') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-sublink">
                        <i class="fas fa-university w-4 text-center"></i> Campus
                    </a>
                    <a href="{{ route('events') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-sublink">
                        <i class="fas fa-calendar-alt w-4 text-center"></i> Events
                    </a>
                    <a href="{{ route('testimonials') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-sublink">
                        <i class="fas fa-star w-4 text-center"></i> Testimonials
                    </a>
                    <a href="{{ route('faq') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-sublink">
                        <i class="fas fa-question-circle w-4 text-center"></i> FAQ
                    </a>
                    <a href="{{ route('contact') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-sublink">
                        <i class="fas fa-envelope w-4 text-center"></i> Contact
                    </a>
                </div>
            </div>

            @auth
            <div class="border-t od-nav-divider pt-3 mt-2">
                <div class="flex items-center px-3 mb-3">
                    <div class="od-nav-avatar od-nav-avatar-lg mr-3">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-medium" style="color: var(--od-fg);">{{ auth()->user()->name }}</div>
                        <div class="text-sm" style="color: var(--od-muted);">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-link">
                    <i class="fas fa-th-large mr-2 w-5 text-center" style="color: var(--od-muted);"></i> Dashboard
                </a>
                <a href="{{ route('enrollments.index') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-link">
                    <i class="fas fa-book mr-2 w-5 text-center" style="color: var(--od-muted);"></i> My Courses
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-link">
                    <i class="fas fa-tachometer-alt mr-2 w-5 text-center" style="color: var(--od-muted);"></i> Admin Panel
                </a>
                @endif
                @if(auth()->user()->isInstructor())
                <a href="{{ route('instructor.dashboard') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-link">
                    <i class="fas fa-chalkboard-teacher mr-2 w-5 text-center" style="color: var(--od-muted);"></i> Instructor Panel
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="od-nav-mobile-link w-full text-left" style="color: var(--od-danger);">
                        <i class="fas fa-sign-out-alt mr-2 w-5 text-center"></i> Logout
                    </button>
                </form>
            </div>
            @else
            <div class="border-t od-nav-divider pt-3 mt-2 space-y-2">
                <a href="{{ route('login') }}" @click="mobileMenuOpen = false" class="od-nav-mobile-link" style="color: var(--od-navy);">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
                <a href="{{ route('register') }}" @click="mobileMenuOpen = false" class="od-nav-btn-navy w-full justify-center">
                    <i class="fas fa-user-plus mr-2"></i> Register
                </a>
            </div>
            @endauth
        </div>
    </div>
</nav>
