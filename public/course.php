<?php
/**
 * Edutrack Computer Training College
 * Course Detail Page
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Enrollment.php';
require_once '../src/classes/Review.php';
require_once '../src/classes/User.php';

// 1. Get Course ID & Validate
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$courseId) {
    setFlashMessage('Invalid course link.', 'error');
    redirect('courses.php');
}

// 2. Fetch Course Object
$course = Course::find($courseId);

if (!$course || !$course->isPublished()) {
    setFlashMessage('Course not found or currently unavailable.', 'error');
    redirect('courses.php');
}

// 3. User Status & Access Logic
$isLoggedIn = isLoggedIn();
$isEnrolled = false;
$canAccess = false;
$enrollmentId = null;

if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $enrollment = Enrollment::findByUserAndCourse($userId, $courseId);
    
    if ($enrollment) {
        $isEnrolled = true;
        $enrollmentId = $enrollment->getId();
        $canAccess = $enrollment->canAccessContent();
    }
}

// 4. Fetch Curriculum
$modules = $course->getModules();
$allLessons = $course->getLessons();

// Group lessons by module_id
$curriculum = [];
foreach ($modules as $mod) {
    $modId = $mod['id'];
    $curriculum[$modId] = [
        'info' => $mod,
        'lessons' => [],
        'duration' => 0,
        'lesson_count' => 0
    ];
}

foreach ($allLessons as $lesson) {
    $modId = $lesson['module_id'];
    if (isset($curriculum[$modId])) {
        $curriculum[$modId]['lessons'][] = $lesson;
        $curriculum[$modId]['duration'] += $lesson['duration_minutes'] ?? 0;
        $curriculum[$modId]['lesson_count']++;
    }
}

// 5. Fetch Reviews & Stats
$reviews = Review::getCourseReviews($courseId, ['status' => 'approved', 'limit' => 10]);
$reviewStats = Review::getCourseStats($courseId);

// 6. Fetch Related Courses
$relatedCoursesData = Course::all([
    'category_id' => $course->getCategoryId(),
    'limit' => 4,
    'exclude_id' => $courseId
]);

$relatedCourses = [];
foreach ($relatedCoursesData as $rc) {
    if (count($relatedCourses) < 3) {
        $relatedCourses[] = new Course($rc['id']);
    }
}

$page_title = sanitize($course->getTitle()) . ' - ' . APP_NAME;
require_once '../src/templates/header.php';
?>

<!-- Course Hero Section -->
<section class="bg-gradient-to-br from-primary-600 via-primary-700 to-purple-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Course Info -->
            <div class="lg:col-span-2">
                <!-- Breadcrumb -->
                <nav class="mb-6 text-sm">
                    <ol class="flex items-center space-x-2">
                        <li><a href="index.php" class="hover:text-yellow-300">Home</a></li>
                        <li><i class="fas fa-chevron-right text-xs"></i></li>
                        <li><a href="courses.php" class="hover:text-yellow-300">Courses</a></li>
                        <li><i class="fas fa-chevron-right text-xs"></i></li>
                        <li><a href="courses.php?category=<?= $course->getCategoryId() ?>" class="hover:text-yellow-300"><?= sanitize($course->getCategoryName()) ?></a></li>
                        <li><i class="fas fa-chevron-right text-xs"></i></li>
                        <li class="text-yellow-300 truncate"><?= sanitize($course->getTitle()) ?></li>
                    </ol>
                </nav>

                <!-- Category Badge -->
                <div class="mb-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-primary-100 text-primary-900">
                        <i class="fas fa-tag mr-2"></i>
                        <?= sanitize($course->getCategoryName()) ?>
                    </span>
                    <?php if ($course->isTevetaCertified()): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-400 text-gray-900 ml-2">
                            <i class="fas fa-certificate mr-2"></i>
                            TEVETA Certified
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Course Title -->
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <?= sanitize($course->getTitle()) ?>
                </h1>

                <!-- Course Short Description -->
                <p class="text-xl text-primary-100 mb-6 leading-relaxed">
                    <?= sanitize($course->getShortDescription()) ?>
                </p>

                <!-- Course Meta Info -->
                <div class="flex flex-wrap items-center gap-6 text-sm mb-6">
                    <?php if ($reviewStats['average_rating'] > 0): ?>
                    <div class="flex items-center">
                        <i class="fas fa-star text-yellow-400 mr-2"></i>
                        <span class="font-semibold"><?= number_format($reviewStats['average_rating'], 1) ?></span>
                        <span class="ml-1 text-primary-100">(<?= $reviewStats['total_reviews'] ?> reviews)</span>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        <span><?= number_format($course->getEnrollmentCount()) ?> students</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-signal mr-2"></i>
                        <span class="capitalize"><?= sanitize($course->getLevel()) ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock mr-2"></i>
                        <span><?= $course->getTotalHours() ?> hours</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-globe mr-2"></i>
                        <span><?= sanitize($course->getLanguage()) ?></span>
                    </div>
                </div>

                <!-- Instructor Info -->
                <div class="flex items-center bg-white/10 backdrop-blur-sm rounded-lg p-4 max-w-md">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                            <i class="fas fa-user text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-primary-100">Instructor</p>
                        <p class="font-semibold text-lg"><?= sanitize($course->getInstructorName()) ?></p>
                    </div>
                </div>
            </div>

            <!-- Right Column - Enrollment Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-2xl overflow-hidden sticky top-4">
                    <!-- Course Thumbnail -->
                    <div class="relative h-48 bg-gradient-to-br from-primary-100 to-primary-200">
                        <?php if ($course->getThumbnailUrl()): ?>
                            <img src="<?= $course->getThumbnailUrl() ?>"
                                 alt="<?= sanitize($course->getTitle()) ?>"
                                 class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-graduation-cap text-6xl text-primary-600"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-6">
                        <!-- Price -->
                        <div class="mb-6">
                            <div class="text-4xl font-bold text-gray-900">
                                <?php if ($course->isFree()): ?>
                                    <span class="text-green-600">Free</span>
                                <?php else: ?>
                                    <?= $course->getFormattedPrice() ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($course->hasDiscount()): ?>
                                <div class="text-lg text-gray-500 line-through mt-1">
                                    <?= formatCurrency($course->getOriginalPrice()) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Enrollment Action -->
                        <div class="mb-6">
                            <?php if ($isEnrolled): ?>
                                <?php if ($canAccess): ?>
                                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                        <div class="flex items-center text-green-800">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            <span class="font-semibold">You're enrolled in this course</span>
                                        </div>
                                    </div>
                                    <a href="learn.php?course=<?= $course->getSlug() ?>"
                                       class="block w-full text-center px-6 py-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition duration-200 shadow-lg">
                                        <i class="fas fa-play-circle mr-2"></i>
                                        Continue Learning
                                    </a>
                                <?php else: ?>
                                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center text-yellow-800">
                                            <i class="fas fa-lock mr-2"></i>
                                            <span class="font-semibold">Access Locked - 30% Payment Required</span>
                                        </div>
                                    </div>
                                    <a href="checkout.php?enrollment_id=<?= $enrollmentId ?>"
                                       class="block w-full text-center px-6 py-4 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition duration-200 shadow-lg">
                                        <i class="fas fa-credit-card mr-2"></i>
                                        Complete Payment
                                    </a>
                                <?php endif; ?>
                            <?php elseif ($isLoggedIn): ?>
                                <a href="enroll.php?course_id=<?= $course->getId() ?>"
                                   class="block w-full text-center px-6 py-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition duration-200 shadow-lg">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Enroll Now
                                </a>
                            <?php else: ?>
                                <a href="register.php?redirect=course.php?id=<?= $course->getId() ?>"
                                   class="block w-full text-center px-6 py-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition duration-200 shadow-lg">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Sign Up to Enroll
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Course Includes -->
                        <div class="pt-6 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-4">This course includes:</h4>
                            <ul class="space-y-3 text-sm text-gray-600">
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                                    <span><?= $course->getTotalHours() ?> hours on-demand content</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                                    <span>Full lifetime access</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                                    <span>Access on mobile and desktop</span>
                                </li>
                                <?php if ($course->isTevetaCertified()): ?>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                                    <span>TEVETA Certificate upon completion</span>
                                </li>
                                <?php endif; ?>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                                    <span>Assignments and quizzes</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Course Content Tabs -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200" x-data="{ activeTab: 'overview' }">
                        <nav class="flex -mb-px">
                            <button @click="activeTab = 'overview'"
                                    :class="activeTab === 'overview' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-6 py-4 border-b-2 font-medium text-sm transition">
                                Overview
                            </button>
                            <button @click="activeTab = 'curriculum'"
                                    :class="activeTab === 'curriculum' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-6 py-4 border-b-2 font-medium text-sm transition">
                                Curriculum
                            </button>
                            <button @click="activeTab = 'reviews'"
                                    :class="activeTab === 'reviews' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-6 py-4 border-b-2 font-medium text-sm transition">
                                Reviews (<?= count($reviews) ?>)
                            </button>
                        </nav>

                        <!-- Tab Content -->
                        <div class="p-8">
                            <!-- Overview Tab -->
                            <div x-show="activeTab === 'overview'" x-transition>
                                <h2 class="text-2xl font-bold text-gray-900 mb-6">Course Description</h2>
                                <div class="prose max-w-none text-gray-700 leading-relaxed">
                                    <?= nl2br(sanitize($course->getDescription())) ?>
                                </div>

                                <?php if ($course->getLearningOutcomes()): ?>
                                <div class="mt-8">
                                    <h3 class="text-xl font-bold text-gray-900 mb-4">What You'll Learn</h3>
                                    <div class="grid md:grid-cols-2 gap-4">
                                        <?php foreach (explode("\n", $course->getLearningOutcomes()) as $outcome): ?>
                                            <?php if(trim($outcome)): ?>
                                            <div class="flex items-start">
                                                <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                                                <span class="text-gray-700"><?= sanitize(trim($outcome)) ?></span>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($course->getPrerequisites()): ?>
                                <div class="mt-8">
                                    <h3 class="text-xl font-bold text-gray-900 mb-4">Prerequisites</h3>
                                    <div class="prose max-w-none text-gray-700">
                                        <?= nl2br(sanitize($course->getPrerequisites())) ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($course->getTargetAudience()): ?>
                                <div class="mt-8">
                                    <h3 class="text-xl font-bold text-gray-900 mb-4">Who This Course Is For</h3>
                                    <div class="prose max-w-none text-gray-700">
                                        <?= nl2br(sanitize($course->getTargetAudience())) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Curriculum Tab -->
                            <div x-show="activeTab === 'curriculum'" x-transition>
                                <h2 class="text-2xl font-bold text-gray-900 mb-6">Course Curriculum</h2>

                                <?php if (!empty($curriculum)): ?>
                                    <div class="space-y-4">
                                        <?php $moduleIndex = 0; ?>
                                        <?php foreach ($curriculum as $modId => $data): ?>
                                            <?php 
                                                $module = $data['info'];
                                                $moduleLessons = $data['lessons'];
                                                $moduleIndex++;
                                            ?>
                                            <div class="border border-gray-200 rounded-lg" x-data="{ open: <?= $moduleIndex === 1 ? 'true' : 'false' ?> }">
                                                <button @click="open = !open"
                                                        class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 transition">
                                                    <div class="flex-1">
                                                        <h4 class="font-semibold text-gray-900">
                                                            Module <?= $moduleIndex ?>: <?= sanitize($module['title']) ?>
                                                        </h4>
                                                        <p class="text-sm text-gray-600 mt-1">
                                                            <?= $data['lesson_count'] ?> lessons • <?= $data['duration'] ?> minutes
                                                        </p>
                                                    </div>
                                                    <i class="fas fa-chevron-down transition-transform" :class="open && 'rotate-180'"></i>
                                                </button>

                                                <div x-show="open" x-collapse class="border-t border-gray-200 bg-gray-50 p-4">
                                                    <?php if ($module['description']): ?>
                                                        <p class="text-gray-700 mb-4"><?= sanitize($module['description']) ?></p>
                                                    <?php endif; ?>

                                                    <?php if (!empty($moduleLessons)): ?>
                                                        <ul class="space-y-2">
                                                            <?php foreach ($moduleLessons as $lesson): ?>
                                                                <li class="flex items-center text-sm text-gray-700 p-2 hover:bg-white rounded transition">
                                                                    <?php 
                                                                        $icon = 'file-alt';
                                                                        if ($lesson['lesson_type'] === 'video') $icon = 'play-circle';
                                                                        if ($lesson['lesson_type'] === 'quiz') $icon = 'question-circle';
                                                                    ?>
                                                                    <i class="fas fa-<?= $icon ?> text-gray-400 mr-3"></i>
                                                                    <span class="flex-1"><?= sanitize($lesson['title']) ?></span>
                                                                    <?php if ($lesson['duration_minutes']): ?>
                                                                        <span class="text-gray-500"><?= $lesson['duration_minutes'] ?> min</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($lesson['is_preview'])): ?>
                                                                        <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded">Preview</span>
                                                                    <?php endif; ?>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        <p class="text-gray-500 text-sm">No lessons available yet.</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-12">
                                        <i class="fas fa-info-circle text-gray-400 text-4xl mb-4"></i>
                                        <p class="text-gray-600">Course curriculum will be available soon.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Reviews Tab -->
                            <div x-show="activeTab === 'reviews'" x-transition>
                                <h2 class="text-2xl font-bold text-gray-900 mb-6">Student Reviews</h2>

                                <?php if (!empty($reviews)): ?>
                                    <!-- Review Stats -->
                                    <div class="bg-gray-50 rounded-lg p-6 mb-8">
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                            <div class="text-center">
                                                <div class="text-4xl font-bold text-gray-900 mb-2">
                                                    <?= number_format($reviewStats['average_rating'], 1) ?>
                                                </div>
                                                <div class="flex justify-center mb-1">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star text-sm <?= $i <= floor($reviewStats['average_rating']) ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <p class="text-sm text-gray-600">Course Rating</p>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-4xl font-bold text-gray-900 mb-2">
                                                    <?= $reviewStats['total_reviews'] ?>
                                                </div>
                                                <p class="text-sm text-gray-600">Total Reviews</p>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-4xl font-bold text-gray-900 mb-2">
                                                    <?= $reviewStats['5_star_count'] ?>
                                                </div>
                                                <p class="text-sm text-gray-600">5 Star Reviews</p>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-4xl font-bold text-gray-900 mb-2">
                                                    <?= number_format(($reviewStats['5_star_count'] / max(1, $reviewStats['total_reviews']) * 100), 0) ?>%
                                                </div>
                                                <p class="text-sm text-gray-600">Positive Feedback</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reviews List -->
                                    <div class="space-y-6">
                                        <?php foreach ($reviews as $review): ?>
                                            <div class="border-b border-gray-200 pb-6 last:border-b-0">
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                                                            <i class="fas fa-user text-gray-500"></i>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4 flex-1">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <h4 class="font-semibold text-gray-900"><?= sanitize($review->getUserName()) ?></h4>
                                                            <span class="text-sm text-gray-500"><?= timeAgo($review->getCreatedAt()) ?></span>
                                                        </div>
                                                        <div class="flex items-center mb-2">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star text-sm <?= $i <= $review->getRating() ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                                            <?php endfor; ?>
                                                            <span class="ml-2 text-sm font-semibold text-gray-700"><?= number_format($review->getRating(), 1) ?></span>
                                                        </div>
                                                        <?php if ($review->getReviewTitle()): ?>
                                                            <h5 class="font-medium text-gray-900 mb-1"><?= sanitize($review->getReviewTitle()) ?></h5>
                                                        <?php endif; ?>
                                                        <p class="text-gray-700"><?= sanitize($review->getReviewText()) ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-12">
                                        <i class="fas fa-star text-gray-400 text-4xl mb-4"></i>
                                        <p class="text-gray-600">No reviews yet. Be the first to review this course!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Related Courses -->
                <?php if (!empty($relatedCourses)): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Related Courses</h3>
                    <div class="space-y-4">
                        <?php foreach ($relatedCourses as $related): ?>
                            <a href="course.php?id=<?= $related->getId() ?>" class="block group">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 w-20 h-16 bg-gradient-to-br from-primary-100 to-primary-200 rounded-lg flex items-center justify-center">
                                        <?php if ($related->getThumbnailUrl()): ?>
                                            <img src="<?= $related->getThumbnailUrl() ?>" 
                                                 alt="<?= sanitize($related->getTitle()) ?>"
                                                 class="w-full h-full object-cover rounded-lg">
                                        <?php else: ?>
                                            <i class="fas fa-book text-primary-600"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 group-hover:text-primary-600 line-clamp-2">
                                            <?= sanitize($related->getTitle()) ?>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <?= $related->isFree() ? 'Free' : $related->getFormattedPrice() ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once '../src/templates/footer.php'; ?>