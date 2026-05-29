<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnrolledMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403, 'Authentication required.');
        }

        $courseId = $request->route('course')?->id ?? $request->route('courseId');
        $user = auth()->user();

        if ($courseId) {
            $enrollment = $user->enrollments()->where('course_id', $courseId)->first();

            if (!$enrollment) {
                abort(403, 'You must be enrolled in this course.');
            }

            if (!$enrollment->canAccessContent()) {
                abort(403, 'Please complete at least a 30% deposit to access this course content.');
            }
        }

        return $next($request);
    }
}
