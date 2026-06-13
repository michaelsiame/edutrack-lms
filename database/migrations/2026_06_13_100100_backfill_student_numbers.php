<?php

use App\Services\StudentNumberService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Mint a permanent student number for every existing student that lacks one,
 * in account-creation order, year-scoped (EDU{year}/{seq}). Idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        $students = DB::table('students')
            ->whereNull('student_number')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        foreach ($students as $student) {
            $year = $student->created_at ? (int) date('Y', strtotime($student->created_at)) : (int) date('Y');
            DB::table('students')
                ->where('id', $student->id)
                ->update(['student_number' => StudentNumberService::generate($year)]);
        }
    }

    public function down(): void
    {
        // Identity values; not reversible.
    }
};
