<?php
/**
 * Home Page
 * Main landing page for Edutrack LMS
 */

require_once '../src/bootstrap.php';

$page_title = "Home - Edutrack computer training college";

require_once '../src/templates/header.php';
?>

<?php
// 1. FEATURED BY CATEGORY (Optimized Query with Instructors)
$featured_by_category = [];
try {
    // We added the JOINs here to get the instructor name, just like in courses.php
    $stmt = $pdo->query("
        SELECT c.*, 
               cc.name as category_name, 
               cc.color as category_color,
               CONCAT(u.first_name, ' ', u.last_name) as instructor_name
        FROM courses c
        JOIN course_categories cc ON c.category_id = cc.id
        LEFT JOIN instructors i ON c.instructor_id = i.id
        LEFT JOIN users u ON i.user_id = u.id
        WHERE c.status = 'published' 
        ORDER BY RAND()
        LIMIT 12
    ");
    $all_featured = $stmt->fetchAll();
    
    // Group by category and limit to 3 courses per category
    foreach ($all_featured as $course) {
        $featured_by_category[$course['category_name']][] = $course;
    }
    
    // Limit courses per category to 3
    array_walk($featured_by_category, function(&$courses) {
        $courses = array_slice($courses, 0, 3);
    });
} catch (PDOException $e) {
    error_log("Homepage featured courses error: " . $e->getMessage());
}

// 2. TOP FEATURED (Recent/Popular with Instructors)
$top_featured = [];
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               cc.name as category_name, 
               cc.color as category_color,
               CONCAT(u.first_name, ' ', u.last_name) as instructor_name
        FROM courses c
        JOIN course_categories cc ON c.category_id = cc.id
        LEFT JOIN instructors i ON c.instructor_id = i.id
        LEFT JOIN users u ON i.user_id = u.id
        WHERE c.status = 'published' 
        ORDER BY c.created_at DESC 
        LIMIT 6
    ");
    $top_featured = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Top featured courses error: " . $e->getMessage());
}
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-primary-600 via-blue-800 to-purple-900 text-white overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-40"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <div class="mb-6 animate-fade-in">
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-yellow-500 text-gray-900 shadow-lg">
                    <i class="fas fa-certificate mr-2"></i>
                    TEVETA Registered Institution
                </span>
            </div>
            <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in">
                Transform Your Future with
                <span class="block text-yellow-400 mt-2">Edutrack computer training college</span>
            </h1>
            <p class="text-xl md:text-2xl text-primary-100 mb-8 max-w-3xl mx-auto leading-relaxed">
                Zambia's premier TEVETA-certified computer training institution. Join thousands of students mastering industry-relevant skills.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center max-w-2xl mx-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-md text-primary-600 bg-white hover:bg-gray-50 transition duration-200 shadow-lg transform hover:-translate-y-1">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="register.php" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 transition duration-200 shadow-lg transform hover:-translate-y-1">
                        <i class="fas fa-user-plus mr-2"></i>
                        Get Started Free
                    </a>
                    <a href="courses.php" class="inline-flex items-center justify-center px-8 py-4 border border-white text-base font-medium rounded-md text-white hover:bg-white hover:text-primary-600 transition duration-200">
                        <i class="fas fa-book mr-2"></i>
                        View Our Courses
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Trust Indicators -->
        <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="animate-slide-up animation-delay-100">
                <i class="fas fa-certificate text-3xl text-yellow-400 mb-2 block"></i>
                <h3 class="text-lg font-semibold text-white">TEVETA Registered</h3>
            </div>
            <div class="animate-slide-up animation-delay-200">
                <i class="fas fa-users text-3xl text-blue-300 mb-2 block"></i>
                <h3 class="text-lg font-semibold text-white">5000+ Students</h3>
            </div>
            <div class="animate-slide-up animation-delay-300">
                <i class="fas fa-graduation-cap text-3xl text-green-300 mb-2 block"></i>
                <h3 class="text-lg font-semibold text-white">Expert Instructors</h3>
            </div>
            <div class="animate-slide-up animation-delay-400">
                <i class="fas fa-award text-3xl text-purple-300 mb-2 block"></i>
                <h3 class="text-lg font-semibold text-white">Career Ready</h3>
            </div>
        </div>
    </div>
