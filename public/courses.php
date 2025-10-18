<?php
/**
 * All Courses Page
 * Browse and filter courses
 */

require_once '../src/includes/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/functions.php';
require_once '../src/classes/Course.php';
require_once '../src/classes/Category.php';

// Get filters from URL
$category_slug = $_GET['category'] ?? null;
$level = $_GET['level'] ?? null;
$price_type = $_GET['price'] ?? null;
$search = $_GET['search'] ?? null;
$sort = $_GET['sort'] ?? 'recent';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build filters array
$filters = [
    'limit' => $per_page,
    'offset' => $offset
];

// Category filter
if ($category_slug) {
    $category = Category::findBySlug($category_slug);
    if ($category) {
        $filters['category_id'] = $category->getId();
    }
}

// Level filter
if ($level && in_array($level, ['beginner', 'intermediate', 'advanced'])) {
    $filters['level'] = $level;
}

// Price filter
if ($price_type == 'free') {
    $filters['is_free'] = true;
} elseif ($price_type == 'paid') {
    $filters['is_free'] = false;
}

// Search filter
if ($search) {
    $filters['search'] = $search;
}

// Sorting
switch ($sort) {
    case 'popular':
        $filters['order_by'] = 'total_students';
        $filters['order_dir'] = 'DESC';
        break;
    case 'rating':
        $filters['order_by'] = 'avg_rating';
        $filters['order_dir'] = 'DESC';
        break;
    case 'price_low':
        $filters['order_by'] = 'price';
        $filters['order_dir'] = 'ASC';
        break;
    case 'price_high':
        $filters['order_by'] = 'price';
        $filters['order_dir'] = 'DESC';
        break;
    default: // recent
        $filters['order_by'] = 'created_at';
        $filters['order_dir'] = 'DESC';
}

// Get courses and total count
$courses = Course::all($filters);
$total_courses = Course::count(array_intersect_key($filters, array_flip(['category_id', 'level', 'is_free', 'search'])));
$total_pages = ceil($total_courses / $per_page);

// Get all categories
$categories = Category::active();

$page_title = 'Browse Courses - Edutrack';
if ($category) {
    $page_title = $category->getName() . ' Courses - Edutrack';
}

require_once '../src/templates/header.php';
?>

