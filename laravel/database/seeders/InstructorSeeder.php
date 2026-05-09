<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('instructors')->insert([
            [
                'id' => 1,
                'user_id' => 2,
                'bio' => 'Experienced ICT instructor with over 10 years in software development and network administration.',
                'specialization' => 'Software Development, Networking',
                'years_experience' => 10,
                'education' => 'BSc Computer Science, University of Zambia',
                'certifications' => 'Cisco CCNA, Microsoft MCP',
                'rating' => 4.80,
                'total_students' => 150,
                'total_courses' => 5,
                'is_verified' => true,
            ],
        ]);
    }
}
