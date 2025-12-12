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
        // Check 30% payment rule via Enrollment class
        $canAccess = $enrollment->canAccessContent();
    }
}

// 4. Fetch Curriculum (Optimized to avoid N+1 queries)
$modules = $course->getModules(); // Assuming Course class has this
$allLessons = $course->getLessons(); // Assuming Course class has this

// Group lessons by module_id for easy display
$curriculum = [];
foreach ($modules as $mod) {
    $modId = $mod['id'];
    $curriculum[$modId] = [
        'info' => $mod,
        'lessons' => [],
        'duration' => 0
    ];
}

foreach ($allLessons as $lesson) {
    $modId = $lesson['module_id'];
    if (isset($curriculum[$modId])) {
        $curriculum[$modId]['lessons'][] = $lesson;
        $curriculum[$modId]['duration'] += $lesson['duration_minutes'] ?? 0;
    }
}

// 5. Fetch Reviews & Stats
$reviews = Review::getCourseReviews($courseId, ['status' => 'approved', 'limit' => 5]);
// $reviewStats = Review::getCourseStats($courseId); // Optional if using cached stats in Course object

// 6. Fetch Related Courses (Using Course::all with filters)
$relatedCoursesData = Course::all([
    'category_id' => $course->getCategoryId(),
    'limit' => 4 // Fetch 4, then remove current course to ensure we have 3
]);

// Filter out the current course from related list
$relatedCourses = [];
foreach ($relatedCoursesData as $rc) {
    if ($rc['id'] != $courseId && count($relatedCourses) < 3) {
        $relatedCourses[] = new Course($rc['id']); // Convert to object for uniform access
    }
}

$page_title = sanitize($course->getTitle()) . ' - ' . APP_NAME;
require_once '../src/templates/header.php';
?>

<!-- Course Hero Section -->
<section class="bg-gradient-to-br from-primary-800 to-primary-900 text-white py-16 lg:py-20 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-1/2 h-full opacity-10 bg-no-repeat bg-right bg-contain" 
         style="background-image: url('<?= asset('images/pattern-dots.svg') ?>');"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            
            <!-- LEFT COLUMN: Course Info -->
            <div class="lg:col-span-2">
                <nav class="flex items-center text-sm text-primary-200 mb-6">
                    <a href="courses.php" class="hover:text-white transition">Courses</a>
                    <i class="fas fa-chevron-right text-xs mx-3"></i>
                    <span class="text-white font-medium truncate"><?= sanitize($course->getTitle()) ?></span>
                </nav>

                <div class="flex flex-wrap gap-3 mb-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-white border border-white/20 backdrop-blur-sm">
                        <?= sanitize($course->getCategoryName()) ?>
                    </span>
                    
                    <?php if ($course->getAvgRating() > 0): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-500/20 text-yellow-300 border border-yellow-500/40">
                        <i class="fas fa-star mr-1"></i> <?= number_format($course->getAvgRating(), 1) ?>
                    </span>
                    <?php endif; ?>
                </div>

                <h1 class="text-3xl md:text-5xl font-bold mb-6 leading-tight">
                    <?= sanitize($course->getTitle()) ?>
                </h1>
                
                <p class="text-lg text-primary-100 mb-8 leading-relaxed max-w-2xl">
                    <?= sanitize($course->getShortDescription()) ?>
                </p>

                <div class="flex items-center p-4 bg-white/5 rounded-lg border border-white/10 max-w-md">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-primary-700 flex items-center justify-center text-xl font-bold border-2 border-primary-500">
                            <?= strtoupper(substr($course->getInstructorName(), 0, 1)) ?>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs text-primary-300 uppercase tracking-wide">Instructor</p>
                        <p class="font-semibold text-white"><?= sanitize($course->getInstructorName()) ?></p>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Sticky Enrollment Card -->
            <div class="lg:col-span-1">
                <div class="bg-white text-gray-800 rounded-xl shadow-2xl overflow-hidden sticky top-24 z-20">
                    
                    <!-- Thumbnail -->
                    <div class="relative h-52 bg-gray-200 group">
                        <img src="<?= $course->getThumbnailUrl() ?>" 
                             alt="<?= sanitize($course->getTitle()) ?>"
                             class="w-full h-full object-cover transition duration-500 group-hover:scale-105">

                        <?php if($course->getVideoIntroUrl()): ?>
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition flex items-center justify-center cursor-pointer">
                            <div class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center shadow-lg backdrop-blur-sm group-hover:scale-110">
                                <i class="fas fa-play text-primary-600 text-xl ml-1"></i>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-6">
                        <!-- Pricing -->
                        <div class="flex items-end gap-3 mb-6">
                            <?php if ($course->isFree()): ?>
                                <span class="text-4xl font-bold text-green-600">Free</span>
                            <?php else: ?>
                                <span class="text-4xl font-bold text-gray-900">
                                    <?= $course->getFormattedPrice() ?>
                                </span>
                                <?php if ($course->getDiscountPrice() > 0): ?>
                                    <span class="text-lg text-gray-400 line-through mb-2">
                                        <?= formatCurrency($course->getDiscountPrice()) ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="mb-6 space-y-3">
                            <?php if ($isEnrolled): ?>
                                <?php if ($canAccess): ?>
                                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg flex items-center text-sm text-green-800 mb-2">
                                        <i class="fas fa-check-circle text-lg mr-2"></i> Active Enrollment
                                    </div>
                                    <a href="learn.php?course=<?= $course->getSlug() ?>" class="btn-primary">
                                        <i class="fas fa-play-circle mr-2"></i> Continue Learning
                                    </a>
                                <?php else: ?>
                                    <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 text-sm mb-2 rounded-r-lg">
                                        <p class="font-bold mb-1"><i class="fas fa-lock mr-1"></i> Access Locked</p>
                                        <p>Pay at least <strong>30%</strong> to unlock.</p>
                                    </div>
                                    <a href="checkout.php?enrollment_id=<?= $enrollmentId ?>" class="btn-warning">
                                        <i class="fas fa-credit-card mr-2"></i> Make Payment
                                    </a>
                                <?php endif; ?>

                            <?php elseif ($isLoggedIn): ?>
                                <a href="enroll.php?course_id=<?= $course->getId() ?>" class="btn-primary">
                                    <i class="fas fa-user-plus mr-2"></i> Enroll Now
                                </a>
                                <p class="text-center text-xs text-gray-500 mt-2">30-Day Money-Back Guarantee</p>

                            <?php else: ?>
                                <a href="login.php?redirect=course.php?id=<?= $course->getId() ?>" class="btn-primary">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Login to Enroll
                                </a>
                                <div class="text-center mt-3">
                                    <span class="text-gray-500 text-sm">Don't have an account?</span>
                                    <a href="register.php" class="text-primary-600 font-semibold text-sm hover:underline">Register</a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Stats -->
                        <div class="border-t border-gray-100 pt-6">
                            <h4 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wide">Included:</h4>
                            <ul class="space-y-3 text-sm text-gray-600">
                                <li class="flex items-start">
                                    <i class="fas fa-video w-6 text-primary-500 mt-0.5"></i>
                                    <span><?= $course->getTotalHours() ?> hours video</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-file-alt w-6 text-primary-500 mt-0.5"></i>
                                    <span><?= count($modules) ?> Modules</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-certificate w-6 text-primary-500 mt-0.5"></i>
                                    <span>Certificate of Completion</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Content Tabs -->
