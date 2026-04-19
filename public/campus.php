<?php
/**
 * Campus & Facilities Page
 * Showcases institution photos, labs, classrooms
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/classes/InstitutionPhoto.php';

$db = Database::getInstance();

// Get filter
$category = $_GET['category'] ?? 'all';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;

// Get photos
$filters = ['limit' => $perPage];
if ($category !== 'all') {
    $filters['category'] = $category;
}
$photos = InstitutionPhoto::getAll($filters);

// Get counts by category
$categoryCounts = $db->fetchAll(
    "SELECT category, COUNT(*) as count FROM institution_photos GROUP BY category"
);
$countMap = array_column($categoryCounts, 'count', 'category');

// Get featured photos for hero
$featuredPhotos = InstitutionPhoto::getFeatured(6);

// Categories
$categories = InstitutionPhoto::getCategories();

$page_title = "Campus & Facilities - Edutrack Computer Training College";

require_once __DIR__ . '/../src/templates/header.php';
?>

<!-- Page Header -->
<section class="relative h-[400px] overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?= !empty($featuredPhotos) ? $featuredPhotos[0]->getImageUrl() : '/assets/images/campus-hero.jpg' ?>');">
        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent"></div>
    </div>
    <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-end pb-16">
        <div>
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                <i class="fas fa-university mr-3"></i>Our Campus
            </h1>
            <p class="text-xl text-gray-200 max-w-2xl">
                Explore our modern facilities designed to provide the best learning environment for our students.
            </p>
        </div>
    </div>
</section>

<!-- Quick Info Cards -->
<section class="py-12 bg-white -mt-8 relative z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-primary-50 rounded-xl p-6 text-center">
                <i class="fas fa-desktop text-3xl text-primary-600 mb-3"></i>
                <div class="text-2xl font-bold text-gray-900">50+</div>
                <div class="text-sm text-gray-600">Computer Workstations</div>
            </div>
            <div class="bg-green-50 rounded-xl p-6 text-center">
                <i class="fas fa-chalkboard-teacher text-3xl text-green-600 mb-3"></i>
                <div class="text-2xl font-bold text-gray-900">8</div>
                <div class="text-sm text-gray-600">Modern Classrooms</div>
            </div>
            <div class="bg-purple-50 rounded-xl p-6 text-center">
                <i class="fas fa-wifi text-3xl text-purple-600 mb-3"></i>
                <div class="text-2xl font-bold text-gray-900">Fiber</div>
                <div class="text-sm text-gray-600">High-Speed Internet</div>
            </div>
            <div class="bg-yellow-50 rounded-xl p-6 text-center">
                <i class="fas fa-book text-3xl text-yellow-600 mb-3"></i>
                <div class="text-2xl font-bold text-gray-900">Library</div>
                <div class="text-sm text-gray-600">Digital Resources</div>
            </div>
        </div>
    </div>
</section>

<!-- Virtual Tour CTA -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-primary-600 to-purple-600 rounded-2xl p-8 md:p-12 text-white flex flex-col md:flex-row items-center justify-between gap-8">
            <div>
                <h2 class="text-3xl font-bold mb-3">Can't Visit in Person?</h2>
                <p class="text-primary-100 text-lg">Schedule a virtual campus tour with our admissions team. We'll show you around via video call.</p>
            </div>
            <a href="contact.php?subject=Virtual Campus Tour" class="px-8 py-4 bg-white text-primary-600 rounded-lg font-semibold hover:bg-gray-100 transition whitespace-nowrap">
                <i class="fas fa-video mr-2"></i>Book Virtual Tour
            </a>
        </div>
    </div>
</section>

<!-- Photo Gallery -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Campus Gallery</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Take a visual tour of our facilities, classrooms, computer labs, and student spaces.
            </p>
        </div>
        
        <!-- Category Filter -->
        <div class="flex flex-wrap justify-center gap-3 mb-10">
            <a href="?category=all" 
               class="px-6 py-2 rounded-full font-medium transition <?= $category === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                All Photos
                <span class="ml-2 text-sm opacity-80">(<?= array_sum($countMap) ?>)</span>
            </a>
            <?php foreach ($categories as $key => $label): 
                $count = $countMap[$key] ?? 0;
                if ($count > 0):
            ?>
            <a href="?category=<?= $key ?>" 
               class="px-6 py-2 rounded-full font-medium transition <?= $category === $key ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <?= $label ?>
                <span class="ml-2 text-sm opacity-80">(<?= $count ?>)</span>
            </a>
            <?php endif; endforeach; ?>
        </div>
        
        <!-- Photo Grid -->
        <?php if (!empty($photos)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="photo-gallery">
            <?php foreach ($photos as $photoData): 
                $photo = new InstitutionPhoto($photoData['id']);
            ?>
            <div class="group relative overflow-hidden rounded-xl cursor-pointer" onclick="openLightbox(<?= $photo->getId() ?>)">
                <img src="<?= $photo->getImageUrl() ?>" 
                     alt="<?= htmlspecialchars($photo->get('title')) ?>"
                     class="w-full h-64 object-cover transform group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-300">
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <span class="text-xs bg-yellow-500 text-gray-900 px-2 py-1 rounded-full font-medium mb-2 inline-block">
                            <?= $categories[$photo->get('category')] ?? 'Campus' ?>
                        </span>
                        <h3 class="text-white font-semibold text-lg"><?= htmlspecialchars($photo->get('title')) ?></h3>
                        <?php if ($photo->get('description')): ?>
                        <p class="text-gray-300 text-sm mt-1 line-clamp-2"><?= htmlspecialchars($photo->get('description')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300 transform translate-y-2 group-hover:translate-y-0">
                    <i class="fas fa-expand text-gray-700"></i>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Load More Button -->
        <div class="text-center mt-10">
            <button id="loadMoreBtn" onclick="loadMorePhotos()" class="px-8 py-3 border-2 border-primary-600 text-primary-600 rounded-lg font-semibold hover:bg-primary-600 hover:text-white transition">
                <i class="fas fa-images mr-2"></i>Load More Photos
            </button>
        </div>
        <?php else: ?>
        <div class="text-center py-16">
            <i class="fas fa-images text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900">Photos Coming Soon</h3>
            <p class="text-gray-600">We're updating our campus gallery. Check back soon!</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Facilities Overview -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Facilities</h2>
            <p class="text-xl text-gray-600">Everything you need for a successful learning experience</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Computer Labs -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                <div class="h-48 bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                    <i class="fas fa-laptop-code text-6xl text-white"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Computer Labs</h3>
                    <p class="text-gray-600 mb-4">
                        Three fully-equipped computer labs with over 50 modern workstations, high-speed internet, and the latest software for hands-on training.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Windows & Linux systems</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>High-speed fiber internet</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Industry software suite</li>
                    </ul>
                </div>
            </div>
            
            <!-- Classrooms -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                <div class="h-48 bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center">
                    <i class="fas fa-chalkboard text-6xl text-white"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Modern Classrooms</h3>
                    <p class="text-gray-600 mb-4">
                        Spacious, air-conditioned classrooms designed for interactive learning with projector systems and comfortable seating.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Projector & display systems</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Air-conditioned comfort</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Interactive whiteboards</li>
                    </ul>
                </div>
            </div>
            
            <!-- Library -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                <div class="h-48 bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center">
                    <i class="fas fa-book-reader text-6xl text-white"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Library & Study Area</h3>
                    <p class="text-gray-600 mb-4">
                        Quiet study spaces with access to digital resources, textbooks, and reference materials for all courses.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Digital resource access</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Quiet study zones</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Reference materials</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Location Map Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Visit Our Campus</h2>
                <p class="text-gray-600 mb-6">
                    Located in the heart of Kalomo, our campus is easily accessible and provides a conducive environment for learning.
                </p>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-primary-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Address</h4>
                            <p class="text-gray-600">Edutrack Computer Training College<br>Kalomo, Southern Province<br>Zambia</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-clock text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Visiting Hours</h4>
                            <p class="text-gray-600">Monday - Friday: 8:00 AM - 5:00 PM<br>Saturday: 9:00 AM - 1:00 PM</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-phone text-yellow-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Contact</h4>
                            <p class="text-gray-600"><?= SITE_PHONE ?><br><?= SITE_EMAIL ?></p>
                        </div>
                    </div>
                </div>
                <a href="contact.php" class="inline-flex items-center mt-8 px-6 py-3 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition">
                    <i class="fas fa-calendar-check mr-2"></i>Schedule a Visit
                </a>
            </div>
            <div class="bg-gray-100 rounded-2xl h-96 overflow-hidden">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15451.774!2d26.0833!3d-17.0333!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x194b4f5b7c7b2b3d%3A0x0!2sKalomo%2C%20Zambia!5e0!3m2!1sen!2szm!4v1713470400000!5m2!1sen!2szm"
                    width="100%"
                    height="100%"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Edutrack Campus Location - Kalomo, Zambia">
                </iframe>
            </div>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-95 z-50 hidden flex items-center justify-center">
    <button onclick="closeLightbox()" class="absolute top-4 right-4 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition">
        <i class="fas fa-times text-xl"></i>
    </button>
    <button onclick="changeLightboxImage(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition">
        <i class="fas fa-chevron-left text-xl"></i>
    </button>
    <button onclick="changeLightboxImage(1)" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition">
        <i class="fas fa-chevron-right text-xl"></i>
    </button>
    <div class="max-w-5xl max-h-screen p-4">
        <img id="lightbox-image" src="" alt="" class="max-w-full max-h-[80vh] object-contain">
        <div id="lightbox-caption" class="text-white text-center mt-4"></div>
    </div>
</div>

<script>
let photos = <?= json_encode(array_map(function($p) { 
    $photo = new InstitutionPhoto($p['id']);
    return [
        'id' => $p['id'],
        'url' => $photo->getImageUrl(),
        'title' => $p['title'],
        'description' => $p['description']
    ];
}, $photos)) ?>;
let currentPhotoIndex = 0;

function openLightbox(photoId) {
    currentPhotoIndex = photos.findIndex(p => p.id === photoId);
    updateLightbox();
    document.getElementById('lightbox').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.add('hidden');
    document.body.style.overflow = '';
}

function changeLightboxImage(direction) {
    currentPhotoIndex = (currentPhotoIndex + direction + photos.length) % photos.length;
    updateLightbox();
}

function updateLightbox() {
    const photo = photos[currentPhotoIndex];
    document.getElementById('lightbox-image').src = photo.url;
    document.getElementById('lightbox-image').alt = photo.title;
    document.getElementById('lightbox-caption').innerHTML = `
        <h3 class="text-xl font-semibold">${photo.title}</h3>
        ${photo.description ? `<p class="text-gray-400 mt-1">${photo.description}</p>` : ''}
    `;
}

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (document.getElementById('lightbox').classList.contains('hidden')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') changeLightboxImage(-1);
    if (e.key === 'ArrowRight') changeLightboxImage(1);
});

function loadMorePhotos() {
    const btn = document.querySelector('[onclick="loadMorePhotos()"]');
    if (btn) btn.remove();
}
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php require_once __DIR__ . '/../src/templates/footer.php'; ?>
