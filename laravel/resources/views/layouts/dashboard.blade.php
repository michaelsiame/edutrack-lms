<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Edutrack LMS'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="flex min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md hidden md:block">
            <div class="p-4 border-b">
                <a href="{{ url('/') }}" class="text-xl font-bold text-blue-600">Edutrack LMS</a>
            </div>
            <nav class="p-4 space-y-1">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i class="fas fa-tachometer-alt mr-2 w-5"></i>Dashboard
                        </a>
                        <a href="{{ route('admin.courses.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.courses.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i class="fas fa-book mr-2 w-5"></i>Courses
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i class="fas fa-users mr-2 w-5"></i>Users
                        </a>
                    @elseif(auth()->user()->isInstructor())
                        <a href="{{ route('instructor.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('instructor.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i class="fas fa-tachometer-alt mr-2 w-5"></i>Dashboard
                        </a>
                        <a href="{{ route('instructor.courses.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('instructor.courses.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i class="fas fa-book mr-2 w-5"></i>My Courses
                        </a>
                        <a href="{{ route('instructor.quizzes.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('instructor.quizzes.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i class="fas fa-question-circle mr-2 w-5"></i>Quizzes
                        </a>
                        <a href="{{ route('instructor.assignments.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('instructor.assignments.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i class="fas fa-tasks mr-2 w-5"></i>Assignments
                        </a>
                    @elseif(auth()->user()->isStudent())
                        <a href="{{ route('student.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('student.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i class="fas fa-tachometer-alt mr-2 w-5"></i>Dashboard
                        </a>
                        <a href="{{ route('student.courses.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('student.courses.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i class="fas fa-book mr-2 w-5"></i>My Courses
                        </a>
                    @elseif(auth()->user()->isFinance())
                        <a href="{{ route('finance.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('finance.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i class="fas fa-tachometer-alt mr-2 w-5"></i>Dashboard
                        </a>
                        <a href="{{ route('finance.transactions') }}" class="block px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('finance.transactions') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <i class="fas fa-money-bill-wave mr-2 w-5"></i>Payments
                        </a>
                    @endif
                @endauth
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ auth()->user()->name ?? 'Guest' }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
