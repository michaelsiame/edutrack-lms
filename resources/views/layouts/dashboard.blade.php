<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}" x-data="{ sidebarOpen: window.innerWidth >= 768 ? (localStorage.getItem('sidebarOpen') !== 'false') : false, darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val)); $watch('darkMode', val => localStorage.setItem('darkMode', val)); window.addEventListener('resize', () => { if(window.innerWidth >= 768) sidebarOpen = localStorage.getItem('sidebarOpen') !== 'false'; })" :class="{'dark': darkMode }">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>@yield('title', config('app.name','Edutrack LMS'))</title>

 <!-- Fonts & Icons -->
 <!-- Self-hosted Inter font (no external CDN dependency) -->
 <link rel="stylesheet" href="{{ asset('assets/css/inter-font.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">

 <!-- Tailwind CSS -->
 <link rel="stylesheet" href="{{ asset('assets/css/tailwind.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">

 <!-- Dashboard Custom CSS -->
 <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
 <link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">

 @stack('styles')
</head>
<body class="font-sans antialiased text-gray-800 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-200" style="background: var(--od-bg);">
 <div class="flex h-screen overflow-hidden">

 <!-- Mobile Sidebar Overlay -->
 <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity.duration.300ms class="fixed inset-0 z-40 bg-black/50 md:hidden" style="display: none;"></div>

 <!-- Sidebar -->
 <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" class="fixed md:relative z-50 h-full bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-transform duration-300 ease-in-out flex flex-col w-[260px] flex-shrink-0">
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
 $role = $user->isAdmin() ?'admin' : ($user->isInstructor() ?'instructor' : ($user->isStudent() ?'student' :'finance'));
 @endphp

 @if($user->isAdmin())
 <x-dashboard-nav-item route="admin.dashboard" icon="fa-tachometer-alt" label="Dashboard" />
 @if(Route::has('admin.users.index'))
 <x-dashboard-nav-item route="admin.users.index" icon="fa-users" label="Users" />
 @endif
 @if(Route::has('admin.courses.index'))
 <x-dashboard-nav-item route="admin.courses.index" icon="fa-book" label="Courses" />
 @endif
 @if(Route::has('admin.intakes.index'))
 <x-dashboard-nav-item route="admin.intakes.index" icon="fa-calendar-alt" label="Intakes" />
 @endif
 @if(Route::has('admin.enrollments.index'))
 <x-dashboard-nav-item route="admin.enrollments.index" icon="fa-user-graduate" label="Enrollments" />
 @endif
 @if(Route::has('admin.payments.index'))
 <x-dashboard-nav-item route="admin.payments.index" icon="fa-money-bill-wave" label="Payments" />
 @endif
 @if(Route::has('admin.announcements.index'))
 <x-dashboard-nav-item route="admin.announcements.index" icon="fa-bullhorn" label="Announcements" />
 @endif
 @if(Route::has('admin.events.index'))
 <x-dashboard-nav-item route="admin.events.index" icon="fa-calendar-alt" label="Events" />
 @endif
 @if(Route::has('admin.photos.index'))
 <x-dashboard-nav-item route="admin.photos.index" icon="fa-images" label="Photos" />
 @endif
 @if(Route::has('admin.team.index'))
 <x-dashboard-nav-item route="admin.team.index" icon="fa-users" label="Team" />
 @endif
 @if(Route::has('admin.testimonials.index'))
 <x-dashboard-nav-item route="admin.testimonials.index" icon="fa-comment-alt" label="Testimonials" />
 @endif
 @if(Route::has('admin.templates.index'))
 <x-dashboard-nav-item route="admin.templates.index" icon="fa-envelope" label="Email Templates" />
 @endif
 @if(Route::has('admin.badges.index'))
 <x-dashboard-nav-item route="admin.badges.index" icon="fa-medal" label="Badges" />
 @endif
 @if(Route::has('admin.newsletter.index'))
 <x-dashboard-nav-item route="admin.newsletter.index" icon="fa-newspaper" label="Newsletter" />
 @endif
 @if(Route::has('admin.promotions.index'))
 <x-dashboard-nav-item route="admin.promotions.index" icon="fa-tags" label="Promotions" />
 @endif
 <x-dashboard-nav-item route="admin.reports" icon="fa-chart-bar" label="Reports" />
 <x-dashboard-nav-item route="admin.settings" icon="fa-cog" label="Settings" />
 @elseif($user->isInstructor())
 <x-dashboard-nav-item route="instructor.dashboard" icon="fa-tachometer-alt" label="Dashboard" />
 @if(Route::has('instructor.courses.index'))
 <x-dashboard-nav-item route="instructor.courses.index" icon="fa-book" label="My Courses" />
 @endif
 @if(Route::has('instructor.assignments.index'))
 <x-dashboard-nav-item route="instructor.assignments.index" icon="fa-tasks" label="Assignments" />
 @endif
 @if(Route::has('instructor.quizzes.index'))
 <x-dashboard-nav-item route="instructor.quizzes.index" icon="fa-question-circle" label="Quizzes" />
 @endif
 <x-dashboard-nav-item route="instructor.submissions" icon="fa-clipboard-check" label="Submissions" />
 <x-dashboard-nav-item route="instructor.progress" icon="fa-user-graduate" label="Class Progress" />
 <x-dashboard-nav-item route="instructor.analytics" icon="fa-chart-line" label="Analytics" />
 @elseif($user->isStudent())
 <x-dashboard-nav-item route="student.dashboard" icon="fa-tachometer-alt" label="Dashboard" />
 <x-dashboard-nav-item route="enrollments.index" icon="fa-book-open" label="My Courses" />
 @if(Route::has('student.assignments.index'))
 <x-dashboard-nav-item route="student.assignments.index" icon="fa-tasks" label="Assignments" />
 @endif
 @if(Route::has('student.notes.index'))
 <x-dashboard-nav-item route="student.notes.index" icon="fa-sticky-note" label="My Notes" />
 @endif
 @if(Route::has('student.schedule'))
 <x-dashboard-nav-item route="student.schedule" icon="fa-calendar-alt" label="Schedule" />
 @endif
 @if(Route::has('student.quizzes.index'))
 <x-dashboard-nav-item route="student.quizzes.index" icon="fa-clipboard-list" label="Quizzes" />
 @endif
 <x-dashboard-nav-item route="student.progress" icon="fa-chart-pie" label="Progress" />
 <x-dashboard-nav-item route="student.certificates" icon="fa-certificate" label="Certificates" />
 <x-dashboard-nav-item route="student.payments" icon="fa-credit-card" label="Payments" />
 @if(Route::has('student.achievements.index'))
 <x-dashboard-nav-item route="student.achievements.index" icon="fa-medal" label="Achievements" />
 @endif
 <x-dashboard-nav-item route="profile.show" icon="fa-user" label="My Profile" />
 @elseif($user->isFinance())
 <x-dashboard-nav-item route="finance.dashboard" icon="fa-tachometer-alt" label="Dashboard" />
 <x-dashboard-nav-item route="finance.transactions" icon="fa-money-bill-wave" label="Transactions" />
 <x-dashboard-nav-item route="finance.payments" icon="fa-check-circle" label="Verify Payments" />
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
 <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20 transition-colors">
 <i class="fas fa-sign-out-alt w-5 text-center"></i>
 Logout
 </button>
 </form>
 </div>
 </aside>

 <!-- Main Content Area -->
 <div class="flex-1 flex flex-col h-full overflow-hidden">
 <!-- Top Bar -->
 <header class="h-16 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4 md:px-6 flex-shrink-0 sticky top-0 z-30">
 <div class="flex items-center gap-4">
 <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors md:hidden" aria-label="Toggle sidebar">
 <i class="fas fa-bars text-lg"></i>
 </button>
 <button @click="sidebarOpen = !sidebarOpen" class="hidden md:flex p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors" aria-label="Toggle sidebar">
 <i class="fas fa-bars text-lg" :class="sidebarOpen ? '' : 'rotate-180'"></i>
 </button>
 <h1 class="text-lg font-semibold text-gray-800 dark:text-white hidden sm:block">@yield('page_title','Dashboard')</h1>
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
 {{ strtoupper(substr(auth()->user()->first_name ?? auth()->user()->username ??'U', 0, 1)) }}
 </div>
 <div class="hidden md:block text-left">
 <div class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ auth()->user()->full_name ?? auth()->user()->username }}</div>
 <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ $role ??'User' }}</div>
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
 <button type="submit" class="w-full text-left px-4 py-2 text-sm text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20">
 <i class="fas fa-sign-out-alt mr-2 w-4"></i> Logout
 </button>
 </form>
 </div>
 </div>
 </div>
 </header>

 <!-- Flash Messages / Toast Notifications -->
 <div
 x-data="{ toasts: [] }"
 x-init="
 @if(session('success')) toasts.push({type:'success', message:'{{ addslashes(session('success')) }}', id:Date.now()}); @endif
 @if(session('error')) toasts.push({type:'error', message:'{{ addslashes(session('error')) }}', id:Date.now()+1}); @endif
 @if(session('warning')) toasts.push({type:'warning', message:'{{ addslashes(session('warning')) }}', id:Date.now()+2}); @endif
 @if(session('info')) toasts.push({type:'info', message:'{{ addslashes(session('info')) }}', id:Date.now()+3}); @endif
 "
 class="fixed top-4 right-4 z-[60] w-full max-w-sm space-y-2 px-4 pointer-events-none"
 >
 <template x-for="toast in toasts" :key="toast.id">
 <div
 x-data="{ show: false }"
 x-init="$nextTick(() => { show = true; setTimeout(() => show = false, 5000); setTimeout(() => toasts = toasts.filter(t => t.id !== toast.id), 5500); })"
 x-show="show"
 x-transition:enter="transition ease-out duration-300"
 x-transition:enter-start="translate-x-full opacity-0"
 x-transition:enter-end="translate-x-0 opacity-100"
 x-transition:leave="transition ease-in duration-200"
 x-transition:leave-start="translate-x-0 opacity-100"
 x-transition:leave-end="translate-x-full opacity-0"
 :class="{
 'od-toast-success': toast.type === 'success',
 'od-toast-error': toast.type === 'error',
 'od-toast-warning': toast.type === 'warning',
 'od-toast-info': toast.type === 'info'
 }"
 class="pointer-events-auto"
 role="alert"
 >
 <i class="fas" :class="{
 'fa-check-circle': toast.type === 'success',
 'fa-exclamation-circle': toast.type === 'error',
 'fa-exclamation-triangle': toast.type === 'warning',
 'fa-info-circle': toast.type === 'info'
 }"></i>
 <span x-text="toast.message"></span>
 <button @click="show = false; setTimeout(() => toasts = toasts.filter(t => t.id !== toast.id), 200)" class="ml-auto opacity-60 hover:opacity-100" aria-label="Dismiss notification">
 <i class="fas fa-times"></i>
 </button>
 </div>
 </template>
 </div>

 <!-- Page Content -->
 <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
 @yield('content')
 </main>
 </div>
 </div>

 <!-- Confirm Modal -->
 <div id="confirmModal" class="od-modal-backdrop" style="display: none;">
 <div class="od-modal-card">
 <div class="text-center mb-4">
 <div class="w-12 h-12 rounded-full bg-warning-100 dark:bg-warning-900/30 text-warning-600 dark:text-warning-400 flex items-center justify-center mx-auto mb-3">
 <i class="fas fa-exclamation-triangle text-xl"></i>
 </div>
 <h3 class="text-lg font-semibold" style="color: var(--od-fg);">Are you sure?</h3>
 <p class="text-sm mt-1 od-meta" id="confirmMessage">This action cannot be undone.</p>
 </div>
 <div class="flex gap-3">
 <button id="confirmCancel" class="od-btn od-btn-secondary flex-1">Cancel</button>
 <button id="confirmOk" class="od-btn od-btn-danger flex-1 bg-danger-600 text-white border-danger-600 hover:bg-danger-700">Delete</button>
 </div>
 </div>
 </div>

 <!-- Alpine.js (self-hosted for speed in Zambia) -->
 <script defer src="{{ asset('assets/js/alpine/collapse.min.js') }}"></script>
 <script defer src="{{ asset('assets/js/alpine/alpine.min.js') }}"></script>
 <!-- Session Heartbeat (prevents 419 CSRF expiry during long study sessions) -->
 <script src="{{ asset('assets/js/session-heartbeat.js') }}"></script>
 <script>
 // Global confirm modal
 window.confirmModal = function(message, onConfirm) {
 const modal = document.getElementById('confirmModal');
 const msgEl = document.getElementById('confirmMessage');
 const okBtn = document.getElementById('confirmOk');
 const cancelBtn = document.getElementById('confirmCancel');
 msgEl.textContent = message || 'This action cannot be undone.';
 modal.style.display = 'grid';
 requestAnimationFrame(() => modal.classList.add('show'));

 function cleanup() {
 modal.classList.remove('show');
 setTimeout(() => modal.style.display = 'none', 200);
 okBtn.removeEventListener('click', handleOk);
 cancelBtn.removeEventListener('click', handleCancel);
 modal.removeEventListener('click', handleBackdrop);
 document.removeEventListener('keydown', handleKey);
 }

 function handleOk() { cleanup(); onConfirm && onConfirm(); }
 function handleCancel() { cleanup(); }
 function handleBackdrop(e) { if (e.target === modal) cleanup(); }
 function handleKey(e) { if (e.key === 'Escape') cleanup(); }

 okBtn.addEventListener('click', handleOk);
 cancelBtn.addEventListener('click', handleCancel);
 modal.addEventListener('click', handleBackdrop);
 document.addEventListener('keydown', handleKey);
 };

 // Auto-replace native confirm on forms with data-confirm
document.addEventListener('DOMContentLoaded', () => {
 document.querySelectorAll('form[data-confirm]').forEach(form => {
 form.addEventListener('submit', e => {
 if (form.dataset.confirmed) return;
 e.preventDefault();
 window.confirmModal(form.dataset.confirm, () => {
 form.dataset.confirmed = 'true';
 form.submit();
 });
 });
 });

 // Button loading states
 document.querySelectorAll('form').forEach(form => {
 form.addEventListener('submit', () => {
 const btn = form.querySelector('button[type="submit"]:not([data-no-loading])');
 if (btn && !btn.disabled) {
 btn.dataset.originalText = btn.innerHTML;
 btn.disabled = true;
 btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1.5"></i> ' + (btn.dataset.loadingText || 'Processing...');
 }
 });
 });
 });
 </script>
 @stack('scripts')
</body>
</html>
