<?php
/**
 * Instructor Panel Header Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Instructor Panel' ?> - <?= APP_NAME ?></title>
    
    <link rel="icon" type="image/png" href="<?= url('assets/images/favicon.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 50: '#EBF4FF', 500: '#2E70DA', 600: '#2563EB', 700: '#1D4ED8' },
                        secondary: { 500: '#F6B745', 600: '#D89E2E' }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100">

<div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
    
    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"></div>
    
    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
           class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static">
        
        <div class="flex items-center justify-between h-16 px-4 bg-gray-800">
            <a href="<?= url('instructor/index.php') ?>" class="flex items-center space-x-2">
                <i class="fas fa-chalkboard-teacher text-2xl text-primary-500"></i>
                <span class="text-lg font-bold">Instructor</span>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="px-2 py-4 space-y-1 overflow-y-auto">
            <a href="<?= url('instructor/index.php') ?>" class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-gray-800">
                <i class="fas fa-tachometer-alt w-6"></i><span>Dashboard</span>
            </a>
            
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-gray-800">
                    <div class="flex items-center"><i class="fas fa-book w-6"></i><span>My Courses</span></div>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
                    <a href="<?= url('instructor/courses.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">All Courses</a>
                    <a href="<?= url('instructor/courses/create.php') ?>" class="block px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded">Create Course</a>
                </div>
            </div>
            
            <a href="<?= url('instructor/students.php') ?>" class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-gray-800">
                <i class="fas fa-users w-6"></i><span>Students</span>
            </a>

            <a href="<?= url('instructor/assignments.php') ?>" class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-gray-800">
                <i class="fas fa-clipboard-list w-6"></i><span>Assignments</span>
            </a>

            <a href="<?= url('instructor/quizzes.php') ?>" class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-gray-800">
                <i class="fas fa-question-circle w-6"></i><span>Quizzes</span>
            </a>

            <a href="<?= url('instructor/live-sessions.php') ?>" class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-gray-800">
                <i class="fas fa-video w-6"></i><span>Live Sessions</span>
            </a>

            <a href="<?= url('instructor/analytics.php') ?>" class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-gray-800">
                <i class="fas fa-chart-bar w-6"></i><span>Analytics</span>
            </a>

            <hr class="my-4 border-gray-700">
            
            <a href="<?= url() ?>" target="_blank" class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-gray-800">
                <i class="fas fa-globe w-6"></i><span>View Site</span>
            </a>
        </nav>
    </aside>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b">
            <div class="flex items-center justify-between h-16 px-4">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <div class="flex items-center space-x-4">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100">
                            <img src="<?= getGravatar($_SESSION['user_email'] ?? '') ?>" class="h-8 w-8 rounded-full">
                            <span class="text-sm font-medium hidden md:block"><?= sanitize($_SESSION['user_first_name'] ?? 'Instructor') ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="<?= url('dashboard.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-th-large mr-2"></i>Student View
                            </a>
                            <?php if (hasRole('admin')): ?>
                            <a href="<?= url('admin/index.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-tachometer-alt mr-2"></i>Admin Panel
                            </a>
                            <?php endif; ?>
                            <hr class="my-1">
                            <a href="<?= url('logout.php') ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <main class="flex-1 overflow-y-auto">
            <?php if ($flash = getFlash()): echo $flash; endif; ?>