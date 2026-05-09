<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('certificates')->insert([
            [
                'certificate_id' => 1,
                'user_id' => 5,
                'course_id' => 1,
                'enrollment_id' => 3,
                'certificate_number' => 'EDT-2024-A1B2C3',
                'issued_date' => '2024-01-10',
                'verification_code' => 'abc123def456ghi789jkl012mno345pq',
                'final_score' => 85.50,
                'issued_at' => '2024-01-10 14:00:00',
                'is_verified' => true,
                'expiry_date' => null,
            ],
        ]);
    }
}
