<?php
/**
 * Browse Courses Page
 * Enhanced UI/UX Version
 */

require_once '../src/bootstrap.php';

$page_title = "Browse Courses - EduTrack LMS";

// --- 1. CONFIGURATION & INPUTS ---
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 9; // Courses per page
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$category_filter = $_GET['category'] ?? '';
$level_filter = $_GET['level'] ?? '';
$sort_filter = $_GET['sort'] ?? 'newest'; // newest, price_low, price_high, popular

// --- 2. DATA FETCHING ---
$db = Database::getInstance();

// Fetch Categories with their DB colors
$categories = $db->fetchAll("SELECT * FROM course_categories WHERE is_active = 1 ORDER BY name");

// Build Query
$base_query = " FROM courses c
                LEFT JOIN course_categories cc ON c.category_id = cc.id
                LEFT JOIN instructors i ON c.instructor_id = i.id
                LEFT JOIN users u ON i.user_id = u.id
                WHERE c.status = 'published'";

$params = [];

// Apply Filters
if (!empty($search)) {
    $base_query .= " AND (c.title LIKE ? OR c.description LIKE ? OR c.short_description LIKE ?)";
    $searchTerm = "%{$search}%";
    array_push($params, $searchTerm, $searchTerm, $searchTerm);
}

if (!empty($category_filter)) {
    $base_query .= " AND c.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($level_filter)) {
    $base_query .= " AND c.level = ?";
    $params[] = $level_filter;
}

// Get Total Count for Pagination
$total_courses = $db->fetchColumn("SELECT COUNT(*)" . $base_query, $params);
$total_pages = ceil($total_courses / $limit);

// Apply Sorting
switch ($sort_filter) {
    case 'price_low':
        $order_sql = "ORDER BY COALESCE(c.discount_price, c.price) ASC";
        break;
    case 'price_high':
        $order_sql = "ORDER BY COALESCE(c.discount_price, c.price) DESC";
        break;
    case 'popular':
        $order_sql = "ORDER BY c.enrollment_count DESC";
        break;
    case 'rating':
        $order_sql = "ORDER BY c.rating DESC";
        break;
    default: // newest
        $order_sql = "ORDER BY c.created_at DESC";
        break;
}

// Final Select Query
$query = "SELECT c.*, 
          cc.name as category_name, 
          COALESCE(cc.color, '#4F46E5') as category_color,
          CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
          i.is_verified
          " . $base_query . " " . $order_sql . " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

$courses = $db->fetchAll($query, $params);

// Helper function to render stars
function renderStars($rating) {
    $html = '<div class="flex items-center">';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="fas fa-star text-yellow-400 text-xs"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="fas fa-star-half-alt text-yellow-400 text-xs"></i>';
        } else {
            $html .= '<i class="far fa-star text-gray-300 text-xs"></i>';
        }
    }
    $html .= '</div>';
    return $html;
}

require_once '../src/templates/header.php';
?>

<!-- Hero Section with Pattern -->
<section class="relative bg-primary-900 overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background-image: url('assets/img/circuit-board.svg');"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-primary-900 via-primary-800 to-indigo-900 opacity-95"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4">
            Advance Your Career
        </h1>
        <p class="text-lg text-primary-200 max-w-2xl mx-auto mb-8">
            Explore <?= number_format($total_courses) ?>+ TEVETA certified courses in Technology, Business, and Design.
        </p>
        
        <!-- Quick Search Bar in Hero -->
        <div class="max-w-2xl mx-auto">
            <form action="" method="GET" class="relative">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400 z-10"></i>
                <input type="text" 
                       name="search" 
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="What do you want to learn today?" 
                       class="w-full pl-12 pr-4 py-3.5 rounded-full shadow-lg border-none focus:ring-2 focus:ring-primary-400 text-gray-900 placeholder-gray-500">
                <button type="submit" class="absolute right-2 top-2 bg-primary-600 text-white px-6 py-1.5 rounded-full hover:bg-primary-700 transition font-medium">
                    Search
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Filter & Sort Bar -->
<section class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <form id="filterForm" method="GET" class="flex flex-col lg:flex-row justify-between gap-4 items-center">
            
            <!-- Retain search term if exists -->
            <?php if(!empty($search)): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <?php endif; ?>

            <!-- Filters Left -->
            <div class="flex flex-wrap gap-3 w-full lg:w-auto">
                <!-- Category Filter -->
                <div class="relative min-w-[180px] flex-1 lg:flex-none">
                    <select name="category" onchange="this.form.submit()" 
                            class="w-full pl-3 pr-10 py-2 border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Level Filter -->
                <div class="relative min-w-[150px] flex-1 lg:flex-none">
                    <select name="level" onchange="this.form.submit()"
                            class="w-full pl-3 pr-10 py-2 border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm">
                        <option value="">All Levels</option>
                        <option value="Beginner" <?= $level_filter === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="Intermediate" <?= $level_filter === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="Advanced" <?= $level_filter === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                    </select>
                </div>
            </div>

            <!-- Sort Right -->
            <div class="flex items-center gap-3 w-full lg:w-auto justify-end">
                <span class="text-sm text-gray-500 hidden sm:inline">Sort by:</span>
                <select name="sort" onchange="this.form.submit()"
                        class="pl-3 pr-8 py-2 border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm bg-gray-50">
                    <option value="newest" <?= $sort_filter === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="popular" <?= $sort_filter === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                    <option value="rating" <?= $sort_filter === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                    <option value="price_low" <?= $sort_filter === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_high" <?= $sort_filter === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </div>
        </form>
    </div>
