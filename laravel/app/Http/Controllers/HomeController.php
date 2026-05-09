<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Instructor;
use App\Models\CourseReview;
use App\Models\Testimonial;
use App\Models\Event;
use App\Models\HeroSlide;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
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

        // Dynamic data for sections
        $featuredTestimonials = Testimonial::approved()->featured()->latest()->limit(6)->get();
        $upcomingEvents = Event::upcoming()->featured()->limit(3)->get();
        $heroSlides = HeroSlide::active()->limit(3)->get();

        return view('home', compact(
            'stats',
            'featuredByCategory',
            'topFeatured',
            'featuredTestimonials',
            'upcomingEvents',
            'heroSlides'
        ));
    }

    public function about()
    {
        $stats = [
            'total_students' => User::whereHas('roles', fn($q) => $q->where('role_id', 4))->where('status', 'active')->count(),
            'total_courses' => Course::published()->count(),
            'total_enrollments' => Enrollment::count(),
            'avg_rating' => CourseReview::avg('rating') ?? 0,
        ];
        return view('about', compact('stats'));
    }

    public function contact()
    {
        return view('contact');
    }

    public function campus()
    {
        $stats = [
            'total_students' => User::whereHas('roles', fn($q) => $q->where('role_id', 4))->count(),
            'total_courses' => Course::published()->count(),
            'total_enrollments' => Enrollment::count(),
        ];
        $photos = \App\Models\InstitutionPhoto::active()->get();
        return view('campus', compact('stats', 'photos'));
    }

    public function faq()
    {
        return view('faq');
    }

    public function testimonials()
    {
        $testimonials = Testimonial::approved()->latest()->paginate(12);
        $featuredTestimonials = Testimonial::approved()->featured()->limit(3)->get();
        return view('testimonials', compact('testimonials', 'featuredTestimonials'));
    }

    public function events()
    {
        $upcomingEvents = Event::upcoming()->latest('event_date')->paginate(9);
        $pastEvents = Event::where('event_date', '<', now())->latest('event_date')->limit(6)->get();
        return view('events', compact('upcomingEvents', 'pastEvents'));
    }
}
