@php
$currentRoute = Route::currentRouteName() ?? '';
$userRoles = [];
$hasMultipleRoles = false;
$activeRole = null;

if (auth()->check()) {
    $user = auth()->user();
    $userRoles = $user->roles->pluck('name')->toArray();
    $hasMultipleRoles = count($userRoles) > 1;
    // Get active role from session or default to first
    $activeRole = session('active_role', $userRoles[0] ?? null);
}
@endphp

<nav class="bg-white shadow-md sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 md:h-20">

            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center min-w-0">
                <a href="{{ url('/') }}" class="flex items-center min-w-0">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Edutrack Logo" class="h-10 md:h-12 w-auto mr-2 md:mr-3 shrink-0">
                    <div class="hidden sm:flex flex-col min-w-0">
                        <span class="text-lg md:text-xl font-bold text-primary-600 leading-tight">Edutrack</span>
                        <span class="text-[10px] md:text-xs text-gray-600 truncate">TEVETA REGISTERED</span>
                    </div>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-6 lg:space-x-8">
                <a href="{{ url('/') }}" class="nav-link text-gray-700 hover:text-primary-600 font-medium {{ request()->routeIs('home') ? 'active text-primary-600' : '' }}">
                    Home
                </a>
                <a href="{{ route('courses.index') }}" class="nav-link text-gray-700 hover:text-primary-600 font-medium {{ request()->routeIs('courses.*') ? 'active text-primary-600' : '' }}">
                    Courses
                </a>
                <a href="{{ route('about') }}" class="nav-link text-gray-700 hover:text-primary-600 font-medium {{ request()->routeIs('about') ? 'active text-primary-600' : '' }}">
                    About Us
                </a>
                <a href="{{ route('campus') }}" class="nav-link text-gray-700 hover:text-primary-600 font-medium {{ request()->routeIs('campus') ? 'active text-primary-600' : '' }}">
                    <i class="fas fa-university mr-1"></i>Campus
                </a>
                <a href="{{ route('events') }}" class="nav-link text-gray-700 hover:text-primary-600 font-medium {{ request()->routeIs('events') ? 'active text-primary-600' : '' }}">
                    <i class="fas fa-calendar-alt mr-1"></i>Events
                </a>
                <a href="{{ route('contact') }}" class="nav-link text-gray-700 hover:text-primary-600 font-medium {{ request()->routeIs('contact') ? 'active text-primary-600' : '' }}">
                    Contact
                </a>

                @auth
                    <!-- Logged In Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-primary-600 font-medium focus:outline-none">
                            <div class="h-8 w-8 rounded-full mr-2 border-2 border-primary-500 bg-primary-100 flex items-center justify-center text-primary-700 font-bold text-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span>{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50">
                            @if($hasMultipleRoles)
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Roles</p>
                                    <div class="mt-2 space-y-1">
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
                                            <div class="w-full flex items-center px-2 py-1.5 text-sm rounded-md text-gray-600">
                                                <i class="fas {{ $roleIcon }} w-5 {{ $roleColor }}"></i>
                                                <span class="ml-2">{{ ucfirst($role) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt mr-2"></i> Admin Panel
                                </a>
                            @endif
                            @if(auth()->user()->isInstructor())
                                <a href="{{ route('instructor.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-chalkboard-teacher mr-2"></i> Instructor Panel
                                </a>
                            @endif
                            @if(auth()->user()->isFinance())
                                <a href="{{ route('finance.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-money-bill-wave mr-2"></i> Finance Panel
                                </a>
                            @endif

                            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-th-large mr-2"></i> Dashboard
                            </a>
                            <a href="{{ route('enrollments.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-book mr-2"></i> My Courses
                            </a>
                            <hr class="my-1">
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Guest Menu -->
                    <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                    <a href="{{ route('register') }}" class="btn-secondary px-6 py-2 rounded-md font-medium">
                        <i class="fas fa-user-plus mr-1"></i> Register
                    </a>
                @endauth
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        :aria-expanded="mobileMenuOpen.toString()"
                        aria-label="Toggle navigation menu"
                        class="text-gray-700 hover:text-primary-600 focus:outline-none p-2 rounded-md hover:bg-gray-100 transition">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-nav-menu"
         x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         @click.away="mobileMenuOpen = false"
         x-cloak
         class="md:hidden bg-white border-t max-h-[calc(100vh-4rem)] overflow-y-auto overscroll-contain">
        <div class="px-2 pt-2 pb-4 space-y-1">
            <a href="{{ url('/') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('home') ? 'text-primary-600 bg-primary-50' : '' }}">
                Home
            </a>
            <a href="{{ route('courses.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('courses.*') ? 'text-primary-600 bg-primary-50' : '' }}">
                Courses
            </a>
            <a href="{{ route('about') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('about') ? 'text-primary-600 bg-primary-50' : '' }}">
                About Us
            </a>
            <a href="{{ route('campus') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('campus') ? 'text-primary-600 bg-primary-50' : '' }}">
                <i class="fas fa-university mr-2 text-primary-600"></i> Campus
            </a>
            <a href="{{ route('events') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('events') ? 'text-primary-600 bg-primary-50' : '' }}">
                <i class="fas fa-calendar-alt mr-2 text-primary-600"></i> Events
            </a>
            <a href="{{ route('contact') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 {{ request()->routeIs('contact') ? 'text-primary-600 bg-primary-50' : '' }}">
                Contact
            </a>

            @auth
                <div class="border-t pt-4 mt-4">
                    <div class="flex items-center px-3 mb-3">
                        <div class="h-10 w-10 rounded-full mr-3 border-2 border-primary-500 bg-primary-100 flex items-center justify-center text-primary-700 font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ auth()->user()->name }}</div>
                            <div class="text-sm text-gray-500">{{ auth()->user()->email }}</div>
                        </div>
                    </div>

                    <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-th-large mr-2"></i> Dashboard
                    </a>
                    <a href="{{ route('enrollments.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-book mr-2"></i> My Courses
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-gray-50">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            @else
                <div class="border-t pt-4 mt-4 space-y-2">
                    <a href="{{ route('login') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-primary-600 hover:bg-primary-50">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </a>
                    <a href="{{ route('register') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium bg-secondary-500 text-gray-900 hover:bg-secondary-600">
                        <i class="fas fa-user-plus mr-2"></i> Register
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
