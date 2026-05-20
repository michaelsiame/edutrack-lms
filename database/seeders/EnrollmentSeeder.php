<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $enrollments = [
            [
                'id' => 1,
                'user_id' => 4,
                'student_id' => 4,
                'course_id' => 1,
                'enrolled_at' => '2024-01-15',
                'start_date' => '2024-01-20',
                'progress' => 75.00,
                'final_grade' => null,
                'enrollment_status' => 'In Progress',
                'payment_status' => 'completed',
                'amount_paid' => 1500.00,
                'completion_date' => null,
                'certificate_issued' => false,
                'certificate_blocked' => false,
                'total_time_spent' => 3600,
            ],
            [
                'id' => 2,
                'user_id' => 4,
                'student_id' => 4,
                'course_id' => 2,
                'enrolled_at' => '2024-02-01',
                'start_date' => '2024-02-05',
                'progress' => 30.00,
                'final_grade' => null,
                'enrollment_status' => 'In Progress',
                'payment_status' => 'pending',
                'amount_paid' => 500.00,
                'completion_date' => null,
                'certificate_issued' => false,
                'certificate_blocked' => false,
                'total_time_spent' => 900,
            ],
            [
                'id' => 3,
                'user_id' => 5,
                'student_id' => 5,
                'course_id' => 1,
                'enrolled_at' => '2023-11-01',
                'start_date' => '2023-11-05',
                'progress' => 100.00,
                'final_grade' => 85.50,
                'enrollment_status' => 'Completed',
                'payment_status' => 'completed',
                'amount_paid' => 1500.00,
                'completion_date' => '2024-01-10',
                'certificate_issued' => true,
                'certificate_blocked' => false,
                'total_time_spent' => 4800,
            ],
        ];

        foreach ($enrollments as $enrollment) {
            DB::table('enrollments')->updateOrInsert(['id' => $enrollment['id']], $enrollment);
        }
    }
}
