<?php
/**
 * Search Page
 * Search for courses with advanced filtering
 */

require_once '../src/bootstrap.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Category.php';

// Get search parameters
$query = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? null;
$level = $_GET['level'] ?? null;
$priceMin = isset($_GET['price_min']) ? floatval($_GET['price_min']) : null;
$priceMax = isset($_GET['price_max']) ? floatval($_GET['price_max']) : null;
$rating = isset($_GET['rating']) ? floatval($_GET['rating']) : null;
$sortBy = $_GET['sort'] ?? 'relevance';

// Build search query
$db = Database::getInstance();

$sql = "SELECT c.*,
               cc.name as category_name,
               cc.color as category_color,
               CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
               COUNT(DISTINCT e.id) as enrolled_students,
               COALESCE(AVG(cr.rating), 0) as avg_rating,
               COUNT(DISTINCT cr.id) as total_reviews
        FROM courses c
        JOIN course_categories cc ON c.category_id = cc.id
        LEFT JOIN instructors i ON c.instructor_id = i.id
        LEFT JOIN users u ON i.user_id = u.id
        LEFT JOIN enrollments e ON c.id = e.course_id
        LEFT JOIN course_reviews cr ON c.id = cr.course_id
        WHERE c.status = 'published'";

$params = [];

// Text search
if (!empty($query)) {
    $sql .= " AND (c.title LIKE ? OR c.description LIKE ? OR c.short_description LIKE ?)";
    $searchTerm = '%' . $query . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Category filter
if ($category) {
    $sql .= " AND c.category_id = ?";
    $params[] = $category;
}

// Level filter
if ($level) {
    $sql .= " AND c.level = ?";
    $params[] = $level;
}

// Price range filter
if ($priceMin !== null) {
    $sql .= " AND c.price >= ?";
    $params[] = $priceMin;
}
if ($priceMax !== null) {
    $sql .= " AND c.price <= ?";
    $params[] = $priceMax;
}

// Rating filter (applied after GROUP BY via HAVING)
$ratingFilter = null;
if ($rating) {
    $ratingFilter = $rating;
}

$sql .= " GROUP BY c.id";

// Rating filter (using HAVING since it's an aggregate)
if ($ratingFilter) {
    $sql .= " HAVING avg_rating >= ?";
    $params[] = $ratingFilter;
}

// Sorting
switch ($sortBy) {
    case 'price_low':
        $sql .= " ORDER BY c.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY c.price DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY avg_rating DESC, total_reviews DESC";
        break;
    case 'popular':
        $sql .= " ORDER BY enrolled_students DESC, avg_rating DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY c.created_at DESC";
        break;
    case 'relevance':
    default:
        if (!empty($query)) {
            // Order by title match first, then rating
            $sql .= " ORDER BY (c.title LIKE ?) DESC, avg_rating DESC";
            $params[] = '%' . $query . '%';
        } else {
            $sql .= " ORDER BY avg_rating DESC, total_reviews DESC";
        }
        break;
}

$courses = $db->fetchAll($sql, $params);

// Get all categories for filter
$categories = Category::all(['active_only' => true]);

$page_title = !empty($query) ? 'Search Results for "' . sanitize($query) . '"' : 'Search Courses';
require_once '../src/templates/header.php';
?>

<div class="min-h-screen bg-gray-50">

    <!-- Search Header -->
    <div class="bg-primary-600 text-white py-12">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold mb-6">
                <?= !empty($query) ? 'Search Results' : 'Find Your Perfect Course' ?>
            </h1>

            <!-- Search Form -->
            <form method="GET" class="max-w-3xl">
                <div class="flex">
                    <input type="text" name="q" value="<?= sanitize($query) ?>"
                           placeholder="Search for courses..."
                           class="flex-1 px-6 py-4 rounded-l-lg text-gray-900 focus:outline-none">
                    <button type="submit" class="px-8 py-4 bg-primary-700 hover:bg-primary-800 rounded-r-lg font-semibold">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </div>
            </form>

            <?php if (!empty($query)): ?>
            <p class="mt-4 text-primary-100">
                Found <?= count($courses) ?> course<?= count($courses) != 1 ? 's' : '' ?> matching "<?= sanitize($query) ?>"
            </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">

            <!-- Filters Sidebar -->
            <aside class="lg:w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Filters</h2>

                    <form method="GET" id="filterForm">
                        <input type="hidden" name="q" value="<?= sanitize($query) ?>">

                        <!-- Category Filter -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Category</h3>
                            <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    onchange="document.getElementById('filterForm').submit()">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat->getId() ?>" <?= $category == $cat->getId() ? 'selected' : '' ?>>
                                        <?= sanitize($cat->getName()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Level Filter -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Level</h3>
                            <select name="level" class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    onchange="document.getElementById('filterForm').submit()">
                                <option value="">All Levels</option>
                                <option value="beginner" <?= $level == 'beginner' ? 'selected' : '' ?>>Beginner</option>
                                <option value="intermediate" <?= $level == 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                <option value="advanced" <?= $level == 'advanced' ? 'selected' : '' ?>>Advanced</option>
                                <option value="all levels" <?= $level == 'all levels' ? 'selected' : '' ?>>All Levels</option>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Price Range (ZMW)</h3>
                            <div class="flex items-center space-x-2">
                                <input type="number" name="price_min" value="<?= $priceMin ?>"
                                       placeholder="Min" min="0" step="100"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <span class="text-gray-500">-</span>
                                <input type="number" name="price_max" value="<?= $priceMax ?>"
                                       placeholder="Max" min="0" step="100"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <button type="submit" class="mt-2 w-full px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm">
                                Apply
                            </button>
                        </div>

                        <!-- Rating Filter -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Minimum Rating</h3>
                            <select name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    onchange="document.getElementById('filterForm').submit()">
                                <option value="">Any Rating</option>
                                <option value="4.5" <?= $rating == 4.5 ? 'selected' : '' ?>>4.5+ ⭐</option>
                                <option value="4.0" <?= $rating == 4.0 ? 'selected' : '' ?>>4.0+ ⭐</option>
                                <option value="3.5" <?= $rating == 3.5 ? 'selected' : '' ?>>3.5+ ⭐</option>
                                <option value="3.0" <?= $rating == 3.0 ? 'selected' : '' ?>>3.0+ ⭐</option>
                            </select>
                        </div>

                        <!-- Clear Filters -->
                        <a href="<?= url('search.php?q=' . urlencode($query)) ?>"
                           class="block w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-center text-sm">
                            Clear All Filters
                        </a>
                    </form>
                </div>
            </aside>

            <!-- Results -->
            <main class="flex-1">

                <!-- Sort Options -->
                <div class="flex items-center justify-between mb-6">
                    <p class="text-gray-600">
                        Showing <?= count($courses) ?> result<?= count($courses) != 1 ? 's' : '' ?>
                    </p>
                    <form method="GET" class="flex items-center space-x-2">
                        <input type="hidden" name="q" value="<?= sanitize($query) ?>">
                        <?php if ($category): ?><input type="hidden" name="category" value="<?= $category ?>"><?php endif; ?>
                        <?php if ($level): ?><input type="hidden" name="level" value="<?= $level ?>"><?php endif; ?>
                        <?php if ($priceMin): ?><input type="hidden" name="price_min" value="<?= $priceMin ?>"><?php endif; ?>
                        <?php if ($priceMax): ?><input type="hidden" name="price_max" value="<?= $priceMax ?>"><?php endif; ?>
                        <?php if ($rating): ?><input type="hidden" name="rating" value="<?= $rating ?>"><?php endif; ?>

                        <label class="text-sm text-gray-600">Sort by:</label>
                        <select name="sort" class="px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                onchange="this.form.submit()">
                            <option value="relevance" <?= $sortBy == 'relevance' ? 'selected' : '' ?>>Relevance</option>
                            <option value="popular" <?= $sortBy == 'popular' ? 'selected' : '' ?>>Most Popular</option>
                            <option value="rating" <?= $sortBy == 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                            <option value="newest" <?= $sortBy == 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="price_low" <?= $sortBy == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sortBy == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        </select>
                    </form>
                </div>

                <!-- Course Results -->
                <?php if (count($courses) > 0): ?>
                    <div class="space-y-6">
                        <?php foreach ($courses as $course): ?>
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                                <div class="md:flex">
                                    <!-- Course Thumbnail -->
                                    <div class="md:w-64 md:flex-shrink-0">
                                        <img src="<?= courseThumbnail($course['thumbnail']) ?>"
                                             alt="<?= sanitize($course['title']) ?>"
                                             class="h-48 w-full object-cover md:h-full">
                                    </div>

                                    <!-- Course Info -->
                                    <div class="p-6 flex-1">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1">
                                                <a href="<?= url('course.php?id=' . $course['id']) ?>"
                                                   class="text-xl font-bold text-gray-900 hover:text-primary-600">
                                                    <?= sanitize($course['title']) ?>
                                                </a>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    by <?= sanitize($course['instructor_name']) ?>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-bold text-primary-600">
                                                    K<?= number_format($course['price'], 2) ?>
                                                </p>
                                            </div>
                                        </div>

                                        <p class="text-gray-700 mb-4 line-clamp-2">
                                            <?= sanitize($course['short_description']) ?>
                                        </p>

                                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                                  style="background-color: <?= $course['category_color'] ?>10; color: <?= $course['category_color'] ?>">
                                                <?= sanitize($course['category_name']) ?>
                                            </span>
                                            <span><i class="fas fa-signal mr-1"></i><?= ucfirst($course['level']) ?></span>
                                            <?php if ($course['avg_rating'] > 0): ?>
                                                <span class="flex items-center">
                                                    <i class="fas fa-star text-yellow-400 mr-1"></i>
                                                    <?= number_format($course['avg_rating'], 1) ?>
                                                    <span class="text-gray-500 ml-1">(<?= $course['total_reviews'] ?>)</span>
                                                </span>
                                            <?php endif; ?>
                                            <span><i class="fas fa-users mr-1"></i><?= number_format($course['enrolled_students']) ?> students</span>
                                        </div>

                                        <div class="flex items-center space-x-3">
                                            <a href="<?= url('course.php?id=' . $course['id']) ?>"
                                               class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                                View Course
                                            </a>
                                            <a href="<?= url('enroll.php?course_id=' . $course['id']) ?>"
                                               class="px-4 py-2 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50">
                                                Enroll Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- No Results -->
                    <div class="bg-white rounded-lg shadow-md p-12 text-center">
                        <i class="fas fa-search text-gray-400 text-6xl mb-4"></i>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">No Courses Found</h2>
                        <p class="text-gray-600 mb-6">
                            We couldn't find any courses matching your search criteria.
                        </p>
                        <div class="space-y-2 text-sm text-gray-600">
                            <p>Try:</p>
                            <ul class="list-disc list-inside">
                                <li>Using different keywords</li>
                                <li>Removing some filters</li>
                                <li>Checking your spelling</li>
                            </ul>
                        </div>
                        <a href="<?= url('courses.php') ?>" class="inline-block mt-6 px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            Browse All Courses
                        </a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>
