<?php

namespace App\Services;

use App\Models\Student;

class StudentNumberService
{
    /**
     * Mint the next student number for a given join year, e.g. "EDU2026/00142".
     * The sequence resets per year. Call inside a DB transaction so the
     * MAX+1 read is consistent under concurrency; the unique index on
     * students.student_number is the final guard.
     */
    public static function generate(int $year): string
    {
        $prefix = 'EDU' . $year . '/';

        $last = Student::where('student_number', 'like', $prefix . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(student_number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)) as seq')
            ->value('seq');

        return $prefix . str_pad((string) (((int) $last) + 1), 5, '0', STR_PAD_LEFT);
    }
}
