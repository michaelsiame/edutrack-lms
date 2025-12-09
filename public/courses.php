<?php
/**
 * All Courses Page - Enhanced UI/UX
 * Browse and filter courses with improved design and functionality
 */

require_once '../src/bootstrap.php';

$page_title = "Browse Our Courses - Edutrack Computer Training College";

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$level_filter = $_GET['level'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Get all categories for filter dropdown
$db = Database::getInstance();
$categories = $db->fetchAll("SELECT * FROM course_categories WHERE is_active = 1 ORDER BY display_order, name");

// Get featured courses
$featured_courses = $db->fetchAll("
    SELECT c.*, cc.name as category_name, cc.color as category_color,
           CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
           COUNT(e.id) as enrollment_count
    FROM courses c
    LEFT JOIN course_categories cc ON c.category_id = cc.id
    LEFT JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN users u ON i.user_id = u.id
    LEFT JOIN enrollments e ON c.id = e.course_id AND e.enrollment_status = 'Enrolled'
    WHERE c.status = 'published' AND c.is_featured = 1
    GROUP BY c.id
    ORDER BY c.rating DESC, c.created_at DESC
    LIMIT 6
");

// Build course query with filters
$query = "SELECT c.*, cc.name as category_name, cc.color as category_color,
          CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
          COUNT(e.id) as enrollment_count,
          COALESCE(AVG(cr.rating), 0) as average_rating,
          COUNT(cr.id) as review_count
          FROM courses c
          LEFT JOIN course_categories cc ON c.category_id = cc.id
          LEFT JOIN instructors i ON c.instructor_id = i.id
          LEFT JOIN users u ON i.user_id = u.id
          LEFT JOIN enrollments e ON c.id = e.course_id AND e.enrollment_status = 'Enrolled'
          LEFT JOIN course_reviews cr ON c.id = cr.course_id
          WHERE c.status = 'published'";

$params = [];
$where_conditions = [];

if (!empty($search)) {
    $where_conditions[] = "(c.title LIKE ? OR c.description LIKE ? OR c.short_description LIKE ?)";
    $searchTerm = "%{$search}%";
    array_push($params, $searchTerm, $searchTerm, $searchTerm);
}

if (!empty($category_filter)) {
    $where_conditions[] = "c.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($level_filter)) {
    $where_conditions[] = "c.level = ?";
    $params[] = $level_filter;
}

if (!empty($where_conditions)) {
    $query .= " AND " . implode(" AND ", $where_conditions);
}

$query .= " GROUP BY c.id";

// Apply sorting
switch ($sort) {
    case 'popular':
        $query .= " ORDER BY enrollment_count DESC, average_rating DESC";
        break;
    case 'rating':
        $query .= " ORDER BY average_rating DESC, enrollment_count DESC";
        break;
    case 'price_low':
        $query .= " ORDER BY c.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY c.price DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY c.created_at DESC";
        break;
}

$courses = $db->fetchAll($query, $params);

// Count total courses
$total_courses = count($courses);

// Prepare category statistics
$category_stats = $db->fetchAll("
    SELECT cc.id, cc.name, cc.color, COUNT(c.id) as course_count
    FROM course_categories cc
    LEFT JOIN courses c ON cc.id = c.category_id AND c.status = 'published'
    WHERE cc.is_active = 1
    GROUP BY cc.id
    ORDER BY cc.display_order
");

require_once '../src/templates/header.php';
?>

<!-- Page Header with Stats -->
<section class="relative bg-gradient-to-br from-blue-900 via-blue-800 to-purple-900 text-white py-16 md:py-20 overflow-hidden">
    <!-- Background pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.4"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-500/20 border border-blue-400/30 mb-6">
                <i class="fas fa-graduation-cap mr-2"></i>
                TEVETA CERTIFIED PROGRAMS
            </span>
            
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4 tracking-tight">
                <span class="block">Transform Your Future</span>
                <span class="block text-transparent bg-clip-text bg-gradient-to-r from-blue-300 to-purple-300">
                    With Professional Training
                </span>
            </h1>
            
            <p class="text-xl text-blue-100 max-w-3xl mx-auto mb-8 leading-relaxed">
                Industry-recognized certificate programs designed to equip you with in-demand skills for today's job market
            </p>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto mt-12">
                <div class="text-center p-4 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20">
                    <div class="text-3xl font-bold mb-1"><?= $total_courses ?></div>
                    <div class="text-sm text-blue-200">Certificates</div>
                </div>
                <div class="text-center p-4 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20">
                    <div class="text-3xl font-bold mb-1">6</div>
                    <div class="text-sm text-blue-200">Categories</div>
                </div>
                <div class="text-center p-4 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20">
                    <div class="text-3xl font-bold mb-1">500+</div>
                    <div class="text-sm text-blue-200">Graduates</div>
                </div>
                <div class="text-center p-4 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20">
                    <div class="text-3xl font-bold mb-1">100%</div>
                    <div class="text-sm text-blue-200">Practical Training</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Navigation -->
<section class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-wrap gap-2 justify-center">
            <a href="courses.php" 
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                      <?= empty($category_filter) ? 'bg-blue-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                All Categories
            </a>
            <?php foreach ($category_stats as $cat): ?>
                <a href="courses.php?category=<?= $cat['id'] ?>" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                          <?= $category_filter == $cat['id'] ? 'bg-blue-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>"
                   style="border-left: 3px solid <?= $cat['color'] ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                    <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full bg-gray-200">
                        <?= $cat['course_count'] ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
<?php if (!empty($featured_courses)): ?>
<section class="py-12 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-3">
                <i class="fas fa-star text-yellow-500 mr-2"></i>
                Featured Programs
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Our most popular and highly-rated certificate programs
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <?php foreach ($featured_courses as $course): ?>
                <div class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                    <!-- Featured Badge -->
                    <div class="absolute top-4 left-4 z-10">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-yellow-500 to-orange-500 text-white shadow-lg">
                            <i class="fas fa-star mr-1"></i>
                            FEATURED
                        </span>
                    </div>
                    
                    <!-- Course Image -->
                    <div class="relative h-48 bg-gradient-to-br from-blue-50 to-purple-50 overflow-hidden">
                        <?php if ($course['thumbnail_url']): ?>
                            <img src="uploads/courses/<?= htmlspecialchars($course['thumbnail_url']) ?>" 
                                 alt="<?= htmlspecialchars($course['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-laptop-code text-6xl text-blue-200 group-hover:text-blue-300 transition-colors duration-300"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Category Badge -->
                        <div class="absolute bottom-4 right-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white shadow-lg"
                                  style="background-color: <?= $course['category_color'] ?>">
                                <?= htmlspecialchars($course['level']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Course Content -->
                    <div class="p-6">
                        <div class="mb-3">
                            <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">
                                <?= htmlspecialchars($course['category_name']) ?>
                            </span>
                        </div>
                        
                        <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
                            <a href="course.php?id=<?= $course['id'] ?>" class="hover:underline">
                                <?= htmlspecialchars($course['title']) ?>
                            </a>
                        </h3>
                        
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                            <?= htmlspecialchars($course['short_description'] ?? substr($course['description'], 0, 120)) ?>...
                        </p>
                        
                        <!-- Course Info -->
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center space-x-4">
                                <span class="flex items-center">
                                    <i class="fas fa-user-tie text-blue-500 mr-1.5"></i>
                                    <?= htmlspecialchars($course['instructor_name']) ?>
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-users text-green-500 mr-1.5"></i>
                                    <?= $course['enrollment_count'] ?> enrolled
                                </span>
                            </div>
                            
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-500 mr-1"></i>
                                <span class="font-semibold"><?= number_format($course['rating'], 1) ?></span>
                            </div>
                        </div>
                        
                        <!-- Price & CTA -->
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-blue-600">
                                    ZMW <?= number_format($course['price'], 2) ?>
                                </div>
                                <?php if ($course['discount_price']): ?>
                                    <div class="text-sm text-gray-400 line-through">
                                        ZMW <?= number_format($course['discount_price'], 2) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="course.php?id=<?= $course['id'] ?>"
                               class="inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                View Details
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform duration-200"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Main Content Area -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Filters Sidebar -->
        <div class="lg:w-1/4">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 mb-6 sticky top-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-600"></i>
                    Filters
                </h3>
                
                <form method="GET" action="" id="courseFilters">
                    <!-- Search -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-1"></i>
                            Search Courses
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="search"
                                   value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Type course name..."
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   id="courseSearch">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tags mr-1"></i>
                            Category
                        </label>
                        <select name="category"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" 
                                        <?= $category_filter == $cat['id'] ? 'selected' : '' ?>
                                        style="border-left: 3px solid <?= $cat['color'] ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Level Filter -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-chart-line mr-1"></i>
                            Difficulty Level
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="level" value="" 
                                       class="mr-3 text-blue-600 focus:ring-blue-500" 
                                       <?= empty($level_filter) ? 'checked' : '' ?>>
                                <span class="text-gray-700">All Levels</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="level" value="Beginner" 
                                       class="mr-3 text-blue-600 focus:ring-blue-500"
                                       <?= $level_filter === 'Beginner' ? 'checked' : '' ?>>
                                <span class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                                    Beginner
                                </span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="level" value="Intermediate" 
                                       class="mr-3 text-blue-600 focus:ring-blue-500"
                                       <?= $level_filter === 'Intermediate' ? 'checked' : '' ?>>
                                <span class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>
                                    Intermediate
                                </span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="level" value="Advanced" 
                                       class="mr-3 text-blue-600 focus:ring-blue-500"
                                       <?= $level_filter === 'Advanced' ? 'checked' : '' ?>>
                                <span class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                                    Advanced
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Sort Options -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sort mr-1"></i>
                            Sort By
                        </label>
                        <select name="sort"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                id="sortOptions">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        </select>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-search mr-2"></i>
                            Apply Filters
                        </button>
                        
                        <?php if ($search || $category_filter || $level_filter || $sort !== 'newest'): ?>
                            <a href="courses.php"
                               class="block w-full px-4 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition duration-200 text-center">
                                <i class="fas fa-times mr-2"></i>
                                Clear All Filters
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
                
                <!-- Quick Stats -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Quick Stats
                    </h4>
                    <div class="space-y-2">
                        <?php foreach ($category_stats as $cat): ?>
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center">
                                    <span class="w-2 h-2 rounded-full mr-2" style="background-color: <?= $cat['color'] ?>"></span>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </span>
                                <span class="font-semibold"><?= $cat['course_count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Need Help Card -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-6">
                <div class="text-center">
                    <i class="fas fa-headset text-3xl text-blue-600 mb-4"></i>
                    <h4 class="font-bold text-gray-900 mb-2">Need Help Choosing?</h4>
                    <p class="text-sm text-gray-600 mb-4">
                        Our advisors can help you select the right program for your career goals
                    </p>
                    <a href="contact.php"
                       class="inline-flex items-center px-4 py-2 bg-white text-blue-600 font-medium rounded-lg border border-blue-200 hover:bg-blue-50 transition duration-200">
                        <i class="fas fa-comment-alt mr-2"></i>
                        Talk to Advisor
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Courses Grid -->
        <div class="lg:w-3/4">
            <!-- Results Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            All Certificate Programs
                        </h2>
                        <p class="text-gray-600 mt-1">
                            Showing <span class="font-semibold"><?= $total_courses ?></span> certificate programs
                            <?php if ($search): ?>
                                for "<span class="font-semibold"><?= htmlspecialchars($search) ?></span>"
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- View Toggle -->
                        <div class="hidden sm:flex items-center bg-gray-100 rounded-lg p-1">
                            <button id="gridView" class="p-2 rounded-md bg-white shadow-sm">
                                <i class="fas fa-th-large text-blue-600"></i>
                            </button>
                            <button id="listView" class="p-2 rounded-md hover:text-blue-600">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                        
                        <!-- Results Count -->
                        <span class="text-sm text-gray-500">
                            Page 1 of 1
                        </span>
                    </div>
                </div>
                
                <!-- Active Filters -->
                <?php if ($search || $category_filter || $level_filter): ?>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center text-sm text-gray-600 mb-2">
                            <i class="fas fa-filter mr-2"></i>
                            Active Filters:
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php if ($search): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                    Search: <?= htmlspecialchars($search) ?>
                                    <a href="?<?= http_build_query(array_filter(['category' => $category_filter, 'level' => $level_filter, 'sort' => $sort])) ?>" 
                                       class="ml-1.5 text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($category_filter): 
                                $selected_category = array_filter($categories, fn($cat) => $cat['id'] == $category_filter)[0] ?? null;
                            ?>
                                <?php if ($selected_category): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-purple-100 text-purple-800">
                                        <?= htmlspecialchars($selected_category['name']) ?>
                                        <a href="?<?= http_build_query(array_filter(['search' => $search, 'level' => $level_filter, 'sort' => $sort])) ?>" 
                                           class="ml-1.5 text-purple-600 hover:text-purple-800">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if ($level_filter): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                    Level: <?= htmlspecialchars($level_filter) ?>
                                    <a href="?<?= http_build_query(array_filter(['search' => $search, 'category' => $category_filter, 'sort' => $sort])) ?>" 
                                       class="ml-1.5 text-green-600 hover:text-green-800">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Courses Grid/List View -->
            <div id="coursesContainer" class="<?= empty($courses) ? '' : 'grid-view grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6' ?>">
                <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $course): 
                        // Calculate average rating
                        $rating = $course['average_rating'] ? number_format($course['average_rating'], 1) : '0.0';
                        $rating_count = $course['review_count'] ?: 0;
                        
                        // Determine color based on category
                        $color_class = match($course['category_color'] ?? '#333333') {
                            '#333333' => 'blue',
                            '#2563eb' => 'blue',
                            '#059669' => 'green',
                            '#dc2626' => 'red',
                            '#ea580c' => 'orange',
                            '#7c3aed' => 'purple',
                            '#db2777' => 'pink',
                            default => 'blue'
                        };
                    ?>
                        <div class="course-card group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 border border-gray-200">
                            <!-- Course Badge -->
                            <?php if ($course['is_featured']): ?>
                                <div class="absolute top-3 left-3 z-10">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-gradient-to-r from-yellow-500 to-orange-500 text-white shadow-md">
                                        <i class="fas fa-star mr-1"></i>
                                        Featured
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Thumbnail -->
                            <div class="relative h-40 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                                <?php if ($course['thumbnail_url'] && file_exists('../public/uploads/courses/' . $course['thumbnail_url'])): ?>
                                    <img src="uploads/courses/<?= htmlspecialchars($course['thumbnail_url']) ?>" 
                                         alt="<?= htmlspecialchars($course['title']) ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <div class="text-center">
                                            <i class="fas fa-graduation-cap text-4xl text-gray-300 group-hover:text-<?= $color_class ?>-400 transition-colors duration-300"></i>
                                            <p class="text-xs text-gray-400 mt-2"><?= htmlspecialchars($course['category_name']) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- TEVETA Badge -->
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 shadow-sm">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        TEVETA
                                    </span>
                                </div>
                                
                                <!-- Category Overlay -->
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-3">
                                    <span class="text-xs font-medium text-white">
                                        <i class="fas fa-tag mr-1"></i>
                                        <?= htmlspecialchars($course['category_name']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Course Content -->
                            <div class="p-5">
                                <!-- Level Badge -->
                                <div class="mb-3">
                                    <?php
                                        $level_color = match($course['level']) {
                                            'Beginner' => 'green',
                                            'Intermediate' => 'yellow',
                                            'Advanced' => 'red',
                                            default => 'gray'
                                        };
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                bg-<?= $level_color ?>-100 text-<?= $level_color ?>-800 border border-<?= $level_color ?>-200">
                                        <i class="fas fa-signal mr-1 text-xs"></i>
                                        <?= htmlspecialchars($course['level']) ?>
                                    </span>
                                </div>
                                
                                <!-- Title -->
                                <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-<?= $color_class ?>-600 transition-colors duration-200 min-h-[56px]">
                                    <a href="course.php?id=<?= $course['id'] ?>" class="hover:underline">
                                        <?= htmlspecialchars($course['title']) ?>
                                    </a>
                                </h3>
                                
                                <!-- Short Description -->
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed min-h-[40px]">
                                    <?= htmlspecialchars(substr($course['short_description'] ?? $course['description'] ?? '', 0, 100)) ?>...
                                </p>
                                
                                <!-- Course Meta -->
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4 border-b border-gray-100">
                                    <div class="flex items-center space-x-4">
                                        <span class="flex items-center" title="Instructor">
                                            <i class="fas fa-chalkboard-teacher text-<?= $color_class ?>-500 mr-1.5"></i>
                                            <?= htmlspecialchars($course['instructor_name'] ?? 'Staff') ?>
                                        </span>
                                        <span class="flex items-center" title="Duration">
                                            <i class="fas fa-clock text-<?= $color_class ?>-500 mr-1.5"></i>
                                            <?= $course['duration_weeks'] ? $course['duration_weeks'] . ' weeks' : ($course['total_hours'] ? $course['total_hours'] . ' hrs' : 'Flexible') ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Rating -->
                                    <?php if ($rating_count > 0): ?>
                                        <div class="flex items-center" title="Rating">
                                            <i class="fas fa-star text-yellow-500 mr-1"></i>
                                            <span class="font-semibold"><?= $rating ?></span>
                                            <span class="text-gray-400 ml-1">(<?= $rating_count ?>)</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Price & CTA -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-2xl font-bold text-<?= $color_class ?>-600">
                                            <?= $course['price'] == 0 ? 'Free' : 'ZMW ' . number_format($course['price'], 2) ?>
                                        </div>
                                        <?php if ($course['discount_price'] && $course['discount_price'] < $course['price']): ?>
                                            <div class="text-sm text-gray-400 line-through">
                                                ZMW <?= number_format($course['discount_price'], 2) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="course.php?id=<?= $course['id'] ?>" 
                                           class="inline-flex items-center px-4 py-2 border border-<?= $color_class ?>-600 text-sm font-medium rounded-lg text-<?= $color_class ?>-600 hover:bg-<?= $color_class ?>-50 transition duration-200"
                                           title="View Details">
                                            <i class="fas fa-eye mr-2"></i>
                                            View
                                        </a>
                                        <?php if (isLoggedIn()): ?>
                                            <?php
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
                                            ?>
                                            <?php if (!$isEnrolled): ?>
                                                <a href="enroll.php?course_id=<?= $course['id'] ?>"
                                                   class="inline-flex items-center px-4 py-2 bg-<?= $color_class ?>-600 text-white text-sm font-medium rounded-lg hover:bg-<?= $color_class ?>-700 transition duration-200 shadow-sm hover:shadow"
                                                   title="Enroll Now">
                                                    <i class="fas fa-shopping-cart mr-2"></i>
                                                    Enroll
                                                </a>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 text-sm font-medium rounded-lg">
                                                    <i class="fas fa-check-circle mr-2"></i>
                                                    Enrolled
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="register.php?redirect=course.php?id=<?= $course['id'] ?>"
                                               class="inline-flex items-center px-4 py-2 bg-<?= $color_class ?>-600 text-white text-sm font-medium rounded-lg hover:bg-<?= $color_class ?>-700 transition duration-200 shadow-sm hover:shadow">
                                                <i class="fas fa-sign-in-alt mr-2"></i>
                                                Enroll
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- No Results State -->
                    <div class="col-span-full">
                        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                            <div class="max-w-md mx-auto">
                                <i class="fas fa-search text-5xl text-gray-300 mb-6"></i>
                                <h3 class="text-2xl font-bold text-gray-900 mb-3">No Courses Found</h3>
                                <p class="text-gray-600 mb-6">
                                    We couldn't find any courses matching your criteria. Try adjusting your filters or browse our featured programs.
                                </p>
                                <div class="space-y-4">
                                    <a href="courses.php" 
                                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition duration-200">
                                        <i class="fas fa-redo mr-2"></i>
                                        Clear All Filters
                                    </a>
                                    <div class="text-sm text-gray-500">
                                        Need help? <a href="contact.php" class="text-blue-600 hover:text-blue-800 font-medium">Contact our advisors</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination (if needed) -->
            <?php if ($total_courses > 12): ?>
                <div class="mt-12">
                    <nav class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">
                        <div class="-mt-px flex w-0 flex-1">
                            <a href="#" class="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                                <i class="fas fa-arrow-left mr-3"></i>
                                Previous
                            </a>
                        </div>
                        <div class="hidden md:-mt-px md:flex">
                            <a href="#" class="inline-flex items-center border-t-2 border-blue-500 px-4 pt-4 text-sm font-medium text-blue-600">1</a>
                            <a href="#" class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">2</a>
                            <a href="#" class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">3</a>
                        </div>
                        <div class="-mt-px flex w-0 flex-1 justify-end">
                            <a href="#" class="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                                Next
                                <i class="fas fa-arrow-right ml-3"></i>
                            </a>
                        </div>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Benefits Section -->
<section class="bg-gray-50 border-t border-gray-200 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-3">
                Why Choose Edutrack?
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                We provide industry-leading training that prepares you for real-world success
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-award text-2xl text-blue-600"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">TEVETA Certified</h3>
                <p class="text-gray-600 text-sm">
                    Accredited programs recognized by industry employers
                </p>
            </div>
            
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-briefcase text-2xl text-green-600"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Career Focused</h3>
                <p class="text-gray-600 text-sm">
                    Practical skills that employers are looking for
                </p>
            </div>
            
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-users text-2xl text-purple-600"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Expert Instructors</h3>
                <p class="text-gray-600 text-sm">
                    Learn from industry professionals with real experience
                </p>
            </div>
            
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fas fa-handshake text-2xl text-orange-600"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Job Support</h3>
                <p class="text-gray-600 text-sm">
                    Career guidance and placement assistance
                </p>
            </div>
        </div>
    </div>
</section>

<!-- JavaScript for Enhanced Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View Toggle
    const gridViewBtn = document.getElementById('gridView');
    const listViewBtn = document.getElementById('listView');
    const coursesContainer = document.getElementById('coursesContainer');
    
    if (gridViewBtn && listViewBtn && coursesContainer) {
        gridViewBtn.addEventListener('click', () => {
            coursesContainer.className = 'grid-view grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6';
            gridViewBtn.className = 'p-2 rounded-md bg-white shadow-sm';
            listViewBtn.className = 'p-2 rounded-md hover:text-blue-600';
        });
        
        listViewBtn.addEventListener('click', () => {
            coursesContainer.className = 'list-view space-y-4';
            gridViewBtn.className = 'p-2 rounded-md hover:text-blue-600';
            listViewBtn.className = 'p-2 rounded-md bg-white shadow-sm';
        });
    }
    
    // Real-time Filter Updates
    const filters = ['courseSearch', 'categoryFilter', 'sortOptions'];
    filters.forEach(filterId => {
        const element = document.getElementById(filterId);
        if (element) {
            element.addEventListener('change', () => {
                document.getElementById('courseFilters').submit();
            });
        }
    });
    
    // Course Search with Debounce
    let searchTimeout;
    const searchInput = document.getElementById('courseSearch');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (e.target.value.length >= 2 || e.target.value.length === 0) {
                    document.getElementById('courseFilters').submit();
                }
            }, 500);
        });
    }
    
    // Smooth scroll to top on filter change
    const filterForm = document.getElementById('courseFilters');
    if (filterForm) {
        filterForm.addEventListener('submit', () => {
            window.scrollTo({
                top: 400,
                behavior: 'smooth'
            });
        });
    }
    
    // Course Card Animation on Hover
    const courseCards = document.querySelectorAll('.course-card');
    courseCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
        });
    });
});
</script>

<style>
/* Custom Styles for Enhanced UI */
.course-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.course-card:hover {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

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

/* Gradient backgrounds for different categories */
.category-bg-blue {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.category-bg-green {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.category-bg-purple {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.category-bg-orange {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
}

/* List view styles */
.list-view .course-card {
    display: flex;
    height: auto;
}

.list-view .course-card > div:first-child {
    width: 200px;
    flex-shrink: 0;
}

.list-view .course-card > div:last-child {
    flex: 1;
}

@media (max-width: 768px) {
    .list-view .course-card {
        flex-direction: column;
    }
    
    .list-view .course-card > div:first-child {
        width: 100%;
        height: 200px;
    }
}

/* Loading animation */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.loading {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<?php require_once '../src/templates/footer.php'; ?>