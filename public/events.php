<?php
/**
 * Events/News Page
 * Display recent events with photos and stories
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/classes/Event.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = $_GET['search'] ?? '';

$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}

// Get paginated events
$result = Event::getPaginated($page, 9, $filters);
$events = $result['events'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];
$totalEvents = $result['total'];

// Get featured events for hero section
$featuredEvents = Event::getFeatured(2);

$page_title = "Recent Events & News - Edutrack computer training college";

require_once __DIR__ . '/../src/templates/header.php';
?>

<!-- Page Header -->
<section class="bg-primary-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-calendar-alt mr-3"></i>Recent Events & News
            </h1>
            <p class="text-xl text-primary-100 max-w-3xl mx-auto">
                Stay updated with the latest happenings at Edutrack. From graduation ceremonies 
                to workshops, corporate partnerships, and student achievements.
            </p>
        </div>
    </div>
</section>

<!-- Featured Events Section -->
<?php if (!empty($featuredEvents)): ?>
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 flex items-center">
            <i class="fas fa-star text-yellow-500 mr-2"></i> Featured Events
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php foreach ($featuredEvents as $eventData): 
                $event = new Event($eventData['id']);
            ?>
            <div class="group bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="relative h-64 overflow-hidden">
                    <?php if ($event->getCoverImageUrl()): ?>
                    <img src="<?= $event->getCoverImageUrl() ?>" 
                         alt="<?= htmlspecialchars($event->get('title')) ?>"
                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                    <div class="w-full h-full bg-primary-500 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-6xl text-white opacity-50"></i>
                    </div>
                    <?php endif; ?>
                    <div class="absolute top-4 left-4">
                        <span class="bg-yellow-500 text-gray-900 px-3 py-1 rounded-full text-sm font-semibold">
                            <i class="fas fa-star mr-1"></i> Featured
                        </span>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-black/70 p-6">
                        <div class="text-white">
                            <?php if ($event->get('event_date')): ?>
                            <span class="text-sm font-medium text-yellow-400">
                                <i class="fas fa-calendar mr-1"></i> <?= $event->getFormattedDate() ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($event->get('location')): ?>
                            <span class="text-sm font-medium text-gray-300 ml-4">
                                <i class="fas fa-map-marker-alt mr-1"></i> <?= htmlspecialchars($event->get('location')) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-primary-600 transition-colors">
                        <?= htmlspecialchars($event->get('title')) ?>
                    </h3>
                    <p class="text-gray-600 line-clamp-3 mb-4">
                        <?= htmlspecialchars($event->get('summary') ?: substr($event->get('story'), 0, 150) . '...') ?>
                    </p>
                    <a href="/event.php?slug=<?= $event->get('slug') ?>" 
                       class="inline-flex items-center text-primary-600 font-semibold hover:text-primary-700">
                        Read Full Story <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Search & Filter -->
<section class="py-8 bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" action="" class="flex gap-4">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search events..."
                       class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <button type="submit" class="px-8 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition">
                Search
            </button>
            <?php if ($search): ?>
            <a href="events.php" class="px-8 py-3 border border-gray-300 rounded-lg font-medium hover:bg-gray-50 transition">
                Clear
            </a>
            <?php endif; ?>
        </form>
    </div>
</section>

<!-- Events Grid -->
<section class="py-16 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Results Count -->
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">
                <?= $search ? "Search Results for \"" . htmlspecialchars($search) . "\"" : "All Events" ?>
            </h2>
            <span class="text-gray-600"><?= $totalEvents ?> event<?= $totalEvents !== 1 ? 's' : '' ?> found</span>
        </div>
        
        <?php if (!empty($events)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($events as $eventData): 
                $event = new Event($eventData['id']);
                $images = $event->getImages();
            ?>
            <article class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex flex-col">
                <!-- Image -->
                <div class="relative h-48 overflow-hidden">
                    <?php if ($event->getCoverImageUrl()): ?>
                    <img src="<?= $event->getCoverImageUrl() ?>" 
                         alt="<?= htmlspecialchars($event->get('title')) ?>"
                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                    <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-image text-4xl text-gray-400"></i>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Image Count Badge -->
                    <?php if (count($images) > 1): ?>
                    <div class="absolute top-3 right-3 bg-black bg-opacity-70 text-white px-2 py-1 rounded-lg text-sm">
                        <i class="fas fa-images mr-1"></i> <?= count($images) ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Content -->
                <div class="p-6 flex-1 flex flex-col">
                    <!-- Date & Location -->
                    <div class="flex flex-wrap gap-3 text-sm text-gray-500 mb-3">
                        <?php if ($event->get('event_date')): ?>
                        <span class="flex items-center">
                            <i class="fas fa-calendar-alt mr-1 text-primary-500"></i>
                            <?= $event->getFormattedDate() ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($event->get('location')): ?>
                        <span class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-1 text-primary-500"></i>
                            <?= htmlspecialchars($event->get('location')) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Title -->
                    <h3 class="text-lg font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-primary-600 transition-colors">
                        <?= htmlspecialchars($event->get('title')) ?>
                    </h3>
                    
                    <!-- Summary -->
                    <p class="text-gray-600 text-sm line-clamp-3 mb-4 flex-1">
                        <?= htmlspecialchars($event->get('summary') ?: substr($event->get('story'), 0, 120) . '...') ?>
                    </p>
                    
                    <!-- Read More Link -->
                    <a href="/event.php?slug=<?= $event->get('slug') ?>" 
                       class="inline-flex items-center text-primary-600 font-medium hover:text-primary-700 mt-auto">
                        Read Full Story <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-12">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <!-- Previous -->
                <a href="?page=<?= max(1, $currentPage - 1) ?>&search=<?= urlencode($search) ?>" 
                   class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?= $currentPage <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
                
                <!-- Page Numbers -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === 1 || $i === $totalPages || ($i >= $currentPage - 1 && $i <= $currentPage + 1)): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50 <?= $currentPage == $i ? 'z-10 bg-primary-50 border-primary-500 text-primary-600' : 'text-gray-500' ?>">
                        <?= $i ?>
                    </a>
                    <?php elseif ($i === $currentPage - 2 || $i === $currentPage + 2): ?>
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                        ...
                    </span>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <!-- Next -->
                <a href="?page=<?= min($totalPages, $currentPage + 1) ?>&search=<?= urlencode($search) ?>" 
                   class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 <?= $currentPage >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </nav>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-300">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900">No events found</h3>
            <p class="mt-1 text-gray-500 max-w-sm mx-auto">
                <?= $search ? "We couldn't find any events matching your search. Try different keywords." : "No events have been published yet. Check back soon!" ?>
            </p>
            <?php if ($search): ?>
            <div class="mt-6">
                <a href="events.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                    View All Events
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Stay Updated</h2>
        <p class="text-gray-600 mb-8">Subscribe to our newsletter to get notified about upcoming events, workshops, and news.</p>
        
        <?php if (isset($_GET['newsletter']) && $_GET['newsletter'] === 'success'): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 max-w-md mx-auto">
            <i class="fas fa-check-circle text-green-600 mr-2"></i>
            <span class="text-green-800">Thank you for subscribing! Check your inbox for confirmation.</span>
        </div>
        <?php else: ?>
        <form method="POST" action="newsletter-subscribe.php" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
            <input type="email" name="email" placeholder="Enter your email" required
                   class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition whitespace-nowrap">
                <i class="fas fa-paper-plane mr-2"></i> Subscribe
            </button>
        </form>
        <p class="text-xs text-gray-500 mt-3">We respect your privacy. Unsubscribe anytime.</p>
        <?php endif; ?>
    </div>
</section>

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
</style>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
