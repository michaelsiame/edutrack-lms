<?php
/**
 * Admin Panel Header Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Admin Panel' ?> - <?= APP_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= url('assets/images/favicon.png') ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#EBF4FF',
                            100: '#D6E9FF',
                            500: '#2E70DA',
                            600: '#2563EB',
                            700: '#1D4ED8',
                        },
                        secondary: {
                            500: '#F6B745',
                            600: '#D89E2E',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Admin Custom Styles -->
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Critical Inline Styles -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">

<div x-data="{ sidebarOpen: false, userMenuOpen: false }" class="flex h-screen overflow-hidden">

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen"
         x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden transition-opacity duration-300"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col"
           @keydown.escape.window="sidebarOpen = false">
        
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-4 bg-gray-800">
            <a href="<?= url('admin/index.php') ?>" class="flex items-center space-x-2">
                <i class="fas fa-graduation-cap text-2xl text-primary-500"></i>
                <span class="text-lg font-bold">Admin Panel</span>
            </a>
            <button @click="sidebarOpen = false"
                    class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors"
                    aria-label="Close sidebar">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent">
            
            <a href="<?= url('admin/index.php') ?>" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span>Dashboard</span>
            </a>
            
            <!-- Courses -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                    <div class="flex items-center">
                        <i class="fas fa-book w-6"></i>
                        <span>Courses</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
                    <a href="<?= url('admin/courses/index.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">All Courses</a>
                    <a href="<?= url('admin/courses/create.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Create Course</a>
                    <a href="<?= url('admin/courses/categories.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Categories</a>
                </div>
            </div>
            
            <!-- Users -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                    <div class="flex items-center">
                        <i class="fas fa-users w-6"></i>
                        <span>Users</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
                    <a href="<?= url('admin/users/index.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">All Users</a>
                    <a href="<?= url('admin/users/index.php?role=student') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Students</a>
                    <a href="<?= url('admin/users/index.php?role=instructor') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Instructors</a>
                    <a href="<?= url('admin/users/create.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Add User</a>
                </div>
            </div>
            
            <a href="<?= url('admin/enrollments/index.php') ?>" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                <i class="fas fa-clipboard-list w-6"></i>
                <span>Enrollments</span>
            </a>
            
            <!-- Payments -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                    <div class="flex items-center">
                        <i class="fas fa-money-bill-wave w-6"></i>
                        <span>Payments</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
                    <a href="<?= url('admin/payments/index.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">All Payments</a>
                    <a href="<?= url('admin/payments/verify.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Verify Payments</a>
                    <a href="<?= url('admin/payments/reports.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Reports</a>
                </div>
            </div>
            
            <!-- Certificates -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                    <div class="flex items-center">
                        <i class="fas fa-certificate w-6"></i>
                        <span>Certificates</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
                    <a href="<?= url('admin/certificates/index.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">All Certificates</a>
                    <a href="<?= url('admin/certificates/issue.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Issue Certificate</a>
                    <a href="<?= url('admin/certificates/verify.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Verify</a>
                </div>
            </div>
            
            <a href="<?= url('admin/analytics/index.php') ?>" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                <i class="fas fa-chart-line w-6"></i>
                <span>Analytics</span>
            </a>
            
            <!-- Settings -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                    <div class="flex items-center">
                        <i class="fas fa-cog w-6"></i>
                        <span>Settings</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
                    <a href="<?= url('admin/settings/index.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">General</a>
                    <a href="<?= url('admin/settings/payment-gateways.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Payment Gateways</a>
                    <a href="<?= url('admin/settings/email.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Email Settings</a>
                </div>
            </div>
            
            <hr class="my-4 border-gray-700">
            
            <a href="<?= url() ?>" target="_blank" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                <i class="fas fa-globe w-6"></i>
                <span>View Site</span>
            </a>
            
        </nav>
        
    </aside>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top Navigation -->
        <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-center justify-between h-16 px-4 lg:px-6">

                <!-- Left side: Menu button + Page breadcrumb -->
                <div class="flex items-center gap-4">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = true"
                            class="lg:hidden p-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                            aria-label="Open menu">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Page title / Breadcrumb (hidden on small screens) -->
                    <div class="hidden sm:block">
                        <h1 class="text-lg font-semibold text-gray-800"><?= $page_title ?? 'Admin' ?></h1>
                    </div>
                </div>

                <!-- Right side: Actions -->
                <div class="flex items-center gap-2 sm:gap-4">

                    <!-- Quick search (desktop only) -->
                    <div class="hidden lg:block relative">
                        <input type="text"
                               placeholder="Search..."
                               class="w-64 pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>

                    <!-- Notifications -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="relative p-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                                aria-label="Notifications">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute top-1 right-1 flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                            </span>
                        </button>
                        <!-- Notifications dropdown would go here -->
                    </div>

                    <!-- User Menu -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="flex items-center gap-2 p-1.5 pr-3 rounded-lg hover:bg-gray-100 transition-colors"
                                aria-label="User menu">
                            <img src="<?= getGravatar($_SESSION['user_email'] ?? '') ?>"
                                 alt="User avatar"
                                 class="h-8 w-8 rounded-full ring-2 ring-gray-200">
                            <span class="hidden sm:block text-sm font-medium text-gray-700"><?= sanitize($_SESSION['user_first_name'] ?? 'Admin') ?></span>
                            <i class="fas fa-chevron-down text-xs text-gray-500 hidden sm:block"></i>
                        </button>
                        <div x-show="open"
                             @click.away="open = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 py-1 z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900"><?= sanitize(($_SESSION['user_first_name'] ?? '') . ' ' . ($_SESSION['user_last_name'] ?? '')) ?></p>
                                <p class="text-xs text-gray-500 truncate"><?= sanitize($_SESSION['user_email'] ?? '') ?></p>
                            </div>
                            <a href="<?= url('profile.php') ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-user w-5 text-gray-400"></i>
                                <span>My Profile</span>
                            </a>
                            <a href="<?= url('dashboard.php') ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-th-large w-5 text-gray-400"></i>
                                <span>Student Dashboard</span>
                            </a>
                            <a href="<?= url('admin/settings/index.php') ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-cog w-5 text-gray-400"></i>
                                <span>Settings</span>
                            </a>
                            <hr class="my-1 border-gray-100">
                            <a href="<?= url('logout.php') ?>" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i class="fas fa-sign-out-alt w-5"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto">
            <?php 
            // Display flash messages
            if ($flash = getFlash()): 
                echo $flash;
            endif;
            ?>