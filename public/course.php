<?php
/**
 * Course Detail Page
 * Display single course information
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/functions.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/User.php';

// Get course by slug
$slug = $_GET['slug'] ?? null;

if (!$slug) {
    redirect('courses.php');
}

$course = Course::findBySlug($slug);

if (!$course) {
    redirect('courses.php');
}

// Check if user is logged in and enrolled
$isLoggedIn = isLoggedIn();
$isEnrolled = false;

if ($isLoggedIn) {
    require_once '../src/classes/Enrollment.php';
    $isEnrolled = $course->isUserEnrolled($_SESSION['user_id']);
}

// Get course modules and lessons
$modules = $course->getModules();
$totalLessons = $course->getTotalLessons();

// Get reviews
$reviews = $course->getReviews(5);
$ratingBreakdown = $course->getRatingBreakdown();

// Get instructor
$instructor = User::find($course->getInstructorId());

// Related courses (same category)
$relatedCourses = Course::all([
    'category_id' => $course->getCategoryId(),
    'limit' => 4
]);
// Remove current course from related
$relatedCourses = array_filter($relatedCourses, function($c) use ($course) {
    return $c['id'] != $course->getId();
});

$page_title = htmlspecialchars($course->getTitle()) . ' - Edutrack';
require_once '../src/templates/header.php';
?>

<!-- Course Hero -->
<div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- Left: Course Info -->
            <div class="lg:col-span-2">
                
                <!-- Breadcrumb -->
                <nav class="text-sm mb-4">
                    <a href="<?= url('courses.php') ?>" class="text-gray-400 hover:text-white">Courses</a>
                    <span class="mx-2 text-gray-500">/</span>
                    <a href="<?= url('courses.php?category=' . $course->getCategorySlug()) ?>" 
                       class="text-gray-400 hover:text-white">
                        <?= htmlspecialchars($course->getCategoryName()) ?>
                    </a>
                    <span class="mx-2 text-gray-500">/</span>
                    <span class="text-gray-300"><?= htmlspecialchars($course->getTitle()) ?></span>
                </nav>
                
                <!-- TEVETA Badge -->
                <?php if ($course->isTeveta()): ?>
                <div class="mb-4">
                    <span class="inline-flex items-center bg-secondary-500 text-gray-900 px-3 py-1 rounded-full text-sm font-bold">
                        <i class="fas fa-certificate mr-2"></i>
                        TEVETA Accredited
                    </span>
                </div>
                <?php endif; ?>
                
                <!-- Title -->
                <h1 class="text-4xl font-bold mb-4">
                    <?= htmlspecialchars($course->getTitle()) ?>
                </h1>
                
                <!-- Short Description -->
                <p class="text-xl text-gray-300 mb-6">
                    <?= htmlspecialchars($course->getShortDescription()) ?>
                </p>
                
                <!-- Meta Info -->
                <div class="flex flex-wrap gap-6 text-sm">
                    <!-- Rating -->
                    <?php if ($course->getTotalReviews() > 0): ?>
                    <div class="flex items-center">
                        <div class="flex items-center text-secondary-500 mr-2">
                            <?php
                            $rating = $course->getAvgRating();
                            for ($i = 1; $i <= 5; $i++): 
                                if ($i <= $rating): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i - 0.5 <= $rating): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif;
                            endfor; ?>
                        </div>
                        <span class="text-white font-semibold"><?= number_format($rating, 1) ?></span>
                        <span class="text-gray-400 ml-1">(<?= number_format($course->getTotalReviews()) ?> reviews)</span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Students -->
                    <div class="flex items-center text-gray-300">
                        <i class="fas fa-user-graduate mr-2"></i>
                        <?= number_format($course->getTotalStudents()) ?> students enrolled
                    </div>
                    
                    <!-- Duration -->
                    <div class="flex items-center text-gray-300">
                        <i class="fas fa-clock mr-2"></i>
                        <?= $course->getDuration() ?> hours
                    </div>
                    
                    <!-- Level -->
                    <div class="flex items-center text-gray-300">
                        <i class="fas fa-signal mr-2"></i>
                        <?= ucfirst($course->getLevel()) ?>
                    </div>
                    
                    <!-- Language -->
                    <div class="flex items-center text-gray-300">
                        <i class="fas fa-language mr-2"></i>
                        <?= htmlspecialchars($course->getLanguage()) ?>
                    </div>
                </div>
                
                <!-- Instructor -->
                <?php if ($instructor): ?>
                <div class="mt-6 flex items-center">
                    <img src="<?= $instructor->getAvatarUrl() ?>" 
                         alt="<?= htmlspecialchars($instructor->getFullName()) ?>"
                         class="w-12 h-12 rounded-full mr-3">
                    <div>
                        <div class="text-sm text-gray-400">Instructor</div>
                        <div class="font-semibold"><?= htmlspecialchars($instructor->getFullName()) ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            
            <!-- Right: Enrollment Card (Desktop) -->
            <div class="hidden lg:block">
                <?php include 'course-enrollment-card.php'; ?>
            </div>
            
        </div>
    </div>
</div>

<!-- Enrollment Card (Mobile) - Sticky Bottom -->
<div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-40">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-2xl font-bold text-gray-900">
                <?= $course->getFormattedPrice() ?>
            </div>
            <?php if (!$course->isFree()): ?>
            <div class="text-sm text-gray-500">One-time payment</div>
            <?php endif; ?>
        </div>
        <?php if ($isEnrolled): ?>
        <a href="<?= url('learn.php?course=' . $course->getSlug()) ?>" 
           class="btn-primary">
            Continue Learning
        </a>
        <?php else: ?>
        <a href="<?= $course->getEnrollUrl() ?>" 
           class="btn-primary">
            Enroll Now
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 mb-20 lg:mb-0">
    <div class="grid lg:grid-cols-3 gap-8">
        
        <!-- Left: Course Content -->
        <div class="lg:col-span-2">
            
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('overview')" 
                            id="tab-overview"
                            class="tab-link active border-b-2 border-primary-600 py-4 px-1 text-primary-600 font-medium">
                        Overview
                    </button>
                    <button onclick="showTab('curriculum')" 
                            id="tab-curriculum"
                            class="tab-link border-b-2 border-transparent py-4 px-1 text-gray-500 hover:text-gray-700 font-medium">
                        Curriculum
                    </button>
                    <button onclick="showTab('instructor')" 
                            id="tab-instructor"
                            class="tab-link border-b-2 border-transparent py-4 px-1 text-gray-500 hover:text-gray-700 font-medium">
                        Instructor
                    </button>
                    <button onclick="showTab('reviews')" 
                            id="tab-reviews"
                            class="tab-link border-b-2 border-transparent py-4 px-1 text-gray-500 hover:text-gray-700 font-medium">
                        Reviews (<?= $course->getTotalReviews() ?>)
                    </button>
                </nav>
            </div>
            
            <!-- Tab Content: Overview -->
            <div id="content-overview" class="tab-content">
                
                <!-- Description -->
                <div class="prose max-w-none mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Course</h2>
                    <div class="text-gray-700">
                        <?= nl2br(htmlspecialchars($course->getDescription())) ?>
                    </div>
                </div>
                
                <!-- What You'll Learn -->
                <?php if ($course->getWhatYouWillLearn()): ?>
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">What You'll Learn</h2>
                    <div class="grid md:grid-cols-2 gap-3">
                        <?php 
                        $learningPoints = explode("\n", $course->getWhatYouWillLearn());
                        foreach ($learningPoints as $point): 
                            $point = trim($point);
                            if ($point):
                        ?>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <span class="text-gray-700"><?= htmlspecialchars($point) ?></span>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Requirements -->
                <?php if ($course->getRequirements()): ?>
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Requirements</h2>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <?php 
                        $requirements = explode("\n", $course->getRequirements());
                        foreach ($requirements as $req): 
                            $req = trim($req);
                            if ($req):
                        ?>
                        <li><?= htmlspecialchars($req) ?></li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- TEVETA Information -->
                <?php if ($course->isTeveta()): ?>
                <div class="bg-secondary-50 border border-secondary-200 rounded-lg p-6 mb-8">
                    <div class="flex items-start">
                        <i class="fas fa-certificate text-secondary-600 text-3xl mr-4"></i>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">
                                TEVETA Accredited Certificate
                            </h3>
                            <p class="text-gray-700 mb-3">
                                This course is accredited by the Technical Education, Vocational and Entrepreneurship Training Authority (TEVETA). 
                                Upon successful completion, you will receive a TEVETA-certified certificate recognized throughout Zambia.
                            </p>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-2"></i>
                                Course Code: <span class="font-mono ml-1"><?= htmlspecialchars($course->getTevetaCourseCode()) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            
            <!-- Tab Content: Curriculum -->
            <div id="content-curriculum" class="tab-content hidden">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Course Curriculum</h2>
                <p class="text-gray-600 mb-6"><?= count($modules) ?> modules • <?= $totalLessons ?> lessons</p>
                
                <?php if (empty($modules)): ?>
                    <p class="text-gray-500 italic">Course curriculum coming soon...</p>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($modules as $index => $module): ?>
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button onclick="toggleModule(<?= $index ?>)" 
                                class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100">
                            <div class="flex items-center">
                                <span class="bg-primary-100 text-primary-700 w-8 h-8 rounded-full flex items-center justify-center font-bold mr-3">
                                    <?= $index + 1 ?>
                                </span>
                                <div class="text-left">
                                    <h3 class="font-bold text-gray-900"><?= htmlspecialchars($module['title']) ?></h3>
                                    <?php if ($module['description']): ?>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($module['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down module-icon text-gray-400"></i>
                        </button>
                        
                        <div id="module-<?= $index ?>" class="module-content hidden border-t border-gray-200">
                            <?php
                            // Get lessons for this module
                            $db = Database::getInstance();
                            $lessons = $db->query(
                                "SELECT * FROM lessons WHERE module_id = :module_id ORDER BY display_order ASC",
                                ['module_id' => $module['id']]
                            )->fetchAll();
                            ?>
                            
                            <?php if (empty($lessons)): ?>
                                <div class="p-4 text-gray-500 italic">No lessons yet</div>
                            <?php else: ?>
                            <ul class="divide-y divide-gray-200">
                                <?php foreach ($lessons as $lesson): ?>
                                <li class="p-4 hover:bg-gray-50 flex items-center justify-between">
                                    <div class="flex items-center flex-1">
                                        <i class="<?= $lesson['lesson_type'] == 'video' ? 'fas fa-play-circle' : 'fas fa-file-alt' ?> text-gray-400 mr-3"></i>
                                        <div>
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($lesson['title']) ?></div>
                                            <?php if ($lesson['duration']): ?>
                                            <div class="text-sm text-gray-500"><?= $lesson['duration'] ?> min</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!$isEnrolled && $lesson['is_preview']): ?>
                                    <a href="<?= url('course-preview.php?lesson=' . $lesson['id']) ?>" 
                                       class="text-primary-600 text-sm font-medium hover:text-primary-700">
                                        Preview
                                    </a>
                                    <?php elseif (!$isEnrolled): ?>
                                    <i class="fas fa-lock text-gray-400"></i>
                                    <?php endif; ?>
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
            
            <!-- Tab Content: Instructor -->
            <div id="content-instructor" class="tab-content hidden">
                <?php if ($instructor): ?>
                <div class="flex items-start">
                    <img src="<?= $instructor->getAvatarUrl() ?>" 
                         alt="<?= htmlspecialchars($instructor->getFullName()) ?>"
                         class="w-24 h-24 rounded-full mr-6">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            <?= htmlspecialchars($instructor->getFullName()) ?>
                        </h2>
                        <p class="text-gray-600 mb-4"><?= ucfirst($instructor->getRole()) ?> at Edutrack</p>
                        
                        <?php if ($instructor->getBio()): ?>
                        <div class="prose max-w-none text-gray-700 mb-4">
                            <?= nl2br(htmlspecialchars($instructor->getBio())) ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Instructor Stats -->
                        <div class="grid grid-cols-3 gap-4 mt-6">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    <?php
                                    $instructorCourses = Course::all(['instructor_id' => $instructor->getId()]);
                                    echo count($instructorCourses);
                                    ?>
                                </div>
                                <div class="text-sm text-gray-600">Courses</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    <?= number_format($course->getTotalStudents()) ?>+
                                </div>
                                <div class="text-sm text-gray-600">Students</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    <?= number_format($course->getAvgRating(), 1) ?>
                                </div>
                                <div class="text-sm text-gray-600">Rating</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Tab Content: Reviews -->
            <div id="content-reviews" class="tab-content hidden">
                
                <!-- Rating Summary -->
                <?php if ($ratingBreakdown['total_reviews'] > 0): ?>
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Average Rating -->
                        <div class="text-center">
                            <div class="text-5xl font-bold text-gray-900 mb-2">
                                <?= number_format($ratingBreakdown['avg_rating'], 1) ?>
                            </div>
                            <div class="flex justify-center items-center text-secondary-500 mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star text-2xl"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="text-gray-600">
                                <?= number_format($ratingBreakdown['total_reviews']) ?> reviews
                            </div>
                        </div>
                        
                        <!-- Rating Breakdown -->
                        <div class="space-y-2">
                            <?php for ($star = 5; $star >= 1; $star--): 
                                $count = $ratingBreakdown[$star == 5 ? 'five_star' : ($star == 4 ? 'four_star' : ($star == 3 ? 'three_star' : ($star == 2 ? 'two_star' : 'one_star')))];
                                $percentage = $ratingBreakdown['total_reviews'] > 0 ? ($count / $ratingBreakdown['total_reviews']) * 100 : 0;
                            ?>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-600 w-12"><?= $star ?> star</span>
                                <div class="flex-1 mx-3 bg-gray-200 rounded-full h-2">
                                    <div class="bg-secondary-500 h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <span class="text-sm text-gray-600 w-12 text-right"><?= $count ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Reviews List -->
                <?php if (!empty($reviews)): ?>
                <div class="space-y-6">
                    <?php foreach ($reviews as $review): ?>
                    <div class="border-b border-gray-200 pb-6">
                        <div class="flex items-start">
                            <img src="<?= getGravatar($review['email'] ?? '', 48) ?>" 
                                 alt="<?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?>"
                                 class="w-12 h-12 rounded-full mr-4">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <h4 class="font-bold text-gray-900">
                                            <?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?>
                                        </h4>
                                        <div class="flex items-center text-secondary-500 text-sm">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?= $i <= $review['rating'] ? '' : ' text-gray-300' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500">
                                        <?= timeAgo($review['created_at']) ?>
                                    </span>
                                </div>
                                <?php if ($review['review_text']): ?>
                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-star text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500">No reviews yet. Be the first to review this course!</p>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
        
        <!-- Right Sidebar (Desktop) -->
        <div class="hidden lg:block">
            <!-- Sticky sidebar content would go here if needed -->
        </div>
        
    </div>
    
    <!-- Related Courses -->
    <?php if (!empty($relatedCourses)): ?>
    <div class="mt-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">More Courses in <?= htmlspecialchars($course->getCategoryName()) ?></h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach (array_slice($relatedCourses, 0, 4) as $relatedCourse): 
                $related = new Course($relatedCourse['id']);
            ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                <a href="<?= $related->getUrl() ?>">
                    <img src="<?= $related->getThumbnailUrl() ?>" 
                         alt="<?= htmlspecialchars($related->getTitle()) ?>"
                         class="w-full h-40 object-cover">
                </a>
                <div class="p-4">
                    <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">
                        <a href="<?= $related->getUrl() ?>" class="hover:text-primary-600">
                            <?= htmlspecialchars($related->getTitle()) ?>
                        </a>
                    </h3>
                    <div class="flex items-center justify-between">
                        <span class="text-primary-600 font-bold"><?= $related->getFormattedPrice() ?></span>
                        <?php if ($related->getAvgRating() > 0): ?>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-star text-secondary-500 mr-1"></i>
                            <?= number_format($related->getAvgRating(), 1) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function showTab(tabName) {
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-link').forEach(link => {
        link.classList.remove('active', 'border-primary-600', 'text-primary-600');
        link.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.add('active', 'border-primary-600', 'text-primary-600');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
}

function toggleModule(index) {
    const content = document.getElementById('module-' + index);
    const icon = content.previousElementSibling.querySelector('.module-icon');
    
    content.classList.toggle('hidden');
    icon.classList.toggle('fa-chevron-down');
    icon.classList.toggle('fa-chevron-up');
}
</script>

<?php require_once '../src/templates/footer.php'; ?>