</section>

<!-- Main Content -->
<section class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Stats Row -->
        <?php if ($search || $category_filter || $level_filter): ?>
            <div class="flex justify-between items-center mb-6">
                <p class="text-gray-600">
                    Found <span class="font-bold text-gray-900"><?= $total_courses ?></span> courses
                    <?php if($search): ?> for "<span class="italic"><?= htmlspecialchars($search) ?></span>"<?php endif; ?>
                </p>
                <a href="courses.php" class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                    <i class="fas fa-times-circle mr-1"></i> Clear filters
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty($courses)): ?>
            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                <?php foreach ($courses as $course): 
                    // Calculate Discount logic
                    $has_discount = !empty($course['discount_price']) && $course['discount_price'] < $course['price'];
                    $current_price = $has_discount ? $course['discount_price'] : $course['price'];
                    $bg_color = $course['category_color']; // Using DB Color
                ?>
                    <!-- Enhanced Card -->
                    <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:border-primary-200 transition-all duration-300 flex flex-col h-full relative">
                        
                        <!-- Image Container -->
                        <div class="relative h-48 overflow-hidden">
                            <?php if ($course['thumbnail_url']): ?>
                                <img src="<?= htmlspecialchars($course['thumbnail_url']) ?>" 
                                     alt="<?= htmlspecialchars($course['title']) ?>" 
                                     class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                            <?php else: ?>
                                <!-- Fallback patterned background using category color -->
                                <div class="w-full h-full flex items-center justify-center relative" 
                                     style="background-color: <?= htmlspecialchars($bg_color) ?>20;"> <!-- 20% opacity -->
                                    <i class="fas fa-graduation-cap text-5xl opacity-30" style="color: <?= htmlspecialchars($bg_color) ?>"></i>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
                                </div>
                            <?php endif; ?>

                            <!-- Badges -->
                            <div class="absolute top-3 left-3 flex flex-col gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-white/90 backdrop-blur-sm shadow-sm" style="color: <?= htmlspecialchars($bg_color) ?>;">
                                    <?= htmlspecialchars($course['category_name']) ?>
                                </span>
                            </div>

                            <?php if ($has_discount): ?>
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-500 text-white shadow-sm animate-pulse">
                                        SALE
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 flex-1 flex flex-col">
                            <!-- Rating & Enrollment -->
                            <div class="flex items-center justify-between mb-3 text-xs text-gray-500">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-gray-900"><?= number_format($course['rating'], 1) ?></span>
                                    <?= renderStars($course['rating']) ?>
                                    <span class="text-gray-400">(<?= $course['total_reviews'] ?>)</span>
                                </div>
                                <div class="flex items-center" title="Enrolled Students">
                                    <i class="fas fa-user-friends mr-1"></i>
                                    <?= number_format($course['enrollment_count']) ?>
                                </div>
                            </div>

                            <!-- Title -->
                            <h3 class="text-lg font-bold text-gray-900 mb-2 leading-tight line-clamp-2 group-hover:text-primary-600 transition-colors">
                                <a href="course_details.php?slug=<?= htmlspecialchars($course['slug']) ?>">
                                    <?= htmlspecialchars($course['title']) ?>
                                </a>
                            </h3>

                            <!-- Instructor -->
                            <div class="flex items-center mb-4">
                                <div class="text-sm text-gray-600 flex items-center">
                                    By <?= htmlspecialchars($course['instructor_name']) ?>
                                    <?php if($course['is_verified']): ?>
                                        <i class="fas fa-check-circle text-blue-500 ml-1 text-xs" title="Verified Instructor"></i>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Divider to push footer down -->
                            <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex flex-col">
                                    <?php if ($current_price == 0): ?>
                                        <span class="text-xl font-bold text-green-600">Free</span>
                                    <?php else: ?>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl font-bold text-gray-900">ZMW <?= number_format($current_price, 2) ?></span>
                                            <?php if ($has_discount): ?>
                                                <span class="text-sm text-gray-400 line-through">ZMW <?= number_format($course['price'], 2) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <a href="course_details.php?slug=<?= htmlspecialchars($course['slug']) ?>" 
                                   class="p-2 rounded-full text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition-colors" title="View Details">
                                    <i class="fas fa-arrow-right text-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="flex justify-center mt-12">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <!-- Previous -->
                        <a href="?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&sort=<?= $sort_filter ?>" 
                           class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
                            <span class="sr-only">Previous</span>
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        
                        <!-- Page Numbers -->
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&sort=<?= $sort_filter ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50 <?= $page == $i ? 'z-10 bg-primary-50 border-primary-500 text-primary-600' : 'text-gray-500' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <!-- Next -->
                        <a href="?page=<?= min($total_pages, $page + 1) ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&sort=<?= $sort_filter ?>" 
                           class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?= $page >= $total_pages ? 'pointer-events-none opacity-50' : '' ?>">
                            <span class="sr-only">Next</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </nav>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-300">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                    <i class="fas fa-search text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No courses found</h3>
                <p class="mt-1 text-gray-500 max-w-sm mx-auto">
                    We couldn't find any courses matching your filters. Try removing some filters or search for something else.
                </p>
                <div class="mt-6">
                    <a href="courses.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        View All Courses
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../src/templates/footer.php'; ?>