</section>

<!-- Explore by Category Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Explore Our Certified Programs</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Choose your learning path from Edutrack's comprehensive government-recognized training programs
            </p>
        </div>

        <?php if (!empty($featured_by_category)): ?>
            <div class="space-y-16">
                <?php foreach ($featured_by_category as $category_name => $category_courses): ?>
                    <!-- Category Header -->
                    <div class="animate-slide-up animation-delay-100">
                        <div class="flex items-center mb-8 border-b pb-4 border-gray-200">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-<?= $category_courses[0]['category_color'] ?>-100 rounded-xl flex items-center justify-center shadow-sm">
                                    <i class="fas fa-layer-group text-<?= $category_courses[0]['category_color'] ?>-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($category_name) ?></h3>
                            </div>
                        </div>
                        
                        <!-- Course Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <?php foreach ($category_courses as $course): ?>
                                <div class="group course-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                                    <!-- Thumbnail -->
                                    <div class="relative h-48 bg-gradient-to-br from-<?= $course['category_color'] ?>-50 to-<?= $course['category_color'] ?>-100 overflow-hidden">
                                        <?php 
                                        // Match paths with courses.php logic
                                        if ($course['thumbnail_url'] && file_exists('../public/uploads/courses/' . $course['thumbnail_url'])): ?>
                                            <img src="uploads/courses/<?= htmlspecialchars($course['thumbnail_url']) ?>" 
                                                 alt="<?= htmlspecialchars($course['title']) ?>" 
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i class="fas fa-laptop-code text-4xl text-<?= $course['category_color'] ?>-600 opacity-60"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Level Badge -->
                                        <div class="absolute top-3 left-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                bg-<?= $course['category_color'] ?>-100 text-<?= $course['category_color'] ?>-800">
                                                <i class="fas fa-tag mr-1"></i>
                                                <?= htmlspecialchars($course['level']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="p-6">
                                        <h4 class="text-lg font-bold text-gray-900 line-clamp-2 mb-3 group-hover:text-primary-600 transition-colors min-h-[56px]">
                                            <?= htmlspecialchars($course['title']) ?>
                                        </h4>
                                        
                                        <!-- Instructor & Duration -->
                                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4 border-b border-gray-100">
                                            <span class="flex items-center">
                                                <i class="fas fa-chalkboard-teacher mr-1.5 text-primary-500"></i>
                                                <?= htmlspecialchars($course['instructor_name'] ?? 'Edutrack Team') ?>
                                            </span>
                                            <span class="flex items-center">
                                                <i class="fas fa-clock mr-1.5 text-primary-500"></i>
                                                <?= $course['duration_weeks'] ? $course['duration_weeks'] . ' weeks' : 'Flexible' ?>
                                            </span>
                                        </div>
                                        
                                        <!-- Price and Button -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-xl font-bold text-primary-600">
                                                <?= $course['price'] == 0 ? 'Free' : 'ZMW ' . number_format($course['price'], 2) ?>
                                            </span>
                                            <a href="course.php?id=<?= $course['id'] ?>" 
                                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 transition duration-200">
                                                View
                                                <i class="fas fa-arrow-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-8 mb-12">
                            <a href="courses.php?category=<?= urlencode($category_name) ?>" 
                               class="inline-flex items-center px-6 py-2 border border-<?= $category_courses[0]['category_color'] ?>-200 text-sm font-medium rounded-full text-<?= $category_courses[0]['category_color'] ?>-700 bg-<?= $category_courses[0]['category_color'] ?>-50 hover:bg-<?= $category_courses[0]['category_color'] ?>-100 transition duration-200">
                                View All <?= htmlspecialchars($category_name) ?>
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500 text-lg">Loading categories...</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Courses Section (Recent) -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Latest Additions</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Check out the newest TEVETA-certified training programs
            </p>
        </div>

        <?php if (!empty($top_featured)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($top_featured as $index => $course): ?>
                    <div class="group course-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 animate-slide-up animation-delay-<?= $index * 100 ?>">
                        <!-- Thumbnail -->
                        <div class="relative h-48 bg-gradient-to-br from-<?= $course['category_color'] ?>-50 to-<?= $course['category_color'] ?>-100 overflow-hidden">
                            <?php if ($course['thumbnail_url'] && file_exists('../public/uploads/courses/' . $course['thumbnail_url'])): ?>
                                <img src="uploads/courses/<?= htmlspecialchars($course['thumbnail_url']) ?>" 
                                     alt="<?= htmlspecialchars($course['title']) ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                    <i class="fas fa-laptop-code text-4xl text-<?= $course['category_color'] ?>-600"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 shadow-sm">
                                    <i class="fas fa-check mr-1"></i> New
                                </span>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6">
                            <div class="mb-2">
                                <span class="text-xs font-bold text-<?= $course['category_color'] ?>-600 uppercase tracking-wide">
                                    <?= htmlspecialchars($course['category_name']) ?>
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 line-clamp-2 mb-2 group-hover:text-primary-600 transition-colors min-h-[56px]">
                                <?= htmlspecialchars($course['title']) ?>
                            </h3>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed min-h-[60px]">
                                <?= htmlspecialchars(substr($course['description'], 0, 100)) ?>...
                            </p>
                            
                            <!-- Instructor & Time -->
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4 border-b border-gray-100">
                                <span class="flex items-center">
                                    <i class="fas fa-user-tie mr-1.5 text-primary-500"></i>
                                    <?= htmlspecialchars($course['instructor_name'] ?? 'Edutrack Team') ?>
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-clock mr-1.5 text-primary-500"></i>
                                    <?= $course['duration_weeks'] ? $course['duration_weeks'] . ' wks' : 'Flex' ?>
                                </span>
                            </div>
                            
                            <!-- Action -->
                            <div class="flex items-center justify-between">
                                <div class="text-2xl font-bold text-primary-600">
                                    <?= $course['price'] == 0 ? 'Free' : 'ZMW ' . number_format($course['price'], 2) ?>
                                </div>
                                <a href="course.php?id=<?= $course['id'] ?>" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 transition duration-200">
                                    View Course
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Why Choose Edutrack Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose Edutrack?</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                As a TEVETA-registered institution, we're committed to excellence
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center animate-slide-up animation-delay-100">
                <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-certificate text-yellow-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">TEVETA Registered</h3>
                <p class="text-gray-600">Officially recognized by the Technical Education, Vocational and Entrepreneurship Training Authority.</p>
            </div>
            
            <div class="text-center animate-slide-up animation-delay-200">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-laptop-code text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Practical Skills</h3>
                <p class="text-gray-600">Curriculum designed with industry experts to ensure you learn skills employers actually need.</p>
            </div>
            
            <div class="text-center animate-slide-up animation-delay-300">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-chalkboard-teacher text-purple-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Expert Tutors</h3>
                <p class="text-gray-600">Learn from certified instructors with real-world industry experience.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once '../src/templates/footer.php'; ?>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .animate-slide-up {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .animate-slide-up.animated {
        opacity: 1;
        transform: translateY(0);
    }
    .animate-fade-in {
        opacity: 0;
        animation: fadeIn 1s ease-out forwards;
    }
    @keyframes fadeIn { to { opacity: 1; } }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-slide-up, .animate-fade-in').forEach(el => observer.observe(el));
    });
</script>