<?php
/**
 * Edutrack Computer Training College
 * Instructor Only Middleware
 * 
 * Require user to be instructor or admin
 */

// First check authentication
require_once dirname(__FILE__) . '/authenticate.php';

// Check if user is instructor or admin
if (!hasRole(['instructor', 'admin'])) {
    http_response_code(403);
    
    // Load header for consistent design
    $page_title = "Access Denied";
    require_once dirname(__DIR__) . '/templates/header.php';
    ?>
    
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <i class="fas fa-user-lock text-yellow-500 text-6xl mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Instructor Access Required</h1>
                <p class="text-gray-600">
                    This page is only accessible to instructors.
                    <br>Please contact support if you believe this is an error.
                </p>
            </div>
            
            <div class="space-y-3">
                <a href="<?= url('dashboard.php') ?>" class="block btn-primary px-6 py-3 rounded-md">
                    <i class="fas fa-arrow-left mr-2"></i>Go to Dashboard
                </a>
                <a href="<?= url('contact.php') ?>" class="block text-primary-600 hover:text-primary-700">
                    <i class="fas fa-envelope mr-2"></i>Contact Support
                </a>
            </div>
        </div>
    </div>
    
    <?php
    require_once dirname(__DIR__) . '/templates/footer.php';
    exit;
}