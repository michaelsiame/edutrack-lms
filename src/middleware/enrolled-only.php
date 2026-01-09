<?php
/**
 * Edutrack computer training college
 * Enrolled Only Middleware
 * 
 * Require user to be enrolled in a specific course
 * Usage: Set $required_course_id before including this file
 */

// First check authentication
require_once dirname(__FILE__) . '/authenticate.php';

// Load User class (required by hasRole() function)
require_once dirname(__DIR__) . '/classes/User.php';

// Check if course ID is provided
if (!isset($required_course_id)) {
    die('Error: Course ID not specified');
}

// Check if user is enrolled in the course
global $db;
$enrollment = $db->fetchOne(
    "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND enrollment_status IN ('active', 'completed')",
    [currentUserId(), $required_course_id]
);

// Allow access if enrolled or if user is admin/instructor
if (!$enrollment && !hasRole(['admin', 'instructor'])) {
    http_response_code(403);
    
    // Get course details
    $course = $db->fetchOne("SELECT title, slug, price FROM courses WHERE id = ?", [$required_course_id]);
    
    // Load header for consistent design
    $page_title = "Enrollment Required";
    require_once dirname(__DIR__) . '/templates/header.php';
    ?>
    
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white shadow-lg rounded-lg p-8 text-center">
                <div class="mb-6">
                    <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lock text-yellow-600 text-3xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Enrollment Required</h1>
                    <p class="text-gray-600">
                        You need to enroll in this course to access its content.
                    </p>
                </div>
                
                <?php if ($course): ?>
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-gray-900 mb-1"><?= sanitize($course['title']) ?></h3>
                        <p class="text-2xl font-bold text-primary-600">
                            <?= $course['price'] == 0 ? 'Free' : formatCurrency($course['price']) ?>
                        </p>
                    </div>
                    
                    <div class="space-y-3">
                        <a href="<?= url('course.php?id=' . $required_course_id) ?>" 
                           class="block btn-secondary px-6 py-3 rounded-md font-medium">
                            <i class="fas fa-shopping-cart mr-2"></i>Enroll Now
                        </a>
                        <a href="<?= url('courses.php') ?>" 
                           class="block text-gray-600 hover:text-primary-600">
                            <i class="fas fa-arrow-left mr-2"></i>Browse Other Courses
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?= url('courses.php') ?>" class="block btn-primary px-6 py-3 rounded-md">
                        <i class="fas fa-arrow-left mr-2"></i>Browse Courses
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php
    require_once dirname(__DIR__) . '/templates/footer.php';
    exit;
}

// Make enrollment data available to the page
$current_enrollment = $enrollment;