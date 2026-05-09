<footer class="bg-gray-800 text-white mt-auto">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-lg font-semibold mb-4">Edutrack LMS</h3>
                <p class="text-gray-300 text-sm">Empowering learners across Zambia with quality education and professional training.</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="text-gray-300 hover:text-white">Home</a></li>
                    <li><a href="{{ route('courses.index') }}" class="text-gray-300 hover:text-white">Courses</a></li>
                    <li><a href="{{ route('about') }}" class="text-gray-300 hover:text-white">About Us</a></li>
                    <li><a href="{{ route('contact') }}" class="text-gray-300 hover:text-white">Contact</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4">Contact</h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li>Kalomo, Zambia</li>
                    <li>edutrackzambia@gmail.com</li>
                    <li>+260 770 666 937</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm text-gray-400">
            <p>&copy; {{ date('Y') }} Edutrack Computer Training College. All rights reserved.</p>
        </div>
    </div>
</footer>