<section class="py-16 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" x-data="{ activeTab: 'overview' }">
                    <div class="flex border-b border-gray-200">
                        <button @click="activeTab = 'overview'" :class="activeTab==='overview'?'text-primary-600 border-primary-600':'text-gray-500 border-transparent'" class="tab-btn">Overview</button>
                        <button @click="activeTab = 'curriculum'" :class="activeTab==='curriculum'?'text-primary-600 border-primary-600':'text-gray-500 border-transparent'" class="tab-btn">Curriculum</button>
                        <button @click="activeTab = 'reviews'" :class="activeTab==='reviews'?'text-primary-600 border-primary-600':'text-gray-500 border-transparent'" class="tab-btn">Reviews</button>
                    </div>

                    <div class="p-6 md:p-8">
                        <!-- Overview -->
                        <div x-show="activeTab === 'overview'" class="space-y-8 animate-fade-in">
                            <div class="prose max-w-none text-gray-600">
                                <h3 class="text-xl font-bold text-gray-900 mb-4">Description</h3>
                                <?= nl2br(sanitize($course->getDescription())) ?>
                            </div>

                            <?php if ($outcomes = $course->getLearningOutcomes()): ?>
                            <div class="bg-primary-50 p-6 rounded-lg border border-primary-100">
                                <h3 class="text-lg font-bold text-primary-900 mb-4">What you'll learn</h3>
                                <div class="grid md:grid-cols-2 gap-3">
                                    <?php foreach (explode("\n", $outcomes) as $outcome): ?>
                                        <?php if(trim($outcome)): ?>
                                        <div class="flex items-start">
                                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                                            <span class="text-gray-700 text-sm"><?= sanitize(trim($outcome)) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($prereqs = $course->getPrerequisites()): ?>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Requirements</h3>
                                <ul class="list-disc pl-5 text-gray-600">
                                    <?php foreach (explode("\n", $prereqs) as $req): ?>
                                        <?php if(trim($req)): ?><li><?= sanitize(trim($req)) ?></li><?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Curriculum -->
                        <div x-show="activeTab === 'curriculum'" style="display: none;" class="animate-fade-in">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Course Content</h3>
                            <?php if (empty($curriculum)): ?>
                                <div class="text-center py-10 bg-gray-50 border border-dashed rounded-lg">
                                    <p class="text-gray-500">Content is being updated.</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php 
                                    $i = 0;
                                    foreach ($curriculum as $modId => $data): 
                                        $i++;
                                        $mod = $data['info'];
                                        $modLessons = $data['lessons'];
                                    ?>
                                    <div class="border border-gray-200 rounded-lg overflow-hidden" x-data="{ open: <?= $i === 1 ? 'true' : 'false' ?> }">
                                        <button @click="open = !open" class="w-full flex justify-between p-4 bg-gray-50 hover:bg-gray-100">
                                            <div class="text-left">
                                                <h4 class="font-bold text-gray-800">Module <?= $i ?>: <?= sanitize($mod['title']) ?></h4>
                                                <p class="text-xs text-gray-500"><?= count($modLessons) ?> Lessons • <?= $data['duration'] ?> Mins</p>
                                            </div>
                                            <i class="fas fa-chevron-down text-gray-400" :class="{'rotate-180': open}"></i>
                                        </button>
                                        
                                        <div x-show="open" class="bg-white border-t border-gray-200">
                                            <?php if (empty($modLessons)): ?>
                                                <div class="p-4 text-sm text-gray-400 italic">No lessons yet.</div>
                                            <?php else: ?>
                                                <ul class="divide-y divide-gray-100">
                                                    <?php foreach ($modLessons as $lesson): 
                                                        $icon = ($lesson['lesson_type'] === 'Video') ? 'play-circle' : 'file-alt';
                                                    ?>
                                                    <li class="flex justify-between p-3 hover:bg-gray-50">
                                                        <div class="flex items-center gap-3">
                                                            <i class="fas fa-<?= $icon ?> text-primary-400 w-5"></i>
                                                            <span class="text-sm text-gray-700"><?= sanitize($lesson['title']) ?></span>
                                                            <?php if (!empty($lesson['is_preview'])): ?>
                                                                <span class="text-xs bg-green-100 text-green-700 px-2 rounded">Preview</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="text-xs text-gray-400"><?= $lesson['duration_minutes'] ?>m</span>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Reviews -->
                        <div x-show="activeTab === 'reviews'" style="display: none;" class="animate-fade-in">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Student Feedback</h3>
                            <?php if (empty($reviews)): ?>
                                <p class="text-gray-500 text-center py-8">No reviews yet.</p>
                            <?php else: ?>
                                <div class="space-y-6">
                                    <?php foreach ($reviews as $review): ?>
                                    <div class="border-b border-gray-100 pb-6 last:border-0">
                                        <div class="flex gap-4">
                                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold flex-shrink-0">
                                                <?= strtoupper(substr($review->getUserName(), 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="flex items-center justify-between mb-1">
                                                    <h5 class="font-bold text-gray-900"><?= sanitize($review->getUserName()) ?></h5>
                                                    <span class="text-xs text-gray-400"><?= timeAgo($review->getCreatedAt()) ?></span>
                                                </div>
                                                <div class="text-yellow-400 text-xs mb-2">
                                                    <?php for($star=1; $star<=5; $star++): ?>
                                                        <i class="fas fa-star <?= $star <= $review->getRating() ? '' : 'text-gray-200' ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <p class="text-sm text-gray-600"><?= sanitize($review->getReviewText()) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Related Courses -->
            <div class="lg:col-span-1">
                <?php if (!empty($relatedCourses)): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Related Courses</h3>
                    <div class="space-y-4">
                        <?php foreach ($relatedCourses as $related): ?>
                        <a href="course.php?id=<?= $related->getId() ?>" class="group flex gap-4">
                            <div class="w-20 h-16 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                <img src="<?= $related->getThumbnailUrl() ?>" class="w-full h-full object-cover group-hover:scale-110 transition">
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 group-hover:text-primary-600 line-clamp-2">
                                    <?= sanitize($related->getTitle()) ?>
                                </h4>
                                <p class="text-xs text-primary-600 font-bold mt-1">
                                    <?= $related->getFormattedPrice() ?>
                                </p>
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

<style>
    .btn-primary { @apply block w-full text-center py-4 bg-primary-600 text-white font-bold rounded-lg hover:bg-primary-700 shadow-lg transition transform hover:-translate-y-0.5; }
    .btn-warning { @apply block w-full text-center py-4 bg-yellow-500 text-white font-bold rounded-lg hover:bg-yellow-600 shadow-lg transition transform hover:-translate-y-0.5; }
    .tab-btn { @apply flex-1 py-4 text-sm font-medium border-b-2 transition; }
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<?php require_once '../src/templates/footer.php'; ?>