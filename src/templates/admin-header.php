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
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom Admin Styles -->
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link.active {
            background-color: #EBF4FF;
            color: #2E70DA;
            border-left: 4px solid #2E70DA;
        }

        /* Button Styles - Base */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
        }

        /* Button variants - work with or without .btn base class */
        .btn-primary,
        .btn.btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            background-color: #2563EB !important;
            color: white !important;
        }

        .btn-primary:hover,
        .btn.btn-primary:hover {
            background-color: #1D4ED8 !important;
        }

        .btn-secondary,
        .btn.btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            background-color: #f3f4f6 !important;
            color: #374151 !important;
            border: 1px solid #d1d5db !important;
        }

        .btn-secondary:hover,
        .btn.btn-secondary:hover {
            background-color: #e5e7eb !important;
        }

        .btn-danger,
        .btn.btn-danger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            background-color: #dc2626 !important;
            color: white !important;
        }

        .btn-danger:hover,
        .btn.btn-danger:hover {
            background-color: #b91c1c !important;
        }

        .btn-success,
        .btn.btn-success {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            background-color: #16a34a !important;
            color: white !important;
        }

        .btn-success:hover,
        .btn.btn-success:hover {
            background-color: #15803d !important;
        }

        .btn-warning,
        .btn.btn-warning {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            background-color: #f59e0b !important;
            color: white !important;
        }

        .btn-warning:hover,
        .btn.btn-warning:hover {
            background-color: #d97706 !important;
        }

        .btn-sm {
            padding: 0.25rem 0.75rem !important;
            font-size: 0.75rem !important;
        }

        .btn-lg {
            padding: 0.75rem 1.5rem !important;
            font-size: 1rem !important;
        }

        /* Ensure submit buttons are visible */
        button[type="submit"],
        input[type="submit"] {
            cursor: pointer;
        }

        /* Fallback for Tailwind primary colors (in case CDN JIT doesn't generate them) */
        .bg-primary-500 { background-color: #2E70DA !important; }
        .bg-primary-600 { background-color: #2563EB !important; }
        .bg-primary-700 { background-color: #1D4ED8 !important; }
        .hover\:bg-primary-600:hover { background-color: #2563EB !important; }
        .hover\:bg-primary-700:hover { background-color: #1D4ED8 !important; }
        .text-primary-500 { color: #2E70DA !important; }
        .text-primary-600 { color: #2563EB !important; }
        .text-primary-700 { color: #1D4ED8 !important; }
        .border-primary-500 { border-color: #2E70DA !important; }
        .border-primary-600 { border-color: #2563EB !important; }
        .ring-primary-500 { --tw-ring-color: #2E70DA !important; }
        .focus\:ring-primary-500:focus { --tw-ring-color: #2E70DA !important; }
        .focus\:border-primary-500:focus { border-color: #2E70DA !important; }
    </style>
</head>
<body class="bg-gray-100">

<div x-data="{ sidebarOpen: true, userMenuOpen: false }" class="flex h-screen overflow-hidden">
    
    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
           class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
        
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-4 bg-gray-800">
            <a href="<?= url('admin/index.php') ?>" class="flex items-center space-x-2">
                <i class="fas fa-graduation-cap text-2xl text-primary-500"></i>
                <span class="text-lg font-bold">Admin Panel</span>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
            
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
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="flex items-center justify-between h-16 px-4">
                
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 hover:text-gray-900 lg:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <div class="flex items-center space-x-4">
                    
                    <!-- Notifications -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500"></span>
                        </button>
                    </div>
                    
                    <!-- User Menu -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100">
                            <img src="<?= getGravatar($_SESSION['user_email'] ?? '') ?>" class="h-8 w-8 rounded-full">
                            <span class="text-sm font-medium text-gray-700 hidden md:block"><?= sanitize($_SESSION['user_first_name'] ?? 'Admin') ?></span>
                            <i class="fas fa-chevron-down text-xs text-gray-600"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="<?= url('profile.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profile
                            </a>
                            <a href="<?= url('dashboard.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-th-large mr-2"></i>Student Dashboard
                            </a>
                            <hr class="my-1">
                            <a href="<?= url('logout.php') ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
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