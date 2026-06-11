<!-- Footer -->
<footer class="od-footer">
    <!-- Main Footer Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

            <!-- About Section -->
            <div>
                <div class="flex items-center mb-4">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Edutrack Logo" class="h-10 w-auto mr-2">
                    <div>
                        <h3 class="od-footer-heading" style="margin-bottom: 0;">Edutrack</h3>
                        <span class="text-xs font-semibold" style="color: var(--od-accent);">COMPUTER TRAINING</span>
                    </div>
                </div>
                <p class="od-footer-text mb-4">
                    Transform your future with Zambia's premier computer training institution.
                    Quality education, industry-recognized certification.
                </p>
                <div class="flex items-center space-x-3">
                    <a href="#" aria-label="Facebook" class="transition inline-flex items-center justify-center" style="color: var(--od-muted); width: 40px; height: 40px;" onmouseover="this.style.color='var(--od-accent)'" onmouseout="this.style.color='var(--od-muted)'"><i class="fab fa-facebook text-xl"></i></a>
                    <a href="#" aria-label="Twitter" class="transition inline-flex items-center justify-center" style="color: var(--od-muted); width: 40px; height: 40px;" onmouseover="this.style.color='var(--od-accent)'" onmouseout="this.style.color='var(--od-muted)'"><i class="fab fa-twitter text-xl"></i></a>
                    <a href="#" aria-label="LinkedIn" class="transition inline-flex items-center justify-center" style="color: var(--od-muted); width: 40px; height: 40px;" onmouseover="this.style.color='var(--od-accent)'" onmouseout="this.style.color='var(--od-muted)'"><i class="fab fa-linkedin text-xl"></i></a>
                    <a href="#" aria-label="Instagram" class="transition inline-flex items-center justify-center" style="color: var(--od-muted); width: 40px; height: 40px;" onmouseover="this.style.color='var(--od-accent)'" onmouseout="this.style.color='var(--od-muted)'"><i class="fab fa-instagram text-xl"></i></a>
                    <a href="#" aria-label="YouTube" class="transition inline-flex items-center justify-center" style="color: var(--od-muted); width: 40px; height: 40px;" onmouseover="this.style.color='var(--od-accent)'" onmouseout="this.style.color='var(--od-muted)'"><i class="fab fa-youtube text-xl"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="od-footer-heading">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="{{ url('/') }}" class="od-footer-link"><i class="fas fa-chevron-right text-xs mr-2" style="color: var(--od-accent);"></i>Home</a></li>
                    <li><a href="{{ route('courses.index') }}" class="od-footer-link"><i class="fas fa-chevron-right text-xs mr-2" style="color: var(--od-accent);"></i>All Courses</a></li>
                    <li><a href="{{ route('about') }}" class="od-footer-link"><i class="fas fa-chevron-right text-xs mr-2" style="color: var(--od-accent);"></i>About Us</a></li>
                    <li><a href="{{ route('campus') }}" class="od-footer-link"><i class="fas fa-chevron-right text-xs mr-2" style="color: var(--od-accent);"></i>Campus</a></li>
                    <li><a href="{{ route('events') }}" class="od-footer-link"><i class="fas fa-chevron-right text-xs mr-2" style="color: var(--od-accent);"></i>Events</a></li>
                    <li><a href="{{ route('faq') }}" class="od-footer-link"><i class="fas fa-chevron-right text-xs mr-2" style="color: var(--od-accent);"></i>FAQ</a></li>
                    <li><a href="{{ route('contact') }}" class="od-footer-link"><i class="fas fa-chevron-right text-xs mr-2" style="color: var(--od-accent);"></i>Contact</a></li>
                    @guest
                    <li><a href="{{ route('register') }}" class="od-footer-link"><i class="fas fa-chevron-right text-xs mr-2" style="color: var(--od-accent);"></i>Register</a></li>
                    @else
                    <li><a href="{{ route('student.dashboard') }}" class="od-footer-link"><i class="fas fa-chevron-right text-xs mr-2" style="color: var(--od-accent);"></i>Dashboard</a></li>
                    @endguest
                </ul>
            </div>

            <!-- Popular Courses -->
            <div>
                <h3 class="od-footer-heading">Popular Courses</h3>
                @php
                $footerCourses = \App\Models\Course::published()->where('is_featured', true)->orWhere('enrollment_count','>', 0)->orderByDesc('enrollment_count')->limit(5)->get();
                @endphp
                @if($footerCourses->count() > 0)
                <ul class="space-y-2">
                    @foreach($footerCourses as $fc)
                    <li><a href="{{ route('courses.show', $fc) }}" class="od-footer-link"><i class="fas fa-graduation-cap text-xs mr-2" style="color: var(--od-accent);"></i>{{ $fc->title }}</a></li>
                    @endforeach
                </ul>
                @else
                <ul class="space-y-2">
                    <li><a href="{{ route('courses.index') }}" class="od-footer-link"><i class="fas fa-chevron-right text-xs mr-2" style="color: var(--od-accent);"></i>Browse All Courses</a></li>
                </ul>
                @endif
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="od-footer-heading">Contact Us</h3>
                @php
                $footerAddress = \App\Models\SystemSetting::get('site_address','Kalomo, Zambia');
                $footerPhone = \App\Models\SystemSetting::get('site_phone');
                @endphp
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 mr-3" style="color: var(--od-accent);"></i>
                        <span class="od-footer-text">{{ $footerAddress }}</span>
                    </li>
                    @if($footerPhone)
                    <li class="flex items-start">
                        <i class="fas fa-phone mt-1 mr-3" style="color: var(--od-accent);"></i>
                        <a href="tel:{{ preg_replace('/\s+/','', $footerPhone) }}" class="od-footer-link">{{ $footerPhone }}</a>
                    </li>
                    @endif
                    <li class="flex items-start">
                        <i class="fas fa-envelope mt-1 mr-3" style="color: var(--od-accent);"></i>
                        @php $siteEmail = \App\Models\SystemSetting::get('site_email'); @endphp
                        @if($siteEmail)
                        <a href="mailto:{{ $siteEmail }}" class="od-footer-link">{{ $siteEmail }}</a>
                        @else
                        <span class="od-footer-text">Email coming soon</span>
                        @endif
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-certificate mt-1 mr-3" style="color: var(--od-accent);"></i>
                        <div class="text-sm">
                            <div class="od-footer-text">Registered Institution</div>
                            <div style="color: var(--od-surface); font-weight: 600;">Quality Assured</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Newsletter Section -->
        <div class="mt-12 pt-8 border-t" style="border-color: color-mix(in oklch, var(--od-fg) 70%, white);">
            <div class="max-w-2xl mx-auto text-center">
                <h3 class="text-xl font-bold mb-2" style="color: var(--od-surface);">Stay Updated</h3>
                <p class="od-footer-text mb-4">Subscribe to our newsletter for course updates and tech news</p>
                <form class="flex flex-col sm:flex-row gap-2 max-w-md mx-auto" action="{{ route('newsletter.subscribe') }}" method="POST">
                    @csrf
                    <input type="text" name="name" placeholder="Your name (optional)" class="od-footer-input">
                    <input type="email" name="email" required placeholder="Enter your email" class="od-footer-input">
                    <button type="submit" class="od-footer-btn">
                        <i class="fas fa-paper-plane mr-2"></i>Subscribe
                    </button>
                </form>
                @if(session('success'))
                    <p class="text-sm mt-2" style="color: var(--od-green);">{{ session('success') }}</p>
                @endif
                @if($errors->has('email'))
                    <p class="text-sm mt-2" style="color: var(--od-danger);">{{ $errors->first('email') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="od-footer-bottom">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="od-footer-bottom-text text-center md:text-left">
                    <p>&copy; {{ date('Y') }} Edutrack Computer Training College. All rights reserved.</p>
                </div>
                <div class="flex flex-wrap items-center justify-center md:justify-end gap-x-4 gap-y-2">
                    <a href="#" class="od-footer-bottom-link">Privacy Policy</a>
                    <span class="od-footer-divider">|</span>
                    <a href="#" class="od-footer-bottom-link">Terms of Service</a>
                    <span class="od-footer-divider">|</span>
                    <a href="#" class="od-footer-bottom-link">Verify Certificate</a>
                </div>
            </div>
        </div>
    </div>
</footer>


