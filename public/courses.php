<?php
/**
 * All Courses Page
 * Browse and filter courses
 */

require_once '../src/bootstrap.php';

$page_title = "Browse Courses - Edutrack computer training college";

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$level_filter = $_GET['level'] ?? '';

// Get all categories for filter dropdown
$db = Database::getInstance();
$categories = $db->fetchAll("SELECT * FROM course_categories WHERE is_active = 1 ORDER BY name");

// Build course query with filters
$query = "SELECT c.*, cc.name as category_name, 'blue' as category_color,
          CONCAT(u.first_name, ' ', u.last_name) as instructor_name
          FROM courses c
          LEFT JOIN course_categories cc ON c.category_id = cc.id
          LEFT JOIN instructors i ON c.instructor_id = i.id
          LEFT JOIN users u ON i.user_id = u.id
          WHERE c.status = 'published'";

$params = [];

if (!empty($search)) {
    $query .= " AND (c.title LIKE ? OR c.description LIKE ? OR c.short_description LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($category_filter)) {
    $query .= " AND c.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($level_filter)) {
    $query .= " AND c.level = ?";
    $params[] = $level_filter;
}

$query .= " ORDER BY c.created_at DESC";

$courses = $db->fetchAll($query, $params);

require_once '../src/templates/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-br from-primary-600 via-blue-700 to-purple-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-graduation-cap mr-3"></i>
                Browse Our Courses
            </h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Discover TEVETA-certified training programs designed to elevate your skills and career
            </p>
        </div>
    </div>
</section>

<!-- Filters Section -->
<section class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <form method="GET" action="" class="flex flex-col md:flex-row gap-4">
            <!-- Search Input -->
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text"
                           name="search"
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Search courses..."
                           class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="w-full md:w-48">
                <select name="category"
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Level Filter -->
            <div class="w-full md:w-48">
                <select name="level"
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Levels</option>
                    <option value="beginner" <?= $level_filter === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                    <option value="intermediate" <?= $level_filter === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="advanced" <?= $level_filter === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                    <option value="all levels" <?= $level_filter === 'all levels' ? 'selected' : '' ?>>All Levels</option>
                </select>
            </div>

            <!-- Filter Button -->
            <div class="flex gap-2">
                <button type="submit"
                        class="px-6 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition duration-200">
                    <i class="fas fa-filter mr-2"></i>
                    Filter
                </button>
                <?php if ($search || $category_filter || $level_filter): ?>
                    <a href="courses.php"
                       class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>

<!-- Courses Grid -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Results Count -->
        <div class="mb-8 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">
                <?= count($courses) ?> <?= count($courses) === 1 ? 'Course' : 'Courses' ?> Found
            </h2>
            <?php if ($search): ?>
                <p class="text-gray-600">
                    Showing results for "<span class="font-semibold"><?= htmlspecialchars($search) ?></span>"
                </p>
            <?php endif; ?>
        </div>

        <?php if (!empty($courses)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($courses as $index => $course): ?>
                    <div class="group course-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                        <!-- Course Thumbnail -->
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

                            <!-- Course Status Badge -->
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 shadow-sm">
                                    <i class="fas fa-check mr-1"></i>
                                    TEVETA REGISTERED
                                </span>
                            </div>

                            <!-- Category Badge -->
                            <div class="absolute top-3 left-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    bg-<?= $course['category_color'] ?>-100 text-<?= $course['category_color'] ?>-800">
                                    <i class="fas fa-tag mr-1"></i>
                                    <?= htmlspecialchars($course['level']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Course Content -->
                        <div class="p-6">
                            <div class="mb-3">
                                <span class="text-xs font-medium text-<?= $course['category_color'] ?>-600 uppercase tracking-wide">
                                    <?= htmlspecialchars($course['category_name']) ?>
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors duration-200 min-h-[56px]">
                                <?= htmlspecialchars($course['title']) ?>
                            </h3>

                            <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed min-h-[60px]">
                                <?= htmlspecialchars(substr($course['short_description'] ?? $course['description'], 0, 120)) ?>...
                            </p>

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4 border-b border-gray-100">
                                <span class="flex items-center">
                                    <i class="fas fa-chalkboard-teacher mr-1.5 text-primary-500"></i>
                                    <?= htmlspecialchars($course['instructor_name']) ?>
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-clock mr-1.5 text-primary-500"></i>
                                    <?= $course['duration_weeks'] ? $course['duration_weeks'] . ' weeks' : ($course['total_hours'] ? $course['total_hours'] . ' hours' : 'Varies') ?>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="text-2xl font-bold text-primary-600">
                                    <?= $course['price'] == 0 ? 'Free' : 'ZMW ' . number_format($course['price'], 2) ?>
                                </div>
                                <a href="course.php?id=<?= $course['id'] ?>"
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 transition duration-200">
                                    View Course
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-12">
                <?php
                emptyState(
                    'fa-search',
                    'No Courses Found',
                    'We couldn\'t find any courses matching your criteria. Try adjusting your filters or search terms.',
                    'courses.php',
                    'View All Courses'
                );
                ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="bg-primary-600 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">
            Can't Find What You're Looking For?
        </h2>
        <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
            Contact us to learn more about our training programs or suggest a course
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="contact.php"
               class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-primary-600 bg-white hover:bg-gray-50 transition duration-200">
                <i class="fas fa-envelope mr-2"></i>
                Contact Us
            </a>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php"
                   class="inline-flex items-center justify-center px-8 py-3 border border-white text-base font-medium rounded-md text-white hover:bg-primary-700 transition duration-200">
                    <i class="fas fa-user-plus mr-2"></i>
                    Register Now
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once '../src/templates/footer.php'; ?>
