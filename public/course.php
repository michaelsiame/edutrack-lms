<?php
/**
 * Course Detail Page
 * Display single course information
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Review.php';

// Get course ID from URL
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$courseId) {
    $_SESSION['error'] = 'Invalid course ID';
    redirect('courses.php');
}

// Get course details with all related information
$db = Database::getInstance();

$course = $db->fetchOne("
    SELECT c.*,
           cc.name as category_name,
           cc.color as category_color,
           CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
           i.id as instructor_id,
           COUNT(DISTINCT e.id) as enrolled_students
    FROM courses c
    JOIN course_categories cc ON c.category_id = cc.id
    LEFT JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN users u ON i.user_id = u.id
    LEFT JOIN enrollments e ON c.id = e.course_id
    WHERE c.id = ? AND c.status = 'published'
    GROUP BY c.id
", [$courseId]);

if (!$course) {
    $_SESSION['error'] = 'Course not found or not available';
    redirect('courses.php');
}

// Check if user is already enrolled
$isEnrolled = false;
$enrollment = null;
if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $enrollment = $db->fetchOne("
        SELECT * FROM enrollments
        WHERE user_id = ? AND course_id = ?
    ", [$userId, $courseId]);
    $isEnrolled = !empty($enrollment);
}

// Get course modules and lessons
$modules = $db->fetchAll("
    SELECT cm.*,
           COUNT(l.id) as lesson_count,
           SUM(l.duration_minutes) as total_duration
    FROM course_modules cm
    LEFT JOIN lessons l ON cm.id = l.module_id
    WHERE cm.course_id = ?
    GROUP BY cm.id
    ORDER BY cm.display_order ASC
", [$courseId]);

// Get course reviews using Review class
$reviewObjects = Review::getCourseReviews($courseId, ['status' => 'approved', 'limit' => 10]);

// Convert to array format for template compatibility
$reviews = [];
foreach ($reviewObjects as $review) {
    $reviews[] = [
        'id' => $review->getId(),
        'rating' => $review->getRating(),
        'review_title' => $review->getReviewTitle(),
        'review_text' => $review->getReviewText(),
        'reviewer_name' => $review->getUserName(),
        'avatar' => $review->getUserAvatar(),
        'created_at' => $review->getCreatedAt(),
        'helpful_count' => $review->getHelpfulCount(),
        'is_featured' => $review->isFeatured()
    ];
}

// Get review statistics
$reviewStats = Review::getCourseStats($courseId);

// Get related courses (same category)
$relatedCourses = $db->fetchAll("
    SELECT c.*, cc.name as category_name, cc.color as category_color
    FROM courses c
    JOIN course_categories cc ON c.category_id = cc.id
    WHERE c.category_id = ? AND c.id != ? AND c.status = 'published'
    ORDER BY c.created_at DESC
    LIMIT 3
", [$course['category_id'], $courseId]);

$page_title = sanitize($course['title']) . ' - Edutrack computer training college';

require_once '../src/templates/header.php';
?>

<!-- Course Hero Section -->
<section class="bg-gradient-to-br from-<?= $course['category_color'] ?>-600 via-<?= $course['category_color'] ?>-700 to-purple-900 text-white py-16">
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
                        <li><a href="courses.php?category=<?= $course['category_id'] ?>" class="hover:text-yellow-300"><?= sanitize($course['category_name']) ?></a></li>
                        <li><i class="fas fa-chevron-right text-xs"></i></li>
                        <li class="text-yellow-300"><?= sanitize($course['title']) ?></li>
                    </ol>
                </nav>

                <!-- Category Badge -->
                <div class="mb-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-<?= $course['category_color'] ?>-100 text-<?= $course['category_color'] ?>-900">
                        <i class="fas fa-tag mr-2"></i>
                        <?= sanitize($course['category_name']) ?>
                    </span>
                    <?php if ($course['is_teveta_certified']): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-400 text-gray-900 ml-2">
                            <i class="fas fa-certificate mr-2"></i>
                            TEVETA Certified
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Course Title -->
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <?= sanitize($course['title']) ?>
                </h1>

                <!-- Course Short Description -->
                <p class="text-xl text-<?= $course['category_color'] ?>-100 mb-6 leading-relaxed">
                    <?= sanitize($course['short_description']) ?>
                </p>

                <!-- Course Meta Info -->
                <div class="flex flex-wrap items-center gap-6 text-sm mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-star text-yellow-400 mr-2"></i>
                        <span class="font-semibold"><?= number_format($course['rating_average'], 1) ?></span>
                        <span class="ml-1 text-<?= $course['category_color'] ?>-100">(<?= $course['rating_count'] ?> reviews)</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        <span><?= number_format($course['enrolled_students']) ?> students</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-signal mr-2"></i>
                        <span class="capitalize"><?= sanitize($course['course_level']) ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock mr-2"></i>
                        <span><?= $course['duration_hours'] ?> hours</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-globe mr-2"></i>
                        <span><?= sanitize($course['language']) ?></span>
                    </div>
                </div>

                <!-- Instructor Info -->
                <div class="flex items-center bg-white/10 backdrop-blur-sm rounded-lg p-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                            <i class="fas fa-user text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-<?= $course['category_color'] ?>-100">Instructor</p>
                        <p class="font-semibold text-lg"><?= sanitize($course['instructor_name']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Right Column - Enrollment Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-2xl overflow-hidden sticky top-4">
                    <!-- Course Thumbnail -->
                    <div class="relative h-48 bg-gradient-to-br from-<?= $course['category_color'] ?>-100 to-<?= $course['category_color'] ?>-200">
                        <?php if ($course['thumbnail'] && file_exists('uploads/courses/' . $course['thumbnail'])): ?>
                            <img src="uploads/courses/<?= sanitize($course['thumbnail']) ?>"
                                 alt="<?= sanitize($course['title']) ?>"
                                 class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-graduation-cap text-6xl text-<?= $course['category_color'] ?>-600"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-6">
                        <!-- Price -->
                        <div class="mb-6">
                            <div class="text-4xl font-bold text-gray-900">
                                <?php if ($course['price'] == 0): ?>
                                    <span class="text-green-600">Free</span>
                                <?php else: ?>
                                    ZMW <?= number_format($course['price'], 2) ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($course['discounted_price'] && $course['discounted_price'] < $course['price']): ?>
                                <div class="text-lg text-gray-500 line-through mt-1">
                                    ZMW <?= number_format($course['price'], 2) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Enrollment Action -->
                        <?php if ($isEnrolled): ?>
                            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-center text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span class="font-semibold">You're enrolled in this course</span>
                                </div>
                            </div>
                            <a href="learn.php?course=<?= $course['slug'] ?>"
                               class="block w-full text-center px-6 py-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition duration-200 shadow-lg">
                                <i class="fas fa-play-circle mr-2"></i>
                                Continue Learning
                            </a>
                        <?php elseif (isLoggedIn()): ?>
                            <a href="enroll.php?course_id=<?= $course['id'] ?>"
                               class="block w-full text-center px-6 py-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition duration-200 shadow-lg">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Enroll Now
                            </a>
                        <?php else: ?>
                            <a href="register.php?redirect=course.php?id=<?= $course['id'] ?>"
                               class="block w-full text-center px-6 py-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition duration-200 shadow-lg">
                                <i class="fas fa-user-plus mr-2"></i>
                                Sign Up to Enroll
                            </a>
                        <?php endif; ?>

                        <!-- Course Includes -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-4">This course includes:</h4>
                            <ul class="space-y-3 text-sm text-gray-600">
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                                    <span><?= $course['duration_hours'] ?> hours on-demand content</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                                    <span>Full lifetime access</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                                    <span>Access on mobile and desktop</span>
                                </li>
                                <?php if ($course['is_teveta_certified']): ?>
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
                                    <?= nl2br(sanitize($course['description'])) ?>
                                </div>

                                <?php if ($course['learning_outcomes']): ?>
                                <div class="mt-8">
                                    <h3 class="text-xl font-bold text-gray-900 mb-4">What You'll Learn</h3>
                                    <div class="prose max-w-none text-gray-700">
                                        <?= nl2br(sanitize($course['learning_outcomes'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($course['prerequisites']): ?>
                                <div class="mt-8">
                                    <h3 class="text-xl font-bold text-gray-900 mb-4">Prerequisites</h3>
                                    <div class="prose max-w-none text-gray-700">
                                        <?= nl2br(sanitize($course['prerequisites'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($course['target_audience']): ?>
                                <div class="mt-8">
                                    <h3 class="text-xl font-bold text-gray-900 mb-4">Who This Course Is For</h3>
                                    <div class="prose max-w-none text-gray-700">
                                        <?= nl2br(sanitize($course['target_audience'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Curriculum Tab -->
                            <div x-show="activeTab === 'curriculum'" x-transition>
                                <h2 class="text-2xl font-bold text-gray-900 mb-6">Course Curriculum</h2>

                                <?php if (!empty($modules)): ?>
                                    <div class="space-y-4">
                                        <?php foreach ($modules as $index => $module): ?>
                                            <div class="border border-gray-200 rounded-lg" x-data="{ open: <?= $index === 0 ? 'true' : 'false' ?> }">
                                                <button @click="open = !open"
                                                        class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 transition">
                                                    <div class="flex-1">
                                                        <h4 class="font-semibold text-gray-900">
                                                            Module <?= $index + 1 ?>: <?= sanitize($module['title']) ?>
                                                        </h4>
                                                        <p class="text-sm text-gray-600 mt-1">
                                                            <?= $module['lesson_count'] ?> lessons • <?= $module['total_duration'] ?? 0 ?> minutes
                                                        </p>
                                                    </div>
                                                    <i class="fas fa-chevron-down transition-transform" :class="open && 'rotate-180'"></i>
                                                </button>

                                                <div x-show="open" x-collapse class="border-t border-gray-200 bg-gray-50 p-4">
                                                    <?php if ($module['description']): ?>
                                                        <p class="text-gray-700 mb-4"><?= sanitize($module['description']) ?></p>
                                                    <?php endif; ?>

                                                    <?php
                                                    $lessons = $db->fetchAll("
                                                        SELECT * FROM lessons
                                                        WHERE module_id = ?
                                                        ORDER BY display_order ASC
                                                    ", [$module['id']]);
                                                    ?>

                                                    <?php if (!empty($lessons)): ?>
                                                        <ul class="space-y-2">
                                                            <?php foreach ($lessons as $lesson): ?>
                                                                <li class="flex items-center text-sm text-gray-700 p-2 hover:bg-white rounded transition">
                                                                    <i class="fas fa-<?= $lesson['lesson_type'] === 'video' ? 'play-circle' : ($lesson['lesson_type'] === 'quiz' ? 'question-circle' : 'file-alt') ?> text-gray-400 mr-3"></i>
                                                                    <span class="flex-1"><?= sanitize($lesson['title']) ?></span>
                                                                    <?php if ($lesson['duration_minutes']): ?>
                                                                        <span class="text-gray-500"><?= $lesson['duration_minutes'] ?> min</span>
                                                                    <?php endif; ?>
                                                                    <?php if ($lesson['is_preview']): ?>
                                                                        <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded">Preview</span>
                                                                    <?php endif; ?>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
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
                                                            <h4 class="font-semibold text-gray-900"><?= sanitize($review['reviewer_name']) ?></h4>
                                                            <span class="text-sm text-gray-500"><?= timeAgo($review['created_at']) ?></span>
                                                        </div>
                                                        <div class="flex items-center mb-2">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star text-sm <?= $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                                            <?php endfor; ?>
                                                            <span class="ml-2 text-sm font-semibold text-gray-700"><?= number_format($review['rating'], 1) ?></span>
                                                        </div>
                                                        <?php if ($review['review_title']): ?>
                                                            <h5 class="font-medium text-gray-900 mb-1"><?= sanitize($review['review_title']) ?></h5>
                                                        <?php endif; ?>
                                                        <p class="text-gray-700"><?= sanitize($review['review_text']) ?></p>
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
                            <a href="course.php?id=<?= $related['id'] ?>" class="block group">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 w-20 h-16 bg-gradient-to-br from-<?= $related['category_color'] ?>-100 to-<?= $related['category_color'] ?>-200 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-book text-<?= $related['category_color'] ?>-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 group-hover:text-primary-600 line-clamp-2">
                                            <?= sanitize($related['title']) ?>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <?= $related['price'] == 0 ? 'Free' : 'ZMW ' . number_format($related['price'], 2) ?>
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
