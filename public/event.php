<?php
/**
 * Event Detail Page
 * Display full event story with photo gallery
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/classes/Event.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    redirect('events.php');
}

$event = Event::findBySlug($slug);

if (!$event) {
    http_response_code(404);
    setFlashMessage('Event not found.', 'error');
    redirect('events.php');
}

$images = $event->getImages();
$page_title = $event->get('title') . ' - Edutrack Events';

// Get related events (other events from same year or recent)
$db = Database::getInstance();
$relatedEvents = $db->fetchAll(
    "SELECT id, title, slug, cover_image, event_date FROM events 
     WHERE status = 'published' AND id != ? 
     ORDER BY event_date DESC LIMIT 3",
    [$event->getId()]
);

require_once __DIR__ . '/../src/templates/header.php';
?>

<!-- Event Header -->
<section class="bg-gradient-to-br from-primary-600 via-blue-700 to-purple-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Breadcrumb -->
        <nav class="mb-6 text-sm">
            <ol class="flex items-center space-x-2">
                <li><a href="/" class="hover:text-yellow-300">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="/events.php" class="hover:text-yellow-300">Events</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-yellow-300 truncate"><?= htmlspecialchars($event->get('title')) ?></li>
            </ol>
        </nav>
        
        <div class="max-w-4xl">
            <?php if ($event->get('is_featured')): ?>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-500 text-gray-900 mb-4">
                <i class="fas fa-star mr-2"></i> Featured Event
            </span>
            <?php endif; ?>
            
            <h1 class="text-4xl md:text-5xl font-bold mb-6">
                <?= htmlspecialchars($event->get('title')) ?>
            </h1>
            
            <div class="flex flex-wrap gap-6 text-lg">
                <?php if ($event->get('event_date')): ?>
                <span class="flex items-center">
                    <i class="fas fa-calendar-alt mr-2 text-yellow-400"></i>
                    <?= $event->getFormattedDate('F j, Y') ?>
                </span>
                <?php endif; ?>
                
                <?php if ($event->get('location')): ?>
                <span class="flex items-center">
                    <i class="fas fa-map-marker-alt mr-2 text-yellow-400"></i>
                    <?= htmlspecialchars($event->get('location')) ?>
                </span>
                <?php endif; ?>
                
                <?php if (!empty($images)): ?>
                <span class="flex items-center">
                    <i class="fas fa-images mr-2 text-yellow-400"></i>
                    <?= count($images) ?> Photos
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Photo Gallery -->
<?php if (!empty($images)): ?>
<section class="bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Main Image -->
        <div class="relative rounded-xl overflow-hidden mb-4 bg-gray-800">
            <img id="main-gallery-image" 
                 src="/uploads/events/<?= htmlspecialchars($images[0]['image_path']) ?>" 
                 alt="<?= htmlspecialchars($images[0]['caption'] ?: $event->get('title')) ?>"
                 class="w-full max-h-[600px] object-contain mx-auto">
            
            <?php if ($images[0]['caption']): ?>
            <div id="main-image-caption" class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-70 text-white p-4">
                <?= htmlspecialchars($images[0]['caption']) ?>
            </div>
            <?php endif; ?>
            
            <!-- Navigation Arrows -->
            <?php if (count($images) > 1): ?>
            <button onclick="changeImage(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white w-12 h-12 rounded-full flex items-center justify-center transition">
                <i class="fas fa-chevron-left text-xl"></i>
            </button>
            <button onclick="changeImage(1)" class="absolute right-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white w-12 h-12 rounded-full flex items-center justify-center transition">
                <i class="fas fa-chevron-right text-xl"></i>
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Thumbnails -->
        <?php if (count($images) > 1): ?>
        <div class="flex gap-3 overflow-x-auto pb-2" id="thumbnail-container">
            <?php foreach ($images as $index => $image): ?>
            <button onclick="selectImage(<?= $index ?>)" 
                    class="thumbnail-btn flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden border-2 transition <?= $index === 0 ? 'border-yellow-500' : 'border-transparent hover:border-gray-500' ?>"
                    data-index="<?= $index ?>">
                <img src="/uploads/events/<?= htmlspecialchars($image['image_path']) ?>" 
                     alt="<?= htmlspecialchars($image['caption'] ?: '') ?>"
                     class="w-full h-full object-cover">
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
const images = <?= json_encode($images) ?>;
let currentImageIndex = 0;

function selectImage(index) {
    currentImageIndex = index;
    updateGallery();
}

function changeImage(direction) {
    currentImageIndex = (currentImageIndex + direction + images.length) % images.length;
    updateGallery();
}

function updateGallery() {
    const mainImage = document.getElementById('main-gallery-image');
    const mainCaption = document.getElementById('main-image-caption');
    
    mainImage.src = '/uploads/events/' + images[currentImageIndex].image_path;
    
    if (images[currentImageIndex].caption) {
        if (!mainCaption) {
            const newCaption = document.createElement('div');
            newCaption.id = 'main-image-caption';
            newCaption.className = 'absolute bottom-0 left-0 right-0 bg-black bg-opacity-70 text-white p-4';
            mainImage.parentElement.appendChild(newCaption);
        }
        document.getElementById('main-image-caption').textContent = images[currentImageIndex].caption;
        document.getElementById('main-image-caption').style.display = 'block';
    } else if (mainCaption) {
        mainCaption.style.display = 'none';
    }
    
    // Update thumbnails
    document.querySelectorAll('.thumbnail-btn').forEach((btn, index) => {
        if (index === currentImageIndex) {
            btn.classList.add('border-yellow-500');
            btn.classList.remove('border-transparent', 'hover:border-gray-500');
            btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        } else {
            btn.classList.remove('border-yellow-500');
            btn.classList.add('border-transparent', 'hover:border-gray-500');
        }
    });
}

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') changeImage(-1);
    if (e.key === 'ArrowRight') changeImage(1);
});
</script>
<?php endif; ?>

<!-- Event Story -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg max-w-none">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">About This Event</h2>
            
            <?php if ($event->get('summary')): ?>
            <p class="text-xl text-gray-600 italic border-l-4 border-primary-500 pl-4 mb-8">
                <?= htmlspecialchars($event->get('summary')) ?>
            </p>
            <?php endif; ?>
            
            <div class="text-gray-700 leading-relaxed whitespace-pre-wrap">
                <?= nl2br(htmlspecialchars($event->get('story'))) ?>
            </div>
        </div>
        
        <!-- Share Buttons -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Share This Event</h3>
            <div class="flex gap-3">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . '/event.php?slug=' . $event->get('slug')) ?>" 
                   target="_blank" rel="noopener"
                   class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?text=<?= urlencode($event->get('title')) ?>&url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . '/event.php?slug=' . $event->get('slug')) ?>" 
                   target="_blank" rel="noopener"
                   class="w-10 h-10 bg-blue-400 text-white rounded-full flex items-center justify-center hover:bg-blue-500 transition">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://wa.me/?text=<?= urlencode($event->get('title') . ' - https://' . $_SERVER['HTTP_HOST'] . '/event.php?slug=' . $event->get('slug')) ?>" 
                   target="_blank" rel="noopener"
                   class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center hover:bg-green-700 transition">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <button onclick="copyLink()" 
                        class="w-10 h-10 bg-gray-600 text-white rounded-full flex items-center justify-center hover:bg-gray-700 transition"
                        title="Copy Link">
                    <i class="fas fa-link"></i>
                </button>
            </div>
            <div id="copy-message" class="text-green-600 text-sm mt-2 hidden">Link copied to clipboard!</div>
        </div>
    </div>
</section>

<script>
function copyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        const msg = document.getElementById('copy-message');
        msg.classList.remove('hidden');
        setTimeout(() => msg.classList.add('hidden'), 2000);
    });
}
</script>

<!-- Related Events -->
<?php if (!empty($relatedEvents)): ?>
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">More Events</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($relatedEvents as $related): 
                $relatedEvent = new Event($related['id']);
            ?>
            <a href="/event.php?slug=<?= $related['slug'] ?>" class="group block bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="h-48 overflow-hidden">
                    <?php if ($related['cover_image']): ?>
                    <img src="/uploads/events/<?= htmlspecialchars($related['cover_image']) ?>" 
                         alt="<?= htmlspecialchars($related['title']) ?>"
                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-4xl text-white opacity-50"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="p-6">
                    <?php if ($related['event_date']): ?>
                    <span class="text-sm text-gray-500 mb-2 block">
                        <i class="fas fa-calendar mr-1"></i> <?= date('F j, Y', strtotime($related['event_date'])) ?>
                    </span>
                    <?php endif; ?>
                    <h3 class="font-bold text-gray-900 group-hover:text-primary-600 transition-colors line-clamp-2">
                        <?= htmlspecialchars($related['title']) ?>
                    </h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-br from-primary-600 via-blue-700 to-purple-800 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Want to Be Part of Our Next Event?</h2>
        <p class="text-xl text-primary-100 mb-8">
            Join Edutrack and participate in our workshops, graduations, and community activities.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/courses.php" class="px-8 py-4 bg-yellow-500 text-gray-900 rounded-lg font-semibold hover:bg-yellow-600 transition">
                <i class="fas fa-book mr-2"></i> Browse Courses
            </a>
            <a href="/contact.php" class="px-8 py-4 border-2 border-white text-white rounded-lg font-semibold hover:bg-white hover:text-primary-600 transition">
                <i class="fas fa-envelope mr-2"></i> Contact Us
            </a>
        </div>
    </div>
</section>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.prose p {
    margin-bottom: 1.25em;
}
</style>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
