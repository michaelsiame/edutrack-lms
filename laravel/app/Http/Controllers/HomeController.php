<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Instructor;
use App\Models\CourseReview;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Hero slides fallback
        $heroSlides = [
            [
                'title' => 'Launch Your Tech Career',
                'subtitle' => 'With Industry-Recognized Skills',
                'description' => 'Join our growing community of Zambians who transformed their lives through TEVETA-certified programs.',
                'image_path' => '',
                'cta_text' => 'Explore Courses',
                'cta_link' => route('courses.index'),
                'secondary_cta_text' => 'Contact Us',
                'secondary_cta_link' => route('contact'),
            ],
            [
                'title' => 'State-of-the-Art Facilities',
                'subtitle' => 'Learn on Modern Equipment',
                'description' => 'Our computer labs feature the latest hardware and software for hands-on learning.',
                'image_path' => '',
                'cta_text' => 'Take a Tour',
                'cta_link' => route('about'),
                'secondary_cta_text' => 'View Programs',
                'secondary_cta_link' => route('courses.index'),
            ],
            [
                'title' => 'Your Success is Our Mission',
                'subtitle' => 'Real Skills, Real Careers',
                'description' => 'Our graduates work at top companies like MTN, Airtel, and major banks.',
                'image_path' => '',
                'cta_text' => 'Apply Now',
                'cta_link' => route('register'),
                'secondary_cta_text' => 'Contact Us',
                'secondary_cta_link' => route('contact'),
            ],
        ];

        // Stats
        $totalStudents = DB::table('users')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->where('user_roles.role_id', 4)
            ->where('users.status', 'active')
            ->count();

        $totalInstructors = DB::table('instructors')
            ->join('users', 'instructors.user_id', '=', 'users.id')
            ->where('users.status', 'active')
            ->count();

        $stats = [
            'total_students' => $totalStudents,
            'total_courses' => Course::published()->count(),
            'total_enrollments' => Enrollment::count(),
            'avg_rating' => CourseReview::avg('rating') ?? 0,
            'total_instructors' => $totalInstructors,
        ];

        // Featured by category
        $featuredByCategory = [];
        $categories = CourseCategory::with(['courses' => function($q) {
                $q->published()->limit(3);
            }])
            ->whereHas('courses', function($q) {
                $q->published();
            })
            ->limit(6)
            ->get();

        foreach ($categories as $category) {
            if ($category->courses->count() > 0) {
                $featuredByCategory[$category->name] = $category->courses;
            }
        }

        // Top featured (latest, excluding already shown)
        $shownIds = $categories->pluck('courses')->flatten()->pluck('id')->unique()->toArray();
        $topFeatured = Course::published()
            ->whereNotIn('id', $shownIds)
            ->latest()
            ->limit(6)
            ->get();

        return view('home', compact('heroSlides', 'stats', 'featuredByCategory', 'topFeatured'));
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }
}
