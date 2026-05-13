<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $category = $request->input('category');
        $level = $request->input('level');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $sort = $request->input('sort', 'relevance');

        $coursesQuery = Course::published()
            ->with(['category', 'instructor.user']);

        if ($query) {
            $coursesQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%");
            });
        }

        if ($category) {
            $coursesQuery->where('category_id', $category);
        }

        if ($level) {
            $coursesQuery->where('level', $level);
        }

        if ($minPrice !== null) {
            $coursesQuery->where(function ($q) use ($minPrice) {
                $q->where('price', '>=', $minPrice)
                  ->orWhere(function ($sq) use ($minPrice) {
                      $sq->whereNotNull('discount_price')
                         ->where('discount_price', '>=', $minPrice);
                  });
            });
        }

        if ($maxPrice !== null) {
            $coursesQuery->where(function ($q) use ($maxPrice) {
                $q->where('price', '<=', $maxPrice)
                  ->orWhere(function ($sq) use ($maxPrice) {
                      $sq->whereNotNull('discount_price')
                         ->where('discount_price', '<=', $maxPrice);
                  });
            });
        }

        // Sorting
        switch ($sort) {
            case 'price_low':
                $coursesQuery->orderByRaw('COALESCE(discount_price, price) ASC');
                break;
            case 'price_high':
                $coursesQuery->orderByRaw('COALESCE(discount_price, price) DESC');
                break;
            case 'newest':
                $coursesQuery->latest();
                break;
            case 'rating':
                $coursesQuery->orderBy('rating', 'desc');
                break;
            default:
                if ($query) {
                    $coursesQuery->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", ["%{$query}%"]);
                } else {
                    $coursesQuery->latest();
                }
        }

        $courses = $coursesQuery->paginate(12)->withQueryString();
        $categories = CourseCategory::orderBy('name')->get();
        $levels = Course::published()->select('level')->distinct()->pluck('level')->filter();

        return view('search', compact('courses', 'categories', 'levels', 'query'));
    }
}
