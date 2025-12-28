<?php
/**
 * Edutrack computer training college
 * Main Navigation Template
 */

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="bg-white shadow-md sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="<?= url() ?>" class="flex items-center">
                    <img src="<?= asset('images/logo.png') ?>" alt="Edutrack Logo" class="h-12 w-auto mr-3">
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-primary-600">Edutrack</span>
                        <span class="text-xs text-gray-600">TEVETA REGISTERED</span>
                    </div>
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="<?= url() ?>" class="nav-link text-gray-700 hover:text-primary-600 font-medium <?= $current_page === 'index.php' ? 'active text-primary-600' : '' ?>">
                    Home
                </a>
                <a href="<?= url('courses.php') ?>" class="nav-link text-gray-700 hover:text-primary-600 font-medium <?= $current_page === 'courses.php' ? 'active text-primary-600' : '' ?>">
                    Courses
                </a>
                <a href="<?= url('about.php') ?>" class="nav-link text-gray-700 hover:text-primary-600 font-medium <?= $current_page === 'about.php' ? 'active text-primary-600' : '' ?>">
                    About Us
                </a>
                <a href="<?= url('contact.php') ?>" class="nav-link text-gray-700 hover:text-primary-600 font-medium <?= $current_page === 'contact.php' ? 'active text-primary-600' : '' ?>">
                    Contact
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <!-- Logged In Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-700 hover:text-primary-600 font-medium focus:outline-none">
                            <img src="<?= userAvatar($_SESSION['user_avatar'] ?? null, $_SESSION['user_email'] ?? '') ?>" alt="Avatar" class="h-8 w-8 rounded-full mr-2 border-2 border-primary-500">
                            <span><?= sanitize($_SESSION['user_first_name'] ?? 'User') ?></span>
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <?php if (hasRole('admin')): ?>
                                <a href="<?= url('admin/index.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt mr-2"></i> Admin Panel
                                </a>
                            <?php elseif (hasRole('instructor')): ?>
                                <a href="<?= url('instructor/index.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-chalkboard-teacher mr-2"></i> Instructor Panel
                                </a>
                            <?php endif; ?>
                            
                            <a href="<?= url('dashboard.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-th-large mr-2"></i> Dashboard
                            </a>
                            <a href="<?= url('my-courses.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-book mr-2"></i> My Courses
                            </a>
                            <a href="<?= url('my-payments.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-credit-card mr-2"></i> My Payments
                            </a>
                            <a href="<?= url('my-certificates.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-certificate mr-2"></i> Certificates
                            </a>
                            <a href="<?= url('profile.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Profile
                            </a>
                            <hr class="my-1">
                            <a href="<?= url('logout.php') ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Guest Menu -->
                    <a href="<?= url('login.php') ?>" class="text-primary-600 hover:text-primary-700 font-medium">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                    <a href="<?= url('register.php') ?>" class="btn-secondary px-6 py-2 rounded-md font-medium">
                        <i class="fas fa-user-plus mr-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:text-primary-600 focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" x-cloak class="md:hidden bg-white border-t">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="<?= url() ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 <?= $current_page === 'index.php' ? 'text-primary-600 bg-primary-50' : '' ?>">
                Home
            </a>
            <a href="<?= url('courses.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 <?= $current_page === 'courses.php' ? 'text-primary-600 bg-primary-50' : '' ?>">
                Courses
            </a>
            <a href="<?= url('about.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 <?= $current_page === 'about.php' ? 'text-primary-600 bg-primary-50' : '' ?>">
                About Us
            </a>
            <a href="<?= url('contact.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 <?= $current_page === 'contact.php' ? 'text-primary-600 bg-primary-50' : '' ?>">
                Contact
            </a>
            
            <?php if (isLoggedIn()): ?>
                <div class="border-t pt-4 mt-4">
                    <div class="flex items-center px-3 mb-3">
                        <img src="<?= userAvatar($_SESSION['user_avatar'] ?? null, $_SESSION['user_email'] ?? '') ?>" alt="Avatar" class="h-10 w-10 rounded-full mr-3 border-2 border-primary-500">
                        <div>
                            <div class="font-medium text-gray-900"><?= sanitize($_SESSION['user_first_name'] ?? 'User') ?> <?= sanitize($_SESSION['user_last_name'] ?? '') ?></div>
                            <div class="text-sm text-gray-500"><?= sanitize($_SESSION['user_email'] ?? '') ?></div>
                        </div>
                    </div>
                    
                    <?php if (hasRole('admin')): ?>
                        <a href="<?= url('admin/index.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                            <i class="fas fa-tachometer-alt mr-2"></i> Admin Panel
                        </a>
                    <?php elseif (hasRole('instructor')): ?>
                        <a href="<?= url('instructor/index.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                            <i class="fas fa-chalkboard-teacher mr-2"></i> Instructor Panel
                        </a>
                    <?php endif; ?>

                    <a href="<?= url('dashboard.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-th-large mr-2"></i> Dashboard
                    </a>
                    <a href="<?= url('my-courses.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-book mr-2"></i> My Courses
                    </a>
                    <a href="<?= url('my-payments.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-credit-card mr-2"></i> My Payments
                    </a>
                    <a href="<?= url('my-certificates.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-certificate mr-2"></i> Certificates
                    </a>
                    <a href="<?= url('profile.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <a href="<?= url('logout.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-gray-50">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            <?php else: ?>
                <div class="border-t pt-4 mt-4 space-y-2">
                    <a href="<?= url('login.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium text-primary-600 hover:bg-primary-50">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </a>
                    <a href="<?= url('register.php') ?>" @click="mobileMenuOpen = false" class="block px-3 py-2 rounded-md text-base font-medium bg-secondary-500 text-gray-900 hover:bg-secondary-600">
                        <i class="fas fa-user-plus mr-2"></i> Register
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Alpine.js for dropdown functionality -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>