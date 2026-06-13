<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Enrollment::with(['user', 'course', 'paymentPlan']);

        if ($request->filled('course')) {
            $query->where('course_id', $request->course);
        }

        if ($request->filled('status')) {
            $query->where('enrollment_status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('enrolled_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('enrolled_at', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->latest('enrolled_at')->paginate(20)->withQueryString();
        $courses = Course::published()->orderBy('title')->get();

        return view('admin.enrollments.index', compact('enrollments', 'courses'));
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'enrollment_status' => 'required|in:Enrolled,In Progress,Completed,Dropped,Expired',
            'progress' => 'nullable|numeric|min:0|max:100',
            'final_grade' => 'nullable|numeric|min:0|max:100',
            'certificate_blocked' => 'nullable|boolean',
            'mode' => 'required|in:online,in_person,hybrid',
        ]);

        $enrollment->update([
            'enrollment_status' => $validated['enrollment_status'],
            'progress' => $validated['progress'] ?? $enrollment->progress,
            'final_grade' => $validated['final_grade'] ?? $enrollment->final_grade,
            'certificate_blocked' => $request->boolean('certificate_blocked'),
            'mode' => $validated['mode'],
        ]);

        return back()->with('success', 'Enrollment updated successfully.');
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return back()->with('success', 'Enrollment deleted successfully.');
    }
}
