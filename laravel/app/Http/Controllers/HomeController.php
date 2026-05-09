<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredCourses = Course::published()->featured()->take(6)->get();
        $latestCourses = Course::published()->latest()->take(6)->get();

        return view('home', compact('featuredCourses', 'latestCourses'));
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
