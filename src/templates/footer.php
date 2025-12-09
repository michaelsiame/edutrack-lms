<?php
/**
 * Edutrack computer training college
 * Main Site Footer Template
 */
?>

<!-- Footer -->
<footer class="bg-gray-900 text-gray-300 mt-20">
    <!-- Main Footer Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            
            <!-- About Section -->
            <div>
                <div class="flex items-center mb-4">
                    <img src="<?= asset('images/logo.png') ?>" alt="Edutrack Logo" class="h-10 w-auto mr-2">
                    <div>
                        <h3 class="text-white text-lg font-bold">Edutrack</h3>
                        <span class="text-secondary-500 text-xs font-semibold">TEVETA REGISTERED</span>
                    </div>
                </div>
                <p class="text-sm text-gray-400 mb-4">
                    Transform your future with Zambia's premier TEVETA-registered computer training institution. 
                    Quality education, industry-recognized certification.
                </p>
                <div class="flex items-center space-x-3">
                    <?php if (config('social.facebook')): ?>
                        <a href="<?= config('social.facebook') ?>" target="_blank" class="text-gray-400 hover:text-secondary-500 transition">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (config('social.twitter')): ?>
                        <a href="<?= config('social.twitter') ?>" target="_blank" class="text-gray-400 hover:text-secondary-500 transition">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (config('social.linkedin')): ?>
                        <a href="<?= config('social.linkedin') ?>" target="_blank" class="text-gray-400 hover:text-secondary-500 transition">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (config('social.instagram')): ?>
                        <a href="<?= config('social.instagram') ?>" target="_blank" class="text-gray-400 hover:text-secondary-500 transition">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (config('social.youtube')): ?>
                        <a href="<?= config('social.youtube') ?>" target="_blank" class="text-gray-400 hover:text-secondary-500 transition">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-white text-lg font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="<?= url() ?>" class="text-gray-400 hover:text-secondary-500 transition text-sm">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>Home
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('courses.php') ?>" class="text-gray-400 hover:text-secondary-500 transition text-sm">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>All Courses
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('about.php') ?>" class="text-gray-400 hover:text-secondary-500 transition text-sm">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>About Us
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('contact.php') ?>" class="text-gray-400 hover:text-secondary-500 transition text-sm">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>Contact
                        </a>
                    </li>
                    <?php if (!isLoggedIn()): ?>
                    <li>
                        <a href="<?= url('register.php') ?>" class="text-gray-400 hover:text-secondary-500 transition text-sm">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>Register
                        </a>
                    </li>
                    <?php else: ?>
                    <li>
                        <a href="<?= url('dashboard.php') ?>" class="text-gray-400 hover:text-secondary-500 transition text-sm">
                            <i class="fas fa-chevron-right text-xs mr-2"></i>Dashboard
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Popular Courses -->
            <div>
                <h3 class="text-white text-lg font-bold mb-4">Popular Courses</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="<?= url('courses.php?category=web-development') ?>" class="text-gray-400 hover:text-secondary-500 transition text-sm">
                            <i class="fas fa-code text-xs mr-2"></i>Web Development
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('courses.php?category=digital-marketing') ?>" class="text-gray-400 hover:text-secondary-500 transition text-sm">
                            <i class="fas fa-bullhorn text-xs mr-2"></i>Digital Marketing
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('courses.php?category=data-science') ?>" class="text-gray-400 hover:text-secondary-500 transition text-sm">
                            <i class="fas fa-chart-line text-xs mr-2"></i>Data Science
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('courses.php?category=graphic-design') ?>" class="text-gray-400 hover:text-secondary-500 transition text-sm">
                            <i class="fas fa-paint-brush text-xs mr-2"></i>Graphic Design
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('courses.php?category=microsoft-office') ?>" class="text-gray-400 hover:text-secondary-500 transition text-sm">
                            <i class="fas fa-file-excel text-xs mr-2"></i>Microsoft Office
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h3 class="text-white text-lg font-bold mb-4">Contact Us</h3>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt text-secondary-500 mt-1 mr-3"></i>
                        <span class="text-sm text-gray-400"><?= SITE_ADDRESS ?></span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-phone text-secondary-500 mt-1 mr-3"></i>
                        <a href="tel:<?= SITE_PHONE ?>" class="text-sm text-gray-400 hover:text-secondary-500 transition">
                            <?= SITE_PHONE ?>
                        </a>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-envelope text-secondary-500 mt-1 mr-3"></i>
                        <a href="mailto:<?= SITE_EMAIL ?>" class="text-sm text-gray-400 hover:text-secondary-500 transition">
                            <?= SITE_EMAIL ?>
                        </a>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-certificate text-secondary-500 mt-1 mr-3"></i>
                        <div class="text-sm">
                            <div class="text-gray-400">TEVETA Registration</div>
                            <div class="text-white font-semibold"><?= TEVETA_CODE ?></div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Newsletter Section -->
        <div class="mt-12 pt-8 border-t border-gray-800">
            <div class="max-w-2xl mx-auto text-center">
                <h3 class="text-white text-xl font-bold mb-2">Stay Updated</h3>
                <p class="text-gray-400 text-sm mb-4">Subscribe to our newsletter for course updates and tech news</p>
                <form class="flex flex-col sm:flex-row gap-2 max-w-md mx-auto">
                    <input type="email" placeholder="Enter your email" class="flex-1 px-4 py-2 rounded-md bg-gray-800 text-white border border-gray-700 focus:outline-none focus:border-secondary-500">
                    <button type="submit" class="btn-secondary px-6 py-2 rounded-md font-medium whitespace-nowrap">
                        <i class="fas fa-paper-plane mr-2"></i>Subscribe
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bottom Bar -->
    <div class="bg-gray-950 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm text-gray-400 mb-4 md:mb-0">
                    <p>&copy; <?= date('Y') ?> Edutrack computer training college. All rights reserved.</p>
                    <p class="text-xs mt-1">TEVETA Registered Institution - <?= TEVETA_CODE ?></p>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                    <a href="<?= url('privacy.php') ?>" class="text-gray-400 hover:text-secondary-500 transition">Privacy Policy</a>
                    <span class="text-gray-600">|</span>
                    <a href="<?= url('terms.php') ?>" class="text-gray-400 hover:text-secondary-500 transition">Terms of Service</a>
                    <span class="text-gray-600">|</span>
                    <a href="<?= url('certificate-verify.php') ?>" class="text-gray-400 hover:text-secondary-500 transition">Verify Certificate</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scrollToTop" class="fixed bottom-8 right-8 bg-primary-600 text-white w-12 h-12 rounded-full shadow-lg hover:bg-primary-700 transition-all duration-300 hidden z-40">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Custom JavaScript -->
<script src="<?= asset('js/main.js') ?>"></script>

<?php if (isset($extra_js)): ?>
    <?= $extra_js ?>
<?php endif; ?>

<!-- Scroll to Top Script -->
<script>
    // Scroll to top functionality
    const scrollToTopBtn = document.getElementById('scrollToTop');
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.remove('hidden');
        } else {
            scrollToTopBtn.classList.add('hidden');
        }
    });
    
    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Mobile menu Alpine.js integration
    document.addEventListener('alpine:init', () => {
        Alpine.data('mobileMenu', () => ({
            mobileMenuOpen: false
        }))
    })
</script>

</body>
</html>