<div class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-4">
            <?php if ($category): ?>
                <?= htmlspecialchars($category->getName()) ?> Courses
            <?php elseif ($search): ?>
                Search Results for "<?= htmlspecialchars($search) ?>"
            <?php else: ?>
                Browse All Courses
            <?php endif; ?>
        </h1>
        <p class="text-xl text-primary-100">
            <?= $total_courses ?> course<?= $total_courses != 1 ? 's' : '' ?> available
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Filters -->
        <aside class="lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                
                <!-- Categories -->
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Categories</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="<?= url('courses.php') ?>" 
                               class="flex items-center justify-between text-gray-700 hover:text-primary-600 <?= !$category ? 'text-primary-600 font-semibold' : '' ?>">
                                <span>All Courses</span>
                                <span class="text-sm text-gray-500"><?= Course::count() ?></span>
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="<?= url('courses.php?category=' . $cat['slug']) ?>" 
                               class="flex items-center justify-between text-gray-700 hover:text-primary-600 <?= $category && $category->getId() == $cat['id'] ? 'text-primary-600 font-semibold' : '' ?>">
                                <span>
                                    <i class="fas <?= htmlspecialchars($cat['icon']) ?> mr-2"></i>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </span>
                                <span class="text-sm text-gray-500"><?= $cat['course_count'] ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Level Filter -->
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Level</h3>
                    <div class="space-y-2">
                        <?php 
                        $levels = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];
                        foreach ($levels as $key => $label):
                            $url = url('courses.php');
                            $params = $_GET;
                            if ($level == $key) {
                                unset($params['level']);
                            } else {
                                $params['level'] = $key;
                            }
                            if (!empty($params)) {
                                $url .= '?' . http_build_query($params);
                            }
                        ?>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                   <?= $level == $key ? 'checked' : '' ?>
                                   onchange="window.location.href='<?= $url ?>'">
                            <span class="ml-2 text-gray-700"><?= $label ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Price Filter -->
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Price</h3>
                    <div class="space-y-2">
                        <?php 
                        $prices = ['free' => 'Free', 'paid' => 'Paid'];
                        foreach ($prices as $key => $label):
                            $url = url('courses.php');
                            $params = $_GET;
                            if ($price_type == $key) {
                                unset($params['price']);
                            } else {
                                $params['price'] = $key;
                            }
                            if (!empty($params)) {
                                $url .= '?' . http_build_query($params);
                            }
                        ?>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                   <?= $price_type == $key ? 'checked' : '' ?>
                                   onchange="window.location.href='<?= $url ?>'">
                            <span class="ml-2 text-gray-700"><?= $label ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- TEVETA Certified -->
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Certification</h3>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-primary-600">
                        <span class="ml-2 text-gray-700">TEVETA Certified</span>
                    </label>
                </div>
                
                <!-- Clear Filters -->
                <?php if ($category || $level || $price_type || $search): ?>
                <div class="pt-4 border-t">
                    <a href="<?= url('courses.php') ?>" 
                       class="block w-full text-center py-2 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Clear All Filters
                    </a>
                </div>
                <?php endif; ?>
                
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1">
            
            <!-- Search and Sort Bar -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    
                    <!-- Search -->
                    <form method="GET" action="<?= url('courses.php') ?>" class="flex-1">
                        <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category->getSlug()) ?>">
                        <?php endif; ?>
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="<?= htmlspecialchars($search ?? '') ?>"
                                   placeholder="Search courses..." 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </form>
                    
                    <!-- Sort -->
                    <div class="sm:w-48">
                        <select onchange="window.location.href=this.value" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <?php
                            $sort_options = [
                                'recent' => 'Most Recent',
                                'popular' => 'Most Popular',
                                'rating' => 'Highest Rated',
                                'price_low' => 'Price: Low to High',
                                'price_high' => 'Price: High to Low'
                            ];
                            $current_params = $_GET;
                            foreach ($sort_options as $key => $label):
                                $current_params['sort'] = $key;
                                $url = url('courses.php?' . http_build_query($current_params));
                            ?>
                            <option value="<?= $url ?>" <?= $sort == $key ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                </div>
            </div>
            
            <!-- Courses Grid -->
            <?php if (empty($courses)): ?>
                <?php emptyState('fa-book', 'No Courses Found', 'Try adjusting your filters or search terms', url('courses.php'), 'View All Courses'); ?>
            <?php else: ?>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($courses as $course): 
                    $courseObj = new Course($course['id']);
                ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group">
                    
                    <!-- Thumbnail -->
                    <a href="<?= $courseObj->getUrl() ?>" class="block relative">
                        <img src="<?= $courseObj->getThumbnailUrl() ?>" 
                             alt="<?= htmlspecialchars($courseObj->getTitle()) ?>"
                             class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                        
                        <!-- Price Badge -->
                        <div class="absolute top-3 right-3">
                            <?php if ($courseObj->isFree()): ?>
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                Free
                            </span>
                            <?php else: ?>
                            <span class="bg-primary-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                <?= $courseObj->getFormattedPrice() ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- TEVETA Badge -->
                        <?php if ($courseObj->isTeveta()): ?>
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
                        <a href="<?= url('courses.php?category=' . $courseObj->getCategorySlug()) ?>" 
                           class="text-primary-600 text-sm font-semibold hover:text-primary-700">
                            <?= htmlspecialchars($courseObj->getCategoryName()) ?>
                        </a>
                        
                        <!-- Title -->
                        <h3 class="text-lg font-bold text-gray-900 mt-2 mb-2 line-clamp-2">
                            <a href="<?= $courseObj->getUrl() ?>" class="hover:text-primary-600">
                                <?= htmlspecialchars($courseObj->getTitle()) ?>
                            </a>
                        </h3>
                        
                        <!-- Description -->
                        <p class="text-gray-600 text-sm line-clamp-2 mb-4">
                            <?= htmlspecialchars($courseObj->getShortDescription()) ?>
                        </p>
                        
                        <!-- Meta Info -->
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-user mr-1"></i>
                                <?= number_format($courseObj->getTotalStudents()) ?>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock mr-1"></i>
                                <?= $courseObj->getDuration() ?> hours
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-signal mr-1"></i>
                                <?= ucfirst($courseObj->getLevel()) ?>
                            </div>
                        </div>
                        
                        <!-- Rating -->
                        <?php if ($courseObj->getTotalReviews() > 0): ?>
                        <div class="flex items-center mb-4">
                            <div class="flex items-center text-secondary-500">
                                <?php
                                $rating = $courseObj->getAvgRating();
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
                                <?= number_format($rating, 1) ?> (<?= number_format($courseObj->getTotalReviews()) ?>)
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Instructor -->
                        <div class="flex items-center text-sm text-gray-700 border-t pt-4">
                            <i class="fas fa-chalkboard-teacher mr-2"></i>
                            <?= htmlspecialchars($courseObj->getInstructorName()) ?>
                        </div>
                        
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center">
                <nav class="flex items-center space-x-2">
                    
                    <?php if ($page > 1): 
                        $prev_params = $_GET;
                        $prev_params['page'] = $page - 1;
                    ?>
                    <a href="<?= url('courses.php?' . http_build_query($prev_params)) ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): 
                        if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2):
                            $page_params = $_GET;
                            $page_params['page'] = $i;
                    ?>
                    <a href="<?= url('courses.php?' . http_build_query($page_params)) ?>" 
                       class="px-4 py-2 <?= $i == $page ? 'bg-primary-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50' ?> rounded-lg">
                        <?= $i ?>
                    </a>
                    <?php 
                        elseif (abs($i - $page) == 3):
                            echo '<span class="px-2">...</span>';
                        endif;
                    endfor; ?>
                    
                    <?php if ($page < $total_pages): 
                        $next_params = $_GET;
                        $next_params['page'] = $page + 1;
                    ?>
                    <a href="<?= url('courses.php?' . http_build_query($next_params)) ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                    
                </nav>
            </div>
            <?php endif; ?>
            
            <?php endif; ?>
            
        </main>
    </div>
</div>

<?php require_once '../src/templates/footer.php'; ?>