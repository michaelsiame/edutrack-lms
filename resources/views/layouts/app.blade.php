<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Edutrack Computer Training College'))</title>

    <!-- Meta Tags -->
    <meta name="description" content="@yield('meta_description', 'Edutrack Computer Training College - Quality computer training in Zambia. Transform your future with industry-recognized certification programs.')">
    <meta name="keywords" content="computer training, Zambia, courses, certification, web development, digital marketing, Kalomo">
    <meta name="author" content="Edutrack Computer Training College">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:description" content="@yield('meta_description', 'Quality computer training institution in Zambia')">
    <meta property="og:image" content="@yield('og_image', asset('assets/images/logo.png'))">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logo.png') }}">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/tailwind.css') }}">

    <!-- Font Awesome (local for reliability) -->
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">display=swap" rel="stylesheet">
    <style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif}</style>

    <!-- Design Tokens + Open Design Components -->
    <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/whatsapp-button.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/ui-fixes.css') }}">

    <!-- Custom Styles -->
    <style>
    :root {
        --primary-color: var(--od-navy);
        --secondary-color: var(--od-accent);
        --primary-dark: var(--od-navy-hover);
        --secondary-dark: var(--od-accent-hover);
    }
    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .btn-primary {
        background-color: var(--od-navy);
        color: white;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background-color: var(--od-navy-hover);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px color-mix(in oklch, var(--od-navy), transparent 60%);
    }
    .btn-secondary {
        background-color: var(--od-accent);
        color: var(--od-fg);
        transition: all 0.3s ease;
    }
    .btn-secondary:hover {
        background-color: var(--od-accent-hover);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px color-mix(in oklch, var(--od-accent), transparent 60%);
    }
    .nav-link {
        position: relative;
        transition: color 0.3s ease;
    }
    .nav-link::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background-color: var(--od-accent);
        transition: width 0.3s ease;
    }
    .nav-link:hover::after,
    .nav-link.active::after {
        width: 100%;
    }
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .animate-slide-up {
        opacity: 1;
        transform: translateY(0);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    /* Enhanced animation when JS is available */
    html.js-enabled .animate-slide-up {
        opacity: 0;
        transform: translateY(30px);
    }
    html.js-enabled .animate-slide-up.animated {
        opacity: 1;
        transform: translateY(0);
    }
    .animate-fade-in {
        opacity: 1;
        animation: none;
    }
    html.js-enabled .animate-fade-in {
        opacity: 0;
        animation: fadeIn 1s ease-out forwards;
    }
    @keyframes fadeIn { to { opacity: 1; } }
    [x-cloak] { display: none !important; }
    </style>

    @stack('styles')
    <script>document.documentElement.classList.add('js-enabled');</script>
</head>
<body style="background: var(--od-bg);">

    <!-- Top Bar -->
    <div style="background: var(--od-fg); color: white;" class="py-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center text-xs sm:text-sm gap-2 sm:gap-0">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-4 gap-y-1">
                    @php
                    $sitePhone = \App\Models\SystemSetting::get('site_phone');
                    @endphp
                    @if($sitePhone)
                    <span class="hidden md:flex items-center">
                        <i class="fas fa-phone mr-1"></i>
                        {{ $sitePhone }}
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-3 sm:gap-4">
                    @php $topBarEmail = \App\Models\SystemSetting::get('site_email'); @endphp
                    @if($topBarEmail)
                    <span class="hidden sm:flex items-center">
                        <i class="fas fa-envelope mr-1"></i>
                        {{ $topBarEmail }}
                    </span>
                    @endif
                    <div class="flex items-center space-x-2">
                        <a href="#" class="transition" style="color: white;" onmouseover="this.style.color='var(--od-accent)'" onmouseout="this.style.color='white'"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="transition" style="color: white;" onmouseover="this.style.color='var(--od-accent)'" onmouseout="this.style.color='white'"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="transition" style="color: white;" onmouseover="this.style.color='var(--od-accent)'" onmouseout="this.style.color='white'"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    @include('layouts.navigation')

    <!-- Active Promotions Banner -->
    <x-promotion-banner />

    <!-- Flash Messages / Toast Notifications -->
    @if(session('success') || session('error') || session('warning') || session('info'))
    <div class="fixed top-4 right-4 z-[60] w-full max-w-sm space-y-2 px-4" x-data>
        @if(session('success'))
        <x-toast type="success" :message="session('success')" />
        @endif
        @if(session('error'))
        <x-toast type="error" :message="session('error')" />
        @endif
        @if(session('warning'))
        <x-toast type="warning" :message="session('warning')" />
        @endif
        @if(session('info'))
        <x-toast type="info" :message="session('info')" />
        @endif
    </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    @include('layouts.footer')

    <!-- Scroll to Top Button -->
    <button id="scrollToTop" class="fixed bottom-8 right-8 w-12 h-12 rounded-full shadow-lg transition-all duration-300 hidden z-40" style="background: var(--od-accent); color: white;" onmouseover="this.style.background='var(--od-accent-hover)'" onmouseout="this.style.background='var(--od-accent)'">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- WhatsApp Floating Button -->
    <div class="whatsapp-float-container">
        <a href="https://chat.whatsapp.com/HkqCis0yejbJybxyTbsG2e?mode=wwt"
           target="_blank"
           rel="noopener noreferrer"
           class="whatsapp-button"
           aria-label="Join our WhatsApp Group">
            <div class="ripple"></div>
            <i class="fab fa-whatsapp"></i>
            <span class="notification-badge">New</span>
            <span class="whatsapp-tooltip">Join Our Community!</span>
        </a>
    </div>

    <!-- Scroll to Top Script -->
    <script>
    const scrollToTopBtn = document.getElementById('scrollToTop');
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.remove('hidden');
        } else {
            scrollToTopBtn.classList.add('hidden');
        }
    });
    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Scroll animations
    document.addEventListener('DOMContentLoaded', function() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.animate-slide-up, .animate-fade-in').forEach(el => observer.observe(el));
    });
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.13.5/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    @auth
    <script src="{{ asset('assets/js/session-heartbeat.js') }}"></script>
    @endauth
    @stack('scripts')
</body>
</html>
