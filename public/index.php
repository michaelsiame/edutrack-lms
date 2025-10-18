<?php
require_once '../src/templates/header.php';
require_once '../src/includes/functions.php';

$page_title = "Home - Edutrack Computer Training College";
?>

<?php
// Featured courses by category (optimized query)
$featured_by_category = [];
try {
    // Get 3 featured courses per category, limit to 4 categories
    $stmt = $pdo->query("
        SELECT c.*, cc.name as category_name, cc.color as category_color
        FROM courses c
        JOIN course_categories cc ON c.category_id = cc.id
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

// Top featured courses (most popular/recent)
$top_featured = [];
try {
    $stmt = $pdo->query("
        SELECT c.*, cc.name as category_name, cc.color as category_color
        FROM courses c
        JOIN course_categories cc ON c.category_id = cc.id
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
                <span class="block text-yellow-400 mt-2">Edutrack Computer Training College</span>
            </h1>
            <p class="text-xl md:text-2xl text-primary-100 mb-8 max-w-3xl mx-auto leading-relaxed">
                Zambia's premier TEVETA-certified computer training institution. Join thousands of students mastering industry-relevant skills with government-recognized certification programs.
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
                <p class="text-sm text-primary-100">Official Government Recognition</p>
            </div>
            <div class="animate-slide-up animation-delay-200">
                <i class="fas fa-users text-3xl text-blue-300 mb-2 block"></i>
                <h3 class="text-lg font-semibold text-white">5000+ Students</h3>
                <p class="text-sm text-primary-100">Successfully Trained</p>
            </div>
            <div class="animate-slide-up animation-delay-300">
                <i class="fas fa-graduation-cap text-3xl text-green-300 mb-2 block"></i>
                <h3 class="text-lg font-semibold text-white">Expert Instructors</h3>
                <p class="text-sm text-primary-100">Industry Professionals</p>
            </div>
            <div class="animate-slide-up animation-delay-400">
                <i class="fas fa-award text-3xl text-purple-300 mb-2 block"></i>
                <h3 class="text-lg font-semibold text-white">Career Ready</h3>
                <p class="text-sm text-primary-100">Job-Ready Skills</p>
            </div>
        </div>
    </div>
</section>

<!-- Explore by Category Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Explore Our TEVETA and Institutional Certified Programs</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Choose your learning path from Edutrack's comprehensive government-recognized training programs
            </p>
        </div>

        <?php if (!empty($featured_by_category)): ?>
            <div class="space-y-16">
                <?php foreach ($featured_by_category as $category_name => $category_courses): ?>
                    <div class="animate-slide-up animation-delay-100">
                        <div class="flex items-center mb-8">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 bg-<?= $category_courses[0]['category_color'] ?>-100 rounded-xl flex items-center justify-center shadow-sm">
                                    <i class="fas fa-<?= strtolower(str_replace(' ', '-', $category_name)) ?> text-<?= $category_courses[0]['category_color'] ?>-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-6">
                                <h3 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($category_name) ?></h3>
                                <p class="text-gray-600">TEVETA-certified courses with expert-led training</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php foreach ($category_courses as $course): ?>
                                <div class="group course-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                                    <div class="relative h-40 bg-gradient-to-br from-<?= $course['category_color'] ?>-50 to-<?= $course['category_color'] ?>-100 overflow-hidden">
                                        <?php if ($course['thumbnail'] && file_exists('uploads/courses/' . $course['thumbnail'])): ?>
                                            <img src="uploads/courses/<?= htmlspecialchars($course['thumbnail']) ?>" 
                                                 alt="<?= htmlspecialchars($course['title']) ?>" 
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i class="fas fa-laptop-code text-4xl text-<?= $course['category_color'] ?>-600 opacity-60"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Category Badge -->
                                        <div class="absolute top-3 left-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                bg-<?= $course['category_color'] ?>-100 text-<?= $course['category_color'] ?>-800">
                                                <i class="fas fa-tag mr-1"></i>
                                                <?= htmlspecialchars($course['course_level']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="p-6">
                                        <h4 class="text-lg font-semibold text-gray-900 line-clamp-2 mb-3 group-hover:text-primary-600 transition-colors">
                                            <?= htmlspecialchars($course['title']) ?>
                                        </h4>
                                        
                                        <p class="text-sm text-gray-600 line-clamp-2 mb-4">
                                            <?= sanitize(substr($course['description'], 0, 100)) ?>
                                        </p>
                                        
                                        <div class="flex items-center justify-between">
                                            <span class="text-lg font-bold text-primary-600">
                                                <?= $course['price'] == 0 ? 'Free' : formatCurrency($course['price']) ?>
                                            </span>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                                <?= $course['duration_weeks'] ?> weeks
                                            </span>
                                        </div>
                                        
                                        <div class="mt-4 pt-4 border-t">
                                            <a href="course.php?id=<?= $course['id'] ?>" 
                                               class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-<?= $course['category_color'] ?>-600 hover:bg-<?= $course['category_color'] ?>-700 transition duration-200">
                                                <i class="fas fa-arrow-right mr-1"></i>
                                                View Course
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-8">
                            <a href="courses.php?category=<?= urlencode($category_name) ?>" 
                               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-<?= $category_courses[0]['category_color'] ?>-600 bg-<?= $category_courses[0]['category_color'] ?>-50 hover:bg-<?= $category_courses[0]['category_color'] ?>-100 transition duration-200">
                                View All <?= htmlspecialchars($category_name) ?> Courses
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

<!-- Featured Courses Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Top Featured Programs</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Start your journey with Edutrack's most popular TEVETA-certified training programs
            </p>
        </div>

        <?php if (!empty($top_featured)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($top_featured as $index => $course): ?>
                    <div class="group course-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 animate-slide-up animation-delay-<?= $index * 100 ?>">
                        <!-- Course Thumbnail -->
                        <div class="relative h-48 bg-gradient-to-br from-<?= $course['category_color'] ?>-50 to-<?= $course['category_color'] ?>-100 overflow-hidden">
                            <?php if ($course['thumbnail'] && file_exists('uploads/courses/' . $course['thumbnail'])): ?>
                                <img src="uploads/courses/<?= htmlspecialchars($course['thumbnail']) ?>" 
                                     alt="<?= htmlspecialchars($course['title']) ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                    <i class="fas fa-laptop-code text-4xl text-<?= $course['category_color'] ?>-600"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Course Status Badge -->
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 shadow-sm">
                                    <i class="fas fa-check mr-1"></i>
                                    TEVETA Certified
                                </span>
                            </div>
                            
                            <!-- Category Badge -->
                            <div class="absolute top-3 left-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    bg-<?= $course['category_color'] ?>-100 text-<?= $course['category_color'] ?>-800">
                                    <i class="fas fa-tag mr-1"></i>
                                    <?= htmlspecialchars($course['course_level']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Course Content -->
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-gray-900 line-clamp-2 group-hover:text-primary-600 transition-colors duration-200">
                                    <?= htmlspecialchars($course['title']) ?>
                                </h3>
                                <div class="text-right">
                                    <div class="text-xl font-bold text-primary-600">
                                        <?= $course['price'] == 0 ? 'Free' : formatCurrency($course['price']) ?>
                                    </div>
                                    <?php if (isset($course['duration_hours']) && $course['duration_hours'] > 0): ?>
                                        <div class="text-xs text-gray-500"><?= $course['duration_hours'] ?>h total</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                                <?= sanitize(substr($course['description'], 0, 120)) ?>...
                            </p>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <?php if (isset($course['instructor'])): ?>
                                    <span class="flex items-center">
                                        <i class="fas fa-chalkboard-teacher mr-1 text-primary-500"></i>
                                        <?= htmlspecialchars($course['instructor']) ?>
                                    </span>
                                <?php endif; ?>
                                <span class="flex items-center">
                                    <i class="fas fa-clock mr-1 text-primary-500"></i>
                                    <?= isset($course['duration_weeks']) ? $course['duration_weeks'] . ' weeks' : (isset($course['duration_hours']) ? $course['duration_hours'] . ' hours' : 'Varies') ?>
                                </span>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-2">
                                <a href="course.php?id=<?= $course['id'] ?>" 
                                   class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 transition duration-200">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Details
                                </a>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-200" 
                                            onclick="enrollCourse(<?= $course['id'] ?>, <?= $course['price'] ?>)">
                                        <i class="fas fa-shopping-cart mr-1"></i>
                                        Enroll Now
                                    </button>
                                <?php else: ?>
                                    <a href="login.php?redirect=course-<?= $course['id'] ?>" 
                                       class="px-4 py-2 border border-primary-600 text-sm font-medium rounded-md text-primary-600 bg-white hover:bg-primary-50 transition duration-200">
                                        <i class="fas fa-sign-in-alt mr-1"></i>
                                        Sign In
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- View All Button -->
            <div class="text-center mt-12">
                <a href="courses.php" class="inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-md text-primary-600 bg-white hover:bg-gray-50 transition duration-200 shadow-lg">
                    <i class="fas fa-arrow-right mr-2"></i>
                    View All Courses
                </a>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500 text-lg">Loading featured courses...</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Why Choose Edutrack Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose Edutrack Computer Training College?</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                As a TEVETA-registered institution, we're committed to excellence in computer training and career development
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center animate-slide-up animation-delay-100">
                <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-certificate text-yellow-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">TEVETA Registered Institution</h3>
                <p class="text-gray-600 leading-relaxed">
                    Officially recognized by the Technical Education, Vocational and Entrepreneurship Training Authority. 
                    All our certificates are government-approved and recognized by employers across Zambia and beyond.
                </p>
            </div>
            
            <div class="text-center animate-slide-up animation-delay-200">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-laptop-code text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Industry-Relevant Curriculum</h3>
                <p class="text-gray-600 leading-relaxed">
                    Our TEVETA-approved curriculum is designed with input from industry experts to ensure you learn 
                    the exact skills employers are looking for in today's job market.
                </p>
            </div>
            
            <div class="text-center animate-slide-up animation-delay-300">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-chalkboard-teacher text-purple-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Qualified Instructors</h3>
                <p class="text-gray-600 leading-relaxed">
                    Learn from TEVETA-certified instructors with real-world industry experience. 
                    At Edutrack, we provide personalized guidance to help you succeed every step of the way.
                </p>
            </div>
        </div>

        <!-- Additional Benefits -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-start p-6 bg-white rounded-lg shadow-sm">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Flexible Learning Options</h4>
                    <p class="text-gray-600">Choose between online, classroom, or hybrid learning modes to fit your schedule and learning style.</p>
                </div>
            </div>

            <div class="flex items-start p-6 bg-white rounded-lg shadow-sm">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-briefcase text-red-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Career Support</h4>
                    <p class="text-gray-600">Access job placement assistance, career counseling, and networking opportunities with our industry partners.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Success Stories from Edutrack</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Hear from our graduates who transformed their careers with TEVETA-certified training
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Testimonial 1 -->
            <div class="bg-gray-50 rounded-xl shadow-sm p-8 border border-gray-100 animate-slide-up animation-delay-100 hover:shadow-md transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0 mt-1">
                        <i class="fas fa-user text-primary-600 text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Sarah M.</h4>
                        <p class="text-sm text-gray-500">Web Development Graduate</p>
                    </div>
                </div>
                <p class="text-gray-700 italic mb-4 leading-relaxed">
                    "Edutrack's TEVETA-certified program changed my life. The practical training and recognized certification 
                    helped me land a developer position within 3 months of graduation!"
                </p>
                <div class="flex items-center space-x-1 text-yellow-400">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
            </div>
            
            <!-- Testimonial 2 -->
            <div class="bg-gray-50 rounded-xl shadow-sm p-8 border border-gray-100 animate-slide-up animation-delay-200 hover:shadow-md transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0 mt-1">
                        <i class="fas fa-user text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Michael K.</h4>
                        <p class="text-sm text-gray-500">Digital Marketing Graduate</p>
                    </div>
                </div>
                <p class="text-gray-700 italic mb-4 leading-relaxed">
                    "The instructors at Edutrack are industry professionals who genuinely care about your success. 
                    My TEVETA certificate has been recognized by every employer I've interviewed with!"
                </p>
                <div class="flex items-center space-x-1 text-yellow-400">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
            </div>
            
            <!-- Testimonial 3 -->
            <div class="bg-gray-50 rounded-xl shadow-sm p-8 border border-gray-100 animate-slide-up animation-delay-300 hover:shadow-md transition-shadow">
                <div class="flex items-start mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0 mt-1">
                        <i class="fas fa-user text-purple-600 text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Lydia N.</h4>
                        <p class="text-sm text-gray-500">Data Analysis Graduate</p>
                    </div>
                </div>
                <p class="text-gray-700 italic mb-4 leading-relaxed">
                    "From zero coding knowledge to confidently analyzing business data. Edutrack's hands-on approach 
                    and TEVETA certification gave me the credibility I needed to excel in my career."
                </p>
                <div class="flex items-center space-x-1 text-yellow-400">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-20 bg-gradient-to-r from-primary-600 to-primary-800 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <div class="relative max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <div class="animate-fade-in">
            <div class="mb-6">
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-yellow-500 text-gray-900 shadow-lg">
                    <i class="fas fa-certificate mr-2"></i>
                    TEVETA Registered Institution
                </span>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Start Your Journey at Edutrack?</h2>
            <p class="text-xl mb-8 text-primary-100 leading-relaxed">
                Join Zambia's premier TEVETA-registered computer training college and take the 
                first step toward a brighter career future with government-recognized certification.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-md text-primary-600 bg-white hover:bg-gray-50 transition duration-200 shadow-lg transform hover:-translate-y-1">
                        <i class="fas fa-rocket mr-2"></i>
                        Continue Learning
                    </a>
                <?php else: ?>
                    <a href="register.php" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-md text-primary-600 bg-white hover:bg-gray-50 transition duration-200 shadow-lg transform hover:-translate-y-1">
                        <i class="fas fa-user-plus mr-2"></i>
                        Create Free Account
                    </a>
                    <a href="courses.php" class="inline-flex items-center justify-center px-8 py-4 border border-white text-base font-medium rounded-md text-white hover:bg-white hover:text-primary-600 transition duration-200">
                        <i class="fas fa-book mr-2"></i>
                        Browse All Courses
                    </a>
                <?php endif; ?>
            </div>
            <p class="text-primary-200 mt-6 text-sm bg-primary-900/20 px-4 py-2 rounded-lg inline-block">
                No credit card required • Flexible payment plans • TEVETA-certified certificates
            </p>
        </div>
    </div>
</section>

<?php require_once '../src/templates/footer.php'; ?>

<script>
    // Enhanced course enrollment function
    function enrollCourse(courseId, price) {
        if (!confirm(`Enroll in this TEVETA-certified course for ${price === 0 ? 'Free' : 'K' + price}?`)) {
            return;
        }
        
        // Show loading state
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        const originalClasses = button.className;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Enrolling...';
        button.disabled = true;
        button.className = 'px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-gray-100 cursor-not-allowed';
        
        // Simulate API call
        setTimeout(() => {
            // Simulate success
            button.innerHTML = '<i class="fas fa-check mr-1"></i>Enrolled!';
            button.className = 'px-4 py-2 border border-green-500 text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 transition duration-200';
            button.disabled = false;
            
            // Optional: Redirect to course or show success modal
            setTimeout(() => {
                alert('Enrollment successful! Welcome to Edutrack Computer Training College. You can now start learning this TEVETA-certified course.');
                // window.location.href = `course.php?id=${courseId}`;
            }, 1000);
        }, 2000);
    }

    // Smooth scroll animations
    function initAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-slide-up, .animate-fade-in').forEach(el => {
            observer.observe(el);
        });
    }

    // Staggered animation for course cards
    document.addEventListener('DOMContentLoaded', function() {
        initAnimations();
        
        const courseCards = document.querySelectorAll('.course-card');
        courseCards.forEach((card, index) => {
            card.style.animationDelay = (index * 0.15) + 's';
        });

        // Add hover effects
        courseCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });

    // Add scroll event for animations
    let ticking = false;
    function updateAnimations() {
        if (!ticking) {
            requestAnimationFrame(() => {
                initAnimations();
                ticking = false;
            });
            ticking = true;
        }
    }
    
    window.addEventListener('scroll', updateAnimations);
</script>

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
    
    @keyframes fadeIn {
        to {
            opacity: 1;
        }
    }
    
    @media (max-width: 768px) {
        .course-card {
            transform: none !important;
        }
    }
</style>