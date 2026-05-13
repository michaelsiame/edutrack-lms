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
                        <span class="text-secondary-500 text-xs font-semibold">COMPUTER TRAINING</span>
                    </div>
                </div>
                <p class="text-sm text-gray-400 mb-4">
                    Transform your future with Zambia's premier computer training institution.
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
                @php
                $footerCourses = \App\Models\Course::published()->where('is_featured', true)->orWhere('enrollment_count', '>', 0)->orderByDesc('enrollment_count')->limit(5)->get();
                @endphp
                @if($footerCourses->count() > 0)
                <ul class="space-y-2">
                    @foreach($footerCourses as $fc)
                    <li><a href="{{ route('courses.show', $fc) }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-graduation-cap text-xs mr-2"></i>{{ $fc->title }}</a></li>
                    @endforeach
                </ul>
                @else
                <ul class="space-y-2">
                    <li><a href="{{ route('courses.index') }}" class="text-gray-400 hover:text-secondary-500 transition text-sm"><i class="fas fa-chevron-right text-xs mr-2"></i>Browse All Courses</a></li>
                </ul>
                @endif
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="text-white text-lg font-bold mb-4">Contact Us</h3>
                @php
                $footerAddress = \App\Models\SystemSetting::get('site_address', 'Kalomo, Zambia');
                $footerPhone = \App\Models\SystemSetting::get('site_phone');
                @endphp
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt text-secondary-500 mt-1 mr-3"></i>
                        <span class="text-sm text-gray-400">{{ $footerAddress }}</span>
                    </li>
                    @if($footerPhone)
                    <li class="flex items-start">
                        <i class="fas fa-phone text-secondary-500 mt-1 mr-3"></i>
                        <a href="tel:{{ preg_replace('/\s+/', '', $footerPhone) }}" class="text-sm text-gray-400 hover:text-secondary-500 transition">{{ $footerPhone }}</a>
                    </li>
                    @endif
                    <li class="flex items-start">
                        <i class="fas fa-envelope text-secondary-500 mt-1 mr-3"></i>
                        @php $siteEmail = \App\Models\SystemSetting::get('site_email'); @endphp
                        @if($siteEmail)
                        <a href="mailto:{{ $siteEmail }}" class="text-sm text-gray-400 hover:text-secondary-500 transition">{{ $siteEmail }}</a>
                        @else
                        <span class="text-sm text-gray-500">Email coming soon</span>
                        @endif
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-certificate text-secondary-500 mt-1 mr-3"></i>
                        <div class="text-sm">
                            <div class="text-gray-400">Registered Institution</div>
                            <div class="text-white font-semibold">Quality Assured</div>
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
                    <input type="email" name="email" required placeholder="Enter your email" class="flex-1 px-4 py-2 rounded-md bg-gray-800 text-white border border-gray-700 focus:outline-none focus:border-secondary-500">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="submit" class="btn-secondary px-6 py-2 rounded-md font-medium whitespace-nowrap">
                        <i class="fas fa-paper-plane mr-2"></i>Subscribe
                    </button>
                </form>
                <p class="text-sm text-green-400 mt-2 hidden newsletter-msg" id="newsletter-success">Thank you for subscribing!</p>
                <p class="text-sm text-red-400 mt-2 hidden newsletter-msg" id="newsletter-error">Something went wrong. Please try again.</p>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="bg-gray-950 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-400 text-center md:text-left">
                    <p>&copy; {{ date('Y') }} Edutrack Computer Training College. All rights reserved.</p>

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
    const successMsg = document.getElementById('newsletter-success');
    const errorMsg = document.getElementById('newsletter-error');
    const email = form.querySelector('input[name="email"]').value;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Subscribing...';
    fetch('{{ route('newsletter.subscribe') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ email: email })
    })
    .then(r => r.json())
    .then(data => {
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>Subscribed';
        successMsg.classList.remove('hidden');
        errorMsg.classList.add('hidden');
        form.reset();
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Subscribe';
        errorMsg.classList.remove('hidden');
    });
}
</script>
