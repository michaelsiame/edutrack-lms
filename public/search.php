<?php
/**
 * Search Page
 * Search for courses
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/functions.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Category.php';

// Get search query
$query = $_GET['q'] ?? '';
$query = trim($query);

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$courses = [];
$total_courses = 0;

if ($query) {
    // Search courses
    $filters = [
        'search' => $query,
        'limit' => $per_page,
        'offset' => $offset,
        'order_by' => 'created_at',
        'order_dir' => 'DESC'
    ];
    
    $courses = Course::all($filters);
    $total_courses = Course::count(['search' => $query]);
}

$total_pages = $total_courses > 0 ? ceil($total_courses / $per_page) : 0;

$page_title = $query ? 'Search: ' . htmlspecialchars($query) . ' - Edutrack' : 'Search Courses - Edutrack';
require_once '../src/templates/header.php';
?>

<div class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-6">Search Courses</h1>
        
        <!-- Search Form -->
        <form method="GET" action="<?= url('search.php') ?>" class="max-w-3xl">
            <div class="relative">
                <input type="text" 
                       name="q" 
                       value="<?= htmlspecialchars($query) ?>"
                       placeholder="Search for courses..." 
                       class="w-full pl-12 pr-4 py-4 rounded-lg text-gray-900 text-lg focus:outline-none focus:ring-2 focus:ring-secondary-500"
                       autofocus>
                <i class="fas fa-search absolute left-4 top-5 text-gray-400 text-xl"></i>
                <button type="submit" 
                        class="absolute right-2 top-2 bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition">
                    Search
                </button>
            </div>
        </form>
        
        <?php if ($query): ?>
        <p class="mt-4 text-primary-100 text-lg">
            <?= $total_courses ?> result<?= $total_courses != 1 ? 's' : '' ?> found for "<?= htmlspecialchars($query) ?>"
        </p>
        <?php endif; ?>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <?php if (!$query): ?>
        
        <!-- Popular Searches -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Popular Searches</h2>
            <div class="flex flex-wrap gap-3">
                <?php
                $popular = ['Microsoft Office', 'Graphic Design', 'Web Development', 'Accounting', 'Business Management', 'Digital Marketing'];
                foreach ($popular as $term):
                ?>
                <a href="<?= url('search.php?q=' . urlencode($term)) ?>" 
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-primary-100 hover:text-primary-700 transition">
                    <?= htmlspecialchars($term) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Browse by Category -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Browse by Category</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $categories = Category::active();
                foreach ($categories as $category):
                ?>
                <a href="<?= url('courses.php?category=' . $category['slug']) ?>" 
                   class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg hover:border-primary-500 transition group">
                    <div class="flex items-center justify-between mb-3">
                        <i class="fas <?= htmlspecialchars($category['icon']) ?> text-3xl" 
                           style="color: <?= htmlspecialchars($category['color']) ?>"></i>
                        <span class="text-sm text-gray-500"><?= $category['course_count'] ?> courses</span>
                    </div>
                    <h3 class="font-bold text-gray-900 group-hover:text-primary-600">
                        <?= htmlspecialchars($category['name']) ?>
                    </h3>
                    <?php if ($category['description']): ?>
                    <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                        <?= htmlspecialchars($category['description']) ?>
                    </p>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
    <?php elseif (empty($courses)): ?>
        
        <!-- No Results -->
        <div class="text-center py-12">
            <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">No courses found</h2>
            <p class="text-gray-600 mb-6">Try adjusting your search or browse our categories</p>
            <a href="<?= url('courses.php') ?>" class="btn-primary inline-block">
                Browse All Courses
            </a>
        </div>
        
    <?php else: ?>
        
        <!-- Search Results -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($courses as $courseData): 
                $course = new Course($courseData['id']);
            ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group">
                
                <!-- Thumbnail -->
                <a href="<?= $course->getUrl() ?>" class="block relative">
                    <img src="<?= $course->getThumbnailUrl() ?>" 
                         alt="<?= htmlspecialchars($course->getTitle()) ?>"
                         class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                    
                    <!-- Price Badge -->
                    <div class="absolute top-3 right-3">
                        <?php if ($course->isFree()): ?>
                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            Free
                        </span>
                        <?php else: ?>
                        <span class="bg-primary-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            <?= $course->getFormattedPrice() ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- TEVETA Badge -->
                    <?php if ($course->isTeveta()): ?>
                    <div class="absolute top-3 left-3">
                        <span class="bg-secondary-500 text-gray-900 px-2 py-1 rounded text-xs font-bold">
                            TEVETA
                        </span>
                    </div>
                    <?php endif; ?>
                </a>
                
                <!-- Content -->
                <div class="p-5">
                    
                    <!-- Category -->
                    <a href="<?= url('courses.php?category=' . $course->getCategorySlug()) ?>" 
                       class="text-primary-600 text-sm font-semibold hover:text-primary-700">
                        <?= htmlspecialchars($course->getCategoryName()) ?>
                    </a>
                    
                    <!-- Title -->
                    <h3 class="text-lg font-bold text-gray-900 mt-2 mb-2 line-clamp-2">
                        <a href="<?= $course->getUrl() ?>" class="hover:text-primary-600">
                            <?= htmlspecialchars($course->getTitle()) ?>
                        </a>
                    </h3>
                    
                    <!-- Description -->
                    <p class="text-gray-600 text-sm line-clamp-2 mb-4">
                        <?= htmlspecialchars($course->getShortDescription()) ?>
                    </p>
                    
                    <!-- Meta Info -->
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-user mr-1"></i>
                            <?= number_format($course->getTotalStudents()) ?>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-1"></i>
                            <?= $course->getDuration() ?> hours
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-signal mr-1"></i>
                            <?= ucfirst($course->getLevel()) ?>
                        </div>
                    </div>
                    
                    <!-- Rating -->
                    <?php if ($course->getTotalReviews() > 0): ?>
                    <div class="flex items-center mb-4">
                        <div class="flex items-center text-secondary-500">
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
                        <span class="ml-2 text-gray-600 text-sm">
                            <?= number_format($rating, 1) ?> (<?= number_format($course->getTotalReviews()) ?>)
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Instructor -->
                    <div class="flex items-center text-sm text-gray-700 border-t pt-4">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>
                        <?= htmlspecialchars($course->getInstructorName()) ?>
                    </div>
                    
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-8 flex justify-center">
            <nav class="flex items-center space-x-2">
                
                <?php if ($page > 1): ?>
                <a href="<?= url('search.php?q=' . urlencode($query) . '&page=' . ($page - 1)) ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): 
                    if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2):
                ?>
                <a href="<?= url('search.php?q=' . urlencode($query) . '&page=' . $i) ?>" 
                   class="px-4 py-2 <?= $i == $page ? 'bg-primary-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50' ?> rounded-lg">
                    <?= $i ?>
                </a>
                <?php 
                    elseif (abs($i - $page) == 3):
                        echo '<span class="px-2">...</span>';
                    endif;
                endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                <a href="<?= url('search.php?q=' . urlencode($query) . '&page=' . ($page + 1)) ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
                
            </nav>
        </div>
        <?php endif; ?>
        
    <?php endif; ?>
    
</div>

<?php require_once '../src/templates/footer.php'; ?>