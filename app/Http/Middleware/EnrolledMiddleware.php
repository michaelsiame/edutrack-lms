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

        if ($courseId && !auth()->user()->isEnrolledIn($courseId)) {
            abort(403, 'You must be enrolled in this course.');
        }

        return $next($request);
    }
}
