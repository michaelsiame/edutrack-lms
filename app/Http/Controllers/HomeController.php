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
use App\Models\Contact;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Stats
        $totalStudents = DB::table('users')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->where('user_roles.role_id', Role::STUDENT)
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
            'total_students' => User::whereHas('roles', fn($q) => $q->where('role_id', Role::STUDENT))->where('status', 'active')->count(),
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

    public function contactSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
        ]);

        Contact::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        return redirect()->route('contact')
            ->with('success', 'Thank you for your message! We will get back to you within 24-48 hours.');
    }

    public function campus()
    {
        $stats = [
            'total_students' => User::whereHas('roles', fn($q) => $q->where('role_id', Role::STUDENT))->count(),
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

    public function testimonials(Request $request)
    {
        $query = Testimonial::approved();

        // Filter by course
        if ($request->filled('course')) {
            $query->where('course_taken', $request->input('course'));
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->input('rating'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('testimonial_text', 'like', "%{$search}%")
                  ->orWhere('course_taken', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'highest' => $query->orderByDesc('rating'),
            'lowest' => $query->orderBy('rating'),
            default => $query->latest(),
        };

        $testimonials = $query->paginate(12)->withQueryString();
        $featuredTestimonials = Testimonial::approved()->featured()->limit(3)->get();

        // Filter options
        $courses = Testimonial::approved()
            ->select('course_taken')
            ->distinct()
            ->orderBy('course_taken')
            ->pluck('course_taken');

        $stats = [
            'total_students' => User::whereHas('roles', fn($q) => $q->where('role_id', Role::STUDENT))->where('status', 'active')->count(),
            'total_courses' => Course::published()->count(),
            'total_enrollments' => Enrollment::count(),
            'avg_rating' => CourseReview::avg('rating') ?? 0,
        ];

        return view('testimonials', compact('testimonials', 'featuredTestimonials', 'stats', 'courses'));
    }

    public function events()
    {
        $upcomingEvents = Event::upcoming()->latest('event_date')->paginate(9);
        $pastEvents = Event::where('event_date', '<', now())->latest('event_date')->limit(6)->get();
        return view('events', compact('upcomingEvents', 'pastEvents'));
    }

    public function showEvent(Event $event)
    {
        $relatedEvents = Event::where('id', '!=', $event->id)
            ->where('status', '!=', 'cancelled')
            ->latest('event_date')
            ->limit(3)
            ->get();

        return view('events.show', compact('event', 'relatedEvents'));
    }
}
