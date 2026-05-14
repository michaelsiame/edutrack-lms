<?php
/**
 * Home Page
 * Main landing page for Edutrack LMS
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/classes/InstitutionPhoto.php';

$page_title = "Edutrack Computer Training College | TEVETA-Certified Tech Training in Zambia";

require_once __DIR__ . '/../src/templates/header.php';

// Fallback default slides if DB query fails or returns no active records
$heroSlides = [
    [
        'title' => 'Launch Your Tech Career',
        'subtitle' => 'With Industry-Recognized Skills',
        'description' => 'Join our growing community of Zambians who transformed their lives through TEVETA-certified programs.',
        'image_path' => '',
        'cta_text' => 'Explore Courses',
        'cta_link' => 'courses.php',
        'secondary_cta_text' => 'Visit Campus',
        'secondary_cta_link' => 'campus.php'
    ],
    [
        'title' => 'State-of-the-Art Facilities',
        'subtitle' => 'Learn on Modern Equipment',
        'description' => 'Our computer labs feature the latest hardware and software for hands-on learning.',
        'image_path' => '',
        'cta_text' => 'Take a Tour',
        'cta_link' => 'campus.php',
        'secondary_cta_text' => 'View Programs',
        'secondary_cta_link' => 'courses.php'
    ],
    [
        'title' => 'Your Success is Our Mission',
        'subtitle' => 'Real Skills, Real Careers',
        'description' => 'Our graduates work at top companies like MTN, Airtel, and major banks.',
        'image_path' => '',
        'cta_text' => 'Apply Now',
        'cta_link' => 'register.php',
        'secondary_cta_text' => 'Contact Us',
        'secondary_cta_link' => 'contact.php'
    ]
];

// Get hero slides from database (guard against missing table/schema mismatch)
try {
    $db_heroSlides = HeroSlide::getActive();
    if (!empty($db_heroSlides)) {
        $heroSlides = $db_heroSlides;
    }
} catch (Throwable $e) {
    error_log("Homepage hero slides error: " . $e->getMessage());
}

// Get featured campus photos
$featuredPhotos = [];
try {
    $featuredPhotos = InstitutionPhoto::getFeatured(4);
} catch (Throwable $e) {
    error_log("Homepage featured photos error: " . $e->getMessage());
}

// --- REALISTIC STATS (from database) ---
$stats = [
    'total_students'   => 0,
    'total_courses'    => 0,
    'total_enrollments'=> 0,
    'avg_rating'       => 0,
    'total_instructors'=> 0,
];
try {
    // Active students
    $stats['total_students'] = $pdo->query("
        SELECT COUNT(DISTINCT u.id) 
        FROM users u 
        JOIN user_roles ur ON u.id = ur.user_id 
        WHERE ur.role_id = 4 AND u.status = 'active'
    ")->fetchColumn() ?: 0;

    // Published courses
    $stats['total_courses'] = $pdo->query("
        SELECT COUNT(*) FROM courses WHERE status = 'published'
    ")->fetchColumn() ?: 0;

    // Total enrollments (all time)
    $stats['total_enrollments'] = $pdo->query("
        SELECT COUNT(*) FROM enrollments
    ")->fetchColumn() ?: 0;

    // Average course rating
    $stats['avg_rating'] = $pdo->query("
        SELECT COALESCE(AVG(rating), 0) FROM course_reviews
    ")->fetchColumn() ?: 0;

    // Active instructors
    $stats['total_instructors'] = $pdo->query("
        SELECT COUNT(DISTINCT i.id) 
        FROM instructors i 
        JOIN users u ON i.user_id = u.id 
        WHERE u.status = 'active'
    ")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    error_log("Homepage stats error: " . $e->getMessage());
}
?>

<?php
// 1. FEATURED BY CATEGORY (Optimized Query with Instructors)
$featured_by_category = [];
try {
    // We added the JOINs here to get the instructor name
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
        ORDER BY c.is_featured DESC, c.enrollment_count DESC, c.created_at DESC
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
// Collect IDs already shown in the category section to avoid duplication
$featured_ids = [];
foreach ($featured_by_category as $cat_courses) {
    foreach ($cat_courses as $course) {
        $featured_ids[] = (int)$course['id'];
    }
}
$featured_ids = array_unique($featured_ids);

$top_featured = [];
try {
    $exclude_sql = '';
    $exclude_params = [];
    if (!empty($featured_ids)) {
        $placeholders = implode(',', array_fill(0, count($featured_ids), '?'));
        $exclude_sql = " AND c.id NOT IN ($placeholders)";
        $exclude_params = $featured_ids;
    }

    $stmt = $pdo->prepare("
        SELECT c.*, 
               cc.name as category_name, 
               cc.color as category_color,
               CONCAT(u.first_name, ' ', u.last_name) as instructor_name
        FROM courses c
        JOIN course_categories cc ON c.category_id = cc.id
        LEFT JOIN instructors i ON c.instructor_id = i.id
        LEFT JOIN users u ON i.user_id = u.id
        WHERE c.status = 'published' $exclude_sql
        ORDER BY c.created_at DESC 
        LIMIT 6
    ");
    $stmt->execute($exclude_params);
    $top_featured = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Top featured courses error: " . $e->getMessage());
}

// 3. UPCOMING EVENTS
$upcoming_events = [];
try {
    $stmt = $pdo->query("
        SELECT * FROM events 
        WHERE event_date >= CURDATE() 
           OR (event_date IS NULL AND status = 'upcoming')
        ORDER BY event_date ASC, created_at DESC
        LIMIT 3
    ");
    $upcoming_events = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Upcoming events error: " . $e->getMessage());
}

// 4. NEXT INTAKE SETTINGS
$next_intake_date = null;
$next_intake_label = 'Next Intake Coming Soon';
try {
    $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'next_intake_date' LIMIT 1");
    $result = $stmt->fetch();
    if ($result) $next_intake_date = $result['setting_value'];
    
    $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'next_intake_label' LIMIT 1");
    $result = $stmt->fetch();
    if ($result && !empty($result['setting_value'])) $next_intake_label = $result['setting_value'];
} catch (PDOException $e) {
    error_log("Next intake settings error: " . $e->getMessage());
}
?>

<!-- Hero Section -->
<section class="relative text-white overflow-hidden" style="min-height: 600px;">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0">
        <?php 
        $heroImage = !empty($heroSlides[0]['image_path']) 
            ? '/uploads/hero/' . $heroSlides[0]['image_path'] 
            : '/assets/images/hero-bg-1.jpg';
        ?>
        <img src="<?= $heroImage ?>" alt="Edutrack Campus" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-primary-900/90 to-black/85"></div>
        <div class="absolute inset-0 bg-black/30"></div>
    </div>
    <!-- Decorative pattern overlay -->
    <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
        <div class="text-center bg-black/20 backdrop-blur-sm rounded-3xl p-6 md:p-10">
            <div class="mb-6 animate-fade-in">
                <span class="inline-flex items-center px-5 py-2.5 rounded-full text-sm font-bold bg-yellow-500 text-gray-900 shadow-lg border-2 border-yellow-400">
                    <i class="fas fa-certificate mr-2"></i>
                    Registered Institution
                </span>
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 animate-fade-in leading-tight">
                Transform Your Future with
                <span class="block text-yellow-400 mt-2 drop-shadow-lg">Edutrack Computer Training College</span>
            </h1>
            <p class="text-lg md:text-xl lg:text-2xl text-blue-100 mb-10 max-w-3xl mx-auto leading-relaxed">
                Zambia's premier computer training institution. Join thousands of students mastering industry-relevant skills.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center max-w-2xl mx-auto pb-6">
                <?php if (isLoggedIn()): ?>
                    <a href="<?= url('dashboard.php') ?>" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-xl text-primary-700 bg-white hover:bg-gray-50 transition duration-200 shadow-xl transform hover:-translate-y-1">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= url('register.php') ?>" class="inline-flex items-center justify-center px-8 py-4 border-2 border-yellow-400 text-base font-medium rounded-xl text-gray-900 bg-yellow-500 hover:bg-yellow-400 transition duration-200 shadow-xl transform hover:-translate-y-1">
                        <i class="fas fa-user-plus mr-2"></i>
                        Get Started Free
                    </a>
                <?php endif; ?>
                <a href="<?= url('courses.php') ?>" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white/60 text-base font-medium rounded-xl text-white hover:bg-white/10 hover:border-white transition duration-200 backdrop-blur-sm">
                    <i class="fas fa-book mr-2"></i>
                    View Our Courses
                </a>
            </div>
        </div>

        <!-- Trust Indicators -->
        <div class="mt-10 md:mt-14 pt-8 border-t border-white/10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <div class="animate-slide-up animation-delay-100 bg-white/10 backdrop-blur-md rounded-2xl p-5 md:p-6 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-14 h-14 mx-auto mb-3 bg-yellow-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-certificate text-2xl text-yellow-400"></i>
                    </div>
                    <h3 class="text-base md:text-lg font-bold text-white">Registered Institution</h3>
                    <p class="text-xs text-blue-200 mt-1">Government Certified</p>
                </div>
                <div class="animate-slide-up animation-delay-200 bg-white/10 backdrop-blur-md rounded-2xl p-5 md:p-6 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-14 h-14 mx-auto mb-3 bg-blue-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-blue-300"></i>
                    </div>
                    <h3 class="text-base md:text-lg font-bold text-white">
                        <?php if ($stats['total_students'] > 0): ?>
                            <?= number_format($stats['total_students']) ?>+ Students
                        <?php else: ?>
                            Growing Community
                        <?php endif; ?>
                    </h3>
                    <p class="text-xs text-blue-200 mt-1">Nationwide Community</p>
                </div>
                <div class="animate-slide-up animation-delay-300 bg-white/10 backdrop-blur-md rounded-2xl p-5 md:p-6 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-14 h-14 mx-auto mb-3 bg-green-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-2xl text-green-300"></i>
                    </div>
                    <h3 class="text-base md:text-lg font-bold text-white">Expert Instructors</h3>
                    <p class="text-xs text-blue-200 mt-1">Industry Professionals</p>
                </div>
                <div class="animate-slide-up animation-delay-400 bg-white/10 backdrop-blur-md rounded-2xl p-5 md:p-6 text-center border border-white/20 hover:bg-white/15 transition-all duration-300">
                    <div class="w-14 h-14 mx-auto mb-3 bg-purple-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-award text-2xl text-purple-300"></i>
                    </div>
                    <h3 class="text-base md:text-lg font-bold text-white">Career Ready</h3>
                    <p class="text-xs text-blue-200 mt-1">Job Placement Support</p>
                </div>
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
                            <?php foreach ($category_courses as $course): 
                                // --- FIX: Image Path Logic ---
                                $thumbnailUrl = null;
                                if (!empty($course['thumbnail_url'])) {
                                    if (filter_var($course['thumbnail_url'], FILTER_VALIDATE_URL)) {
                                        $thumbnailUrl = $course['thumbnail_url'];
                                    } else {
                                        $thumbnailUrl = '/uploads/courses/' . $course['thumbnail_url'];
                                    }
                                }
                            ?>
                                <div class="group course-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                                    <!-- Thumbnail -->
                                    <div class="relative h-48 bg-<?= $course['category_color'] ?>-50 overflow-hidden">
                                        <?php if ($thumbnailUrl): ?>
                                            <img src="<?= htmlspecialchars($thumbnailUrl) ?>" 
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
                <?php foreach ($top_featured as $index => $course): 
                    // --- FIX: Image Path Logic ---
                    $thumbnailUrl = null;
                    if (!empty($course['thumbnail_url'])) {
                        if (filter_var($course['thumbnail_url'], FILTER_VALIDATE_URL)) {
                            $thumbnailUrl = $course['thumbnail_url'];
                        } else {
                            $thumbnailUrl = '/uploads/courses/' . $course['thumbnail_url'];
                        }
                    }
                ?>
                    <div class="group course-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 animate-slide-up animation-delay-<?= $index * 100 ?>">
                        <!-- Thumbnail -->
                        <div class="relative h-48 bg-<?= $course['category_color'] ?>-50 overflow-hidden">
                            <?php if ($thumbnailUrl): ?>
                                <img src="<?= htmlspecialchars($thumbnailUrl) ?>" 
                                     alt="<?= htmlspecialchars($course['title']) ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-gray-100">
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

<?php if (!empty($upcoming_events)): ?>
<!-- Upcoming Events Preview -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Upcoming Events</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Stay connected with workshops, graduations, and community events
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($upcoming_events as $event): 
                $eventDate = !empty($event['event_date']) ? date('M j, Y', strtotime($event['event_date'])) : 'TBA';
            ?>
            <div class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
                <div class="relative h-48 overflow-hidden bg-gradient-to-br from-primary-50 to-blue-50">
                    <?php if (!empty($event['cover_image'])): ?>
                        <img src="<?= htmlspecialchars($event['cover_image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-5xl text-primary-300"></i>
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-600 text-white">
                            <?= htmlspecialchars($event['category'] ?? 'Event') ?>
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <i class="far fa-calendar-alt mr-2 text-primary-500"></i>
                        <?= $eventDate ?>
                        <?php if (!empty($event['location'])): ?>
                            <span class="mx-2">&bull;</span>
                            <i class="fas fa-map-marker-alt mr-1 text-primary-500"></i>
                            <?= htmlspecialchars($event['location']) ?>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors"><?= htmlspecialchars($event['title']) ?></h3>
                    <p class="text-gray-600 text-sm line-clamp-2"><?= htmlspecialchars(substr($event['description'] ?? '', 0, 120)) ?>...</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-10">
            <a href="events.php" class="inline-flex items-center px-6 py-3 border border-primary-200 text-primary-700 font-medium rounded-lg hover:bg-primary-50 transition duration-200">
                All Events <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($next_intake_date || $next_intake_label !== 'Next Intake Coming Soon'): ?>
<!-- Next Intake Banner -->
<section class="py-6 bg-secondary-500 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-rocket text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold"><?= htmlspecialchars($next_intake_label) ?></h3>
                    <?php if ($next_intake_date): ?>
                        <p class="text-white text-opacity-90">Limited spots available</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <?php if ($next_intake_date): ?>
                <div class="text-center hidden md:block">
                    <div class="text-3xl font-bold" id="countdown-days">--</div>
                    <div class="text-xs uppercase tracking-wide opacity-80">Days Left</div>
                </div>
                <?php endif; ?>
                <a href="courses.php" class="px-8 py-3 bg-white text-orange-600 rounded-lg font-bold hover:bg-gray-100 transition shadow-lg">
                    Enroll Now <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<?php if ($next_intake_date): ?>
<script>
// Countdown timer
const intakeDate = new Date('<?= $next_intake_date ?>T00:00:00');
function updateCountdown() {
    const now = new Date();
    const diff = intakeDate - now;
    const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
    document.getElementById('countdown-days').textContent = days > 0 ? days : 0;
}
updateCountdown();
setInterval(updateCountdown, 86400000); // Update daily
</script>
<?php endif; ?>
<?php endif; ?>

<?php
try {
    require_once __DIR__ . '/../src/templates/testimonials-section.php';
} catch (Throwable $e) {
    error_log("Homepage testimonials section error: " . $e->getMessage());
}
?>

<!-- Why Choose Edutrack Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose Edutrack?</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                As a registered institution, we're committed to excellence
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center animate-slide-up animation-delay-100">
                <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-certificate text-yellow-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Registered Institution</h3>
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

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>

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
