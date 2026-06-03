<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create a default intake for every existing course
        $courses = DB::table('courses')->get();

        foreach ($courses as $course) {
            $intakeId = DB::table('intakes')->insertGetId([
                'course_id' => $course->id,
                'name' => $course->title . ' (Default)',
                'start_date' => $course->start_date,
                'end_date' => $course->end_date,
                'application_deadline' => $course->end_date,
                'max_students' => $course->max_students ?? 0,
                'enrollment_count' => $course->enrollment_count ?? 0,
                'status' => $course->status === 'published' ? 'open' : 'draft',
                'is_default' => true,
                'display_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Link existing enrollments to this default intake
            DB::table('enrollments')
                ->where('course_id', $course->id)
                ->whereNull('intake_id')
                ->update(['intake_id' => $intakeId]);

            // Link existing live sessions to this default intake
            // Find live sessions through lessons -> modules -> course
            $lessonIds = DB::table('lessons')
                ->join('modules', 'lessons.module_id', '=', 'modules.id')
                ->where('modules.course_id', $course->id)
                ->pluck('lessons.id');

            if ($lessonIds->isNotEmpty()) {
                DB::table('live_sessions')
                    ->whereIn('lesson_id', $lessonIds)
                    ->whereNull('intake_id')
                    ->update(['intake_id' => $intakeId]);
            }
        }
    }

    public function down(): void
    {
        // Unlink enrollments and live sessions
        DB::table('enrollments')->update(['intake_id' => null]);
        DB::table('live_sessions')->update(['intake_id' => null]);

        // Delete all default intakes
        DB::table('intakes')->where('is_default', true)->delete();
    }
};
