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
                        <span class="text-lg font-bold text-primary-600 leading-tight">Edutrack</span>
                        <span class="text-[10px] text-gray-600 truncate">COMPUTER TRAINING</span>
                    </div>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="{{ url('/') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('home') ? 'text-primary-600 bg-primary-50' : '' }}">
                    Home
                </a>
                <a href="{{ route('courses.index') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('courses.*') ? 'text-primary-600 bg-primary-50' : '' }}">
                    Courses
                </a>
                <a href="{{ route('about') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('about') ? 'text-primary-600 bg-primary-50' : '' }}">
                    About
                </a>

                <!-- More Dropdown -->
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button @click="open = !open" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 flex items-center {{ request()->routeIs('campus','events','contact','faq','testimonials') ? 'text-primary-600 bg-primary-50' : '' }}">
                        More
                        <i class="fas fa-chevron-down ml-1 text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1" x-cloak class="absolute left-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                        <a href="{{ route('campus') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600">
                            <i class="fas fa-university w-5 text-gray-400"></i> Campus
                        </a>
                        <a href="{{ route('events') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600">
                            <i class="fas fa-calendar-alt w-5 text-gray-400"></i> Events
                        </a>
                        <a href="{{ route('testimonials') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600">
                            <i class="fas fa-star w-5 text-gray-400"></i> Testimonials
                        </a>
                        <a href="{{ route('faq') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600">
                            <i class="fas fa-question-circle w-5 text-gray-400"></i> FAQ
                        </a>
                        <a href="{{ route('contact') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600">
                            <i class="fas fa-envelope w-5 text-gray-400"></i> Contact
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="flex items-center space-x-2">
                <!-- Search Icon Button -->
                <a href="{{ route('search') }}" class="hidden sm:flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:text-primary-600 hover:bg-gray-100 transition" title="Search courses">
                    <i class="fas fa-search text-sm"></i>
                </a>

                @auth
                    <!-- Logged In Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-primary-600 font-medium focus:outline-none px-2 py-1 rounded-md hover:bg-gray-50">
                            <div class="h-8 w-8 rounded-full border-2 border-primary-500 bg-primary-100 flex items-center justify-center text-primary-700 font-bold text-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="hidden md:inline text-sm">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                            @if($hasMultipleRoles)
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Roles</p>
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
                                                    'admin' => 'text-red-600',
                                                    'instructor' => 'text-blue-600',
                                                    'student' => 'text-green-600',
                                                    'finance' => 'text-yellow-600',
                                                    default => 'text-gray-600'
                                                };
                                            @endphp
                                            <div class="flex items-center px-2 py-1 text-sm text-gray-600">
                                                <i class="fas {{ $roleIcon }} w-5 {{ $roleColor }}"></i>
                                                <span class="ml-2">{{ ucfirst($role) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600">
                                    <i class="fas fa-tachometer-alt mr-2 w-4"></i> Admin Panel
                                </a>
                            @endif
                            @if(auth()->user()->isInstructor())
                                <a href="{{ route('instructor.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600">
                                    <i class="fas fa-chalkboard-teacher mr-2 w-4"></i> Instructor Panel
                                </a>
                            @endif
                            @if(auth()->user()->isFinance())
                                <a href="{{ route('finance.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600">
                                    <i class="fas fa-money-bill-wave mr-2 w-4"></i> Finance Panel
                                </a>
                            @endif

                            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600">
                                <i class="fas fa-th-large mr-2 w-4"></i> Dashboard
                            </a>
                            <a href="{{ route('enrollments.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600">
                                <i class="fas fa-book mr-2 w-4"></i> My Courses
                            </a>
                            <hr class="my-1 border-gray-100">
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                    <i class="fas fa-sign-out-alt mr-2 w-4"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Guest Menu -->
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center text-sm text-primary-600 hover:text-primary-700 font-medium px-3 py-2">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center text-sm bg-primary-600 text-white hover:bg-primary-700 font-medium px-4 py-2 rounded-lg transition">
                        Register
                    </a>
                @endauth

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                            :aria-expanded="mobileMenuOpen.toString()"
                            aria-label="Toggle navigation menu"
                            class="text-gray-700 hover:text-primary-600 focus:outline-none p-2 rounded-md hover:bg-gray-100 transition">
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
         class="md:hidden bg-white border-t max-h-[calc(100vh-4rem)] overflow-y-auto overscroll-contain">
        <div class="px-4 pt-2 pb-4 space-y-1">
            <a href="{{ url('/') }}" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('home') ? 'text-primary-600 bg-primary-50' : '' }}">
                Home
            </a>
            <a href="{{ route('courses.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('courses.*') ? 'text-primary-600 bg-primary-50' : '' }}">
                Courses
            </a>
            <a href="{{ route('about') }}" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('about') ? 'text-primary-600 bg-primary-50' : '' }}">
                About Us
            </a>

            <!-- Mobile More Section -->
            <div x-data="{ moreExpanded: false }" class="border-t border-gray-100 mt-1 pt-1">
                <button @click="moreExpanded = !moreExpanded" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                    <span>More</span>
                    <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform" :class="moreExpanded ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="moreExpanded" x-collapse x-cloak class="pl-4 space-y-1">
                    <a href="{{ route('search') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-search mr-2 w-4"></i> Search
                    </a>
                    <a href="{{ route('campus') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-university mr-2 w-4"></i> Campus
                    </a>
                    <a href="{{ route('events') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-calendar-alt mr-2 w-4"></i> Events
                    </a>
                    <a href="{{ route('testimonials') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-star mr-2 w-4"></i> Testimonials
                    </a>
                    <a href="{{ route('faq') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-question-circle mr-2 w-4"></i> FAQ
                    </a>
                    <a href="{{ route('contact') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-envelope mr-2 w-4"></i> Contact
                    </a>
                </div>
            </div>

            @auth
                <div class="border-t border-gray-100 pt-3 mt-2">
                    <div class="flex items-center px-3 mb-3">
                        <div class="h-10 w-10 rounded-full mr-3 border-2 border-primary-500 bg-primary-100 flex items-center justify-center text-primary-700 font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ auth()->user()->name }}</div>
                            <div class="text-sm text-gray-500">{{ auth()->user()->email }}</div>
                        </div>
                    </div>

                    <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-th-large mr-2 w-5"></i> Dashboard
                    </a>
                    <a href="{{ route('enrollments.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-book mr-2 w-5"></i> My Courses
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                            <i class="fas fa-tachometer-alt mr-2 w-5"></i> Admin Panel
                        </a>
                    @endif
                    @if(auth()->user()->isInstructor())
                        <a href="{{ route('instructor.dashboard') }}" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                            <i class="fas fa-chalkboard-teacher mr-2 w-5"></i> Instructor Panel
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2.5 rounded-lg text-base font-medium text-red-600 hover:bg-gray-50">
                            <i class="fas fa-sign-out-alt mr-2 w-5"></i> Logout
                        </button>
                    </form>
                </div>
            @else
                <div class="border-t border-gray-100 pt-3 mt-2 space-y-2">
                    <a href="{{ route('login') }}" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-primary-600 hover:bg-primary-50">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </a>
                    <a href="{{ route('register') }}" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium bg-primary-600 text-white hover:bg-primary-700 text-center">
                        <i class="fas fa-user-plus mr-2"></i> Register
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
