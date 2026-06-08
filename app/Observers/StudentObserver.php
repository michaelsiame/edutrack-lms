<?php

namespace App\Observers;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Student;

class StudentObserver
{
    public function created(Enrollment $enrollment): void
    {
        static::syncCounters($enrollment->user_id);
    }

    public function updated(Enrollment $enrollment): void
    {
        static::syncCounters($enrollment->user_id);
    }

    public function deleted(Enrollment $enrollment): void
    {
        static::syncCounters($enrollment->user_id);
    }

    public function createdCertificate(Certificate $certificate): void
    {
        static::syncCounters($certificate->user_id);
    }

    /**
     * Recalculate and update denormalized counters.
     */
    public static function syncCounters(int $userId): void
    {
        $student = Student::where('user_id', $userId)->first();
        if (!$student) {
            return;
        }

        $enrolled = Enrollment::where('user_id', $userId)->count();
        $completed = Enrollment::where('user_id', $userId)
            ->where('enrollment_status', 'Completed')
            ->count();
        $certificates = Certificate::whereHas('enrollment', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();

        $student->update([
            'total_courses_enrolled' => $enrolled,
            'total_courses_completed' => $completed,
            'total_certificates' => $certificates,
        ]);
    }
}
