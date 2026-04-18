<?php
/**
 * 404 Not Found Page
 * Custom error page for Edutrack LMS
 */

// Ensure bootstrap is loaded
require_once __DIR__ . '/../src/bootstrap.php';

// Set 404 status header
http_response_code(404);

$page_title = "Page Not Found - Edutrack";
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <!-- 404 Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 bg-primary-100 rounded-full">
                <i class="fas fa-exclamation-triangle text-6xl text-primary-600"></i>
            </div>
        </div>
        
        <!-- Error Code -->
        <h1 class="text-9xl font-bold text-primary-600 mb-4">404</h1>
        
        <!-- Error Message -->
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Page Not Found</h2>
        <p class="text-lg text-gray-600 mb-8">
            Oops! The page you're looking for doesn't exist or has been moved.
        </p>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= url() ?>" class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-md">
                <i class="fas fa-home mr-2"></i>
                Go Home
            </a>
            <a href="<?= url('courses.php') ?>" class="inline-flex items-center justify-center px-6 py-3 bg-white text-primary-600 font-semibold rounded-lg border-2 border-primary-600 hover:bg-primary-50 transition">
                <i class="fas fa-book mr-2"></i>
                Browse Courses
            </a>
        </div>
        
        <!-- Help Text -->
        <div class="mt-12 p-6 bg-white rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Need Help?</h3>
            <p class="text-gray-600 mb-4">
                If you believe this is an error, please contact our support team.
            </p>
            <a href="mailto:<?= SITE_EMAIL ?>" class="text-primary-600 hover:text-primary-700 font-medium">
                <i class="fas fa-envelope mr-1"></i>
                <?= SITE_EMAIL ?>
            </a>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>
