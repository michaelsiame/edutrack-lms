<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $this->authorizeInstructor($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $maxOrder = $course->modules()->max('display_order') ?? 0;

        Module::create([
            'course_id' => $course->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'display_order' => $validated['display_order'] ?? ($maxOrder + 1),
            'is_published' => true,
        ]);

        return back()->with('success', 'Module created successfully.');
    }

    public function update(Request $request, Course $course, Module $module)
    {
        $this->authorizeInstructor($course);

        if ($module->course_id !== $course->id) {
            abort(403, 'Module does not belong to this course.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $module->update($validated);

        return back()->with('success', 'Module updated successfully.');
    }

    public function destroy(Course $course, Module $module)
    {
        $this->authorizeInstructor($course);

        if ($module->course_id !== $course->id) {
            abort(403, 'Module does not belong to this course.');
        }

        $module->delete();

        return back()->with('success', 'Module deleted successfully.');
    }

    public function moveUp(Course $course, Module $module)
    {
        $this->authorizeInstructor($course);

        if ($module->course_id !== $course->id) {
            abort(403);
        }

        $prevModule = Module::where('course_id', $course->id)
            ->where('display_order', '<', $module->display_order)
            ->orderBy('display_order', 'desc')
            ->first();

        if ($prevModule) {
            $temp = $module->display_order;
            $module->update(['display_order' => $prevModule->display_order]);
            $prevModule->update(['display_order' => $temp]);
        }

        return back();
    }

    public function moveDown(Course $course, Module $module)
    {
        $this->authorizeInstructor($course);

        if ($module->course_id !== $course->id) {
            abort(403);
        }

        $nextModule = Module::where('course_id', $course->id)
            ->where('display_order', '>', $module->display_order)
            ->orderBy('display_order')
            ->first();

        if ($nextModule) {
            $temp = $module->display_order;
            $module->update(['display_order' => $nextModule->display_order]);
            $nextModule->update(['display_order' => $temp]);
        }

        return back();
    }

    protected function authorizeInstructor(Course $course): void
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor || $course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this course.');
        }
    }
}
