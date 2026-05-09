<!-- Footer -->
<footer class="bg-gray-900 text-gray-300 mt-20">
    <!-- Main Footer Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

            <!-- About Section -->
            <div>
                <div class="flex items-center mb-4">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Edutrack Logo" class="h-10 w-auto mr-2">
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
                    <a href="#" class="text-gray-400 hover:text-secondary-500 transition"><i class="fab fa-facebook text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-secondary-500 transition"><i class="fab fa-twitter text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-secondary-500 transition"><i class="fab fa-linkedin text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-secondary-500 transition"><i class="fab fa-instagram text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-secondary-500 transition"><i class="fab fa-youtube text-xl"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-white text-lg font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="{{ url('/') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-chevron-right text-xs mr-2"></i>Home</a></li>
                    <li><a href="{{ route('courses.index') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-chevron-right text-xs mr-2"></i>All Courses</a></li>
                    <li><a href="{{ route('about') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-chevron-right text-xs mr-2"></i>About Us</a></li>
                    <li><a href="{{ route('campus') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-chevron-right text-xs mr-2"></i>Campus</a></li>
                    <li><a href="{{ route('events') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-chevron-right text-xs mr-2"></i>Events</a></li>
                    <li><a href="{{ route('faq') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-chevron-right text-xs mr-2"></i>FAQ</a></li>
                    <li><a href="{{ route('contact') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-chevron-right text-xs mr-2"></i>Contact</a></li>
                    @guest
                        <li><a href="{{ route('register') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-chevron-right text-xs mr-2"></i>Register</a></li>
                    @else
                        <li><a href="{{ route('student.dashboard') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-chevron-right text-xs mr-2"></i>Dashboard</a></li>
                    @endguest
                </ul>
            </div>

            <!-- Popular Courses -->
            <div>
                <h3 class="text-white text-lg font-bold mb-4">Popular Courses</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('courses.index') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-code text-xs mr-2"></i>Web Development</a></li>
                    <li><a href="{{ route('courses.index') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-bullhorn text-xs mr-2"></i>Digital Marketing</a></li>
                    <li><a href="{{ route('courses.index') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-chart-line text-xs mr-2"></i>Data Science</a></li>
                    <li><a href="{{ route('courses.index') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-paint-brush text-xs mr-2"></i>Graphic Design</a></li>
                    <li><a href="{{ route('courses.index') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-file-excel text-xs mr-2"></i>Microsoft Office</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="text-white text-lg font-bold mb-4">Contact Us</h3>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt text-secondary-500 mt-1 mr-3"></i>
                        <span class="text-sm text-gray-400">Kalomo, Zambia</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-phone text-secondary-500 mt-1 mr-3"></i>
                        <a href="tel:+260770666937" class="text-sm text-gray-400 hover:text-secondary-500 transition">+260 770 666 937</a>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-envelope text-secondary-500 mt-1 mr-3"></i>
                        <a href="mailto:edutrackzambia@gmail.com" class="text-sm text-gray-400 hover:text-secondary-500 transition">edutrackzambia@gmail.com</a>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-certificate text-secondary-500 mt-1 mr-3"></i>
                        <div class="text-sm">
                            <div class="text-gray-400">TEVETA Registration</div>
                            <div class="text-white font-semibold">TEVETA/CTR/2024/001</div>
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
                <form class="flex flex-col sm:flex-row gap-2 max-w-md mx-auto" onsubmit="event.preventDefault(); handleNewsletterSubmit(this);">
                    <input type="email" required placeholder="Enter your email" class="flex-1 px-4 py-2 rounded-md bg-gray-800 text-white border border-gray-700 focus:outline-none focus:border-secondary-500">
                    <button type="submit" class="btn-secondary px-6 py-2 rounded-md font-medium whitespace-nowrap">
                        <i class="fas fa-paper-plane mr-2"></i>Subscribe
                    </button>
                </form>
                <p class="text-sm text-green-400 mt-2 hidden newsletter-msg">Thank you for subscribing!</p>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="bg-gray-950 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-400 text-center md:text-left">
                    <p>&copy; {{ date('Y') }} Edutrack Computer Training College. All rights reserved.</p>
                    <p class="text-xs mt-1">TEVETA Registered Institution - TEVETA/CTR/2024/001</p>
                </div>
                <div class="flex flex-wrap items-center justify-center md:justify-end gap-x-4 gap-y-2 text-sm">
                    <a href="#" class="text-gray-400 hover:text-secondary-500 transition">Privacy Policy</a>
                    <span class="text-gray-600">|</span>
                    <a href="#" class="text-gray-400 hover:text-secondary-500 transition">Terms of Service</a>
                    <span class="text-gray-600">|</span>
                    <a href="#" class="text-gray-400 hover:text-secondary-500 transition">Verify Certificate</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
function handleNewsletterSubmit(form) {
    const btn = form.querySelector('button[type="submit"]');
    const msg = form.parentElement.querySelector('.newsletter-msg');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Subscribing...';
    setTimeout(function() {
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>Subscribed';
        msg.classList.remove('hidden');
    }, 1000);
}
</script>
