<?php
/**
 * Instructor Panel Header Template
 * Modern design with enhanced navigation
 */

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Determine section based on directory and page
$active_section = '';
if ($current_dir === 'instructor') {
    if ($current_page === 'index') $active_section = 'dashboard';
    elseif ($current_page === 'courses' || $current_page === 'course-edit' || $current_dir === 'courses') $active_section = 'courses';
    elseif ($current_page === 'students') $active_section = 'students';
    elseif ($current_page === 'assignments') $active_section = 'assignments';
    elseif ($current_page === 'quizzes') $active_section = 'quizzes';
    elseif ($current_page === 'live-sessions') $active_section = 'live';
    elseif ($current_page === 'analytics') $active_section = 'analytics';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Instructor Panel' ?> - <?= APP_NAME ?></title>
    
    <link rel="icon" type="image/png" href="<?= url('assets/images/favicon.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Shared Tailwind Config -->
    <script src="<?= url('assets/js/tailwind-config.js') ?>"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover { background: rgba(255,255,255,0.1); }
        .sidebar-link.active { background: rgba(59, 130, 246, 0.9); box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3); }
        .glass-effect { backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-gray-50">

<div x-data="{ 
    sidebarOpen: false, 
    coursesOpen: false,
    notificationsOpen: false,
    userMenuOpen: false 
}" class="flex h-screen overflow-hidden">
    
    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak 
         class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden glass-effect">
    </div>
    
    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
           class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-gray-900 via-gray-800 to-gray-900 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static flex flex-col">
        
        <!-- Logo Section -->
        <div class="flex items-center h-16 px-6 bg-gray-900 border-b border-gray-700">
            <a href="<?= url('instructor/index.php') ?>" class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-graduation-cap text-white text-xl"></i>
                </div>
                <div>
                    <span class="text-lg font-bold tracking-tight">Edutrack</span>
                    <span class="block text-xs text-gray-400 -mt-1">Instructor</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden ml-auto text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            
            <!-- Dashboard -->
            <a href="<?= url('instructor/index.php') ?>" 
               class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $active_section === 'dashboard' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                <div class="w-8 h-8 rounded-lg <?= $active_section === 'dashboard' ? 'bg-white/20' : 'bg-gray-700/50' ?> flex items-center justify-center mr-3">
                    <i class="fas fa-th-large"></i>
                </div>
                <span>Dashboard</span>
            </a>
            
            <!-- Courses Section -->
            <div x-data="{ open: <?= $active_section === 'courses' ? 'true' : 'false' ?> }">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl <?= $active_section === 'courses' ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/30' : 'text-gray-300 hover:text-white hover:bg-gray-800' ?>">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg <?= $active_section === 'courses' ? 'bg-white/20' : 'bg-gray-700/50' ?> flex items-center justify-center mr-3">
                            <i class="fas fa-book"></i>
                        </div>
                        <span>My Courses</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     x-cloak 
                     class="ml-4 mt-2 space-y-1 border-l-2 border-gray-700 pl-4">
                    <a href="<?= url('instructor/courses.php') ?>" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-lg transition">
                        <i class="fas fa-list mr-2 w-4"></i>All Courses
                    </a>
                    <a href="<?= url('instructor/courses/create.php') ?>" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-lg transition">
                        <i class="fas fa-plus-circle mr-2 w-4"></i>Create Course
                    </a>
                    <a href="<?= url('instructor/courses/templates.php') ?>" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-lg transition">
                        <i class="fas fa-magic mr-2 w-4"></i>Templates
                    </a>
                </div>
            </div>
            
            <!-- Students -->
            <a href="<?= url('instructor/students.php') ?>" 
               class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $active_section === 'students' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                <div class="w-8 h-8 rounded-lg <?= $active_section === 'students' ? 'bg-white/20' : 'bg-gray-700/50' ?> flex items-center justify-center mr-3">
                    <i class="fas fa-users"></i>
                </div>
                <span>Students</span>
                <span class="ml-auto bg-primary-500 text-white text-xs px-2 py-0.5 rounded-full">New</span>
            </a>

            <!-- Assignments -->
            <a href="<?= url('instructor/assignments.php') ?>" 
               class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $active_section === 'assignments' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                <div class="w-8 h-8 rounded-lg <?= $active_section === 'assignments' ? 'bg-white/20' : 'bg-gray-700/50' ?> flex items-center justify-center mr-3">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <span>Assignments</span>
            </a>

            <!-- Quizzes -->
            <a href="<?= url('instructor/quizzes.php') ?>" 
               class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $active_section === 'quizzes' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                <div class="w-8 h-8 rounded-lg <?= $active_section === 'quizzes' ? 'bg-white/20' : 'bg-gray-700/50' ?> flex items-center justify-center mr-3">
                    <i class="fas fa-question-circle"></i>
                </div>
                <span>Quizzes</span>
            </a>

            <!-- Live Sessions -->
            <a href="<?= url('instructor/live-sessions.php') ?>" 
               class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $active_section === 'live' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                <div class="w-8 h-8 rounded-lg <?= $active_section === 'live' ? 'bg-white/20' : 'bg-gray-700/50' ?> flex items-center justify-center mr-3">
                    <i class="fas fa-video"></i>
                </div>
                <span>Live Sessions</span>
                <span class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full animate-pulse">Live</span>
            </a>

            <!-- Analytics -->
            <a href="<?= url('instructor/analytics.php') ?>" 
               class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-xl <?= $active_section === 'analytics' ? 'active' : 'text-gray-300 hover:text-white' ?>">
                <div class="w-8 h-8 rounded-lg <?= $active_section === 'analytics' ? 'bg-white/20' : 'bg-gray-700/50' ?> flex items-center justify-center mr-3">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <span>Analytics</span>
            </a>

            <hr class="my-4 border-gray-700/50">
            
            <!-- Quick Links -->
            <div class="px-4 py-2">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Quick Links</span>
            </div>
            
            <a href="<?= url('instructor/quick-actions.php') ?>" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium text-gray-300 hover:text-white rounded-xl">
                <div class="w-8 h-8 rounded-lg bg-gray-700/50 flex items-center justify-center mr-3">
                    <i class="fas fa-bolt"></i>
                </div>
                <span>Quick Actions</span>
            </a>
            
            <a href="<?= url('instructor/courses/templates.php') ?>" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium text-gray-300 hover:text-white rounded-xl">
                <div class="w-8 h-8 rounded-lg bg-gray-700/50 flex items-center justify-center mr-3">
                    <i class="fas fa-magic"></i>
                </div>
                <span>Templates</span>
            </a>
            
            <a href="<?= url() ?>" target="_blank" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium text-gray-300 hover:text-white rounded-xl">
                <div class="w-8 h-8 rounded-lg bg-gray-700/50 flex items-center justify-center mr-3">
                    <i class="fas fa-external-link-alt"></i>
                </div>
                <span>View Site</span>
            </a>
            
            <a href="<?= url('dashboard.php') ?>" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium text-gray-300 hover:text-white rounded-xl">
                <div class="w-8 h-8 rounded-lg bg-gray-700/50 flex items-center justify-center mr-3">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <span>Student View</span>
            </a>
        </nav>
        
        <!-- User Profile Summary at Bottom -->
        <div class="p-4 border-t border-gray-700/50">
            <div class="flex items-center space-x-3">
                <img src="<?= getGravatar($_SESSION['user_email'] ?? '') ?>" class="h-10 w-10 rounded-full border-2 border-primary-500">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">
                        <?= sanitize($_SESSION['user_first_name'] ?? 'Instructor') ?> <?= sanitize($_SESSION['user_last_name'] ?? '') ?>
                    </p>
                    <p class="text-xs text-gray-400 truncate"><?= sanitize($_SESSION['user_email'] ?? '') ?></p>
                </div>
            </div>
        </div>
    </aside>
    
    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                <!-- Left: Mobile Menu Button & Breadcrumb -->
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" 
                            class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Breadcrumb -->
                    <nav class="hidden md:flex ml-4" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2 text-sm text-gray-500">
                            <li><a href="<?= url('instructor/index.php') ?>" class="hover:text-primary-600 transition">Home</a></li>
                            <li><i class="fas fa-chevron-right text-xs text-gray-400"></i></li>
                            <li class="text-gray-900 font-medium"><?= $page_title ?? 'Dashboard' ?></li>
                        </ol>
                    </nav>
                </div>
                
                <!-- Right: Actions & User Menu -->
                <div class="flex items-center space-x-4">
                    <!-- Create New Dropdown -->
                    <div x-data="{ open: false }" class="hidden sm:block relative">
                        <button @click="open = !open" 
                                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition shadow-sm">
                            <i class="fas fa-plus mr-2"></i>
                            <span>Create New</span>
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             x-cloak
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                            <a href="<?= url('instructor/courses/create.php') ?>" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-3">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div>
                                    <p class="font-medium">New Course</p>
                                    <p class="text-xs text-gray-500">Create a new course</p>
                                </div>
                            </a>
                            <a href="<?= url('instructor/live-sessions.php') ?>" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center mr-3">
                                    <i class="fas fa-video"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Live Session</p>
                                    <p class="text-xs text-gray-500">Schedule a live class</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" 
                                class="flex items-center space-x-3 p-2 rounded-xl hover:bg-gray-100 transition">
                            <img src="<?= getGravatar($_SESSION['user_email'] ?? '') ?>" class="h-9 w-9 rounded-full border border-gray-200">
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-900"><?= sanitize($_SESSION['user_first_name'] ?? 'Instructor') ?></p>
                                <p class="text-xs text-gray-500">Instructor</p>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-gray-400 hidden md:block"></i>
                        </button>
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             x-cloak
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900"><?= sanitize($_SESSION['user_first_name'] ?? '') ?> <?= sanitize($_SESSION['user_last_name'] ?? '') ?></p>
                                <p class="text-xs text-gray-500 truncate"><?= sanitize($_SESSION['user_email'] ?? '') ?></p>
                            </div>
                            <a href="<?= url('dashboard.php') ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-th-large w-5 mr-3 text-gray-400"></i>Student View
                            </a>
                            <?php if (hasRole('admin')): ?>
                            <a href="<?= url('admin/index.php') ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-shield-alt w-5 mr-3 text-gray-400"></i>Admin Panel
                            </a>
                            <?php endif; ?>
                            <a href="<?= url('profile.php') ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-user w-5 mr-3 text-gray-400"></i>My Profile
                            </a>
                            <hr class="my-2 border-gray-100">
                            <a href="<?= url('logout.php') ?>" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                <i class="fas fa-sign-out-alt w-5 mr-3"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Flash Messages -->
        <?php if ($flash = getFlash()): ?>
        <div class="px-4 sm:px-6 lg:px-8 pt-6">
            <?= $flash ?>
        </div>
        <?php endif; ?>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
