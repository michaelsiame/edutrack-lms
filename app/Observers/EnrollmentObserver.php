<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Intake;

class EnrollmentObserver
{
    public function saved(Enrollment $enrollment): void
    {
        StudentObserver::syncCounters($enrollment->user_id);
        $this->syncCounts($enrollment);

        // If the enrolment moved between intakes, refresh the one it left too.
        if ($enrollment->wasChanged('intake_id')) {
            $previous = $enrollment->getOriginal('intake_id');
            if ($previous) {
                $this->recomputeIntake($previous);
            }
        }
    }

    public function deleted(Enrollment $enrollment): void
    {
        StudentObserver::syncCounters($enrollment->user_id);
        $this->syncCounts($enrollment);
    }

    public function restored(Enrollment $enrollment): void
    {
        $this->syncCounts($enrollment);
    }

    protected function syncCounts(Enrollment $enrollment): void
    {
        if ($enrollment->intake_id) {
            $this->recomputeIntake($enrollment->intake_id);
        }
        if ($enrollment->course_id) {
            $this->recomputeCourse($enrollment->course_id);
        }
    }

    /**
     * Recompute a counter from the source of truth: active (non-dropped,
     * non-deleted) enrolments. Uses a query-builder update so it never
     * re-triggers model events.
     */
    protected function recomputeIntake(int $intakeId): void
    {
        $count = Enrollment::where('intake_id', $intakeId)
            ->where('enrollment_status', '!=', 'Dropped')
            ->count();

        Intake::whereKey($intakeId)->update(['enrollment_count' => $count]);
    }

    protected function recomputeCourse(int $courseId): void
    {
        $count = Enrollment::where('course_id', $courseId)
            ->where('enrollment_status', '!=', 'Dropped')
            ->count();

        Course::whereKey($courseId)->update(['enrollment_count' => $count]);
    }
}
