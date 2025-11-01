<?php
/**
 * Access Control Functions
 * Centralized access denied and authorization error handling
 */

/**
 * Display access denied page
 * Reduces middleware duplication
 *
 * @param string $reason Reason for denial (admin, instructor, enrollment, etc.)
 * @param string $customMessage Custom message to display
 * @param array $options Additional options (show_contact, redirect_url, etc.)
 */
function accessDenied($reason = 'permission', $customMessage = '', $options = []) {
    http_response_code(403);

    $page_title = "Access Denied";

    // Reason messages
    $messages = [
        'admin' => [
            'icon' => 'fa-shield-alt',
            'title' => 'Administrator Access Required',
            'message' => 'This page is restricted to administrators only. You do not have permission to access this resource.',
            'color' => 'red'
        ],
        'instructor' => [
            'icon' => 'fa-chalkboard-teacher',
            'title' => 'Instructor Access Required',
            'message' => 'This page is restricted to instructors and administrators only. Please contact support if you believe this is an error.',
            'color' => 'orange'
        ],
        'enrollment' => [
            'icon' => 'fa-lock',
            'title' => 'Course Enrollment Required',
            'message' => 'You must be enrolled in this course to access this content. Please enroll to continue.',
            'color' => 'blue'
        ],
        'permission' => [
            'icon' => 'fa-exclamation-triangle',
            'title' => 'Access Denied',
            'message' => 'You do not have permission to access this resource.',
            'color' => 'red'
        ],
        'login' => [
            'icon' => 'fa-sign-in-alt',
            'title' => 'Login Required',
            'message' => 'Please log in to access this page.',
            'color' => 'blue'
        ]
    ];

    $info = $messages[$reason] ?? $messages['permission'];

    if ($customMessage) {
        $info['message'] = $customMessage;
    }

    // Check if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $info['title'],
            'message' => $info['message']
        ]);
        exit;
    }

    // Render HTML error page
    require_once dirname(__DIR__) . '/templates/header.php';
    ?>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">
            <!-- Icon -->
            <div class="mb-8">
                <i class="fas <?= $info['icon'] ?> text-<?= $info['color'] ?>-500 text-6xl mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= sanitize($info['title']) ?></h1>
                <p class="text-gray-600"><?= sanitize($info['message']) ?></p>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <?php if ($reason == 'enrollment'): ?>
                    <?php if (isset($options['course_slug'])): ?>
                        <a href="<?= url('course.php?slug=' . $options['course_slug']) ?>"
                           class="w-full inline-block px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            <i class="fas fa-graduation-cap mr-2"></i>View Course & Enroll
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('courses.php') ?>"
                       class="w-full inline-block px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-book mr-2"></i>Browse Courses
                    </a>
                <?php elseif ($reason == 'login'): ?>
                    <a href="<?= url('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'])) ?>"
                       class="w-full inline-block px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="<?= url('register.php') ?>"
                       class="w-full inline-block px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-user-plus mr-2"></i>Create Account
                    </a>
                <?php else: ?>
                    <?php if (isLoggedIn()): ?>
                        <a href="<?= url('dashboard.php') ?>"
                           class="w-full inline-block px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            <i class="fas fa-home mr-2"></i>Go to Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?= url('login.php') ?>"
                           class="w-full inline-block px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <a href="<?= url() ?>"
                   class="w-full inline-block px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Home
                </a>

                <?php if ($options['show_contact'] ?? true): ?>
                    <a href="<?= url('contact.php') ?>"
                       class="w-full inline-block px-6 py-3 text-primary-600 hover:text-primary-700">
                        <i class="fas fa-envelope mr-2"></i>Contact Support
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    require_once dirname(__DIR__) . '/templates/footer.php';
    exit;
}

/**
 * Require admin access (shorthand)
 */
function requireAdmin($message = '') {
    if (!hasRole('admin')) {
        accessDenied('admin', $message);
    }
}

/**
 * Require instructor or admin access (shorthand)
 */
function requireInstructor($message = '') {
    if (!hasRole(['instructor', 'admin'])) {
        accessDenied('instructor', $message);
    }
}

/**
 * Require specific role
 */
function requireRole($roles, $message = '') {
    if (!hasRole($roles)) {
        accessDenied('permission', $message ?: 'You do not have the required role to access this resource.');
    }
}

/**
 * Require course enrollment
 */
function requireEnrollment($courseId, $courseSlug = null) {
    if (!isLoggedIn()) {
        accessDenied('login');
    }

    require_once dirname(__DIR__) . '/classes/Enrollment.php';

    if (!Enrollment::isEnrolled(currentUserId(), $courseId)) {
        accessDenied('enrollment', '', ['course_slug' => $courseSlug]);
    }
}

/**
 * Check if user owns resource
 */
function requireOwnership($resourceUserId, $message = 'You do not have permission to access this resource.') {
    if (!isLoggedIn() || currentUserId() != $resourceUserId) {
        accessDenied('permission', $message);
    }
}

/**
 * Custom 404 Not Found page
 */
function notFound($message = 'The page you are looking for could not be found.') {
    http_response_code(404);

    $page_title = "Page Not Found";

    require_once dirname(__DIR__) . '/templates/header.php';
    ?>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <i class="fas fa-exclamation-circle text-gray-400 text-6xl mb-4"></i>
                <h1 class="text-6xl font-bold text-gray-900 mb-2">404</h1>
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Page Not Found</h2>
                <p class="text-gray-600"><?= sanitize($message) ?></p>
            </div>

            <div class="space-y-3">
                <a href="<?= url() ?>"
                   class="w-full inline-block px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-home mr-2"></i>Back to Home
                </a>
                <a href="javascript:history.back()"
                   class="w-full inline-block px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i>Go Back
                </a>
            </div>
        </div>
    </div>
    <?php
    require_once dirname(__DIR__) . '/templates/footer.php';
    exit;
}
