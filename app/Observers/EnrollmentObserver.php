<?php

namespace App\Observers;

use App\Models\Enrollment;

class EnrollmentObserver
{
    public function saved(Enrollment $enrollment): void
    {
        StudentObserver::syncCounters($enrollment->user_id);
    }

    public function deleted(Enrollment $enrollment): void
    {
        StudentObserver::syncCounters($enrollment->user_id);
    }
}
